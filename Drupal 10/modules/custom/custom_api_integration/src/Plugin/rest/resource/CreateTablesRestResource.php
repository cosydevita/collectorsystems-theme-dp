<?php

namespace Drupal\custom_api_integration\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Database\Connection;
use Drupal\custom_api_integration\Csconstants;
use Drupal\Core\Database\Database;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Datetime\DrupalDateTime;



/**
 * Provides a resource to get view modes by entity and bundle.
 * @RestResource(
 *   id = "cs_create_tables_rest_resource",
 *   label = @Translation("Collector Systems Create Tables"),
 *   uri_paths = {
 *     "canonical" = "/v1/cs-create-tables",
 *     "create" = "/v1/cs-create-tables"
 *   }
 * )
 */
class CreateTablesRestResource extends ResourceBase {


   /**
   * Responds to POST requests.
   *
   * Creates a user account.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    if(isset($data['btn_action'])){
      $btn_action = $data['btn_action'];
    }
    else{
      $btn_action = '';
    }

    $this->custom_api_integration_sync_api_data($btn_action);
    $response = [
      'messgae' => 'All tables created successfully test.'
    ];

    return new ResourceResponse($response);
  }

   /**
   * Sync the api data to the database
   */
  function custom_api_integration_sync_api_data($btn_action) {

    //drop tables
    $this->custom_api_integration_drop_tables($btn_action);

    if($btn_action == 'reset-and-create-dataset'){

      //drop images directory
      $allImagesDirectory = PublicStream::basePath() . '/All Images';
      if(file_exists( $allImagesDirectory ))
      {
        $this->deleteDirectory($allImagesDirectory);
      }

    }

    // create tables
    $this->custom_api_integration_create_tables($btn_action);

    $this->sync_api_data($btn_action);
    $this->update_CSSynced_table();


  }

  function sync_api_data($btn_action){
    $this->sync_api_data_Artists($btn_action);
    $this->sync_api_data_Objects($btn_action);
    $this->sync_api_data_Collections($btn_action);
    $this->sync_api_data_Exhibitions($btn_action);
    $this->sync_api_data_Groups($btn_action);
    $this->sync_api_data_ExhibitionObjects();
    $this->sync_api_data_GroupObjects();

  }

