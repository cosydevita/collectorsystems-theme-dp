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
 *   id = "collector_systems_collections",
 *   admin_label = @Translation("Collector Sytems Collections"),
 * )
 */

class CollectorSystemsCollections extends BlockBase {


 /**
   * {@inheritdoc}
   */
  public function build() {

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
    $dataorderby = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : "FullCollectionName%20desc";
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
