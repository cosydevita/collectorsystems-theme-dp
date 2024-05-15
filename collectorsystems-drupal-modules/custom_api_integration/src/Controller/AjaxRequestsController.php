<?php

namespace Drupal\custom_api_integration\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\Condition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

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
        $groupLevelSearchHtml = '<div class="card-group row g-5 artist-objects-container mt-5" id="groupLevelObjectsData">';
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
        $groupLevelSearchHtml .= '</div>';
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
        $groupLevelSearchHtml = '<div class="card-group row g-5 artist-objects-container mt-5" id="groupLevelObjectsData">';
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
        $groupLevelSearchHtml .= '</div>';
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
        $groupLevelSearchHtml = '<div class="card-group row g-5 artist-objects-container mt-5" id="groupLevelObjectsData">';
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
        $groupLevelSearchHtml .= '</div>';
      } else {
        $groupLevelSearchHtml .= '<div class="cs-theme-nodata">No results found. Please try another search.</div>';
      }
      $groupLevelSearchHtml.= '<input type="hidden" id="hdnTotalGroupLevelObjectCount" value="'.$obj_count.'"></input>';
    } else if ($Spage == "collection-detail") {

      $collectionID = $groupTypeId;
      // Fetch artist details from the database
      $connection = Database::getConnection();

      // Construct the WHERE clause for LIKE condition on multiple fields
      $where_conditions = new Condition('OR');
      foreach ($customized_fields_array as $field) {
        $where_conditions->condition($field, '%' . $qSearch . '%', 'LIKE');
      }

      //Fetch Collection Detail From Database
      $database = \Drupal::database();


      //Fetch Objects Where CollectionId
      $object_table = 'CSObjects';
      $query = $database->select($object_table, 'co')
        ->fields('co')
        ->condition('co.CollectionId', $collectionID);

      if ($qSearch !== NULL && count($customized_fields_array) > 0) {
        $query->condition($where_conditions);
      }

      $query->range($groupLevelSkipCount, $groupLevelTopCount);

      //sorting
      $this->query_sort_objects_list($groupLevelOrderBy, $qSearch, $query);

      $object_details = $query->execute()->fetchAllAssoc('ObjectId');

      //Count
      $count_query = $query->countQuery();
      $obj_count = $count_query->execute()->fetchField();


      if ($obj_count > 0) {
        $groupLevelSearchHtml = '<div class="card-group row g-5 artist-objects-container mt-5" id="groupLevelObjectsData">';
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
        $groupLevelSearchHtml .= '</div>';
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
}
