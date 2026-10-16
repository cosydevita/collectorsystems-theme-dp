<?php

namespace Drupal\collector_systems;

use Drupal\Core\Database\Connection;

class ObjectsService {

  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public function getObjectListSortableFields() {

    $query = $this->database->select('collector_systems_clsobjects_fields', 'c')
      ->fields('c', ['fieldname', 'fieldvalue'])
      ->condition('fieldtype', 'ObjectList');

    $results = $query->execute()->fetchAll();

    $sortable = [];
    $object_columns = $this->cs_get_table_columns('collector_systems_objects');
    $artist_columns = $this->cs_get_table_columns('collector_systems_artists');
    $collection_columns = $this->cs_get_table_columns('collector_systems_collections');

    foreach ($results as $row) {
      // Skip options whose sort column does not exist.
      switch ($row->fieldname) {
        case 'ArtistName':
          $available = in_array('ArtistName', $artist_columns, TRUE);
          break;

        case 'FullCollectionName':
          $available = in_array('CollectionName', $collection_columns, TRUE);
          break;

        case 'AdditionalArtists':
          $available = in_array('AdditionalArtistsText', $object_columns, TRUE);
          break;

        case 'AdditionalArtistMakers':
          $available = in_array('AdditionalArtistMakersText', $object_columns, TRUE);
          break;

        default:
          $available = in_array($row->fieldname, $object_columns, TRUE);
      }
      if (!$available) {
        continue;
      }
      $sortable[$row->fieldname] = Csfieldresolver::getLabelOverride('object_list', $row->fieldname) ?? $row->fieldvalue;
    }

    return $sortable;
  }

  /**
   * Returns the column names of a given table.
   *
   * @param string $table_name
   *   The unprefixed table name.
   *
   * @return array
   *   A simple array of column names.
   */
  public function cs_get_table_columns($table_name) {
    $prefix = $this->database->getPrefix();
    $prefixed_table = $prefix . $table_name;

    try {
      // Use SHOW COLUMNS — faster and no INFORMATION_SCHEMA permission issues.
      $result = $this->database->query("SHOW COLUMNS FROM `$prefixed_table`")->fetchAll();
      return array_map(static fn($row) => $row->Field, $result);
    }
    catch (\Exception $e) {
      \Drupal::logger('collector_systems')->error(
        'Error fetching columns for table @table: @message',
        ['@table' => $prefixed_table, '@message' => $e->getMessage()]
      );
      return [];
    }
  }

}
