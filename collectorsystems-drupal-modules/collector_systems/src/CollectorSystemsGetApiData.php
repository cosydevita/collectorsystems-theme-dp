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

  public function getTotalColletionsCount(){
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




}
