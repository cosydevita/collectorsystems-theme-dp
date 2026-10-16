<?php

namespace Drupal\collector_systems;
use Drupal\Core\Database\Database;
use Drupal\collector_systems\Csconstants;
use Drupal\Core\File\FileSystemInterface;
use Drupal\collector_systems\CollectorSystemsGetApiData;

class DataSyncManager {

  protected $fileSystem;
  protected $collectorSystemsGetApiData;

  public function __construct( FileSystemInterface $fileSystem, CollectorSystemsGetApiData $collectorSystemsGetApiData) {
    $this->fileSystem = $fileSystem;
    $this->collectorSystemsGetApiData = $collectorSystemsGetApiData;
  }
  /**
   * Get the data for batch processing.
   */
  public function getDataForProcessing()
  {

    $chunk_size = 20; // number of objects to process in a batch

    $collector_systemsts_get_api_data = \Drupal::service('collector_systems.collector_systemsts_get_api_data');
    $import_types = [
      'Artists',
      'Objects',
      'Collections',
      'Exhibitions',
      'Groups',
      'ExhibitionsObjects',
      'GroupsObjects',
    ];

    $config_collector_systems = \Drupal::config('collector_systems.settings');
    $checkbox_groups = $config_collector_systems->get('checkboxes.groups');
    $checkbox_collections = $config_collector_systems->get('checkboxes.collections');
    $checkbox_exhibitions = $config_collector_systems->get('checkboxes.exhibitions');
    $checkbox_artists = $config_collector_systems->get('checkboxes.artists');

    // Do not import if checkbox is not checked.
    if($checkbox_groups == 0){
      unset($import_types[array_search('Groups', $import_types)]);
      unset($import_types[array_search('GroupsObjects', $import_types)]);
    }
    if($checkbox_collections == 0){
      unset($import_types[array_search('Collections', $import_types)]);      
    }
    if($checkbox_exhibitions == 0){
      unset($import_types[array_search('Exhibitions', $import_types)]);
      unset($import_types[array_search('ExhibitionsObjects', $import_types)]);
    }
    if($checkbox_artists == 0){
      unset($import_types[array_search('Artists', $import_types)]);
    }
    


    $data = [];

    foreach ($import_types as $import_type) {
      if ($import_type == 'Artists') {
        $total_count = $collector_systemsts_get_api_data->getTotalArtistsCount();
      } elseif ($import_type == 'Objects') {
        $total_count = $collector_systemsts_get_api_data->getApiTotalObjectsCount();
      } elseif ($import_type == 'Collections') {
        $total_count = $collector_systemsts_get_api_data->getTotalCollectionsCount();
      } elseif ($import_type == 'Groups') {
        $total_count = $collector_systemsts_get_api_data->getTotalGroupsCount();
      } elseif ($import_type == 'Exhibitions') {
        $total_count = $collector_systemsts_get_api_data->getTotalExhibitionsCount();
      } elseif ($import_type == 'GroupsObjects') {
        $total_count = $collector_systemsts_get_api_data->getTotalGroupsObjectsCount();
      } elseif ($import_type == 'ExhibitionsObjects') {
        $total_count = $collector_systemsts_get_api_data->getTotalExhibitionsObjectsCount();
      }

      if ($total_count > 0) {
        $total_chunks = ceil($total_count / $chunk_size);
        for ($i = 0; $i < $total_chunks; $i++) {
          // Calculate the offset for each API call
          $offset = $i * $chunk_size;
          $data[] = [
            'total_chunks' => $total_chunks,
            'chunk_size' => $chunk_size,
            'offset' => $offset,
            'current_batch_number' => $i,
            'import_type' => $import_type
          ];
        }
      }
    }

    return $data;
  }

