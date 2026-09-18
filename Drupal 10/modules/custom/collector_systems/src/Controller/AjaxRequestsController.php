<?php

namespace Drupal\collector_systems\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\Condition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;
use Drupal\collector_systems\Csconstants;
use Drupal\collector_systems\ObjectsService;
use Drupal\collector_systems\AdvancedSearchService;


class AjaxRequestsController extends ControllerBase
{

  /**
   * The allow-listed values for the object list "order by" parameter.
   *
   * Mirrors the literal comparisons in query_sort_objects_list() so invalid
   * values are rejected at the request boundary, before they can reach the
   * HTML/link-building code in CustomTwig.php.
   */
  const ALLOWED_ORDER_BY = [
    'Title%20desc',
    'Title%20asc',
    'InventoryNumber%20asc',
    'InventoryNumber%20desc',
    'ObjectDate%20desc',
    'ObjectDate%20asc',
    'Collection/CollectionName%20asc',
    'Collection/CollectionName%20desc',
  ];

  /**
   * The Twig environment.
   *
   * @var \Twig\Environment
   */
  protected $twig;

  /**
   * The objects service.
   *
   * @var \Drupal\collector_systems\ObjectsService
   */
  protected $objectsService;

  /**
   * The advanced search service.
   *
   * @var \Drupal\collector_systems\AdvancedSearchService
   */
  protected $advancedSearch;

