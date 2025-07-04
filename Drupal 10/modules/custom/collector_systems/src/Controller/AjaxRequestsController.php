<?php

namespace Drupal\collector_systems\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\Condition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;
use Drupal\collector_systems\Csconstants;


class AjaxRequestsController extends ControllerBase
{

  /**
   * The Twig environment.
   *
   * @var \Twig\Environment
   */
  protected $twig;

  public function __construct(Environment $twig)
  {
    $this->twig = $twig;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('twig')
    );
  }


  public function groupLevelObjects_searching_page()
  {

    $groupLevelSearchHtml = "";
    $Spage = strip_tags($_POST['pagename']);

    $groupTypeId = $_POST['groupTypeId'];
    $groupLevelTopCount = $_POST['groupLevelTopCount'];
    $groupLevelSkipCount = $_POST['groupLevelSkipCount'] ?? 0;
    $groupLevelOrderBy = $_POST['groupLevelOrderBy'];
    $groupLevelSearch = trim($_POST['searchWord']);

    $collectionLeftExtent = $_POST['collectionLeftExtent'];
    $collectionRightExtent = $_POST['collectionRightExtent'];

    $groupLevelPageNo = $_POST['groupLevelPageNoValue'] ?? 1;

    $loadsec = 1;
    $customized_fields = $this->getCommaSeperatedFieldsForListPageObject();
    $qSearch = $groupLevelSearch;

    $customized_fields_array = explode(',', $customized_fields);

    if ($Spage == "artist-detail") {
      $artistId = $groupTypeId;
      // Fetch artist details from the database
      $connection = Database::getConnection();

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

      if ($qSearch !== NULL && count($customized_fields_array) > 0) {
        $query->condition($where_conditions);
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
          call_user_func($function, $object_details, $value, $groupLevelOrderBy, $groupLevelPageNo, $qSearch, $delaytm, 'https://cdn.collectorsystems.com/images/noimage300.png');

          // Get the contents of the output buffer (i.e., the output of your function)
          $functionOutput = ob_get_clean();

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

      // Construct the WHERE clause for LIKE condition on multiple fields
      $where_conditions = new Condition('OR');
      foreach ($customized_fields_array as $field) {
        $where_conditions->condition($field, '%' . $qSearch . '%', 'LIKE');
      }

      //Fetch Objects Where ExhibitionId
      $exhibitionObj_table = 'ExhibitionObjects';
      $object_table = 'CSObjects';

      $query = \Drupal::database()->select($exhibitionObj_table, 'eo');
      $query->fields('eo');
      $query->join($object_table, 'co', 'eo.ObjectId = co.ObjectId');
      $query->fields('co');
      $query->condition('eo.ExhibitionId', $exhibitionID);

      if ($qSearch !== NULL && count($customized_fields_array) > 0) {
        $query->condition($where_conditions);
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
          $function = $this->twig->getFunction('getExhibitionObjectsListHtml')->getCallable();

          // Start output buffering
          ob_start();

          // Call the function. Its output will be captured by the output buffer
          call_user_func($function, $value, $groupLevelOrderBy, $groupLevelPageNo, $qSearch, $delaytm, 'https://cdn.collectorsystems.com/images/noimage300.png');

          // Get the contents of the output buffer (i.e., the output of your function)
          $functionOutput = ob_get_clean();

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

      // Construct the WHERE clause for LIKE condition on multiple fields
      $where_conditions = new Condition('OR');
      foreach ($customized_fields_array as $field) {
        $where_conditions->condition($field, '%' . $qSearch . '%', 'LIKE');
      }

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

      if ($qSearch !== NULL && count($customized_fields_array) > 0) {
        $query->condition($where_conditions);
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
          $function = $this->twig->getFunction('getGroupObjectsListHtml')->getCallable();

          // Start output buffering
          ob_start();

          // Call the function. Its output will be captured by the output buffer
          call_user_func($function, $value, $groupLevelOrderBy, $groupLevelPageNo, $qSearch, $delaytm, 'https://cdn.collectorsystems.com/images/noimage300.png');

          // Get the contents of the output buffer (i.e., the output of your function)
          $functionOutput = ob_get_clean();

          $groupLevelSearchHtml .= $functionOutput;
        }
      } else {
        $groupLevelSearchHtml .= '<div class="cs-theme-nodata">No results found. Please try another search.</div>';
      }
      $groupLevelSearchHtml.= '<input type="hidden" id="hdnTotalGroupLevelObjectCount" value="'.$obj_count.'"></input>';
    } else if ($Spage == "collection-detail") {

      $collectionID = $groupTypeId;
      // Fetch artist details from the database
      $connection = Database::getConnection();

    

      //Fetch Collection Detail From Database
      $database = \Drupal::database();


      //Fetch Collection Objects 
      $object_table = 'CSObjects';
      $connection = \Drupal::database();
      $query = $connection->select('CSObjects', 'o');
      $query->innerJoin('Collections', 'c', 'o.CollectionId = c.CollectionId');
      $query->innerJoin('Collections', 'c_target', 'c_target.CollectionId = '.$collectionID);
      $query->fields('o');
      $query->fields('c');

      // WHERE (o.CollectionId = :group_id OR o.ObjectId BETWEEN c_target.LeftExtent AND c_target.RightExtent)
      $or_condition = $query->orConditionGroup()
        ->condition('o.CollectionId', $collectionID)
        ->where('o.ObjectId BETWEEN c_target.LeftExtent AND c_target.RightExtent');

      $query->condition($or_condition);

      if ($qSearch !== NULL && count($customized_fields_array) > 0) {
        // Construct the WHERE clause for LIKE condition on multiple fields
        $where_conditions = new Condition('OR');
        foreach ($customized_fields_array as $field) {
          $where_conditions->condition('o.'.$field, '%' . $qSearch . '%', 'LIKE');
        }
        $query->condition($where_conditions);
      }


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
          call_user_func($function, $object_details, $value, $groupLevelOrderBy, $groupLevelPageNo, $qSearch, $delaytm, 'https://cdn.collectorsystems.com/images/noimage300.png');

          // Get the contents of the output buffer (i.e., the output of your function)
          $functionOutput = ob_get_clean();

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

    $tblnm = "clsobjects_fields";
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

    $collection_table =  $connection->prefixTables('Collections');

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
        return $connection->query("SELECT COUNT(*) FROM {ThumbImages}")->fetchField();

      case 'artists_images':
        return $connection->query("
          SELECT COUNT(*) 
          FROM {Artists} 
          WHERE 
            (ArtistPhotoAttachment IS NOT NULL AND ArtistPhotoAttachment != '') 
            OR 
            (ImagePath IS NOT NULL AND ImagePath != '')
        ")->fetchField();

      case 'collections_images':
        return $connection->query("
          SELECT COUNT(*) 
          FROM {Collections} 
          WHERE 
            (CollectionImageAttachment IS NOT NULL AND CollectionImageAttachment != '') 
            OR 
            (ImagePath IS NOT NULL AND ImagePath != '')
        ")->fetchField();

      case 'exhibitions_images':
        return $connection->query("
          SELECT COUNT(*) 
          FROM {Exhibitions} 
          WHERE 
            (ExhibitionImageAttachment IS NOT NULL AND ExhibitionImageAttachment != '') 
            OR 
            (ImagePath IS NOT NULL AND ImagePath != '')
        ")->fetchField();

      case 'groups_images':
        return $connection->query("
          SELECT COUNT(*) 
          FROM {Groups} 
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


}