  public function processImportArtists($Detaildata, $btn_action)
  {
    \Drupal::logger('collector_systems')->debug('Start process Import Artists.');
    $database = Database::getConnection();
    $table_name = 'collector_systems_artists';
    $ArtistData = $Detaildata;

    $artistIds_API = [];
    //Start Artists
    foreach ($ArtistData['value'] as $art) {

      $artistId = $art['ArtistId'];
      $artistIds_API[] = $artistId;
      $artistName = NULL;
      $artistFirst = NULL;
      $artistLast = NULL;
      $artistYears = NULL;
      $artistNationality = NULL;
      $artistLocale = NULL;
      $artistBio = NULL;
      if (isset($art['ArtistName']) && $art['ArtistName'] !== NULL) {
        $artistName = $art['ArtistName'];
      }
      if (isset($art['ArtistFirst']) && $art['ArtistFirst'] !== NULL) {
        $artistFirst = $art['ArtistFirst'];
      }
      if (isset($art['ArtistLast']) && $art['ArtistLast'] !== NULL) {
        $artistLast = $art['ArtistLast'];
      }
      if (isset($art['ArtistYears']) && $art['ArtistYears'] !== NULL) {
        $artistYears = $art['ArtistYears'];
      }
      if (isset($art['ArtistNationality']) && $art['ArtistNationality'] !== NULL) {
        $artistNationality = $art['ArtistNationality'];
      }
      if (isset($art['ArtistLocale']) && $art['ArtistLocale'] !== NULL) {
        $artistLocale = $art['ArtistLocale'];
      }
      if (isset($art['ArtistBio']) && $art['ArtistBio'] !== NULL) {
        $artistBio = $art['ArtistBio'];
      }
      if (isset($art['ModificationDate']) && $art['ModificationDate'] !== NULL) {
        $ModificationDate = $art['ModificationDate'];
      } elseif (isset($art['CreationDate']) && $art['CreationDate'] !== NULL) {
        $ModificationDate = $art['CreationDate'];
      }
      if ($artistId !== 0) {
        // Prepare the data for insertion.
        $data = [
          'ArtistId' => $artistId,
          'ArtistName' => $artistName,
          'ArtistFirst' => $artistFirst,
          'ArtistLast' => $artistLast,
          'ArtistYears' => $artistYears,
          'ArtistNationality' => $artistNationality,
          'ArtistLocale' => $artistLocale,
          'ArtistBio' => $artistBio,
          'ModificationDate' => $ModificationDate
        ];
        if ($btn_action == 'update_dataset') {
          // Check if the record exists.
          $record_exists = $database->select($table_name)
            ->fields($table_name)
            ->condition('ArtistId', $artistId)
            ->execute()
            ->fetchAssoc();

          if ($record_exists) {
            // Update the existing record if the ModificationDate has changed
            $database->update($table_name)
              ->fields($data)
              ->condition('ArtistId', $artistId)
              ->condition('ModificationDate', $ModificationDate, '<>')
              ->execute();
          } else {
            // Handle if record doesn't exist
            // Insert data into the table.
            $result = $database->insert($table_name)
              ->fields($data)
              ->execute();
          }
        } else {
          // Insert data into the table.
          $result = $database->insert($table_name)
            ->fields($data)
            ->execute();
        }
      }
    } //End Artists

    if ($artistIds_API) {
      // @todo
      // $this->remove_unrequired_Artists_from_Database($artistIds_API);
    }
  }

