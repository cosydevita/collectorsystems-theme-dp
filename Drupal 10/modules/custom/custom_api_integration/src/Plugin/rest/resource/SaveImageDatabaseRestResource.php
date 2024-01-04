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
 *   id = "cs_save_image_database_ajax",
 *   label = @Translation("Collector Systems Save Image to Database"),
 *   uri_paths = {
 *     "canonical" = "/v1/cs-save-image-database-ajax",
 *     "create" = "/v1/cs-save-image-database-ajax"
 *   }
 * )
 */
class SaveImageDatabaseRestResource extends ResourceBase {


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


     //Fetch Artist Images
    //  global $wpdb , $subAcntId , $subsId , $subsKey;
     $url = csconstants::Public_API_URL.$subAcntId . '/Artists?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=ArtistPhotoAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)';
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

     $ArtistPhoto = curl_exec($curl);
     curl_close($curl);

     $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
     if($httpcode == 403)
     {
        //  get_template_part( 403 );
         exit();
     }
     $ArtistPhoto = json_decode($ArtistPhoto, TRUE);

     //Fetch Collection Images
    //  global $wpdb , $subAcntId , $subsId , $subsKey;
     $url = csconstants::Public_API_URL.$subAcntId . '/Collections?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=CollectionImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL),';
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

     $CollectionPhoto = curl_exec($curl);
     curl_close($curl);

     $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
     if($httpcode == 403)
     {
        //  get_template_part( 403 );
         exit();
     }
     $CollectionPhoto = json_decode($CollectionPhoto, TRUE);

     //Fetch Groups Images
    //  global $wpdb , $subAcntId , $subsId , $subsKey;
     $url = csconstants::Public_API_URL. $subAcntId . '/Groups?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=GroupImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL),';
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

     $GroupImages = curl_exec($curl);
     curl_close($curl);

     $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
     if($httpcode == 403)
     {
        //  get_template_part( 403 );
         exit();
     }
     $GroupImages = json_decode($GroupImages, TRUE);

     //Fetch Exhibitions Images
    //  global $wpdb , $subAcntId , $subsId , $subsKey;
     $url = csconstants::Public_API_URL.$subAcntId . '/Exhibitions?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=ExhibitionImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL),';
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

     $ExhibitionPhoto = curl_exec($curl);
     curl_close($curl);

     $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
     if($httpcode == 403)
     {
        //  get_template_part( 403 );
         exit();
     }
     $ExhibitionPhoto = json_decode($ExhibitionPhoto, TRUE);

     //Fetch Object Images
    //  global $wpdb , $subAcntId , $subsId , $subsKey;
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
        //  get_template_part( 403 );
         exit();
     }
     $table_name = $database->prefixTables('CSObjects');

     $Detaildata = json_decode($Detaildata, TRUE);

     //Fetch GroupObjects
     global $wpdb , $subAcntId , $subsId , $subsKey;
     $url=csconstants::Public_API_URL.$subAcntId.'/GroupObjects?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=group,object($expand=MainImageattachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)),';
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

     $GroupObjects = curl_exec($curl);
     curl_close($curl);

     $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
     if($httpcode == 403)
     {
        //  get_template_part( 403 );
         exit();
     }
     $GroupObjects = json_decode($GroupObjects, TRUE);

     //Fetch ExhibitionObjects
     global $wpdb , $subAcntId , $subsId , $subsKey;
     $url=csconstants::Public_API_URL.$subAcntId.'/ExhibitionObjects?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=exhibition,object($expand=Mainimageattachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)),';
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

     $ExhibitionObjects = curl_exec($curl);
     curl_close($curl);

     $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
     if($httpcode == 403)
     {
        //  get_template_part( 403 );
         exit();
     }
     $ExhibitionObjects = json_decode($ExhibitionObjects, TRUE);

      // Delete Artist's Images
      $artist_table = 'Artists';
      $database->update($artist_table)
        ->fields(['ArtistPhotoAttachment' => null])
        ->execute();

      // Delete Group's Images
      $group_table = 'Groups';
      $database->update($group_table)
        ->fields(['GroupImageAttachment' => null])
        ->execute();

      // Delete Exhibition's Images
      $exhibition_table = 'Exhibitions';
      $database->update($exhibition_table)
        ->fields(['ExhibitionImageAttachment' => null])
        ->execute();

      // Delete Collection's Images
      $collection_table = 'Collections';
      $database->update($collection_table)
        ->fields(['CollectionImageAttachment' => null])
        ->execute();


      // Delete Object's MainImages
      $object_table = 'CSObjects';
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
    $groupObj_table = 'GroupObjects';
    $database->update($groupObj_table)
      ->fields(['ObjectImage' => null])
      ->execute();

    // Delete GroupObject's Paths
    $database->update($groupObj_table)
      ->fields(['ObjectImagePath' => null])
      ->execute();

    // Delete ExhibitionObject's Images
    $exhibitionObj_table = 'ExhibitionObjects';
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

     //Save Artist Images
     foreach($ArtistPhoto['value'] as $photo)
     {
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

     //Save Collection Images
     foreach($CollectionPhoto['value'] as $photo)
     {
         $mainImageURL = $photo['CollectionImageAttachment']['DetailLargeURL'];
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

     //Save Groups Images
     foreach ($GroupImages['value'] as $photo)
     {
         $mainImageURL = $photo['GroupImageAttachment']['DetailLargeURL'];
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

     //Save Exhibitions Images
     foreach ($ExhibitionPhoto['value'] as $photo)
     {
         $mainImageURL = $photo['ExhibitionImageAttachment']['DetailLargeURL'];
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

     //Start GroupObjects
     foreach($GroupObjects['value'] as $obj)
     {
         $object_image = $obj['Object']['MainImageAttachment']['DetailLargeURL'];
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
     }//End GroupObjects

     //Start GroupObjects
     foreach($ExhibitionObjects['value'] as $obj)
     {
         $object_image = $obj['Object']['MainImageAttachment']['DetailLargeURL'];
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
     }//End GroupObjects



    // $this->save_image_directory();
    $response = [
      'messgae' => 'All the images are saved in the database successfully!'
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