  /**
   * A request stack symfony instance.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  public function __construct(Environment $twig, ObjectsService $objects_service, AdvancedSearchService $advanced_search, RequestStack $request_stack)
  {
    $this->twig = $twig;
    $this->objectsService = $objects_service;
    $this->advancedSearch = $advanced_search;
    $this->requestStack = $request_stack;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('twig'),
      $container->get('collector_systems.objects_service'),
      $container->get('collector_systems.advanced_search_service'),
      $container->get('request_stack')
    );
  }


  public function groupLevelObjects_searching_page()
  {
    $request = $this->requestStack->getCurrentRequest();

    $groupLevelSearchHtml = "";
    $Spage = strip_tags(trim($request->request->get('pagename', '')));

    $groupTypeId = $request->request->getInt('groupTypeId');
    $groupLevelTopCount = $request->request->getInt('groupLevelTopCount');
    $groupLevelSkipCount = $request->request->getInt('groupLevelSkipCount', 0);
    $groupLevelOrderBy = $request->request->get('groupLevelOrderBy', '');
    if (!in_array($groupLevelOrderBy, self::ALLOWED_ORDER_BY, TRUE)) {
      $groupLevelOrderBy = '';
    }
    $groupLevelSearch = trim($request->request->get('searchWord', ''));

    $groupLevelPageNo = max(1, $request->request->getInt('groupLevelPageNoValue', 1));

    $loadsec = 1;
    $customized_fields = $this->getCommaSeparatedFieldsForSearch();
    $qSearch = $groupLevelSearch;

    $customized_fields_array = explode(',', $customized_fields);
    $connection = Database::getConnection();

    // Table names
    $object_table = $connection->prefixTables('collector_systems_objects');
    $collection_table = 'collector_systems_collections';
    $artist_table = 'collector_systems_artists';

    if ($Spage == "artist-detail") {
      $artistId = $groupTypeId;
      // Fetch artist details from the database

      // Fetch objects where ArtistId
      $query = $connection->select($object_table, 'o')
        ->fields('o') // Specify the fields you want to select
        ->condition('o.ArtistId', $artistId);

      // Advanced Search takes priority over the plain search box (matches
      // the WP plugin's mutual-exclusivity rule): if this request submitted
      // the advanced panel, ignore the plain search word entirely.
      if ($this->advancedSearch->isActiveRequest()) {
        $advanced_condition = $this->advancedSearch->buildConditionGroup($query, 'artist-detail');
        if ($advanced_condition) {
          $query->condition($advanced_condition);
        }
      }
      // search filter
      elseif ($qSearch !== NULL && count($customized_fields_array) > 0) {
        // Apply search conditions if needed.
        if (!empty($customized_fields_array) && !empty($qSearch)) {
          $escaped_search = '%' . $connection->escapeLike($qSearch) . '%';
          $or_condition_group = $query->orConditionGroup();

          // Get the column names for objects table
          $object_columns = $this->objectsService->cs_get_table_columns($object_table);
          // we only need to search objects table columns field because selected fields data are from objects table only.
          foreach ($customized_fields_array as $field) {
            if (in_array($field, $object_columns, true)) {
              $or_condition_group->condition("o.$field", $escaped_search, 'LIKE');
            }
            else {
              // Optional: Log or ignore unknown fields
              \Drupal::logger('collector_systems')->warning("Unknown search field: @field", ['@field' => $field]);
            }
          }

          $query->condition($or_condition_group);
        }

      }

      $query->range($groupLevelSkipCount, $groupLevelTopCount);

      //sorting
      $this->query_sort_objects_list($groupLevelOrderBy, $qSearch, $query);


      $object_details = $query->execute()->fetchAllAssoc('ObjectId');

      // Count
      $obj_count = count($object_details);

      if ($obj_count > 0) {
        foreach ($object_details as $value) {
          // Calculate delay time based on $loadsec.
          if ($loadsec == 1) {
            $delaytm = '0.01s';
          } elseif ($loadsec == 2) {
            $delaytm = '0.03s';
          } else {
            $delaytm = '0.05s';
          }

          // Get the function from the Twig environment and call it.
          $function = $this->twig->getFunction('getObjectslistHtml')->getCallable();

          // Start output buffering
          ob_start();

          // Call the function. Its output will be captured by the output buffer
          $returnedHtml = call_user_func($function, $value, $groupLevelOrderBy, $groupLevelPageNo, $qSearch, $delaytm, 'https://cdn.collectorsystems.com/images/noimage300.png');

          // Get the contents of the output buffer (i.e., the output of your function)
          $echoedHtml = ob_get_clean();
          $functionOutput = $echoedHtml . $returnedHtml;

          $groupLevelSearchHtml .= $functionOutput;
        }
      } else {
        $groupLevelSearchHtml .= '<div class="cs-theme-nodata">No results found. Please try another search.</div>';
      }
      $groupLevelSearchHtml.= '<input type="hidden" id="hdnTotalGroupLevelObjectCount" value="'.$obj_count.'"></input>';
    } else if ($Spage == "exhibition-detail") {

      $exhibitionID = $groupTypeId;
      // Fetch artist details from the database
      $connection = Database::getConnection();
      $database = \Drupal::database();

      //Fetch Objects Where ExhibitionId
      $exhibitionObj_table = 'collector_systems_exhibition_objects';
      $object_table = 'collector_systems_objects';

      $query = \Drupal::database()->select($exhibitionObj_table, 'eo');
      $query->fields('eo');
      $query->join($object_table, 'o', 'eo.ObjectId = o.ObjectId');
      $query->fields('o');
      $query->condition('eo.ExhibitionId', $exhibitionID);

      // Advanced Search takes priority over the plain search box (matches
      // the WP plugin's mutual-exclusivity rule): if this request submitted
      // the advanced panel, ignore the plain search word entirely.
      if ($this->advancedSearch->isActiveRequest()) {
        $advanced_condition = $this->advancedSearch->buildConditionGroup($query, 'exhibition-detail');
        if ($advanced_condition) {
          $query->condition($advanced_condition);
        }
      }
      // search filter
      elseif ($qSearch !== NULL && count($customized_fields_array) > 0) {
        // Apply search conditions if needed.
        if (!empty($customized_fields_array) && !empty($qSearch)) {
          $escaped_search = '%' . $connection->escapeLike($qSearch) . '%';
          $or_condition_group = $query->orConditionGroup();

          // Get the column names for objects table
          $object_columns = $this->objectsService->cs_get_table_columns($object_table);
          // we only need to search objects table columns field because selected fields data are from objects table only.
          foreach ($customized_fields_array as $field) {
            if (in_array($field, $object_columns, true)) {
              $or_condition_group->condition("o.$field", $escaped_search, 'LIKE');
            }
            else {
              // Optional: Log or ignore unknown fields
              \Drupal::logger('collector_systems')->warning("Unknown search field: @field", ['@field' => $field]);
            }
          }

          $query->condition($or_condition_group);
        }

      }

      $query->range($groupLevelSkipCount, $groupLevelTopCount);

      //sorting
      $this->query_sort_objects_list($groupLevelOrderBy, $qSearch, $query);

      $result = $query->execute();

      $object_details = $result->fetchAllAssoc('ObjectId');

      //Count
      $count_query = $query->countQuery();
      $obj_count = $count_query->execute()->fetchField();

      if ($obj_count > 0) {
        foreach ($object_details as $value) {
          // Calculate delay time based on $loadsec.
          if ($loadsec == 1) {
            $delaytm = '0.01s';
          } elseif ($loadsec == 2) {
            $delaytm = '0.03s';
          } else {
            $delaytm = '0.05s';
          }

          // Get the function from the Twig environment and call it.
          $function = $this->twig->getFunction('getObjectslistHtml')->getCallable();

          // Start output buffering
          ob_start();

          // Call the function. Its output will be captured by the output buffer
          $returnedHtml = call_user_func($function, $value, $groupLevelOrderBy, $groupLevelPageNo, $qSearch, $delaytm, 'https://cdn.collectorsystems.com/images/noimage300.png');

          // Get the contents of the output buffer (i.e., the output of your function)
          $echoedHtml = ob_get_clean();
          $functionOutput = $echoedHtml . $returnedHtml;

          $groupLevelSearchHtml .= $functionOutput;
        }
      } else {
        $groupLevelSearchHtml .= '<div class="cs-theme-nodata">No results found. Please try another search.</div>';
      }
      $groupLevelSearchHtml.= '<input type="hidden" id="hdnTotalGroupLevelObjectCount" value="'.$obj_count.'"></input>';
    } else if ($Spage == "group-detail") {

      $groupID = $groupTypeId;
      // Fetch artist details from the database
      $connection = Database::getConnection();
      $database = \Drupal::database();

      //Fetch Objects Where GroupId
      $groupObj_table = "collector_systems_group_objects";
      $query = $database->select($groupObj_table, 'go')
        ->fields('go')
        ->condition('GroupId', $groupID);
      $object_details = $query->execute()->fetchAssoc();


      $object_table = "collector_systems_objects";
      $query = $database->select($groupObj_table, 'eo');
      $query->fields('eo');
      $query->condition('eo.GroupId', $groupID);
      $query->join($object_table, 'o', 'eo.ObjectId = o.ObjectId');
      $query->fields('o');

      // Advanced Search takes priority over the plain search box (matches
      // the WP plugin's mutual-exclusivity rule): if this request submitted
      // the advanced panel, ignore the plain search word entirely.
      if ($this->advancedSearch->isActiveRequest()) {
        $advanced_condition = $this->advancedSearch->buildConditionGroup($query, 'group-detail');
        if ($advanced_condition) {
          $query->condition($advanced_condition);
        }
      }
      // search filter
      elseif ($qSearch !== NULL && count($customized_fields_array) > 0) {
        // Apply search conditions if needed.
        if (!empty($customized_fields_array) && !empty($qSearch)) {
          $escaped_search = '%' . $connection->escapeLike($qSearch) . '%';
          $or_condition_group = $query->orConditionGroup();

          // Get the column names for objects table
          $object_columns = $this->objectsService->cs_get_table_columns($object_table);
          // we only need to search objects table columns field because selected fields data are from objects table only.
          foreach ($customized_fields_array as $field) {
            if (in_array($field, $object_columns, true)) {
              $or_condition_group->condition("o.$field", $escaped_search, 'LIKE');
            }
            else {
              // Optional: Log or ignore unknown fields
              \Drupal::logger('collector_systems')->warning("Unknown search field: @field", ['@field' => $field]);
            }
          }

          $query->condition($or_condition_group);
        }

      }

      $query->range($groupLevelSkipCount, $groupLevelTopCount);


      //sorting
      $this->query_sort_objects_list($groupLevelOrderBy, $qSearch, $query);


      //Count
      $count_query = $query->countQuery();
      $obj_count = $count_query->execute()->fetchField();

      $object_details = $query->execute()->fetchAllAssoc('ObjectId');

      if ($obj_count > 0) {
        foreach ($object_details as $value) {
          // Calculate delay time based on $loadsec.
          if ($loadsec == 1) {
            $delaytm = '0.01s';
          } elseif ($loadsec == 2) {
            $delaytm = '0.03s';
          } else {
            $delaytm = '0.05s';
          }

          // Get the function from the Twig environment and call it.
          $function = $this->twig->getFunction('getObjectslistHtml')->getCallable();

          // Start output buffering
          ob_start();

          // Call the function. Its output will be captured by the output buffer
          $returnedHtml = call_user_func($function, $value, $groupLevelOrderBy, $groupLevelPageNo, $qSearch, $delaytm, 'https://cdn.collectorsystems.com/images/noimage300.png');

          // Get the contents of the output buffer (i.e., the output of your function)
          $echoedHtml = ob_get_clean();
          $functionOutput = $echoedHtml . $returnedHtml;

          $groupLevelSearchHtml .= $functionOutput;
        }
      } else {
        $groupLevelSearchHtml .= '<div class="cs-theme-nodata">No results found. Please try another search.</div>';
      }
      $groupLevelSearchHtml.= '<input type="hidden" id="hdnTotalGroupLevelObjectCount" value="'.$obj_count.'"></input>';
    } else if ($Spage == "collection-detail") {

      $collectionID = $groupTypeId;

      $database = \Drupal::database();
      // collection object details.
      $object_table = 'collector_systems_objects';
      $connection = \Drupal::database();

      $connection = \Drupal::database();
      $extent = $connection->select('collector_systems_collections', 'c')
      ->fields('c', ['LeftExtent', 'RightExtent'])
      ->condition('CollectionId', $collectionID)
      ->execute()
      ->fetchAssoc();

      $leftExtent = $extent['LeftExtent'];
      $rightExtent = $extent['RightExtent'];
      

      $query = $connection->select($object_table, 'o');
      $query->innerJoin('collector_systems_collections', 'c', 'o.CollectionId = c.CollectionId');

      // Apply value bounds to LeftExtent and RightExtent
      $query->condition('c.LeftExtent', $leftExtent, '>=');
      $query->condition('c.RightExtent', $rightExtent, '<=');

      $query->fields('o');
      $query->fields('c');

      // Advanced Search takes priority over the plain search box (matches
      // the WP plugin's mutual-exclusivity rule): if this request submitted
      // the advanced panel, ignore the plain search word entirely.
      if ($this->advancedSearch->isActiveRequest()) {
        $advanced_condition = $this->advancedSearch->buildConditionGroup($query, 'collection-detail');
        if ($advanced_condition) {
          $query->condition($advanced_condition);
        }
      }
      // search filter
      elseif ($qSearch !== NULL && count($customized_fields_array) > 0) {
        // Apply search conditions if needed.
        if (!empty($customized_fields_array) && !empty($qSearch)) {
          $escaped_search = '%' . $connection->escapeLike($qSearch) . '%';
          $or_condition_group = $query->orConditionGroup();

          // Get the column names for objects table
          $object_columns = $this->objectsService->cs_get_table_columns($object_table);
          // we only need to search objects table columns field because selected fields data are from objects table only.
          foreach ($customized_fields_array as $field) {
            if (in_array($field, $object_columns, true)) {
              $or_condition_group->condition("o.$field", $escaped_search, 'LIKE');
            }
            else {
              // Optional: Log or ignore unknown fields
              \Drupal::logger('collector_systems')->warning("Unknown search field: @field", ['@field' => $field]);
            }
          }

          $query->condition($or_condition_group);
        }

      }

      $count_query = $query->countQuery();
      $query->range($groupLevelSkipCount, $groupLevelTopCount);
      
      //start sort
      if ($groupLevelOrderBy === "Title%20desc" && $qSearch !== NULL) {

        $query->orderBy('Title', 'DESC');
      }
      else if($groupLevelOrderBy === "Title%20asc" && $qSearch !== NULL)
      {

        $query->orderBy('Title', 'ASC');
      }
      else if($groupLevelOrderBy === "InventoryNumber%20asc" && $qSearch !== NULL)
      {
          $query->orderBy('InventoryNumber', 'ASC');
      }
      else if($groupLevelOrderBy === "InventoryNumber%20desc" && $qSearch !== NULL)
      {

        $query->orderBy('InventoryNumber', 'DESC');
      }
      else if($groupLevelOrderBy === "ObjectDate%20desc" && $qSearch !== NULL)
      {

        $query->orderBy('ObjectDate', 'DESC');
      }
      else if($groupLevelOrderBy === "ObjectDate%20asc" && $qSearch !== NULL)
      {

        $query->orderBy('ObjectDate', 'ASC');
      }
      else if($groupLevelOrderBy === "Collection/CollectionName%20asc" && $qSearch !== NULL){
        $query->orderBy('c.CollectionName', 'ASC');

      }
      else if($groupLevelOrderBy === "Collection/CollectionName%20desc" && $qSearch !== NULL){
        $query->orderBy('c.CollectionName', 'DESC');
      }
      //end sort

      $object_details = $query->execute()->fetchAllAssoc('ObjectId');

      //Count
      $obj_count = $count_query->execute()->fetchField();


      if ($obj_count > 0) {
        foreach ($object_details as $value) {
          // Calculate delay time based on $loadsec.
          if ($loadsec == 1) {
            $delaytm = '0.01s';
          } elseif ($loadsec == 2) {
            $delaytm = '0.03s';
          } else {
            $delaytm = '0.05s';
          }

          // Get the function from the Twig environment and call it.
          $function = $this->twig->getFunction('getObjectslistHtml')->getCallable();

          // Start output buffering
          ob_start();

          // Call the function. Its output will be captured by the output buffer
          $returnedHtml = call_user_func($function, $value, $groupLevelOrderBy, $groupLevelPageNo, $qSearch, $delaytm, 'https://cdn.collectorsystems.com/images/noimage300.png');

          // Get the contents of the output buffer (i.e., the output of your function)
          $echoedHtml = ob_get_clean();
          $functionOutput = $echoedHtml . $returnedHtml;

          $groupLevelSearchHtml .= $functionOutput;
        }
      } else {
        $groupLevelSearchHtml .= '<div class="cs-theme-nodata">No results found. Please try another search.</div>';
      }
      $groupLevelSearchHtml.= '<input type="hidden" id="hdnTotalGroupLevelObjectCount" value="'.$obj_count.'"></input>';
    }

    return new JsonResponse(['groupLevelSearchHtml' => $groupLevelSearchHtml]);
  }






  public function getCommaSeperatedFieldsForListPageObject()
  {
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

  function query_sort_objects_list($groupLevelOrderBy, $qSearch, $query){
    $connection = Database::getConnection();

    $collection_table =  $connection->prefixTables('collector_systems_collections');

    //for sorting
    if ($groupLevelOrderBy === "Title%20desc" && $qSearch !== NULL) {

      $query->orderBy('Title', 'DESC');
    }
    else if($groupLevelOrderBy === "Title%20asc" && $qSearch !== NULL)
    {

      $query->orderBy('Title', 'ASC');
    }
    else if($groupLevelOrderBy === "InventoryNumber%20asc" && $qSearch !== NULL)
    {
        $query->orderBy('InventoryNumber', 'ASC');
    }
    else if($groupLevelOrderBy === "InventoryNumber%20desc" && $qSearch !== NULL)
    {

      $query->orderBy('InventoryNumber', 'DESC');
    }
    else if($groupLevelOrderBy === "ObjectDate%20desc" && $qSearch !== NULL)
    {

      $query->orderBy('ObjectDate', 'DESC');
    }
    else if($groupLevelOrderBy === "ObjectDate%20asc" && $qSearch !== NULL)
    {

      $query->orderBy('ObjectDate', 'ASC');
    }
    else if($groupLevelOrderBy === "Collection/CollectionName%20asc" && $qSearch !== NULL){
      $query->fields('o')
      ->fields('c', ['CollectionName'])
      ->join($collection_table, 'c', 'o.CollectionId = c.CollectionId');

      $query->orderBy('c.CollectionName', 'ASC');

    }
    else if($groupLevelOrderBy === "Collection/CollectionName%20desc" && $qSearch !== NULL){
      $query->fields('o')
      ->fields('c', ['CollectionName'])
      ->join($collection_table, 'c', 'o.CollectionId = c.CollectionId');

      $query->orderBy('c.CollectionName', 'DESC');
    }
  }

  public function getImagesCountData(){
    $collector_systemsts_get_api_data = \Drupal::service('collector_systems.collector_systemsts_get_api_data');

    $object_images_db_count = $this->getCsGetDbImageTypeCount('object_images');
    $object_images_api_count = $collector_systemsts_get_api_data->getApiImageTypeCount('objects_images');

    // other images api count.
    $artists_images_api_count =  $collector_systemsts_get_api_data->getApiImageTypeCount('artists_images');
    $collections_images_api_count =  $collector_systemsts_get_api_data->getApiImageTypeCount('collections_images');
    $exhibitions_images_api_count =  $collector_systemsts_get_api_data->getApiImageTypeCount('exhibitions_images');
    $groups_images_api_count =  $collector_systemsts_get_api_data->getApiImageTypeCount('groups_images');
    $other_images_api_count = $artists_images_api_count + $collections_images_api_count + $exhibitions_images_api_count + $groups_images_api_count;
    


    // other images db count.
    $artists_images_db_count =  $this->getCsGetDbImageTypeCount('artists_images');
    $collections_images_db_count =  $this->getCsGetDbImageTypeCount('collections_images');
    $exhibitions_images_db_count =  $this->getCsGetDbImageTypeCount('exhibitions_images');
    $groups_images_db_count =  $this->getCsGetDbImageTypeCount('groups_images');
    $other_images_db_count = $artists_images_db_count + $collections_images_db_count + $exhibitions_images_db_count + $groups_images_db_count;
    
    $other_images_db_count = $artists_images_db_count + $collections_images_db_count + $exhibitions_images_db_count + $groups_images_db_count;


    $response = [
        'object_images_api_count' => $object_images_api_count,
        'object_images_db_count' => $object_images_db_count,
        'other_images_api_count' => $other_images_api_count,
        'other_images_db_count' => $other_images_db_count,
        'all_details_count' => [
            'object_images_db_count' => $object_images_db_count,
            'object_images_api_count' => $object_images_api_count,
            'artists_images_db_count'=> $artists_images_db_count,
            'artists_images_api_count'=> $artists_images_api_count,
            'collections_images_db_count' => $collections_images_db_count,
            'collections_images_api_count' => $collections_images_api_count,
            'groups_images_api_count' => $groups_images_api_count,
            'groups_images_db_count' => $groups_images_db_count,
            'exhibitions_images_db_count' => $exhibitions_images_db_count,
            'exhibitions_images_api_count' => $exhibitions_images_api_count
        ]

    ];

    return new JsonResponse($response);

  }

  public function saveCheckBoxOptionsDataType(){
    if(!isset($_POST['checkboxes'])){
     return new JsonResponse([
        'success' => False,
        'message' => 'Missing data.'
      ]);
    }

    $checkbox_groups= $_POST['checkboxes']['groups'];
    $checkbox_collections = $_POST['checkboxes']['collections'];
    $checkbox_exhibitions = $_POST['checkboxes']['exhibitions'];
    $checkbox_artists = $_POST['checkboxes']['artists'];

    \Drupal::configFactory()->getEditable('collector_systems.settings')
    ->set('checkboxes.groups', $checkbox_groups)
    ->set('checkboxes.collections', $checkbox_collections)
    ->set('checkboxes.exhibitions', $checkbox_exhibitions)
    ->set('checkboxes.artists', $checkbox_artists)
    ->save();


    return new JsonResponse([
      'success' => True,
      'message' => 'Checkboxes settings saved successfully.'
    ]);

  }

  /**
   * Get image count for a given image type.
   *
   * @param string $image_type
   *   One of: object_images, artists_images, collections_images, exhibitions_images, groups_images.
   *
   * @return int|null
   *   The count of image records, or NULL on error.
  */
  function getCsGetDbImageTypeCount($image_type) {
    $connection = \Drupal::database();

    try {
      switch ($image_type) {
        case 'object_images':
          return $connection->query("SELECT COUNT(*) FROM {collector_systems_thumb_images}")->fetchField();

        case 'artists_images':
          return $connection->query("
            SELECT COUNT(*) 
            FROM {collector_systems_artists} 
            WHERE 
              (ArtistPhotoAttachment IS NOT NULL AND ArtistPhotoAttachment != '') 
              OR 
              (ImagePath IS NOT NULL AND ImagePath != '')
          ")->fetchField();

        case 'collections_images':
          return $connection->query("
            SELECT COUNT(*) 
            FROM {collector_systems_collections} 
            WHERE 
              (CollectionImageAttachment IS NOT NULL AND CollectionImageAttachment != '') 
              OR 
              (ImagePath IS NOT NULL AND ImagePath != '')
          ")->fetchField();

        case 'exhibitions_images':
          return $connection->query("
            SELECT COUNT(*) 
            FROM {collector_systems_exhibitions} 
            WHERE 
              (ExhibitionImageAttachment IS NOT NULL AND ExhibitionImageAttachment != '') 
              OR 
              (ImagePath IS NOT NULL AND ImagePath != '')
          ")->fetchField();

        case 'groups_images':
          return $connection->query("
            SELECT COUNT(*) 
            FROM {collector_systems_groups} 
            WHERE 
              (GroupImageAttachment IS NOT NULL AND GroupImageAttachment != '') 
              OR 
              (ImagePath IS NOT NULL AND ImagePath != '')
          ")->fetchField();

        default:
          return NULL;
      }

    } catch (\Exception $e) {
      \Drupal::logger('collector_systems')->error('Error GetDbImageCount: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Provides count for different data types
  */
  public function getTotalCountData() {
    $apiCountForGroup = $this->GetApiGroupCount();
    $DbCountForGroup = $this-> GetDbGroupCount(  );
    $DbCountForObject  = $this->GetDbObjectCount();
    $apiCountForObject = $this->GetApiObjectCount( );
    $apiCountForArtist = $this->GetApiArtistCount();
    $DbCountForArtist= $this->GetDbArtistCount( );
    $DbcollectionCount = $this-> GetDbcollectionCount( );
    $ApicollectionCount = $this-> GetApicollectionCount( );

    $DbExhibitionsCount = $this-> GetDbExhibitionsCount( );
    $ApiExhibitionsCount = $this-> GetApiExhibitionsCount( );

    // $this->save_image_directory();
    $response = [
      'DbCountForGroup' => $DbCountForGroup,
      'apiCountForGroup' => $apiCountForGroup,
      'DbCountForObject' => $DbCountForObject,
      'apiCountForObject' => $apiCountForObject,
      'DbCountForArtist' => $DbCountForArtist,
      'apiCountForArtist' => $apiCountForArtist,
      'DbcollectionCount' => $DbcollectionCount,
      'ApicollectionCount' => $ApicollectionCount,
      'DbExhibitionsCount' => $DbExhibitionsCount,
      'ApiExhibitionsCount' => $ApiExhibitionsCount,

    ];

    return new JsonResponse($response);
  }



  //for goup
  function GetApiGroupCount()
  {
    $config = \Drupal::config('collector_systems.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');

    $wordforsearch="Groups";
    $url = Csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=GroupId';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
    "Accept: application/json",
    "Ocp-Apim-Subscription-Key:$subsKey ",
    "Cache-Control:no-cache",
    'User-Agent: cosydrupal',
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

    $data = curl_exec($curl);
    curl_close($curl);

    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if($httpcode == 403)
    {
        exit();
    }

    $data = json_decode($data, TRUE);
    return $data['@odata.count'];
  }
  function GetDbGroupCount(){
    try {

      $database = Database::getConnection();
      $table_name = 'collector_systems_groups';
      $query = $database->select($table_name, 'g');
      $count = $query->countQuery()->execute()->fetchField();
      return $count;
    }catch (\Exception $e) {
      \Drupal::logger('collector_systems')->error('Database query error: @message', ['@message' => $e->getMessage()]);
      // If an exception is thrown (e.g., table not found), return 0.
      return 0;
    }

  }



  //for object
  function GetApiObjectCount()
  {

    $config = \Drupal::config('collector_systems.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');

      $wordforsearch="Objects";
      $url = Csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=ObjectId';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $headers = array(
      "Accept: application/json",
      "Ocp-Apim-Subscription-Key:$subsKey ",
      "Cache-Control:no-cache",
      'User-Agent: cosydrupal',
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

      $data = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          exit();
      }

    $data = json_decode($data, TRUE);
    return $data['@odata.count'];
  }

  function GetDbObjectCount(){
    try{
      $database = \Drupal::database();

      $table_name = 'collector_systems_objects';
      $query = $database->select($table_name, 'c');
      $count2 = $query->countQuery()->execute()->fetchField();

      return $count2;

    }catch (\Exception $e) {
      \Drupal::logger('collector_systems')->error('Database query error: @message', ['@message' => $e->getMessage()]);
      // If an exception is thrown (e.g., table not found), return 0.
      return 0;
    }

  }


  //for Artist
  function GetApiArtistCount()
  {
      $config = \Drupal::config('collector_systems.settings');
      $subsKey = $config->get('subscription_key');
      $subAcntId = $config->get('account_guid');
      $subsId = $config->get('subscription_id');

      $wordforsearch="Artists";
      $url = Csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=ArtistId';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $headers = array(
      "Accept: application/json",
      "Ocp-Apim-Subscription-Key:$subsKey ",
      "Cache-Control:no-cache",
      'User-Agent: cosydrupal',
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

      $data = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          // get_template_part( 403 );
          exit();
      }

    $data = json_decode($data, TRUE);
    return $data['@odata.count'];
  }

  function GetDbArtistCount(){
    try{
      $database = \Drupal::database();

      $table_name = 'collector_systems_artists';
      $query = $database->select($table_name, 'c');
      $count2 = $query->countQuery()->execute()->fetchField();

      return $count2;
    }catch (\Exception $e) {
      \Drupal::logger('collector_systems')->error('Database query error: @message', ['@message' => $e->getMessage()]);
      // If an exception is thrown (e.g., table not found), return 0.
      return 0;
    }
  }

  //for collection
  function GetApicollectionCount()
  {
      $config = \Drupal::config('collector_systems.settings');
      $subsKey = $config->get('subscription_key');
      $subAcntId = $config->get('account_guid');
      $subsId = $config->get('subscription_id');

      $wordforsearch="Collections";
      $url = Csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=CollectionId';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $headers = array(
      "Accept: application/json",
      "Ocp-Apim-Subscription-Key:$subsKey ",
      "Cache-Control:no-cache",
      'User-Agent: cosydrupal',
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

      $data = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          exit();
      }

    $data = json_decode($data, TRUE);
    return $data['@odata.count'];
  }

  function GetDbcollectionCount(){
    try{
      $table_name = 'collector_systems_collections';
      $database = \Drupal::database();
      $query = $database->select($table_name, 'c');
      $count2 = $query->countQuery()->execute()->fetchField();
      return $count2;
    }catch (\Exception $e) {
      \Drupal::logger('collector_systems')->error('Database query error: @message', ['@message' => $e->getMessage()]);
      // If an exception is thrown (e.g., table not found), return 0.
      return 0;
    }
  }
  //for collection
  function GetApiExhibitionsCount()
  {
      $config = \Drupal::config('collector_systems.settings');
      $subsKey = $config->get('subscription_key');
      $subAcntId = $config->get('account_guid');
      $subsId = $config->get('subscription_id');

      $wordforsearch="Exhibitions";
      $url = Csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=ExhibitionId';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $headers = array(
      "Accept: application/json",
      "Ocp-Apim-Subscription-Key:$subsKey ",
      "Cache-Control:no-cache",
      'User-Agent: cosydrupal',
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

      $data = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          exit();
      }

    $data = json_decode($data, TRUE);
    return $data['@odata.count'];
  }

  function GetDbExhibitionsCount(){
    try{
      $database = \Drupal::database();

      $table_name =  'collector_systems_exhibitions'; // Use $wpdb->prefix to get the table prefix defined by WordPress

        $query = $database->select($table_name, 'c');
        $count2 = $query->countQuery()->execute()->fetchField();
        return $count2;
    }catch (\Exception $e) {
      \Drupal::logger('collector_systems')->error('Database query error: @message', ['@message' => $e->getMessage()]);
      // If an exception is thrown (e.g., table not found), return 0.
      return 0;
    }
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

  public function artistsLoadMore() {
    $request    = \Drupal::request();
    $config     = \Drupal::config('collector_systems.settings');
    $pageSize   = (int) ($config->get('items_per_page') ?? 9);
    $showImages = $config->get('show_images_artists');
    $modulePath = \Drupal::service('extension.path.resolver')->getPath('module', 'collector_systems');
    $siteUrl    = $request->getSchemeAndHttpHost();

    $pageNo  = max(1, (int) $request->query->get('pageNo', 1));
    $sortBy  = $request->query->get('sortBy', 'ArtistName asc');
    $char    = strtoupper(substr(trim($request->query->get('char', '')), 0, 1));
    $qSearch = trim($request->query->get('qSearch', ''));
    $shskip  = ($pageNo - 1) * $pageSize;

    $sortDir = strpos(strtolower(urldecode($sortBy)), 'desc') !== FALSE ? 'DESC' : 'ASC';

    $db    = Database::getConnection();
    $table = 'collector_systems_artists';

    // Build one query with all conditions + ordering applied, then derive
    // the count query from it, so the displayed total always matches what's
    // actually filtered.
    $query = $db->select($table, 'a');
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

    $countQuery = $query->countQuery();
    $totalCount = (int) $countQuery->execute()->fetchField();

    $query->range($shskip, $pageSize);
    $artists = $query->execute()->fetchAll();

    // Group by first letter.
    $grouped = [];
    foreach ($artists as $artist) {
      $letter = strtoupper(substr($artist->ArtistName, 0, 1));
      $grouped[$letter][] = $artist;
    }
    ($sortDir === 'DESC') ? krsort($grouped) : ksort($grouped);

    // Build HTML fragment — letter headers + card groups only.
    $html = '';
    foreach ($grouped as $letter => $letterArtists) {
      $safeLetter = htmlspecialchars($letter, ENT_QUOTES, 'UTF-8');
      $html .= '<h2 class="letter-header mb-3 mt-4" data-letter="' . $safeLetter . '">' . $safeLetter . '</h2>';
      $html .= '<div class="card-group artists-container row g-4 mb-4" data-letter-group="' . $safeLetter . '">';
      foreach ($letterArtists as $artist) {
        $artistId   = (int) $artist->ArtistId;
        $artistName = htmlspecialchars($artist->ArtistName ?? '', ENT_QUOTES, 'UTF-8');
        $altText    = !empty($artistName) ? $artistName : 'Image description is not available';
        $safeUrl    = htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8');
        $onclick    = 'return getmoredetailsForArtist(\'' . $safeUrl . '\',' . $artistId . ')';

        // Resolve image source.
        $imageSrc = '/' . $modulePath . '/assets/img/artist.png';
        if ($showImages) {
          $serverPath = $artist->ImagePath ?? '';
          $b64data    = $artist->ArtistPhotoAttachment ?? '';
          if (!empty($serverPath)) {
            $segments = explode('/', '/' . $serverPath);
            $imageSrc = implode('/', array_map('rawurlencode', $segments));
          }
          elseif (!empty($b64data)) {
            $imageSrc = 'data:image/jpeg;base64,' . base64_encode($b64data);
          }
        }

        $html .= '<div class="card col-lg-4 col-md-6 col-sm-6 col-12 mb-3">';
        if ($showImages) {
          $safeImg  = htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8');
          $html .= '<div class="card-body d-flex flex-column"><div class="image-wrapper">';
          $html .= '<a href="javascript:;" onclick="' . $onclick . '" class="image-wrapper-link">';
          $html .= '<img class="img-fluid" src="' . $safeImg . '" alt="' . $altText . '"/>';
          $html .= '</a></div></div>';
        }
        $html .= '<div class="card-footer text-muted">';
        $html .= '<a href="javascript:;" onclick="' . $onclick . '">';
        $html .= '<h5 class="card-title font-normal">' . $artistName . '</h5>';
        $html .= '</a></div></div>';
      }
      $html .= '</div>';
    }

    return new JsonResponse([
      'html'       => $html,
      'hasMore'    => $totalCount > ($shskip + $pageSize),
      'totalCount' => $totalCount,
    ]);
  }

}