  public function processImportObjects($Detaildata, $btn_action)
  {
    \Drupal::logger('collector_systems')->debug('Start process Import Objects.');

    $data1 = $Detaildata; //End Object's API Data

    $table_name = 'collector_systems_objects';
    $database = Database::getConnection();
    $field_names = $field_names_array = $this->get_field_names(); //temp test


    $objectIds_API = [];
    foreach ($data1['value'] as $value) {
      $combinedObjectValues = [];
      if (!empty($value['Address']['Latitude'])) {
        $combinedObjectValues['Latitude'] = $value['Address']['Latitude'];
      } else {
        $combinedObjectValues['Latitude'] = '';
      }
      if (!empty($value['Address']['Longitude'])) {
        $combinedObjectValues['Longitude'] = $value['Address']['Longitude'];
      } else {
        $combinedObjectValues['Longitude'] = '';
      }
      if (!empty($value['Address']['AddressName'])) {
        $combinedObjectValues['AddressName'] = $value['Address']['AddressName'];
      } else {
        $combinedObjectValues['AddressName'] = '';
      }


      foreach ($field_names as $field_name) {
        $resolved = $this->resolveFieldValue($field_name, $value);
        if ($resolved !== NULL) {
          $combinedObjectValues[$field_name] = $resolved;
        } else {
          // DO NOT DELETE this fallback. resolveFieldValue() returns NULL
          // for the ~239 fields that need no special handling (their raw
          // API value sits under a key with the same name as the field
          // itself, e.g. $value['Title'] for Title), and this is what
          // actually reads and stores them. Removing this "because
          // resolveFieldValue() already handles everything" would silently
          // blank out the majority of synced fields.
          if (!empty($value[$field_name])) {
            $combinedObjectValues[$field_name] = $value[$field_name];
          } else {
            $combinedObjectValues[$field_name] = "";
          }
        }
      }

      $id1 = $value['ObjectId'];

      $objectIds_API[] = $id1;

      $imgId1 = NULL;
      if (isset($value['MainImageAttachmentId']) && $value['MainImageAttachmentId'] !== NULL) {
        $imgId1 = $value['MainImageAttachmentId'];
      }
      $artistId = 0;
      if (isset($value['ArtistId']) && $value['ArtistId'] != NULL) {
        $artistId = $value['ArtistId'];
      }
      if (isset($value['Artist']['ArtistId']) && $value['Artist']['ArtistId'] != NULL) {
        $artistId = $value['Artist']['ArtistId'];
      }
      $title = NULL;
      if (isset($value['Title']) && $value['Title'] != NULL) {
        $title = $value['Title'];
      }
      $inventNumber = NULL;
      if (isset($value['InventoryNumber']) && $value['InventoryNumber'] != NULL) {
        $inventNumber = $value['InventoryNumber'];
      }

      $objectDate = NULL;
      if (isset($value['ObjectDate']) && $value['ObjectDate'] != NULL) {
        $objectDate = $value['ObjectDate'];
      }

      $collectionId = 0;
      if (isset($value['CollectionId']) && $value['CollectionId'] != NULL) {
        $collectionId = $value['CollectionId'];
      }
      if (isset($value['Collection']['CollectionId']) && $value['Collection']['CollectionId'] != NULL) {
        $collectionId = $value['Collection']['CollectionId'];
      }
      if (isset($value['ModificationDate']) && $value['ModificationDate'] !== NULL) {
        $ModificationDate = $value['ModificationDate'];
      } elseif (isset($value['CreationDate']) && $value['CreationDate'] !== NULL) {
        $ModificationDate = $value['CreationDate'];
      }


      // Derive flat text mirrors of AdditionalArtists/ArtistMakers/Authors/Creators/Publishers for
      // sorting/searching/display, matching the raw JSON columns above.
      if (in_array('AdditionalAuthors', $field_names)) {
        $AdditionalAuthorsText = '';
        if (isset($value['AdditionalAuthors']) && is_array($value['AdditionalAuthors']) && count($value['AdditionalAuthors']) > 0) {
          foreach ($value['AdditionalAuthors'] as $additional_author) {
            if (isset($additional_author['Author']['AuthorName'])) {
              $AdditionalAuthorsText .= $additional_author['Author']['AuthorName'] . ', ';
            }
          }
        }
        if (!empty($AdditionalAuthorsText)) {
          $combinedObjectValues['AdditionalAuthorsText'] = rtrim($AdditionalAuthorsText, ', ');
        }
      }

      if (in_array('AdditionalCreators', $field_names)) {
        $AdditionalCreatorsText = '';
        if (isset($value['AdditionalCreators']) && is_array($value['AdditionalCreators']) && count($value['AdditionalCreators']) > 0) {
          foreach ($value['AdditionalCreators'] as $additional_creator) {
            if (isset($additional_creator['Creator']['ContactName'])) {
              $AdditionalCreatorsText .= $additional_creator['Creator']['ContactName'] . ', ';
            }
          }
        }
        if (!empty($AdditionalCreatorsText)) {
          $combinedObjectValues['AdditionalCreatorsText'] = rtrim($AdditionalCreatorsText, ', ');
        }
      }

      if (in_array('AdditionalPublishers', $field_names)) {
        $AdditionalPublishersText = '';
        if (isset($value['AdditionalPublishers']) && is_array($value['AdditionalPublishers']) && count($value['AdditionalPublishers']) > 0) {
          foreach ($value['AdditionalPublishers'] as $additional_publisher) {
            if (isset($additional_publisher['Publisher']['ContactName'])) {
              $AdditionalPublishersText .= $additional_publisher['Publisher']['ContactName'] . ', ';
            }
          }
        }
        if (!empty($AdditionalPublishersText)) {
          $combinedObjectValues['AdditionalPublishersText'] = rtrim($AdditionalPublishersText, ', ');
        }
      }

      if (in_array('AdditionalArtists', $field_names)) {
        $AdditionalArtistsText = '';
        if (isset($value['AdditionalArtists']) && is_array($value['AdditionalArtists'])) {
          foreach ($value['AdditionalArtists'] as $additional_artist) {
            if (isset($additional_artist['Artist']['ArtistName'])) {
              $AdditionalArtistsText .= $additional_artist['Artist']['ArtistName'] . ', ';
            }
          }
        }
        if (!empty($AdditionalArtistsText)) {
          $combinedObjectValues['AdditionalArtistsText'] = rtrim($AdditionalArtistsText, ', ');
        }
      }

      if (in_array('AdditionalArtistMakers', $field_names)) {
        $AdditionalArtistMakersText = '';
        if (isset($value['AdditionalArtistMakers']) && is_array($value['AdditionalArtistMakers'])) {
          foreach ($value['AdditionalArtistMakers'] as $additional_maker) {
            if (isset($additional_maker['ArtistMaker']['ArtistName'])) {
              $AdditionalArtistMakersText .= $additional_maker['ArtistMaker']['ArtistName'] . ', ';
            }
          }
        }
        if (!empty($AdditionalArtistMakersText)) {
          $combinedObjectValues['AdditionalArtistMakersText'] = rtrim($AdditionalArtistMakersText, ', ');
        }
      }

      // Create an associative array with field-value pairs
      $values = array(
        'ObjectId' => $id1,
        'Title' => $title,
        'InventoryNumber' => $inventNumber,
        'ObjectDate' => $objectDate,
        'MainImageAttachmentId' => $imgId1,
        'ArtistId' => $artistId,
        'CollectionId' => $collectionId,
        'ModificationDate' => $ModificationDate,
      );

      // If $combinedObjectValues is not empty, add its values to the $values array
      if (!empty($combinedObjectValues)) {
        $values = array_merge($values, $combinedObjectValues);
      }


      if ($btn_action == 'update_dataset') {
        // Check if the record exists.
        $record_exists = $database->select($table_name)
          ->fields($table_name)
          ->condition('ObjectId', $id1)
          ->execute()
          ->fetchAssoc();

        if ($record_exists) {
          // Update the existing record if the ModificationDate has changed
          $database->update($table_name)
            ->fields($values)
            ->condition('ObjectId', $id1)
            ->condition('ModificationDate', $ModificationDate, '<>')
            ->execute();
        } else {
          // Handle if record doesn't exist
          // Insert data into the table.
          $database->insert($table_name)
            ->fields($values)
            ->execute();
        }
      } else {
        // Perform the database insert
        $database->insert($table_name)
          ->fields($values)
          ->execute();
      }
    } //End Objects



    if ($objectIds_API) {
      //@todo
      // $this->remove_unrequired_Objects_from_Database($objectIds_API);
    }
  }

