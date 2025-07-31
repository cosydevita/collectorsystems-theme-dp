<?php
namespace Drupal\collector_systems;
use Drupal\collector_systems\Csconstants;

class CollectorSystemsGetApiData{

  protected $subsKey;
  protected $subAcntId;
  protected $subsId;

  public function __construct(){
    $config = \Drupal::config('collector_systems.settings');
    $this->subsKey = $config->get('subscription_key');
    $this->subAcntId = $config->get('account_guid');
    $this->subsId = $config->get('subscription_id');
  }

  public function getApiTotalObjectsCount(){

    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;


    $url = Csconstants::Public_API_URL.$subAcntId.'/'.'Objects?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'%20And%20Deleted%20eq%20false&$select=ObjectId';

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

    $Detaildata = json_decode($Detaildata, TRUE);
    $count = $Detaildata['@odata.count'];

    return $count;
  }

  public function getApiObjectImagesData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

    //Fetch Object Images
    //expanded to include the attachmentkeywords
    $url =csconstants::Public_API_URL.$subAcntId.'/Objects?$expand=MainImageAttachment($select=AttachmentId,Description,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL,SlideShowURL),ObjectImageAttachments($expand=Attachment($select=AttachmentId,SubscriptionId,FileName,Description,ContentType,CreationDate,FileURL,ThumbSizeURL,MidSizeURL,DetailURL,DetailLargeURL,DetailXLargeURL,iphoneURL,SlideShowURL;$expand=AttachmentKeywords($select=AttachmentKeywordString))),&$select=InventoryNumber,Title,InventoryNumber,ObjectId,MainImageAttachmentId,ModificationDate,CreationDate&$filter=SubscriptionId%20eq%20'.$subsId.'%20And%20Deleted%20eq%20false&$top='.$top.'&$skip='. $skip;

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


