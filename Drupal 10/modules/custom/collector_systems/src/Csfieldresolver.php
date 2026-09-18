<?php

namespace Drupal\collector_systems;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;

/**
 * Collector Systems field value/label/link resolver.
 *
 * The single place field derivation logic lives, driven by
 * src/Csfieldregistry.php. Consumed by CustomTwig::fillObjectListHtml(),
 * CustomTwig::GetCustomizedObjectDetailsForTheme(), and
 * AdvancedSearchService::getObjectFields().
 */
class Csfieldresolver {

  /**
   * Classifies a field so the rest of this class knows how to derive its
   * value/label.
   *
   * @param string $object_field
   *   A Csconstants::X field key (e.g. Csconstants::InventoryDate).
   *
   * @return string
   *   One of: 'date', 'boolean', 'richtext', 'udf_text', 'udf_date',
   *   'udf_controlled_vocab', 'udf_richtext', 'additional_array',
   *   'additional_text', 'generic'.
   */
  public static function getCategory(string $object_field): string {
    static $map = NULL;

    if ($map === NULL) {
      $map = [];

      foreach (Csfieldregistry::DATE_FIELDS as $field) {
        $map[$field] = 'date';
      }
      foreach (Csfieldregistry::BOOLEAN_FIELDS as $field) {
        $map[$field] = 'boolean';
      }
      foreach (Csfieldregistry::RICHTEXT_FIELDS as $field) {
        $map[$field] = 'richtext';
      }
      foreach (array_keys(Csfieldregistry::ADDITIONAL_ARRAY_FIELD_MAP) as $field) {
        $map[$field] = 'additional_array';
      }
      foreach (Csfieldregistry::ADDITIONAL_TEXT_COLUMN_FIELDS as $field) {
        $map[$field] = 'additional_text';
      }
    }

    if (isset($map[$object_field])) {
      return $map[$object_field];
    }

    // UserDefined* families are numbered/homogeneous, so they're classified
    // by prefix instead of being enumerated above. Order matters:
    // most-specific prefix first, so e.g. UserDefinedRichText3 isn't caught
    // by a broader check.
    if (strpos($object_field, 'UserDefinedRichText') === 0) {
      return 'udf_richtext';
    }
    if (strpos($object_field, 'UserDefinedControlledVocab') === 0) {
      return 'udf_controlled_vocab';
    }
    if (strpos($object_field, 'UserDefinedDate') === 0) {
      return 'udf_date';
    }
    if (strpos($object_field, 'UserDefinedNumber') === 0 || strpos($object_field, 'UserDefinedCurrency') === 0) {
      return 'udf_text';
    }
    if (preg_match('/^UserDefined\d+$/', $object_field)) {
      return 'udf_text';
    }

    return 'generic';
  }

  /**
   * Derives the display value for an 'additional_array' field
   * (AdditionalArtists, AdditionalArtistMakers): JSON-decodes the row's raw
   * column and renders it as comma-joined, artist-detail-linked names.
   *
   * @param string $object_field
   *   A Csconstants::X key present in
   *   Csfieldregistry::ADDITIONAL_ARRAY_FIELD_MAP.
   * @param array $record
   *   The current row, keyed by column/field name.
   *
   * @return string
   *   Finished HTML (comma-joined links), or '' if empty/undecodable.
   */
  protected static function resolveAdditionalArrayValue(string $object_field, array $record): string {
    if (empty($record[$object_field]) || !isset(Csfieldregistry::ADDITIONAL_ARRAY_FIELD_MAP[$object_field])) {
      return '';
    }

    [$childKey, $idProperty, $nameProperty] = Csfieldregistry::ADDITIONAL_ARRAY_FIELD_MAP[$object_field];
    $decoded = json_decode($record[$object_field], TRUE);

    if (empty($decoded)) {
      return '';
    }

    return CustomTwig::implodeChildArrayProperty($decoded, $childKey, $idProperty, $nameProperty);
  }

  /**
   * Derives the display value for a 'udf_controlled_vocab' field
   * (UserDefinedControlledVocab1-10): prefers the pre-flattened
   * "{field}Text" sibling column, falling back to decoding the JSON
   * column's "Term" property.
   *
   * @param string $object_field
   *   A UserDefinedControlledVocab* Csconstants::X key.
   * @param array $record
   *   The current row, keyed by column/field name.
   *
   * @return string
   *   The escaped vocab term, or '' if neither source is populated.
   */
  protected static function resolveControlledVocabValue(string $object_field, array $record): string {
    $jsonVal = $record[$object_field] ?? '';
    $textVal = $record[$object_field . 'Text'] ?? '';

    if (empty($jsonVal) && empty($textVal)) {
      return '';
    }

    if (!empty($textVal)) {
      $term = $textVal;
    }
    else {
      $decoded = json_decode($jsonVal, TRUE);
      $term = is_array($decoded) && !empty($decoded['Term']) ? $decoded['Term'] : '';
    }

    return !empty($term) ? Html::escape($term) : '';
  }