  /**
   * Resolves the database value for a single synced object field, driven by
   * Csfieldregistry -- the same category/lookup data the render side
   * (Csfieldresolver) trusts. Replaces what used to be a 404-case switch:
   * most of that switch was either a plain passthrough identical to the
   * field's own default behavior (so this returns NULL for those, letting
   * the caller fall back to the same plain passthrough-or-empty-string
   * logic every field already had), or a single nested-object lookup now
   * expressed as data in Csfieldregistry::SYNC_NESTED_LOOKUP_MAP instead of
   * hand-written code.
   *
   * @param string $field_name
   *   A Csconstants::X field key.
   * @param array $value
   *   The current object's raw API response row.
   *
   * @return string|null
   *   The resolved value, or NULL if this field isn't handled generically
   *   (the caller should fall back to plain passthrough-or-empty in that case).
   *   IMPORTANT: returning NULL here is intentional and required for the
   *   ~239 passthrough fields (they have no case here at all -- see the
   *   caller's own DO-NOT-DELETE comment in processImportObjects()). Do not
   *   "complete" this method by adding a default/generic branch that
   *   returns $value[$field_name] instead of NULL, or the caller's
   *   passthrough fallback becomes dead code without anyone removing it.
   */
  private function resolveFieldValue(string $field_name, array $value): ?string {
    switch (Csfieldresolver::getCategory($field_name)) {
      case 'date':
        return !empty($value[$field_name]) ? date('m/d/Y', strtotime($value[$field_name])) : '';

      case 'boolean':
        // Fixes a dead branch in the original per-field cases: they gated
        // on !empty(), which is already implied by == true, so the 'No'
        // branch could never be reached and a false value always stored as
        // '' instead of 'No'. Gating on isset() instead lets a genuinely
        // present-but-falsy API value correctly resolve to 'No'.
        return isset($value[$field_name]) ? (($value[$field_name] == TRUE) ? 'Yes' : 'No') : '';

      case 'additional_array':
      case 'additional_text':
      case 'udf_controlled_vocab':
        // All three categories store the raw API array as JSON on the sync
        // side (the render-side distinction between additional_array's
        // direct JSON-decode and additional_text's/udf_controlled_vocab's
        // Text-column preference doesn't apply here -- this switch only
        // ever wrote the JSON column itself).
        return (isset($value[$field_name]) && is_array($value[$field_name]) && count($value[$field_name]) > 0)
          ? json_encode($value[$field_name])
          : json_encode([]);
    }

    if (isset(Csfieldregistry::SYNC_NESTED_LOOKUP_MAP[$field_name])) {
      $cursor = $value;
      foreach (Csfieldregistry::SYNC_NESTED_LOOKUP_MAP[$field_name] as $segment) {
        if (!isset($cursor[$segment])) {
          return '';
        }
        $cursor = $cursor[$segment];
      }
      return $cursor;
    }

    return NULL;
  }

  public function processImportCollections($Detaildata, $btn_action)
  {
    $data2 = $Detaildata; //End Collection's API Data
    $database = Database::getConnection();
    $table_name2 = 'collector_systems_collections';

    $collectionIds_API = [];
    //Start Collections
    foreach ($data2['value'] as $collection) {
      $collectionId = $collection['CollectionId'];
      $collectionIds_API[] = $collectionId;
      $collectionName = $collection['CollectionName'];
      $collectionFullName = $collection['FullCollectionName'];
      $collectionLeftExtent = isset($collection['LeftExtent']) ? $collection['LeftExtent'] : null;
      $collectionRightExtent = isset($collection['RightExtent']) ? $collection['RightExtent'] : null;
      $collectionMemo = NULL;
      if (isset($collection['CollectionMemo']) && $collection['CollectionMemo'] !== NULL) {
        $collectionMemo = $collection['CollectionMemo'];
      }

      if (isset($collection['ModificationDate']) && $collection['ModificationDate'] !== NULL) {
        $ModificationDate = $collection['ModificationDate'];
      } elseif (isset($collection['CreationDate']) && $collection['CreationDate'] !== NULL) {
        $ModificationDate = $collection['CreationDate'];
      }

      if ($collectionId !== 0 && $collectionName !== null && $collectionFullName !== null) {
        // $sql2 = $wpdb->prepare("INSERT INTO $table_name2 (CollectionId , CollectionName , FullCollectionName) VALUES (%d , %s , %s)", $collectionId ,  $collectionName , $collectionFullName);
        // $wpdb->query($sql2);
        // Define the data to be inserted.
        $data = [
          'CollectionId' => $collectionId,
          'CollectionName' => $collectionName,
          'FullCollectionName' => $collectionFullName,
          'ModificationDate' => $ModificationDate,
          'LeftExtent' => $collectionLeftExtent,
          'RightExtent' => $collectionRightExtent,
          'CollectionMemo' => $collectionMemo
        ];

        if ($btn_action == 'update_dataset') {
          // Check if the record exists.
          $record_exists = $database->select($table_name2)
            ->fields($table_name2)
            ->condition('CollectionId', $collectionId)
            ->execute()
            ->fetchAssoc();

          if ($record_exists) {
            // Update the existing record if the ModificationDate has changed
            $database->update($table_name2)
              ->fields($data)
              ->condition('CollectionId', $collectionId)
              ->condition('ModificationDate', $ModificationDate, '<>')
              ->execute();
          } else {
            // Handle if record doesn't exist
            // Insert data into the table.
            $database->insert($table_name2)
              ->fields($data)
              ->execute();
          }
        } else {
          // Insert data into the table using the database API.
          $database->insert($table_name2)
            ->fields($data)
            ->execute();
        }
      }
    } //End Collections

    //remove unrequired data from the database which does not exist in API
    if ($collectionIds_API) {

      //@todo
      // $this->remove_unrequired_Collections_from_Database($collectionIds_API);
    }
  }

