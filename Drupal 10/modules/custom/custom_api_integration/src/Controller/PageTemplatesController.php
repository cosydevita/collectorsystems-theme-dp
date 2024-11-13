<?php

namespace Drupal\custom_api_integration\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\Condition;

class PageTemplatesController extends ControllerBase
{
  public function ObjectsListPage(){

    if(!$this->is_CS_tables_exists()){
      $build = [
        '#theme' => 'objects-list-page',
        '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.
      ];
      return $build;
    }

    $listPageSize =  \Drupal::config('custom_api_integration.settings')->get('items_per_page');

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

    $customized_fields_list_and_detail_page = $this->getCommaSeparatedFieldsForListAndDetailPage();
    $customized_fields_list_and_detail_page_array = null;
    if($customized_fields_list_and_detail_page){
      $customized_fields_list_and_detail_page_array = explode(',', $customized_fields_list_and_detail_page);
    }

    // Count Total Objects
    $object_table = 'CSObjects';

    // Collection Table
    $collection_table = 'Collections';

    // Fetch object details from the database
    $query = Database::getConnection()->select($object_table, 'o');
    if($customized_fields){
      $or_condition_group = $query->orConditionGroup();

      foreach($customized_fields_list_and_detail_page_array as $customized_field){
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

    $count_query = $query->countQuery();
    $total_results = $count_query->execute()->fetchField();

    $query_without_range = $query;
    $query->range($shskip, $showrec);

    $result = $query->execute();
    $object_details = $result->fetchAllAssoc('ObjectId'); // Assuming 'ObjectId' is the primary key field
    $module_path = \Drupal::service('extension.list.module')->getPath('custom_api_integration');


    //start azure map
    $enable_maps = \Drupal::config('custom_api_integration.settings')->get('enable_maps');
    if($enable_maps){
      $locations = [];
      $query_without_range = $query->range(); //to include all results without range
      $result = $query_without_range->execute();
      $object_details_without_range =  $result->fetchAllAssoc('ObjectId');
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
          "main_image_attachment" => base64_encode($main_image_attachment),
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

      $state = \Drupal::state();
      $subscription_key = $state->get('collector_systems_azure_map.subscription_key');

      $js_settings = [
        'locations' => $locations,
        'subscription_key' => $subscription_key,
        'module_path' => $module_path
      ];
    }

    //end azure map

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


    if($enable_maps){
      foreach ($js_settings as $key => $value) {
        $build['#attached']['drupalSettings']['azure_map'][$key] = $value;
      }
      $build['#attached']['library'][] = 'custom_api_integration/azure_map';
      $build['#attached']['library'][] = 'custom_api_integration/custom_tabs';
    }

    return $build;

  }

  public function ArtistsListPage(){
    if(!$this->is_CS_tables_exists()){
      $build = [
        '#theme' => 'artists-list-page',
        '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.
      ];
      return $build;
    }

    $listPageSize =  \Drupal::config('custom_api_integration.settings')->get('items_per_page');
    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $shskip = 0;
    $ajaxfor = "artist";
    $current_page = "artists";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "ArtistName%20asc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";

    $requested_page = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $shskip = ($requested_page - 1) * $showrec;


    // Fetch Count From the Database
    $artist_table = 'Artists'; // Replace with your table name
    $query = Database::getConnection()->select($artist_table, 'a');
    $query->addExpression('COUNT(*)');
    $count = $query->execute()->fetchField();

    // Fetch Artists record from database
    $query = Database::getConnection()->select($artist_table, 'a');
    $query->fields('a');
    $query->range($shskip, $showrec);

    if ($dataorderby === "ArtistName%20desc" && $qSearch === NULL) {
        $query->orderBy('ArtistName', 'DESC');
    } elseif ($dataorderby === "ArtistName%20desc" && $qSearch !== NULL) {
        $query->condition('ArtistName', '%' . Database::getConnection()->escapeLike($qSearch) . '%', 'LIKE');
        $query->orderBy('ArtistName', 'DESC');
    } elseif ($dataorderby === "ArtistName%20asc" && $qSearch === NULL) {
        $query->orderBy('ArtistName', 'ASC');
    } elseif ($dataorderby === "ArtistName%20asc" && $qSearch !== NULL) {
        $query->condition('ArtistName', '%' . Database::getConnection()->escapeLike($qSearch) . '%', 'LIKE');
        $query->orderBy('ArtistName', 'ASC');
    }

    $AllArtists = $query->execute()->fetchAll();

    $nxshowrec = isset($listPageSize) ? $listPageSize : 9;
    $nxshskip = $shskip;

    $loadsec = 1;
    $showImagesOnListPages =  \Drupal::config('custom_api_integration.settings')->get('show_images_on_list_pages');

    $build = [
      '#theme' => 'artists-list-page',
      '#AllArtists' => $AllArtists,
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#count' => $count,
      '#dataorderby' => $dataorderby,
      '#current_page' => $current_page,
      '#qSearch' => $qSearch,
      '#loadsec' => $loadsec,
      '#requested_page' => $requested_page,
      '#showImagesOnListPages' => $showImagesOnListPages,
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];


    return $build;

  }

  public function ExhibitionsListPage(){
    if(!$this->is_CS_tables_exists()){
      $build = [
        '#theme' => 'exhibitions-list-page',
        '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.
      ];
      return $build;
    }


    $listPageSize =  \Drupal::config('custom_api_integration.settings')->get('items_per_page');
    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $shskip =   0;
    $ajaxfor=   "listexhibition";
    $current_page=   "exhibitions";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "ExhibitionSubject%20asc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";

    $requested_pageNo = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $shskip = ($requested_pageNo - 1) * $showrec;
    $nxshowrec=   isset($listPageSize) ? $listPageSize : 9;
    $nxshskip =   $shskip;
    $loadsec=1;

    $exhibition_table = "Exhibitions";

    // Fetch Count From the Database
    $query = Database::getConnection()->select($exhibition_table, 'a');
    $query->addExpression('COUNT(*)');
    $count = $query->execute()->fetchField();

    $database = Database::getConnection();

    //Fetch Exhibition Data from Database
    $query = $database->select($exhibition_table, 'et');
    $query->fields('et');


    // Use an alternative method to specify the sorting order.
    $query->orderBy('ExhibitionSubject', ($dataorderby === 'ExhibitionSubject%20desc') ? 'DESC' : 'ASC');
    $query->orderBy('ExhibitionDate', ($dataorderby === 'ExhibitionStartDate%20desc' || $dataorderby === 'ExhibitionEndDate%20desc') ? 'DESC' : 'ASC');

    // Additional steps as needed for your specific use case.
    $query->range(0, $showrec);
    // Execute the query.
    $result = $query->execute();

    // Use an alternative method to limit the number of records, e.g., range.

      if ($qSearch !== NULL) {
        $query->condition('ExhibitionSubject', '%' . $database->escapeLike($qSearch) . '%', 'LIKE');
      }

      $query->range($shskip, $showrec);

      $result = $query->execute();

      // Fetch the results as objects.
      $all_exhibitions = $result->fetchAll(\PDO::FETCH_OBJ);


    $showImagesOnListPages =  \Drupal::config('custom_api_integration.settings')->get('show_images_on_list_pages');

    $build = [
      '#theme' => 'exhibitions-list-page',
      '#all_exhibitions' => $all_exhibitions,
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#count' => $count,
      '#dataorderby' => $dataorderby,
      '#current_page' => $current_page,
      '#qSearch' => $qSearch,
      '#loadsec' => $loadsec,
      '#requested_pageNo' => $requested_pageNo,
      '#showImagesOnListPages' => $showImagesOnListPages,
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];


    return $build;

  }

  public function GroupsListPage(){

    if(!$this->is_CS_tables_exists()){
      $build = [
        '#theme' => 'groups-list-page',
        '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.
      ];
      return $build;
    }

    $listPageSize =  \Drupal::config('custom_api_integration.settings')->get('items_per_page');
    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $shskip =   0;
    $ajaxfor=   "listgroup";
    $current_page=   "groups";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "GroupDescription%20asc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";

    $requested_pageNo = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $shskip = ($requested_pageNo - 1) * $showrec;

    // $groups=getallGroups($subsId,$subsKey,$subAcntId,$showrec,$shskip,$dataorderby,$qSearch);
    // $tot=listGroupTotalRecords($subsId,$subsKey,$subAcntId,$qSearch);    //Total Records

    $nxshowrec=   isset($listPageSize) ? $listPageSize : 9;
    $nxshskip =   $shskip;
    $loadsec=1;

    //Fetch Group's Data From Database
    $group_table = "Groups";
    $database = \Drupal::database();

    $count = $database->select($group_table)
    ->countQuery()
    ->execute()
    ->fetchField();

    if($dataorderby === "GroupDescription%20desc" && $qSearch === NULL)
    {
        // $fetch_groups_record = $wpdb->prepare("SELECT * FROM $group_table ORDER BY GroupDescription DESC LIMIT %d, %d", $shskip, $showrec);
        $query = $database->select($group_table, 'g')
          ->fields('g')
          ->orderBy('GroupDescription', 'DESC')
          ->range($shskip, $showrec);

        $fetch_groups_record = $query->execute()->fetchAll();
    }
    else if($dataorderby === "GroupDescription%20desc" && $qSearch !== NULL)
    {
        // $fetch_groups_record = $wpdb->prepare("SELECT * FROM $group_table WHERE GroupDescription LIKE %s ORDER BY GroupDescription DESC LIMIT %d, %d",'%' . $wpdb->esc_like($qSearch) . '%', $shskip, $showrec);
        $query = $database->select($group_table, 'g')
          ->fields('g')
          ->condition('GroupDescription', '%' . $database->escapeLike($qSearch) . '%', 'LIKE')
          ->orderBy('GroupDescription', 'DESC')
          ->range($shskip, $showrec);

        $fetch_groups_record = $query->execute()->fetchAll();
    }
    else if($dataorderby === "GroupDescription%20asc" && $qSearch === NULL)
    {
      $query = $database->select($group_table, 'g')
      ->fields('g')
      ->condition('GroupDescription', '%' . $database->escapeLike($qSearch) . '%', 'LIKE')
      ->condition('GroupId', '%' . $database->escapeLike($qSearch) . '%', 'LIKE')
      ->condition('GroupMemo', '%' . $database->escapeLike($qSearch) . '%', 'LIKE')
      ->orderBy('GroupDescription', 'ASC')
      ->range($shskip, $showrec);

      $fetch_groups_record = $query->execute()->fetchAll();
    }
    else if($dataorderby === "GroupDescription%20asc" && $qSearch !== NULL)
    {
        // $fetch_groups_record = $wpdb->prepare("SELECT * FROM $group_table WHERE GroupDescription LIKE %s ORDER BY GroupDescription ASC LIMIT %d, %d",'%' . $wpdb->esc_like($qSearch) . '%', $shskip, $showrec);
        $query = $database->select($group_table, 'g')
          ->fields('g')
          ->condition('GroupDescription', '%' . $database->escapeLike($qSearch) . '%', 'LIKE')
          ->orderBy('GroupDescription', 'ASC')
          ->range($shskip, $showrec);

        $fetch_groups_record = $query->execute()->fetchAll();

    }

    $all_groups = $fetch_groups_record;

    $showImagesOnListPages =  \Drupal::config('custom_api_integration.settings')->get('show_images_on_list_pages');
    $build = [
      '#theme' => 'groups-list-page',
      '#all_groups' => $all_groups,
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#count' => $count,
      '#dataorderby' => $dataorderby,
      '#current_page' => $current_page,
      '#qSearch' => $qSearch,
      '#loadsec' => $loadsec,
      '#requested_pageNo' => $requested_pageNo,
      '#showImagesOnListPages' => $showImagesOnListPages,
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];


    return $build;

  }

  public function CollectionsListPage(){

    if(!$this->is_CS_tables_exists()){
      $build = [
        '#theme' => 'collections-list-page',
        '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.
      ];
      return $build;
    }


    $listPageSize =  \Drupal::config('custom_api_integration.settings')->get('items_per_page');
    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $shskip =   0;
    $ajaxfor=   "listcollection";

    $current_page=   "collections";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "FullCollectionName%20asc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";

    $requested_page = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $shskip = ($requested_page - 1) * $showrec;

    $nxshowrec=   isset($listPageSize) ? $listPageSize : 9;
    $nxshskip =   $shskip;

    $loadsec=1;

    //Fetch Collections From Database
    $collection_table = "Collections";
    $database = \Drupal::database();
    $connection = Database::getConnection();


    $count = $database->select($collection_table)
    ->countQuery()
    ->execute()
    ->fetchField();

      // Build the query.
      $query = $connection->select($collection_table, 'c');
      $query->fields('c');

      // Add conditions based on $dataorderby and $qSearch.
      if ($dataorderby === 'CollectionName%20asc') {
          $query->orderBy('c.CollectionName');
      } elseif ($dataorderby === 'CollectionName%20desc') {
          $query->orderBy('c.CollectionName', 'DESC');
      }elseif ($dataorderby === 'FullCollectionName%20desc') {
        $query->orderBy('c.FullCollectionName', 'DESC');
      }

      if ($qSearch !== NULL) {
          $query->condition('c.CollectionName', '%' . $connection->escapeLike($qSearch) . '%', 'LIKE');
      }

      // Add limits.
      $query->range($shskip, $showrec);

      // Execute the query.
      $result = $query->execute();

      // Fetch all collections.
      $all_collections = $result->fetchAll();

      $base_url_with_scheme = \Drupal::request()->getSchemeAndHttpHost();

    $showImagesOnListPages =  \Drupal::config('custom_api_integration.settings')->get('show_images_on_list_pages');

    $build = [
      '#theme' => 'collections-list-page',
      '#all_collections' => $all_collections,
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#count' => $count,
      '#dataorderby' => $dataorderby,
      '#current_page' => $current_page,
      '#qSearch' => $qSearch,
      '#loadsec' => $loadsec,
      '#requested_page' => $requested_page,
      '#site_url' => $base_url_with_scheme,
      '#showImagesOnListPages' => $showImagesOnListPages,
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];


    return $build;

  }

  public function ObjectDetailPage(){

    $artObjID=$_REQUEST['dataId'];
    $sortBy=isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "Title%20desc";
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

    if ($sortBy === 'Title%20desc' || $sortBy === 'Title asc') {
      $query->orderBy('Title', ($sortBy === 'Title%20desc') ? 'DESC' : 'ASC');
    }
    elseif ($sortBy === 'InventoryNumber%20asc' || $sortBy === 'InventoryNumber%20desc') {
      $query->orderBy('InventoryNumber', ($sortBy === 'InventoryNumber%20desc') ? 'DESC' : 'ASC');

    }
    elseif ($sortBy === 'ObjectDate%20desc' || $sortBy === 'ObjectDate%20asc') {
      $query->orderBy('ObjectDate', ($sortBy === 'ObjectDate%20desc') ? 'DESC' : 'ASC');
    }
    elseif ($sortBy === 'Collection/CollectionName%20asc' || $sortBy === 'Collection/CollectionName%20desc') {
      $query->join($collection_table, 'c', 'o.CollectionId = c.CollectionId');
      $query->fields('c', ['CollectionName']);
      $query->orderBy('c.CollectionName', ($sortBy === 'Collection/CollectionName%20desc') ? 'DESC' : 'ASC');
    }

    // $object_details = $query->execute()->fetchAllAssoc('ObjectId');
    $object_details = $query->execute()->fetchAll();

    $module_path = \Drupal::service('extension.list.module')->getPath('custom_api_integration');

    $enable_maps = \Drupal::config('custom_api_integration.settings')->get('enable_maps');
    if($enable_maps){
      //start azure map
      $customized_fields = $this->getCommaSeperatedFieldsForDetailPage();
      $customized_fields_array = explode(',', $customized_fields);
      $locations = [];
      $query->condition('ObjectId', $artObjID);
      $query_without_range = $query->range(); //to include all results without range
      $result = $query_without_range->execute();
      $object_details_without_range =  $result->fetchAllAssoc('ObjectId');
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
          "main_image_attachment" => base64_encode($main_image_attachment),
          "main_image_path" => $main_image_path,
          "object_detail_url" => '/artobject-detail?dataId='. $object_id,

        ];
        if($Latitude && $Longitude){
          //only display image, title and Inventory in pin-popup  Number for the detail page
          $locations_data['data_selected_fields']['Title'] = $object->Title;
          $locations_data['data_selected_fields']['InventoryNumber'] = $object->InventoryNumber;
          $locations[] =  $locations_data;
        }
      }


      $state = \Drupal::state();
      $subscription_key = $state->get('collector_systems_azure_map.subscription_key');

      $js_settings = [
        'locations' => $locations,
        'subscription_key' => $subscription_key,
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

    if($object_details)
    {
        foreach($object_details as $key=>$object)
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

    $module_path = \Drupal::service('extension.list.module')->getPath('custom_api_integration');
    $enable_zoom =  \Drupal::config('custom_api_integration.settings')->get('enable_zoom');


    $build = [
      '#theme' => 'artobject-detail-page',
      '#thumbDetails' => $thumbDetails,
      '#customized_fields_array' => $customized_fields_array,
      '#object_details' => $object_details,
      '#row_number' => $row_number,
      '#row_before' => $row_before,
      '#row_after' => $row_after,
      '#count' => $count,
      '#sortBy' => $sortBy,
      '#qSearch' => $qSearch,
      '#requested_pageNo' => $requested_pageNo,
      '#module_path' => $module_path,
      '#enable_maps' => $enable_maps,
      '#enable_zoom' => $enable_zoom,
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];

    if($enable_maps){
      foreach ($js_settings as $key => $value) {
        $build['#attached']['drupalSettings']['azure_map'][$key] = $value;
      }

      $build['#attached']['library'][] = 'custom_api_integration/azure_map';
      $build['#attached']['library'][] = 'custom_api_integration/custom_tabs';
    }
    $build['#attached']['library'][] = 'custom_api_integration/jquery_ui';
    $build['#attached']['library'][] = 'custom_api_integration/artobject_detail_page';
    return $build;
  }

  public function ArtistDetailPage(){
    $artistId=$_REQUEST['dataId'];
    $listPageSize =  \Drupal::config('custom_api_integration.settings')->get('items_per_page');
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
    $query = $connection->select($object_table, 'o')
      ->fields('o') // Specify the fields you want to select
      ->condition('o.ArtistId', $artistId);
    $query->orderBy('Title', 'ASC');


    if ($qSearch !== NULL && count($customized_fields_array)>0) {
      $query->condition($where_conditions);
    }
    $object_details = $query->execute()->fetchAllAssoc('ObjectId');

    // Count
    $count_object = $connection->select($object_table, 'o')
      ->condition('o.ArtistId', $artistId)
      ->countQuery()
      ->execute()
      ->fetchField();

    $obj_count = $count_object;

    $module_path = \Drupal::service('extension.list.module')->getPath('custom_api_integration');

    $enable_maps = \Drupal::config('custom_api_integration.settings')->get('enable_maps');
    if($enable_maps){
      //start azure map
      $locations = [];
      $query_without_range = $query->range(); //to include all results without range
      $result = $query_without_range->execute();
      $object_details_without_range =  $result->fetchAllAssoc('ObjectId');
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
          "main_image_attachment" => base64_encode($main_image_attachment),
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


      $state = \Drupal::state();
      $subscription_key = $state->get('collector_systems_azure_map.subscription_key');

      $js_settings = [
        'locations' => $locations,
        'subscription_key' => $subscription_key,
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
      '#module_path' => $module_path,
      '#enable_maps' => $enable_maps,
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];

    if($enable_maps){
      foreach ($js_settings as $key => $value) {
        $build['#attached']['drupalSettings']['azure_map'][$key] = $value;
      }

      $build['#attached']['library'][] = 'custom_api_integration/azure_map';
      $build['#attached']['library'][] = 'custom_api_integration/custom_tabs';
    }



    return $build;

  }

  public function ExhibitionDetailPage(){
    $exhibitionID=$_REQUEST['dataId'];
    $listPageSize =  \Drupal::config('custom_api_integration.settings')->get('items_per_page');
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

    $query = \Drupal::database()->select($exhibitionObj_table, 'eo');
    $query->fields('eo');
    $query->join($object_table, 'co', 'eo.ObjectId = co.ObjectId');
    $query->fields('co');
    $query->condition('eo.ExhibitionId', $exhibitionID);
    $query->orderBy('Title', 'ASC');

    $result = $query->execute();

    $object_details = $result->fetchAllAssoc('ObjectId');

    //Count
    $count_object = $database->query("SELECT COUNT(*) FROM {" . $exhibitionObj_table . "} WHERE ExhibitionId = :exhibition_id", [
      ':exhibition_id' => $exhibitionID,
    ]);
    $obj_count = $count_object->fetchField();

    $module_path = \Drupal::service('extension.list.module')->getPath('custom_api_integration');
    $enable_maps = \Drupal::config('custom_api_integration.settings')->get('enable_maps');

    if($enable_maps){
      //start azure map
      $customized_fields = $this->getCommaSeperatedFieldsForListPageObject();
      if($customized_fields){
        $customized_fields_array = explode(',', $customized_fields);
      }else{
        $customized_fields_array = [];
      }

      $locations = [];
      $query_without_range = $query->range(); //to include all results without range
      $result = $query_without_range->execute();
      $object_details_without_range =  $result->fetchAllAssoc('ObjectId');
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
          "main_image_attachment" => base64_encode($main_image_attachment),
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


      $state = \Drupal::state();
      $subscription_key = $state->get('collector_systems_azure_map.subscription_key');

      $js_settings = [
        'locations' => $locations,
        'subscription_key' => $subscription_key,
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

    if($enable_maps){
      foreach ($js_settings as $key => $value) {
        $build['#attached']['drupalSettings']['azure_map'][$key] = $value;
      }

      $build['#attached']['library'][] = 'custom_api_integration/azure_map';
      $build['#attached']['library'][] = 'custom_api_integration/custom_tabs';
    }

    return $build;

  }

  public function GroupDetailPage(){
    $groupID=$_REQUEST['dataId'];
    $listPageSize =  \Drupal::config('custom_api_integration.settings')->get('items_per_page');
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
    $query = $database->select($groupObj_table, 'eo');
    $query->fields('eo');
    $query->condition('eo.GroupId', $groupID);
    $query->join($object_table, 'co', 'eo.ObjectId = co.ObjectId');
    $query->fields('co');
    $query->orderBy('Title', 'ASC');


    $group_object_details = $query->execute()->fetchAllAssoc('ObjectId');


    $query_count = $database->select($groupObj_table, 'go')
      ->condition('GroupId', $groupID)
      ->countQuery();
    $obj_count = $query_count->execute()->fetchField();

    $module_path = \Drupal::service('extension.list.module')->getPath('custom_api_integration');
    $enable_maps = \Drupal::config('custom_api_integration.settings')->get('enable_maps');
    if($enable_maps){
      //start azure map
      $customized_fields = $this->getCommaSeperatedFieldsForListPageObject();
      if($customized_fields){
        $customized_fields_array = explode(',', $customized_fields);
      }else{
        $customized_fields_array = [];
      }

      $locations = [];
      $query_without_range = $query->range(); //to include all results without range
      $result = $query_without_range->execute();
      $object_details_without_range =  $result->fetchAllAssoc('ObjectId');
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
          "main_image_attachment" => base64_encode($main_image_attachment),
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


      $state = \Drupal::state();
      $subscription_key = $state->get('collector_systems_azure_map.subscription_key');

      $js_settings = [
        'locations' => $locations,
        'subscription_key' => $subscription_key,
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

    if($enable_maps){
      foreach ($js_settings as $key => $value) {
        $build['#attached']['drupalSettings']['azure_map'][$key] = $value;
      }

      $build['#attached']['library'][] = 'custom_api_integration/azure_map';
      $build['#attached']['library'][] = 'custom_api_integration/custom_tabs';
    }


    return $build;

  }


  public function CollectionDetailPage(){

    $collectionID=$_REQUEST['dataId'];
    $listPageSize =  \Drupal::config('custom_api_integration.settings')->get('items_per_page');
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


    $object_table = 'CSObjects';
    $query = $database->select($object_table, 'co')
      ->fields('co')
      ->condition('co.CollectionId', $collectionID);

    // Add limits.
    $query->range($shskip, $showrec);
    $query->orderBy('Title', 'ASC');

    $object_details = $query->execute()->fetchAllAssoc('ObjectId');


    //Count
    $query_count = $database->select($object_table, 'co')
    ->condition('co.CollectionId', $collectionID)
    ->countQuery();
    $obj_count = $query_count->execute()->fetchField();

    $module_path = \Drupal::service('extension.list.module')->getPath('custom_api_integration');
    $enable_maps = \Drupal::config('custom_api_integration.settings')->get('enable_maps');

    if($enable_maps){
      //start azure map
      $customized_fields = $this->getCommaSeperatedFieldsForListPageObject();
      if($customized_fields){
        $customized_fields_array = explode(',', $customized_fields);
      }else{
        $customized_fields_array = [];
      }

      $locations = [];
      $query_without_range = $query->range(); //to include all results without range
      $result = $query_without_range->execute();
      $object_details_without_range =  $result->fetchAllAssoc('ObjectId');
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
          "main_image_attachment" => base64_encode($main_image_attachment),
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


      $state = \Drupal::state();
      $subscription_key = $state->get('collector_systems_azure_map.subscription_key');

      $js_settings = [
        'locations' => $locations,
        'subscription_key' => $subscription_key,
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

    if($enable_maps){
      foreach ($js_settings as $key => $value) {
        $build['#attached']['drupalSettings']['azure_map'][$key] = $value;
      }

      $build['#attached']['library'][] = 'custom_api_integration/azure_map';
      $build['#attached']['library'][] = 'custom_api_integration/custom_tabs';
    }

    return $build;

  }

  public function AzureMapPage(){
    $locations = [];
    $object_table = 'CSObjects';
    try {

      // Database query to fetch latitude and longitude values
      $query = \Drupal::database()->select($object_table, 'o');
      $query->fields('o', ['Latitude', 'Longitude', 'AddressName']);
      $query->condition('o.Latitude', NULL, 'IS NOT NULL');
      $query->condition('o.Longitude', NULL, 'IS NOT NULL');
      $results = $query->execute()->fetchAll();
      foreach ($results as $record) {
        $Latitude = $record->Latitude;
        $Longitude = $record->Longitude;
        $AddressName = $record->AddressName;

        $locations[] =    ["latitude" => $Latitude, "longitude" => $Longitude, "AddressName" => $AddressName];

      }
    } catch (\Exception $e) {
    }
    $state = \Drupal::state();
    $subscription_key = $state->get('collector_systems_azure_map.subscription_key');

    $js_settings = [
      'locations' => $locations,
      'subscription_key' => $subscription_key
    ];

    $build = [
      '#theme' => 'azure-map-page',
      '#locations' => $locations,
    ];

    $build['#attached']['library'][] = 'custom_api_integration/azure_map';

    foreach ($js_settings as $key => $value) {
      $build['#attached']['drupalSettings']['azure_map'][$key] = $value;
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

  public function getCommaSeparatedFieldsForListAndDetailPage(){
    $db = \Drupal::database();

    $tblnm = "clsobjects_fields";
    $settblnm = $tblnm;

    $query = $db->select($settblnm, 'c')
      ->fields('c', ['fieldname']);
    // Create a Condition object for OR logic
    $orCondition = new Condition('OR');
    $orCondition->condition('fieldtype', 'ObjectList');
    $orCondition->condition('fieldtype', 'Objectdetail');
    $query->condition($orCondition);


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