  function update_CSSynced_table(){
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

  function sync_api_data_GroupObjects(){
    $config = \Drupal::config('custom_api_integration.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');

    $database = Database::getConnection();
    $table_name6 = 'GroupObjects';


    //Fetch GroupObjects
    $url=csconstants::Public_API_URL.$subAcntId.'/GroupObjects?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=group,object($expand=MainImageattachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL))';
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
        exit();
    }
    $GroupObjects = json_decode($GroupObjects, TRUE);

    //Start GroupObjects
    foreach($GroupObjects['value'] as $obj)
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

    }//End GroupObjects

  }

  function sync_api_data_ExhibitionObjects(){

    $config = \Drupal::config('custom_api_integration.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');

    $database = Database::getConnection();
    $table_name5 = 'ExhibitionObjects';


     //Fetch ExhibitionObjects
     $url=csconstants::Public_API_URL.$subAcntId.'/ExhibitionObjects?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=exhibition,object($expand=Mainimageattachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL))';
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
         exit();
     }
     $ExhibitionObjects = json_decode($ExhibitionObjects, TRUE);

    //Start ExhibitionObjects
    foreach($ExhibitionObjects['value'] as $obj)
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

    }//End ExhibitionObjects

  }

  function sync_api_data_Groups($btn_action){
    $config = \Drupal::config('custom_api_integration.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');

    $database = Database::getConnection();
    $table_name3 = 'Groups';

    //Fetching Group's API Data
    $wordforsearch = "Groups";
    $url = csconstants::Public_API_URL . $subAcntId . '/' . $wordforsearch;

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

      $data3 = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          exit();
      }
      $data3 = json_decode($data3, TRUE); //End Group's API Data


      //start group images
      $url = csconstants::Public_API_URL. $subAcntId . '/Groups?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=GroupImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)';
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
            exit();
        }
      $GroupImages = json_decode($GroupImages, TRUE);
      //end group images

      //Start Groups
      $groupIds_API = [];
      foreach ($GroupImages['value'] as $group)
      {
          $groupId = $group['GroupId'];

          $groupIds_API[] = $groupId;

          $groupDescription = $group['GroupDescription'];
          $groupMemo = NULL;
          if(isset($group['GroupMemo']) && $group['GroupMemo'] !== NULL)
          {
              $groupMemo = $group['GroupMemo'];
          }

          if(isset($group['ModificationDate']) && $group['ModificationDate'] !== NULL)
          {
              $ModificationDate = $group['ModificationDate'];
          }elseif(isset($group['CreationDate']) && $group['CreationDate'] !== NULL){
            $ModificationDate = $group['CreationDate'];
          }


          if($groupId !== 0)
          {
              // $sql3 = $wpdb->prepare("INSERT INTO $table_name3 (GroupId , GroupDescription , GroupMemo) VALUES (%d , %s , %s)", $groupId ,  $groupDescription , $groupMemo);
              // $wpdb->query($sql3);
              // Define the data to be inserted.
              $data = array(
                'GroupId' => $groupId,
                'GroupDescription' => $groupDescription,
                'GroupMemo' => $groupMemo,
                'ModificationDate' => $ModificationDate,

              );

              if($btn_action == 'update-dataset'){
                // Check if the record exists.
                 $record_exists = $database->select($table_name3)
                 ->fields($table_name3)
                 ->condition('GroupId', $groupId)
                 ->execute()
                 ->fetchAssoc();

                if($record_exists){
                  // Update the existing record if the ModificationDate has changed
                  $database->update($table_name3)
                    ->fields($data)
                    ->condition('GroupId', $groupId)
                    ->condition('ModificationDate', $ModificationDate, '<>')
                    ->execute();
                }else{
                    // Handle if record doesn't exist
                    // Insert data into the table.
                  $database->insert($table_name3)
                    ->fields($data)
                    ->execute();
                }

              }else{
                // Insert the data into the table.
                \Drupal::database()->insert($table_name3)
                ->fields($data)
                ->execute();

              }


          }

      }//End Groups

    if($groupIds_API){
        $this->remove_unrequired_Groups_from_Database($groupIds_API);
    }

  }

  function sync_api_data_Exhibitions($btn_action){
    $config = \Drupal::config('custom_api_integration.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');

    $database = Database::getConnection();
    $table_name4 = 'Exhibitions';

     //Fetch Exhibition Images
     $url = csconstants::Public_API_URL.$subAcntId . '/Exhibitions?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=ExhibitionImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)';
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
         exit();
     }
     $ExhibitionPhoto = json_decode($ExhibitionPhoto, TRUE);



      //Fetching Exhibition's API Data
    	$wordforsearch = "Exhibitions";
    	$url = csconstants::Public_API_URL . $subAcntId . '/' . $wordforsearch;

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

        $data4 = curl_exec($curl);
        curl_close($curl);

        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if($httpcode == 403)
        {
            exit();
        }
        $data4 = json_decode($data4, TRUE); //End Exhibition's API Data
        $exhibitionIds_API = [];
        //Start Exhibitions
        foreach ($ExhibitionPhoto['value'] as $exhibition)
        {
            $exhibitionId = $exhibition['ExhibitionId'];
            $exhibitionIds_API[] = $exhibitionId;
            $exhibitionSubject = $exhibition['ExhibitionSubject'];
            $exhibitionLocation = NULL;
            if(isset($exhibition['ExhibitionLocation']) && $exhibition['ExhibitionLocation'] !== NULL)
            {
                $exhibitionLocation = $exhibition['ExhibitionLocation'];
            }
            $exhibitionDate = $exhibition['ExhibitionDate'];
            $exhibitionMemo = NULL;
            if(isset($exhibition['ExhibitionMemo']) && $exhibition['ExhibitionMemo'] !== NULL)
            {
                $exhibitionMemo = $exhibition['ExhibitionMemo'];
            }

            if(isset($exhibition['ModificationDate']) && $exhibition['ModificationDate'] !== NULL)
            {
                $ModificationDate = $exhibition['ModificationDate'];
            }elseif(isset($exhibition['CreationDate']) && $exhibition['CreationDate'] !== NULL){
              $ModificationDate = $exhibition['CreationDate'];
            }


            if($exhibitionId !== null)
            {
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

                if($btn_action == 'update-dataset'){
                  // Check if the record exists.
                   $record_exists = $database->select($table_name4)
                   ->fields($table_name4)
                   ->condition('ExhibitionId', $exhibitionId)
                   ->execute()
                   ->fetchAssoc();

                  if($record_exists){
                    // Update the existing record if the ModificationDate has changed
                    $database->update($table_name4)
                      ->fields($data)
                      ->condition('ExhibitionId', $exhibitionId)
                      ->condition('ModificationDate', $ModificationDate, '<>')
                      ->execute();
                  }else{
                      // Handle if record doesn't exist
                      // Insert data into the table.
                    $database->insert($table_name4)
                      ->fields($data)
                      ->execute();
                  }

                }else{
                  $database->insert($table_name4)
                  ->fields($data)
                  ->execute();
                }

            }
        } //End Exhibitions
         //remove unrequired data from the database which does not exist in API
        if($exhibitionIds_API){
          $this->remove_unrequired_Exhibitions_from_Database($exhibitionIds_API);
        }

  }
  function sync_api_data_Collections($btn_action){
    // Get the api config settings
    $config = \Drupal::config('custom_api_integration.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');

    $database = Database::getConnection();
    $table_name2 = 'Collections';


    //Fetch Collection Images
    $url = csconstants::Public_API_URL.$subAcntId . '/Collections?$expand=CollectionImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL),&$filter=SubscriptionId%20eq%20' . $subsId;
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
        exit();
    }
    $CollectionPhoto = json_decode($CollectionPhoto, TRUE);

    //end fetch colection Images


    //Fetching Collection's API Data
    $wordforsearch = "Collections";
    $url = csconstants::Public_API_URL . $subAcntId . '/' . $wordforsearch;

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

      $data2 = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          exit();
      }
      $data2 = json_decode($data2, TRUE); //End Collection's API Data

      $collectionIds_API = [];

      //Start Collections
      foreach ($CollectionPhoto['value'] as $collection)
      {
          $collectionId = $collection['CollectionId'];
          $collectionIds_API[] = $collectionId;
          $collectionName = $collection['CollectionName'];
          $collectionFullName = $collection['FullCollectionName'];

          if(isset($collection['ModificationDate']) && $collection['ModificationDate'] !== NULL)
          {
              $ModificationDate = $collection['ModificationDate'];
          }elseif(isset($collection['CreationDate']) && $collection['CreationDate'] !== NULL){
            $ModificationDate = $collection['CreationDate'];
          }

          if ($collectionId !== 0 && $collectionName !== null && $collectionFullName !== null)
          {
              // $sql2 = $wpdb->prepare("INSERT INTO $table_name2 (CollectionId , CollectionName , FullCollectionName) VALUES (%d , %s , %s)", $collectionId ,  $collectionName , $collectionFullName);
              // $wpdb->query($sql2);
              // Define the data to be inserted.
              $data = [
                'CollectionId' => $collectionId,
                'CollectionName' => $collectionName,
                'FullCollectionName' => $collectionFullName,
                'ModificationDate' => $ModificationDate,

              ];



              if($btn_action == 'update-dataset'){
                // Check if the record exists.
                 $record_exists = $database->select($table_name2)
                 ->fields($table_name2)
                 ->condition('CollectionId', $collectionId)
                 ->execute()
                 ->fetchAssoc();

                if($record_exists){
                  // Update the existing record if the ModificationDate has changed
                  $database->update($table_name2)
                    ->fields($data)
                    ->condition('CollectionId', $collectionId)
                    ->condition('ModificationDate', $ModificationDate, '<>')
                    ->execute();
                }else{
                    // Handle if record doesn't exist
                    // Insert data into the table.
                  $database->insert($table_name2)
                    ->fields($data)
                    ->execute();
                }

              }else{
                // Insert data into the table using the database API.
                $database->insert($table_name2)
                ->fields($data)
                ->execute();

              }


          }

      }//End Collections

    //remove unrequired data from the database which does not exist in API
    if($collectionIds_API){
    $this->remove_unrequired_Collections_from_Database($collectionIds_API);
    }

  }
  function sync_api_data_Objects($btn_action){

    $field_names = $field_names_array = $this->get_field_names(); //temp test

     // Get the api config settings
     $config = \Drupal::config('custom_api_integration.settings');
     $subsKey = $config->get('subscription_key');
     $subAcntId = $config->get('account_guid');
     $subsId = $config->get('subscription_id');


     $database = Database::getConnection();
     $table_name = 'CSObjects';

      // Fetching Object's API Data
    	$wordforsearch = "Objects";
      if ($field_names != null && $field_names != "") {
        $customized_fields = $this->getCommaSeperatedUniqueFieldsForSearch($field_names);
        $baseurl = csconstants::Public_API_URL.$subAcntId . '/Objects?$expand=MainImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL),Address($select=AddressId,AddressName,LatitudeDegrees,LongitudeDegrees),';
        $qSearch= '';
        $apiCallFor = '';

        $url = $this->getDynamicUrlForEndpoint($field_names_array,$baseurl,$qSearch,$apiCallFor);

      }
      else{
    	$url = csconstants::Public_API_URL . $subAcntId . '/' . $wordforsearch . '?$filter=SubscriptionId%20eq%20' . $subsId;
      }

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

      $data1 = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          exit();
      }
      $data1 = json_decode($data1, TRUE); //End Object's API Data


        $objectIds_API = [];
        foreach ($data1['value'] as $value)
        {
          $combinedObjectValues = [];
          if (!empty($value['Address']['LatitudeDegrees'])) {
            $combinedObjectValues['LatitudeDegrees'] = $value['Address']['LatitudeDegrees'];
          }
          if (!empty($value['Address']['LongitudeDegrees'])) {
            $combinedObjectValues['LongitudeDegrees'] = $value['Address']['LongitudeDegrees'];
          }
          if (!empty($value['Address']['AddressName'])) {
            $combinedObjectValues['AddressName'] = $value['Address']['AddressName'];
          }


          foreach ($field_names as $field_name)
          {
              switch($field_name)
              {
                case csconstants::InventoryNumber:
                  if (!empty($value['InventoryNumber'])) {
                      $combinedObjectValues[$field_name] = $value['InventoryNumber'];
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
                  }
                  break;
                case csconstants::ArtistFirst:
                    if (!empty($value['ArtistFirst'])) {
                        $combinedObjectValues[$field_name] = $value['ArtistFirst'];
                    }
                    break;

                case csconstants::ArtistLast:
                    if (!empty($value['ArtistLast'])) {
                        $combinedObjectValues[$field_name] = $value['ArtistLast'];
                    }
                    break;

                case csconstants::ArtistYears:
                    if (!empty($value['ArtistYears'])) {
                        $combinedObjectValues[$field_name] = $value['ArtistYears'];
                    }
                    break;

                case csconstants::ArtistLocale:
                    if (!empty($value['ArtistLocale'])) {
                        $combinedObjectValues[$field_name] = $value['ArtistLocale'];
                    }
                    break;

                case csconstants::ArtistBio:
                    if (!empty($value['ArtistBio'])) {
                        $combinedObjectValues[$field_name] = $value['ArtistBio'];
                    }
                    break;

                case csconstants::CollectionName:
                    if (!empty($value['Collection'])) {
                        $combinedObjectValues[$field_name] = $value['Collection']['CollectionName'];
                    }
                    break;

                case csconstants::FullCollectionName:
                    if (!empty($value['Collection'])) {
                        $combinedObjectValues[$field_name] = $value['Collection']['FullCollectionName'];
                    }
                    break;

                case csconstants::NomenclatureObjectName:
                    if (!empty($value['NomenclatureObjectName'])) {
                        $combinedObjectValues[$field_name] = $value['NomenclatureObjectName'];
                    }
                    break;

                case csconstants::ObjectStatus:
                    if (!empty($value['ObjectStatus'])) {
                        $combinedObjectValues[$field_name] = $value['ObjectStatus'];
                    }
                    break;

                case csconstants::ObjectType:
                    if (!empty($value['ObjectType']['ObjectTypeName'])) {
                        $combinedObjectValues[$field_name] = $value['ObjectType']['ObjectTypeName'];
                    }
                    break;

                case csconstants::LocationName:
                    if (!empty($value['Location']['LocationName'])) {
                        $combinedObjectValues[$field_name] = $value['Location']['LocationName'];
                    }
                    break;

                case csconstants::FullLocationName:
                    if (!empty($value['Location']['FullLocationName'])) {
                        $combinedObjectValues[$field_name] = $value['Location']['FullLocationName'];
                    }
                    break;

                case csconstants::PermanentLocationName:
                    if (!empty($value['PermanentLocation']['LocationName'])) {
                        $combinedObjectValues[$field_name] = $value['PermanentLocation']['LocationName'];
                    }
                    break;

                case csconstants::PermanentFullLocationName:
                    if (!empty($value['PermanentLocation']['FullLocationName'])) {
                        $combinedObjectValues[$field_name] = $value['PermanentLocation']['FullLocationName'];
                    }
                    break;

                case csconstants::CollectionName:
                    if (!empty($value['Collection']['CollectionName'])) {
                        $combinedObjectValues[$field_name] = $value['Collection']['CollectionName'];
                    }
                    break;

                case csconstants::FullCollectionName:
                    if (!empty($value['Collection']['FullCollectionName'])) {
                        $combinedObjectValues[$field_name] = $value['Collection']['FullCollectionName'];
                    }
                    break;

                case csconstants::CreditLine:
                    if (!empty($value['CreditLine'])) {
                        $combinedObjectValues[$field_name] = $value['CreditLine'];
                    }
                    break;

                case csconstants::ArtistName:
                    if (!empty($value['Artist']) && isset($value['Artist'])) {
                        if (!empty($value['Artist']['ArtistName'])) {
                            $combinedObjectValues[$field_name] = $value['Artist']['ArtistName'];
                        }
                    }
                    break;

                case csconstants::AdditionalArtists:
                    if (!empty($value['AdditionalArtists'])) {
                        $combinedObjectValues[$field_name] = $this->implodeChildArrayProperty($value['AdditionalArtists'], "Artist", "ArtistId", "ArtistName");
                    }
                    break;

                case csconstants::Maker:
                    if (!empty($value['Maker'])) {
                        $combinedObjectValues[$field_name] = $value['Maker'];
                    }
                    break;

                case csconstants::Title:
                    if (!empty($value['Title'])) {
                        $combinedObjectValues[$field_name] = $value['Title'];
                    }
                    break;

                case csconstants::AlternateTitle:
                    if (!empty($value['AlternateTitle'])) {
                        $combinedObjectValues[$field_name] = $value['AlternateTitle'];
                    }
                    break;

                case csconstants::ObjectDate:
                    if (!empty($value['ObjectDate'])) {
                        $combinedObjectValues[$field_name] = $value['ObjectDate'];
                    }
                    break;

                case csconstants::Medium:
                    if (!empty($value['Medium'])) {
                        $combinedObjectValues[$field_name] = $value['Medium'];
                    }
                    break;

                case csconstants::LocationStatus:
                    if (!empty($value['LocationStatus']['Term'])) {
                        $combinedObjectValues[$field_name] = $value['LocationStatus']['Term'];
                    }
                    break;

                case csconstants::InventoryDate:
                    if (!empty($value['InventoryDate'])) {
                        $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['InventoryDate']));
                    }
                    break;

                case csconstants::InventoryContactName:
                    if (!empty($value['InventoryContact']['ContactName'])) {
                        $combinedObjectValues[$field_name] = $value['InventoryContact']['ContactName'];
                    }
                    break;

                case csconstants::Form:
                    if (!empty($value['Form'])) {
                        $combinedObjectValues[$field_name] = $value['Form'];
                    }
                    break;

                case csconstants::Subject:
                    if (!empty($value['Subject'])) {
                        $combinedObjectValues[$field_name] = $value['Subject'];
                    }
                    break;

                case csconstants::CategoryStyle:
                    if (!empty($value['CategoryStyle'])) {
                        $combinedObjectValues[$field_name] = $value['CategoryStyle'];
                    }
                    break;

                case csconstants::CountryOrigin:
                    if (!empty($value['CountryOrigin'])) {
                        $combinedObjectValues[$field_name] = $value['CountryOrigin'];
                    }
                    break;

                case csconstants::Edition:
                    if (!empty($value['Edition'])) {
                        $combinedObjectValues[$field_name] = $value['Edition'];
                    }
                    break;

                case csconstants::SuitePortfolio:
                    if (!empty($value['SuitePortfolio'])) {
                        $combinedObjectValues[$field_name] = $value['SuitePortfolio'];
                    }
                    break;
                case csconstants::CatalogRaisonne:
                  if (!empty($value['CatalogRaisonne'])) {
                      $combinedObjectValues[$field_name] = $value['CatalogRaisonne'];
                  }
                  break;

                case csconstants::RFIDTagNumber:
                    if (!empty($value['RFIDTagNumber'])) {
                        $combinedObjectValues[$field_name] = $value['RFIDTagNumber'];
                    }
                    break;

                case csconstants::Term:
                    if (!empty($value['Term'])) {
                        $combinedObjectValues[$field_name] = $value['Term'];
                    }
                    break;

                case csconstants::CatalogNumber:
                    if (!empty($value['CatalogNumber'])) {
                        $combinedObjectValues[$field_name] = $value['CatalogNumber'];
                    }
                    break;

                case csconstants::OtherNumbers:
                    if (!empty($value['OtherNumbers'])) {
                        $combinedObjectValues[$field_name] = $value['OtherNumbers'];
                    }
                    break;

                case csconstants::ItemCount:
                    if (!empty($value['ItemCount'])) {
                        $combinedObjectValues[$field_name] = $value['ItemCount'];
                    }
                    break;

                case csconstants::CatalogerContactName:
                    if (!empty($value['CatalogerContact']['ContactName'])) {
                        $combinedObjectValues[$field_name] = $value['CatalogerContact']['ContactName'];
                    }
                    break;

                case csconstants::CatalogDate:
                    if (!empty($value['CatalogDate'])) {
                        $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['CatalogDate']));
                    }
                    break;

                case csconstants::CollectionTitle:
                    if (!empty($value['CollectionTitle'])) {
                        $combinedObjectValues[$field_name] = $value['CollectionTitle'];
                    }
                    break;

                case csconstants::CollectionNumber:
                    if (!empty($value['CollectionNumber'])) {
                        $combinedObjectValues[$field_name] = $value['CollectionNumber'];
                    }
                    break;

                case csconstants::Material:
                    if (!empty($value['Material'])) {
                        $combinedObjectValues[$field_name] = $value['Material'];
                    }
                    break;

                case csconstants::Technique:
                    if (!empty($value['Technique']['Term'])) {
                        $combinedObjectValues[$field_name] = $value['Technique']['Term'];
                    }
                    break;

                case csconstants::Color:
                    if (!empty($value['Color']['Term'])) {
                        $combinedObjectValues[$field_name] = $value['Color']['Term'];
                    }
                    break;

                case csconstants::StateOfOrigin:
                    if (!empty($value['StateOfOrigin'])) {
                        $combinedObjectValues[$field_name] = $value['StateOfOrigin'];
                    }
                    break;

                case csconstants::CountyOfOrigin:
                    if (!empty($value['CountyOfOrigin'])) {
                        $combinedObjectValues[$field_name] = $value['CountyOfOrigin'];
                    }
                    break;

                case csconstants::CityOfOrigin:
                    if (!empty($value['CityOfOrigin'])) {
                        $combinedObjectValues[$field_name] = $value['CityOfOrigin'];
                    }
                    break;

                case csconstants::State:
                    if (!empty($value['State'])) {
                        $combinedObjectValues[$field_name] = $value['State'];
                    }
                    break;

                case csconstants::Duration:
                    if (!empty($value['Duration'])) {
                        $combinedObjectValues[$field_name] = $value['Duration'];
                    }
                    break;

                case csconstants::RevisedNomenclature:
                    if (!empty($value['RevisedNomenclature'])) {
                        $combinedObjectValues[$field_name] = $value['RevisedNomenclature'];
                    }
                    break;

                case csconstants::PreviousCatalogNumber:
                    if (!empty($value['PreviousCatalogNumber'])) {
                        $combinedObjectValues[$field_name] = $value['PreviousCatalogNumber'];
                    }
                    break;

                case csconstants::FieldSpecimenNumber:
                    if (!empty($value['FieldSpecimenNumber'])) {
                        $combinedObjectValues[$field_name] = $value['FieldSpecimenNumber'];
                    }
                    break;

                case csconstants::StatusDate:
                    if (!empty($value['StatusDate'])) {
                        $combinedObjectValues[$field_name] = $value['StatusDate'];
                    }
                    break;

                case csconstants::StorageUnit:
                    if (!empty($value['StorageUnit']['Term'])) {
                        $combinedObjectValues[$field_name] = $value['StorageUnit']['Term'];
                    }
                    break;
                    case csconstants::CollectionDate:
                      if (!empty($value['CollectionDate'])) {
                          $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['CollectionDate']));
                      }
                      break;

                  case csconstants::CollectorContactName:
                      if (!empty($value['CollectorContact']['ContactName'])) {
                          $combinedObjectValues[$field_name] = $value['CollectorContact']['ContactName'];
                      }
                      break;

                  case csconstants::CollectorPlace:
                      if (!empty($value['CollectorPlace'])) {
                          $combinedObjectValues[$field_name] = $value['CollectorPlace'];
                      }
                      break;

                  case csconstants::CatalogFolder:
                      if (!empty($value['CatalogFolder'])) {
                          $combinedObjectValues[$field_name] = $value['CatalogFolder'] == true ? 'Yes' : 'No';
                      }
                      break;

                  case csconstants::IdentifiedByContactName:
                      if (!empty($value['IdentifiedByContact']['ContactName'])) {
                          $combinedObjectValues[$field_name] = $value['IdentifiedByContact']['ContactName'];
                      }
                      break;

                  case csconstants::IdentifiedDate:
                      if (!empty($value['IdentifiedDate'])) {
                          $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['IdentifiedDate']));
                      }
                      break;

                  case csconstants::EminentFigureContactName:
                      if (!empty($value['EminentFigureContact']['ContactName'])) {
                          $combinedObjectValues[$field_name] = $value['EminentFigureContact']['ContactName'];
                      }
                      break;

                  case csconstants::EminentOrganizationContactName:
                      if (!empty($value['EminentOrganizationContact']['ContactName'])) {
                          $combinedObjectValues[$field_name] = $value['EminentOrganizationContact']['ContactName'];
                      }
                      break;

                  case csconstants::ControlledProperty:
                      if (!empty($value['ControlledProperty'])) {
                          $combinedObjectValues[$field_name] = $value['ControlledProperty'] == true ? 'Yes' : 'No';
                      }
                      break;

                  case csconstants::ArtistMakerName:
                      if (!empty($value["ArtistMaker"]['ArtistName'])) {
                          $combinedObjectValues[$field_name] = $value["ArtistMaker"]['ArtistName'];
                      }
                      break;
                  case csconstants::ArtistMakerFirst:
                    if (!empty($value["ArtistMaker"]['ArtistMakerFirst'])) {
                      $combinedObjectValues[$field_name] = $value["ArtistMaker"]['ArtistMakerFirst'];
                    }
                    break;
                  case csconstants::ArtistMakerLast:
                    if (!empty($value["ArtistMaker"]['ArtistMakerLast'])) {
                      $combinedObjectValues[$field_name] = $value["ArtistMaker"]['ArtistMakerLast'];
                    }
                    break;

                  case csconstants::TaxonomicSerialNumber:
                      if (!empty($value['TaxonomicSerialNumber'])) {
                          $combinedObjectValues[$field_name] = $value['TaxonomicSerialNumber'];
                      }
                      break;

                  case csconstants::Kingdom:
                      if (!empty($value['Kingdom'])) {
                          $combinedObjectValues[$field_name] = $value['Kingdom'];
                      }
                      break;

                  case csconstants::PhylumDivision:
                      if (!empty($value['PhylumDivision'])) {
                          $combinedObjectValues[$field_name] = $value['PhylumDivision'];
                      }
                      break;

                  case csconstants::CSClass:
                      if (!empty($value['Class'])) {
                          $combinedObjectValues[$field_name] = $value['Class'];
                      }
                      break;

                  case csconstants::Order:
                      if (!empty($value['Order'])) {
                          $combinedObjectValues[$field_name] = $value['Order'];
                      }
                      break;

                  case csconstants::Family:
                      if (!empty($value['Family'])) {
                          $combinedObjectValues[$field_name] = $value['Family'];
                      }
                      break;
                      case csconstants::SubFamily:
                        if (!empty($value['SubFamily'])) {
                            $combinedObjectValues[$field_name] = $value['SubFamily'];
                        }
                        break;

                    case csconstants::ScientificName:
                        if (!empty($value['ScientificName'])) {
                            $combinedObjectValues[$field_name] = $value['ScientificName'];
                        }
                        break;

                    case csconstants::CommonName:
                        if (!empty($value['CommonName'])) {
                            $combinedObjectValues[$field_name] = $value['CommonName'];
                        }
                        break;

                    case csconstants::Species:
                        if (!empty($value['Species'])) {
                            $combinedObjectValues[$field_name] = $value['Species'];
                        }
                        break;

                    case csconstants::SpeciesAuthorName:
                        if (!empty($value['SpeciesAuthor']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['SpeciesAuthor']['ContactName'];
                        }
                        break;

                    case csconstants::SpeciesAuthorDate:
                        if (!empty($value['SpeciesAuthorDate'])) {
                            $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['SpeciesAuthorDate']));
                        }
                        break;

                    case csconstants::Subspecies:
                        if (!empty($value['Subspecies'])) {
                            $combinedObjectValues[$field_name] = $value['Subspecies'];
                        }
                        break;

                    case csconstants::SubspeciesAuthorityContactName:
                        if (!empty($value['SubspeciesAuthorityContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['SubspeciesAuthorityContact']['ContactName'];
                        }
                        break;

                    case csconstants::SubspeciesAuthorName:
                        if (!empty($value['SubspeciesAuthor']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['SubspeciesAuthor']['ContactName'];
                        }
                        break;

                    case csconstants::SubspeciesAuthorDate:
                        if (!empty($value['SubspeciesAuthorDate'])) {
                            $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['SubspeciesAuthorDate']));
                        }
                        break;

                    case csconstants::SubspeciesYear:
                        if (!empty($value['SubspeciesYear'])) {
                            $combinedObjectValues[$field_name] = $value['SubspeciesYear'];
                        }
                        break;

                    case csconstants::SubspeciesVariety:
                        if (!empty($value['SubspeciesVariety'])) {
                            $combinedObjectValues[$field_name] = $value['SubspeciesVariety'];
                        }
                        break;

                    case csconstants::SubspeciesVarietyAuthorityContactName:
                        if (!empty($value['SubspeciesVarietyAuthorityContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['SubspeciesVarietyAuthorityContact']['ContactName'];
                        }
                        break;

                    case csconstants::SubspeciesVarietyYear:
                        if (!empty($value['SubspeciesVarietyYear'])) {
                            $combinedObjectValues[$field_name] = $value['SubspeciesVarietyYear'];
                        }
                        break;

                    case csconstants::SubspeciesForma:
                        if (!empty($value['SubspeciesForma'])) {
                            $combinedObjectValues[$field_name] = $value['SubspeciesForma'];
                        }
                        break;

                    case csconstants::SubspeciesFormaAuthorityContactName:
                        if (!empty($value['SubspeciesFormaAuthorityContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['SubspeciesFormaAuthorityContact']['ContactName'];
                        }
                        break;

                    case csconstants::SubspeciesFormaYear:
                        if (!empty($value['SubspeciesFormaYear'])) {
                            $combinedObjectValues[$field_name] = $value['SubspeciesFormaYear'];
                        }
                        break;

                    case csconstants::StudyNumber:
                        if (!empty($value['StudyNumber'])) {
                            $combinedObjectValues[$field_name] = $value['StudyNumber'];
                        }
                        break;

                    case csconstants::AlternateName:
                        if (!empty($value['AlternateName'])) {
                            $combinedObjectValues[$field_name] = $value['AlternateName'];
                        }
                        break;

                    case csconstants::CulturalID:
                        if (!empty($value['CulturalID'])) {
                            $combinedObjectValues[$field_name] = $value['CulturalID'];
                        }
                        break;

                    case csconstants::CultureOfUse:
                        if (!empty($value['CultureOfUse']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['CultureOfUse']['Term'];
                        }
                        break;

                    case csconstants::ManufactureDate:
                        if (!empty($value['ManufactureDate'])) {
                            $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['ManufactureDate']));
                        }
                        break;

                    case csconstants::UseDate:
                        if (!empty($value['UseDate'])) {
                            $combinedObjectValues[$field_name] = $value['UseDate'];
                        }
                        break;
                    case csconstants::TimePeriod:
                      if (!empty($value['TimePeriod'])) {
                          $combinedObjectValues[$field_name] = $value['TimePeriod'];
                      }
                      break;

                    case csconstants::HistoricCulturalPeriod:
                        if (!empty($value['HistoricCulturalPeriod']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['HistoricCulturalPeriod']['Term'];
                        }
                        break;

                    case csconstants::ManufacturingTechnique:
                        if (!empty($value['ManufacturingTechnique']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['ManufacturingTechnique']['Term'];
                        }
                        break;

                    case csconstants::Material:
                        if (!empty($value['Material'])) {
                            $combinedObjectValues[$field_name] = $value['Material'];
                        }
                        break;

                    case csconstants::BroadClassOfMaterial:
                        if (!empty($value['BroadClassOfMaterial']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['BroadClassOfMaterial']['Term'];
                        }
                        break;

                    case csconstants::SpecificClassOfMaterial:
                        if (!empty($value['SpecificClassOfMaterial']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['SpecificClassOfMaterial']['Term'];
                        }
                        break;

                    case csconstants::Quantity:
                        if (!empty($value['Quantity'])) {
                            $combinedObjectValues[$field_name] = $value['Quantity'];
                        }
                        break;

                    case csconstants::PlaceOfManufactureCountry:
                        if (!empty($value['PlaceOfManufactureCountry'])) {
                            $combinedObjectValues[$field_name] = $value['PlaceOfManufactureCountry'];
                        }
                        break;

                    case csconstants::PlaceOfManufactureState:
                        if (!empty($value['PlaceOfManufactureState'])) {
                            $combinedObjectValues[$field_name] = $value['PlaceOfManufactureState'];
                        }
                        break;

                    case csconstants::PlaceOfManufactureCounty:
                        if (!empty($value['PlaceOfManufactureCounty'])) {
                            $combinedObjectValues[$field_name] = $value['PlaceOfManufactureCounty'];
                        }
                        break;

                    case csconstants::PlaceOfManufactureCity:
                        if (!empty($value['PlaceOfManufactureCity'])) {
                            $combinedObjectValues[$field_name] = $value['PlaceOfManufactureCity'];
                        }
                        break;

                    case csconstants::OtherManufacturingSite:
                        if (!empty($value['OtherManufacturingSite'])) {
                            $combinedObjectValues[$field_name] = $value['OtherManufacturingSite'];
                        }
                        break;

                    case csconstants::Latitude:
                        if (!empty($value['Latitude'])) {
                            $combinedObjectValues[$field_name] = $value['Latitude'];
                        }
                        break;

                    case csconstants::Longitude:
                        if (!empty($value['Longitude'])) {
                            $combinedObjectValues[$field_name] = $value['Longitude'];
                        }
                        break;

                    case csconstants::UTMCoordinates:
                        if (!empty($value['UTMCoordinates'])) {
                            $combinedObjectValues[$field_name] = $value['UTMCoordinates'];
                        }
                        break;

                    case csconstants::TownshipRangeSection:
                        if (!empty($value['TownshipRangeSection'])) {
                            $combinedObjectValues[$field_name] = $value['TownshipRangeSection'];
                        }
                        break;

                    case csconstants::FieldSiteNumber:
                        if (!empty($value['FieldSiteNumber'])) {
                            $combinedObjectValues[$field_name] = $value['FieldSiteNumber'];
                        }
                        break;

                    case csconstants::StateSiteNumber:
                        if (!empty($value['StateSiteNumber'])) {
                            $combinedObjectValues[$field_name] = $value['StateSiteNumber'];
                        }
                        break;

                    case csconstants::SiteName:
                        if (!empty($value['SiteName'])) {
                            $combinedObjectValues[$field_name] = $value['SiteName'];
                        }
                        break;
                    case csconstants::SiteNumber:
                      if (!empty($value['SiteNumber'])) {
                          $combinedObjectValues[$field_name] = $value['SiteNumber'];
                      }
                      break;

                    case csconstants::DecorativeMotif:
                        if (!empty($value['DecorativeMotif'])) {
                            $combinedObjectValues[$field_name] = $value['DecorativeMotif'];
                        }
                        break;

                    case csconstants::DecorativeTechnique:
                        if (!empty($value['DecorativeTechnique']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['DecorativeTechnique']['Term'];
                        }
                        break;

                    case csconstants::Reproduction:
                        if (!empty($value['Reproduction'])) {
                            $combinedObjectValues[$field_name] = $value['Reproduction'];
                        }
                        break;

                    case csconstants::ObjectForm:
                        if (!empty($value['ObjectForm']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['ObjectForm']['Term'];
                        }
                        break;

                    case csconstants::ObjectPart:
                        if (!empty($value['ObjectPart']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['ObjectPart']['Term'];
                        }
                        break;

                    case csconstants::ComponentPart:
                        if (!empty($value['ComponentPart'])) {
                            $combinedObjectValues[$field_name] = $value['ComponentPart'];
                        }
                        break;

                    case csconstants::Temper:
                        if (!empty($value['Temper']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['Temper']['Term'];
                        }
                        break;

                    case csconstants::TypeName:
                        if (!empty($value['TypeName']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['TypeName']['Term'];
                        }
                        break;

                    case csconstants::SlideNumber:
                        if (!empty($value['SlideNumber'])) {
                            $combinedObjectValues[$field_name] = $value['SlideNumber'];
                        }
                        break;

                    case csconstants::BagNumber:
                        if (!empty($value['BagNumber'])) {
                            $combinedObjectValues[$field_name] = $value['BagNumber'];
                        }
                        break;

                    case csconstants::TotalBags:
                        if (!empty($value['TotalBags'])) {
                            $combinedObjectValues[$field_name] = $value['TotalBags'];
                        }
                        break;

                    case csconstants::BoxNumber:
                        if (!empty($value['BoxNumber'])) {
                            $combinedObjectValues[$field_name] = $value['BoxNumber'];
                        }
                        break;

                    case csconstants::TotalBoxes:
                        if (!empty($value['TotalBoxes'])) {
                            $combinedObjectValues[$field_name] = $value['TotalBoxes'];
                        }
                        break;

                    case csconstants::MakersMark:
                        if (!empty($value['MakersMark'])) {
                            $combinedObjectValues[$field_name] = $value['MakersMark'];
                        }
                        break;

                    case csconstants::NAGPRA:
                        if (!empty($value['NAGPRA']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['NAGPRA']['Term'];
                        }
                        break;

                    case csconstants::OldNumber:
                        if (!empty($value['OldNumber'])) {
                            $combinedObjectValues[$field_name] = $value['OldNumber'];
                        }
                        break;

                    case csconstants::AdditionalAccessionNumber:
                        if (!empty($value['AdditionalAccessionNumber'])) {
                            $combinedObjectValues[$field_name] = $value['AdditionalAccessionNumber'];
                        }
                        break;

                    case csconstants::CatalogLevel:
                        if (!empty($value['CatalogLevel']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['CatalogLevel']['Term'];
                        }
                        break;
                    case csconstants::LevelOfControl:
                      if (!empty($value['LevelOfControl'])) {
                          $combinedObjectValues[$field_name] = $value['LevelOfControl'];
                      }
                      break;

                    case csconstants::AlternateName:
                        if (!empty($value['AlternateName'])) {
                            $combinedObjectValues[$field_name] = $value['AlternateName'];
                        }
                        break;

                    case csconstants::AuthorName:
                        if (!empty($value['Author']['AuthorName'])) {
                            $combinedObjectValues[$field_name] = $value['Author']['AuthorName'];
                        }
                        break;

                    case csconstants::CreatorContactName:
                        if (!empty($value['CreatorContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['CreatorContact']['ContactName'];
                        }
                        break;

                    case csconstants::ComposerContactName:
                        if (!empty($value['ComposerContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['ComposerContact']['ContactName'];
                        }
                        break;

                    case csconstants::NarratorContactName:
                        if (!empty($value['NarratorContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['NarratorContact']['ContactName'];
                        }
                        break;

                    case csconstants::EditorContactName:
                        if (!empty($value['EditorContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['EditorContact']['ContactName'];
                        }
                        break;

                    case csconstants::PublisherContactName:
                        if (!empty($value['PublisherContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['PublisherContact']['ContactName'];
                        }
                        break;

                    case csconstants::IllustratorContactName:
                        if (!empty($value['IllustratorContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['IllustratorContact']['ContactName'];
                        }
                        break;

                    case csconstants::ContributorContactName:
                        if (!empty($value['ContributorContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['ContributorContact']['ContactName'];
                        }
                        break;

                    case csconstants::StudioContactName:
                        if (!empty($value['StudioContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['StudioContact']['ContactName'];
                        }
                        break;

                    case csconstants::DirectorContactName:
                        if (!empty($value['DirectorContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['DirectorContact']['ContactName'];
                        }
                        break;

                    case csconstants::ArtDirectorContactName:
                        if (!empty($value['ArtDirectorContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['ArtDirectorContact']['ContactName'];
                        }
                        break;

                    case csconstants::ProducerContactName:
                        if (!empty($value['ProducerContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['ProducerContact']['ContactName'];
                        }
                        break;

                    case csconstants::ProductionDesignerContactName:
                        if (!empty($value['ProductionDesignerContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['ProductionDesignerContact']['ContactName'];
                        }
                        break;

                    case csconstants::ProductionCompanyContactName:
                        if (!empty($value['ProductionCompanyContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['ProductionCompanyContact']['ContactName'];
                        }
                        break;

                    case csconstants::DistributionCompany:
                        if (!empty($value['DistributionCompany']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['DistributionCompany']['ContactName'];
                        }
                        break;
                    case csconstants::WriterContactName:
                      if (!empty($value['WriterContact']['ContactName'])) {
                          $combinedObjectValues[$field_name] = $value['WriterContact']['ContactName'];
                      }
                      break;

                    case csconstants::CinematographerContactName:
                        if (!empty($value['CinematographerContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['CinematographerContact']['ContactName'];
                        }
                        break;

                    case csconstants::PhotographyContactName:
                        if (!empty($value['PhotographyContact']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['PhotographyContact']['ContactName'];
                        }
                        break;

                    case csconstants::PublisherLocation:
                        if (!empty($value['PublisherLocation'])) {
                            $combinedObjectValues[$field_name] = $value['PublisherLocation'];
                        }
                        break;

                    case csconstants::Event:
                        if (!empty($value['Event']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['Event']['Term'];
                        }
                        break;

                    case csconstants::PeopleContent:
                        if (!empty($value['PeopleContent'])) {
                            $combinedObjectValues[$field_name] = $value['PeopleContent'];
                        }
                        break;

                    case csconstants::PlaceContent:
                        if (!empty($value['PlaceContent'])) {
                            $combinedObjectValues[$field_name] = $value['PlaceContent'];
                        }
                        break;

                    case csconstants::TownshipRangeSection:
                        if (!empty($value['TownshipRangeSection'])) {
                            $combinedObjectValues[$field_name] = $value['TownshipRangeSection'];
                        }
                        break;

                    case csconstants::ISBN:
                        if (!empty($value['ISBN'])) {
                            $combinedObjectValues[$field_name] = $value['ISBN'];
                        }
                        break;

                    case csconstants::ISSN:
                        if (!empty($value['ISSN'])) {
                            $combinedObjectValues[$field_name] = $value['ISSN'];
                        }
                        break;

                    case csconstants::CallNumber:
                        if (!empty($value['CallNumber'])) {
                            $combinedObjectValues[$field_name] = $value['CallNumber'];
                        }
                        break;

                    case csconstants::CoverType:
                        if (!empty($value['CoverType'])) {
                            $combinedObjectValues[$field_name] = $value['CoverType'];
                        }
                        break;

                    case csconstants::TypeOfBinding:
                        if (!empty($value['TypeOfBinding'])) {
                            $combinedObjectValues[$field_name] = $value['TypeOfBinding'];
                        }
                        break;

                    case csconstants::Language:
                        if (!empty($value['Language'])) {
                            $combinedObjectValues[$field_name] = $value['Language'];
                        }
                        break;

                    case csconstants::NumberOfPages:
                        if (!empty($value['NumberOfPages'])) {
                            $combinedObjectValues[$field_name] = $value['NumberOfPages'];
                        }
                        break;

                    case csconstants::NegativeNumber:
                        if (!empty($value['NegativeNumber'])) {
                            $combinedObjectValues[$field_name] = $value['NegativeNumber'];
                        }
                        break;

                    case csconstants::FilmSize:
                        if (!empty($value['FilmSize'])) {
                            $combinedObjectValues[$field_name] = $value['FilmSize'];
                        }
                        break;

                    case csconstants::Process:
                        if (!empty($value['Process'])) {
                            $combinedObjectValues[$field_name] = $value['Process'];
                        }
                        break;

                    case csconstants::ImageNumber:
                        if (!empty($value['ImageNumber'])) {
                            $combinedObjectValues[$field_name] = $value['ImageNumber'];
                        }
                        break;

                    case csconstants::ImageRights:
                        if (!empty($value['ImageRights'])) {
                            $combinedObjectValues[$field_name] = $value['ImageRights'];
                        }
                        break;

                    case csconstants::Copyrights:
                        if (!empty($value['Copyrights'])) {
                            $combinedObjectValues[$field_name] = $value['Copyrights'];
                        }
                        break;

                    case csconstants::FindingAids:
                        if (!empty($value['FindingAids'])) {
                            $combinedObjectValues[$field_name] = $value['FindingAids'];
                        }
                        break;

                    case csconstants::VolumeNumber:
                        if (!empty($value['VolumeNumber'])) {
                            $combinedObjectValues[$field_name] = $value['VolumeNumber'];
                        }
                        break;

                    case csconstants::CompletionYear:
                        if (!empty($value['CompletionYear'])) {
                            $combinedObjectValues[$field_name] = $value['CompletionYear'];
                        }
                        break;

                    case csconstants::Format:
                        if (!empty($value['Format']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['Format']['Term'];
                        }
                        break;
                    case csconstants::Genre:
                      if (!empty($value['Genre']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['Genre']['Term'];
                      }
                      break;

                    case csconstants::Subgenre:
                        if (!empty($value['Subgenre']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['Subgenre']['Term'];
                        }
                        break;

                    case csconstants::ReleaseDate:
                        if (!empty($value['ReleaseDate'])) {
                            $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['ReleaseDate']));
                        }
                        break;

                    case csconstants::ProductionDate:
                        if (!empty($value['ProductionDate'])) {
                            $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['ProductionDate']));
                        }
                        break;

                    case csconstants::Genus:
                        if (!empty($value['Genus'])) {
                            $combinedObjectValues[$field_name] = $value['Genus'];
                        }
                        break;

                    case csconstants::Stage:
                        if (!empty($value['Stage'])) {
                            $combinedObjectValues[$field_name] = $value['Stage'];
                        }
                        break;

                    case csconstants::Section:
                        if (!empty($value['Section'])) {
                            $combinedObjectValues[$field_name] = $value['Section'];
                        }
                        break;

                    case csconstants::QuarterSection:
                        if (!empty($value['QuarterSection'])) {
                            $combinedObjectValues[$field_name] = $value['QuarterSection'];
                        }
                        break;

                    case csconstants::Age:
                        if (!empty($value['Age'])) {
                            $combinedObjectValues[$field_name] = $value['Age'];
                        }
                        break;

                    case csconstants::Locality:
                        if (!empty($value['Locality'])) {
                            $combinedObjectValues[$field_name] = $value['Locality'];
                        }
                        break;

                    case csconstants::HabitatCommunity:
                        if (!empty($value['HabitatCommunity']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['HabitatCommunity']['Term'];
                        }
                        break;

                    case csconstants::TypeSpecimen:
                        if (!empty($value['TypeSpecimen']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['TypeSpecimen']['Term'];
                        }
                        break;

                    case csconstants::Sex:
                        if (!empty($value['Sex']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['Sex']['Term'];
                        }
                        break;
                    case csconstants::ExoticNative:
                      if (!empty($value['ExoticNative']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['ExoticNative']['Term'];
                      }
                      break;

                    case csconstants::TaxonomicNotes:
                        if (!empty($value['TaxonomicNotes'])) {
                            $combinedObjectValues[$field_name] = $value['TaxonomicNotes'];
                        }
                        break;

                    case csconstants::Rare:
                        if (!empty($value['Rare'])) {
                            $combinedObjectValues[$field_name] = $value['Rare'];
                        }
                        break;

                    case csconstants::ThreatenedEndangeredDate:
                        if (!empty($value['ThreatenedEndangeredDate'])) {
                            $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['ThreatenedEndangeredDate']));
                        }
                        break;

                    case csconstants::ThreatenedEndangeredSpeciesSynonym:
                        if (!empty($value['ThreatenedEndangeredSpeciesSynonym'])) {
                            $combinedObjectValues[$field_name] = $value['ThreatenedEndangeredSpeciesSynonym'] == true ? 'Yes' : 'No';
                        }
                        break;

                    case csconstants::ThreatenedEndangeredSpeciesSynonymName:
                        if (!empty($value['ThreatenedEndangeredSpeciesSynonymName'])) {
                            $combinedObjectValues[$field_name] = $value['ThreatenedEndangeredSpeciesSynonymName'];
                        }
                        break;

                    case csconstants::ThreatenedEndangeredSpeciesStatus:
                        if (!empty($value['ThreatenedEndangeredSpeciesStatus']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['ThreatenedEndangeredSpeciesStatus']['Term'];
                        }
                        break;

                    case csconstants::SubspeciesSynonym:
                        if (!empty($value['SubspeciesSynonym'])) {
                            $combinedObjectValues[$field_name] = $value['SubspeciesSynonym'];
                        }
                        break;

                    case csconstants::ContinentWorldRegion:
                        if (!empty($value['ContinentWorldRegion'])) {
                            $combinedObjectValues[$field_name] = $value['ContinentWorldRegion'];
                        }
                        break;

                    case csconstants::ReproductionMethod:
                        if (!empty($value['ReproductionMethod'])) {
                            $combinedObjectValues[$field_name] = $value['ReproductionMethod'];
                        }
                        break;

                    case csconstants::ReferenceDatum:
                        if (!empty($value['ReferenceDatum'])) {
                            $combinedObjectValues[$field_name] = $value['ReferenceDatum'];
                        }
                        break;

                    case csconstants::Aspect:
                        if (!empty($value['Aspect'])) {
                            $combinedObjectValues[$field_name] = $value['Aspect'];
                        }
                        break;

                    case csconstants::FormationPeriodSubstrate:
                        if (!empty($value['FormationPeriodSubstrate']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['FormationPeriodSubstrate']['Term'];
                        }
                        break;

                    case csconstants::SoilType:
                        if (!empty($value['SoilType'])) {
                            $combinedObjectValues[$field_name] = $value['SoilType'];
                        }
                        break;

                    case csconstants::Slope:
                        if (!empty($value['Slope'])) {
                            $combinedObjectValues[$field_name] = $value['Slope'];
                        }
                        break;

                    case csconstants::Unit:
                        if (!empty($value['Unit']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['Unit']['Term'];
                        }
                        break;
                    case csconstants::DepthInMeters:
                      if (!empty($value['DepthInMeters'])) {
                          $combinedObjectValues[$field_name] = $value['DepthInMeters'];
                      }
                      break;

                    case csconstants::ElevationInMeters:
                        if (!empty($value['ElevationInMeters'])) {
                            $combinedObjectValues[$field_name] = $value['ElevationInMeters'];
                        }
                        break;

                    case csconstants::EthnologyCulture:
                        if (!empty($value['EthnologyCulture'])) {
                            $combinedObjectValues[$field_name] = $value['EthnologyCulture'];
                        }
                        break;

                    case csconstants::Alternate1EthnologyCulture:
                        if (!empty($value['Alternate1EthnologyCulture'])) {
                            $combinedObjectValues[$field_name] = $value['Alternate1EthnologyCulture'];
                        }
                        break;

                    case csconstants::Alternate2EthnologyCulture:
                        if (!empty($value['Alternate2EthnologyCulture'])) {
                            $combinedObjectValues[$field_name] = $value['Alternate2EthnologyCulture'];
                        }
                        break;

                    case csconstants::AboriginalName:
                        if (!empty($value['AboriginalName']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['AboriginalName']['Term'];
                        }
                        break;

                    case csconstants::AdditionalArea:
                        if (!empty($value['AdditionalArea']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['AdditionalArea']['Term'];
                        }
                        break;

                    case csconstants::AdditionalGroup:
                        if (!empty($value['AdditionalGroup'])) {
                            $combinedObjectValues[$field_name] = $value['AdditionalGroup'];
                        }
                        break;

                    case csconstants::DescriptiveName:
                        if (!empty($value['DescriptiveName'])) {
                            $combinedObjectValues[$field_name] = $value['DescriptiveName'];
                        }
                        break;

                    case csconstants::PeriodSystem:
                        if (!empty($value['PeriodSystem']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['PeriodSystem']['Term'];
                        }
                        break;

                    case csconstants::EpochSeries:
                        if (!empty($value['EpochSeries']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['EpochSeries']['Term'];
                        }
                        break;

                    case csconstants::AgeStage:
                        if (!empty($value['AgeStage']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['AgeStage']['Term'];
                        }
                        break;

                    case csconstants::Composition:
                        if (!empty($value['Composition'])) {
                            $combinedObjectValues[$field_name] = $value['Composition'];
                        }
                        break;

                    case csconstants::StrunzClass:
                        if (!empty($value['StrunzClass'])) {
                            $combinedObjectValues[$field_name] = $value['StrunzClass'];
                        }
                        break;

                    case csconstants::StrunzDivision:
                        if (!empty($value['StrunzDivision'])) {
                            $combinedObjectValues[$field_name] = $value['StrunzDivision'];
                        }
                        break;

                    case csconstants::StrunzID:
                        if (!empty($value['StrunzID'])) {
                            $combinedObjectValues[$field_name] = $value['StrunzID'];
                        }
                        break;

                    case csconstants::LithologyPedotype:
                        if (!empty($value['LithologyPedotype'])) {
                            $combinedObjectValues[$field_name] = $value['LithologyPedotype'];
                        }
                        break;

                    case csconstants::Formation:
                        if (!empty($value['Formation'])) {
                            $combinedObjectValues[$field_name] = $value['Formation'];
                        }
                        break;
                    case csconstants::VerticalDatum:
                      if (!empty($value['VerticalDatum'])) {
                          $combinedObjectValues[$field_name] = $value['VerticalDatum'];
                      }
                      break;

                    case csconstants::Datum:
                        if (!empty($value['Datum'])) {
                            $combinedObjectValues[$field_name] = $value['Datum'];
                        }
                        break;

                    case csconstants::DepositionalEnvironment:
                        if (!empty($value['DepositionalEnvironment'])) {
                            $combinedObjectValues[$field_name] = $value['DepositionalEnvironment'];
                        }
                        break;

                    case csconstants::Member:
                        if (!empty($value['Member']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['Member']['Term'];
                        }
                        break;

                    case csconstants::GeoUnit:
                        if (!empty($value['GeoUnit']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['GeoUnit']['Term'];
                        }
                        break;

                    case csconstants::ThinSection:
                        $combinedObjectValues[$field_name] = $value['ThinSection'] == true ? 'Yes' : 'No';
                        break;

                    case csconstants::PatentDate:
                        if (!empty($value['PatentDate'])) {
                            $combinedObjectValues[$field_name] = $value['PatentDate'];
                        }
                        break;

                    case csconstants::Copyright:
                        if (!empty($value['Copyright'])) {
                            $combinedObjectValues[$field_name] = $value['Copyright'];
                        }
                        break;

                    case csconstants::School:
                        if (!empty($value['School'])) {
                            $combinedObjectValues[$field_name] = $value['School'];
                        }
                        break;

                    case csconstants::Lithology:
                        if (!empty($value['Lithology'])) {
                            $combinedObjectValues[$field_name] = $value['Lithology'];
                        }
                        break;

                    case csconstants::Horizon:
                        if (!empty($value['Horizon']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['Horizon']['Term'];
                        }
                        break;

                    case csconstants::InsituFloat:
                        if (!empty($value['InsituFloat']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['InsituFloat']['Term'];
                        }
                        break;

                    case csconstants::Taphonomy:
                        if (!empty($value['Taphonomy'])) {
                            $combinedObjectValues[$field_name] = $value['Taphonomy'];
                        }
                        break;

                    case csconstants::Model:
                        if (!empty($value['Model'])) {
                            $combinedObjectValues[$field_name] = $value['Model'];
                        }
                        break;

                    case csconstants::Stones:
                        if (!empty($value['Stones'])) {
                            $combinedObjectValues[$field_name] = $value['Stones'];
                        }
                        break;

                    case csconstants::Karats:
                        if (!empty($value['Karats'])) {
                            $combinedObjectValues[$field_name] = $value['Karats'];
                        }
                        break;

                    case csconstants::Carats:
                        if (!empty($value['Carats'])) {
                            $combinedObjectValues[$field_name] = $value['Carats'];
                        }
                        break;

                    case csconstants::Cut:
                        if (!empty($value['Cut'])) {
                            $combinedObjectValues[$field_name] = $value['Cut'];
                        }
                        break;

                    case csconstants::Clarity:
                        if (!empty($value['Clarity'])) {
                            $combinedObjectValues[$field_name] = $value['Clarity'];
                        }
                        break;

                    case csconstants::TypeOfGemstone:
                        if (!empty($value['TypeOfGemstone'])) {
                            $combinedObjectValues[$field_name] = $value['TypeOfGemstone'];
                        }
                        break;

                    case csconstants::Size:
                        if (!empty($value['Size']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['Size']['Term'];
                        }
                        break;

                    case csconstants::MetalType:
                        if (!empty($value['MetalType'])) {
                            $combinedObjectValues[$field_name] = $value['MetalType'];
                        }
                        break;

                    case csconstants::DrivenBy:
                        if (!empty($value['DrivenBy'])) {
                            $combinedObjectValues[$field_name] = $value['DrivenBy'];
                        }
                        break;

                    case csconstants::VIN:
                        if (!empty($value['VIN'])) {
                            $combinedObjectValues[$field_name] = $value['VIN'];
                        }
                        break;

                    case csconstants::ChassisNumber:
                        if (!empty($value['ChassisNumber'])) {
                            $combinedObjectValues[$field_name] = $value['ChassisNumber'];
                        }
                        break;

                    case csconstants::Mileage:
                        if (!empty($value['Mileage'])) {
                            $combinedObjectValues[$field_name] = $value['Mileage'];
                        }
                        break;

                    case csconstants::Power:
                        if (!empty($value['Power'])) {
                            $combinedObjectValues[$field_name] = $value['Power'];
                        }
                        break;
                    case csconstants::EngineType:
                      if (!empty($value['EngineType'])) {
                          $combinedObjectValues[$field_name] = $value['EngineType'];
                      }
                      break;

                    case csconstants::EnginePosition:
                        if (!empty($value['EnginePosition'])) {
                            $combinedObjectValues[$field_name] = $value['EnginePosition'];
                        }
                        break;

                    case csconstants::Transmission:
                        if (!empty($value['Transmission']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['Transmission']['Term'];
                        }
                        break;

                    case csconstants::Passengers:
                        if (!empty($value['Passengers'])) {
                            $combinedObjectValues[$field_name] = $value['Passengers'];
                        }
                        break;

                    case csconstants::FuelHighway:
                        if (!empty($value['FuelHighway'])) {
                            $combinedObjectValues[$field_name] = $value['FuelHighway'];
                        }
                        break;

                    case csconstants::Acceleration:
                        if (!empty($value['Acceleration'])) {
                            $combinedObjectValues[$field_name] = $value['Acceleration'];
                        }
                        break;

                    case csconstants::TopSpeed:
                        if (!empty($value['TopSpeed'])) {
                            $combinedObjectValues[$field_name] = $value['TopSpeed'];
                        }
                        break;

                    case csconstants::EngineNumber:
                        if (!empty($value['EngineNumber'])) {
                            $combinedObjectValues[$field_name] = $value['EngineNumber'];
                        }
                        break;

                    case csconstants::LicensePlateNumber:
                        if (!empty($value['LicensePlateNumber'])) {
                            $combinedObjectValues[$field_name] = $value['LicensePlateNumber'];
                        }
                        break;

                    case csconstants::TransmissionFluid:
                        if (!empty($value['TransmissionFluid'])) {
                            $combinedObjectValues[$field_name] = $value['TransmissionFluid'];
                        }
                        break;

                    case csconstants::BrakeFluid:
                        if (!empty($value['BrakeFluid'])) {
                            $combinedObjectValues[$field_name] = $value['BrakeFluid'];
                        }
                        break;

                    case csconstants::OilType:
                        if (!empty($value['OilType'])) {
                            $combinedObjectValues[$field_name] = $value['OilType'];
                        }
                        break;

                    case csconstants::FuelType:
                        if (!empty($value['FuelType'])) {
                            $combinedObjectValues[$field_name] = $value['FuelType'];
                        }
                        break;

                    case csconstants::RegistrationStatus:
                        if (!empty($value['RegistrationStatus']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['RegistrationStatus']['Term'];
                        }
                        break;

                    case csconstants::TitleStatus:
                        if (!empty($value['TitleStatus']['Term'])) {
                            $combinedObjectValues[$field_name] = $value['TitleStatus']['Term'];
                        }
                        break;

                    case csconstants::Paint:
                        if (!empty($value['Paint'])) {
                            $combinedObjectValues[$field_name] = $value['Paint'];
                        }
                        break;
                    case csconstants::Battery:
                      if (!empty($value['Battery'])) {
                          $combinedObjectValues[$field_name] = $value['Battery'];
                      }
                      break;

                    case csconstants::ShiftPattern:
                        if (!empty($value['ShiftPattern'])) {
                            $combinedObjectValues[$field_name] = $value['ShiftPattern'];
                        }
                        break;

                    case csconstants::DashLayout:
                        if (!empty($value['DashLayout'])) {
                            $combinedObjectValues[$field_name] = $value['DashLayout'];
                        }
                        break;

                    case csconstants::TypeOfWine:
                        if (!empty($value['TypeOfWine'])) {
                            $combinedObjectValues[$field_name] = $value['TypeOfWine'];
                        }
                        break;

                    case csconstants::Maturity:
                        if (!empty($value['Maturity'])) {
                            $combinedObjectValues[$field_name] = $value['Maturity'];
                        }
                        break;

                    case csconstants::Grape:
                        if (!empty($value['Grape'])) {
                            $combinedObjectValues[$field_name] = $value['Grape'];
                        }
                        break;

                    case csconstants::Region:
                        if (!empty($value['Region'])) {
                            $combinedObjectValues[$field_name] = $value['Region'];
                        }
                        break;

                    case csconstants::BottleSize:
                        if (!empty($value['BottleSize'])) {
                            $combinedObjectValues[$field_name] = $value['BottleSize'];
                        }
                        break;

                    case csconstants::FermentationPeriod:
                        if (!empty($value['FermentationPeriod'])) {
                            $combinedObjectValues[$field_name] = $value['FermentationPeriod'];
                        }
                        break;

                    case csconstants::DesignerName:
                        if (!empty($value['Designer']['ContactName'])) {
                            $combinedObjectValues[$field_name] = $value['Designer']['ContactName'];
                        }
                        break;

                    case csconstants::Brand:
                        if (!empty($value['Brand'])) {
                            $combinedObjectValues[$field_name] = $value['Brand'];
                        }
                        break;

                    case csconstants::FabricMaterial:
                        if (!empty($value['FabricMaterial'])) {
                            $combinedObjectValues[$field_name] = $value['FabricMaterial'];
                        }
                        break;

                    case csconstants::SKU:
                        if (!empty($value['SKU'])) {
                            $combinedObjectValues[$field_name] = $value['SKU'];
                        }
                        break;

                    /*dimension fields */
                    case csconstants::HeightMetric:
                      if (!empty($value['MainDimension']['HeightMetric'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['HeightMetric'];
                      }
                      break;

                  case csconstants::WidthMetric:
                      if (!empty($value['MainDimension']['WidthMetric'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['WidthMetric'];
                      }
                      break;

                  case csconstants::DepthMetric:
                      if (!empty($value['MainDimension']['DepthMetric'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['DepthMetric'];
                      }
                      break;

                  case csconstants::DiameterMetric:
                      if (!empty($value['MainDimension']['DiameterMetric'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['DiameterMetric'];
                      }
                      break;

                  case csconstants::WeightMetric:
                      if (!empty($value['MainDimension']['WeightMetric'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['WeightMetric'];
                      }
                      break;

                  case csconstants::WeightImperial:
                      if (!empty($value['MainDimension']['WeightImperial'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['WeightImperial'];
                      }
                      break;

                  case csconstants::HeightImperial:
                      if (!empty($value['MainDimension']['HeightImperial'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['HeightImperial'];
                      }
                      break;

                  case csconstants::WidthImperial:
                      if (!empty($value['MainDimension']['WidthImperial'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['WidthImperial'];
                      }
                      break;

                  case csconstants::DepthImperial:
                      if (!empty($value['MainDimension']['DepthImperial'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['DepthImperial'];
                      }
                      break;

                  case csconstants::DiameterImperial:
                      if (!empty($value['MainDimension']['DiameterImperial'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['DiameterImperial'];
                      }
                      break;

                  case csconstants::SquareMeters:
                      if (!empty($value['MainDimension']['SquareMeters'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['SquareMeters'];
                      }
                      break;

                  case csconstants::SquareFeet:
                      if (!empty($value['MainDimension']['SquareFeet'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['SquareFeet'];
                      }
                      break;
                  case csconstants::ImperialDims:
                    if (!empty($value['MainDimension']['ImperialDims'])) {
                        $combinedObjectValues[$field_name] = $value['MainDimension']['ImperialDims'];
                    }
                    break;

                  case csconstants::MetricDims:
                      if (!empty($value['MainDimension']['MetricDims'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['MetricDims'];
                      }
                      break;

                  case csconstants::DimensionDescription:
                      if (!empty($value['MainDimension']['DimensionDescription']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['MainDimension']['DimensionDescription']['Term'];
                      }
                      break;
                  /*richtext fields*/


                  /*spectrumobject fields*/

                  case csconstants::OtherNumberType:
                    if (!empty($value['OtherNumberType']['Term'])) {
                        $combinedObjectValues[$field_name] = $value['OtherNumberType']['Term'];
                    }
                    break;

                case csconstants::ResponsibleDepartment:
                    if (!empty($value['ResponsibleDepartment']['Term'])) {
                        $combinedObjectValues[$field_name] = $value['ResponsibleDepartment']['Term'];
                    }
                    break;

                case csconstants::Completeness:
                    if (!empty($value['Completeness']['Term'])) {
                        $combinedObjectValues[$field_name] = $value['Completeness']['Term'];
                    }
                    break;

                case csconstants::CompletenessDate:
                    if (!empty($value['CompletenessDate'])) {
                        $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['CompletenessDate']));
                    }
                    break;

                case csconstants::CompletenessNote:
                    if (!empty($value['CompletenessNote'])) {
                        $combinedObjectValues[$field_name] = $value['CompletenessNote'];
                    }
                    break;

                case csconstants::MovementReferenceNumber:
                    if (!empty($value['MovementReferenceNumber'])) {
                        $combinedObjectValues[$field_name] = $value['MovementReferenceNumber'];
                    }
                    break;

                case csconstants::MovementAuthorizerContactName:
                    if (!empty($value['MovementAuthorizer']['ContactName'])) {
                        $combinedObjectValues[$field_name] = $value['MovementAuthorizer']['ContactName'];
                    }
                    break;

                case csconstants::MovementAuthorizationDate:
                    if (!empty($value['MovementAuthorizationDate'])) {
                        $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['MovementAuthorizationDate']));
                    }
                    break;

                case csconstants::MovementContactName:
                    if (!empty($value['MovementContact']['ContactName'])) {
                        $combinedObjectValues[$field_name] = $value['MovementContact']['ContactName'];
                    }
                    break;

                case csconstants::MovementMethod:
                    if (!empty($value['MovementMethod']['Term'])) {
                        $combinedObjectValues[$field_name] = $value['MovementMethod']['Term'];
                    }
                    break;

                case csconstants::MovementMemo:
                    if (!empty($value['MovementMemo'])) {
                        $combinedObjectValues[$field_name] = $value['MovementMemo'];
                    }
                    break;

                case csconstants::MovementReason:
                    if (!empty($value['MovementReason']['Term'])) {
                        $combinedObjectValues[$field_name] = $value['MovementReason']['Term'];
                    }
                    break;

                case csconstants::PlannedRemoval:
                    if (!empty($value['PlannedRemoval'])) {
                        $combinedObjectValues[$field_name] = $value['PlannedRemoval'];
                    }
                    break;

                case csconstants::LocationReferenceNameNumber:
                    if (!empty($value['LocationReferenceNameNumber'])) {
                        $combinedObjectValues[$field_name] = $value['LocationReferenceNameNumber'];
                    }
                    break;

                case csconstants::LocationType:
                    if (!empty($value['LocationType']['Term'])) {
                        $combinedObjectValues[$field_name] = $value['LocationType']['Term'];
                    }
                    break;
                    case csconstants::LocationAccessMemo:
                      if (!empty($value['LocationAccessMemo'])) {
                          $combinedObjectValues[$field_name] = $value['LocationAccessMemo'];
                      }
                      break;

                  case csconstants::LocationConditionMemo:
                      if (!empty($value['LocationConditionMemo'])) {
                          $combinedObjectValues[$field_name] = $value['LocationConditionMemo'];
                      }
                      break;

                  case csconstants::LocationConditionDate:
                      if (!empty($value['LocationConditionDate'])) {
                          $combinedObjectValues[$field_name] = date('m/d/Y', strtotime($value['LocationConditionDate']));
                      }
                      break;

                  case csconstants::LocationSecurityMemo:
                      if (!empty($value['LocationSecurityMemo'])) {
                          $combinedObjectValues[$field_name] = $value['LocationSecurityMemo'];
                      }
                      break;

                  case csconstants::ObjectNameCurrency:
                      if (!empty($value['ObjectNameCurrency']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['ObjectNameCurrency']['Term'];
                      }
                      break;

                  case csconstants::ObjectNameLevel:
                      if (!empty($value['ObjectNameLevel']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['ObjectNameLevel']['Term'];
                      }
                      break;

                  case csconstants::ObjectNameNote:
                      if (!empty($value['ObjectNameNote'])) {
                          $combinedObjectValues[$field_name] = $value['ObjectNameNote'];
                      }
                      break;

                  case csconstants::ObjectNameSystem:
                      if (!empty($value['ObjectNameSystem']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['ObjectNameSystem']['Term'];
                      }
                      break;

                  case csconstants::ObjectNameType:
                      if (!empty($value['ObjectNameType']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['ObjectNameType']['Term'];
                      }
                      break;

                  case csconstants::ObjectNameTitleLanguage:
                      if (!empty($value['ObjectNameTitleLanguage']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['ObjectNameTitleLanguage']['Term'];
                      }
                      break;

                  case csconstants::FieldCollectionMethod:
                      if (!empty($value['FieldCollectionMethod']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['FieldCollectionMethod']['Term'];
                      }
                      break;

                  case csconstants::FieldCollectionPlace:
                      if (!empty($value['FieldCollectionPlace'])) {
                          $combinedObjectValues[$field_name] = $value['FieldCollectionPlace'];
                      }
                      break;

                  case csconstants::FieldCollectionSourceContactName:
                      if (!empty($value['FieldCollectionSource']['ContactName'])) {
                          $combinedObjectValues[$field_name] = $value['FieldCollectionSource']['ContactName'];
                      }
                      break;

                  case csconstants::FieldCollectionMemo:
                      if (!empty($value['FieldCollectionMemo'])) {
                          $combinedObjectValues[$field_name] = $value['FieldCollectionMemo'];
                      }
                      break;

                  case csconstants::GeologicalComplexName:
                      if (!empty($value['GeologicalComplexName']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['GeologicalComplexName']['Term'];
                      }
                      break;

                  case csconstants::Habitat:
                      if (!empty($value['Habitat']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['Habitat']['Term'];
                      }
                      break;

                  case csconstants::HabitatMemo:
                      if (!empty($value['HabitatMemo'])) {
                          $combinedObjectValues[$field_name] = $value['HabitatMemo'];
                      }
                      break;

                  case csconstants::StratigraphicUnitName:
                      if (!empty($value['StratigraphicUnitName']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['StratigraphicUnitName']['Term'];
                      }
                      break;

                  case csconstants::StratigraphicUnitType:
                      if (!empty($value['StratigraphicUnitType']['Term'])) {
                          $combinedObjectValues[$field_name] = $value['StratigraphicUnitType']['Term'];
                      }
                      break;

                  case csconstants::StratigraphicUnitMemo:
                      if (!empty($value['StratigraphicUnitMemo'])) {
                          $combinedObjectValues[$field_name] = $value['StratigraphicUnitMemo'];
                      }
                      break;
                  /*udf fields*/
                  case csconstants::UserDefined1:
                    if (!empty($value['UserDefined1'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined1'];
                    }
                    break;

                case csconstants::UserDefined2:
                    if (!empty($value['UserDefined2'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined2'];
                    }
                    break;

                case csconstants::UserDefined3:
                    if (!empty($value['UserDefined3'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined3'];
                    }
                    break;

                case csconstants::UserDefined4:
                    if (!empty($value['UserDefined4'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined4'];
                    }
                    break;

                case csconstants::UserDefined5:
                    if (!empty($value['UserDefined5'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined5'];
                    }
                    break;

                case csconstants::UserDefined6:
                    if (!empty($value['UserDefined6'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined6'];
                    }
                    break;

                case csconstants::UserDefined7:
                    if (!empty($value['UserDefined7'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined7'];
                    }
                    break;

                case csconstants::UserDefined8:
                    if (!empty($value['UserDefined8'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined8'];
                    }
                    break;

                case csconstants::UserDefined9:
                    if (!empty($value['UserDefined9'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined9'];
                    }
                    break;

                case csconstants::UserDefined10:
                    if (!empty($value['UserDefined10'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined10'];
                    }
                    break;

                case csconstants::UserDefined11:
                    if (!empty($value['UserDefined11'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined11'];
                    }
                    break;

                case csconstants::UserDefined12:
                    if (!empty($value['UserDefined12'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined12'];
                    }
                    break;

                case csconstants::UserDefined13:
                    if (!empty($value['UserDefined13'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined13'];
                    }
                    break;

                case csconstants::UserDefined14:
                    if (!empty($value['UserDefined14'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined14'];
                    }
                    break;

                case csconstants::UserDefined15:
                    if (!empty($value['UserDefined15'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined15'];
                    }
                    break;

                case csconstants::UserDefined16:
                    if (!empty($value['UserDefined16'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined16'];
                    }
                    break;

                case csconstants::UserDefined17:
                    if (!empty($value['UserDefined17'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined17'];
                    }
                    break;

                case csconstants::UserDefined18:
                    if (!empty($value['UserDefined18'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined18'];
                    }
                    break;

                case csconstants::UserDefined19:
                    if (!empty($value['UserDefined19'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined19'];
                    }
                    break;

                case csconstants::UserDefined20:
                    if (!empty($value['UserDefined20'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined20'];
                    }
                    break;

                case csconstants::UserDefined21:
                    if (!empty($value['UserDefined21'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined21'];
                    }
                    break;

                case csconstants::UserDefined22:
                    if (!empty($value['UserDefined22'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined22'];
                    }
                    break;

                case csconstants::UserDefined23:
                    if (!empty($value['UserDefined23'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined23'];
                    }
                    break;

                case csconstants::UserDefined24:
                    if (!empty($value['UserDefined24'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined24'];
                    }
                    break;

                case csconstants::UserDefined25:
                    if (!empty($value['UserDefined25'])) {
                        $combinedObjectValues[$field_name] = $value['UserDefined25'];
                    }
                    break;
                    case csconstants::UserDefined26:
                      if (!empty($value['UserDefined26'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined26'];
                      }
                      break;

                  case csconstants::UserDefined27:
                      if (!empty($value['UserDefined27'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined27'];
                      }
                      break;

                  case csconstants::UserDefined28:
                      if (!empty($value['UserDefined28'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined28'];
                      }
                      break;

                  case csconstants::UserDefined29:
                      if (!empty($value['UserDefined29'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined29'];
                      }
                      break;

                  case csconstants::UserDefined30:
                      if (!empty($value['UserDefined30'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined30'];
                      }
                      break;

                  case csconstants::UserDefined31:
                      if (!empty($value['UserDefined31'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined31'];
                      }
                      break;

                  case csconstants::UserDefined32:
                      if (!empty($value['UserDefined32'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined32'];
                      }
                      break;

                  case csconstants::UserDefined33:
                      if (!empty($value['UserDefined33'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined33'];
                      }
                      break;

                  case csconstants::UserDefined34:
                      if (!empty($value['UserDefined34'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined34'];
                      }
                      break;

                  case csconstants::UserDefined35:
                      if (!empty($value['UserDefined35'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined35'];
                      }
                      break;

                  case csconstants::UserDefined36:
                      if (!empty($value['UserDefined36'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined36'];
                      }
                      break;

                  case csconstants::UserDefined37:
                      if (!empty($value['UserDefined37'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined37'];
                      }
                      break;

                  case csconstants::UserDefined38:
                      if (!empty($value['UserDefined38'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined38'];
                      }
                      break;

                  case csconstants::UserDefined39:
                      if (!empty($value['UserDefined39'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined39'];
                      }
                      break;

                  case csconstants::UserDefined40:
                      if (!empty($value['UserDefined40'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefined40'];
                      }
                      break;

                  case csconstants::UserDefinedDate1:
                      if (!empty($value['UserDefinedDate1'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefinedDate1'];
                      }
                      break;

                  case csconstants::UserDefinedDate2:
                      if (!empty($value['UserDefinedDate2'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefinedDate2'];
                      }
                      break;

                  case csconstants::UserDefinedNumber1:
                      if (!empty($value['UserDefinedNumber1'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefinedNumber1'];
                      }
                      break;

                  case csconstants::UserDefinedNumber2:
                      if (!empty($value['UserDefinedNumber2'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefinedNumber2'];
                      }
                      break;

                  case csconstants::UserDefinedCurrency1:
                      if (!empty($value['UserDefinedCurrency1'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefinedCurrency1'];
                      }
                      break;

                  case csconstants::UserDefinedCurrency2:
                      if (!empty($value['UserDefinedCurrency2'])) {
                          $combinedObjectValues[$field_name] = $value['UserDefinedCurrency2'];
                      }
                      break;

                  default:
                    break;
                }


          }




          $id1 = $value['ObjectId'];

          $objectIds_API[] = $id1;

          $imgId1 = NULL;
          if(isset($value['MainImageAttachmentId']) && $value['MainImageAttachmentId'] !== NULL)
          {
              $imgId1 = $value['MainImageAttachmentId'];
          }
          $artistId = 0;
          if(isset($value['ArtistId']) && $value['ArtistId'] != NULL)
          {
              $artistId = $value['ArtistId'];
          }
          if(isset($value['Artist']['ArtistId']) && $value['Artist']['ArtistId'] != NULL)
          {
              $artistId = $value['Artist']['ArtistId'];
          }
          $title = NULL;
          if(isset($value['Title']) && $value['Title'] != NULL)
          {
              $title = $value['Title'];
          }
          $inventNumber = NULL;
          if(isset($value['InventoryNumber']) && $value['InventoryNumber'] != NULL)
          {
            $inventNumber = $value['InventoryNumber'];
          }

          $objectDate = NULL;
          if(isset($value['ObjectDate']) && $value['ObjectDate'] != NULL)
          {
              $objectDate = $value['ObjectDate'];
          }

          $collectionId = 0;
          if(isset($value['CollectionId']) && $value['CollectionId'] != NULL)
          {
              $collectionId = $value['CollectionId'];
          }
          if(isset($value['Collection']['CollectionId']) && $value['Collection']['CollectionId'] != NULL)
          {
              $collectionId = $value['Collection']['CollectionId'];
          }
          if(isset($value['ModificationDate']) && $value['ModificationDate'] !== NULL)
          {
              $ModificationDate = $value['ModificationDate'];
          }elseif(isset($value['CreationDate']) && $value['CreationDate'] !== NULL){
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


        if($btn_action == 'update-dataset'){
          // Check if the record exists.
           $record_exists = $database->select($table_name)
           ->fields($table_name)
           ->condition('ObjectId', $id1)
           ->execute()
           ->fetchAssoc();

          if($record_exists){
            // Update the existing record if the ModificationDate has changed
            $database->update($table_name)
              ->fields($values)
              ->condition('ObjectId', $id1)
              ->condition('ModificationDate', $ModificationDate, '<>')
              ->execute();
          }else{
              // Handle if record doesn't exist
              // Insert data into the table.
            $database->insert($table_name)
              ->fields($values)
              ->execute();
          }

        }else{
          // Perform the database insert
          $database->insert($table_name)
          ->fields($values)
          ->execute();
        }


      }//End Objects



      if($objectIds_API){
        $this->remove_unrequired_Objects_from_Database($objectIds_API);
      }

  }
  function sync_api_data_Artists($btn_action){

    // Get the api config settings
    $config = \Drupal::config('custom_api_integration.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');


    $database = Database::getConnection();
    $table_name = 'Artists';
    // Fetching Artist's API Data
    $wordforsearch="Artists";
    $url = csconstants::Public_API_URL . $subAcntId . '/' . $wordforsearch.'&$filter=SubscriptionId%20eq%20' . $subsId;
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

      $data = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          exit();
      }
      $data = json_decode($data, TRUE); //End Artist's API Data

      //Fetch Artist Images
      $url = csconstants::Public_API_URL.$subAcntId . '/Artists?&$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=ArtistPhotoAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)';
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
          exit();
      }
      $ArtistPhoto = json_decode($ArtistPhoto, TRUE);

      $artistIds_API = [];
      //Start Artists
      foreach ($ArtistPhoto['value'] as $art)
      {

          $artistId = $art['ArtistId'];
          $artistIds_API[] = $artistId;
          $artistName = NULL;
          $artistFirst = NULL;
          $artistLast = NULL;
          $artistYears = NULL;
          $artistNationality = NULL;
          $artistLocale = NULL;
          $artistBio = NULL;
          if(isset($art['ArtistName']) && $art['ArtistName'] !== NULL)
          {
              $artistName = $art['ArtistName'];
          }
          if(isset($art['ArtistFirst']) && $art['ArtistFirst'] !== NULL)
          {
              $artistFirst = $art['ArtistFirst'];
          }
          if(isset($art['ArtistLast']) && $art['ArtistLast'] !== NULL)
          {
              $artistLast = $art['ArtistLast'];
          }
          if(isset($art['ArtistYears']) && $art['ArtistYears'] !== NULL)
          {
              $artistYears = $art['ArtistYears'];
          }
          if(isset($art['ArtistNationality']) && $art['ArtistNationality'] !== NULL)
          {
              $artistNationality = $art['ArtistNationality'];
          }
          if(isset($art['ArtistLocale']) && $art['ArtistLocale'] !== NULL)
          {
              $artistLocale = $art['ArtistLocale'];
          }
          if(isset($art['ArtistBio']) && $art['ArtistBio'] !== NULL)
          {
              $artistBio = $art['ArtistBio'];
          }
          if(isset($art['ModificationDate']) && $art['ModificationDate'] !== NULL)
          {
              $ModificationDate = $art['ModificationDate'];
          }elseif(isset($art['CreationDate']) && $art['CreationDate'] !== NULL){
            $ModificationDate = $art['CreationDate'];
          }
          if($artistId !== 0)
          {
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
              if($btn_action == 'update-dataset'){
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


              }else{
                // Insert data into the table.
                $result = $database->insert($table_name)
                ->fields($data)
                ->execute();
              }

          }

      } //End Artists

      if($artistIds_API){
        $this->remove_unrequired_Artists_from_Database($artistIds_API);
      }
  }

  /**
   * Helper function to drop the tables.
   */
  function custom_api_integration_drop_tables($btn_action) {
    if($btn_action == 'update-dataset'){
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
        \Drupal::logger('custom_api_integration')->notice('Dropped table: %table_name', ['%table_name' => $table_name]);
      } else {
        \Drupal::logger('custom_api_integration')->notice('Table does not exist: %table_name', ['%table_name' => $table_name]);
      }
    }
  }

  /**
   * Helper function to create the dynamic table.
   */
  function custom_api_integration_create_tables($btn_action) {
    if($btn_action == 'update-dataset'){
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
        'FileURL' => [
          'type' => 'text',
        ],
        'ModificationDate' => [
          'type' => 'varchar',
          'length' => 500,
        ],
        'LatitudeDegrees' => [
          'type' => 'numeric',
          'precision' => 9,
          'scale' => 6,
        ],
        'LongitudeDegrees' => [
          'type' => 'numeric',
          'precision' => 9,
          'scale' => 6,
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
                  'type' => 'varchar',
                  'length' => 500
              ];
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
          'type' => 'varchar',
          'length' => 500,
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


  function get_field_names(){
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


  function getDynamicUrlForEndpoint($objectFields_arr,$dynamicurl,$searchWord,$apiCallFor="")
    {
      // Get the api config settings
     $config = \Drupal::config('custom_api_integration.settings');
     $subsId = $config->get('subscription_id');


    $querymid="%20OR%20";
            // $objectFields_arr = explode(',', $savedfields);
            // $objectFields_arr = ['ArtistName', 'AboriginalName', 'Acceleration', 'InventoryNumber'];
            //List<string> NonExpandableFields = new List<string>();
            $non_expandable_fields = array();
            $filterTermArray = array();
            $filtercount = 0;
            $filtertemp='';
            $len = count($objectFields_arr);

            //to get CollectionId and ArtistId values by default
            $dynamicurl.= 'Collection($select=CollectionId),';
            $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistId),';

            foreach ($objectFields_arr as $field){
                //print_r($filtercount.'-'.$field);
                switch ($field)
                {
                    case "LocationName":
                    case "FullLocationName":
                      $dynamicurl.= 'Location($select=LocationId,LocationName,FullLocationName),';
                      $srch_temp="contains".'(Location/'.$field.','."'$searchWord'".')';
                      break;

                    case "PermanentLocationName":
                      $dynamicurl.= 'PermanentLocation($select=LocationId,LocationName,FullLocationName),';
                      $srch_temp="contains".'(PermanentLocation/LocationName,'."'$searchWord'".')';
                      break;
                    case "PermanentFullLocationName":
                        $dynamicurl.= 'PermanentLocation($select=LocationId,LocationName,FullLocationName),';
                        $srch_temp="contains".'(PermanentLocation/'.'FullLocationName'.','."'$searchWord'".')';
                        break;

                    case "CollectionName":
                    case "FullCollectionName":
                        $dynamicurl.= 'Collection($select=CollectionId,CollectionName,FullCollectionName),';
                        $srch_temp="contains".'(Collection/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistName":
                      $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistId,ArtistName,ArtistFirst,ArtistLast,ArtistCompany,ArtistGender,ArtistLocale,ArtistNationality,ArtistYears,ArtistBio),';
                      $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistAlias":
                      $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistAlias),';
                      $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistAliasFirst":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistAliasFirst),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistAliasLast":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistAliasLast),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistCompany":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistCompany),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistFirst":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistFirst),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistGender":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistGender),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistLast":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistLast),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistLink":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistLink),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistSchool":
                      $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistSchool),';
                      $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistLocale":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistLocale),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistNationality":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistNationality),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistYears":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistYears),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistRace":
                      $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistRace),';
                      $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistEthnicity":
                      $dynamicurl.= 'PublicAPIV2.Models.Art/Artist($select=ArtistEthnicity),';
                      $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/'.$field.','."'$searchWord'".')';
                        break;

                    case "ArtistMakerAlias":
                        $dynamicurl.= 'ArtistMaker($select=ArtistAlias),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistAlias,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerAliasFirst":
                        $dynamicurl.= 'ArtistMaker($select=ArtistAliasFirst),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistAliasFirst,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerAliasLast":
                        $dynamicurl.= 'ArtistMaker($select=ArtistAliasLast),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistAliasLast,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerCompany":
                        $dynamicurl.= 'ArtistMaker($select=ArtistCompany),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistCompany,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerFirst":
                        $dynamicurl.= 'ArtistMaker($select=ArtistFirst),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistFirst,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerGender":
                        $dynamicurl.= 'ArtistMaker($select=ArtistGender),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistGender,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerLast":
                        $dynamicurl.= 'ArtistMaker($select=ArtistLast),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistLast,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerLink":
                        $dynamicurl.= 'ArtistMaker($select=ArtistLink),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistLink,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerLocale":
                        $dynamicurl.= 'ArtistMaker($select=ArtistLocale),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistLocale,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerName":
                        $dynamicurl.= 'ArtistMaker($select=ArtistId,ArtistName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistName,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerNationality":
                        $dynamicurl.= 'ArtistMaker($select=ArtistNationality),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistNationality,'."'$searchWord'".')';
                        break;

                    case "ArtistMakerYears":
                        $dynamicurl.= 'ArtistMaker($select=ArtistYears),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/Artist/ArtistYears,'."'$searchWord'".')';
                        break;

                    case "AdditionalArtists":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/AdditionalArtists($expand=Artist($select=ArtistId,ArtistName);$select=Artist),';
                        //$srch_temp="contains".'(PublicAPIV2.Models.Art/AdditionalArtists/Artist/ArtistName,'."'$searchWord'".')';
                        break;

                    case "AdditionalArtistMakers":
                        $dynamicurl.= 'PublicAPIV2.Models.Art/AdditionalArtistMakers($expand=ArtistMaker($select=ArtistId,ArtistName);$select=ArtistMaker),';
                        //$srch_temp="contains".'(PublicAPIV2.Models.Art/AdditionalArtistMakers/Artist/ArtistName,'."'$searchWord'".')';
                        break;

                    case "AdditionalAuthors":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/AdditionalAuthors($expand=Author($select=AuthorId,AuthorName);$select=Author),';
                        //$srch_temp="contains".'(PublicAPIV2.Models.Archive/AdditionalAuthors/Author/AuthorName,'."'$searchWord'".')';
                        break;

                    case "Color":
                    case "Completeness":
                    case "CultureOfUse":
                    case "EpochSeries":
                    case "Event":
                    case "FieldCollectionMethod":
                    case "Format":
                    case "Genre":
                    case "GeoUnit":
                    case "GeologicalComplexName":
                    case "Habitat":
                    case "HistoricCulturalPeriod":
                    case "LocationStatus":
                    case "LocationType":
                    case "ManufacturingTechnique":
                    case "Member":
                    case "MovementMethod":
                    case "MovementReason":
                    case "NAGPRA":
                    case "ObjectNameCurrency":
                    case "ObjectNameLevel":
                    case "ObjectNameSystem":
                    case "ObjectNameTitleLanguage":
                    case "ObjectNameType":
                    case "OtherNumberType":
                    case "PeriodSystem":
                    case "ResponsibleDepartment":
                    case "Size":
                    case "SpecificClassOfMaterial":
                    case "StorageUnit":
                    case "StratigraphicUnitName":
                    case "StratigraphicUnitType":
                    case "Subgenre":
                    case "TypeSpecimen":
                    case "Unit":
                      $dynamicurl.= $field.'($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'('.$field.'/Term,'."'$searchWord'".')';
                        break;

                    case "Technique":
                      $dynamicurl.= 'PublicAPIV2.Models.Art/Technique($select=ControlledVocabId,Term),';
                      $srch_temp="contains".'(PublicAPIV2.Models.Art/Technique/'.$field.','."'$searchWord'".')';
                        break;

                    case "AdditionalArea":
                        $dynamicurl.= 'PublicAPIV2.Models.Ethnology/AdditionalArea($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Ethnology/AdditionalArea/'.$field.','."'$searchWord'".')';
                        break;

                    case "AboriginalName":
                      $dynamicurl.= 'PublicAPIV2.Models.Ethnology/AboriginalName($select=ControlledVocabId,Term),';
                      $srch_temp="contains".'(PublicAPIV2.Models.Ethnology/AboriginalName/'.$field.','."'$searchWord'".')';
                        break;

                    case "AgeStage":
                        $dynamicurl.= 'PublicAPIV2.Models.Geology/AgeStage($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Geology/AgeStage/'.$field.','."'$searchWord'".')';
                        break;

                    case "BroadClassOfMaterial":
                        $dynamicurl.= 'PublicAPIV2.Models.Archaeology/BroadClassOfMaterial($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archaeology/BroadClassOfMaterial/'.$field.','."'$searchWord'".')';
                        break;

                    case "CatalogLevel":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/CatalogLevel($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/CatalogLevel/'.$field.','."'$searchWord'".')';
                        break;

                    case "DecorativeTechnique":
                        $dynamicurl.= 'PublicAPIV2.Models.Archaeology/DecorativeTechnique($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archaeology/DecorativeTechnique/'.$field.','."'$searchWord'".')';
                        break;

                    case "ExoticNative":
                        $dynamicurl.= 'PublicAPIV2.Models.Biology/ExoticNative($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Biology/ExoticNative/'.$field.','."'$searchWord'".')';
                        break;

                    case "FormationPeriodSubstrate":
                        $dynamicurl.= 'PublicAPIV2.Models.Biology/FormationPeriodSubstrate($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Biology/FormationPeriodSubstrate/'.$field.','."'$searchWord'".')';
                        break;

                    case "HabitatCommunity":
                        $dynamicurl.= 'PublicAPIV2.Models.Biology/HabitatCommunity($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Biology/HabitatCommunity/'.$field.','."'$searchWord'".')';
                        break;

                    case "Horizon":
                        $dynamicurl.= 'PublicAPIV2.Models.Paleontology/Horizon($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Paleontology/Horizon/'.$field.','."'$searchWord'".')';
                        break;

                    case "InsituFloat":
                        $dynamicurl.= 'PublicAPIV2.Models.Paleontology/InsituFloat($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Paleontology/InsituFloat/'.$field.','."'$searchWord'".')';
                        break;

                    case "ObjectForm":
                        $dynamicurl.= 'PublicAPIV2.Models.Archaeology/ObjectForm($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archaeology/ObjectForm/'.$field.','."'$searchWord'".')';
                        break;

                    case "ObjectPart":
                        $dynamicurl.= 'PublicAPIV2.Models.Archaeology/ObjectPart($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archaeology/ObjectPart/'.$field.','."'$searchWord'".')';
                        break;


                    case "RegistrationStatus":
                        $dynamicurl.= 'PublicAPIV2.Models.Car/RegistrationStatus($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Car/RegistrationStatus/'.$field.','."'$searchWord'".')';
                        break;

                    case "Sex":
                        $dynamicurl.= 'PublicAPIV2.Models.Biology/Sex($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Biology/Sex/'.$field.','."'$searchWord'".')';
                        break;

                    case "Temper":
                        $dynamicurl.= 'PublicAPIV2.Models.Archaeology/Temper($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archaeology/Temper/'.$field.','."'$searchWord'".')';
                        break;

                    case "ThreatenedEndangeredSpeciesStatus":
                        $dynamicurl.= 'PublicAPIV2.Models.Biology/ThreatenedEndangeredSpeciesStatus($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Biology/ThreatenedEndangeredSpeciesStatus/'.$field.','."'$searchWord'".')';
                        break;

                    case "TitleStatus":
                        $dynamicurl.= 'PublicAPIV2.Models.Car/TitleStatus($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Car/TitleStatus/'.$field.','."'$searchWord'".')';
                        break;

                    case "Transmission":
                        $dynamicurl.= 'PublicAPIV2.Models.Car/Transmission($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Car/Transmission/'.$field.','."'$searchWord'".')';
                        break;

                    case "TypeName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archaeology/TypeName($select=ControlledVocabId,Term),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archaeology/TypeName/'.$field.','."'$searchWord'".')';
                        break;

                    //Contacts
                    case "ArtDirectorContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/ArtDirector($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/ArtDirector/ContactName,'."'$searchWord'".')';
                        break;

                    case "CatalogerContactName":
                        $dynamicurl.= 'CatalogerContact($select=ContactId,ContactName),';
                        $srch_temp="contains".'(CatalogerContact/ContactName,'."'$searchWord'".')';
                        break;

                    case "CinematographerContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Cinematographer($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Cinematographer/ContactName,'."'$searchWord'".')';
                        break;

                    case "CollectorContactName":
                        $dynamicurl.= 'CollectorContact($select=ContactId,ContactName),';
                        $srch_temp="contains".'(CollectorContact/ContactName,'."'$searchWord'".')';
                        break;

                    case "ComposerContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Composer($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Composer/ContactName,'."'$searchWord'".')';
                        break;

                    case "ContributorContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Contributor($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Contributor/ContactName,'."'$searchWord'".')';
                        break;

                    case "CreatorContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Creator($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Creator/ContactName,'."'$searchWord'".')';
                        break;

                    case "DesignerName":
                        $dynamicurl.= 'PublicAPIV2.Models.Clothing/Designer($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Clothing/Designer/ContactName,'."'$searchWord'".')';
                        break;

                    case "DirectorContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Director($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Director/ContactName,'."'$searchWord'".')';
                        break;

                    case "DistributionCompany":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/DistributionCompany($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/DistributionCompany/ContactName,'."'$searchWord'".')';
                        break;

                    case "EditorContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Editor($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Editor/ContactName,'."'$searchWord'".')';
                        break;

                    case "EminentFigureContactName":
                        $dynamicurl.= 'EminentFigureContact($select=ContactId,ContactName),';
                        $srch_temp="contains".'(EminentFigureContact/ContactName,'."'$searchWord'".')';
                        break;

                    case "EminentOrganizationContactName":
                        $dynamicurl.= 'EminentOrganizationContact($select=ContactId,ContactName),';
                        $srch_temp="contains".'(EminentOrganizationContact/ContactName,'."'$searchWord'".')';
                        break;

                    case "FieldCollectionSourceContactName":
                        $dynamicurl.= 'FieldCollectionSource($select=ContactId,ContactName),';
                        $srch_temp="contains".'(FieldCollectionSource/'.$field.','."'$searchWord'".')';
                        break;

                    case "IdentifiedByContactName":
                        $dynamicurl.= 'IdentifiedByContact($select=ContactId,ContactName),';
                        $srch_temp="contains".'(IdentifiedByContact/ContactName,'."'$searchWord'".')';
                        break;

                    case "IllustratorContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Illustrator($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Illustrator/ContactName,'."'$searchWord'".')';
                        break;

                    case "InventoryContactName":
                        $dynamicurl.= 'InventoryContact($select=ContactId,ContactName),';
                        $srch_temp="contains".'(InventoryContact/ContactName,'."'$searchWord'".')';
                        break;

                    case "MovementAuthorizerContactName":
                        $dynamicurl.= 'MovementAuthorizer($select=ContactId,ContactName),';
                        $srch_temp="contains".'(MovementAuthorizer/ContactName,'."'$searchWord'".')';
                        break;

                    case "MovementContactName":
                        $dynamicurl.= 'MovementContact($select=ContactId,ContactName),';
                        $srch_temp="contains".'(MovementContact/ContactName,'."'$searchWord'".')';
                        break;

                    case "NarratorContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Narrator($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Narrator/ContactName,'."'$searchWord'".')';
                        break;

                    case "PhotographyContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Photography($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Photography/ContactName,'."'$searchWord'".')';
                        break;

                    case "ProducerContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Producer($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Producer/ContactName,'."'$searchWord'".')';
                        break;

                    case "ProductionCompanyContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/ProductionCompany($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/ProductionCompany/ContactName,'."'$searchWord'".')';
                        break;

                    case "ProductionDesignerContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/ProductionDesigner($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/ProductionDesigner/ContactName,'."'$searchWord'".')';
                        break;

                    case "PublisherContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Publisher($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Publisher/ContactName,'."'$searchWord'".')';
                        break;

                    case "SpeciesAuthorName":
                        $dynamicurl.= 'SpeciesAuthor($select=ContactId,ContactName),';
                        $srch_temp="contains".'(SpeciesAuthor/ContactName,'."'$searchWord'".')';
                        break;

                    case "StudioContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Studio($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Studio/ContactName,'."'$searchWord'".')';
                        break;

                    case "SubspeciesAuthorName":
                        $dynamicurl.= 'SubspeciesAuthor($select=ContactId,ContactName),';
                        $srch_temp="contains".'(SubspeciesAuthor/ContactName,'."'$searchWord'".')';
                        break;

                    case "SubspeciesAuthorityContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Biology/SubspeciesAuthorityContact($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Biology/SubspeciesAuthorityContact/ContactName,'."'$searchWord'".')';
                        break;

                    case "SubspeciesFormaAuthorityContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Biology/SubspeciesFormaAuthorityContact($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Biology/SubspeciesFormaAuthorityContact/ContactName,'."'$searchWord'".')';
                        break;

                    case "SubspeciesVarietyAuthorityContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Biology/SubspeciesVarietyAuthorityContact($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Biology/SubspeciesVarietyAuthorityContact/ContactName,'."'$searchWord'".')';
                        break;


                    case "WriterContactName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Writer($select=ContactId,ContactName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Writer/ContactName,'."'$searchWord'".')';
                        break;

                    case "AuthorName":
                        $dynamicurl.= 'PublicAPIV2.Models.Archive/Author($select=AuthorId,AuthorName),';
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/Author/'.$field.','."'$searchWord'".')';
                        break;

                    case "ObjectType":
                        $dynamicurl.= 'ObjectType($select=ObjectTypeId,ObjectTypeName),';
                        $srch_temp="contains".'(ObjectType/ObjectTypeName,'."'$searchWord'".')';
                        break;

                    case "HeightMetric":
                    case "WidthMetric":
                    case "DepthMetric":
                    case "DiameterMetric":
                    case "WeightMetric":
                    case "WeightImperial":
                    case "HeightImperial":
                    case "WidthImperial":
                    case "DepthImperial":
                    case "DiameterImperial":
                    case "SquareMeters":
                    case "SquareFeet":
                    case "ImperialDims":
                    case "MetricDims":
                        $dynamicurl.= 'MainDimension($select=DimensionId,'.$field.'),';
                        $srch_temp="contains".'(MainDimension/'.$field.','."'$searchWord'".')';
                        break;

                    case "DimensionMemo":
                        $dynamicurl.= 'MainDimension($select=DimensionId,'.$field.'),';
                        $decodedSearchWord = urldecode($searchWord);
                        $htmldecodeValue = htmlentities($decodedSearchWord); //html decode
                        $urlencodeValue = urlencode($htmldecodeValue);
                        $srch_temp="contains".'(MainDimension/'.$field.','."'$searchWord'".')';
                        break;

                    case "DimensionDescription":
                        $dynamicurl.= 'MainDimension($select=DimensionId,DimensionDescriptionId;$expand=DimensionDescription($select=term)),';
                        $srch_temp="contains".'(MainDimension/'.$field.'/Term,'."'$searchWord'".')';
                        break;

                    case "ObjectDescription":
                    case "InventoryMemo":
                    case "Signatures":
                    case "Inscriptions":
                    case "Labels":
                    case "Provenance":
                    case "ResearchNotes":
                    case "StaffNotes":
                    case "RelatedCollections":
                    case "KeyDescriptor":
                    case "WithinSiteProveniance":
                    case "Waterbody":
                    case "Transcription":
                    case "Drainage":
                    case "ObjectUse":
                    case "CompletenessNote":
                    case "MovementMemo":
                    case "LocationAccessMemo":
                    case "LocationConditionMemo":
                    case "LocationSecurityMemo":
                    case "ObjectNameNote":
                    case "FieldCollectionMemo":
                    case "HabitatMemo":
                    case "StratigraphicUnitMemo":
                    case "UserDefinedRichText1":
                    case "UserDefinedRichText2":
                    case "UserDefinedRichText3":
                    case "UserDefinedRichText4":
                    case "UserDefinedRichText5":
                    case "UserDefinedRichText6":
                    case "UserDefinedRichText7":
                    case "UserDefinedRichText8":
                    case "UserDefinedRichText9":
                    case "UserDefinedRichText10":
                    case "UserDefinedRichText11":
                    case "UserDefinedRichText12":
                    case "UserDefinedRichText13":
                    case "UserDefinedRichText14":
                    case "UserDefinedRichText15":
                    case "UserDefinedRichText16":
                    case "UserDefinedRichText17":
                    case "UserDefinedRichText18":
                        array_push($non_expandable_fields, $field);
                        $decodedSearchWord = urldecode($searchWord);
                        $htmldecodeValue = htmlentities($decodedSearchWord); //html decode
                        $urlencodeValue = urlencode($htmldecodeValue);
                        $srch_temp="contains".'('.$field.','."'$urlencodeValue'".')';

                        // echo '<script>console.log("searchWord: ' . $decodedSearchWord . '")</script>';
                        // echo '<script>console.log("htmldecodeValue: ' . $htmldecodeValue . '")</script>';
                        // echo '<script>console.log("urlencodeValue: ' . $urlencodeValue . '")</script>';

                        break;

                    case "ReferenceNotes":
                        array_push($non_expandable_fields, $field);
                        $decodedSearchWord = urldecode($searchWord);
                        $htmldecodeValue = htmlentities($decodedSearchWord); //html decode
                        $urlencodeValue = urlencode($htmldecodeValue);
                        $srch_temp="contains".'(PublicAPIV2.Models.Art/'.$field.','."'$urlencodeValue'".')';
                        break;

                    case "SubspeciesDescriptiveName":
                    case "AssociatedSpecies":
                        array_push($non_expandable_fields, $field);
                        $decodedSearchWord = urldecode($searchWord);
                        $htmldecodeValue = htmlentities($decodedSearchWord); //html decode
                        $urlencodeValue = urlencode($htmldecodeValue);
                        $srch_temp="contains".'(PublicAPIV2.Models.Biology/'.$field.','."'$urlencodeValue'".')';
                    break;

                    case "History":
                    case "CastAndCrew":
                    case "Synopsis":
                        array_push($non_expandable_fields, $field);
                        $decodedSearchWord = urldecode($searchWord);
                        $htmldecodeValue = htmlentities($decodedSearchWord); //html decode
                        $urlencodeValue = urlencode($htmldecodeValue);
                        $srch_temp="contains".'(PublicAPIV2.Models.Archive/'.$field.','."'$urlencodeValue'".')';
                        break;

                    case "StartingInstructions":
                    case "RegistrationNotes":
                    case "TitleStatusNotes":
                    case "RepairsMade":
                        array_push($non_expandable_fields, $field);
                        $decodedSearchWord = urldecode($searchWord);
                        $htmldecodeValue = htmlentities($decodedSearchWord); //html decode
                        $urlencodeValue = urlencode($htmldecodeValue);
                        $srch_temp="contains".'(PublicAPIV2.Models.Car/'.$field.','."'$urlencodeValue'".')';
                        break;

                    //Specific Object classification fields
                    //Art
                      case "CatalogRaisonne":
                      case "SuitePortfolio":
                      case "Form":

                      //Archaeology
                      case "DecorativeMotif":
                      case "FieldSpecimenNumber":
                      case "MakersMark":
                      case "ObjectForm":
                      case "ObjectPart":
                      case "PreviousCatalogNumber":
                      case "RevisedNomenclature":
                      case "SlideNumber":
                      case "TimePeriod":

                      //Archive
                      case "AdditionalAccessionNumber":
                      case "CallNumber":
                      case "CastAndCrew":
                      case "CompletionYear":
                      case "CoverType":
                      case "FilmSize":
                      case "FindingAids":
                      case "History":
                      case "ImageNumber":
                      case "ImageRights":
                      case "ISBN":
                      case "ISSN":
                      case "Language":
                      case "LevelOfControl":
                      case "NegativeNumber":
                      case "NumberOfPages":
                      case "Process":
                      case "ProductionDate":
                      case "PublisherLocation":
                      case "ReleaseDate":
                      case "Synopsis":
                      case "TypeOfBinding":
                      case "VolumeNumber":

                      //Biology
                      case "Aspect":
                      case "AssociatedSpecies":
                      case "QuarterSection":
                      case "Rare":
                      case "ReferenceDatum":
                      case "Section":
                      case "Slope":
                      case "SoilType":
                      case "Stage":
                      case "SubspeciesDescriptiveName":
                      case "SubspeciesForma":
                      case "SubspeciesFormaYear":
                      case "SubspeciesVariety":
                      case "SubspeciesVarietyYear":
                      case "SubspeciesYear":
                      case "ThreatenedEndangeredDate":
                      case "ThreatenedEndangeredSpeciesSynonym":
                      case "ThreatenedEndangeredSpeciesSynonymName":

                      //Ethnology
                      case "AdditionalGroup":
                      case "Alternate1EthnologyCulture":
                      case "Alternate2EthnologyCulture":
                      case "EthnologyCulture":

                      //Geology
                      case "Composition":
                      case "DepositionalEnvironment":
                      case "DescriptiveName":
                      case "GeologicalClassification":
                      case "LithologyPedotype":
                      case "StrunzClass":
                      case "StrunzDivision":
                      case "StrunzID":
                      case "ThinSection":

                      //History
                      case "Copyright":
                      case "PatentDate":
                      case "School":

                      //Paleontology
                      case "Lithology":
                      case "Taphonomy":

                      //Jewelry
                      case "Carats":
                      case "Clarity":
                      case "Cut":
                      case "Karats":
                      case "MetalType":
                      case "Stones":
                      case "TypeOfGemstone":

                      //Car
                      case "Acceleration":
                      case "Battery":
                      case "BrakeFluid":
                      case "ChassisNumber":
                      case "DashLayout":
                      case "DrivenBy":
                      case "EngineNumber":
                      case "EnginePosition":
                      case "EngineType":
                      case "FuelType":
                      case "FuelHighway":
                      case "LicensePlateNumber":
                      case "Mileage":
                      case "OilType":
                      case "Paint":
                      case "Passengers":
                      case "Power":
                      case "RegistrationNotes":
                      case "RepairsMade":
                      case "ShiftPattern":
                      case "StartingInstructions":
                      case "TitleStatusNotes":
                      case "TopSpeed":
                      case "TransmissionFluid":
                      case "VIN":

                      //Wine
                      case "BottleSize":
                      case "FermentationPeriod":
                      case "Grape":
                      case "Maturity":
                      case "Region":
                      case "TypeOfWine":

                      //Clothing
                      case "Brand":
                      case "FabricMaterial":
                      case "SKU":
                          array_push($non_expandable_fields, $field);
                          break;

                    default:
                        array_push($non_expandable_fields, $field);

                        /*start- added this for accent character search */

                        $decodedSearchWord = urldecode($searchWord);
                        $urlencodeValue = urlencode($decodedSearchWord);
                        $srch_temp="contains".'('.$field.','."'$urlencodeValue'".')';

                        /*end- added this for accent character search */
                        break;
                }
                if(!empty($srch_temp)){
                    array_push($filterTermArray, $srch_temp);
                }

            }
            if (count($non_expandable_fields) > 0)
            {
                $dynamicurl.= '&$select=';
                for($i = 0; $i < count($non_expandable_fields); $i++)
                {
                    $cs_non_expandable_field = $non_expandable_fields[$i];
                    switch ($non_expandable_fields[$i])
                    {
                        //Art
                        case "CatalogRaisonne":
                        case "Form":
                        case "ReferenceNotes":
                        case "SuitePortfolio":
                          $non_expandable_fields[$i]= "PublicAPIV2.Models.Art/" .$non_expandable_fields[$i];
                          $srch_temp="contains".'(PublicAPIV2.Models.Art/'.$cs_non_expandable_field.','."'$searchWord'".')';
                          array_push($filterTermArray, $srch_temp);
                        break;

                        //Archaeology
                        case "DecorativeMotif":
                        case "FieldSpecimenNumber":
                        case "MakersMark":
                        case "ObjectForm":
                        case "ObjectPart":
                        case "PreviousCatalogNumber":
                        case "RevisedNomenclature":
                        case "SlideNumber":
                        case "TimePeriod":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Archaeology/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.Archaeology/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                        break;
                        //Archive
                        case "AdditionalAccessionNumber":
                        case "CallNumber":
                        case "CastAndCrew":
                        case "CompletionYear":
                        case "CoverType":
                        case "FilmSize":
                        case "FindingAids":
                        case "History":
                        case "ImageNumber":
                        case "ImageRights":
                        case "ISBN":
                        case "ISSN":
                        case "Language":
                        case "LevelOfControl":
                        case "NegativeNumber":
                        case "NumberOfPages":
                        case "Process":
                        case "ProductionDate":
                        case "PublisherLocation":
                        case "ReleaseDate":
                        case "Synopsis":
                        case "TypeOfBinding":
                        case "VolumeNumber":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Archive/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.Archive/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                        break;

                        //Biology
                        case "Aspect":
                        case "AssociatedSpecies":
                        case "QuarterSection":
                        case "Rare":
                        case "ReferenceDatum":
                        case "Section":
                        case "Slope":
                        case "SoilType":
                        case "Stage":
                        case "SubspeciesDescriptiveName":
                        case "SubspeciesForma":
                        case "SubspeciesFormaYear":
                        case "SubspeciesVariety":
                        case "SubspeciesVarietyYear":
                        case "SubspeciesYear":
                        case "ThreatenedEndangeredDate":
                        case "ThreatenedEndangeredSpeciesSynonym":
                        case "ThreatenedEndangeredSpeciesSynonymName":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Biology/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.Biology/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                        break;
                        //Ethnology
                        case "AdditionalGroup":
                        case "Alternate1EthnologyCulture":
                        case "Alternate2EthnologyCulture":
                        case "EthnologyCulture":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Ethnology/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.Ethnology/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                        break;

                        //Geology
                        case "Composition":
                        case "DepositionalEnvironment":
                        case "DescriptiveName":
                        case "GeologicalClassification":
                        case "LithologyPedotype":
                        case "StrunzClass":
                        case "StrunzDivision":
                        case "StrunzID":
                        case "ThinSection":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Geology/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.Geology/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                        break;

                        //History

                        case "Copyright":
                        case "PatentDate":
                        case "School":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.History/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.History/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                        break;

                        //Paleontology

                        case "Lithology":
                        case "Taphonomy":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Paleontology/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.Paleontology/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                        break;

                        //Jewelry

                        case "Carats":
                        case "Clarity":
                        case "Cut":
                        case "Karats":
                        case "MetalType":
                        case "Stones":
                        case "TypeOfGemstone":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Jewelry/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.Jewelry/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                        break;

                        //Car
                        case "Acceleration":
                        case "Battery":
                        case "BrakeFluid":
                        case "ChassisNumber":
                        case "DashLayout":
                        case "DrivenBy":
                        case "EngineNumber":
                        case "EnginePosition":
                        case "EngineType":
                        case "FuelType":
                        case "FuelHighway":
                        case "LicensePlateNumber":
                        case "Mileage":
                        case "OilType":
                        case "Paint":
                        case "Passengers":
                        case "Power":
                        case "RegistrationNotes":
                        case "RepairsMade":
                        case "ShiftPattern":
                        case "StartingInstructions":
                        case "TitleStatusNotes":
                        case "TopSpeed":
                        case "TransmissionFluid":
                        case "VIN":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Car/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.Car/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                        break;

                        //Wine
                        case "BottleSize":
                        case "FermentationPeriod":
                        case "Grape":
                        case "Maturity":
                        case "Region":
                        case "TypeOfWine":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Wine/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.Wine/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                        break;

                        //Clothing
                        case "Brand":
                        case "FabricMaterial":
                        case "SKU":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Clothing/" .$non_expandable_fields[$i];
                            $srch_temp="contains".'(PublicAPIV2.Models.Clothing/'.$cs_non_expandable_field.','."'$searchWord'".')';
                            array_push($filterTermArray, $srch_temp);
                          break;
                        case "ReleaseDate":
                        case "ProductionDate":
                          $non_expandable_fields[$i] = "PublicAPIV2.Models.Archive/" .$non_expandable_fields[$i];
                          break;


                    }
                }
                foreach ($filterTermArray as $filterfield){
                    if ($filtercount == 0) {
                        $filtertemp = $filterfield;
                        $filtercount++;
                    }else{
                        $filtertemp = $filtertemp.$querymid.$filterfield;
                        $filtercount++;
                    }
                }
                  foreach ($non_expandable_fields as $non_expand_field)
                  $dynamicurl = $dynamicurl.$non_expand_field.',';

            }
            $dynamicurl = $dynamicurl.'ObjectId,';
            $dynamicurl = $dynamicurl.'ModificationDate,';
            $dynamicurl = $dynamicurl.'CreationDate,';

            if(empty($filtertemp)){
                return $dynamicurl;
            }
            else{
                if(empty($searchWord)){
                    return $dynamicurl.'&$filter=SubscriptionId%20eq%20'.$subsId;
                }
                else{
                    $dynamicurl = $dynamicurl.'&$filter=('.$filtertemp.')And(SubscriptionId%20eq%20'.$subsId.')';
                    return $dynamicurl;
                }
            }
  }

  function getCommaSeperatedUniqueFieldsForSearch($field_names_array){
    $values = implode(',', $field_names_array);;
    return $values;

  }

  function implodeChildArrayProperty($additionalArrayObject,$additionalArray,$additionalPropertyId,$additionalProperty) {
    $commaSeperatedItem = "";
    foreach ($additionalArrayObject as $additionalItem) {
      $commaSeperatedItem != "" && $commaSeperatedItem .= ", ";
      $commaSeperatedItem .= '<a href="/artist-detail?dataId='.$additionalItem[$additionalArray][$additionalPropertyId].'">'.$additionalItem[$additionalArray][$additionalProperty].'</a>';
    }
    return $commaSeperatedItem;
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

  public function remove_unrequired_Exhibitions_from_Database($exhibitionIds_API){
    $database = Database::getConnection();
    $table_name = 'Exhibitions';


    // Get all exhibitionIds from the database
    $dbexhibitionIds = $database->select($table_name, 't')
        ->fields('t', ['ExhibitionId'])
        ->execute()
        ->fetchCol();

    // Find exhibitionIds in the database that are not in the API response
    $unrequiredexhibitionIds = array_diff($dbexhibitionIds, $exhibitionIds_API);

    if (!empty($unrequiredexhibitionIds)) {
        // Remove rows with unrequired ExhibitionIds from the database
        $database->delete($table_name)
            ->condition('ExhibitionId', $unrequiredexhibitionIds, 'IN')
            ->execute();
    }
  }

  public function remove_unrequired_Groups_from_Database($groupIds_API){
    $database = Database::getConnection();
    $table_name = 'Groups';


    // Get all GroupIds from the database
    $dbGroupIds = $database->select($table_name, 't')
        ->fields('t', ['GroupId'])
        ->execute()
        ->fetchCol();

    // Find GroupIds in the database that are not in the API response
    $unrequiredGroupIds = array_diff($dbGroupIds, $groupIds_API);

    if (!empty($unrequiredGroupIds)) {
        // Remove rows with unrequired GroupIds from the database
        $database->delete($table_name)
            ->condition('GroupId', $unrequiredGroupIds, 'IN')
            ->execute();
    }
  }

  public function remove_unrequired_Collections_from_Database($collectionIds_API){
    $database = Database::getConnection();
    $table_name = 'Collections';


    // Get all CollectionIds from the database
    $dbCollectionIds = $database->select($table_name, 't')
        ->fields('t', ['CollectionId'])
        ->execute()
        ->fetchCol();

    // Find CollectionIds in the database that are not in the API response
    $unrequiredCollectionIds = array_diff($dbCollectionIds, $collectionIds_API);

    if (!empty($unrequiredCollectionIds)) {
        // Remove rows with unrequired CollectionIds from the database
        $database->delete($table_name)
            ->condition('CollectionId', $unrequiredCollectionIds, 'IN')
            ->execute();
    }
  }

  public function remove_unrequired_Artists_from_Database($artistIds_API){
    $database = Database::getConnection();
    $table_name = 'Artists';


    // Get all ArtistIds from the database
    $dbArtistIds = $database->select($table_name, 't')
        ->fields('t', ['ArtistId'])
        ->execute()
        ->fetchCol();

    // Find ArtistIds in the database that are not in the API response
    $unrequiredArtistIds = array_diff($dbArtistIds, $artistIds_API);

    if (!empty($unrequiredArtistIds)) {
        // Remove rows with unrequired ArtistIds from the database
        $database->delete($table_name)
            ->condition('ArtistId', $unrequiredArtistIds, 'IN')
            ->execute();
    }
  }

  public function remove_unrequired_Objects_from_Database($objectIds_API){
    $database = Database::getConnection();
    $table_name = 'CSObjects';


    // Get all ObjectIds from the database
    $dbObjectIds = $database->select($table_name, 't')
        ->fields('t', ['ObjectId'])
        ->execute()
        ->fetchCol();

    // Find ObjectIds in the database that are not in the API response
    $unrequiredObjectIds = array_diff($dbObjectIds, $objectIds_API);

    if (!empty($unrequiredObjectIds)) {
        // Remove rows with unrequired ObjectIds from the database
        $database->delete($table_name)
            ->condition('ObjectId', $unrequiredObjectIds, 'IN')
            ->execute();
    }
  }
}