  public function processImportExhibitions($Detaildata, $btn_action)
  {

    $data4 = $Detaildata; //End Exhibition's API Data
    $database = Database::getConnection();
    $table_name4 = 'collector_systems_exhibitions';
    $exhibitionIds_API = [];
    //Start Exhibitions
    foreach ($data4['value'] as $exhibition) {
      $exhibitionId = $exhibition['ExhibitionId'];
      $exhibitionIds_API[] = $exhibitionId;
      $exhibitionSubject = $exhibition['ExhibitionSubject'];
      $exhibitionLocation = NULL;
      if (isset($exhibition['ExhibitionLocation']) && $exhibition['ExhibitionLocation'] !== NULL) {
        $exhibitionLocation = $exhibition['ExhibitionLocation'];
      }
      $exhibitionDate = $exhibition['ExhibitionDate'];
      
      // Normalize the actual DB fields because the ExhibitionStartDate and ExhibitionEndDate are datetime fields in DB but coming in API as different format. We need to normalize them before inserting into DB otherwise it will give error.
      $ExhibitionStartDate = $this->normalizeDatetime($exhibition['ExhibitionStartDate']);
      $ExhibitionEndDate   = $this->normalizeDatetime($exhibition['ExhibitionEndDate']);

      $exhibitionMemo = NULL;
      if (isset($exhibition['ExhibitionMemo']) && $exhibition['ExhibitionMemo'] !== NULL) {
        $exhibitionMemo = $exhibition['ExhibitionMemo'];
      }

      if (isset($exhibition['ModificationDate']) && $exhibition['ModificationDate'] !== NULL) {
        $ModificationDate = $exhibition['ModificationDate'];
      } elseif (isset($exhibition['CreationDate']) && $exhibition['CreationDate'] !== NULL) {
        $ModificationDate = $exhibition['CreationDate'];
      }


      if ($exhibitionId !== null) {
        // $sql4 = $wpdb->prepare("INSERT INTO $table_name4(ExhibitionId , ExhibitionSubject , ExhibitionLocation , ExhibitionDate , ExhibitionMemo) VALUES (%d , %s , %s , %s , %s)", $exhibitionId ,  $exhibitionSubject , $exhibitionLocation , $exhibitionDate , $exhibitionMemo );
        // $wpdb->query($sql4);
        $data = array(
          'ExhibitionId' => $exhibitionId,
          'ExhibitionSubject' => $exhibitionSubject,
          'ExhibitionLocation' => $exhibitionLocation,
          'ExhibitionDate' => $exhibitionDate,
          'ExhibitionStartDate' => $ExhibitionStartDate,
          'ExhibitionEndDate' => $ExhibitionEndDate,
          'ExhibitionMemo' => $exhibitionMemo,
          'ModificationDate' => $ModificationDate,
        );

        if ($btn_action == 'update_dataset') {
          // Check if the record exists.
          $record_exists = $database->select($table_name4)
            ->fields($table_name4)
            ->condition('ExhibitionId', $exhibitionId)
            ->execute()
            ->fetchAssoc();

          if ($record_exists) {
            try {
            // Update the existing record if the ModificationDate has changed
            $database->update($table_name4)
              ->fields($data)
              ->condition('ExhibitionId', $exhibitionId)
              ->condition('ModificationDate', $ModificationDate, '<>')
              ->execute();

            } catch (\Exception $e) {
              \Drupal::logger('collector_systems')->error('Error updating ExhibitionId ' . $exhibitionId . ': ' . $e->getMessage());
            }
          } else {
            try {
              // Handle if record doesn't exist
              // Insert data into the table.
              $database->insert($table_name4)
                ->fields($data)
                ->execute();
            } catch (\Exception $e) {
              \Drupal::logger('collector_systems')->error('Error inserting ExhibitionId ' . $exhibitionId . ': ' . $e->getMessage());
            }
          }
        } else {
          try {
            $database->insert($table_name4)
              ->fields($data)
              ->execute();
          } catch (\Exception $e) {
            \Drupal::logger('collector_systems')->error('Error inserting ExhibitionId ' . $exhibitionId . ': ' . $e->getMessage());
          }
        }
      }
    } //End Exhibitions
    //remove unrequired data from the database which does not exist in API
    if ($exhibitionIds_API) {
      //@todo
      // $this->remove_unrequired_Exhibitions_from_Database($exhibitionIds_API);
    }
  }

