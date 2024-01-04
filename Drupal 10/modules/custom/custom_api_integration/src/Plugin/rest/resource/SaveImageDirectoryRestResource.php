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
 *   id = "cs_save_image_directory_ajax",
 *   label = @Translation("Collector Systems Save  Image to directory"),
 *   uri_paths = {
 *     "canonical" = "/v1/cs-save-image-directory-ajax",
 *     "create" = "/v1/cs-save-image-directory-ajax"
 *   }
 * )
 */
class SaveImageDirectoryRestResource extends ResourceBase {


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
    $url = csconstants::Public_API_URL.$subAcntId . '/Artists?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=ArtistPhotoAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL),';
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
        // get_template_part( 403 );
        exit();
    }
    $ArtistPhoto = json_decode($ArtistPhoto, TRUE);

    //Fetch Collection Images
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
        // get_template_part( 403 );
        exit();
    }
    $CollectionPhoto = json_decode($CollectionPhoto, TRUE);

    //Fetch Groups Images
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
        // get_template_part( 403 );
        exit();
    }
    $GroupImages = json_decode($GroupImages, TRUE);

    //Fetch Exhibitions Images
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
        // get_template_part( 403 );
        exit();
    }
    $ExhibitionPhoto = json_decode($ExhibitionPhoto, TRUE);

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
    $Detaildata = json_decode($Detaildata, TRUE);

    //Fetch GroupObjects
    $url=csconstants::Public_API_URL.$subAcntId.'/GroupObjects?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=group,object($expand=MainImageattachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)),';        $curl = curl_init($url);
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
        // get_template_part( 403 );
        exit();
    }
    $GroupObjects = json_decode($GroupObjects, TRUE);

    //Fetch ExhibitionObjects
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
        // get_template_part( 403 );
        exit();
    }
    $ExhibitionObjects = json_decode($ExhibitionObjects, TRUE);

    //Create Artist Directory
    // $artistDirectory = __DIR__ . '/All Images' . '/Artists';
    $artistDirectory = ( PublicStream::basePath().'/All Images/Artists');

    if (!file_exists($artistDirectory))
    {
        mkdir($artistDirectory, 0755, true);
    }

    //Create Group Directory
    // $groupDirectory = __DIR__ . '/All Images' . '/Groups';
    $groupDirectory = ( PublicStream::basePath().'/All Images/Groups');

    if (!file_exists($groupDirectory))
    {
        mkdir($groupDirectory, 0755, true);
    }

    //Create Collection Directory
    // $collectionDirectory = __DIR__ . '/All Images' . '/Collections';
    $collectionDirectory = ( PublicStream::basePath().'/All Images/Collections');

    if (!file_exists($collectionDirectory))
    {
        mkdir($collectionDirectory, 0755, true);
    }

    //Create Exhibition Directory
    // $exhibitionDirectory = __DIR__ . '/All Images' . '/Exhibitions';
    $exhibitionDirectory = ( PublicStream::basePath().'/All Images/Exhibitions');

    if (!file_exists($exhibitionDirectory))
    {
        mkdir($exhibitionDirectory, 0755, true);
    }

    //Create GroupObjects Directory
    // $groupObjDirectory = __DIR__ . '/All Images' . '/GroupObjects';
    $groupObjDirectory = ( PublicStream::basePath().'/All Images/GroupObjects');

    if (!file_exists($groupObjDirectory))
    {
        mkdir($groupObjDirectory, 0755, true);
    }

    //Create ExhibitionObjects Directory
    // $exhibitionObjDirectory = __DIR__ . '/All Images' . '/ExhibitionObjects';
    $exhibitionObjDirectory = ( PublicStream::basePath().'/All Images/ExhibitionObjects');

    if (!file_exists($exhibitionObjDirectory))
    {
        mkdir($exhibitionObjDirectory, 0755, true);
    }




    // Place null in the ImagePath for Artists.
    $artist_table = 'Artists'; // Assuming the table name is 'artists'.
    $database->update($artist_table)
      ->fields(['ImagePath' => null, 'ArtistPhotoAttachment' => null])
      ->execute();

    // Place null in the ImagePath and CollectionImageAttachment for Collections.
    $collection_table = 'Collections'; // Assuming the table name is 'collections'.
    $database->update($collection_table)
      ->fields(['ImagePath' => null, 'CollectionImageAttachment' => null])
      ->execute();

    // Place null in the ImagePath and GroupImageAttachment for Groups.
    $group_table = 'Groups'; // Assuming the table name is 'groups'.
    $database->update($group_table)
      ->fields(['ImagePath' => null, 'GroupImageAttachment' => null])
      ->execute();

    // Place null in the ImagePath and ExhibitionImageAttachment for Exhibitions.
    $exhibition_table = 'Exhibitions'; // Assuming the table name is 'exhibitions'.
    $database->update($exhibition_table)
      ->fields(['ImagePath' => null, 'ExhibitionImageAttachment' => null])
      ->execute();

    // Place null in various fields for CSObjects.
    $object_table = 'CSObjects'; // Assuming the table name is 'csobjects'.
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
    $groupObj_table = 'GroupObjects'; // Assuming the table name is 'groupobjects'.
    $database->update($groupObj_table)
      ->fields(['ObjectImage' => null, 'ObjectImagePath' => null])
      ->execute();

    // Place null in the ObjectImage and ObjectImagePath for ExhibitionObjects.
    $exhibitionObj_table = 'ExhibitionObjects'; // Assuming the table name is 'exhibitionobjects'.
    $database->update($exhibitionObj_table)
      ->fields(['ObjectImage' => null, 'ObjectImagePath' => null])
      ->execute();


    //Save Artist Images in the Artists Directory
    foreach($ArtistPhoto['value'] as $photo)
    {
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

    //Save Collection Images in the Collections Directory
    foreach($CollectionPhoto['value'] as $photo)
    {
        $collection_image_path = $collectionDirectory . '/' . $photo['CollectionImageAttachment']['FileName'];
        $mainImageURL = $photo['CollectionImageAttachment']['DetailLargeURL'];

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

    //Save Group Images in the Groups Directory
    foreach($GroupImages['value'] as $photo)
    {
        $group_image_path = $groupDirectory . '/' . $photo['GroupImageAttachment']['FileName'];
        $mainImageURL = $photo['GroupImageAttachment']['DetailLargeURL'];

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

    //Save Exhibition Images in the Exhibitions Directory
    foreach($ExhibitionPhoto['value'] as $photo)
    {
        $exhibition_image_path = $exhibitionDirectory . '/' . $photo['ExhibitionImageAttachment']['FileName'];
        $mainImageURL = $photo['ExhibitionImageAttachment']['DetailLargeURL'];

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

    //Start GroupObjects
    foreach($GroupObjects['value'] as $obj)
    {
        $groupObj_image_path = $groupObjDirectory . '/' . $obj['Object']['MainImageAttachment']['FileName'];
        $object_image = $obj['Object']['MainImageAttachment']['DetailLargeURL'];
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
    }//End GroupObjects

    //Start ExhibitionObjects
    foreach($ExhibitionObjects['value'] as $obj)
    {
        $exhibitionObj_image_path = $exhibitionObjDirectory . '/' . $obj['Object']['MainImageAttachment']['FileName'];
        $object_image = $obj['Object']['MainImageAttachment']['DetailLargeURL'];
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
    }//End ExhibitionObjects




    $response = [
      'messgae' => 'All the images are saved in their respective directories successfully!'
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
