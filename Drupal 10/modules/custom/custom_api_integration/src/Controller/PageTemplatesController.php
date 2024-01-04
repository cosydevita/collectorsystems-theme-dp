<?php

namespace Drupal\custom_api_integration\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
class PageTemplatesController extends ControllerBase
{
  public function ObjectsListPage(){
    $showrec = 9;
    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $shskip = 0;
    $ajaxfor = "artobjects";
    $current_page = "objects";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "Title%20desc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";

    $requested_pageNo = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $shskip = ($requested_pageNo - 1) * $showrec;

    $nxshowrec = isset($listPageSize) ? $listPageSize : 9;
    $nxshskip = $shskip;
    $loadsec = 1;

    // $customized_fields = getCommaSeparatedFieldsForListPage();
    $customized_fields = "title"; //temp only

    // echo "field:" . $customized_fields;
    $customized_fields_array = explode(',', $customized_fields);

    // Count Total Objects
    $object_table = 'CSObjects';
    // $count = Database::getConnection()->select($object_table, 'o')
    //   ->fields('o', ['COUNT(*)'])
    //   ->execute()
    //   ->fetchField();
    $count = Database::getConnection()->select($object_table)
    ->countQuery()
    ->execute()
    ->fetchField();

  $count = (int) $count;

    // Collection Table
    $collection_table = 'Collections';

    // Fetch object details from the database
    $query = Database::getConnection()->select($object_table, 'o');

    if ($dataorderby === "Title%20desc" && $qSearch !== NULL) {
      $query->condition('Title', '%' . Database::getConnection()->escapeLike($qSearch) . '%', 'LIKE');
      $query->orderBy('Title', 'DESC');
    }
    // Add other conditions for different order by options and search criteria

    // Ensure the SELECT clause includes all necessary columns
    $query->fields('o');

    // foreach ($customized_fields_array as $field) {
    //   $query->orderBy($field);
    // }

    $query->range($shskip, $showrec);

    $result = $query->execute();
    $object_details = $result->fetchAllAssoc('ObjectId'); // Assuming 'ObjectId' is the primary key field

    // echo "<pre>";
    // print $query->__toString();
    // print_r($object_details);

    // die();

    $build = [
      '#theme' => 'objects-list-page',
      '#object_details' => $object_details,
      '#nxshowrec' => $nxshowrec,
      '#nxshskip' => $nxshskip,
      '#count' => $count,
      '#dataorderby' => $dataorderby,
      '#current_page' => $current_page,
      '#requested_pageNo' => $requested_pageNo,
      '#qSearch' => $qSearch,
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];


    return $build;



  }

  public function ArtistsListPage(){

    $showrec = 9;
    $listPageSize = isset($listPageSize) ? $listPageSize : 9;
    $shskip = 0;
    $ajaxfor = "artist";
    $current_page = "artists";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "ArtistName%20desc";
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
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];


    return $build;

  }

  public function ExhibitionsListPage(){
    $showrec=   9;
    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $shskip =   0;
    $ajaxfor=   "listexhibition";
    $current_page=   "exhibitions";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "ExhibitionSubject%20desc";
    $qSearch = isset($_REQUEST['qSearch']) ? $_REQUEST['qSearch'] : "";

    $requested_pageNo = isset($_REQUEST['pageNo']) ? intval($_REQUEST['pageNo']) : 1;
    $shskip = ($requested_pageNo - 1) * $showrec;
    $nxshowrec=   isset($listPageSize) ? $listPageSize : 9;
    $nxshskip =   $shskip;
    $loadsec=1;

    //Fetch Exhibition Data from Database
    $exhibition_table = "Exhibitions";
    $connection = Database::getConnection();
    $count = $connection->query("SELECT COUNT(*) FROM $exhibition_table")->fetchField();


    $database = Database::getConnection();


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
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];


    return $build;

  }

  public function GroupsListPage(){

    $showrec=   9;
    $showrec = isset($listPageSize) ? $listPageSize : 9;
    $shskip =   0;
    $ajaxfor=   "listgroup";
    $current_page=   "groups";
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "GroupDescription%20desc";
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
      '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.

    ];


    return $build;

  }


  public function drupal_selected($value, $current_value, $echo = true) {
    $selected = $value == $current_value ? 'selected="selected"' : '';

    if ($echo) {
        echo $selected;
    }

    return $selected;
  }
}
