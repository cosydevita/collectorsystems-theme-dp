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
 *   id = "collector_systems_exhibitions",
 *   admin_label = @Translation("Collector Sytems Exhibitions"),
 * )
 */

class CollectorSystemsExhibitions extends BlockBase {


 /**
   * {@inheritdoc}
   */
  public function build() {
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
