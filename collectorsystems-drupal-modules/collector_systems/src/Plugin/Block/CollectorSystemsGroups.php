<?php

namespace Drupal\collector_systems\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\Condition;

/**
 * Provides a custom shortcode block.
 *
 * @Block(
 *   id = "collector_systems_groups",
 *   admin_label = @Translation("Collector Sytems Groups"),
 * )
 */

class CollectorSystemsGroups extends BlockBase {


 /**
   * {@inheritdoc}
   */
  public function build() {

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

    $build['#attached']['library'][] = 'collector_systems/collector-systems';

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
