<?php

namespace Drupal\collector_systems\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\collector_systems\AdvancedSearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom shortcode block.
 *
 * @Block(
 *   id = "collector_systems_artists",
 *   admin_label = @Translation("Artists"),
 * )
 */

class CollectorSystemsArtists extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The advanced search service.
   *
   * @var \Drupal\collector_systems\AdvancedSearchService
   */
  protected $advancedSearch;

  /**
   * Constructs a new CollectorSystemsArtists object.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\collector_systems\AdvancedSearchService $advanced_search
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AdvancedSearchService $advanced_search) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->advancedSearch = $advanced_search;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('collector_systems.advanced_search_service')
    );
  }

 /**
   * {@inheritdoc}
   */
  public function build() {
    if(!$this->is_CS_tables_exists()){
      $build = [
        '#theme' => 'artists-list-page',
        '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.
      ];
      return $build;
    }

    $config = \Drupal::config('collector_systems.settings');
    $listPageSize = $config->get('items_per_page');
    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $current_page = "artists";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "ArtistName%20asc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";
    $requested_page = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $shskip = ($requested_page - 1) * $showrec;

    $enableAlphabeticalArtists = $config->get('enable_alphabetical_artists');
    $char = ($enableAlphabeticalArtists && isset($_REQUEST['char']))
      ? strtoupper(substr(trim($_REQUEST['char']), 0, 1))
      : '';

    $artist_table = 'collector_systems_artists';
    $db = Database::getConnection();
    $sortDir = strpos(strtolower(urldecode($dataorderby)), 'desc') !== FALSE ? 'DESC' : 'ASC';

    // Build one query with all conditions + ordering applied, then derive
    // the count query from it, so the displayed total always matches what's
    // actually filtered.
    $query = $db->select($artist_table, 'a');
    $query->fields('a');

    // The A-Z letter filter is a separate axis from search (which letter
    // subset), so it stays combinable with either search mode.
    if (!empty($char)) {
      $query->condition('ArtistName', $db->escapeLike($char) . '%', 'LIKE');
    }

    // Advanced Search takes priority over the plain search box (matches the
    // WP plugin's mutual-exclusivity rule): if this request submitted the
    // advanced panel, ignore qSearch entirely.
    if ($this->advancedSearch->isActiveRequest()) {
      $advanced_condition = $this->advancedSearch->buildConditionGroup($query, 'artists');
      if ($advanced_condition) {
        $query->condition($advanced_condition);
      }
    }
    elseif (!empty($qSearch)) {
      $query->condition('ArtistName', '%' . $db->escapeLike($qSearch) . '%', 'LIKE');
    }

    $query->orderBy('ArtistName', $sortDir);

    $count_query = $query->countQuery();
    $count = $count_query->execute()->fetchField();

    $query->range($shskip, $showrec);
    $AllArtists = $query->execute()->fetchAll();

    // Group by first letter for alphabetical mode.
    $groupedArtists = [];
    if ($enableAlphabeticalArtists) {
      foreach ($AllArtists as $artist) {
        $firstLetter = strtoupper(substr($artist->ArtistName, 0, 1));
        $groupedArtists[$firstLetter][] = $artist;
      }
      ($sortDir === 'DESC') ? krsort($groupedArtists) : ksort($groupedArtists);
    }

    $loadsec = 1;
    $collector_systems_module_path = \Drupal::service('extension.path.resolver')->getPath('module', 'collector_systems');
    $showImagesOnListPages = $config->get('show_images_artists');

    $advanced_search_fields = $this->advancedSearch->getFields('artists');
    $build_advanced_search = [
      'fields' => $advanced_search_fields,
      'active' => $this->advancedSearch->isActiveRequest(),
      'filtered' => $this->advancedSearch->hasActiveFilters('artists'),
      'values' => $this->advancedSearch->getSubmittedValues('artists'),
      'sort_by' => $dataorderby,
      'data_id' => NULL,
      'clear_url' => \Drupal::request()->getPathInfo(),
    ];

    $build = [
      '#theme' => 'artists-list-page',
      '#AllArtists' => $AllArtists,
      '#nxshowrec' => $showrec,
      '#nxshskip' => $shskip,
      '#count' => $count,
      '#dataorderby' => $dataorderby,
      '#current_page' => $current_page,
      '#qSearch' => $qSearch,
      '#advanced_search' => $build_advanced_search,
      '#loadsec' => $loadsec,
      '#requested_page' => $requested_page,
      '#collector_systems_module_path' => $collector_systems_module_path,
      '#showImagesOnListPages' => $showImagesOnListPages,
      '#enableAlphabeticalArtists' => $enableAlphabeticalArtists,
      '#groupedArtists' => $groupedArtists,
      '#char' => $char,
      '#hasMoreResults' => $count > ($shskip + $showrec),
      '#alphabetLetters' => range('A', 'Z'),
      '#cache' => ['max-age' => 0,],
    ];

    $build['#attached']['library'][] = 'collector_systems/collector-systems';

    if ($enableAlphabeticalArtists) {
      $build['#attached']['library'][] = 'collector_systems/artists-alpha';
      $build['#attached']['drupalSettings']['collectorSystems']['artistsAlpha'] = [
        'hasMore'     => $count > ($shskip + $showrec),
        'currentPage' => $requested_page,
        'char'        => $char,
        'ajaxUrl'     => '/collector-systems/artists/load-more',
      ];
    }

    return $build;
  }

  public function is_CS_tables_exists(){
    $tables = [
      'collector_systems_objects',
      'collector_systems_artists',
      'collector_systems_collections',
      'collector_systems_groups',
      'collector_systems_exhibitions',
      'collector_systems_exhibition_objects',
      'collector_systems_group_objects',
      'collector_systems_thumb_images'

    ];
    $tables_exists = true;
    $database = Database::getConnection();
    foreach($tables as $table){
      if(!$database->schema()->tableExists($table) ){
        $tables_exists = false;
      }
    }

    return $tables_exists;
  }


  public function getCommaSeparatedFieldsForListPage(){
    $db = \Drupal::database();

    $tblnm = "collector_systems_clsobjects_fields";
    $settblnm = $tblnm;

    $query = $db->select($settblnm, 'c')
      ->fields('c', ['fieldname'])
      ->condition('fieldtype', 'ObjectList');

    $result = $query->execute()->fetchAllAssoc('fieldname');

    $values = implode(',', array_keys($result));

    return $values;

  }
}
