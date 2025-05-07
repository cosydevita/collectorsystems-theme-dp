<?php

namespace Drupal\collector_systems\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a custom shortcode block.
 *
 * @Block(
 *   id = "collector_systems_objects",
 *   admin_label = @Translation("Objects"),
 * )
 */

class CollectorSystemsObjects extends BlockBase  implements ContainerFactoryPluginInterface{

 /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

    /**
   * Constructs a new CollectorSystemsObjects object.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

   /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

 /**
   * {@inheritdoc}
   */
  public function build() {
    if(!$this->is_CS_tables_exists()){
      $build = [
        '#theme' => 'objects-list-page',
        '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.
      ];
      return $build;
    }

    $listPageSize =  $this->configFactory->get('collector_systems.settings')->get('items_per_page');

    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $shskip = 0;
    $ajaxfor = "artobjects";
    $current_page = "objects";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "Title%20asc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";

    $requested_pageNo = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $shskip = ($requested_pageNo - 1) * $showrec;

    $nxshowrec = isset($listPageSize) ? $listPageSize : 9;
    $nxshskip = $shskip;
    $loadsec = 1;

    $customized_fields = $this->getCommaSeparatedFieldsForListPage();
    if($customized_fields){
      $customized_fields_array = explode(',', $customized_fields);
    }

    // Count Total Objects
    $object_table = 'CSObjects';

    // Collection Table
    $collection_table = 'Collections';

    // Fetch object details from the database
    $query = Database::getConnection()->select($object_table, 'o');
    if($customized_fields){
      $or_condition_group = $query->orConditionGroup();

      foreach($customized_fields_array as $customized_field){
        $or_condition_group->condition($customized_field, '%' . Database::getConnection()->escapeLike($qSearch) . '%', 'LIKE');
      }
      $query->condition($or_condition_group);

    }


    if ($dataorderby === "Title%20desc" && $qSearch !== NULL) {

      $query->orderBy('Title', 'DESC');
    }
    else if($dataorderby === "Title%20asc" && $qSearch !== NULL)
    {

      $query->orderBy('Title', 'ASC');
    }
    else if($dataorderby === "InventoryNumber%20asc" && $qSearch !== NULL)
    {
        $query->orderBy('InventoryNumber', 'ASC');
    }
    else if($dataorderby === "InventoryNumber%20desc" && $qSearch !== NULL)
    {

      $query->orderBy('InventoryNumber', 'DESC');
    }
    else if($dataorderby === "ObjectDate%20desc" && $qSearch !== NULL)
    {

      $query->orderBy('ObjectDate', 'DESC');
    }
    else if($dataorderby === "ObjectDate%20asc" && $qSearch !== NULL)
    {

      $query->orderBy('ObjectDate', 'ASC');
    }
    else if($dataorderby === "Collection/CollectionName%20asc" && $qSearch !== NULL){
      $query->fields('o')
      ->fields('c', ['CollectionName'])
      ->join($collection_table, 'c', 'o.CollectionId = c.CollectionId');

      $query->orderBy('c.CollectionName', 'ASC');

    }
    else if($dataorderby === "Collection/CollectionName%20desc" && $qSearch !== NULL){
      $query->fields('o')
      ->fields('c', ['CollectionName'])
      ->join($collection_table, 'c', 'o.CollectionId = c.CollectionId');

      $query->orderBy('c.CollectionName', 'DESC');

    }
    // Add other conditions for different order by options and search criteria

    // Ensure the SELECT clause includes all necessary columns
    $query->fields('o');
    try{
      $count_query = $query->countQuery();
      $total_results = $count_query->execute()->fetchField();
    } catch (\Exception $e) {
      \Drupal::logger('collector_systems')->error('Error in count query: @message', ['@message' => $e->getMessage()]);
      $total_results = 0; // Default value in case of failure
    }

    try{
      $query->range($shskip, $showrec);

      $result = $query->execute();
      $object_details = $result->fetchAllAssoc('ObjectId'); // Assuming 'ObjectId' is the primary key field
    } catch (\Exception $e) {
      \Drupal::logger('collector_systems')->error('Error in main query execution: @message', ['@message' => $e->getMessage()]);
      $object_details = []; // Default empty array in case of failure
    }


    $module_path = \Drupal::service('extension.list.module')->getPath('collector_systems');

    $enable_maps = $this->configFactory->get('collector_systems.settings')->get('enable_maps');
    if($enable_maps){
      //start azure map
      $locations = [];
      try{
        $query_without_range = $query->range(); //to include all results without range
        $result = $query_without_range->execute();
        $object_details_without_range =  $result->fetchAllAssoc('ObjectId');

      }catch (\Exception $e) {
          \Drupal::logger('collector_systems')->error('Error generating map locations: @message', ['@message' => $e->getMessage()]);
          $object_details_without_range = [];
      }
      foreach ($object_details_without_range as $object) {
        $Latitude = $object->Latitude;
        $Longitude = $object->Longitude;
        $AddressName = $object->AddressName;
        $main_image_attachment = $object->main_image_attachment;
        $main_image_path = $object->main_image_path;
        $object_id = $object->ObjectId;

        $locations_data =  [
          "latitude" => $Latitude,
          "longitude" => $Longitude,
          "AddressName" => $AddressName,
          "main_image_attachment" => $main_image_attachment ? base64_encode($main_image_attachment) : '',
          "main_image_path" => $main_image_path,
          "object_detail_url" => '/artobject-detail?dataId='. $object_id,

        ];
        if($Latitude && $Longitude){
          if($customized_fields && !empty($customized_fields_array)){
            foreach($customized_fields_array as $customized_field){
              $locations_data['data_selected_fields'][$customized_field] = $object->$customized_field;

            }
          }
          $locations[] =  $locations_data;
        }

      }


      $config = $this->configFactory->get('collector_systems.settings');
      $azure_subscription_key = $config->get('azure_map_subscription_key');

      $js_settings = [
        'locations' => $locations,
        'subscription_key' => $azure_subscription_key,
        'module_path' => $module_path
      ];

      //end azure map
    }


    $build = [
      '#theme' => 'objects-list-page',
      '#object_details' => $object_details,
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#count' => $total_results,
      '#dataorderby' => $dataorderby,
      '#current_page' => $current_page,
      '#requested_pageNo' => $requested_pageNo,
      '#qSearch' => $qSearch,
      '#module_path' => $module_path,
      '#enable_maps' => $enable_maps,
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];

    $build['#attached']['library'][] = 'collector_systems/collector-systems';

    if($enable_maps){
      foreach ($js_settings as $key => $value) {
        $build['#attached']['drupalSettings']['azure_map'][$key] = $value;
      }
      $build['#attached']['library'][] = 'collector_systems/azure_map';
      $build['#attached']['library'][] = 'collector_systems/custom_tabs';
    }

    return $build;
  }

  public function is_CS_tables_exists(){
    $tables = [
      'CSObjects',
      'Artists',
      'Collections',
      'Groups',
      'Exhibitions',
      'ExhibitionObjects',
      'GroupObjects',
      'ThumbImages'

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

    $tblnm = "clsobjects_fields";
    $settblnm = $tblnm;

    $query = $db->select($settblnm, 'c')
      ->fields('c', ['fieldname'])
      ->condition('fieldtype', 'ObjectList');

    $result = $query->execute()->fetchAllAssoc('fieldname');

    $values = implode(',', array_keys($result));

    return $values;

  }
}
