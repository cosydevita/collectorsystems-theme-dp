<?php

namespace Drupal\collector_systems\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\collector_systems\Csconstants;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateTablesForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'cs_create_tables_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $request = \Drupal::request();
    $btn_action = $request->query->get('btn_action', '');


    $form['btn_action'] = [
      '#type' => 'radios',
      '#title' => $this->t('Data'),
      '#options' => [
        'reset_and_create_dataset' => $this->t('Reset and Create Dataset'),
        'update_dataset' => $this->t('Update Dataset'),
      ],
      '#default_value' => $btn_action,
      '#required' => TRUE,

    ];
    // Add a button to trigger the batch.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    // Add a container to display the status message.
    $form['status_message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="batch-status"></div>',
    ];

    return $form;
  }



  /**
   * Get the data for batch processing.
   */
  public function getDataForProcessing()
  {


    $chunk_size = 10; // number of objects to process in a batch

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

  /**
   * Start the batch process.
   */
  public function startBatchProcess($data, $btn_action, $is_automatic_sync = null)
  {

    $operations = [];
    foreach ($data as $item) {
      $operations[] = [[$this, 'processItem'], [$item, $btn_action]];
    }

    // Define the batch.
    $batch = [
      'title' => t('Importing Data from Collector Systems API...'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('An error occurred during the download.'),
      'operations' => $operations,
      'finished' => [$this, 'batchFinished'],
    ];

    \Drupal::logger('collector_systems')->debug('PHP_SAPI: '. PHP_SAPI);

    // If triggered by Cron, ensure batch processing works without UI interaction.
    if ($is_automatic_sync == TRUE) {
      $batch['progressive'] = FALSE; // Ensure batch runs non-interactively.

      \Drupal::logger('collector_systems')->debug('Operations:'. json_encode($operations));
      \Drupal::logger('collector_systems')->debug('Automatic sync Triggered');
      batch_set($batch);

      // Explicitly process the batch during cron.
      $batch =& batch_get();
      if (!empty($batch)) {
        $batch['progressive'] = FALSE; // Ensure non-interactive mode.
        batch_process();
      }
    } else {
      \Drupal::logger('collector_systems')->debug('Batch Process triggered for: ' . $btn_action);
      batch_set($batch);
    }

  }

  /**
   * Batch operation to process each item.
   */
  public function processItem($item, $btn_action, &$context)
  {
    \Drupal::logger('collector_systems')->debug('processItem Triggered');

    $collector_systemsts_get_api_data = \Drupal::service('collector_systems.collector_systemsts_get_api_data');
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

  /**
   * Batch finished callback.
   */
  public function batchFinished($success, $results, $operations)
  {
    \Drupal::logger('collector_systems')->debug('Batch Finished.');

    if ($success) {
      \Drupal::logger('collector_systems')->debug('Batch success.');
      $this->update_CSSynced_table();
      \Drupal::messenger()->addMessage($this->t('Collector Systems: Import completed successfully.'));

      $redirect_url = "/admin/collector-systems/api-dashboard";
      return new RedirectResponse($redirect_url);

    } else {
      \Drupal::logger('collector_systems')->debug('Batch was not successful.');
      \Drupal::messenger()->addError($this->t('Collector Systems: An error occurred during the import.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $btn_action = $form_state->getValue('btn_action');
    $data = $this->getDataForProcessing();
    $this->startBatchProcess($data, $btn_action);
  }





  public function processSyncData($Detaildata, $current_batch_number, $import_type, $btn_action)
  {
    if ($current_batch_number == 0 && $import_type == 'Artists') {
      //This will run only once at the first batch
      \Drupal::logger('collector_systems')->debug('Start processSyncData.');
       //drop tables
      $this->custom_api_integration_drop_tables($btn_action);

      if($btn_action == 'reset_and_create_dataset'){

        //drop images directory
        $allImagesDirectory = PublicStream::basePath() . '/All Images';
        if(file_exists( $allImagesDirectory ))
        {
          $this->deleteDirectory($allImagesDirectory);
        }

      }

      // create tables
      $this->custom_api_integration_create_tables($btn_action);



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

  public function processImportArtists($Detaildata, $btn_action)
  {
    \Drupal::logger('collector_systems')->debug('Start process Import Artists.');
    $database = Database::getConnection();
    $table_name = 'Artists';
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

    $table_name = 'CSObjects';
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
        switch ($field_name) {
          case csconstants::InventoryNumber:
            if (!empty($value['InventoryNumber'])) {
              $combinedObjectValues[$field_name] = $value['InventoryNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

            // case csconstants::ArtistName:
            //     if (!empty($value['ArtistName'])) {
            //         $combinedObjectValues[$field_name] = $value['ArtistName'];
            //     }
            //     break;
          case Csconstants::ObjectDescription:
            if (!empty($value['ObjectDescription'])) {
              $combinedObjectValues[$field_name] = $value['ObjectDescription'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::ArtistFirst:
            if (!empty($value['ArtistFirst'])) {
              $combinedObjectValues[$field_name] = $value['ArtistFirst'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ArtistLast:
            if (!empty($value['ArtistLast'])) {
              $combinedObjectValues[$field_name] = $value['ArtistLast'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ArtistYears:
            if (!empty($value['ArtistYears'])) {
              $combinedObjectValues[$field_name] = $value['ArtistYears'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ArtistLocale:
            if (!empty($value['ArtistLocale'])) {
              $combinedObjectValues[$field_name] = $value['ArtistLocale'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ArtistBio:
            if (!empty($value['ArtistBio'])) {
              $combinedObjectValues[$field_name] = $value['ArtistBio'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CollectionName:
            if (!empty($value['Collection'])) {
              $combinedObjectValues[$field_name] = $value['Collection']['CollectionName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FullCollectionName:
            if (!empty($value['Collection'])) {
              $combinedObjectValues[$field_name] = $value['Collection']['FullCollectionName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::NomenclatureObjectName:
            if (!empty($value['NomenclatureObjectName'])) {
              $combinedObjectValues[$field_name] = $value['NomenclatureObjectName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectStatus:
            if (!empty($value['ObjectStatus'])) {
              $combinedObjectValues[$field_name] = $value['ObjectStatus'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectType:
            if (!empty($value['ObjectType']['ObjectTypeName'])) {
              $combinedObjectValues[$field_name] = $value['ObjectType']['ObjectTypeName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::LocationName:
            if (!empty($value['Location']['LocationName'])) {
              $combinedObjectValues[$field_name] = $value['Location']['LocationName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FullLocationName:
            if (!empty($value['Location']['FullLocationName'])) {
              $combinedObjectValues[$field_name] = $value['Location']['FullLocationName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PermanentLocationName:
            if (!empty($value['PermanentLocation']['LocationName'])) {
              $combinedObjectValues[$field_name] = $value['PermanentLocation']['LocationName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PermanentFullLocationName:
            if (!empty($value['PermanentLocation']['FullLocationName'])) {
              $combinedObjectValues[$field_name] = $value['PermanentLocation']['FullLocationName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CollectionName:
            if (!empty($value['Collection']['CollectionName'])) {
              $combinedObjectValues[$field_name] = $value['Collection']['CollectionName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FullCollectionName:
            if (!empty($value['Collection']['FullCollectionName'])) {
              $combinedObjectValues[$field_name] = $value['Collection']['FullCollectionName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CreditLine:
            if (!empty($value['CreditLine'])) {
              $combinedObjectValues[$field_name] = $value['CreditLine'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ArtistName:
            if (!empty($value['Artist']) && isset($value['Artist'])) {
              if (!empty($value['Artist']['ArtistName'])) {
                $combinedObjectValues[$field_name] = $value['Artist']['ArtistName'];
              }
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::AdditionalArtists:
            if (!empty($value['AdditionalArtists'])) {
              $combinedObjectValues[$field_name] = $this->implodeChildArrayProperty($value['AdditionalArtists'], "Artist", "ArtistId", "ArtistName");
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Maker:
            if (!empty($value['Maker'])) {
              $combinedObjectValues[$field_name] = $value['Maker'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Title:
            if (!empty($value['Title'])) {
              $combinedObjectValues[$field_name] = $value['Title'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::AlternateTitle:
            if (!empty($value['AlternateTitle'])) {
              $combinedObjectValues[$field_name] = $value['AlternateTitle'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectDate:
            if (!empty($value['ObjectDate'])) {
              $combinedObjectValues[$field_name] = $value['ObjectDate'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Medium:
            if (!empty($value['Medium'])) {
              $combinedObjectValues[$field_name] = $value['Medium'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::LocationStatus:
            if (!empty($value['LocationStatus']['Term'])) {
              $combinedObjectValues[$field_name] = $value['LocationStatus']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::InventoryDate:
            if (!empty($value['InventoryDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['InventoryDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::InventoryContactName:
            if (!empty($value['InventoryContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['InventoryContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Form:
            if (!empty($value['Form'])) {
              $combinedObjectValues[$field_name] = $value['Form'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Subject:
            if (!empty($value['Subject'])) {
              $combinedObjectValues[$field_name] = $value['Subject'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CategoryStyle:
            if (!empty($value['CategoryStyle'])) {
              $combinedObjectValues[$field_name] = $value['CategoryStyle'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CountryOrigin:
            if (!empty($value['CountryOrigin'])) {
              $combinedObjectValues[$field_name] = $value['CountryOrigin'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Edition:
            if (!empty($value['Edition'])) {
              $combinedObjectValues[$field_name] = $value['Edition'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SuitePortfolio:
            if (!empty($value['SuitePortfolio'])) {
              $combinedObjectValues[$field_name] = $value['SuitePortfolio'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::CatalogRaisonne:
            if (!empty($value['CatalogRaisonne'])) {
              $combinedObjectValues[$field_name] = $value['CatalogRaisonne'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::RFIDTagNumber:
            if (!empty($value['RFIDTagNumber'])) {
              $combinedObjectValues[$field_name] = $value['RFIDTagNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Term:
            if (!empty($value['Term'])) {
              $combinedObjectValues[$field_name] = $value['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CatalogNumber:
            if (!empty($value['CatalogNumber'])) {
              $combinedObjectValues[$field_name] = $value['CatalogNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::OtherNumbers:
            if (!empty($value['OtherNumbers'])) {
              $combinedObjectValues[$field_name] = $value['OtherNumbers'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ItemCount:
            if (!empty($value['ItemCount'])) {
              $combinedObjectValues[$field_name] = $value['ItemCount'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CatalogerContactName:
            if (!empty($value['CatalogerContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['CatalogerContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CatalogDate:
            if (!empty($value['CatalogDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['CatalogDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CollectionTitle:
            if (!empty($value['CollectionTitle'])) {
              $combinedObjectValues[$field_name] = $value['CollectionTitle'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CollectionNumber:
            if (!empty($value['CollectionNumber'])) {
              $combinedObjectValues[$field_name] = $value['CollectionNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Material:
            if (!empty($value['Material'])) {
              $combinedObjectValues[$field_name] = $value['Material'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Technique:
            if (!empty($value['Technique']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Technique']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Color:
            if (!empty($value['Color']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Color']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StateOfOrigin:
            if (!empty($value['StateOfOrigin'])) {
              $combinedObjectValues[$field_name] = $value['StateOfOrigin'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CountyOfOrigin:
            if (!empty($value['CountyOfOrigin'])) {
              $combinedObjectValues[$field_name] = $value['CountyOfOrigin'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CityOfOrigin:
            if (!empty($value['CityOfOrigin'])) {
              $combinedObjectValues[$field_name] = $value['CityOfOrigin'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::State:
            if (!empty($value['State'])) {
              $combinedObjectValues[$field_name] = $value['State'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Duration:
            if (!empty($value['Duration'])) {
              $combinedObjectValues[$field_name] = $value['Duration'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::RevisedNomenclature:
            if (!empty($value['RevisedNomenclature'])) {
              $combinedObjectValues[$field_name] = $value['RevisedNomenclature'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PreviousCatalogNumber:
            if (!empty($value['PreviousCatalogNumber'])) {
              $combinedObjectValues[$field_name] = $value['PreviousCatalogNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FieldSpecimenNumber:
            if (!empty($value['FieldSpecimenNumber'])) {
              $combinedObjectValues[$field_name] = $value['FieldSpecimenNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StatusDate:
            if (!empty($value['StatusDate'])) {
              $combinedObjectValues[$field_name] = $value['StatusDate'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StorageUnit:
            if (!empty($value['StorageUnit']['Term'])) {
              $combinedObjectValues[$field_name] = $value['StorageUnit']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::CollectionDate:
            if (!empty($value['CollectionDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['CollectionDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CollectorContactName:
            if (!empty($value['CollectorContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['CollectorContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CollectorPlace:
            if (!empty($value['CollectorPlace'])) {
              $combinedObjectValues[$field_name] = $value['CollectorPlace'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CatalogFolder:
            if (!empty($value['CatalogFolder'])) {
              $combinedObjectValues[$field_name] = $value['CatalogFolder'] == true ? 'Yes' : 'No';
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::IdentifiedByContactName:
            if (!empty($value['IdentifiedByContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['IdentifiedByContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::IdentifiedDate:
            if (!empty($value['IdentifiedDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['IdentifiedDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::EminentFigureContactName:
            if (!empty($value['EminentFigureContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['EminentFigureContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::EminentOrganizationContactName:
            if (!empty($value['EminentOrganizationContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['EminentOrganizationContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ControlledProperty:
            if (!empty($value['ControlledProperty'])) {
              $combinedObjectValues[$field_name] = $value['ControlledProperty'] == true ? 'Yes' : 'No';
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ArtistMakerName:
            if (!empty($value["ArtistMaker"]['ArtistName'])) {
              $combinedObjectValues[$field_name] = $value["ArtistMaker"]['ArtistName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::ArtistMakerFirst:
            if (!empty($value["ArtistMaker"]['ArtistMakerFirst'])) {
              $combinedObjectValues[$field_name] = $value["ArtistMaker"]['ArtistMakerFirst'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::ArtistMakerLast:
            if (!empty($value["ArtistMaker"]['ArtistMakerLast'])) {
              $combinedObjectValues[$field_name] = $value["ArtistMaker"]['ArtistMakerLast'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TaxonomicSerialNumber:
            if (!empty($value['TaxonomicSerialNumber'])) {
              $combinedObjectValues[$field_name] = $value['TaxonomicSerialNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Kingdom:
            if (!empty($value['Kingdom'])) {
              $combinedObjectValues[$field_name] = $value['Kingdom'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PhylumDivision:
            if (!empty($value['PhylumDivision'])) {
              $combinedObjectValues[$field_name] = $value['PhylumDivision'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CSClass:
            if (!empty($value['Class'])) {
              $combinedObjectValues[$field_name] = $value['Class'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Order:
            if (!empty($value['Order'])) {
              $combinedObjectValues[$field_name] = $value['Order'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Family:
            if (!empty($value['Family'])) {
              $combinedObjectValues[$field_name] = $value['Family'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::SubFamily:
            if (!empty($value['SubFamily'])) {
              $combinedObjectValues[$field_name] = $value['SubFamily'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ScientificName:
            if (!empty($value['ScientificName'])) {
              $combinedObjectValues[$field_name] = $value['ScientificName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CommonName:
            if (!empty($value['CommonName'])) {
              $combinedObjectValues[$field_name] = $value['CommonName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Species:
            if (!empty($value['Species'])) {
              $combinedObjectValues[$field_name] = $value['Species'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SpeciesAuthorName:
            if (!empty($value['SpeciesAuthor']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['SpeciesAuthor']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SpeciesAuthorDate:
            if (!empty($value['SpeciesAuthorDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['SpeciesAuthorDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Subspecies:
            if (!empty($value['Subspecies'])) {
              $combinedObjectValues[$field_name] = $value['Subspecies'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesAuthorityContactName:
            if (!empty($value['SubspeciesAuthorityContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['SubspeciesAuthorityContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesAuthorName:
            if (!empty($value['SubspeciesAuthor']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['SubspeciesAuthor']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesAuthorDate:
            if (!empty($value['SubspeciesAuthorDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['SubspeciesAuthorDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesYear:
            if (!empty($value['SubspeciesYear'])) {
              $combinedObjectValues[$field_name] = $value['SubspeciesYear'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesVariety:
            if (!empty($value['SubspeciesVariety'])) {
              $combinedObjectValues[$field_name] = $value['SubspeciesVariety'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesVarietyAuthorityContactName:
            if (!empty($value['SubspeciesVarietyAuthorityContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['SubspeciesVarietyAuthorityContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesVarietyYear:
            if (!empty($value['SubspeciesVarietyYear'])) {
              $combinedObjectValues[$field_name] = $value['SubspeciesVarietyYear'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesForma:
            if (!empty($value['SubspeciesForma'])) {
              $combinedObjectValues[$field_name] = $value['SubspeciesForma'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesFormaAuthorityContactName:
            if (!empty($value['SubspeciesFormaAuthorityContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['SubspeciesFormaAuthorityContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesFormaYear:
            if (!empty($value['SubspeciesFormaYear'])) {
              $combinedObjectValues[$field_name] = $value['SubspeciesFormaYear'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StudyNumber:
            if (!empty($value['StudyNumber'])) {
              $combinedObjectValues[$field_name] = $value['StudyNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::AlternateName:
            if (!empty($value['AlternateName'])) {
              $combinedObjectValues[$field_name] = $value['AlternateName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CulturalID:
            if (!empty($value['CulturalID'])) {
              $combinedObjectValues[$field_name] = $value['CulturalID'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CultureOfUse:
            if (!empty($value['CultureOfUse']['Term'])) {
              $combinedObjectValues[$field_name] = $value['CultureOfUse']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ManufactureDate:
            if (!empty($value['ManufactureDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['ManufactureDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UseDate:
            if (!empty($value['UseDate'])) {
              $combinedObjectValues[$field_name] = $value['UseDate'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::TimePeriod:
            if (!empty($value['TimePeriod'])) {
              $combinedObjectValues[$field_name] = $value['TimePeriod'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::HistoricCulturalPeriod:
            if (!empty($value['HistoricCulturalPeriod']['Term'])) {
              $combinedObjectValues[$field_name] = $value['HistoricCulturalPeriod']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ManufacturingTechnique:
            if (!empty($value['ManufacturingTechnique']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ManufacturingTechnique']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Material:
            if (!empty($value['Material'])) {
              $combinedObjectValues[$field_name] = $value['Material'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::BroadClassOfMaterial:
            if (!empty($value['BroadClassOfMaterial']['Term'])) {
              $combinedObjectValues[$field_name] = $value['BroadClassOfMaterial']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SpecificClassOfMaterial:
            if (!empty($value['SpecificClassOfMaterial']['Term'])) {
              $combinedObjectValues[$field_name] = $value['SpecificClassOfMaterial']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Quantity:
            if (!empty($value['Quantity'])) {
              $combinedObjectValues[$field_name] = $value['Quantity'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PlaceOfManufactureCountry:
            if (!empty($value['PlaceOfManufactureCountry'])) {
              $combinedObjectValues[$field_name] = $value['PlaceOfManufactureCountry'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PlaceOfManufactureState:
            if (!empty($value['PlaceOfManufactureState'])) {
              $combinedObjectValues[$field_name] = $value['PlaceOfManufactureState'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PlaceOfManufactureCounty:
            if (!empty($value['PlaceOfManufactureCounty'])) {
              $combinedObjectValues[$field_name] = $value['PlaceOfManufactureCounty'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PlaceOfManufactureCity:
            if (!empty($value['PlaceOfManufactureCity'])) {
              $combinedObjectValues[$field_name] = $value['PlaceOfManufactureCity'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::OtherManufacturingSite:
            if (!empty($value['OtherManufacturingSite'])) {
              $combinedObjectValues[$field_name] = $value['OtherManufacturingSite'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Latitude:
            if (!empty($value['Latitude'])) {
              $combinedObjectValues[$field_name] = $value['Latitude'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Longitude:
            if (!empty($value['Longitude'])) {
              $combinedObjectValues[$field_name] = $value['Longitude'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UTMCoordinates:
            if (!empty($value['UTMCoordinates'])) {
              $combinedObjectValues[$field_name] = $value['UTMCoordinates'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TownshipRangeSection:
            if (!empty($value['TownshipRangeSection'])) {
              $combinedObjectValues[$field_name] = $value['TownshipRangeSection'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FieldSiteNumber:
            if (!empty($value['FieldSiteNumber'])) {
              $combinedObjectValues[$field_name] = $value['FieldSiteNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StateSiteNumber:
            if (!empty($value['StateSiteNumber'])) {
              $combinedObjectValues[$field_name] = $value['StateSiteNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SiteName:
            if (!empty($value['SiteName'])) {
              $combinedObjectValues[$field_name] = $value['SiteName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::SiteNumber:
            if (!empty($value['SiteNumber'])) {
              $combinedObjectValues[$field_name] = $value['SiteNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DecorativeMotif:
            if (!empty($value['DecorativeMotif'])) {
              $combinedObjectValues[$field_name] = $value['DecorativeMotif'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DecorativeTechnique:
            if (!empty($value['DecorativeTechnique']['Term'])) {
              $combinedObjectValues[$field_name] = $value['DecorativeTechnique']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Reproduction:
            if (!empty($value['Reproduction'])) {
              $combinedObjectValues[$field_name] = $value['Reproduction'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectForm:
            if (!empty($value['ObjectForm']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ObjectForm']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectPart:
            if (!empty($value['ObjectPart']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ObjectPart']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ComponentPart:
            if (!empty($value['ComponentPart'])) {
              $combinedObjectValues[$field_name] = $value['ComponentPart'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Temper:
            if (!empty($value['Temper']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Temper']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TypeName:
            if (!empty($value['TypeName']['Term'])) {
              $combinedObjectValues[$field_name] = $value['TypeName']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SlideNumber:
            if (!empty($value['SlideNumber'])) {
              $combinedObjectValues[$field_name] = $value['SlideNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::BagNumber:
            if (!empty($value['BagNumber'])) {
              $combinedObjectValues[$field_name] = $value['BagNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TotalBags:
            if (!empty($value['TotalBags'])) {
              $combinedObjectValues[$field_name] = $value['TotalBags'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::BoxNumber:
            if (!empty($value['BoxNumber'])) {
              $combinedObjectValues[$field_name] = $value['BoxNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TotalBoxes:
            if (!empty($value['TotalBoxes'])) {
              $combinedObjectValues[$field_name] = $value['TotalBoxes'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::MakersMark:
            if (!empty($value['MakersMark'])) {
              $combinedObjectValues[$field_name] = $value['MakersMark'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::NAGPRA:
            if (!empty($value['NAGPRA']['Term'])) {
              $combinedObjectValues[$field_name] = $value['NAGPRA']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::OldNumber:
            if (!empty($value['OldNumber'])) {
              $combinedObjectValues[$field_name] = $value['OldNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::AdditionalAccessionNumber:
            if (!empty($value['AdditionalAccessionNumber'])) {
              $combinedObjectValues[$field_name] = $value['AdditionalAccessionNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CatalogLevel:
            if (!empty($value['CatalogLevel']['Term'])) {
              $combinedObjectValues[$field_name] = $value['CatalogLevel']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::LevelOfControl:
            if (!empty($value['LevelOfControl'])) {
              $combinedObjectValues[$field_name] = $value['LevelOfControl'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::AlternateName:
            if (!empty($value['AlternateName'])) {
              $combinedObjectValues[$field_name] = $value['AlternateName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::AuthorName:
            if (!empty($value['Author']['AuthorName'])) {
              $combinedObjectValues[$field_name] = $value['Author']['AuthorName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CreatorContactName:
            if (!empty($value['CreatorContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['CreatorContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ComposerContactName:
            if (!empty($value['ComposerContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['ComposerContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::NarratorContactName:
            if (!empty($value['NarratorContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['NarratorContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::EditorContactName:
            if (!empty($value['EditorContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['EditorContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PublisherContactName:
            if (!empty($value['PublisherContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['PublisherContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::IllustratorContactName:
            if (!empty($value['IllustratorContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['IllustratorContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ContributorContactName:
            if (!empty($value['ContributorContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['ContributorContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StudioContactName:
            if (!empty($value['StudioContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['StudioContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DirectorContactName:
            if (!empty($value['DirectorContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['DirectorContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ArtDirectorContactName:
            if (!empty($value['ArtDirectorContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['ArtDirectorContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ProducerContactName:
            if (!empty($value['ProducerContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['ProducerContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ProductionDesignerContactName:
            if (!empty($value['ProductionDesignerContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['ProductionDesignerContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ProductionCompanyContactName:
            if (!empty($value['ProductionCompanyContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['ProductionCompanyContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DistributionCompany:
            if (!empty($value['DistributionCompany']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['DistributionCompany']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::WriterContactName:
            if (!empty($value['WriterContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['WriterContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CinematographerContactName:
            if (!empty($value['CinematographerContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['CinematographerContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PhotographyContactName:
            if (!empty($value['PhotographyContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['PhotographyContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PublisherLocation:
            if (!empty($value['PublisherLocation'])) {
              $combinedObjectValues[$field_name] = $value['PublisherLocation'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Event:
            if (!empty($value['Event']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Event']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PeopleContent:
            if (!empty($value['PeopleContent'])) {
              $combinedObjectValues[$field_name] = $value['PeopleContent'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PlaceContent:
            if (!empty($value['PlaceContent'])) {
              $combinedObjectValues[$field_name] = $value['PlaceContent'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TownshipRangeSection:
            if (!empty($value['TownshipRangeSection'])) {
              $combinedObjectValues[$field_name] = $value['TownshipRangeSection'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ISBN:
            if (!empty($value['ISBN'])) {
              $combinedObjectValues[$field_name] = $value['ISBN'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ISSN:
            if (!empty($value['ISSN'])) {
              $combinedObjectValues[$field_name] = $value['ISSN'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CallNumber:
            if (!empty($value['CallNumber'])) {
              $combinedObjectValues[$field_name] = $value['CallNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CoverType:
            if (!empty($value['CoverType'])) {
              $combinedObjectValues[$field_name] = $value['CoverType'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TypeOfBinding:
            if (!empty($value['TypeOfBinding'])) {
              $combinedObjectValues[$field_name] = $value['TypeOfBinding'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Language:
            if (!empty($value['Language'])) {
              $combinedObjectValues[$field_name] = $value['Language'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::NumberOfPages:
            if (!empty($value['NumberOfPages'])) {
              $combinedObjectValues[$field_name] = $value['NumberOfPages'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::NegativeNumber:
            if (!empty($value['NegativeNumber'])) {
              $combinedObjectValues[$field_name] = $value['NegativeNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FilmSize:
            if (!empty($value['FilmSize'])) {
              $combinedObjectValues[$field_name] = $value['FilmSize'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Process:
            if (!empty($value['Process'])) {
              $combinedObjectValues[$field_name] = $value['Process'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ImageNumber:
            if (!empty($value['ImageNumber'])) {
              $combinedObjectValues[$field_name] = $value['ImageNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ImageRights:
            if (!empty($value['ImageRights'])) {
              $combinedObjectValues[$field_name] = $value['ImageRights'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Copyrights:
            if (!empty($value['Copyrights'])) {
              $combinedObjectValues[$field_name] = $value['Copyrights'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FindingAids:
            if (!empty($value['FindingAids'])) {
              $combinedObjectValues[$field_name] = $value['FindingAids'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::VolumeNumber:
            if (!empty($value['VolumeNumber'])) {
              $combinedObjectValues[$field_name] = $value['VolumeNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CompletionYear:
            if (!empty($value['CompletionYear'])) {
              $combinedObjectValues[$field_name] = $value['CompletionYear'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Format:
            if (!empty($value['Format']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Format']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::Genre:
            if (!empty($value['Genre']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Genre']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Subgenre:
            if (!empty($value['Subgenre']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Subgenre']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ReleaseDate:
            if (!empty($value['ReleaseDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['ReleaseDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ProductionDate:
            if (!empty($value['ProductionDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['ProductionDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Genus:
            if (!empty($value['Genus'])) {
              $combinedObjectValues[$field_name] = $value['Genus'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Stage:
            if (!empty($value['Stage'])) {
              $combinedObjectValues[$field_name] = $value['Stage'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Section:
            if (!empty($value['Section'])) {
              $combinedObjectValues[$field_name] = $value['Section'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::QuarterSection:
            if (!empty($value['QuarterSection'])) {
              $combinedObjectValues[$field_name] = $value['QuarterSection'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Age:
            if (!empty($value['Age'])) {
              $combinedObjectValues[$field_name] = $value['Age'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Locality:
            if (!empty($value['Locality'])) {
              $combinedObjectValues[$field_name] = $value['Locality'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::HabitatCommunity:
            if (!empty($value['HabitatCommunity']['Term'])) {
              $combinedObjectValues[$field_name] = $value['HabitatCommunity']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TypeSpecimen:
            if (!empty($value['TypeSpecimen']['Term'])) {
              $combinedObjectValues[$field_name] = $value['TypeSpecimen']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Sex:
            if (!empty($value['Sex']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Sex']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::ExoticNative:
            if (!empty($value['ExoticNative']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ExoticNative']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TaxonomicNotes:
            if (!empty($value['TaxonomicNotes'])) {
              $combinedObjectValues[$field_name] = $value['TaxonomicNotes'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Rare:
            if (!empty($value['Rare'])) {
              $combinedObjectValues[$field_name] = $value['Rare'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ThreatenedEndangeredDate:
            if (!empty($value['ThreatenedEndangeredDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['ThreatenedEndangeredDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ThreatenedEndangeredSpeciesSynonym:
            if (!empty($value['ThreatenedEndangeredSpeciesSynonym'])) {
              $combinedObjectValues[$field_name] = $value['ThreatenedEndangeredSpeciesSynonym'] == true ? 'Yes' : 'No';
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ThreatenedEndangeredSpeciesSynonymName:
            if (!empty($value['ThreatenedEndangeredSpeciesSynonymName'])) {
              $combinedObjectValues[$field_name] = $value['ThreatenedEndangeredSpeciesSynonymName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ThreatenedEndangeredSpeciesStatus:
            if (!empty($value['ThreatenedEndangeredSpeciesStatus']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ThreatenedEndangeredSpeciesStatus']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SubspeciesSynonym:
            if (!empty($value['SubspeciesSynonym'])) {
              $combinedObjectValues[$field_name] = $value['SubspeciesSynonym'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ContinentWorldRegion:
            if (!empty($value['ContinentWorldRegion'])) {
              $combinedObjectValues[$field_name] = $value['ContinentWorldRegion'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ReproductionMethod:
            if (!empty($value['ReproductionMethod'])) {
              $combinedObjectValues[$field_name] = $value['ReproductionMethod'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ReferenceDatum:
            if (!empty($value['ReferenceDatum'])) {
              $combinedObjectValues[$field_name] = $value['ReferenceDatum'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Aspect:
            if (!empty($value['Aspect'])) {
              $combinedObjectValues[$field_name] = $value['Aspect'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FormationPeriodSubstrate:
            if (!empty($value['FormationPeriodSubstrate']['Term'])) {
              $combinedObjectValues[$field_name] = $value['FormationPeriodSubstrate']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SoilType:
            if (!empty($value['SoilType'])) {
              $combinedObjectValues[$field_name] = $value['SoilType'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Slope:
            if (!empty($value['Slope'])) {
              $combinedObjectValues[$field_name] = $value['Slope'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Unit:
            if (!empty($value['Unit']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Unit']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::DepthInMeters:
            if (!empty($value['DepthInMeters'])) {
              $combinedObjectValues[$field_name] = $value['DepthInMeters'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ElevationInMeters:
            if (!empty($value['ElevationInMeters'])) {
              $combinedObjectValues[$field_name] = $value['ElevationInMeters'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::EthnologyCulture:
            if (!empty($value['EthnologyCulture'])) {
              $combinedObjectValues[$field_name] = $value['EthnologyCulture'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Alternate1EthnologyCulture:
            if (!empty($value['Alternate1EthnologyCulture'])) {
              $combinedObjectValues[$field_name] = $value['Alternate1EthnologyCulture'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Alternate2EthnologyCulture:
            if (!empty($value['Alternate2EthnologyCulture'])) {
              $combinedObjectValues[$field_name] = $value['Alternate2EthnologyCulture'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::AboriginalName:
            if (!empty($value['AboriginalName']['Term'])) {
              $combinedObjectValues[$field_name] = $value['AboriginalName']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::AdditionalArea:
            if (!empty($value['AdditionalArea']['Term'])) {
              $combinedObjectValues[$field_name] = $value['AdditionalArea']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::AdditionalGroup:
            if (!empty($value['AdditionalGroup'])) {
              $combinedObjectValues[$field_name] = $value['AdditionalGroup'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DescriptiveName:
            if (!empty($value['DescriptiveName'])) {
              $combinedObjectValues[$field_name] = $value['DescriptiveName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PeriodSystem:
            if (!empty($value['PeriodSystem']['Term'])) {
              $combinedObjectValues[$field_name] = $value['PeriodSystem']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::EpochSeries:
            if (!empty($value['EpochSeries']['Term'])) {
              $combinedObjectValues[$field_name] = $value['EpochSeries']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::AgeStage:
            if (!empty($value['AgeStage']['Term'])) {
              $combinedObjectValues[$field_name] = $value['AgeStage']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Composition:
            if (!empty($value['Composition'])) {
              $combinedObjectValues[$field_name] = $value['Composition'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StrunzClass:
            if (!empty($value['StrunzClass'])) {
              $combinedObjectValues[$field_name] = $value['StrunzClass'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StrunzDivision:
            if (!empty($value['StrunzDivision'])) {
              $combinedObjectValues[$field_name] = $value['StrunzDivision'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StrunzID:
            if (!empty($value['StrunzID'])) {
              $combinedObjectValues[$field_name] = $value['StrunzID'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::LithologyPedotype:
            if (!empty($value['LithologyPedotype'])) {
              $combinedObjectValues[$field_name] = $value['LithologyPedotype'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Formation:
            if (!empty($value['Formation'])) {
              $combinedObjectValues[$field_name] = $value['Formation'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::VerticalDatum:
            if (!empty($value['VerticalDatum'])) {
              $combinedObjectValues[$field_name] = $value['VerticalDatum'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Datum:
            if (!empty($value['Datum'])) {
              $combinedObjectValues[$field_name] = $value['Datum'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DepositionalEnvironment:
            if (!empty($value['DepositionalEnvironment'])) {
              $combinedObjectValues[$field_name] = $value['DepositionalEnvironment'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Member:
            if (!empty($value['Member']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Member']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::GeoUnit:
            if (!empty($value['GeoUnit']['Term'])) {
              $combinedObjectValues[$field_name] = $value['GeoUnit']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ThinSection:
            if (!empty($value['ThinSection'])) {
              $combinedObjectValues[$field_name] = $value['ThinSection'] == true ? 'Yes' : 'No';
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PatentDate:
            if (!empty($value['PatentDate'])) {
              $combinedObjectValues[$field_name] = $value['PatentDate'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Copyright:
            if (!empty($value['Copyright'])) {
              $combinedObjectValues[$field_name] = $value['Copyright'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::School:
            if (!empty($value['School'])) {
              $combinedObjectValues[$field_name] = $value['School'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Lithology:
            if (!empty($value['Lithology'])) {
              $combinedObjectValues[$field_name] = $value['Lithology'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Horizon:
            if (!empty($value['Horizon']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Horizon']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::InsituFloat:
            if (!empty($value['InsituFloat']['Term'])) {
              $combinedObjectValues[$field_name] = $value['InsituFloat']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Taphonomy:
            if (!empty($value['Taphonomy'])) {
              $combinedObjectValues[$field_name] = $value['Taphonomy'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Model:
            if (!empty($value['Model'])) {
              $combinedObjectValues[$field_name] = $value['Model'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Stones:
            if (!empty($value['Stones'])) {
              $combinedObjectValues[$field_name] = $value['Stones'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Karats:
            if (!empty($value['Karats'])) {
              $combinedObjectValues[$field_name] = $value['Karats'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Carats:
            if (!empty($value['Carats'])) {
              $combinedObjectValues[$field_name] = $value['Carats'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Cut:
            if (!empty($value['Cut'])) {
              $combinedObjectValues[$field_name] = $value['Cut'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Clarity:
            if (!empty($value['Clarity'])) {
              $combinedObjectValues[$field_name] = $value['Clarity'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TypeOfGemstone:
            if (!empty($value['TypeOfGemstone'])) {
              $combinedObjectValues[$field_name] = $value['TypeOfGemstone'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Size:
            if (!empty($value['Size']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Size']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::MetalType:
            if (!empty($value['MetalType'])) {
              $combinedObjectValues[$field_name] = $value['MetalType'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DrivenBy:
            if (!empty($value['DrivenBy'])) {
              $combinedObjectValues[$field_name] = $value['DrivenBy'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::VIN:
            if (!empty($value['VIN'])) {
              $combinedObjectValues[$field_name] = $value['VIN'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ChassisNumber:
            if (!empty($value['ChassisNumber'])) {
              $combinedObjectValues[$field_name] = $value['ChassisNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Mileage:
            if (!empty($value['Mileage'])) {
              $combinedObjectValues[$field_name] = $value['Mileage'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Power:
            if (!empty($value['Power'])) {
              $combinedObjectValues[$field_name] = $value['Power'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::EngineType:
            if (!empty($value['EngineType'])) {
              $combinedObjectValues[$field_name] = $value['EngineType'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::EnginePosition:
            if (!empty($value['EnginePosition'])) {
              $combinedObjectValues[$field_name] = $value['EnginePosition'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Transmission:
            if (!empty($value['Transmission']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Transmission']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Passengers:
            if (!empty($value['Passengers'])) {
              $combinedObjectValues[$field_name] = $value['Passengers'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FuelHighway:
            if (!empty($value['FuelHighway'])) {
              $combinedObjectValues[$field_name] = $value['FuelHighway'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Acceleration:
            if (!empty($value['Acceleration'])) {
              $combinedObjectValues[$field_name] = $value['Acceleration'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TopSpeed:
            if (!empty($value['TopSpeed'])) {
              $combinedObjectValues[$field_name] = $value['TopSpeed'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::EngineNumber:
            if (!empty($value['EngineNumber'])) {
              $combinedObjectValues[$field_name] = $value['EngineNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::LicensePlateNumber:
            if (!empty($value['LicensePlateNumber'])) {
              $combinedObjectValues[$field_name] = $value['LicensePlateNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TransmissionFluid:
            if (!empty($value['TransmissionFluid'])) {
              $combinedObjectValues[$field_name] = $value['TransmissionFluid'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::BrakeFluid:
            if (!empty($value['BrakeFluid'])) {
              $combinedObjectValues[$field_name] = $value['BrakeFluid'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::OilType:
            if (!empty($value['OilType'])) {
              $combinedObjectValues[$field_name] = $value['OilType'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FuelType:
            if (!empty($value['FuelType'])) {
              $combinedObjectValues[$field_name] = $value['FuelType'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::RegistrationStatus:
            if (!empty($value['RegistrationStatus']['Term'])) {
              $combinedObjectValues[$field_name] = $value['RegistrationStatus']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TitleStatus:
            if (!empty($value['TitleStatus']['Term'])) {
              $combinedObjectValues[$field_name] = $value['TitleStatus']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Paint:
            if (!empty($value['Paint'])) {
              $combinedObjectValues[$field_name] = $value['Paint'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::Battery:
            if (!empty($value['Battery'])) {
              $combinedObjectValues[$field_name] = $value['Battery'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ShiftPattern:
            if (!empty($value['ShiftPattern'])) {
              $combinedObjectValues[$field_name] = $value['ShiftPattern'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DashLayout:
            if (!empty($value['DashLayout'])) {
              $combinedObjectValues[$field_name] = $value['DashLayout'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::TypeOfWine:
            if (!empty($value['TypeOfWine'])) {
              $combinedObjectValues[$field_name] = $value['TypeOfWine'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Maturity:
            if (!empty($value['Maturity'])) {
              $combinedObjectValues[$field_name] = $value['Maturity'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Grape:
            if (!empty($value['Grape'])) {
              $combinedObjectValues[$field_name] = $value['Grape'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Region:
            if (!empty($value['Region'])) {
              $combinedObjectValues[$field_name] = $value['Region'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::BottleSize:
            if (!empty($value['BottleSize'])) {
              $combinedObjectValues[$field_name] = $value['BottleSize'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FermentationPeriod:
            if (!empty($value['FermentationPeriod'])) {
              $combinedObjectValues[$field_name] = $value['FermentationPeriod'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DesignerName:
            if (!empty($value['Designer']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['Designer']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Brand:
            if (!empty($value['Brand'])) {
              $combinedObjectValues[$field_name] = $value['Brand'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FabricMaterial:
            if (!empty($value['FabricMaterial'])) {
              $combinedObjectValues[$field_name] = $value['FabricMaterial'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SKU:
            if (!empty($value['SKU'])) {
              $combinedObjectValues[$field_name] = $value['SKU'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

            /*dimension fields */
          case csconstants::HeightMetric:
            if (!empty($value['MainDimension']['HeightMetric'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['HeightMetric'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::WidthMetric:
            if (!empty($value['MainDimension']['WidthMetric'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['WidthMetric'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DepthMetric:
            if (!empty($value['MainDimension']['DepthMetric'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['DepthMetric'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DiameterMetric:
            if (!empty($value['MainDimension']['DiameterMetric'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['DiameterMetric'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::WeightMetric:
            if (!empty($value['MainDimension']['WeightMetric'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['WeightMetric'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::WeightImperial:
            if (!empty($value['MainDimension']['WeightImperial'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['WeightImperial'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::HeightImperial:
            if (!empty($value['MainDimension']['HeightImperial'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['HeightImperial'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::WidthImperial:
            if (!empty($value['MainDimension']['WidthImperial'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['WidthImperial'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DepthImperial:
            if (!empty($value['MainDimension']['DepthImperial'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['DepthImperial'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DiameterImperial:
            if (!empty($value['MainDimension']['DiameterImperial'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['DiameterImperial'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SquareMeters:
            if (!empty($value['MainDimension']['SquareMeters'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['SquareMeters'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::SquareFeet:
            if (!empty($value['MainDimension']['SquareFeet'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['SquareFeet'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::ImperialDims:
            if (!empty($value['MainDimension']['ImperialDims'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['ImperialDims'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::MetricDims:
            if (!empty($value['MainDimension']['MetricDims'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['MetricDims'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::DimensionDescription:
            if (!empty($value['MainDimension']['DimensionDescription']['Term'])) {
              $combinedObjectValues[$field_name] = $value['MainDimension']['DimensionDescription']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
            /*richtext fields*/


            /*spectrumobject fields*/

          case csconstants::OtherNumberType:
            if (!empty($value['OtherNumberType']['Term'])) {
              $combinedObjectValues[$field_name] = $value['OtherNumberType']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ResponsibleDepartment:
            if (!empty($value['ResponsibleDepartment']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ResponsibleDepartment']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Completeness:
            if (!empty($value['Completeness']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Completeness']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CompletenessDate:
            if (!empty($value['CompletenessDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['CompletenessDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::CompletenessNote:
            if (!empty($value['CompletenessNote'])) {
              $combinedObjectValues[$field_name] = $value['CompletenessNote'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::MovementReferenceNumber:
            if (!empty($value['MovementReferenceNumber'])) {
              $combinedObjectValues[$field_name] = $value['MovementReferenceNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::MovementAuthorizerContactName:
            if (!empty($value['MovementAuthorizer']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['MovementAuthorizer']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::MovementAuthorizationDate:
            if (!empty($value['MovementAuthorizationDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['MovementAuthorizationDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::MovementContactName:
            if (!empty($value['MovementContact']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['MovementContact']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::MovementMethod:
            if (!empty($value['MovementMethod']['Term'])) {
              $combinedObjectValues[$field_name] = $value['MovementMethod']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::MovementMemo:
            if (!empty($value['MovementMemo'])) {
              $combinedObjectValues[$field_name] = $value['MovementMemo'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::MovementReason:
            if (!empty($value['MovementReason']['Term'])) {
              $combinedObjectValues[$field_name] = $value['MovementReason']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::PlannedRemoval:
            if (!empty($value['PlannedRemoval'])) {
              $combinedObjectValues[$field_name] = $value['PlannedRemoval'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::LocationReferenceNameNumber:
            if (!empty($value['LocationReferenceNameNumber'])) {
              $combinedObjectValues[$field_name] = $value['LocationReferenceNameNumber'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::LocationType:
            if (!empty($value['LocationType']['Term'])) {
              $combinedObjectValues[$field_name] = $value['LocationType']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::LocationAccessMemo:
            if (!empty($value['LocationAccessMemo'])) {
              $combinedObjectValues[$field_name] = $value['LocationAccessMemo'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::LocationConditionMemo:
            if (!empty($value['LocationConditionMemo'])) {
              $combinedObjectValues[$field_name] = $value['LocationConditionMemo'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::LocationConditionDate:
            if (!empty($value['LocationConditionDate'])) {
              $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['LocationConditionDate']));
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::LocationSecurityMemo:
            if (!empty($value['LocationSecurityMemo'])) {
              $combinedObjectValues[$field_name] = $value['LocationSecurityMemo'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectNameCurrency:
            if (!empty($value['ObjectNameCurrency']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ObjectNameCurrency']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectNameLevel:
            if (!empty($value['ObjectNameLevel']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ObjectNameLevel']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectNameNote:
            if (!empty($value['ObjectNameNote'])) {
              $combinedObjectValues[$field_name] = $value['ObjectNameNote'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectNameSystem:
            if (!empty($value['ObjectNameSystem']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ObjectNameSystem']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectNameType:
            if (!empty($value['ObjectNameType']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ObjectNameType']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::ObjectNameTitleLanguage:
            if (!empty($value['ObjectNameTitleLanguage']['Term'])) {
              $combinedObjectValues[$field_name] = $value['ObjectNameTitleLanguage']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FieldCollectionMethod:
            if (!empty($value['FieldCollectionMethod']['Term'])) {
              $combinedObjectValues[$field_name] = $value['FieldCollectionMethod']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FieldCollectionPlace:
            if (!empty($value['FieldCollectionPlace'])) {
              $combinedObjectValues[$field_name] = $value['FieldCollectionPlace'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FieldCollectionSourceContactName:
            if (!empty($value['FieldCollectionSource']['ContactName'])) {
              $combinedObjectValues[$field_name] = $value['FieldCollectionSource']['ContactName'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::FieldCollectionMemo:
            if (!empty($value['FieldCollectionMemo'])) {
              $combinedObjectValues[$field_name] = $value['FieldCollectionMemo'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::GeologicalComplexName:
            if (!empty($value['GeologicalComplexName']['Term'])) {
              $combinedObjectValues[$field_name] = $value['GeologicalComplexName']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::Habitat:
            if (!empty($value['Habitat']['Term'])) {
              $combinedObjectValues[$field_name] = $value['Habitat']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::HabitatMemo:
            if (!empty($value['HabitatMemo'])) {
              $combinedObjectValues[$field_name] = $value['HabitatMemo'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StratigraphicUnitName:
            if (!empty($value['StratigraphicUnitName']['Term'])) {
              $combinedObjectValues[$field_name] = $value['StratigraphicUnitName']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StratigraphicUnitType:
            if (!empty($value['StratigraphicUnitType']['Term'])) {
              $combinedObjectValues[$field_name] = $value['StratigraphicUnitType']['Term'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::StratigraphicUnitMemo:
            if (!empty($value['StratigraphicUnitMemo'])) {
              $combinedObjectValues[$field_name] = $value['StratigraphicUnitMemo'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
            /*udf fields*/
          case csconstants::UserDefined1:
            if (!empty($value['UserDefined1'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined1'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined2:
            if (!empty($value['UserDefined2'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined2'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined3:
            if (!empty($value['UserDefined3'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined3'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined4:
            if (!empty($value['UserDefined4'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined4'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined5:
            if (!empty($value['UserDefined5'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined5'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined6:
            if (!empty($value['UserDefined6'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined6'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined7:
            if (!empty($value['UserDefined7'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined7'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined8:
            if (!empty($value['UserDefined8'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined8'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined9:
            if (!empty($value['UserDefined9'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined9'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined10:
            if (!empty($value['UserDefined10'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined10'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined11:
            if (!empty($value['UserDefined11'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined11'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined12:
            if (!empty($value['UserDefined12'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined12'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined13:
            if (!empty($value['UserDefined13'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined13'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined14:
            if (!empty($value['UserDefined14'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined14'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined15:
            if (!empty($value['UserDefined15'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined15'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined16:
            if (!empty($value['UserDefined16'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined16'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined17:
            if (!empty($value['UserDefined17'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined17'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined18:
            if (!empty($value['UserDefined18'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined18'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined19:
            if (!empty($value['UserDefined19'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined19'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined20:
            if (!empty($value['UserDefined20'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined20'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined21:
            if (!empty($value['UserDefined21'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined21'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined22:
            if (!empty($value['UserDefined22'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined22'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined23:
            if (!empty($value['UserDefined23'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined23'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined24:
            if (!empty($value['UserDefined24'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined24'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined25:
            if (!empty($value['UserDefined25'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined25'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
          case csconstants::UserDefined26:
            if (!empty($value['UserDefined26'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined26'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined27:
            if (!empty($value['UserDefined27'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined27'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined28:
            if (!empty($value['UserDefined28'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined28'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined29:
            if (!empty($value['UserDefined29'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined29'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined30:
            if (!empty($value['UserDefined30'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined30'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined31:
            if (!empty($value['UserDefined31'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined31'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined32:
            if (!empty($value['UserDefined32'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined32'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined33:
            if (!empty($value['UserDefined33'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined33'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined34:
            if (!empty($value['UserDefined34'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined34'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined35:
            if (!empty($value['UserDefined35'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined35'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined36:
            if (!empty($value['UserDefined36'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined36'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined37:
            if (!empty($value['UserDefined37'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined37'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined38:
            if (!empty($value['UserDefined38'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined38'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined39:
            if (!empty($value['UserDefined39'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined39'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefined40:
            if (!empty($value['UserDefined40'])) {
              $combinedObjectValues[$field_name] = $value['UserDefined40'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefinedDate1:
            if (!empty($value['UserDefinedDate1'])) {
              $combinedObjectValues[$field_name] = $value['UserDefinedDate1'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefinedDate2:
            if (!empty($value['UserDefinedDate2'])) {
              $combinedObjectValues[$field_name] = $value['UserDefinedDate2'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefinedNumber1:
            if (!empty($value['UserDefinedNumber1'])) {
              $combinedObjectValues[$field_name] = $value['UserDefinedNumber1'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefinedNumber2:
            if (!empty($value['UserDefinedNumber2'])) {
              $combinedObjectValues[$field_name] = $value['UserDefinedNumber2'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefinedCurrency1:
            if (!empty($value['UserDefinedCurrency1'])) {
              $combinedObjectValues[$field_name] = $value['UserDefinedCurrency1'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          case csconstants::UserDefinedCurrency2:
            if (!empty($value['UserDefinedCurrency2'])) {
              $combinedObjectValues[$field_name] = $value['UserDefinedCurrency2'];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;

          default:
            if (!empty($value[$field_name])) {
              $combinedObjectValues[$field_name] = $value[$field_name];
            } else {
              $combinedObjectValues[$field_name] = "";
            }
            break;
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


  public function processImportCollections($Detaildata, $btn_action)
  {
    $data2 = $Detaildata; //End Collection's API Data
    $database = Database::getConnection();
    $table_name2 = 'Collections';

    $collectionIds_API = [];
    //Start Collections
    foreach ($data2['value'] as $collection) {
      $collectionId = $collection['CollectionId'];
      $collectionIds_API[] = $collectionId;
      $collectionName = $collection['CollectionName'];
      $collectionFullName = $collection['FullCollectionName'];

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
    $table_name4 = 'Exhibitions';
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
            // Update the existing record if the ModificationDate has changed
            $database->update($table_name4)
              ->fields($data)
              ->condition('ExhibitionId', $exhibitionId)
              ->condition('ModificationDate', $ModificationDate, '<>')
              ->execute();
          } else {
            // Handle if record doesn't exist
            // Insert data into the table.
            $database->insert($table_name4)
              ->fields($data)
              ->execute();
          }
        } else {
          $database->insert($table_name4)
            ->fields($data)
            ->execute();
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
    $table_name3 = 'Groups';

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
    $table_name6 = 'GroupObjects';

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
    $table_name5 = 'ExhibitionObjects';

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



  public  function deleteDirectory($dir)
  {
    if (is_dir($dir)) {
      $files = array_diff(scandir($dir), array('.', '..'));
      foreach ($files as $file) {
        $path = "$dir/$file";
        if (is_dir($path)) {
          $this->deleteDirectory($path);
        } else {
          unlink($path);
        }
      }
      rmdir($dir);
    }
  }

  /*
  * Returns true if the object is modified
  */
  public function is_object_modified($objectId, $ApiModificationDate)
  {
    $database = Database::getConnection();
    $table_name = 'CSObjects';
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
    $table_name = 'ThumbImages';
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
    $table_name = 'ThumbImages';
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
    $table_name = 'CSObjects';
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
   * Remove the unrequired rows from the 'ThumbImages' table which does not exist in the API response
   */
  public function remove_unrequired_AttachmentIds_from_Database($AttachmentIds_API)
  {
    $database = Database::getConnection();
    $table_name = 'ThumbImages';

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
    $source_table_name = 'clsobjects_fields';

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
  function custom_api_integration_drop_tables($btn_action) {
    if($btn_action == 'update_dataset'){
      //if update dataset then only delete specific tables
      $tables = [
        'ExhibitionObjects',
        'GroupObjects',
        'CSSynced'
      ];
    }else{
      $tables = [
        'CSObjects',
        'Artists',
        'Collections',
        'Groups',
        'Exhibitions',
        'ExhibitionObjects',
        'GroupObjects',
        'ThumbImages',
        'CSSynced'
      ];
    }

    $database = Database::getConnection();
    foreach ($tables as $table_name) {
      if ($database->schema()->tableExists($table_name)) {
        $database->schema()->dropTable($table_name);
        \Drupal::logger('collector_systems')->notice('Dropped table: %table_name', ['%table_name' => $table_name]);
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
      $this->create_table_ExhibitionObjects();
      $this->create_table_GroupObjects();
      $this->create_table_CSSynced();

    }else{
      $this->create_table_CSObjects();
      $this->create_table_Artists();
      $this->create_table_Collections();
      $this->create_table_Groups();
      $this->create_table_Exhibitions();
      $this->create_table_ExhibitionObjects();
      $this->create_table_GroupObjects();
      $this->create_table_ThumbImages();
      $this->create_table_CSSynced();
    }


  }

  function create_table_CSObjects(){
    // Create the new table
    $table_name = 'CSObjects';
    // $selected_fields = ['ArtistName', 'InventoryNumber', 'ArtistCompany']; //temp test
    $selected_fields = $this->get_field_names(); //temp test

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
        ]
      ],
      'primary key' => ['ObjectId'],
    ];

    if($selected_fields){
      // Add dynamic fields if available
      foreach ($selected_fields as $field) {
        switch($field){
          case "ObjectDescription":
            $schema['fields'][$field] = [
                'type' => 'text',
                'size' => 'big'
            ];
            break;
          default:
            $schema['fields'][$field] = [
                'type' => 'text',
            ];
            break;
        }

      }
    }

    // Create the table
    Database::getConnection()->schema()->createTable($table_name, $schema);

  }

  function create_table_Artists(){
    // Create the new table
    $table_name = 'Artists';
    $schema = [
      'fields' => [
        'ArtistId' => [
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'ArtistName' => [
          'type' => 'varchar',
          'length' => 500,
          'not null' => TRUE,
        ],
        'ArtistFirst' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ArtistLast' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ArtistYears' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ArtistNationality' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ArtistLocale' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ArtistBio' => [
          'type' => 'text',
        ],
        'ArtistPhotoAttachment' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'ImagePath' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ModificationDate' => [
          'type' => 'varchar',
          'length' => 500,
        ]
      ],
      'primary key' => ['ArtistId'],
    ];

    // Create the table
    Database::getConnection()->schema()->createTable($table_name, $schema);

  }

  function create_table_Collections(){
    // Create the new table
    $table_name = 'Collections';
    $schema = [
      'fields' => [
        'CollectionId' => [
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'CollectionName' => [
          'type' => 'varchar',
          'length' => 500,
          'not null' => TRUE,
        ],
        'FullCollectionName' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'CollectionImageAttachment' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'ImagePath' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ModificationDate' => [
          'type' => 'varchar',
          'length' => 500,
        ]
      ],
      'primary key' => ['CollectionId'],
    ];

    // Create the table
    Database::getConnection()->schema()->createTable($table_name, $schema);

  }

  function create_table_Groups(){
    // Create the new table
    $table_name = 'Groups';
    $schema = [
      'fields' => [
        'GroupId' => [
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'GroupDescription' => [
          'type' => 'varchar',
          'length' => 500,
          'not null' => TRUE,
        ],
        'GroupMemo' => [
          'type' => 'text',
          'size' => 'big'
        ],
        'GroupImageAttachment' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'ImagePath' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ModificationDate' => [
          'type' => 'varchar',
          'length' => 500,
        ]
      ],
      'primary key' => ['GroupId'],
    ];

    // Create the table
    Database::getConnection()->schema()->createTable($table_name, $schema);

  }

  function create_table_Exhibitions(){
    // Create the new table
    $table_name = 'Exhibitions';
    $schema = [
      'fields' => [
        'ExhibitionId' => [
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'ExhibitionSubject' => [
          'type' => 'varchar',
          'length' => 500,
          'not null' => TRUE,
        ],
        'ExhibitionLocation' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ExhibitionDate' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ExhibitionMemo' => [
          'type' => 'text',
          // 'length' => 500,
        ],
        'ExhibitionImageAttachment' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'ImagePath' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'ModificationDate' => [
          'type' => 'varchar',
          'length' => 500,
        ]
      ],
      'primary key' => ['ExhibitionId'],
    ];
    // Create the table
    Database::getConnection()->schema()->createTable($table_name, $schema);

  }

  function create_table_ExhibitionObjects(){
    // Create the new table
    $table_name = 'ExhibitionObjects';
    $schema = [
      'fields' => [
        'ExhibitionId' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'ObjectId' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'ObjectImage' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'ObjectImagePath' => [
          'type' => 'text',
        ],
      ],
      'primary key' => ['ExhibitionId', 'ObjectId'],
    ];
    // Create the table
    Database::getConnection()->schema()->createTable($table_name, $schema);

  }

  function create_table_GroupObjects(){
    // Create the new table
    $table_name = 'GroupObjects';
    $schema = [
      'fields' => [
        'GroupId' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'ObjectId' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'ObjectImage' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'ObjectImagePath' => [
          'type' => 'text',
        ],
      ],
      'primary key' => ['GroupId', 'ObjectId'],
    ];
    // Create the table
    Database::getConnection()->schema()->createTable($table_name, $schema);

  }

  function create_table_CSSynced(){
    $table_name = 'CSSynced';
    $schema = [
      'fields' => [
        'LastSyncedDateTime' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ],
        'LastSyncedBy' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ],

      ]
    ];
    Database::getConnection()->schema()->createTable($table_name, $schema);

  }

  public function update_CSSynced_table(){
    $table_name = 'CSSynced';
    $database = Database::getConnection();

    // Get the current user object.
    $current_user = \Drupal::currentUser();

    // Check if the user is authenticated.
    if ($current_user->isAuthenticated()) {
      // Truncate the CSSynced table.
      $truncate_query = $database->truncate($table_name);
      $truncate_query->execute();


      // Get the user name.
      $username = $current_user->getAccountName();
      // Output the username.
      $current_date_time = new DrupalDateTime();
      $formatted_date_time = $current_date_time->format('m/d/y H:i:s');

      $data = array(
        'LastSyncedBy' => $username,
        'LastSyncedDateTime' => $formatted_date_time,

      );


      $database->insert($table_name)
      ->fields($data)
      ->execute();
    }

  }


  function create_table_ThumbImages(){
    // Create the new table
    $table_name = 'ThumbImages';
    $schema = [
      'fields' => [
        'ID' => [
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'ThumbURL' => [
          'type' => 'varchar',
          'length' => 255, // Set an appropriate length
          'not null' => TRUE,
        ],
        'ObjectId' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'thumb_size_URL' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'thumb_size_URL_path' => [
          'type' => 'text',
        ],
        'slide_show_attachment' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'slide_show_URL_path' => [
          'type' => 'text',
        ],
        'MainImageAttachmentId' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'object_image_attachment' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'object_image_path' => [
          'type' => 'text',
        ],
        'AttachmentId' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'attachment_description' => [
          'type' => 'text'
        ],
        'keywords' => [
          'type' => 'blob',
          'size' => 'big',
        ],
        'ModificationDate' => [
          'type' => 'varchar',
          'length' => 500,
        ]

      ],
      'primary key' => ['ID'],
      'unique keys' => [
        'ThumbURL' => ['ThumbURL'],
      ],
    ];
    // Create the table
    Database::getConnection()->schema()->createTable($table_name, $schema);

  }
}