  public function processImportGroups($Detaildata, $btn_action)
  {
    $database = Database::getConnection();
    $table_name3 = 'collector_systems_groups';

    $groupIds_API = [];

    foreach ($Detaildata['value'] as $group) {
      $groupId = $group['GroupId'];

      $groupIds_API[] = $groupId;

      $groupDescription = $group['GroupDescription'];
      $groupMemo = NULL;
      if (isset($group['GroupMemo']) && $group['GroupMemo'] !== NULL) {
        $groupMemo = $group['GroupMemo'];
      }

      if (isset($group['ModificationDate']) && $group['ModificationDate'] !== NULL) {
        $ModificationDate = $group['ModificationDate'];
      } elseif (isset($group['CreationDate']) && $group['CreationDate'] !== NULL) {
        $ModificationDate = $group['CreationDate'];
      }


      if ($groupId !== 0) {
        // $sql3 = $wpdb->prepare("INSERT INTO $table_name3 (GroupId , GroupDescription , GroupMemo) VALUES (%d , %s , %s)", $groupId ,  $groupDescription , $groupMemo);
        // $wpdb->query($sql3);
        // Define the data to be inserted.
        $data = array(
          'GroupId' => $groupId,
          'GroupDescription' => $groupDescription,
          'GroupMemo' => $groupMemo,
          'ModificationDate' => $ModificationDate,

        );

        if ($btn_action == 'update_dataset') {
          // Check if the record exists.
          $record_exists = $database->select($table_name3)
            ->fields($table_name3)
            ->condition('GroupId', $groupId)
            ->execute()
            ->fetchAssoc();

          if ($record_exists) {
            // Update the existing record if the ModificationDate has changed
            $database->update($table_name3)
              ->fields($data)
              ->condition('GroupId', $groupId)
              ->condition('ModificationDate', $ModificationDate, '<>')
              ->execute();
          } else {
            // Handle if record doesn't exist
            // Insert data into the table.
            $database->insert($table_name3)
              ->fields($data)
              ->execute();
          }
        } else {
          // Insert the data into the table.
          \Drupal::database()->insert($table_name3)
            ->fields($data)
            ->execute();
        }
      }
    } //End Groups

    if ($groupIds_API) {
      //@todo
      // $this->remove_unrequired_Groups_from_Database($groupIds_API);
    }
  }

  public function processImportGroupsObjects($Detaildata, $btn_action)
  {
    $database = Database::getConnection();
    $table_name6 = 'collector_systems_group_objects';

    //Start GroupObjects
    foreach($Detaildata['value'] as $obj)
    {
        $groupId = $obj['GroupId'];
        $objectId = $obj['ObjectId'];

      $data = array(
        'GroupId' => $groupId,
        'ObjectId' => $objectId,
      );

      $database->insert($table_name6)
        ->fields($data)
        ->execute();

    }

  }

  public function processImportExhibitionsObjects($Detaildata, $btn_action)
  {
    $database = Database::getConnection();
    $table_name5 = 'collector_systems_exhibition_objects';

     //Start ExhibitionObjects
    foreach($Detaildata['value'] as $obj)
    {
      $objectId = $obj['ObjectId'];
      $exhibitionId = $obj['ExhibitionId'];

      $data = array(
        'ExhibitionId' => $exhibitionId,
        'ObjectId' => $objectId,
      );

      // Insert the data into the table.
      $database->insert($table_name5)
        ->fields($data)
        ->execute();

    }
    //End ExhibitionObjects
  }

/*
  * Returns true if the object is modified
  */
  public function is_object_modified($objectId, $ApiModificationDate)
  {
    $database = Database::getConnection();
    $table_name = 'collector_systems_objects';
    // Check if the object is modified
    $record_exists = $database->select($table_name)
      ->fields($table_name)
      ->condition('ObjectId', $objectId)
      ->condition('ModificationDate', $ApiModificationDate)
      ->execute()
      ->fetchAssoc();

    if ($record_exists) {
      //object is not modified
      return false;
    } else {
      //object is modified
      return true;
    }
  }


  /***
   * Returns TRUE if the image is modified and exists
   *  */
  function is_image_modified($ModificationDate_API, $AttachmentId)
  {
    $database = Database::getConnection();
    $table_name = 'collector_systems_thumb_images';
    // Check if the object is modified
    $query = $database->select($table_name)
      ->fields($table_name)
      ->condition('AttachmentId', $AttachmentId)
      ->isNotNull('object_image_attachment')
      ->isNotNull('thumb_size_URL')
      ->condition('ModificationDate', $ModificationDate_API)
      ->execute();


    if ($query) {
      $record_exists = $query->fetchAssoc();
      if ($record_exists) {
        return FALSE;
      } else {
        return TRUE;
      }
    } else {
      // Handle query execution error
      \Drupal::logger('collector_systems')->error('Error executing database query for function is_image_modified');
      return FALSE;
    }
  }

  public function is_exist_object_image_AttachmentId_DB($objectId, $AttachmentId)
  {
    $database = Database::getConnection();
    $table_name = 'collector_systems_thumb_images';
    // Check if the object is modified
    $record_exists = $database->select($table_name)
      ->fields($table_name)
      ->condition('ObjectId', $objectId)
      ->condition('AttachmentId', $AttachmentId)
      ->execute()
      ->fetchAssoc();

    if ($record_exists) {
      return true;
    } else {
      return false;
    }
  }

