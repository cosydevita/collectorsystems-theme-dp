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
 *   id = "collector_systems_objects",
 *   admin_label = @Translation("Collector Sytems Objects"),
 * )
 */

class CollectorSystemsObjects extends BlockBase {


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

    $listPageSize =  \Drupal::config('custom_api_integration.settings')->get('items_per_page');

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

    $count_query = $query->countQuery();
    $total_results = $count_query->execute()->fetchField();

    $query->range($shskip, $showrec);

    $result = $query->execute();
    $object_details = $result->fetchAllAssoc('ObjectId'); // Assuming 'ObjectId' is the primary key field


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
