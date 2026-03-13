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

    foreach ($results as $row) {
      $sortable[$row->fieldname] = $row->fieldvalue;
    }

    return $sortable;
  }

}
