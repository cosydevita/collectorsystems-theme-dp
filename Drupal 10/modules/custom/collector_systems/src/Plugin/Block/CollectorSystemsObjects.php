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
    $connection = \Drupal::database();
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

    $customized_fields_objects_list_array = $this->getCustomizedObjectListFields();
    // Set default sorting to the first sortable field if available, otherwise default to 'Title asc'
    // the first selected option in the customize object list field settings will be the default sorting field. 
    $default_orderby = !empty($customized_fields_objects_list_array) ? rawurlencode($customized_fields_objects_list_array[0]. ' asc') : 'Title%20asc';
    // if sortBy is set in the request, use it; otherwise, use the default sorting.
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : $default_orderby;
    
    // Get sorting parameters based on dataorderby value
    list($sortParam, $sortOrder) = $this->cs_get_sorting_params_for_objects_list($dataorderby);

    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";

    $requested_pageNo = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $shskip = ($requested_pageNo - 1) * $showrec;

    $nxshowrec = isset($listPageSize) ? $listPageSize : 9;
    $nxshskip = $shskip;
    $loadsec = 1;

    $customized_fields = $this->getCommaSeparatedFieldsForSearch();
    $customized_fields_array = [];
    if($customized_fields){
      $customized_fields_array = explode(',', $customized_fields);
      
    }
    // print_r($customized_fields_array);

    // Count Total Objects
    $object_table = 'collector_systems_objects';

    // Collection Table
    $collection_table = 'collector_systems_collections';
    $artist_table = 'collector_systems_artists';

   // Fetch object details from the database.
    $connection = \Drupal::database();
    $query = $connection->select($object_table, 'o');

    // Join related tables
    $query->leftJoin($collection_table, 'c', 'o.CollectionId = c.CollectionId');
    $query->leftJoin($artist_table, 'a', 'a.ArtistId = o.ArtistId');

    // Select the desired fields.
    $query->fields('o');
    $query->fields('c');
    $query->fields('a');

    // Apply search conditions if needed.
    if (!empty($customized_fields_array) && !empty($qSearch)) {
      $escaped_search = '%' . $connection->escapeLike($qSearch) . '%';
      $or_condition_group = $query->orConditionGroup();

      // Get the column names for each table

      $object_columns = $this->cs_get_table_columns($object_table);
      $collection_columns = $this->cs_get_table_columns($collection_table);
      $artist_columns = $this->cs_get_table_columns($artist_table);

      foreach ($customized_fields_array as $field) {
        if (in_array($field, $collection_columns, true)) {
          $or_condition_group->condition("c.$field", $escaped_search, 'LIKE');
        }
        elseif (in_array($field, $artist_columns, true)) {
          $or_condition_group->condition("a.$field", $escaped_search, 'LIKE');
        }
        elseif (in_array($field, $object_columns, true)) {
          $or_condition_group->condition("o.$field", $escaped_search, 'LIKE');
        }
        else {
          // Optional: Log or ignore unknown fields
          \Drupal::logger('collector_systems')->warning("Unknown search field: @field", ['@field' => $field]);
        }
      }

      $query->condition($or_condition_group);
    }

    $query->orderBy($sortParam, $sortOrder);
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
      $query = Database::getConnection()->select($object_table, 'o');
      $query->fields('o');
      $query->condition('o.Latitude', NULL, 'IS NOT NULL');
      $query->condition('o.Latitude', '', '<>');
      $query->condition('o.Longitude', NULL, 'IS NOT NULL');
      $query->condition('o.Longitude', '', '<>');
      $result = $query->execute();
      $object_details_for_map = $result->fetchAllAssoc('ObjectId');
      //start azure map
      $locations = [];
      foreach ($object_details_for_map as $object) {
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
              if(isset($object->$customized_field)){
                // Check if the field exists in the object
                $locations_data['data_selected_fields'][$customized_field] = $object->$customized_field;
              }
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

    $objects_service = \Drupal::service('collector_systems.objects_service');
    $object_list_sortable_fields = $objects_service->getObjectListSortableFields();

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
      '#objects_list_sortable_fields' => $object_list_sortable_fields,
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


  /**
  * Get comma separated field names for search.
  * @return string
  */
  public function getCommaSeparatedFieldsForSearch(){
    $db = \Drupal::database();

    $tblnm = "collector_systems_clsobjects_fields";
    $settblnm = $tblnm;

    $query = $db->select($settblnm, 'c')
      ->fields('c', ['fieldname']);
      // ->condition('fieldtype', 'ObjectList');
    $result = $query->execute()->fetchAllAssoc('fieldname');

    $values = implode(',', array_keys($result));

    return $values;

  }

  /**
  * Get comma separated field names for search.
  * @return array
  */
  public function getCustomizedObjectListFields(){
    $db = \Drupal::database();

    $tblnm = "collector_systems_clsobjects_fields";
    $settblnm = $tblnm;

    $query = $db->select($settblnm, 'c')
      ->fields('c', ['fieldname'])
      ->condition('fieldtype', 'ObjectList');
    $result = $query->execute()->fetchAllAssoc('fieldname');

    return array_keys($result);

  }


  /**
 * Get column names for a given database table (prefix-aware, Drupal 10+).
 *
 * @param string $table_name
 *   The base table name (without prefix), e.g. 'collector_systems_objects'.
 *
 * @return array
 *   A simple array of column names.
 */
  function cs_get_table_columns($table_name) {
    $connection = \Drupal::database();
    $prefix = $connection->getPrefix();
    $prefixed_table = $prefix . $table_name;
  
    try {
      // Use SHOW COLUMNS — faster and no INFORMATION_SCHEMA permission issues.
      $result = $connection->query("SHOW COLUMNS FROM `$prefixed_table`")->fetchAll();
      return array_map(static fn($row) => $row->Field, $result);
    }
    catch (\Exception $e) {
      \Drupal::logger('collector_systems')->error(
        'Error fetching columns for table @table: @message',
        ['@table' => $prefixed_table, '@message' => $e->getMessage()]
      );
      return [];
    }
  }


  /**
   * Get SQL ORDER BY clause based on frontend dataorderby param.
   *
   * @param string $dataorderby
   * @return array [sort_column, sort_order]
   */
  public function cs_get_sorting_params_for_objects_list($dataorderby) {
  
      $sortParam = "Title";
      $sortOrder = "DESC";
  
      // Define sorting logic
      switch ($dataorderby) {
          case "Title%20asc":
              $sortParam = "Title";
              $sortOrder = "ASC";
              break;
          case "InventoryNumber%20asc":
              $sortParam = "InventoryNumber";
              $sortOrder = "ASC";
              break;
          case "InventoryNumber%20desc":
              $sortParam = "InventoryNumber";
              $sortOrder = "DESC";
              break;
          case "ObjectDate%20asc":
              $sortParam = "ObjectDate";
              $sortOrder = "ASC";
              break;
          case "ObjectDate%20desc":
              $sortParam = "ObjectDate";
              $sortOrder = "DESC";
              break;
          case "FullCollectionName%20asc":
              $sortParam = "c.CollectionName";
              $sortOrder = "ASC";
              break;
          case "FullCollectionName%20desc":
              $sortParam = "c.CollectionName";
              $sortOrder = "DESC";
              break;
          case "ArtistName%20asc":
              // use alias 'a' for Artists table
              $sortParam = "a.ArtistName";
              $sortOrder = "ASC";
              break;
          case "ArtistName%20desc":
              // use alias 'a' for Artists table
              $sortParam = "a.ArtistName";
              $sortOrder = "DESC";
              break;
          case "AdditionalArtists%20asc":
              // For additional artists, we use the AdditionalArtistsText field which is a concatenated string of all additional artists for sorting purposes
              $sortParam = "AdditionalArtistsText";
              $sortOrder = "ASC";
              break;
          case "AdditionalArtists%20desc":
                 // For additional artists, we use the AdditionalArtistsText field which is a concatenated string of all additional artists for sorting purposes
              $sortParam = "AdditionalArtistsText";
              $sortOrder = "DESC";
              break;
          case "AdditionalArtistMaker%20asc":
              // For additional artist makers, we use the AdditionalArtistMakersText field which is a concatenated string of all additional artist makers for sorting purposes
              $sortParam = "AdditionalArtistMakersText";
              $sortOrder = "ASC";
              break;
          case "AdditionalArtistMaker%20desc":
              // For additional artist makers, we use the AdditionalArtistMakersText field which is a concatenated string of all additional artist makers for sorting purposes
              $sortParam = "AdditionalArtistMakersText";
              $sortOrder = "DESC";
              break;
          default:
              $decoded = rawurldecode($dataorderby);
              [$field, $order] = explode(' ', $decoded, 2);
              $sortOrder = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';
              // Assume field exists on objects table
              $sortParam = 'o.' . $field;
      }
  
    return [$sortParam, $sortOrder];
  }
}
