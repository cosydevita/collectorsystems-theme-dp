<?php

namespace Drupal\custom_api_integration\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Database\Connection;
use Drupal\custom_api_integration\Csconstants;
use Drupal\Core\Database\Database;

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
    $this->custom_api_integration_sync_api_data();
    $response = [
      'messgae' => 'All tables created successfully test.'
    ];

    return new ResourceResponse($response);
  }

   /**
   * Sync the api data to the database
   */
  function custom_api_integration_sync_api_data() {

    //drop tables
    $this->custom_api_integration_drop_tables();

    // create tables
    $this->custom_api_integration_create_tables();

    $this->sync_api_data();


  }

  function sync_api_data(){
    $this->sync_api_data_Artists();
    $this->sync_api_data_Objects();
    $this->sync_api_data_Collections();
    $this->sync_api_data_Exhibitions();
    $this->sync_api_data_Groups();
    $this->sync_api_data_ExhibitionObjects();
    $this->sync_api_data_GroupObjects();

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

  function sync_api_data_Groups(){
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
      foreach ($GroupImages['value'] as $group)
      {
          $groupId = $group['GroupId'];
          $groupDescription = $group['GroupDescription'];
          $groupMemo = NULL;
          if(isset($group['GroupMemo']) && $group['GroupMemo'] !== NULL)
          {
              $groupMemo = $group['GroupMemo'];
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
              );

              // Insert the data into the table.
              \Drupal::database()->insert($table_name3)
                ->fields($data)
                ->execute();
          }

      }//End Groups


  }

  function sync_api_data_Exhibitions(){
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

        //Start Exhibitions
        foreach ($ExhibitionPhoto['value'] as $exhibition)
        {
            $exhibitionId = $exhibition['ExhibitionId'];
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
                );
               $database->insert($table_name4)
              ->fields($data)
              ->execute();
            }
        } //End Exhibitions

  }
  function sync_api_data_Collections(){
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

      //Start Collections
      foreach ($CollectionPhoto['value'] as $collection)
      {
          $collectionId = $collection['CollectionId'];
          $collectionName = $collection['CollectionName'];
          $collectionFullName = $collection['FullCollectionName'];

          if ($collectionId !== 0 && $collectionName !== null && $collectionFullName !== null)
          {
              // $sql2 = $wpdb->prepare("INSERT INTO $table_name2 (CollectionId , CollectionName , FullCollectionName) VALUES (%d , %s , %s)", $collectionId ,  $collectionName , $collectionFullName);
              // $wpdb->query($sql2);
              // Define the data to be inserted.
              $data = [
                'CollectionId' => $collectionId,
                'CollectionName' => $collectionName,
                'FullCollectionName' => $collectionFullName,
              ];

              // Insert data into the table using the database API.
              $database->insert($table_name2)
                ->fields($data)
                ->execute();
          }

      }//End Collections

  }
  function sync_api_data_Objects(){

    $field_names = ['ArtistName', 'InventoryNumber', 'ArtistCompany']; //temp test
     // Get the api config settings
     $config = \Drupal::config('custom_api_integration.settings');
     $subsKey = $config->get('subscription_key');
     $subAcntId = $config->get('account_guid');
     $subsId = $config->get('subscription_id');


     $database = Database::getConnection();
     $table_name = 'CSObjects';

      // Fetching Object's API Data
    	$wordforsearch = "Objects";
    	$url = csconstants::Public_API_URL . $subAcntId . '/' . $wordforsearch . '?$filter=SubscriptionId%20eq%20' . $subsId;

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

      //Start Objects
      foreach ($data1['value'] as $object)
      {
          $combinedObjectValues = [];
          foreach ($field_names as $value1)
          {
              switch($value1)
              {
                  case isset($object[$value1]):
                      // $combinedObjectValues[] = esc_sql($object[$value1]);
                      $combinedObjectValues[] = \Drupal::database()->query ("SELECT :value", [':value' => $object[$value1]])->fetchField();
                      break;

                  default:
                      $combinedObjectValues[] = '';
                      break;
              }
          }
          $id1 = $object['ObjectId'];
          $imgId1 = NULL;
          if(isset($object['MainImageAttachmentId']) && $object['MainImageAttachmentId'] !== NULL)
          {
              $imgId1 = $object['MainImageAttachmentId'];
          }
          $artistId = 0;
          if(isset($object['ArtistId']) && $object['ArtistId'] !== NULL)
          {
              $artistId = $object['ArtistId'];
          }
          $title = NULL;
          if(isset($object['Title']) && $object['Title'] !== NULL)
          {
              $title = $object['Title'];
          }
          $inventNumber = $object['InventoryNumber'];
          $objectDate = NULL;
          if(isset($object['ObjectDate']) && $object['ObjectDate'] !== NULL)
          {
              $objectDate = $object['ObjectDate'];
          }

          $collectionId = 0;
          if(isset($object['CollectionId']) && $object['CollectionId'] !== NULL)
          {
              $collectionId = $object['CollectionId'];
          }

        // Define the fields for the insert query
        $fields = array('ObjectId', 'Title', 'InventoryNumber', 'ObjectDate', 'MainImageAttachmentId', 'ArtistId', 'CollectionId');

        // Create an associative array with field-value pairs
        $values = array(
          'ObjectId' => $id1,
          'Title' => $title,
          'InventoryNumber' => $inventNumber,
          'ObjectDate' => $objectDate,
          'MainImageAttachmentId' => $imgId1,
          'ArtistId' => $artistId,
          'CollectionId' => $collectionId,
        );

        // If $combinedObjectValues is not empty, add its values to the $values array
        if (!empty($combinedObjectValues)) {
          $combinedObjectValues = array_combine($field_names, $combinedObjectValues);
          $values = array_merge($values, $combinedObjectValues);
        }

        // Perform the database insert
        $database->insert($table_name)
          ->fields($values)
          ->execute();
      }//End Objects




  }
  function sync_api_data_Artists(){

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


      //Start Artists
      foreach ($ArtistPhoto['value'] as $art)
      {

          $artistId = $art['ArtistId'];
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
              ];

              // Insert data into the table.
              $result = $database->insert($table_name)
                ->fields($data)
                ->execute();
          }

      } //End Artists
  }

  /**
   * Helper function to drop the tables.
   */
  function custom_api_integration_drop_tables() {
    $tables = [
      'CSObjects',
      'Artists',
      'Collections',
      'Groups',
      'Exhibitions',
      'ExhibitionObjects',
      'GroupObjects',
      'ThumbImages'
    ];
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
  function custom_api_integration_create_tables() {
    $this->create_table_CSObjects();
    $this->create_table_Artists();
    $this->create_table_Collections();
    $this->create_table_Groups();
    $this->create_table_Exhibitions();
    $this->create_table_ExhibitionObjects();
    $this->create_table_GroupObjects();
    $this->create_table_ThumbImages();

  }

  function create_table_CSObjects(){
    // Create the new table
    $table_name = 'CSObjects';
    // $selected_fields = ['field1', 'field2', 'field3'];
    $selected_fields = ['ArtistName', 'InventoryNumber', 'ArtistCompany']; //temp test


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
      ],
      'primary key' => ['ObjectId'],
    ];

    if($selected_fields){
      // Add dynamic fields if available
      foreach ($selected_fields as $field) {
        $schema['fields'][$field] = [
          'type' => 'varchar',
          'length' => 500,
        ];
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
