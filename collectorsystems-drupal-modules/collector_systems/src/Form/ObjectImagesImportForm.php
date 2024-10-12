<?php
namespace Drupal\collector_systems\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\StreamWrapper\PublicStream;


class ObjectImagesImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cs_objects_images_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['save_option'] = [
      '#type' => 'radios',
      '#title' => $this->t('Save Option'),
      '#options' => [
        'save_to_database' => $this->t('Save to Database'),
        'save_to_directory' => $this->t('Save to Directory'),
      ],
      '#default_value' => '',
      '#required' => TRUE,

    ];
    // Add a button to trigger the batch.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import Object Images'),
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
    $collector_systemsts_get_api_data = \Drupal::service('collector_systems.collector_systemsts_get_api_data');
    $total_objects_count = $collector_systemsts_get_api_data->getApiTotalObjectsCount();

    $chunk_size = 10; // number of objects to process in a batch
    $total_chunks = ceil($total_objects_count / $chunk_size);
    $data = [];

    for ($i = 0; $i < $total_chunks; $i++) {
      // Calculate the offset for each API call
      $offset = $i * $chunk_size;
      $data[] = [
        'total_chunks' => $total_chunks,
        'chunk_size' => $chunk_size,
        'offset' => $offset,
        'current_batch_number' => $i
      ];
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
      'title' => t('Importing Objects Images...'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('An error occurred during the download.'),
      'operations' => $operations,
      'finished' => '::batchFinishedCallback',
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
    $chunk_size = $item['chunk_size'];
    $offset = $item['offset'];
    $current_batch_number = $item['current_batch_number'];
    $getApiObjectImagesData = $collector_systemsts_get_api_data->getApiObjectImagesData($chunk_size, $offset);

    // Decode JSON data.
    $Detaildata = json_decode($getApiObjectImagesData, TRUE);

    if ($selected_option == 'save_to_database') {
      $this->processImportToDatabase($Detaildata, $current_batch_number);
    }elseif($selected_option == 'save_to_directory') {
      $this->processImportToDirectory($Detaildata, $current_batch_number);
    }
  }

  /**
   * Batch finished callback.
   */
  public function batchFinishedCallback($success, $results, $operations) {
    if ($success) {
      \Drupal::messenger()->addMessage(t('Batch processing complete.'));
    }
    else {
      \Drupal::messenger()->addError(t('An error occurred during batch processing.'));
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




  public function processImportToDatabase($Detaildata, $current_batch_number){
    $database = Database::getConnection();
    // Define the table names.
    $table_name = $database->prefixTables('CSObjects');
    $object_table = $database->prefixTables('CSObjects');
    $thumbImage_table = $database->prefixTables('ThumbImages');

    // // Truncate the ThumbImages table.
    // $truncate_query = $database->truncate($thumbImage_table);
    // $truncate_query->execute();

    if ($current_batch_number == 0) {
      //This will run only once at the first batch
      $allImagesDirectory = PublicStream::basePath() . '/All Images' . '/Objects';
      if(file_exists( $allImagesDirectory ))
      {
          $this->deleteDirectory($allImagesDirectory);
      }

      $remove_directory_data = $database->update($thumbImage_table)
      ->fields([
        'thumb_size_URL_path' => NULL,
        'object_image_path' => NULL,
      ])
      ->execute();

      $remove_object_directory_data = $database->update($object_table)
      ->fields([
        'main_image_path' => NULL,
        'thumb_size_URL_path' => NULL,
        'object_image_path' => NULL,
      ])
      ->execute();
    }


     //to store ObjectImageAttachments AttachmentIds
     $AttachmentIds_API = [];

     //Save Objects Images
     foreach ($Detaildata['value'] as $image)
     {
         $mainImageURL = $image['MainImageAttachment']['DetailLargeURL'] ?? null;
         $mainImageDescription = $image['MainImageAttachment']['Description'] ?? null;


         $mainID = $image['MainImageAttachmentId'] ?? null;
         $objectId = $image['ObjectId'];
         $objectApiModificationDate = $image['ModificationDate'] ?? $image['CreationDate'];


         $objectImages = $image['ObjectImageAttachments'] ?? null;

         //Object has multiple image attachments. Object’s modification date will only update if main image attachment is added/updated. It will not change if any other image is added/updated.
         $is_exist_main_image_attachment = $this->is_exist_main_image_attachment($objectId, $mainID);
         $is_object_modified = $this->is_object_modified($objectId, $objectApiModificationDate);

         //if there is already the data for the same MainImageAttachmentId then do not insert the new data
         if(!$is_exist_main_image_attachment || $is_object_modified){
           $curlMain = curl_init($mainImageURL);
           curl_setopt($curlMain, CURLOPT_RETURNTRANSFER, true);

           $mainImageData = curl_exec($curlMain);

           curl_close($curlMain);

           if ($mainImageData !== false)
           {
               $id = $image['ObjectId'];

               $insertImage = $database->update($object_table)
               ->fields([
                 'main_image_attachment' => $mainImageData,
                 'MainImageAttachmentId' => $mainID,
                 'main_image_attachment_description' => $mainImageDescription

                 ])
               ->condition('ObjectId', $id)
               ->execute();
           }
         }

         if (!empty($objectImages))
         {

             foreach ($objectImages as $objectImage)
             {
                 $objectImageDetailLargeURL = $objectImage['Attachment']['DetailXLargeURL'];
                 if(isset($objectImage['Attachment']['ModificationDate'])){
                   $ModificationDate_API =  $objectImage['Attachment']['ModificationDate'];
                 }else{
                   $ModificationDate_API = $objectImage['Attachment']['CreationDate'];
                 }
                 $AttachmentId = $objectImage['Attachment']['AttachmentId'];
                 $AttachmentIds_API[] =  $AttachmentId;

                 $is_image_modified = $this->is_image_modified($ModificationDate_API, $AttachmentId);

                 //if image is not modified then skip
                 if(!$is_image_modified){
                   continue;
                 }


                 $AttachmentKeywords = $objectImage['Attachment']['AttachmentKeywords'];

                 $keywords = [];
                 foreach($AttachmentKeywords as $AttachmentKeyword){
                   $keywords[] = $AttachmentKeyword['AttachmentKeywordString'];
                 }
                 $keywords_serialized = json_encode($keywords);

                 $fileName1 = $objectImage['Attachment']['FileURL'];
                 $attachment_description = $objectImage['Attachment']['Description'];


                 $curlObject = curl_init($objectImageDetailLargeURL);
                 curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
                 $objectImageData = curl_exec($curlObject);
                 curl_close($curlObject);

                 $objectImageThumbSizeURL = $objectImage['Attachment']['ThumbSizeURL'];
                 $curlObject1 = curl_init($objectImageThumbSizeURL);
                 curl_setopt($curlObject1, CURLOPT_RETURNTRANSFER, true);
                 $thumbImageData = curl_exec($curlObject1);
                 curl_close($curlObject1);

                 if ($objectImageData !== false)
                 {
                     $id1 = $image['ObjectId'];
                     $mainId = $image['MainImageAttachmentId'] ?? null;
                       // Update $object_table.
                       $updateObjectQuery = $database->update($object_table)
                       ->fields([
                         'object_image_attachment' => $objectImageData,
                         'thumb_size_URL' => $thumbImageData,
                         'FileURL' => $fileName1,
                       ])
                       ->condition('ObjectId', $id1)
                       ->execute();

                       $query = $database->select($thumbImage_table)
                       ->fields($thumbImage_table, ['AttachmentId'])
                       ->condition('AttachmentId', $AttachmentId)
                       ->range(0, 1); // Optimize by limiting the result to 1 row.
                       $result = $query->execute();

                       if (!empty($result->fetch())) {
                         // AttachmentId exists, update the record.
                         $updateThumbImageQuery = $database->update($thumbImage_table)
                           ->fields([
                             'ThumbURL' => $fileName1,
                             'ObjectId' => $id1,
                             'thumb_size_URL' => $thumbImageData,
                             'object_image_attachment' => $objectImageData,
                             'keywords' => $keywords_serialized,
                             'MainImageAttachmentId' => $mainId,
                             'ModificationDate' => $ModificationDate_API,
                             'attachment_description' => $attachment_description
                           ])
                           ->condition('AttachmentId', $AttachmentId)
                           ->execute();
                       } else {

                         // Insert into $thumbImage_table.
                         $insertThumbImageQuery = $database->insert($thumbImage_table)
                         ->fields([
                           'ThumbURL' => $fileName1,
                           'ObjectId' => $id1,
                           'thumb_size_URL' => $thumbImageData,
                           'object_image_attachment' => $objectImageData,
                           'AttachmentId' => $AttachmentId,
                           'keywords' => $keywords_serialized,
                           'MainImageAttachmentId' => $mainId,
                           'ModificationDate' => $ModificationDate_API,
                           'attachment_description' => $attachment_description
                         ])
                         ->execute();
                       }

                 }
             }

         }
     }

  }

  public function processImportToDirectory($Detaildata, $current_batch_number){

    $connection = Database::getConnection();
    $object_table = $connection->prefixTables('CSObjects');
    $thumbImage_table = $connection->prefixTables('ThumbImages');

    if ($current_batch_number == 0) {
      //This will run only once at the first batch
      \Drupal::logger(channel: 'custom_api_integration')->debug('Started importing to Directory');

      $trunc_thumb_table = $connection->truncate($thumbImage_table);
      $trunc_thumb_table->execute();

      // Update data in the CSObjects table.
      $update_object_data = $connection->update($object_table)
      ->fields([
        'main_image_attachment' => null,
        'object_image_attachment' => null,
        'thumb_size_URL' => null,
      ]);
      $update_object_data->execute();

      $allImagesDirectory = PublicStream::basePath() . '/All Images' . '/Objects';
      if(file_exists( $allImagesDirectory ))
      {
        $this->deleteDirectory($allImagesDirectory);
      }
    }


    $filesystem = \Drupal::service('file_system');

    //Create object's mainImageAttachment Directory
    // $objectDirectory = __DIR__ . '/All Images' . '/Objects' . '/MainImageAttachments';
    $objectDirectory = ( PublicStream::basePath().'/All Images/Objects/MainImageAttachments');

    if (!file_exists($objectDirectory))
    {
        mkdir($objectDirectory, 0755, true);
    }

    //Create object's objectImageAttachment Directory
    // $objectDirectory1 = __DIR__ . '/All Images' . '/Objects' . '/ObjectImageAttachments'; //WP
    $objectDirectory1 =  PublicStream::basePath().'/All Images/Objects/ObjectImageAttachments';

    if (!file_exists($objectDirectory1))
    {
        mkdir($objectDirectory1, 0755, true);
    }

    //Create object's ThumbSizeURL Directory
    // $objectDirectory2 = __DIR__ . '/All Images' . '/Objects' . '/ThumbSizeImages'; //WP
    $objectDirectory2 =  PublicStream::basePath().'/All Images/Objects/ThumbSizeImages';

    if (!file_exists($objectDirectory2))
    {
        mkdir($objectDirectory2, 0755, true);
    }

    //Save Object Images
    foreach($Detaildata['value'] as $image)
    {
      $object_main_image_path = '';
      $mainImageDescription = $image['MainImageAttachment']['Description'] ?? null;

      if (
        isset($image['MainImageAttachment']) &&
        isset($image['MainImageAttachment']['FileName'])
      ) {
          $object_main_image_path = $objectDirectory . '/' . $image['MainImageAttachment']['FileName'];
      }
        $mainImageURL = $image['MainImageAttachment']['DetailLargeURL'] ?? null;
        $objectImages = $image['ObjectImageAttachments'] ?? null;
        $curlMain1 = curl_init($mainImageURL);
        curl_setopt($curlMain1, CURLOPT_RETURNTRANSFER, true);

        $mainImageData = curl_exec($curlMain1);

        curl_close($curlMain1);
        if($object_main_image_path &&  $mainImageData){
          file_put_contents($object_main_image_path, $mainImageData);
        }
        //for local hosts only
        // $object_main_image_path = preg_replace("#.*?\\\\wp-content#", "/wp-content", $object_main_image_path);
        $attachment = $image['MainImageAttachment'];
        $id = $image['ObjectId'];
        if($mainImageData!==false)
        {
            // $add_directory_path = $wpdb->prepare("UPDATE $object_table SET main_image_path = %s WHERE ObjectId = %d", $object_main_image_path, $id);
            // $wpdb->query($add_directory_path);
            $update_object_path = $connection->update($object_table)
              ->fields([
                'main_image_path' => $object_main_image_path,
                'main_image_attachment_description' => $mainImageDescription
              ])
              ->condition('ObjectId', $id);
            $update_object_path->execute();

        }

        if (!empty($objectImages))
        {
            foreach ($objectImages as $objectImage)
            {

                if(isset($objectImage['Attachment']['ModificationDate'])){
                  $ModificationDate_API =  $objectImage['Attachment']['ModificationDate'];
                }else{
                  $ModificationDate_API = $objectImage['Attachment']['CreationDate'];
                }

                $AttachmentId = $objectImage['Attachment']['AttachmentId'];
                $AttachmentKeywords = $objectImage['Attachment']['AttachmentKeywords'];

                $keywords = [];
                foreach($AttachmentKeywords as $AttachmentKeyword){
                  $keywords[] = $AttachmentKeyword['AttachmentKeywordString'];
                }
                $keywords_serialized = json_encode($keywords);
                $attachment_description = $objectImage['Attachment']['Description'];


                $object_image_path = $objectDirectory1 . '/' . $objectImage['Attachment']['FileName'];
                $thumb_image_path = $objectDirectory2 . '/' . $objectImage['Attachment']['FileName'];
                $objectImageDetailLargeURL = $objectImage['Attachment']['DetailXLargeURL'];
                $objectImageThumbSizeURL = $objectImage['Attachment']['ThumbSizeURL'];
                $curlObject = curl_init($objectImageDetailLargeURL);
                curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
                $objectImageData = curl_exec($curlObject);
                curl_close($curlObject);
                file_put_contents($object_image_path, $objectImageData);
                //for local hosts only
                // $object_image_path = preg_replace("#.*?\\\\wp-content#", "/wp-content", $object_image_path);
                $curlObject1 = curl_init($objectImageThumbSizeURL);
                curl_setopt($curlObject1, CURLOPT_RETURNTRANSFER, true);
                $thumbImageData = curl_exec($curlObject1);
                curl_close($curlObject1);
                file_put_contents($thumb_image_path, $thumbImageData);
                //for local hosts only
                // $thumb_image_path = preg_replace("#.*?\\\\wp-content#", "/wp-content", $thumb_image_path);
                $id1 = $image['ObjectId'];
                $fileName1 = $objectImage['Attachment']['FileURL'];
                if($objectImageData!==false)
                {
                    $mainId = $image['MainImageAttachmentId'] ?? null;
                    $update_object_data = $connection->update($object_table)
                    ->fields([
                      'object_image_path' => $object_image_path,
                      'thumb_size_URL_path' => $thumb_image_path,
                      'FileURL' => $fileName1,
                    ])
                    ->condition('ObjectId', $id1);
                    $update_object_data->execute();

                    // Insert data into the ThumbImages table.
                    $insert_thumb_image_data = $connection->insert($thumbImage_table)
                    ->fields([
                      'ThumbURL' => $fileName1,
                      'ObjectId' => $id1,
                      'thumb_size_URL_path' => $thumb_image_path,
                      'object_image_path' => $object_image_path,
                      'AttachmentId' => $AttachmentId,
                      'keywords' => $keywords_serialized,
                      'ModificationDate' => $ModificationDate_API,
                      'attachment_description' => $attachment_description
                    ]);
                    $insert_thumb_image_data->execute();

                    // Update MainImageAttachmentId in the ThumbImages table.
                    $update_main_id_data = $connection->update($thumbImage_table)
                    ->fields([
                      'MainImageAttachmentId' => $mainId,
                    ])
                    ->condition('ObjectId', $id1);
                    $update_main_id_data->execute();
                }
            }
        }
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

  /*
  * Returns true if the object is modified
  */
  public function is_object_modified($objectId, $ApiModificationDate){
    $database = Database::getConnection();
    $table_name = 'CSObjects';
    // Check if the object is modified
    $record_exists = $database->select($table_name)
    ->fields($table_name)
    ->condition('ObjectId', $objectId)
    ->condition('ModificationDate', $ApiModificationDate)
    ->execute()
    ->fetchAssoc();

    if($record_exists){
      //object is not modified
      return false;
    }else{
      //object is modified
      return true;
    }

  }


  /***
   * Returns TRUE if the image is modified and exists
   *  */
  function is_image_modified($ModificationDate_API, $AttachmentId){
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

  public function is_exist_object_image_AttachmentId_DB($objectId, $AttachmentId){
    $database = Database::getConnection();
    $table_name = 'ThumbImages';
    // Check if the object is modified
    $record_exists = $database->select($table_name)
    ->fields($table_name)
    ->condition('ObjectId', $objectId)
    ->condition('AttachmentId', $AttachmentId)
    ->execute()
    ->fetchAssoc();

    if($record_exists){
      return true;
    }else{
      return false;
    }

  }

  /*
  * Returns true if main_image_attachment_exists
  */
  public function is_exist_main_image_attachment($objectId, $MainImageAttachmentId){

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

    if($record_exists){
      return true;
    }else{
      return false;
    }

  }

  /**
   * Remove the unrequired rows from the 'ThumbImages' table which does not exist in the API response
   */
  public function remove_unrequired_AttachmentIds_from_Database($AttachmentIds_API){
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
}
