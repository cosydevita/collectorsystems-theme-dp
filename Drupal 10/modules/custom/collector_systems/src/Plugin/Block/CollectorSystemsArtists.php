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
 *   id = "collector_systems_artists",
 *   admin_label = @Translation("Artists"),
 * )
 */

class CollectorSystemsArtists extends BlockBase {


 /**
   * {@inheritdoc}
   */
  public function build() {
    if(!$this->is_CS_tables_exists()){
      $build = [
        '#theme' => 'artists-list-page',
        '#cache' => ['max-age' => 0,],    //Set cache for 0 seconds.
      ];
      return $build;
    }

    $listPageSize =  \Drupal::config('collector_systems.settings')->get('items_per_page');
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
    $collector_systems_module_path = \Drupal::service('extension.path.resolver')->getPath('module', 'collector_systems');
    $showImagesOnListPages =  \Drupal::config('collector_systems.settings')->get('show_images_on_list_pages');
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
      '#collector_systems_module_path' => $collector_systems_module_path,
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
