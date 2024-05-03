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
 *   id = "cs_save_object_image_directory_ajax",
 *   label = @Translation("Collector Systems Save Object Image to directory"),
 *   uri_paths = {
 *     "canonical" = "/v1/cs-save-object-image-directory-ajax",
 *     "create" = "/v1/cs-save-object-image-directory-ajax"
 *   }
 * )
 */
class SaveObjectImageDirectoryRestResource extends ResourceBase {


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
        //expanded to include the attachmentkeywords
        $url =csconstants::Public_API_URL.$subAcntId.'/Objects?$expand=MainImageAttachment($select=AttachmentId,Description,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL,SlideShowURL),ObjectImageAttachments($expand=Attachment($select=AttachmentId,SubscriptionId,FileName,Description,ContentType,CreationDate,FileURL,ThumbSizeURL,MidSizeURL,DetailURL,DetailLargeURL,DetailXLargeURL,iphoneURL,SlideShowURL;$expand=AttachmentKeywords($select=AttachmentKeywordString))),&$select=InventoryNumber,Title,InventoryNumber,ObjectId,MainImageAttachmentId,ModificationDate,CreationDate&$filter=SubscriptionId%20eq%20'.$subsId.'%20And%20Deleted%20eq%20false';

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

        $Detaildata = json_decode($Detaildata, TRUE);
        // $object_table = $wpdb->prefix . "CSObjects";
        // $thumbImage_table = $wpdb->prefix . "ThumbImages";
        // $trunc_thumb_table = $wpdb->prepare("TRUNCATE TABLE $thumbImage_table");
        // $query_thumb = $wpdb->query($trunc_thumb_table);

        // $remove_object_database_data = $wpdb->prepare("UPDATE $object_table SET main_image_attachment = %s, object_image_attachment = %s, thumb_size_URL = %s",null, null,null);
        // $wpdb->query($remove_object_database_data);
        $connection = Database::getConnection();
        $object_table = $connection->prefixTables('CSObjects');
        $thumbImage_table = $connection->prefixTables('ThumbImages');
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
                        // $add_directory_path = $wpdb->prepare("UPDATE $object_table SET object_image_path = %s , thumb_size_URL_path = %s , FileURL = %s WHERE ObjectId = %d", $object_image_path, $thumb_image_path , $fileName1 ,$id1);
                        // $wpdb->query($add_directory_path);
                        // $insertObjectImage1 = $wpdb->prepare("INSERT INTO $thumbImage_table(ThumbURL,ObjectId, thumb_size_URL_path, object_image_path) VALUES(%s,%d, %s, %s)",$fileName1,$id1 , $thumb_image_path, $object_image_path);
                        // $wpdb->query($insertObjectImage1);
                        // $insertmainId1 = $wpdb->prepare("UPDATE $thumbImage_table SET MainImageAttachmentId = %d WHERE ObjectId = %d", $mainId, $id1);
                        // $wpdb->query($insertmainId1);
                        // Update data in the CSObjects table.
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

    $response = [
      'messgae' => 'All Object Images are stored in the directory successfully!'
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

}