    return $Detaildata;


  }

  public function getTotalArtistsCount(){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;


    $url = Csconstants::Public_API_URL.$subAcntId.'/'.'Artists?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'%20&$select=ArtistId';

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

    $Detaildata = json_decode($Detaildata, TRUE);
    $count = $Detaildata['@odata.count'];

    return $count;

  }

  public function getApiArtistsImagesData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

     //Fetch Artist Images
    $url = csconstants::Public_API_URL.$subAcntId . '/Artists?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=ArtistPhotoAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)&$top='.$top.'&$skip='.$skip;

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

    return $ArtistPhoto;
  }

  public function getTotalCollectionsCount(){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;


    $url = Csconstants::Public_API_URL.$subAcntId.'/'.'Collections?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'%20&$select=CollectionId';

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

    $Detaildata = json_decode($Detaildata, TRUE);
    $count = $Detaildata['@odata.count'];

    return $count;

  }


  public function getApiCollectionsImagesData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

     //Fetch Collection Images
    //  global $wpdb , $subAcntId , $subsId , $subsKey;
    $url = csconstants::Public_API_URL.$subAcntId . '/Collections?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=CollectionImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)&$top='.$top.'&$skip='.$skip;
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

    return $CollectionPhoto;
  }


  public function getTotalGroupsCount(){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;


    $url = Csconstants::Public_API_URL.$subAcntId.'/'.'Groups?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'%20&$select=GroupId';

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

    $Detaildata = json_decode($Detaildata, TRUE);
    $count = $Detaildata['@odata.count'];

    return $count;

  }

  public function getApiGroupsImagesData($top, $skip){

    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

    //Fetch Groups Images
    //  global $wpdb , $subAcntId , $subsId , $subsKey;
    $url = csconstants::Public_API_URL. $subAcntId . '/Groups?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=GroupImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)&$top='.$top.'&$skip='.$skip;
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

    return $GroupImages;
  }

  public function getTotalExhibitionsCount(){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;


    $url = Csconstants::Public_API_URL.$subAcntId.'/'.'Exhibitions?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'%20&$select=ExhibitionId';

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

    $Detaildata = json_decode($Detaildata, TRUE);
    $count = $Detaildata['@odata.count'];

    return $count;

  }

  public function getApiExhibitionsImagesData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

    //Fetch Exhibitions Images
    //  global $wpdb , $subAcntId , $subsId , $subsKey;
    $url = csconstants::Public_API_URL.$subAcntId . '/Exhibitions?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=ExhibitionImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL)&$top='.$top.'&$skip='.$skip;
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

    return $ExhibitionPhoto;

  }

  public function getTotalGroupsObjectsCount(){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;


    $url = Csconstants::Public_API_URL.$subAcntId.'/'.'GroupObjects?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'%20&$select=GroupId';

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

    $Detaildata = json_decode($Detaildata, TRUE);
    $count = $Detaildata['@odata.count'];

    return $count;

  }

  public function getApiGroupsObjectsImagesData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

    //Fetch GroupObjects
    $url=csconstants::Public_API_URL.$subAcntId.'/GroupObjects?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=group,object($expand=MainImageattachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL))&$top='.$top.'&$skip='.$skip;
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

    return $GroupObjects;
  }

  public function getTotalExhibitionsObjectsCount(){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;


    $url = Csconstants::Public_API_URL.$subAcntId.'/'.'ExhibitionObjects?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'%20&$select=ExhibitionId';

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

    $Detaildata = json_decode($Detaildata, TRUE);
    $count = $Detaildata['@odata.count'];

    return $count;

  }

  public function getApiExhibitionsObjectsImagesData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

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

    return $ExhibitionObjects;

  }

  public function getApiArtistsData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

     //Fetch Artists Data
    $url = csconstants::Public_API_URL.$subAcntId . '/Artists?$filter=SubscriptionId%20eq%20' . $subsId. '&$top='.$top.'&$skip='.$skip;

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

    return $data;
  }

  public function getApiObjectsData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

    $field_names = $field_names_array = $this->get_field_names(); //temp test

    $wordforsearch = "Objects";
      if ($field_names != null && $field_names != "") {
        $customized_fields = $this->getCommaSeperatedUniqueFieldsForSearch($field_names);
        $baseurl = csconstants::Public_API_URL.$subAcntId . '/Objects?$expand=MainImageAttachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL),Address($select=AddressId,AddressName,Latitude,Longitude),';
        $qSearch= '';
        $apiCallFor = '';

        $url = $this->getDynamicUrlForEndpoint($field_names_array,$baseurl,$qSearch,$apiCallFor);
        $url.= '&$top='.$top.'&$skip='.$skip;

      }
      else{
    	$url = csconstants::Public_API_URL . $subAcntId . '/' . $wordforsearch . '?$filter=SubscriptionId%20eq%20' . $subsId. '?$top='.$top.'&$skip='.$skip;
      }

      // \Drupal::logger('collector_systems')->debug('Sync Objects API: %url', ['%url' => $url]);

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

    return $data1;
  }

  public function get_field_names(){
    $source_table_name = 'collector_systems_clsobjects_fields';

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

  public function getCommaSeperatedUniqueFieldsForSearch($field_names_array){
    $values = implode(',', $field_names_array);;
    return $values;

  }

  public function getDynamicUrlForEndpoint($objectFields_arr,$dynamicurl,$searchWord,$apiCallFor="")
  {
      // Get the api config settings
     $config = \Drupal::config('collector_systems.settings');
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
                    case "AddressName":
                      $dynamicurl.= 'Address($select=AddressId,AddressName,Latitude,Longitude),';
                      break;
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

  public function getApiCollectionsData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

    //Fetching Collection's API Data
    $wordforsearch = "Collections";
    $url = csconstants::Public_API_URL . $subAcntId . '/' . $wordforsearch. '?$filter=SubscriptionId%20eq%20' . $subsId . '&$top='.$top.'&$skip='.$skip;

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

      return $data2;

  }

  public function getApiGroupsData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

    //Fetching Group's API Data
    $wordforsearch = "Groups";
    $url = csconstants::Public_API_URL . $subAcntId . '/' . $wordforsearch. '?$filter=SubscriptionId%20eq%20' . $subsId . '&$top='.$top.'&$skip='.$skip;

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

    return $data3;
  }

  public function getApiExhibitionsData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;


    //Fetching Exhibition's API Data
    $wordforsearch = "Exhibitions";
    $url = csconstants::Public_API_URL . $subAcntId . '/' . $wordforsearch. '?$filter=SubscriptionId%20eq%20' . $subsId . '&$top='.$top.'&$skip='.$skip;

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
    return $data4;
  }

  public function getApiGroupsObjectsData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;


   //Fetch GroupObjects
   $url=csconstants::Public_API_URL.$subAcntId.'/GroupObjects?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=group,object($expand=MainImageattachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL))'.'&$top='.$top.'&$skip='.$skip;
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

   return $GroupObjects;
  }

  public function getApiExhibitionsObjectsData($top, $skip){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;


    //Fetch ExhibitionObjects
    $url=csconstants::Public_API_URL.$subAcntId.'/ExhibitionObjects?$filter=SubscriptionId%20eq%20' . $subsId. '&$expand=exhibition,object($expand=Mainimageattachment($select=AttachmentId,SubscriptionId,FileName,DetailLargeURL,DetailXLargeURL))'.'&$top='.$top.'&$skip='.$skip;
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

    return $ExhibitionObjects;
  }

  /**
   * Fetches the count of items with images for a specific entity type from the public API.
   *
   * Supported $image_type values:
   *  - 'objects_images'     : Counts artists with non-null AttachmentId.
   *  - 'artists_images'     : Counts artists with non-null ArtistPhotoAttachmentId.
   *  - 'collections_images' : Counts collections with non-null CollectionImageAttachmentId.
   *  - 'exhibitions_images' : Counts exhibitions with non-null ExhibitionImageAttachmentId.
   *  - 'groups_images'      : Counts groups with non-null GroupImageAttachmentId.
   *
   * @param string $image_type The type of image to count (e.g. 'artists_images').
   * @return int|null The count of entities with images, or null on failure.
   */
  public function getApiImageTypeCount($image_type){
    $subsKey = $this->subsKey;
    $subAcntId = $this->subAcntId;
    $subsId = $this->subsId;

    $url = '';

    if($image_type == 'objects_images'){
        $wordforsearch="ObjectImageAttachments";
        $url = csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'&$select=AttachmentId,ObjectId';
    }
    else if($image_type == 'artists_images'){
        $wordforsearch="Artists";
        $url =  csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'and ArtistPhotoAttachmentId ne null&$select=ArtistId';
    } else if($image_type == 'collections_images'){
        $wordforsearch="Collections";
        $url =  csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'and CollectionImageAttachmentId ne null&$select=CollectionId';
    } else if($image_type == 'exhibitions_images'){
        $wordforsearch="Exhibitions";
        $url =  csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'and ExhibitionImageAttachmentId ne null&$select=ExhibitionId';
    }else if($image_type == 'groups_images'){
        $wordforsearch="Groups";
        $url =  csconstants::Public_API_URL.$subAcntId.'/'.$wordforsearch.'?$count=true&$filter=SubscriptionId%20eq%20'.$subsId.'and GroupImageAttachmentId ne null&$select=GroupId';
    }
    

    if($url){
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
    
        $Detaildata = json_decode($Detaildata, TRUE);
        $count = $Detaildata['@odata.count'];
    
        return $count;
    } else {
        return null;
    }
  }
}
