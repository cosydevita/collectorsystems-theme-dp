<?php
namespace Drupal\collector_systems\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;


class OtherImagesImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cs_other_images_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $request = \Drupal::request();
    $get_save_option = $request->query->get('save_option', '');
    $selected_option = '';
    if($get_save_option == 'database'){
      $selected_option = 'save_to_database';
    }elseif($get_save_option == 'directory'){
      $selected_option = 'save_to_directory';
    }


    $form['save_option'] = [
      '#type' => 'radios',
      '#title' => $this->t('Save Option'),
      '#options' => [
        'save_to_database' => $this->t('Save to Database'),
        'save_to_directory' => $this->t('Save to Directory'),
      ],
      '#default_value' => $selected_option,
      '#required' => TRUE,

    ];
    // Add a button to trigger the batch.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import Other Images'),
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
  protected function getDataForProcessing() {


    $chunk_size = 10; // number of objects to process in a batch

    $collector_systemsts_get_api_data = \Drupal::service('collector_systems.collector_systemsts_get_api_data');
    $import_types = [
      'ArtistsImages',
      'CollectionsImages',
      'GroupsImages',
      'ExhibitionsImages',
      'GroupsObjectsImages',
      'ExhibitionsObjectsImages'
    ];
    $data = [];

    foreach($import_types as $import_type){
      if($import_type == 'ArtistsImages'){
        $total_count = $collector_systemsts_get_api_data->getTotalArtistsCount();
      }elseif($import_type == 'CollectionsImages'){
        $total_count = $collector_systemsts_get_api_data->getTotalColletionsCount();
      }elseif($import_type == 'GroupsImages'){
        $total_count = $collector_systemsts_get_api_data->getTotalGroupsCount();
      }elseif($import_type == 'ExhibitionsImages'){
        $total_count = $collector_systemsts_get_api_data->getTotalExhibitionsCount();
      }elseif($import_type == 'GroupsObjectsImages'){
        $total_count = $collector_systemsts_get_api_data->getTotalGroupsObjectsCount();
      }elseif($import_type == 'ExhibitionsObjectsImages'){
        $total_count = $collector_systemsts_get_api_data->getTotalExhibitionsObjectsCount();

      }

      if($total_count > 0){
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
  protected function startBatchProcess($data, $selected_option) {

    $operations = [];
    foreach ($data as $item) {
      $operations[] = [[$this, 'processItem'], [$item, $selected_option]];
    }

    // Define the batch.
    $batch = [
      'title' => t('Collector Systems: Importing Other Images...'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('An error occurred during the download.'),
      'operations' => $operations,
      'finished' => [$this, 'batchFinished'],
    ];

    // Set and process the batch.
    batch_set($batch);
    // batch_process();
  }

  /**
   * Batch operation to process each item.
   */
  public function processItem($item, $selected_option, &$context) {

    $collector_systemsts_get_api_data = \Drupal::service('collector_systems.collector_systemsts_get_api_data');
    $import_type = $item['import_type'];
    $chunk_size = $item['chunk_size'];
    $offset = $item['offset'];
    $current_batch_number = $item['current_batch_number'];

    if($import_type == 'ArtistsImages'){
      $getApiArtistsImagesData = $collector_systemsts_get_api_data->getApiArtistsImagesData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiArtistsImagesData, TRUE);

    }elseif($import_type == 'CollectionsImages'){
      $getApiCollectionsImagesData = $collector_systemsts_get_api_data->getApiCollectionsImagesData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiCollectionsImagesData, TRUE);

    }elseif($import_type == 'GroupsImages'){
      $getApiGroupsImagesData = $collector_systemsts_get_api_data->getApiGroupsImagesData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiGroupsImagesData, TRUE);
    }elseif($import_type == 'ExhibitionsImages'){
      $getApiExhibitionsImagesData = $collector_systemsts_get_api_data->getApiExhibitionsImagesData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiExhibitionsImagesData, TRUE);
    }elseif($import_type == 'GroupsObjectsImages'){
      $getApiGroupsObjectsImagesData = $collector_systemsts_get_api_data->getApiGroupsObjectsImagesData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiGroupsObjectsImagesData, TRUE);

    }elseif($import_type == 'ExhibitionsObjectsImages'){

      $getApiExhibitionsObjectsImagesData = $collector_systemsts_get_api_data->getApiExhibitionsObjectsImagesData($chunk_size, $offset);
      // Decode JSON data.
      $Detaildata = json_decode($getApiExhibitionsObjectsImagesData, TRUE);

    }


    if ($selected_option == 'save_to_database') {
      $this->processImportToDatabase($Detaildata, $current_batch_number, $import_type);
    }elseif($selected_option == 'save_to_directory') {
      $this->processImportToDirectory($Detaildata, $current_batch_number, $import_type);
    }
  }

  /**
   * Batch finished callback.
   */
  public function batchFinished($success, $results, $operations) {
    if ($success) {
      \Drupal::messenger()->addMessage(t('Collector Systems Images Import: Import is successfully completed.'));

      $redirect_url = Url::fromRoute('custom_api_integration.dashboard')->toString();
      return new RedirectResponse($redirect_url);

    }
    else {
      \Drupal::messenger()->addError(t('Collector Systems Images Import: An error occurred during batch processing.'));
    }
  }

   /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $selected_option = $form_state->getValue('save_option');
    $data = $this->getDataForProcessing();
    $this->startBatchProcess($data, $selected_option);

  }




  public function processImportToDatabase($Detaildata, $current_batch_number, $import_type){
    $database = Database::getConnection();
    $artist_table = 'Artists';
    $group_table = 'Groups';
    $exhibition_table = 'Exhibitions';
    $collection_table = 'Collections';
    $object_table = 'CSObjects';
    $exhibitionObj_table = 'ExhibitionObjects';
    $groupObj_table = 'GroupObjects';


    if ($current_batch_number == 0 && $import_type == 'ArtistsImages') {
      //This will run only once at the first batch
      \Drupal::logger(channel: 'custom_api_integration')->debug('Started importing Artists Images to Database');

        // Delete Artist's Images
        $database->update($artist_table)
          ->fields(['ArtistPhotoAttachment' => null])
          ->execute();

        // Delete Group's Images
        $database->update($group_table)
          ->fields(['GroupImageAttachment' => null])
          ->execute();

        // Delete Exhibition's Images
        $database->update($exhibition_table)
          ->fields(['ExhibitionImageAttachment' => null])
          ->execute();

        // Delete Collection's Images
        $database->update($collection_table)
          ->fields(['CollectionImageAttachment' => null])
          ->execute();


        // Delete Object's MainImages
        $database->update($object_table)
          ->fields(['main_image_attachment' => null])
          ->execute();

        // Delete Object's ObjectImages
        $database->update($object_table)
          ->fields(['object_image_attachment' => null])
          ->execute();

        // Delete Object's ThumbSizeImages
        $database->update($object_table)
          ->fields(['thumb_size_URL' => null])
          ->execute();

        // Delete Artist's ImagePath
        $database->update($artist_table)
          ->fields(['ImagePath' => null])
          ->execute();

        // Delete Group's ImagePath
        $database->update($group_table)
          ->fields(['ImagePath' => null])
          ->execute();

        // Delete Collection's ImagePath
        $database->update($collection_table)
          ->fields(['ImagePath' => null])
          ->execute();

      // Delete Exhibition's ImagePath
      $database->update($exhibition_table)
        ->fields(['ImagePath' => null])
        ->execute();

      // Delete Object's MainImagePath
      $database->update($object_table)
        ->fields(['main_image_path' => null])
        ->execute();

      // Delete Object's ObjectImagePath
      $database->update($object_table)
        ->fields(['object_image_path' => null])
        ->execute();

      // Delete Object's ThumbSizePath
      $database->update($object_table)
        ->fields(['thumb_size_URL_path' => null])
        ->execute();

      // Delete GroupObject's Images
      $database->update($groupObj_table)
        ->fields(['ObjectImage' => null])
        ->execute();

      // Delete GroupObject's Paths
      $database->update($groupObj_table)
        ->fields(['ObjectImagePath' => null])
        ->execute();

      // Delete ExhibitionObject's Images
      $database->update($exhibitionObj_table)
        ->fields(['ObjectImage' => null])
        ->execute();

      // Delete ExhibitionObject's Paths
      $database->update($exhibitionObj_table)
        ->fields(['ObjectImagePath' => null])
        ->execute();

      // Delete Thumb Images
        $thumbImage_table = 'ThumbImages';
        $database->update($thumbImage_table)
          ->fields(['thumb_size_URL' => null])
          ->execute();

        // Delete Thumb Images Path
        $database->update($thumbImage_table)
          ->fields(['thumb_size_URL_path' => null])
          ->execute();

        // Delete Thumb Object Images
        $database->update($thumbImage_table)
          ->fields(['object_image_attachment' => null])
          ->execute();



      //Delete Directory
      //  $allImagesDirectory = __DIR__ . '/All Images';
      $allImagesDirectory = PublicStream::basePath() . '/All Images';


      // Delete all contents inside "All Images"
      $this->deleteDirectory($allImagesDirectory);
    }

    if($import_type == 'ArtistsImages'){
      $ArtistPhoto = $Detaildata;

       //Save Artist Images
      foreach($ArtistPhoto['value'] as $photo)
      {
        $ArtistPhotoAttachment =  $photo['ArtistPhotoAttachment'];
        if($ArtistPhotoAttachment){
          $mainImageURL = $photo['ArtistPhotoAttachment']['DetailLargeURL'];
          $curlMain = curl_init($mainImageURL);
          curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);
          $mainImageData = curl_exec($curlMain);
          curl_close($curlMain);
          $id = $photo['ArtistId'];
          //  $update_photo = $wpdb->prepare("UPDATE $artist_table SET ArtistPhotoAttachment = %s WHERE ArtistId = %d",$mainImageData,$id);
          //  $wpdb->query($update_photo);
          $update_photo = $database->update($artist_table)
          ->fields(['ArtistPhotoAttachment' => $mainImageData])
          ->condition('ArtistId', $id)
          ->execute();

        }


      }
    }

    if($import_type == 'CollectionsImages'){
      $CollectionPhoto = $Detaildata;
       //Save Collection Images
      foreach($CollectionPhoto['value'] as $photo)
      {
        $CollectionImageAttachment = $photo['CollectionImageAttachment'];
        if($CollectionImageAttachment){
          $mainImageURL = $photo['CollectionImageAttachment']['DetailLargeURL'] ?? null;
          $curlMain = curl_init($mainImageURL);
          curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);
          $mainImageData = curl_exec($curlMain);
          curl_close($curlMain);
          $id = $photo['CollectionId'];
          //  $update_photo = $wpdb->prepare("UPDATE $collection_table SET CollectionImageAttachment = %s WHERE CollectionId = %d",$mainImageData,$id);
          //  $wpdb->query($update_photo);
          $update_photo = $database->update($collection_table)
          ->fields(['CollectionImageAttachment' => $mainImageData])
          ->condition('CollectionId', $id)
          ->execute();
        }
      }
    }

    if($import_type == 'GroupsImages'){
      $GroupImages = $Detaildata;
      //Save Groups Images
      foreach ($GroupImages['value'] as $photo)
      {
        $GroupImageAttachment = $photo['GroupImageAttachment'];
        if($GroupImageAttachment){
          $mainImageURL = $photo['GroupImageAttachment']['DetailLargeURL'] ?? null;
          $curlMain = curl_init($mainImageURL);
          curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);
          $mainImageData = curl_exec($curlMain);
          curl_close($curlMain);
          $id = $photo['GroupId'];
          //  $upload_photo = $wpdb->prepare("UPDATE $group_table SET GroupImageAttachment = %s WHERE GroupId = %d" , $mainImageData , $id);
          //  $wpdb->query($upload_photo);
          $upload_photo = $database->update($group_table)
          ->fields(['GroupImageAttachment' => $mainImageData])
          ->condition('GroupId', $id)
          ->execute();
        }

      }
    }

    if($import_type == 'ExhibitionsImages'){
      $ExhibitionPhoto = $Detaildata;

      //Save Exhibitions Images
      foreach ($ExhibitionPhoto['value'] as $photo)
      {
        $ExhibitionImageAttachment = $photo['ExhibitionImageAttachment'];
        if($ExhibitionImageAttachment){
          $mainImageURL = $photo['ExhibitionImageAttachment']['DetailLargeURL'] ?? null;
          $curlMain = curl_init($mainImageURL);
          curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);
          $mainImageData = curl_exec($curlMain);
          curl_close($curlMain);
          $id = $photo['ExhibitionId'];
          //  $update_image = $wpdb->prepare("UPDATE $exhibition_table SET ExhibitionImageAttachment = %s WHERE ExhibitionId = %d" , $mainImageData , $id);
          //  $wpdb->query($update_image);
          $update_image = $database->update($exhibition_table)
          ->fields(['ExhibitionImageAttachment' => $mainImageData])
          ->condition('ExhibitionId', $id)
          ->execute();
        }

      }
    }

    if($import_type == 'GroupsObjectsImages'){
      $GroupObjects = $Detaildata;
      //Start GroupObjects
     foreach($GroupObjects['value'] as $obj)
     {
        $MainImageAttachment = $obj['Object']['MainImageAttachment'];
        if($MainImageAttachment){
          $object_image = $obj['Object']['MainImageAttachment']['DetailLargeURL'] ?? null;
            $curlMain = curl_init($object_image);
            curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);
            $mainImageData = curl_exec($curlMain);
            curl_close($curlMain);
            $object_id = $obj['Object']['ObjectId'];
          //  $update_groupObj_images = $wpdb->prepare("UPDATE $groupObj_table SET ObjectImage = %s WHERE ObjectId = %d", $mainImageData , $object_id);
          //  $wpdb->query($update_groupObj_images);
          $update_groupObj_images =  $database->update($groupObj_table)
          ->fields(['ObjectImage' => $mainImageData])
          ->condition('ObjectId', $object_id)
          ->execute();

        }

     }//End GroupObjects

    }

    if($import_type == 'ExhibitionsObjectsImages'){
      $ExhibitionObjects = $Detaildata;
      //Start GroupObjects
     foreach($ExhibitionObjects['value'] as $obj)
     {
        $MainImageAttachment = $obj['Object']['MainImageAttachment'];
        if($MainImageAttachment){
          $object_image = $obj['Object']['MainImageAttachment']['DetailLargeURL'] ?? null;
          $curlMain = curl_init($object_image);
          curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);
          $mainImageData = curl_exec($curlMain);
          curl_close($curlMain);
          $object_id = $obj['Object']['ObjectId'];
          //  $update_exhibitionObj_images = $wpdb->prepare("UPDATE $exhibitionObj_table SET ObjectImage = %s WHERE ObjectId = %d", $mainImageData , $object_id);
          //  $wpdb->query($update_exhibitionObj_images);
          $update_exhibitionObj_images = $database->update($exhibitionObj_table)
          ->fields(['ObjectImage' => $mainImageData])
          ->condition('ObjectId', $object_id)
          ->execute();
        }

     }//End GroupObjects
    }

  }

  public function processImportToDirectory($Detaildata, $current_batch_number, $import_type){

    $database = Database::getConnection();
    $artist_table = 'Artists';
    $group_table = 'Groups';
    $exhibition_table = 'Exhibitions';
    $collection_table = 'Collections';
    $object_table = 'CSObjects';
    $exhibitionObj_table = 'ExhibitionObjects';
    $groupObj_table = 'GroupObjects';

    $artistDirectory =  PublicStream::basePath().'/All Images/Artists';
    $collectionDirectory =  PublicStream::basePath().'/All Images/Collections';
    $exhibitionDirectory =  PublicStream::basePath().'/All Images/Exhibitions';
    $groupDirectory =  PublicStream::basePath().'/All Images/Groups';
    $groupObjDirectory =  PublicStream::basePath().'/All Images/GroupObjects';
    $exhibitionObjDirectory = PublicStream::basePath().'/All Images/ExhibitionObjects';


    if ($current_batch_number == 0 && $import_type == 'ArtistsImages') {
      //This will run only once at the first batch
      \Drupal::logger(channel: 'custom_api_integration')->debug('Started importing Artists Images to Directory');


      //Create Artist Directory
      if (!file_exists($artistDirectory))
      {
          mkdir($artistDirectory, 0755, true);
      }

      //Create Group Directory
      if (!file_exists($groupDirectory))
      {
          mkdir($groupDirectory, 0755, true);
      }

      //Create Collection Directory
      if (!file_exists($collectionDirectory))
      {
          mkdir($collectionDirectory, 0755, true);
      }

      //Create Exhibition Directory
      if (!file_exists($exhibitionDirectory))
      {
          mkdir($exhibitionDirectory, 0755, true);
      }

      //Create GroupObjects Directory

      if (!file_exists($groupObjDirectory))
      {
          mkdir($groupObjDirectory, 0755, true);
      }

      //Create ExhibitionObjects Directory
      if (!file_exists($exhibitionObjDirectory))
      {
          mkdir($exhibitionObjDirectory, 0755, true);
      }




      // Place null in the ImagePath for Artists.
      $database->update($artist_table)
        ->fields(['ImagePath' => null, 'ArtistPhotoAttachment' => null])
        ->execute();

      // Place null in the ImagePath and CollectionImageAttachment for Collections.
      $database->update($collection_table)
        ->fields(['ImagePath' => null, 'CollectionImageAttachment' => null])
        ->execute();

      // Place null in the ImagePath and GroupImageAttachment for Groups.
      $database->update($group_table)
        ->fields(['ImagePath' => null, 'GroupImageAttachment' => null])
        ->execute();

      // Place null in the ImagePath and ExhibitionImageAttachment for Exhibitions.
      $database->update($exhibition_table)
        ->fields(['ImagePath' => null, 'ExhibitionImageAttachment' => null])
        ->execute();

      // Place null in various fields for CSObjects.
      $database->update($object_table)
        ->fields([
          'main_image_path' => null,
          'object_image_path' => null,
          'main_image_attachment' => null,
          'object_image_attachment' => null,
          'thumb_size_URL' => null,
          'thumb_size_URL_path' => null,
        ])
        ->execute();

      // Place null in the ObjectImage and ObjectImagePath for GroupObjects.
      $database->update($groupObj_table)
        ->fields(['ObjectImage' => null, 'ObjectImagePath' => null])
        ->execute();

      // Place null in the ObjectImage and ObjectImagePath for ExhibitionObjects.
      $database->update($exhibitionObj_table)
        ->fields(['ObjectImage' => null, 'ObjectImagePath' => null])
        ->execute();
    }

    if($import_type == 'ArtistsImages'){
      $ArtistPhoto = $Detaildata;
      \Drupal::logger(channel: 'custom_api_integration')->debug('Started importing Artists Images to Directory');
      //Save Artist Images in the Artists Directory
      foreach($ArtistPhoto['value'] as $photo)
      {
        $ArtistPhotoAttachment = $photo['ArtistPhotoAttachment'];

        if($ArtistPhotoAttachment){
          $artist_image_path = $artistDirectory . '/' . $photo['ArtistPhotoAttachment']['FileName'];
          $mainImageURL = $photo['ArtistPhotoAttachment']['DetailLargeURL'];

          $curlMain = curl_init($mainImageURL);
          curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);

          $mainImageData = curl_exec($curlMain);

          curl_close($curlMain);
          file_put_contents($artist_image_path, $mainImageData);
          // //for local hosts only
          // $artist_image_path = preg_replace("#.*?\\\\wp-content#", "\\wp-content", $artist_image_path);
          $id = $photo['ArtistId'];
          $attachment = $photo['ArtistPhotoAttachment'];

          if ($attachment !== null)
          {
              // $add_directory_path = $wpdb->prepare("UPDATE $artist_table SET ImagePath = %s WHERE ArtistId = %d", $artist_image_path, $id);
              // $wpdb->query($add_directory_path);
              $add_directory_path = $database->update($artist_table)
              ->fields(['ImagePath' => $artist_image_path])
              ->condition('ArtistId', $id)
              ->execute();
          }

        }

      }
    }elseif($import_type == 'CollectionsImages'){
      $CollectionPhoto = $Detaildata;
      //Save Collection Images in the Collections Directory
      foreach($CollectionPhoto['value'] as $photo)
      {
        $CollectionImageAttachment = $photo['CollectionImageAttachment'];
        if($CollectionImageAttachment){
          $collection_image_path = $collectionDirectory . '/' . $photo['CollectionImageAttachment']['FileName'];
          $mainImageURL = $photo['CollectionImageAttachment']['DetailLargeURL'];
          if($mainImageURL){
            $curlMain = curl_init($mainImageURL);
            curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);

            $mainImageData = curl_exec($curlMain);
            curl_close($curlMain);
            file_put_contents($collection_image_path, $mainImageData);
            //for local hosts only
            // $collection_image_path = preg_replace("#.*?\\\\wp-content#", "\\wp-content", $collection_image_path);
            $attachment = $photo['CollectionImageAttachment'];
            $id = $photo['CollectionId'];
            if($attachment!==null)
            {
                // $add_directory_path = $wpdb->prepare("UPDATE $collection_table SET ImagePath = %s WHERE CollectionId = %d", $collection_image_path, $id);
                // $wpdb->query($add_directory_path);
                $add_directory_path = $database->update($collection_table)
                ->fields(['ImagePath' => $collection_image_path])
                ->condition('CollectionId', $id)
                ->execute();
            }

          }

        }
      }
    }elseif($import_type == 'GroupsImages'){
      $GroupImages = $Detaildata;
      //Save Group Images in the Groups Directory
      foreach($GroupImages['value'] as $photo)
      {
        $GroupImageAttachment = $photo['GroupImageAttachment'];
        if($GroupImageAttachment){
          $group_image_path = $groupDirectory . '/' . $photo['GroupImageAttachment']['FileName'];
          $mainImageURL = $photo['GroupImageAttachment']['DetailLargeURL'];
          if($mainImageURL){
            $curlMain = curl_init($mainImageURL);
            curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);

            $mainImageData = curl_exec($curlMain);

            curl_close($curlMain);
            file_put_contents($group_image_path, $mainImageData);
            //for local hosts only
            // $group_image_path = preg_replace("#.*?\\\\wp-content#", "\\wp-content", $group_image_path);
            $attachment = $photo['GroupImageAttachment'];
            $id = $photo['GroupId'];
            if($attachment!==null)
            {
                // $add_directory_path = $wpdb->prepare("UPDATE $group_table SET ImagePath = %s WHERE GroupId = %d", $group_image_path, $id);
                // $wpdb->query($add_directory_path);
                $add_directory_path = $database->update($group_table)
                ->fields(['ImagePath' => $group_image_path])
                ->condition('GroupId', $id)
                ->execute();
            }
          }

        }
      }
    }elseif($import_type == 'ExhibitionsImages'){
      $ExhibitionPhoto = $Detaildata;
      //Save Exhibition Images in the Exhibitions Directory
      foreach($ExhibitionPhoto['value'] as $photo)
      {
        $ExhibitionImageAttachment = $photo['ExhibitionImageAttachment'];
        if($ExhibitionImageAttachment){
          $exhibition_image_path = $exhibitionDirectory . '/' . $photo['ExhibitionImageAttachment']['FileName'];
          $mainImageURL = $photo['ExhibitionImageAttachment']['DetailLargeURL'];
          if($mainImageURL){
            $curlMain = curl_init($mainImageURL);
            curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);

            $mainImageData = curl_exec($curlMain);
            //for local hosts only
            // $exhibition_image_path = preg_replace("#.*?\\\\wp-content#", "\\wp-content", $exhibition_image_path);
            curl_close($curlMain);
            file_put_contents($exhibition_image_path, $mainImageData);

            $attachment = $photo['ExhibitionImageAttachment'];
            $id = $photo['ExhibitionId'];
            if($attachment!==null)
            {
                // $add_directory_path = $wpdb->prepare("UPDATE $exhibition_table SET ImagePath = %s WHERE ExhibitionId = %d", $exhibition_image_path, $id);
                // $wpdb->query($add_directory_path);
                $add_directory_path = $database->update($exhibition_table)
                ->fields(['ImagePath' => $exhibition_image_path])
                ->condition('ExhibitionId', $id)
                ->execute();
            }
          }
        }

      }

    }elseif($import_type == 'GroupsObjectsImages'){
      $GroupObjects = $Detaildata;
      //Start GroupObjects
      foreach($GroupObjects['value'] as $obj)
      {
          $MainImageAttachment = $obj['Object']['MainImageAttachment'];
          if($MainImageAttachment){
            $groupObj_image_path = $groupObjDirectory . '/' . $obj['Object']['MainImageAttachment']['FileName'];
            $object_image = $obj['Object']['MainImageAttachment']['DetailLargeURL'];
            if($object_image){
              $curlMain = curl_init($object_image);
              curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);
              $mainImageData = curl_exec($curlMain);
              curl_close($curlMain);
              file_put_contents($groupObj_image_path, $mainImageData);
              //for local hosts only
              // $groupObj_image_path = preg_replace("#.*?\\\\wp-content#", "\\wp-content", $groupObj_image_path);
              $object_id = $obj['Object']['ObjectId'];
              $attachment = $obj['Object']['MainImageAttachment'];
              if($attachment!==null)
              {
                  // $add_directory_path = $wpdb->prepare("UPDATE $groupObj_table SET ObjectImagePath = %s WHERE ObjectId = %d", $groupObj_image_path, $object_id);
                  // $wpdb->query($add_directory_path);
                  $add_directory_path = $database->update($groupObj_table)
                  ->fields(['ObjectImagePath' => $groupObj_image_path])
                  ->condition('ObjectId', $object_id)
                  ->execute();
              }
            }
          }


      }//End GroupObjects

    }elseif($import_type == 'ExhibitionsObjectsImages'){
      $ExhibitionObjects = $Detaildata;
      //Start ExhibitionObjects
      foreach($ExhibitionObjects['value'] as $obj)
      {
          $MainImageAttachment = $obj['Object']['MainImageAttachment'];
          if($MainImageAttachment){
            $exhibitionObj_image_path = $exhibitionObjDirectory . '/' . $obj['Object']['MainImageAttachment']['FileName'];
            $object_image = $obj['Object']['MainImageAttachment']['DetailLargeURL'];
            if($object_image){
              $curlMain = curl_init($object_image);
              curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);
              $mainImageData = curl_exec($curlMain);
              curl_close($curlMain);
              file_put_contents($exhibitionObj_image_path, $mainImageData);
              //for local hosts only
              // $exhibitionObj_image_path = preg_replace("#.*?\\\\wp-content#", "\\wp-content", $exhibitionObj_image_path);
              $object_id = $obj['Object']['ObjectId'];
              $attachment = $obj['Object']['MainImageAttachment'];
              if($attachment!==null)
              {
                  // $add_directory_path = $wpdb->prepare("UPDATE $exhibitionObj_table SET ObjectImagePath = %s WHERE ObjectId = %d", $exhibitionObj_image_path, $object_id);
                  // $wpdb->query($add_directory_path);
                  $add_directory_path = $database->update($exhibitionObj_table)
                  ->fields(['ObjectImagePath' => $exhibitionObj_image_path])
                  ->condition('ObjectId', $object_id)
                  ->execute();
              }
            }
          }

      }//End ExhibitionObjects
    }

  }


  public  function deleteDirectory($dir)
    {
      if (is_dir($dir))
      {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file)
        {
            $path = "$dir/$file";
            if (is_dir($path))
            {
                $this->deleteDirectory($path);
            }
            else
            {
                unlink($path);
            }
        }
        rmdir($dir);
      }
    }


}
