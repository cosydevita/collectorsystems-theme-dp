<?php

namespace Drupal\custom_api_integration\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Database\Connection;
use Drupal\custom_api_integration\Csconstants;
use Drupal\Core\Database\Database;
use Drupal\Core\StreamWrapper\PublicStream;


/**
 * Provides a resource to get view modes by entity and bundle.
 * @RestResource(
 *   id = "cs_save_object_image_database_ajax",
 *   label = @Translation("Collector Systems Save Object Image to Database"),
 *   uri_paths = {
 *     "canonical" = "/v1/cs-save-object-image-database-ajax",
 *     "create" = "/v1/cs-save-object-image-database-ajax"
 *   }
 * )
 */
class SaveObjectImageDatabaseRestResource extends ResourceBase {


   /**
   * Responds to POST requests.
   *
   * Creates a user account.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {

    $config = \Drupal::config('custom_api_integration.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');

    $database = Database::getConnection();

    //Fetch Object Images
    $url = csconstants::Public_API_URL.$subAcntId.'/Objects?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=MainImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL,SlideShowURL),ObjectImageAttachments($expand=Attachment),';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
    "Accept: application/json",
    "Ocp-Apim-Subscription-Key:$subsKey ",
    "Cache-Control:no-cache",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $Detaildata = curl_exec($curl);
    curl_close($curl);

    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if($httpcode == 403)
    {
        // get_template_part( 403 );
        exit();
    }

    // Define the table names.
    $table_name = $database->prefixTables('CSObjects');
    $object_table = $database->prefixTables('CSObjects');
    $thumbImage_table = $database->prefixTables('ThumbImages');

    // Decode JSON data.
    $Detaildata = json_decode($Detaildata, TRUE);

    // // Truncate the ThumbImages table.
    // $truncate_query = $database->truncate($thumbImage_table);
    // $truncate_query->execute();

    // $allImagesDirectory = __DIR__ . '/All Images' . '/Objects';
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

    //to store ObjectImageAttachments AttachmentIds
    $AttachmentIds_API = [];

    //Save Objects Images
    foreach ($Detaildata['value'] as $image)
    {
        $mainImageURL = $image['MainImageAttachment']['DetailLargeURL'] ?? null;

        $mainID = $image['MainImageAttachmentId'] ?? null;
        $objectId = $image['ObjectId'];
        $ApiModificationDate =  $image['ModificationDate'];

        $filtered_keywords = get_filtered_keywords();
        if($filtered_keywords){
          $mainAttachmentId= isset($image['MainImageAttachment']) ? $image['MainImageAttachment']['AttachmentId'] : 0;
          $objectImages = getObjectImageAttachmentsByObjectId($objectId, $mainAttachmentId);
        }
        else{
          $objectImages = $image['ObjectImageAttachments'] ?? null;
        }

        // $check_is_object_modified = $this->check_is_object_modified($objectId, $ApiModificationDate);
        // if(!$check_is_object_modified){
        //   // If object is not modified then skip to the next iteration without updating it
        //   continue;
        // }

        $is_exist_object_MainImageAttachmentId = $this->is_exist_object_MainImageAttachmentId($objectId, $mainID);
        //if there is already the data for the same MainImageAttachmentId then do not insert the new data
        if(!$is_exist_object_MainImageAttachmentId){
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
                'MainImageAttachmentId' => $mainID

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
                $fileName1 = $objectImage['Attachment']['FileURL'];
                $AttachmentId = $objectImage['Attachment']['AttachmentId'];
                $AttachmentIds_API[] =  $AttachmentId;
                $is_exist_object_image_AttachmentId_DB = $this->is_exist_object_image_AttachmentId_DB($objectId, $AttachmentId);

                //if there is already the data for the same attachmentId then do not insert the new data
                if($is_exist_object_image_AttachmentId_DB){
                  continue;
                }

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
                    // $insertObjectImage = $wpdb->prepare("UPDATE $object_table SET object_image_attachment= %s , thumb_size_URL = %s , FileURL = %s WHERE ObjectId = %d" , $objectImageData , $thumbImageData , $fileName1 , $id1);
                    // $resultObject = $wpdb->query($insertObjectImage);
                    // $insertObjectImage1 = $wpdb->prepare("INSERT INTO $thumbImage_table(ThumbURL,ObjectId, thumb_size_URL, object_image_attachment) VALUES(%s,%d, %s, %s)",$fileName1,$id1 , $thumbImageData, $objectImageData);
                    // $wpdb->query($insertObjectImage1);
                    // $insertmainId1 = $wpdb->prepare("UPDATE $thumbImage_table SET MainImageAttachmentId = %d WHERE ObjectId = %d", $mainId, $id1);
                    // $wpdb->query($insertmainId1);


                      // Update $object_table.
                      $updateObjectQuery = $database->update($object_table)
                      ->fields([
                        'object_image_attachment' => $objectImageData,
                        'thumb_size_URL' => $thumbImageData,
                        'FileURL' => $fileName1,
                      ])
                      ->condition('ObjectId', $id1)
                      ->execute();

                      // Insert into $thumbImage_table.
                      $insertThumbImageQuery = $database->insert($thumbImage_table)
                      ->fields([
                        'ThumbURL' => $fileName1,
                        'ObjectId' => $id1,
                        'thumb_size_URL' => $thumbImageData,
                        'object_image_attachment' => $objectImageData,
                        'AttachmentId' => $AttachmentId
                      ])
                      ->execute();

                      // Update $thumbImage_table.
                      $updateMainIdQuery = $database->update($thumbImage_table)
                      ->fields(['MainImageAttachmentId' => $mainId])
                      ->condition('ObjectId', $id1)
                      ->execute();
                }
            }

        }
    }

    //remove all the attachment ids from the database which does not exist in API
    if($AttachmentIds_API){
      $this->remove_unrequired_AttachmentIds_from_Database($AttachmentIds_API);
    }



    // $this->save_image_directory();
    $response = [
      'messgae' => 'All Object Images are stored in the database successfully!'
    ];

    return new ResourceResponse($response);
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

  public function check_is_object_modified($objectId, $ApiModificationDate){
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
      return true;
    }else{
      return false;
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

  public function is_exist_object_MainImageAttachmentId($objectId, $MainImageAttachmentId){

    $database = Database::getConnection();
    $table_name = 'CSObjects';
    // Check if the object is modified
    $record_exists = $database->select($table_name)
    ->fields($table_name)
    ->condition('ObjectId', $objectId)
    ->condition('MainImageAttachmentId', $MainImageAttachmentId)
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
