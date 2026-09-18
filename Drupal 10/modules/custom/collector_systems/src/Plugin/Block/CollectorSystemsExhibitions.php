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
 *   id = "collector_systems_exhibitions",
 *   admin_label = @Translation("Exhibitions"),
 * )
 */

class CollectorSystemsExhibitions extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The advanced search service.
   *
   * @var \Drupal\collector_systems\AdvancedSearchService
   */
  protected $advancedSearch;

  /**
   * Constructs a new CollectorSystemsExhibitions object.
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
        '#theme' => 'exhibitions-list-page',
        '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.
      ];
      return $build;
    }


    $listPageSize =  \Drupal::config('collector_systems.settings')->get('items_per_page');
    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $shskip =   0;
    $ajaxfor=   "listexhibition";
    $current_page=   "exhibitions";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "ExhibitionStartDate%20desc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";

    $requested_pageNo = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $shskip = ($requested_pageNo - 1) * $showrec;
    $nxshowrec=   isset($listPageSize) ? $listPageSize : 9;
    $nxshskip =   $shskip;
    $loadsec=1;

    $exhibition_table = "collector_systems_exhibitions";
    $database = Database::getConnection();

    switch ($dataorderby) {
      case 'ExhibitionStartDate%20asc':
        $sortColumn = 'ExhibitionStartDate';
        $sortOrder = 'ASC';
        break;
      case 'ExhibitionEndDate%20desc':
        $sortColumn = 'ExhibitionEndDate';
        $sortOrder = 'DESC';
        break;
      case 'ExhibitionEndDate%20asc':
        $sortColumn = 'ExhibitionEndDate';
        $sortOrder = 'ASC';
        break;
      case 'ExhibitionSubject%20desc':
        $sortColumn = 'ExhibitionSubject';
        $sortOrder = 'DESC';
        break;
      case 'ExhibitionSubject%20asc':
        $sortColumn = 'ExhibitionSubject';
        $sortOrder = 'ASC';
        break;
      case 'ExhibitionStartDate%20desc':
      default:
        $sortColumn = 'ExhibitionStartDate';
        $sortOrder = 'DESC';
        break;
    }

    // Build one query with all conditions + ordering applied, then derive
    // the count query from it (rather than a separate, unconditioned count
    // query) so the displayed total always matches what's actually filtered.
    $query = $database->select($exhibition_table, 'et');
    $query->fields('et');

    // Advanced Search takes priority over the plain search box (matches the
    // WP plugin's mutual-exclusivity rule): if this request submitted the
    // advanced panel, ignore qSearch entirely.
    if ($this->advancedSearch->isActiveRequest()) {
      $advanced_condition = $this->advancedSearch->buildConditionGroup($query, 'exhibitions');
      if ($advanced_condition) {
        $query->condition($advanced_condition);
      }
    }
    elseif (!empty($qSearch)) {
      $query->condition('ExhibitionSubject', '%' . $database->escapeLike($qSearch) . '%', 'LIKE');
    }

    $query->orderBy($sortColumn, $sortOrder);

    $count_query = $query->countQuery();
    $count = $count_query->execute()->fetchField();

    $query->range($shskip, $showrec);
    $all_exhibitions = $query->execute()->fetchAll(\PDO::FETCH_OBJ);

    $showImagesOnListPages =  \Drupal::config('collector_systems.settings')->get('show_images_exhibitions');

    $advanced_search_fields = $this->advancedSearch->getFields('exhibitions');
    $build_advanced_search = [
      'fields' => $advanced_search_fields,
      'active' => $this->advancedSearch->isActiveRequest(),
      'values' => $this->advancedSearch->getSubmittedValues('exhibitions'),
      'sort_by' => $dataorderby,
      'data_id' => NULL,
      'clear_url' => \Drupal::request()->getPathInfo(),
    ];

    $build = [
      '#theme' => 'exhibitions-list-page',
      '#all_exhibitions' => $all_exhibitions,
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#count' => $count,
      '#dataorderby' => $dataorderby,
      '#current_page' => $current_page,
      '#qSearch' => $qSearch,
      '#advanced_search' => $build_advanced_search,
      '#loadsec' => $loadsec,
      '#requested_pageNo' => $requested_pageNo,
      '#showImagesOnListPages' => $showImagesOnListPages,
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];

    $build['#attached']['library'][] = 'collector_systems/collector-systems';

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