  /*
  * Returns true if main_image_attachment_exists
  */
  public function is_exist_main_image_attachment($objectId, $MainImageAttachmentId)
  {

    $database = Database::getConnection();
    $table_name = 'collector_systems_objects';
    // Check if the object is modified
    $record_exists = $database->select($table_name)
      ->fields($table_name)
      ->condition('ObjectId', $objectId)
      ->condition('MainImageAttachmentId', $MainImageAttachmentId)
      ->isNotNull('main_image_attachment')
      ->execute()
      ->fetchAssoc();

    if ($record_exists) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Remove the unrequired rows from the 'collector_systems_thumb_images' table which does not exist in the API response
   */
  public function remove_unrequired_AttachmentIds_from_Database($AttachmentIds_API)
  {
    $database = Database::getConnection();
    $table_name = 'collector_systems_thumb_images';

    // Get all AttachmentIds from the database
    $dbAttachmentIds = $database->select($table_name, 't')
      ->fields('t', ['AttachmentId'])
      ->execute()
      ->fetchCol();

    // Find AttachmentIds in the database that are not in the API response
    $unrequiredAttachmentIds = array_diff($dbAttachmentIds, $AttachmentIds_API);

    if (!empty($unrequiredAttachmentIds)) {
      // Remove rows with unrequired AttachmentIds from the database
      $database->delete($table_name)
        ->condition('AttachmentId', $unrequiredAttachmentIds, 'IN')
        ->execute();
    }
  }

  public function get_field_names()
  {
    $source_table_name = 'collector_systems_clsobjects_fields';

    // Define the fields to exclude.
    $exclude_fields = ['Title', 'InventoryNumber', 'ObjectDate'];

    // Use the Drupal Database API.
    $query = \Drupal::database()->select($source_table_name, 't')
      ->fields('t', ['fieldname'])
      ->distinct();
    // ->condition('fieldname', $exclude_fields, 'NOT IN');

    // Execute the query and fetch the result.
    $field_names = $query->execute()->fetchCol();

    return $field_names;
  }

  /**
   * Helper function to drop the tables.
   */
  function clear_tables_data($btn_action) {
    if($btn_action == 'update_dataset'){
      //if update dataset then only delete specific tables
      $tables = [
        'collector_systems_exhibition_objects',
        'collector_systems_group_objects',
        'collector_systems_cssynced'
      ];
    }else{
      $tables = [
        'collector_systems_objects',
        'collector_systems_artists',
        'collector_systems_collections',
        'collector_systems_groups',
        'collector_systems_exhibitions',
        'collector_systems_exhibition_objects',
        'collector_systems_group_objects',
        'collector_systems_thumb_images',
        'collector_systems_cssynced'
      ];
    }

    $database = Database::getConnection();
    foreach ($tables as $table_name) {
      if ($database->schema()->tableExists($table_name)) {
        // If the table is 'collector_systems_objects', drop it, otherwise truncate it.
        if($table_name == 'collector_systems_objects'){
          $database->schema()->dropTable($table_name);
        } else {
          $database->truncate($table_name)->execute();
        }

        \Drupal::logger('collector_systems')->notice('Truncated/Dropped table: %table_name', ['%table_name' => $table_name]);
      } else {
        \Drupal::logger('collector_systems')->notice('Table does not exist: %table_name', ['%table_name' => $table_name]);
      }
    }
  }

  /**
   * Helper function to create the dynamic table.
   */
  function custom_api_integration_create_tables($btn_action) {
    \Drupal::logger('collector_systems')->debug('Start Create Tables Process.');

    if($btn_action == 'update_dataset'){

    }else{
      $this->create_table_collector_systems_objects();
    }


  }

  function create_table_collector_systems_objects(){
    // Create the new table
    $table_name = 'collector_systems_objects';
    $selected_fields = $this->get_field_names(); 

    // List of rich text fields for objects
    $rich_text_fields = [
      'AddressInventoryMemo',
      'AssociatedSpecies',
      'CastAndCrew',
      'CompletenessNote',
      'Drainage',
      'FieldCollectionMemo',
      'HabitatMemo',
      'History',
      'Inscriptions',
      'InventoryMemo',
      'KeyDescriptor',
      'Labels',
      'LocationAccessMemo',
      'LocationConditionMemo',
      'LocationSecurityMemo',
      'MaterialComponentMemo',
      'MovementMemo',
      'ObjectDescription',
      'ObjectNameNote',
      'ObjectProductionMemo',
      'ObjectUse',
      'ObjectUseMemo',
      'Provenance',
      'ReferenceNotes',
      'RegistrationNotes',
      'RelatedCollections',
      'RepairsMade',
      'ResearchNotes',
      'Signatures',
      'StaffNotes',
      'StartingInstructions',
      'StratigraphicUnitMemo',
      'SubspeciesDescriptiveName',
      'Synopsis',
      'TaxonomicNotes',
      'Term',
      'TitleStatusNotes',
      'Transcription',
      'TypeStatus',
      'UserDefinedRichText1',
      'UserDefinedRichText2',
      'UserDefinedRichText3',
      'UserDefinedRichText4',
      'UserDefinedRichText5',
      'UserDefinedRichText6',
      'UserDefinedRichText7',
      'UserDefinedRichText8',
      'UserDefinedRichText9',
      'UserDefinedRichText10',
      'UserDefinedRichText11',
      'UserDefinedRichText12',
      'UserDefinedRichText13',
      'UserDefinedRichText14',
      'UserDefinedRichText15',
      'UserDefinedRichText16',
      'UserDefinedRichText17',
      'UserDefinedRichText18',
      'Waterbody',
      'WithinSiteProveniance',
    ];

    $schema = [
      'fields' => [
        'ObjectId' => [
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'Title' => [
          'type' => 'varchar',
          'length' => 500,
          // 'not null' => TRUE,
        ],
        'InventoryNumber' => [
          'type' => 'varchar',
          'length' => 50,
        ],
        'ObjectDate' => [
          'type' => 'text',
          // 'length' => 30,
        ],
        'MainImageAttachmentId' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'main_image_attachment' => [
          'type' => 'blob',
          'size' => 'big',
          // 'not null' => TRUE,
        ],
        'main_image_attachment_description' => [
          'type' => 'text'
        ],
        'object_image_attachment' => [
          'type' => 'blob',
          'size' => 'big',
          // 'not null' => TRUE,
        ],
        'main_image_path' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'object_image_path' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ArtistId' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'CollectionId' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'thumb_size_URL' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'thumb_size_URL_path' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'slide_show_attachment' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'slide_show_URL_path' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'FileURL' => [
          'type' => 'text',
        ],
        'ModificationDate' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'Latitude' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'Longitude' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'AddressName' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'AdditionalAuthorsText' => [
          'type' => 'text',
          'not null' => FALSE,
        ],
        'AdditionalCreatorsText' => [
          'type' => 'text',
          'not null' => FALSE,
        ],
        'AdditionalPublishersText' => [
          'type' => 'text',
          'not null' => FALSE,
        ],
        'AdditionalArtistsText' => [
          'type' => 'text',
          'not null' => FALSE,
        ],
        'AdditionalArtistMakersText' => [
          'type' => 'text',
          'not null' => FALSE,
        ],
      ],
      'primary key' => ['ObjectId'],
    ];

    if($selected_fields){
      // Add dynamic fields if available
      foreach ($selected_fields as $field) {
        if (in_array($field, $rich_text_fields, TRUE)) {
          // Rich text → size - big
          $schema['fields'][$field] = [
            'type' => 'text',
            'size' => 'big',
          ];
        } else {
          // Plain text → TEXT
          $schema['fields'][$field] = [
            'type' => 'text',
          ];
        }

      }
    }

    // Create the table
    Database::getConnection()->schema()->createTable($table_name, $schema);

  }


  public function processSyncData($Detaildata, $current_batch_number, $import_type, $btn_action)
  {
    if ($current_batch_number == 0 && $import_type == 'Artists') {
      //This will run only once at the first batch
      \Drupal::logger('collector_systems')->debug('Start processSyncData.');

      if($btn_action == 'reset_and_create_dataset'){

        //drop images directory
        $allImagesDirectory = \Drupal::service('stream_wrapper_manager')->getViaUri('public://')->getDirectoryPath() . '/collector_systems/images';
        if(file_exists( $allImagesDirectory ))
        {
          $this->fileSystem->deleteRecursive($allImagesDirectory);
        }

      }

    }

    if ($import_type == 'Artists') {
      $this->processImportArtists($Detaildata, $btn_action);
    } elseif ($import_type == 'Objects') {
      $this->processImportObjects($Detaildata, $btn_action);
    } elseif ($import_type == 'Collections') {
      $this->processImportCollections($Detaildata, $btn_action);
    } elseif ($import_type == 'Exhibitions') {
      $this->processImportExhibitions($Detaildata, $btn_action);
    } elseif ($import_type == 'Groups') {
      $this->processImportGroups($Detaildata, $btn_action);
    } elseif ($import_type == 'ExhibitionsObjects'){
      $this->processImportExhibitionsObjects($Detaildata, $btn_action);
    } elseif ($import_type == 'GroupsObjects'){
      $this->processImportGroupsObjects($Detaildata, $btn_action);
    }
  }

  /**
   * Batch operation to process each item.
   */
  public function processItem($item, $btn_action)
  {
    $collector_systemsts_get_api_data = $this->collectorSystemsGetApiData;
    $import_type = $item['import_type'];
    $chunk_size = $item['chunk_size'];
    $offset = $item['offset'];
    $current_batch_number = $item['current_batch_number'];

    if ($import_type == 'Artists') {
      $getApiArtistsData = $collector_systemsts_get_api_data->getApiArtistsData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiArtistsData, TRUE);
    } elseif ($import_type == 'Objects') {
      $getApiObjectsData = $collector_systemsts_get_api_data->getApiObjectsData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiObjectsData, TRUE);
    } elseif ($import_type == 'Collections') {
      $getApiCollectionsData = $collector_systemsts_get_api_data->getApiCollectionsData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiCollectionsData, TRUE);
    } elseif ($import_type == 'Groups') {
      $getApiGroupsData = $collector_systemsts_get_api_data->getApiGroupsData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiGroupsData, TRUE);
    } elseif ($import_type == 'Exhibitions') {
      $getApiExhibitionsData = $collector_systemsts_get_api_data->getApiExhibitionsData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiExhibitionsData, TRUE);
    } elseif ($import_type == 'GroupsObjects') {
      $getApiGroupsObjectsData = $collector_systemsts_get_api_data->getApiGroupsObjectsData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiGroupsObjectsData, TRUE);
    } elseif ($import_type == 'ExhibitionsObjects') {

      $getApiExhibitionsObjectsData = $collector_systemsts_get_api_data->getApiExhibitionsObjectsData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiExhibitionsObjectsData, TRUE);
    }


    $this->processSyncData($Detaildata, $current_batch_number, $import_type, $btn_action);
  }

  protected function normalizeDatetime($value) {
    if (empty($value)) {
      return NULL;
    }
  
    $timestamp = strtotime($value);
    return date('Y-m-d H:i:s', $timestamp);
  }
}
