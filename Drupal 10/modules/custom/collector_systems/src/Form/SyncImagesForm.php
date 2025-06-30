<?php
namespace Drupal\collector_systems\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SyncImagesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cs_sync_images_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $request = \Drupal::request();
    $get_save_option = $request->query->get('save_option', default: '');
    $selected_option = '';
    if($get_save_option == 'database'){
      $selected_option = 'save_to_database';
    }elseif($get_save_option == 'directory'){
      $selected_option = 'save_to_directory';
    }

    // Wrapper container for horizontal layout
    $form['horizontal_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['form-horizontal-wrapper']],
    ];

    $form['horizontal_wrapper']['save_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Save Images To'),
      '#options' => [
        'save_to_directory' => $this->t('Directory'),
        'save_to_database' => $this->t('Database'),
      ],
      '#default_value' => $selected_option,
      '#required' => TRUE,

    ];
    // Add a button to trigger the batch.
    $form['horizontal_wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sync Images'),
      '#attributes' => ['class' => ['btn-dark']],
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
  public function getDataForProcessingObjectImages() {
    $collector_systemsts_get_api_data = \Drupal::service('collector_systems.collector_systemsts_get_api_data');
    $total_objects_count = $collector_systemsts_get_api_data->getApiTotalObjectsCount();

    $chunk_size = 5; // number of objects to process in a batch
    $total_chunks = ceil($total_objects_count / $chunk_size);
    $data = [];

    for ($i = 0; $i < $total_chunks; $i++) {
      // Calculate the offset for each API call
      $offset = $i * $chunk_size;
      $data[] = [
        'total_chunks' => $total_chunks,
        'chunk_size' => $chunk_size,
        'offset' => $offset,
        'current_batch_number' => $i,
        'image_type' => 'object_images'
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
        if($item['image_type'] == 'object_images'){
            $operations[] = [[$this, 'processItem_ObjectImages'], [$item, $selected_option]];
        } else if($item['image_type'] == 'other_images'){
            $operations[] = [[$this, 'processItem_OtherImages'], [$item, $selected_option]];
        }
    }

    // Define the batch.
    $batch = [
      'title' => t('Importing Objects Images...'),
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
  public function processItem_ObjectImages($item, $selected_option): void {

    $collector_systemsts_get_api_data = \Drupal::service('collector_systems.collector_systemsts_get_api_data');
    $chunk_size = $item['chunk_size'];
    $offset = $item['offset'];
    $current_batch_number = $item['current_batch_number'];
    $getApiObjectImagesData = $collector_systemsts_get_api_data->getApiObjectImagesData($chunk_size, $offset);

    // Decode JSON data.
    $Detaildata = json_decode($getApiObjectImagesData, TRUE);

    if ($selected_option == 'save_to_database') {
      $this->processImportToDatabaseObjectImages($Detaildata, $current_batch_number);
    }elseif($selected_option == 'save_to_directory') {
      $this->processImportToDirectoryObjectImages($Detaildata, $current_batch_number);
    }
  }

  /**
   * Batch finished callback.
   */
  public function batchFinished($success, $results, $operations) {
    // store the sync started info in databse.
    collector_systems_update_CSSynced_table('images', 'manual', false,  true);

    if ($success) {
      \Drupal::messenger()->addMessage(t('Collector Systems - Images sync successfully completed.'));

      $redirect_url = Url::fromRoute('custom_api_integration.dashboard')->toString();
      return new RedirectResponse($redirect_url);
    }
    else {
      \Drupal::messenger()->addError(t('Collector Systems - An error occurred during images sync.'));
    }
    
  }

   /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // store the sync started info in databse.
    collector_systems_update_CSSynced_table('images', 'manual', true,  false);

    $selected_option = $form_state->getValue('save_option');
    $data_object_images = $this->getDataForProcessingObjectImages();
    $data_other_images = $this->getDataForProcessingOtherImages();
    $data = array_merge($data_object_images, $data_other_images);
    $this->startBatchProcess($data, $selected_option);
  }




  public function processImportToDatabaseObjectImages($Detaildata, $current_batch_number){
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
        'slide_show_URL_path' => NULL
      ])
      ->execute();

      $remove_object_directory_data = $database->update($object_table)
      ->fields([
        'main_image_path' => NULL,
        'thumb_size_URL_path' => NULL,
        'slide_show_URL_path' => NULL,
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

                  $objectImageSlideShowURL = $objectImage['Attachment']['SlideShowURL'];
                  $curlObject1 = curl_init($objectImageSlideShowURL);
                  curl_setopt($curlObject1, CURLOPT_RETURNTRANSFER, true);
                  $slideShowImageData = curl_exec($curlObject1);
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
                         'slide_show_attachment' => $slideShowImageData,
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
                             'slide_show_attachment' => $slideShowImageData,
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
                           'slide_show_attachment' => $slideShowImageData,
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

  public function processImportToDirectoryObjectImages($Detaildata, $current_batch_number){

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
          'slide_show_attachment' => null,
          'thumb_size_URL' => null,
        ]);
        $update_object_data->execute();
  
        $allImagesDirectory = PublicStream::basePath() . '/All Images' . '/Objects';
        if(file_exists( $allImagesDirectory ))
        {
          $this->deleteDirectory($allImagesDirectory);
        }

        //Create object's mainImageAttachment Directory
        // $objectDirectory = __DIR__ . '/All Images' . '/Objects' . '/MainImageAttachments';
        $objectDirectory = ( PublicStream::basePath().'/All Images/Objects/MainImageAttachments');
    
        if (!file_exists($objectDirectory))
        {
            mkdir($objectDirectory, 0755, true);
        }
    
        //Create object's SlideShowImages Directory
        $SlideShowImagesDirectory = ( PublicStream::basePath().'/All Images/Objects/SlideShowImages');
    
        if (!file_exists($SlideShowImagesDirectory))
        {
            mkdir($SlideShowImagesDirectory, 0755, true);
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
      }
  
      //Save Object Images
      foreach($Detaildata['value'] as $image)
      {
        $ObjectImageAttachments = $image['ObjectImageAttachments'] ?? null;
        $this->processImportObjectAttachmentsToDirectory($image, $ObjectImageAttachments);
      }
  
  }

  public function processImportObjectAttachmentsToDirectory($image, $ObjectImageAttachments){
    $objectImages = $ObjectImageAttachments;
    if (!empty($objectImages))
    {
      foreach ($objectImages as $objectImage)
      {
        $this->processImportSingleObjectAttachmentToDirectory($image, $objectImage);
      }
    }
  }

  public function processImportSingleObjectAttachmentToDirectory($image, $objectImage){
    $database = Database::getConnection();
    $connection = Database::getConnection();
    $objectDirectory1 =  PublicStream::basePath().'/All Images/Objects/ObjectImageAttachments';
    $objectDirectory2 =  PublicStream::basePath().'/All Images/Objects/ThumbSizeImages';
    $SlideShowImagesDirectory = ( PublicStream::basePath().'/All Images/Objects/SlideShowImages');
    $object_table = $database->prefixTables('CSObjects');
    $thumbImage_table = $database->prefixTables('ThumbImages');

    if(isset($objectImage['Attachment']['ModificationDate'])){
      $ModificationDate_API =  $objectImage['Attachment']['ModificationDate'];
    }else{
      $ModificationDate_API = $objectImage['Attachment']['CreationDate'];
    }

    $MainImageAttachmentId = $image['MainImageAttachmentId'] ?? null;

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
    $slideshow_image_path = $SlideShowImagesDirectory . '/' . $objectImage['Attachment']['FileName'];
    $objectImageDetailLargeURL = $objectImage['Attachment']['DetailXLargeURL'];
    $slideShowImageURL = $objectImage['Attachment']['SlideShowURL'];
    $objectImageThumbSizeURL = $objectImage['Attachment']['ThumbSizeURL'];
    
    // $curlObject = curl_init($objectImageDetailLargeURL);
    // curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
    // $objectImageData = curl_exec($curlObject);
    // curl_close($curlObject);
    // file_put_contents($object_image_path, $objectImageData);

    // //for local hosts only
    // // $object_image_path = preg_replace("#.*?\\\\wp-content#", "/wp-content", $object_image_path);
    // $curlObject1 = curl_init($objectImageThumbSizeURL);
    // curl_setopt($curlObject1, CURLOPT_RETURNTRANSFER, true);
    // $thumbImageData = curl_exec($curlObject1);
    // curl_close($curlObject1);
    // file_put_contents($thumb_image_path, $thumbImageData);
    // //for local hosts only
    // // $thumb_image_path = preg_replace("#.*?\\\\wp-content#", "/wp-content", $thumb_image_path);

    // //save slideshow image to directory
    // if(!empty($slideShowImageURL)){
    //   $curlObject1 = curl_init($slideShowImageURL);
    //   curl_setopt($curlObject1, CURLOPT_RETURNTRANSFER, true);
    //   $slideShowImageData = curl_exec($curlObject1);
    //   curl_close($curlObject1);
    //   file_put_contents($slideshow_image_path, $slideShowImageData);
    // }

    // Prepare all URLs
    $curl_map = [
      'object' => [
        'url' => $objectImageDetailLargeURL,
        'path' => $object_image_path,
      ],
      'thumb' => [
        'url' => $objectImageThumbSizeURL,
        'path' => $thumb_image_path,
      ],
    ];

    if (!empty($slideShowImageURL)) {
      $curl_map['slideshow'] = [
        'url' => $slideShowImageURL,
        'path' => $slideshow_image_path,
      ];
    }

    $object_main_image_path = '';
    if($AttachmentId == $MainImageAttachmentId){
      $objectDirectory = ( PublicStream::basePath().'/All Images/Objects/MainImageAttachments');
      if (
        isset($image['MainImageAttachment']) &&
        isset($image['MainImageAttachment']['FileName'])
      ) {
          $object_main_image_path = $objectDirectory . '/' . $image['MainImageAttachment']['FileName'];
      }
      $mainImageURL = $image['MainImageAttachment']['DetailLargeURL'] ?? null;

      $curl_map['main_image_path'] = [
        'url' => $mainImageURL,
        'path' => $object_main_image_path,
      ];
    }

    // Initialize multi cURL
    $multiHandle = curl_multi_init();
    $curlHandles = [];

    foreach ($curl_map as $key => $info) {
      $ch = curl_init($info['url']);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_multi_add_handle($multiHandle, $ch);
      $curlHandles[$key] = $ch;
    }

    // Execute all requests in parallel
    $running = null;
    do {
      curl_multi_exec($multiHandle, $running);
      curl_multi_select($multiHandle);
    } while ($running > 0);

    // Save content to files
    foreach ($curlHandles as $key => $ch) {
      $data = curl_multi_getcontent($ch);
      file_put_contents($curl_map[$key]['path'], $data);
      curl_multi_remove_handle($multiHandle, $ch);
      curl_close($ch);
    }

    curl_multi_close($multiHandle);


    $id1 = $image['ObjectId'];
    $fileName1 = $objectImage['Attachment']['FileURL'];

    $mainId = $image['MainImageAttachmentId'] ?? null;

    if($AttachmentId == $MainImageAttachmentId){
      $object_id = $image['ObjectId'];
      $mainImageDescription = $image['MainImageAttachment']['Description'] ?? null;
      $update_object_data = $connection->update($object_table)
      ->fields([
        'main_image_path' => $object_main_image_path,
        'main_image_attachment_description' => $mainImageDescription,
        'object_image_path' => $object_image_path,
        'thumb_size_URL_path' => $thumb_image_path,
        'slide_show_URL_path' => $slideshow_image_path,
        'FileURL' => $fileName1,
      ])
      ->condition('ObjectId', $object_id);
      $update_object_data->execute();
    }
    
    // Insert data into the ThumbImages table.
    $insert_thumb_image_data = $connection->insert($thumbImage_table)
    ->fields([
      'ThumbURL' => $fileName1,
      'ObjectId' => $id1,
      'thumb_size_URL_path' => $thumb_image_path,
      'slide_show_URL_path' => $slideshow_image_path,
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

  /**
   * Get the data for batch processing (Other Images).
   */
  public function getDataForProcessingOtherImages() {


    $chunk_size = 5; // number of objects to process in a batch

    $collector_systemsts_get_api_data = \Drupal::service('collector_systems.collector_systemsts_get_api_data');
    $import_types = [
      'ArtistsImages',
      'CollectionsImages',
      'GroupsImages',
      'ExhibitionsImages',
    ];
    $data = [];

    foreach($import_types as $import_type){
      if($import_type == 'ArtistsImages'){
        $total_count = $collector_systemsts_get_api_data->getTotalArtistsCount();
      }elseif($import_type == 'CollectionsImages'){
        $total_count = $collector_systemsts_get_api_data->getTotalCollectionsCount();
      }elseif($import_type == 'GroupsImages'){
        $total_count = $collector_systemsts_get_api_data->getTotalGroupsCount();
      }elseif($import_type == 'ExhibitionsImages'){
        $total_count = $collector_systemsts_get_api_data->getTotalExhibitionsCount();
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
            'import_type' => $import_type,
            'image_type' => 'other_images'
          ];
        }

      }



    }

    return $data;
  }

  /**
   * Batch operation to process each item.
   */
  public function processItem_OtherImages($item, $selected_option) {

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
      $this->processImportToDatabaseOtherImages($Detaildata, $current_batch_number, $import_type);
    }elseif($selected_option == 'save_to_directory') {
      $this->processImportToDirectoryOtherImages($Detaildata, $current_batch_number, $import_type);
    }
  }

  public function processImportToDatabaseOtherImages($Detaildata, $current_batch_number, $import_type){
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

  public function processImportToDirectoryOtherImages($Detaildata, $current_batch_number, $import_type){

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
}
