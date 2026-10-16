<?php

namespace Drupal\collector_systems;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Builds and applies the Advanced Search field definitions and conditions.
 *
 * Single choke point (isEnabled()/getFields()) for the "Enable Advanced
 * Search" site setting: when disabled, getFields() returns an empty array
 * for every page, which both hides the UI (templates check
 * `advanced_search.fields is not empty`) and no-ops buildConditionGroup()
 * (it can't add conditions for fields it doesn't know about).
 */
class AdvancedSearchService {

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The objects service.
   *
   * @var \Drupal\collector_systems\ObjectsService
   */
  protected $objectsService;

  public function __construct(ConfigFactoryInterface $config_factory, Connection $database, ObjectsService $objects_service, TranslationInterface $string_translation) {
    $this->configFactory = $config_factory;
    $this->database = $database;
    $this->objectsService = $objects_service;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Whether Advanced Search is enabled site-wide. Off by default.
   *
   * @return bool
   */
  public function isEnabled() {
    return (bool) $this->configFactory->get('collector_systems.settings')->get('enable_advanced_search');
  }

  /**
   * Returns the Advanced Search field definitions for a given page.
   *
   * @param string $pagename
   *   One of: objects, artists, groups, exhibitions, artist-detail,
   *   group-detail, exhibition-detail, collection-detail. Anything else
   *   (notably 'collections', which has only one searchable field, and
   *   'artobject-detail', which isn't a list) resolves to no fields.
   *
   * @return array
   *   Array of field definitions, each:
   *   - key: string, used for the adv_{key} request parameter.
   *   - label: string, human-readable label.
   *   - type: 'text' or 'daterange'.
   *   - column: string (text fields), table-qualified where needed.
   *   - from_column/to_column: string (daterange fields only).
   */
  public function getFields($pagename) {
    if (!$this->isEnabled()) {
      return [];
    }
    // The dynamic Objects field set can include collection/artist columns
    // (e.g. FullCollectionName, ArtistName), so it's only safe to surface
    // those on a query that actually joins those tables under the alias
    // this maps to -- otherwise buildConditionGroup() would reference a
    // table alias the caller's query doesn't have. Each detail page's
    // query shape (checked directly against its controller method) decides
    // this per page, not the caller, since it's fixed per page, not
    // per-request.
    switch ($pagename) {
      case 'objects':
        // Objects list: joins collections as 'c' and artists as 'a'.
        return $this->getObjectFields('o', 'c', 'a');

      case 'artist-detail':
        // ArtistDetailPage's nested object query: objects only, no joins.
        return $this->getObjectFields('o', NULL, NULL);

      case 'group-detail':
      case 'exhibition-detail':
        // Group/ExhibitionDetailPage's nested object query: objects table
        // is aliased 'o' (joined onto the junction table), no other joins.
        return $this->getObjectFields('o', NULL, NULL);

      case 'collection-detail':
        // CollectionDetailPage's nested object query: joins collections as
        // 'c' (for the nested-set LeftExtent/RightExtent bounds), no
        // artists join.
        return $this->getObjectFields('o', 'c', NULL);

      case 'artists':
        return $this->getArtistFields();

      case 'groups':
        return $this->getGroupFields();

      case 'exhibitions':
        return $this->getExhibitionFields();

      default:
        return [];
    }
  }

  /**
   * Whether the current request is an Advanced Search submission.
   *
   * @return bool
   */
  public function isActiveRequest() {
    return !empty($_REQUEST['advSearch']);
  }

  /**
   * Whether Advanced Search is actually narrowing this page's results, not
   * just whether the panel was submitted -- an empty submit shouldn't be
   * treated as "filtered" (e.g. for a "results are filtered" UI indicator).
   *
   * @param string $pagename
   *
   * @return bool
   */
  public function hasActiveFilters($pagename) {
    if (!$this->isActiveRequest()) {
      return FALSE;
    }
    foreach ($this->getSubmittedValues($pagename) as $value) {
      if ($value !== '') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Reads the submitted adv_* request values for a page's fields.
   *
   * Single source of truth for reading these values -- used both to
   * prefill the panel's inputs and (by buildConditionGroup()) to filter
   * the query, so the two can never disagree.
   *
   * @param string $pagename
   *
   * @return array
   *   Keyed by request parameter name (e.g. 'adv_ArtistName',
   *   'adv_ExhibitionDate_from'), trimmed string values.
   */
  public function getSubmittedValues($pagename) {
    $values = [];
    foreach ($this->getFields($pagename) as $field) {
      if ($field['type'] === 'daterange') {
        $values['adv_' . $field['key'] . '_from'] = trim((string) ($_REQUEST['adv_' . $field['key'] . '_from'] ?? ''));
        $values['adv_' . $field['key'] . '_to'] = trim((string) ($_REQUEST['adv_' . $field['key'] . '_to'] ?? ''));
      }
      else {
        $values['adv_' . $field['key']] = trim((string) ($_REQUEST['adv_' . $field['key']] ?? ''));
      }
    }
    return $values;
  }

  /**
   * Builds an AND-combined condition group for the submitted Advanced
   * Search values, or NULL when disabled / not an advanced request / no
   * fields were actually filled in.
   *
   * The caller is responsible for applying the result via
   * $query->condition($result).
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The query the condition group will be attached to (needed so
   *   andConditionGroup()/orConditionGroup() are created against the right
   *   query object).
   * @param string $pagename
   *
   * @return \Drupal\Core\Database\Query\Condition|null
   */
  public function buildConditionGroup(SelectInterface $query, $pagename) {
    if (!$this->isActiveRequest()) {
      return NULL;
    }
    $fields = $this->getFields($pagename);
    if (empty($fields)) {
      return NULL;
    }

    $values = $this->getSubmittedValues($pagename);
    $and = $query->andConditionGroup();
    $has_condition = FALSE;

    foreach ($fields as $field) {
      if ($field['type'] === 'daterange') {
        $from = $values['adv_' . $field['key'] . '_from'];
        $to = $values['adv_' . $field['key'] . '_to'];

        // Interval overlap: the record's own [start, end] range overlaps
        // the requested [from, to] range.
        if ($from !== '') {
          $and->condition($query->orConditionGroup()
            ->isNull($field['to_column'])
            ->condition($field['to_column'], $from . ' 00:00:00', '>='));
          $has_condition = TRUE;
        }
        if ($to !== '') {
          $and->condition($query->orConditionGroup()
            ->isNull($field['from_column'])
            ->condition($field['from_column'], $to . ' 23:59:59', '<='));
          $has_condition = TRUE;
        }
      }
      else {
        $value = $values['adv_' . $field['key']];
        if ($value !== '') {
          $and->condition($field['column'], '%' . $this->database->escapeLike($value) . '%', 'LIKE');
          $has_condition = TRUE;
        }
      }
    }

    return $has_condition ? $and : NULL;
  }

  /**
   * Objects field set: dynamic, sourced from the admin-configurable field
   * registry (collector_systems_clsobjects_fields), resolved against
   * whichever of the objects/collections/artists tables the caller's query
   * actually joins.
   *
   * @param string $object_alias
   *   The alias the caller's query uses for the objects table.
   * @param string|null $collection_alias
   *   The alias for the collections table, or NULL if the caller's query
   *   doesn't join it (fields that would resolve to a collections column
   *   are skipped entirely rather than referencing a nonexistent alias).
   * @param string|null $artist_alias
   *   Same, for the artists table.
   *
   * @return array
   */
  private function getObjectFields($object_alias, $collection_alias, $artist_alias) {
    $fieldnames = $this->database->select('collector_systems_clsobjects_fields', 'c')
      ->fields('c', ['fieldname'])
      ->distinct()
      ->execute()
      ->fetchCol();

    $object_list_fieldnames = $this->database->select('collector_systems_clsobjects_fields', 'c')
      ->fields('c', ['fieldname'])
      ->condition('fieldtype', 'ObjectList')
      ->execute()
      ->fetchCol();

    $object_columns = $this->objectsService->cs_get_table_columns('collector_systems_objects');
    $collection_columns = $collection_alias !== NULL ? $this->objectsService->cs_get_table_columns('collector_systems_collections') : [];
    $artist_columns = $artist_alias !== NULL ? $this->objectsService->cs_get_table_columns('collector_systems_artists') : [];

    $fields = [];
    foreach ($fieldnames as $fieldname) {
      if ($collection_alias !== NULL && in_array($fieldname, $collection_columns, TRUE)) {
        $column = "$collection_alias.$fieldname";
      }
      elseif ($artist_alias !== NULL && in_array($fieldname, $artist_columns, TRUE)) {
        $column = "$artist_alias.$fieldname";
      }
      elseif (in_array($fieldname, $object_columns, TRUE)) {
        $column = "$object_alias.$fieldname";
      }
      else {
        // Configured field doesn't exist on any table this query actually
        // joins -- skip it rather than building a condition against a
        // nonexistent alias.
        continue;
      }

      // Fields here can come from either the Object Detail or Object List selected-fields
      // table, so check both override buckets; object_list wins if both are set.
      $label = Csfieldresolver::getLabel($fieldname, $this->prettifyFieldName($fieldname));
      $objectListOverride = in_array($fieldname, $object_list_fieldnames, TRUE) ? Csfieldresolver::getLabelOverride('object_list', $fieldname) : NULL;
      if ($objectListOverride !== NULL) {
        $label = $objectListOverride;
      }

      $fields[] = [
        'key' => $fieldname,
        'label' => $label,
        'type' => 'text',
        'column' => $column,
      ];
    }
    return $fields;
  }

  /**
   * Artists field set: hardcoded, matches the columns the existing plain
   * search already searches against.
   *
   * @return array
   */
  private function getArtistFields() {
    return [
      ['key' => 'ArtistName', 'label' => $this->t('Artist Name'), 'type' => 'text', 'column' => 'ArtistName'],
      ['key' => 'ArtistFirst', 'label' => $this->t('First Name'), 'type' => 'text', 'column' => 'ArtistFirst'],
      ['key' => 'ArtistLast', 'label' => $this->t('Last Name'), 'type' => 'text', 'column' => 'ArtistLast'],
      ['key' => 'ArtistYears', 'label' => $this->t('Years'), 'type' => 'text', 'column' => 'ArtistYears'],
      ['key' => 'ArtistNationality', 'label' => $this->t('Nationality'), 'type' => 'text', 'column' => 'ArtistNationality'],
      ['key' => 'ArtistLocale', 'label' => $this->t('Locale'), 'type' => 'text', 'column' => 'ArtistLocale'],
      ['key' => 'ArtistBio', 'label' => $this->t('Biography'), 'type' => 'text', 'column' => 'ArtistBio'],
    ];
  }

  /**
   * Groups field set: hardcoded, matches the existing plain search.
   *
   * @return array
   */
  private function getGroupFields() {
    return [
      ['key' => 'GroupDescription', 'label' => $this->t('Description'), 'type' => 'text', 'column' => 'GroupDescription'],
      ['key' => 'GroupMemo', 'label' => $this->t('Memo'), 'type' => 'text', 'column' => 'GroupMemo'],
    ];
  }

  /**
   * Exhibitions field set: hardcoded, matches the existing plain search,
   * plus ExhibitionDate as a date-range field.
   *
   * @return array
   */
  private function getExhibitionFields() {
    return [
      ['key' => 'ExhibitionSubject', 'label' => $this->t('Subject'), 'type' => 'text', 'column' => 'ExhibitionSubject'],
      ['key' => 'ExhibitionLocation', 'label' => $this->t('Location'), 'type' => 'text', 'column' => 'ExhibitionLocation'],
      ['key' => 'ExhibitionMemo', 'label' => $this->t('Memo'), 'type' => 'text', 'column' => 'ExhibitionMemo'],
      [
        'key' => 'ExhibitionDate',
        'label' => $this->t('Exhibition Date'),
        'type' => 'daterange',
        'from_column' => 'ExhibitionStartDate',
        'to_column' => 'ExhibitionEndDate',
      ],
    ];
  }

  /**
   * Turns a raw DB field name into a human-readable fallback label
   * (used only when no Csconstants::<field>FieldLabel constant exists).
   *
   * @param string $fieldname
   *
   * @return string
   */
  private function prettifyFieldName($fieldname) {
    $label = preg_replace('/(?<!^)([A-Z])/', ' $1', $fieldname);
    return ucfirst(trim($label));
  }

}
