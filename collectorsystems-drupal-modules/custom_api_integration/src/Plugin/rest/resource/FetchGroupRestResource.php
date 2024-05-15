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
 *   id = "cs_fetch_group_ajax",
 *   label = @Translation("Collector Systems API to get total count of each group or content types"),
 *   uri_paths = {
 *     "canonical" = "/v1/cs-fetch-group-ajax",
 *     "create" = "/v1/cs-fetch-group-ajax"
 *   }
 * )
 */
class FetchGroupRestResource extends ResourceBase {


   /**
   * Responds to POST requests.
   *
   * Creates a user account.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    $apiCountForGroup = $this->GetApiGroupCount();
    $DbCountForGroup = $this-> GetDbGroupCount(  );
    $DbCountForObject  = $this->GetDbObjectCount();
    $apiCountForObject = $this->GetApiObjectCount( );
    $apiCountForArtist = $this->GetApiArtistCount();
    $DbCountForArtist= $this->GetDbArtistCount( );
    $DbcollectionCount = $this-> GetDbcollectionCount( );
    $ApicollectionCount = $this-> GetApicollectionCount( );

    $DbExhibitionsCount = $this-> GetDbExhibitionsCount( );
    $ApiExhibitionsCount = $this-> GetApiExhibitionsCount( );

    // $this->save_image_directory();
    $response = [
      'DbCountForGroup' => $DbCountForGroup,
      'apiCountForGroup' => $apiCountForGroup,
      'DbCountForObject' => $DbCountForObject,
      'apiCountForObject' => $apiCountForObject,
      'DbCountForArtist' => $DbCountForArtist,
      'apiCountForArtist' => $apiCountForArtist,
      'DbcollectionCount' => $DbcollectionCount,
      'ApicollectionCount' => $ApicollectionCount,
      'DbExhibitionsCount' => $DbExhibitionsCount,
      'ApiExhibitionsCount' => $ApiExhibitionsCount,

    ];

    return new ResourceResponse($response);
  }



  //for goup
  function GetApiGroupCount()
  {
    $config = \Drupal::config('custom_api_integration.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');

    $wordforsearch="Groups";
    $url = csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=GroupId';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
    "Accept: application/json",
    "Ocp-Apim-Subscription-Key:$subsKey ",
    "Cache-Control:no-cache",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $data = curl_exec($curl);
    curl_close($curl);

    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if($httpcode == 403)
    {
        exit();
    }

    $data = json_decode($data, TRUE);
    return $data['@odata.count'];
  }
  function GetDbGroupCount(){
    try {

      $database = Database::getConnection();
      $table_name = 'Groups';
      $query = $database->select($table_name, 'g');
      $count = $query->countQuery()->execute()->fetchField();
      return $count;
    }catch (\Exception $e) {
      \Drupal::logger('custom_api_integration')->error('Database query error: @message', ['@message' => $e->getMessage()]);
      // If an exception is thrown (e.g., table not found), return 0.
      return 0;
    }

  }



  //for object
  function GetApiObjectCount()
  {

    $config = \Drupal::config('custom_api_integration.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');

      $wordforsearch="Objects";
      $url = csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=ObjectId';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $headers = array(
      "Accept: application/json",
      "Ocp-Apim-Subscription-Key:$subsKey ",
      "Cache-Control:no-cache",
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      //for debug only!
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

      $data = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          exit();
      }

    $data = json_decode($data, TRUE);
    return $data['@odata.count'];
  }

  function GetDbObjectCount(){
    try{
      $database = \Drupal::database();

      $table_name = 'CSObjects';
      $query = $database->select($table_name, 'c');
      $count2 = $query->countQuery()->execute()->fetchField();

      return $count2;

    }catch (\Exception $e) {
      \Drupal::logger('custom_api_integration')->error('Database query error: @message', ['@message' => $e->getMessage()]);
      // If an exception is thrown (e.g., table not found), return 0.
      return 0;
    }

  }


  //for Artist
  function GetApiArtistCount()
  {
      $config = \Drupal::config('custom_api_integration.settings');
      $subsKey = $config->get('subscription_key');
      $subAcntId = $config->get('account_guid');
      $subsId = $config->get('subscription_id');

      $wordforsearch="Artists";
      $url = csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=ArtistId';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $headers = array(
      "Accept: application/json",
      "Ocp-Apim-Subscription-Key:$subsKey ",
      "Cache-Control:no-cache",
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      //for debug only!
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

      $data = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          // get_template_part( 403 );
          exit();
      }

    $data = json_decode($data, TRUE);
    return $data['@odata.count'];
  }

  function GetDbArtistCount(){
    try{
      $database = \Drupal::database();

      $table_name = 'Artists';
      $query = $database->select($table_name, 'c');
      $count2 = $query->countQuery()->execute()->fetchField();

      return $count2;
    }catch (\Exception $e) {
      \Drupal::logger('custom_api_integration')->error('Database query error: @message', ['@message' => $e->getMessage()]);
      // If an exception is thrown (e.g., table not found), return 0.
      return 0;
    }
  }

  //for collection
  function GetApicollectionCount()
  {
      $config = \Drupal::config('custom_api_integration.settings');
      $subsKey = $config->get('subscription_key');
      $subAcntId = $config->get('account_guid');
      $subsId = $config->get('subscription_id');

      $wordforsearch="Collections";
      $url = csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=CollectionId';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $headers = array(
      "Accept: application/json",
      "Ocp-Apim-Subscription-Key:$subsKey ",
      "Cache-Control:no-cache",
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      //for debug only!
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

      $data = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          exit();
      }

    $data = json_decode($data, TRUE);
    return $data['@odata.count'];
  }

  function GetDbcollectionCount(){
    try{
      $table_name = 'Collections';
      $database = \Drupal::database();
      $query = $database->select($table_name, 'c');
      $count2 = $query->countQuery()->execute()->fetchField();
      return $count2;
    }catch (\Exception $e) {
      \Drupal::logger('custom_api_integration')->error('Database query error: @message', ['@message' => $e->getMessage()]);
      // If an exception is thrown (e.g., table not found), return 0.
      return 0;
    }
  }
  //for collection
  function GetApiExhibitionsCount()
  {
      $config = \Drupal::config('custom_api_integration.settings');
      $subsKey = $config->get('subscription_key');
      $subAcntId = $config->get('account_guid');
      $subsId = $config->get('subscription_id');

      $wordforsearch="Exhibitions";
      $url = csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=ExhibitionId';
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $headers = array(
      "Accept: application/json",
      "Ocp-Apim-Subscription-Key:$subsKey ",
      "Cache-Control:no-cache",
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      //for debug only!
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

      $data = curl_exec($curl);
      curl_close($curl);

      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode == 403)
      {
          exit();
      }

    $data = json_decode($data, TRUE);
    return $data['@odata.count'];
  }

  function GetDbExhibitionsCount(){
    try{
      $database = \Drupal::database();

      $table_name =  'Exhibitions'; // Use $wpdb->prefix to get the table prefix defined by WordPress

        $query = $database->select($table_name, 'c');
        $count2 = $query->countQuery()->execute()->fetchField();
        return $count2;
    }catch (\Exception $e) {
      \Drupal::logger('custom_api_integration')->error('Database query error: @message', ['@message' => $e->getMessage()]);
      // If an exception is thrown (e.g., table not found), return 0.
      return 0;
    }
  }

}
