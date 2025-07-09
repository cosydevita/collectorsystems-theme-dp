<?php

namespace Drupal\collector_systems\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;


class PageTemplatesController extends ControllerBase
{
  /**
   * A request stack symfony instance.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  public function __construct(RequestStack $request_stack, ConfigFactoryInterface $config_factory) {
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('config.factory')
    );
  }

  public function ObjectDetailPage(){
    $request = $this->requestStack->getCurrentRequest();
    $artObjID=$_REQUEST['dataId'];
    $sortBy = $request->query->get('sortBy', 'Title asc');
    $qSearch=isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";
    $requested_pageNo=isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;

    // Fetch Objects Data from database
    $object_table = 'CSObjects';
    $count = \Drupal::database()->select($object_table)
      ->countQuery()
      ->execute()
      ->fetchField();

    // Collection Table
    $collection_table = 'Collections';

    // Fetch object details from database
    $query = \Drupal::database()->select($object_table, 'o');
    $query ->fields('o');

    if ($sortBy === 'Title desc' || $sortBy === 'Title asc') {
      $query->orderBy('Title', ($sortBy === 'Title desc') ? 'DESC' : 'ASC');
    }
    elseif ($sortBy === 'InventoryNumber asc' || $sortBy === 'InventoryNumber desc') {
      $query->orderBy('InventoryNumber', ($sortBy === 'InventoryNumber desc') ? 'DESC' : 'ASC');

    }
    elseif ($sortBy === 'ObjectDate desc' || $sortBy === 'ObjectDate asc') {
      $query->orderBy('ObjectDate', ($sortBy === 'ObjectDate desc') ? 'DESC' : 'ASC');
    }
    elseif ($sortBy === 'Collection/CollectionName asc' || $sortBy === 'Collection/CollectionName desc') {
      $query->join($collection_table, 'c', 'o.CollectionId = c.CollectionId');
      $query->fields('c', ['CollectionName']);
      $query->orderBy('c.CollectionName', ($sortBy === 'Collection/CollectionName desc') ? 'DESC' : 'ASC');
    }

    $query->condition('ObjectId', $artObjID);

    $object_details = $query->execute()->fetchAllAssoc('ObjectId');
    $object_details = $object_details[$artObjID];

    

    $module_path = \Drupal::service('extension.list.module')->getPath('collector_systems');
    $enable_maps = \Drupal::config('collector_systems.settings')->get('enable_maps');
    $locations = [];
    if($enable_maps){
      //start azure map
      $customized_fields = $this->getCommaSeperatedFieldsForDetailPage();
      $customized_fields_array = explode(',', $customized_fields);
      if ($object_details) {
        $Latitude = $object_details->Latitude;
        $Longitude = $object_details->Longitude;
        $AddressName = $object_details->AddressName;
        $main_image_attachment = $object_details->main_image_attachment;
        $main_image_path = $object_details->main_image_path;
        $object_id = $object_details->ObjectId;
        $locations_data =  [
          "latitude" => $Latitude,
          "longitude" => $Longitude,
          "AddressName" => $AddressName,
          "main_image_attachment" => $main_image_attachment ? base64_encode($main_image_attachment) : '',
          "main_image_path" => $main_image_path,
          "object_detail_url" => '/artobject-detail?dataId='. $object_id,

        ];
        if($Latitude && $Longitude){
          //only display image, title and Inventory in pin-popup  Number for the detail page
          $locations_data['data_selected_fields']['Title'] = $object_details->Title;
          $locations_data['data_selected_fields']['InventoryNumber'] = $object_details->InventoryNumber;
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


    $database = Database::getConnection();

    // Assuming $thumbImage_table is the name of your custom table.
    $thumbImage_table = $database->prefixTables('ThumbImages');

    // Fetching multiple rows based on a condition.
    $query = $database->select($thumbImage_table, 'ti')
      ->fields('ti')
      ->condition('ObjectId', $artObjID)
      ->orderBy('object_image_path', 'DESC')
      ->execute();
    $thumbDetails = $query->fetchAllAssoc('ID');

    //FOR NEXT AND PREVIOUS
    $customized_fields = $this->getCommaSeperatedFieldsForDetailPage();

    $customized_fields_array = explode(',', $customized_fields);


    $row_number=-1;

    $getObjectIdsForPrevNext = $this->getObjectIdsForPrevNext($sortBy);

    if($getObjectIdsForPrevNext)
    {
        foreach($getObjectIdsForPrevNext as $key=>$object)
        {
            // if($object['ObjectId'] == $artObjID)
            if ($object->ObjectId == $artObjID)
            {
              // print_r($object_details);

                $row_number = $key;
                break;
            }
        }

    }
    $row_before = (int)$row_number-1;
    $row_after = (int)$row_number+1;


    // filter images using keywords
    $filtered_keywords = get_filtered_keywords();
    if($filtered_keywords){
      foreach($thumbDetails as  $thumbDetailKey => $thumbDetail){
        $keywords = json_decode($thumbDetail->keywords);
        $MainImageAttachmentId = $thumbDetail->MainImageAttachmentId;
        $AttachmentId = $thumbDetail->AttachmentId;

        // Check if any of the $keywords exists in $filtered_keywords
        $foundKeyword = false;
        foreach($keywords as $keyword) {
            if (in_array($keyword, $filtered_keywords)) {
                $foundKeyword = true;
              break;
            }
        }
        if (!$foundKeyword) {
          //do not remove if the attachmentId is MainImageAttachmentId
          if($MainImageAttachmentId != $AttachmentId){
            unset($thumbDetails[$thumbDetailKey]);

          }
        }

      }
    }

    $thumbDetails =  array_values($thumbDetails);

    //move the main image to the first index
    foreach($thumbDetails as $thumbDetailKey => $thumbDetail){
      if($thumbDetail->MainImageAttachmentId == $thumbDetail->AttachmentId ){
        $this->moveElementToFirstIndex($thumbDetails, $thumbDetailKey);
      }
    }

    $enable_zoom =  \Drupal::config('collector_systems.settings')->get('enable_zoom');

    $base_url_with_scheme = \Drupal::request()->getSchemeAndHttpHost();
    $build = [
      '#theme' => 'artobject-detail-page',
      '#thumbDetails' => $thumbDetails,
      '#customized_fields_array' => $customized_fields_array,
      '#object_details' => $object_details,
      '#object_ids_for_prev_next' => $getObjectIdsForPrevNext,
      '#row_number' => $row_number,
      '#row_before' => $row_before,
      '#row_after' => $row_after,
      '#count' => $count,
      '#sortBy' => $sortBy,
      '#qSearch' => $qSearch,
      '#requested_pageNo' => $requested_pageNo,
      '#site_url' => $base_url_with_scheme,
      '#module_path' => $module_path,
      '#enable_maps' => $enable_maps,
      '#enable_zoom' => $enable_zoom,
      '#locations' => $locations,
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
    $build['#attached']['library'][] = 'collector_systems/jquery_ui';
    $build['#attached']['library'][] = 'collector_systems/artobject_detail_page';
    return $build;

  }

  public function getObjectIdsForPrevNext($sortBy){
    $object_table = 'CSObjects';
    $collection_table = 'Collections';
     
    // Fetch object details from database
    $query = \Drupal::database()->select($object_table, 'o');
    $query ->fields('o', ['ObjectId']);

    if ($sortBy === 'Title desc' || $sortBy === 'Title asc') {
      $query->orderBy('Title', ($sortBy === 'Title desc') ? 'DESC' : 'ASC');
    }
    elseif ($sortBy === 'InventoryNumber asc' || $sortBy === 'InventoryNumber desc') {
      $query->orderBy('InventoryNumber', ($sortBy === 'InventoryNumber desc') ? 'DESC' : 'ASC');

    }
    elseif ($sortBy === 'ObjectDate desc' || $sortBy === 'ObjectDate asc') {
      $query->orderBy('ObjectDate', ($sortBy === 'ObjectDate desc') ? 'DESC' : 'ASC');
    }
    elseif ($sortBy === 'Collection/CollectionName asc' || $sortBy === 'Collection/CollectionName desc') {
      $query->join($collection_table, 'c', 'o.CollectionId = c.CollectionId');
      $query->fields('c', ['CollectionName']);
      $query->orderBy('c.CollectionName', ($sortBy === 'Collection/CollectionName desc') ? 'DESC' : 'ASC');
    }

    $object_ids = $query->execute()->fetchAll(); 

    return $object_ids;

  }

  public function ArtistDetailPage(){
    $artistId=$_REQUEST['dataId'];
    $listPageSize =  \Drupal::config('collector_systems.settings')->get('items_per_page');
    $groupLevelTopCount = isset($listPageSize) ? $listPageSize : 9;
    $groupLevelSkipCount =   0;
    $ajaxfor=   "artist-detail";
    $current_page=   "artist-detail";

    $groupLevelOrderBy=   isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "Title%20desc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";
    $requested_pageNo = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $groupLevelPageNo = isset($_REQUEST['groupLevelPageNo']) ? intval($_REQUEST['groupLevelPageNo']) : 1;
    $groupLevelSkipCount = ($groupLevelPageNo - 1) * $groupLevelTopCount;

    $nxshowrec=   isset($listPageSize) ? $listPageSize : 9;
    $nxshskip =   $groupLevelSkipCount;
    $loadsec=1;

    $customized_fields = $this->getCommaSeperatedFieldsForListPageObject();
    if($customized_fields){
      $customized_fields_array = explode(',', $customized_fields);
    }else{
      $customized_fields_array = [];
    }

    // Fetch artist details from the database
    $connection = Database::getConnection();
    $artist_table = $connection->prefixTables('Artists');
    $fetch_artist_details = $connection->select($artist_table, 'a')
      ->fields('a', ['ArtistId', 'ArtistName', 'ArtistFirst', 'ArtistLast', 'ArtistYears', 'ArtistNationality', 'ArtistLocale', 'ArtistBio'])
      ->condition('ArtistId', $artistId)
      ->execute()
      ->fetchAssoc();
    $artist_details = $fetch_artist_details;

    // Construct the WHERE clause for LIKE condition on multiple fields
    $where_conditions = new Condition('OR');
    foreach ($customized_fields_array as $field) {
      $where_conditions->condition($field, '%' . $qSearch . '%', 'LIKE');
    }
    // Fetch objects where ArtistId
    $object_table = $connection->prefixTables('CSObjects');
    $query_object_details = $connection->select($object_table, 'o')
      ->fields('o') // Specify the fields you want to select
      ->condition('o.ArtistId', $artistId);
    $query_object_details->orderBy('Title', 'ASC');


    if ($qSearch !== NULL && count($customized_fields_array)>0) {
      $query_object_details->condition($where_conditions);
    }
    $query_object_details->range($groupLevelSkipCount, $nxshowrec);
    $object_details = $query_object_details->execute()->fetchAllAssoc('ObjectId');

    // Count
    $count_object = $connection->select($object_table, 'o')
      ->condition('o.ArtistId', $artistId)
      ->countQuery()
      ->execute()
      ->fetchField();

    $obj_count = $count_object;

    $collector_systems_module_path = \Drupal::service('extension.path.resolver')->getPath('module', 'collector_systems');

    $module_path = \Drupal::service('extension.list.module')->getPath('collector_systems');

    $enable_maps = \Drupal::config('collector_systems.settings')->get('enable_maps');
    if($enable_maps){
      $query_map_object_details = $query_object_details->range();
      $result = $query_map_object_details->execute();
      $object_details_without_range =  $result->fetchAllAssoc('ObjectId');
      //start azure map
      $locations = [];
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
          foreach($customized_fields_array as $customized_field){
            $locations_data['data_selected_fields'][$customized_field] = $object->$customized_field;
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
      '#theme' => 'artist-detail-page',
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#obj_count' => $obj_count,
      '#artistId' => $artistId,
      '#groupLevelOrderBy' => $groupLevelOrderBy,
      '#groupLevelPageNo' => $groupLevelPageNo,
      '#artist_details' => $artist_details,
      '#qSearch' => $qSearch,
      '#loadsec' => $loadsec,
      '#object_details' => $object_details,
      '#ajaxfor' => $ajaxfor,
      '#listPageSize' => $listPageSize,
      '#collector_systems_module_path' => $collector_systems_module_path,
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

  public function ExhibitionDetailPage(){
    $exhibitionID=$_REQUEST['dataId'];
    $listPageSize =  \Drupal::config('collector_systems.settings')->get('items_per_page');
    $groupLevelTopCount = isset($listPageSize) ? $listPageSize : 9;
    $groupLevelSkipCount =   0;
    $ajaxfor=   "exhibition-detail";
    $current_page=   "exhibition-detail";

    $groupLevelOrderBy=   isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "Title%20desc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";
    $requested_pageNo = isset($_REQUEST['pageNo']) ? absint($_REQUEST['pageNo']) : 1;
    $groupLevelPageNo = isset($_REQUEST['groupLevelPageNo']) ? absint($_REQUEST['groupLevelPageNo']) : 1;
    $groupLevelSkipCount = ($groupLevelPageNo - 1) * $groupLevelTopCount;


    $nxshowrec=   isset($listPageSize) ? $listPageSize : 9;
    $nxshskip =   $groupLevelSkipCount;

    $loadsec=1;

    //Fetch Exhibition Data from database
    $database = \Drupal::database();
    // Define the table name using Drupal's table() method.
    $exhibition_table = 'Exhibitions';

    // Build the database query.
    $query = $database->select($exhibition_table);
    $query->fields($exhibition_table);
    $query->condition('ExhibitionId', $exhibitionID);
    $result = $query->execute();

    // Fetch the result as an associative array.
    $exhibition_details = $result->fetchAssoc();


    //Fetch Objects Where ExhibitionId
    $exhibitionObj_table = 'ExhibitionObjects';
    $object_table = 'CSObjects';

    $query_exhibition_objects = \Drupal::database()->select($exhibitionObj_table, 'eo');
    $query_exhibition_objects->fields('eo');
    $query_exhibition_objects->join($object_table, 'co', 'eo.ObjectId = co.ObjectId');
    $query_exhibition_objects->fields('co');
    $query_exhibition_objects->condition('eo.ExhibitionId', $exhibitionID);
    $query_exhibition_objects->range($nxshskip, $nxshowrec);
    $query_exhibition_objects->orderBy('Title', 'ASC');

    $result = $query_exhibition_objects->execute();

    $object_details = $result->fetchAllAssoc('ObjectId');

    //Count
    $count_object = $database->query("SELECT COUNT(*) FROM {" . $exhibitionObj_table . "} WHERE ExhibitionId = :exhibition_id", [
      ':exhibition_id' => $exhibitionID,
    ]);
    $obj_count = $count_object->fetchField();

    $module_path = \Drupal::service('extension.list.module')->getPath('collector_systems');
    $enable_maps = \Drupal::config('collector_systems.settings')->get('enable_maps');
    if($enable_maps){
      //start azure map
      $query_map_exhibition_objects = $query_exhibition_objects->range();
      $result = $query_map_exhibition_objects->execute();
      $object_details_without_range =  $result->fetchAllAssoc('ObjectId');

      $customized_fields = $this->getCommaSeperatedFieldsForListPageObject();
      if($customized_fields){
        $customized_fields_array = explode(',', $customized_fields);
      }else{
        $customized_fields_array = [];
      }

      $locations = [];
      
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
          foreach($customized_fields_array as $customized_field){
            $locations_data['data_selected_fields'][$customized_field] = $object->$customized_field;
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
      '#theme' => 'exhibition-detail-page',
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#obj_count' => $obj_count,
      '#groupLevelOrderBy' => $groupLevelOrderBy,
      '#groupLevelPageNo' => $groupLevelPageNo,
      '#qSearch' => $qSearch,
      '#loadsec' => $loadsec,
      '#object_details' => $object_details,
      '#exhibition_details' => $exhibition_details,
      '#ajaxfor' => $ajaxfor,
      '#exhibitionID' => $exhibitionID,
      '#listPageSize' => $listPageSize,
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

  public function GroupDetailPage(){
    $groupID=$_REQUEST['dataId'];
    $listPageSize =  \Drupal::config('collector_systems.settings')->get('items_per_page');
    $groupLevelTopCount = isset($listPageSize) ? $listPageSize : 9;
    $groupLevelSkipCount =   0;
    $ajaxfor=   "group-detail";
    $current_page=   "group-detail";

    $groupLevelOrderBy=   isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "Object/Title%20desc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";
    $requested_pageNo = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $groupLevelPageNo = isset($_REQUEST['groupLevelPageNo']) ? intval($_REQUEST['groupLevelPageNo']) : 1;
    $groupLevelSkipCount = ($groupLevelPageNo - 1) * $groupLevelTopCount;



    $nxshowrec=   isset($listPageSize) ? $listPageSize : 9;
    $nxshskip =   $groupLevelSkipCount;

    $loadsec=1;

    $database = \Drupal::database();

    //Fetch Group Details From Database

    $group_table = 'Groups';
    $query = $database->select($group_table, 'g')
    ->fields('g', ['GroupId', 'GroupDescription', 'GroupMemo'])
    ->condition('GroupId', $groupID)
    ->range(0, 1); // Assuming you only expect one result.
    $group_details = $query->execute()->fetchAssoc();


    //Fetch Objects Where GroupId
    $groupObj_table = "GroupObjects";
    $query = $database->select($groupObj_table, 'go')
    ->fields('go')
    ->condition('GroupId', $groupID);
    $object_details = $query->execute()->fetchAssoc();


    $object_table = "CSObjects";
    $query_group_objects = $database->select($groupObj_table, 'eo');
    $query_group_objects->fields('eo');
    $query_group_objects->condition('eo.GroupId', $groupID);
    $query_group_objects->join($object_table, 'co', 'eo.ObjectId = co.ObjectId');
    $query_group_objects->fields('co');
    $query_group_objects->range($nxshskip, $nxshowrec);
    $query_group_objects->orderBy('Title', 'ASC');
    $group_object_details = $query_group_objects->execute()->fetchAllAssoc('ObjectId');

    $query_count = $database->select($groupObj_table, 'go')
      ->condition('GroupId', $groupID)
      ->countQuery();
    $obj_count = $query_count->execute()->fetchField();

    $module_path = \Drupal::service('extension.list.module')->getPath('collector_systems');
    $enable_maps = \Drupal::config('collector_systems.settings')->get('enable_maps');
    if($enable_maps){
      //Fetch Group Objects without range for map to show all objects in the group.
      $object_table = "CSObjects";
      $query_map_group_objects = $query_group_objects->range();
      $result = $query_map_group_objects->execute();
      $group_object_details_without_range =  $result->fetchAllAssoc('ObjectId');
  
      //start azure map
      $customized_fields = $this->getCommaSeperatedFieldsForListPageObject();
      if($customized_fields){
        $customized_fields_array = explode(',', $customized_fields);
      }else{
        $customized_fields_array = [];
      }

      $locations = [];
      foreach ($group_object_details_without_range as $object) {
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
          "main_image_attachment" => $main_image_attachment ? ($main_image_attachment) : '',
          "main_image_path" => $main_image_path,
          "object_detail_url" => '/artobject-detail?dataId='. $object_id,

        ];
        if($Latitude && $Longitude){
          foreach($customized_fields_array as $customized_field){
              $locations_data['data_selected_fields'][$customized_field] = $object->$customized_field;
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
      '#theme' => 'group-detail-page',
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#obj_count' => $obj_count,
      '#groupLevelOrderBy' => $groupLevelOrderBy,
      '#groupLevelPageNo' => $groupLevelPageNo,
      '#qSearch' => $qSearch,
      '#loadsec' => $loadsec,
      '#ajaxfor' => $ajaxfor,
      '#object_details' => $object_details,
      '#group_details' => $group_details,
      '#group_object_details' => $group_object_details,
      '#listPageSize' => $listPageSize,
      '#groupID' => $groupID,
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


  public function CollectionDetailPage(){

    $collectionID=$_REQUEST['dataId'];
    $listPageSize =  \Drupal::config('collector_systems.settings')->get('items_per_page');
    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $shskip =   0;
    $groupLevelTopCount = isset($listPageSize) ? $listPageSize : 9;
    $groupLevelSkipCount =   0;
    $ajaxfor=   "collection-detail";
    $current_page=   "collection-detail";

    $groupLevelOrderBy=   isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "Title%20desc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";
    $requested_pageNo = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $groupLevelPageNo = isset($_REQUEST['groupLevelPageNo']) ? intval($_REQUEST['groupLevelPageNo']) : 1;
    $groupLevelSkipCount = ($groupLevelPageNo - 1) * $groupLevelTopCount;

    $nxshowrec=   isset($listPageSize) ? $listPageSize : 9;
    $nxshskip =   $groupLevelSkipCount;
    $loadsec=1;

    //Fetch Collection Detail From Database
    $database = \Drupal::database();
    $collection_table = 'Collections';
    $query = $database->select($collection_table, 'c')
    ->fields('c', ['CollectionId', 'FullCollectionName'])
    ->condition('c.CollectionId', $collectionID)
    ->range(0, 1); // Assuming you only expect one result.
    $collection_details = $query->execute()->fetchAssoc();

    // collection object details.
    $object_table = 'CSObjects';
    $connection = \Drupal::database();
    $query_collection_objects = $connection->select('CSObjects', 'o');
    $query_collection_objects->innerJoin('Collections', 'c', 'o.CollectionId = c.CollectionId');
    $query_collection_objects->innerJoin('Collections', 'c_target', 'c_target.CollectionId = '.$collectionID);

    $query_collection_objects->fields('o');
    $query_collection_objects->fields('c');

    // WHERE (o.CollectionId = :group_id OR o.ObjectId BETWEEN c_target.LeftExtent AND c_target.RightExtent)
    $or_condition = $query_collection_objects->orConditionGroup()
      ->condition('o.CollectionId', $collectionID)
      ->where('o.ObjectId BETWEEN c_target.LeftExtent AND c_target.RightExtent');

    $query_collection_objects->condition($or_condition);

    $query_objects_total_count = $query_collection_objects->countQuery();

    // Add limits.
    $query_collection_objects->range($shskip, $showrec);
    $query_collection_objects->orderBy('Title', 'ASC');

    $object_details = $query_collection_objects->execute()->fetchAllAssoc('ObjectId');
    //end collection object details.

    //Count collection objects.
    $query_count = $query_objects_total_count->countQuery();
    $obj_count = $query_count->execute()->fetchField();
    

    $module_path = \Drupal::service('extension.list.module')->getPath('collector_systems');

    $enable_maps = \Drupal::config('collector_systems.settings')->get('enable_maps');
    if($enable_maps){
      $query_map_collection_objects = $query_collection_objects->range();
      $result = $query_map_collection_objects->execute();
      $object_details_without_range =  $result->fetchAllAssoc('ObjectId');
      //start azure map
      $customized_fields = $this->getCommaSeperatedFieldsForListPageObject();
      if($customized_fields){
        $customized_fields_array = explode(',', $customized_fields);
      }else{
        $customized_fields_array = [];
      }

      $locations = [];
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
          "main_image_attachment" => $main_image_attachment ? base64_encode($main_image_attachment): '',
          "main_image_path" => $main_image_path,
          "object_detail_url" => '/artobject-detail?dataId='. $object_id,

        ];
        if($Latitude && $Longitude){
          foreach($customized_fields_array as $customized_field){
            $locations_data['data_selected_fields'][$customized_field] = $object->$customized_field;
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
      '#theme' => 'collection-detail-page',
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#obj_count' => $obj_count,
      '#groupLevelOrderBy' => $groupLevelOrderBy,
      '#groupLevelPageNo' => $groupLevelPageNo,
      '#qSearch' => $qSearch,
      '#ajaxfor' => $ajaxfor,
      '#loadsec' => $loadsec,
      '#object_details' => $object_details,
      '#collection_details' => $collection_details,
      '#collectionID' => $collectionID,
      '#listPageSize' => $listPageSize,
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

  public function drupal_selected($value, $current_value, $echo = true) {
    $selected = $value == $current_value ? 'selected="selected"' : '';

    if ($echo) {
        echo $selected;
    }

    return $selected;
  }

  public function getCommaSeperatedFieldsForDetailPage(){

    $db = \Drupal::database();

    $tblnm = "clsobjects_fields";
    $settblnm = $tblnm;

    $query = $db->select($settblnm, 'c')
      ->fields('c', ['fieldname'])
      ->condition('fieldtype', 'ObjectDetail');

    $result = $query->execute()->fetchAllAssoc('fieldname');

    $values = implode(',', array_keys($result));

    return $values;
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

  public function getCommaSeperatedFieldsForListPageObject(){
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

  function moveElementToFirstIndex(&$array, $index) {
    if (isset($array[$index])) {
        $element = $array[$index];
        unset($array[$index]);
        array_unshift($array, $element);
    }
  }
}