  /**
   * The single value-derivation entry point: returns a field's fully-derived,
   * display-ready string (date formatted, boolean mapped to Yes/No, JSON
   * decoded, escaped as appropriate to its category) with no link/label
   * markup applied.
   *
   * @param string $object_field
   *   A Csconstants::X field key.
   * @param array $record
   *   The current row (object list row or object detail row), keyed by
   *   column/field name.
   *
   * @return string
   *   The derived value, or '' if there is nothing to render.
   */
  public static function getValue(string $object_field, array $record): string {
    switch (self::getCategory($object_field)) {
      case 'date':
      case 'udf_date':
        return !empty($record[$object_field]) ? date('m/d/Y', strtotime($record[$object_field])) : '';

      case 'boolean':
        return !empty($record[$object_field]) ? (($record[$object_field] == TRUE) ? 'Yes' : 'No') : '';

      case 'udf_controlled_vocab':
        return self::resolveControlledVocabValue($object_field, $record);

      case 'additional_array':
        return self::resolveAdditionalArrayValue($object_field, $record);

      case 'additional_text':
        if (empty($record[$object_field]) || empty($record[$object_field . 'Text'])) {
          return '';
        }
        return Html::escape($record[$object_field . 'Text']);

      case 'richtext':
      case 'udf_richtext':
        return !empty($record[$object_field]) ? Xss::filterAdmin($record[$object_field]) : '';

      default:
        return !empty($record[$object_field]) ? Html::escape($record[$object_field]) : '';
    }
  }

  /**
   * Whether a field's value should be wrapped in a link, and what to link to.
   *
   * @param string $object_field
   *   A Csconstants::X field key.
   *
   * @return array{type: string, id_column?: string}|null
   *   'type' is one of 'self' (open this object; caller supplies the link
   *   target, no id_column), 'collection', 'artist', or 'artist_maker'.
   *   'id_column' names the row column holding the related record's id.
   *   Null if the field isn't linked.
   */
  public static function getLinkType(string $object_field): ?array {
    if (in_array($object_field, Csfieldregistry::SELF_LINK_FIELDS, TRUE)) {
      return ['type' => 'self'];
    }
    if (in_array($object_field, Csfieldregistry::COLLECTION_LINK_FIELDS, TRUE)) {
      return [
        'type' => 'collection',
        'id_column' => 'CollectionId',
      ];
    }
    if (in_array($object_field, Csfieldregistry::ARTIST_LINK_FIELDS, TRUE)) {
      return [
        'type' => 'artist',
        'id_column' => 'ArtistId',
      ];
    }
    if (in_array($object_field, Csfieldregistry::ARTIST_MAKER_LINK_FIELDS, TRUE)) {
      return [
        'type' => 'artist_maker',
        'id_column' => 'ArtistMakerId',
      ];
    }

    return NULL;
  }

  /**
   * The single canonical label resolver for the whole module.
   *
   * Prefers the DB-stored, admin-customized label (set via the object detail
   * fields settings form), falling back to the static
   * Csconstants::{field}FieldLabel constant, falling back to $fallback (or
   * the raw field key if no fallback is given).
   *
   * @param string $object_field
   *   A Csconstants::X field key.
   * @param string|null $fallback
   *   Label to use when neither the DB nor a Csconstants::{field}FieldLabel
   *   constant has one. Defaults to the raw field key.
   *
   * @return string
   *   The resolved label.
   */
  public static function getLabel(string $object_field, ?string $fallback = NULL): string {
    /** @var \Drupal\collector_systems\ObjectFieldsService $objectFieldsService */
    $objectFieldsService = \Drupal::service('customize_object_detail_fields.object_fields_service');
    $dbLabel = $objectFieldsService->getObjectFieldLabelFromDatabase($object_field);

    // getObjectFieldLabelFromDatabase() returns the raw field key itself
    // when there's no stored row, which is how we detect "nothing found"
    // and fall through to the next source.
    if ($dbLabel !== $object_field) {
      return $dbLabel;
    }

    $constantName = 'Drupal\collector_systems\Csconstants::' . $object_field . 'FieldLabel';
    if (defined($constantName)) {
      return constant($constantName);
    }

    return $fallback ?? $object_field;
  }

}
