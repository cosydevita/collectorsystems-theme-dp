<?php

namespace Drupal\custom_api_integration\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;

class ApiIntegrationController extends ControllerBase
{
    protected $configFactory;

    public function __construct(ConfigFactoryInterface $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('config.factory')
        );
    }

    public function getArtFromAPI()
    {
        $config = $this->configFactory->get('custom_api_integration.settings');
        $subscriptionKey = $config->get('subscription_key');
        $accountGuid = $config->get('account_guid');

        $endpoints = [
          'artists' => 'ArtistPhotoAttachment',
          'objects' => 'MainImageAttachment',
          'collections' => 'CollectionImageAttachment',
          'exhibitions' => 'ExhibitionImageAttachment',
          'groups' => 'GroupImageAttachment',
          'art' => null
        ];

        $apiData = [];
        $apiImg = [];

        $client = new Client(); // Assuming you've imported the Guzzle HTTP Client namespace.

        foreach ($endpoints as $endpoint => $imgEndpoint) {
            $apiUrl = "https://apis.collectorsystems.com/public/v2/{$accountGuid}/$endpoint";
            $response = $client->get($apiUrl, [
              'headers' => [
                'Ocp-Apim-Subscription-Key' => $subscriptionKey,
              ],
            ]);

            $data = json_decode($response->getBody(), true);
            $apiData[$endpoint] = $data;

            if ($imgEndpoint) {
                $imgUrl = "https://apis.collectorsystems.com/public/v2/{$accountGuid}/$endpoint?\$expand=$imgEndpoint";

                $res = $client->get($imgUrl, [
                    'headers' => [
                        'Ocp-Apim-Subscription-Key' => $subscriptionKey,
                    ],
                ]);
                $dataImg = json_decode($res->getBody(), true);
                $apiImg[$endpoint][$imgEndpoint] = $dataImg;
            }
        }

        $result = [
            'endpoints' => array_keys($endpoints),
            'data' => $apiData,
            'imgEndpoints' => array_values($endpoints),
            'imgData' => $apiImg,
        ];


        // // Fetching Exhibitions data
        $objects = $result['data']['objects']['value'];
        $objectsImg = $result['imgData']['objects']['MainImageAttachment']['value'];

        $counter = 1;

        //article is machine name of Article content type.
        $content_type = 'objects';
        $ids = \Drupal::entityQuery('node')->accessCheck(TRUE)
            ->condition('type', $content_type)
            ->execute();

        //Method 1: To delete multiple nodes by looping node entities.
        $nodes = Node::loadMultiple($ids);

        foreach($nodes as $node) {
            $node->delete();
        }

        foreach ($objects as $key => $object) {
            if (isset($objectsImg[$key])) {
                $objectImg = $objectsImg[$key];

                $modificatedate = $object['ModificationDate'];
                $updateDate = new DrupalDateTime($modificatedate);
                $updateModificateDate = $updateDate->format('Y-m-d H:i:s');

                $creationdate = $object['CreationDate'];
                $updateDate = new DrupalDateTime($creationdate);
                $updateCreationDate = $updateDate->format('Y-m-d H:i:s');

                $Inverstdate = $object['InventoryDate'];
                $updateDate = new DrupalDateTime($Inverstdate);
                $updateInverstDate = $updateDate->format('Y-m-d H:i:s');

                $title = !empty($object['Title']) ? $object['Title'] : 'Object ' . $counter;
                $counter++;

                $node = Node::create([

                 'type' => 'objects',
                 'title' =>  $title,
                 'field_objectid' => $object['ObjectId'],
                 'field_inventorynumber' => $object['InventoryNumber'],
                 'field_uniqueid' => $object['UniqueId'],
                 'field_creditline' => $object['CreditLine'],
                 'field_maindimensionid' => $object['MainDimensionId'],
                 'field_collectionid' => $object['CollectionId'],
                 'field_userdefined2' => $object['UserDefined2'],
                 'field_objectdescription' => $object['ObjectDescription'],
                 'field_deleted' => $object['Deleted'],
                 'field_mainimageattachmentid' => $object['MainImageAttachmentId'],
                 'field_ispublicapi' => $object['IsPublicAPI'],
                 'field_locationid' => $object['LocationId'],
                 'field_inventorydate' => $updateInverstDate,
                 'field_inventorycontactid' => $object['InventoryContactId'],
                 'field_inventorymemo' => $object['InventoryMemo'],
                 'field_permanentlocationid' => $object['PermanentLocationId'],
                 'field_subscriptionid' => $object['SubscriptionId'],
                 'field_userdefined3' => $object['UserDefined3'],
                 'field_objectstatuscode' => $object['ObjectStatusCode'],
                 'field_userdefined5' => $object['UserDefined5'],
                 'field_userdefined6' => $object['UserDefined6'],
                 'field_userdefined7' => $object['UserDefined7'],
                 'field_userdefined8' => $object['UserDefined8'],
                 'field_userdefined9' => $object['UserDefined9'],
                 'field_userdefined14' => $object['UserDefined14'],
                 'field_userdefined38' => $object['UserDefined38'],
                 'field_userdefined39' => $object['UserDefined39'],
                 'field_userdefined40' => $object['UserDefined40'],
                 'field_objectstatus' => $object['ObjectStatus'],
                 'field_userdefinedrichtext1' => $object['UserDefinedRichText1'],
                 'field_userdefinedrichtext2' => $object['UserDefinedRichText2'],
                 'field_userdefinedrichtext3' => $object['UserDefinedRichText3'],
                 'field_userdefinedrichtext12' => $object['UserDefinedRichText12'],
                 'field_userdefineddate1' => $object['UserDefinedDate1'],
                 'field_userdefineddate2' => $object['UserDefinedDate2'],
                 'field_userdefinedcurrency1' => $object['UserDefinedCurrency1'],
                 'field_userdefinedcurrency2' => $object['UserDefinedCurrency2'],
                 'field_userdefineddomesticcurrenc' => $object['UserDefinedDomesticCurrency1'],
                 'field_userdefineddomesticcurren2' => $object['UserDefinedDomesticCurrency2'],
                 'field_objectcurrencytypeid' => $object['ObjectCurrencyTypeId'],
                 'field_userdefinednumber1' => $object['UserDefinedNumber1'],
                 'field_userdefinednumber2' => $object['UserDefinedNumber2'],
                 'field_objectconversionrate' => $object['ObjectConversionRate'],
                 'field_objectclassificationcode' => $object['ObjectClassificationCode'],
                 'field_objectclassification' => $object['ObjectClassification'],
                 'field_signatures' => $object['Signatures'],
                 'field_provenance' => $object['Provenance'],
                 'field_term' => $object['Term'],
                 'field_artistid' => $object['ArtistId'],
                 'field_currentvaluationid' => $object['CurrentValuationId'],
                 'field_currentinsurancepolicyid' => $object['CurrentInsurancePolicyId'],
                 'field_currentconditionid' => $object['CurrentConditionId'],
                 'field_domesticexpensetotal' => $object['DomesticExpenseTotal'],
                 'field_rfidtagnumber' => $object['RFIDTagNumber'],
                 'field_objecttypeid' => $object['ObjectTypeId'],
                 'field_othernumbertypeid' => $object['OtherNumberTypeId'],
                 'field_responsibledepobjectmentid' => $object['ResponsibleDepobjectmentId'],
                 'field_completenessid' => $object['CompletenessId'],
                 'field_completenessdate' => $object['CompletenessDate'],
                 'field_completenessnote' => $object['CompletenessNote'],
                 'field_movementreferencenumber' => $object['MovementReferenceNumber'],
                 'field_movementauthorizerid' => $object['MovementAuthorizerId'],
                 'field_movementauthorizationdate' => $object['MovementAuthorizationDate'],
                 'field_movementcontactid' => $object['MovementContactId'],
                 'field_movementmethodid' => $object['MovementMethodId'],
                 'field_movementmemo' => $object['MovementMemo'],
                 'field_movementreasonid' => $object['MovementReasonId'],
                 'field_plannedremoval' => $object['PlannedRemoval'],
                 'field_locationreferencenamenumbe' => $object['LocationReferenceNameNumber'],
                 'field_locationtypeid' => $object['LocationTypeId'],
                 'field_locationaccessmemo' => $object['LocationAccessMemo'],
                 'field_locationconditionmemo' => $object['LocationConditionMemo'],
                 'field_locationconditiondate' => $object['LocationConditionDate'],
                 'field_locationsecuritymemo' => $object['LocationSecurityMemo'],
                 'field_objectdate' => $object['ObjectDate'],
                 'field_countryorigin' => $object['CountryOrigin'],
                 'field_categorystyle' => $object['CategoryStyle'],
                 'field_maker' => $object['Maker'],
                 'field_alternatetitle' => $object['AlternateTitle'],
                 'field_subject' => $object['Subject'],
                 'field_stateoforigin' => $object['StateOfOrigin'],
                 'field_countyoforigin' => $object['CountyOfOrigin'],
                 'field_cityoforigin' => $object['CityOfOrigin'],
                 'field_medium' => $object['Medium'],
                 'field_edition' => $object['Edition'],
                 'field_nomenclatureobjectname' => $object['NomenclatureObjectName'],
                 'field_locationstatusid' => $object['LocationStatusId'],
                 'field_colorid' => $object['ColorId'],
                 'field_objectcurrencytypecode' => $object['ObjectCurrencyTypeCode'],
                 'field_domesticcurrencytypecode' => $object['DomesticCurrencyTypeCode'],
                 'field_itemcount' => $object['ItemCount'],
                 'field_othernumbers' => $object['OtherNumbers'],
                 'field_material' => $object['Material'],
                 'field_state' => $object['State'],
                 'field_catalognumber' => $object['CatalogNumber'],
                 'field_collectiontitle' => $object['CollectionTitle'],
                 'field_catalogercontactid' => $object['CatalogerContactId'],
                 'field_catalogdate' => $object['CatalogDate'],
                 'field_relatedcollections' => $object['RelatedCollections'],
                 'field_durationseconds' => $object['DurationSeconds'],
                 'field_duration' => $object['Duration'],
                 'field_quantity' => $object['Quantity'],
                 'field_researchnotes' => $object['ResearchNotes'],
                 'field_collectorcontactid' => $object['CollectorContactId'],
                 'field_latitudedirection' => $object['LatitudeDirection'],
                 'field_latitude' => $object['Latitude'],
                 'field_longitudedirection' => $object['LongitudeDirection'],
                 'field_longitude' => $object['Longitude'],
                 'field_allfields' => $object['AllFields'],
                 'field_objectistid' => $object['objectistId'],
                 'field_suiteportfolio' => $object['SuitePortfolio'],
                 'field_catalograisonne' => $object['CatalogRaisonne'],
                 'field_form' => $object['Form'],
                 'field_techniqueid' => $object['TechniqueId'],
                 'field_referencenotes' => $object['ReferenceNotes'],
                 'field_fileurl' => $objectImg['MainImageAttachment']['FileURL'],
                 'field_thumbsizeurl' => $objectImg['MainImageAttachment']['ThumbSizeURL'],
                 'field_midsizeurl' => $objectImg['MainImageAttachment']['MidSizeURL'],
                 'field_detailurl' => $objectImg['MainImageAttachment']['DetailURL'],
                 'field_detaillargeurl' => $objectImg['MainImageAttachment']['DetailLargeURL'],
                 'field_detailxlargeurl' => $objectImg['MainImageAttachment']['DetailXLargeURL'],
                 'field_iphoneurl' => $objectImg['MainImageAttachment']['iPhoneURL'],
                 'field_ipadurl' => $objectImg['MainImageAttachment']['iPadURL'],
                 'field_slideshowurl' => $objectImg['MainImageAttachment']['SlideShowURL']
                ]);
                $node->save();
            }
        }

        //article is machine name of Article content type.
        $content_type = 'exhibitions';
        $ids = \Drupal::entityQuery('node')->accessCheck(TRUE)
            ->condition('type', $content_type)
            ->execute();

        //Method 1: To delete multiple nodes by looping node entities.
        $nodes = Node::loadMultiple($ids);

        foreach($nodes as $node) {
            $node->delete();
        }

        // Fetching Exhibitions data
        $exhibitions = $result['data']['exhibitions']['value'];
        $exhibitionsImg = $result['imgData']['exhibitions']['ExhibitionImageAttachment']['value'];

        foreach ($exhibitions as $key => $exhibition) {
            if (isset($exhibitionsImg[$key])) {
                $exhibitionImg = $exhibitionsImg[$key];

                $modificatedate = $exhibition['ModificationDate'];
                $updateDate = new DrupalDateTime($modificatedate);
                $updateModificateDate = $updateDate->format('Y-m-d H:i:s');

                $creationdate = $exhibition['CreationDate'];
                $updateDate = new DrupalDateTime($creationdate);
                $updateCreationDate = $updateDate->format('Y-m-d H:i:s');

                $ExStartdate = $exhibition['ExhibitionStartDate'];
                $updateDate = new DrupalDateTime($ExStartdate);
                $updateStartDate = $updateDate->format('Y-m-d H:i:s');

                $ExEnddate = $exhibition['ExhibitionEndDate'];
                $updateDate = new DrupalDateTime($ExEnddate);
                $updateEndDate = $updateDate->format('Y-m-d H:i:s');

                $title = !empty($exhibition['ExhibitionTitle']) ? $exhibition['ExhibitionTitle'] : 'Default Title';

                $node = Node::create([
                    'type' => 'exhibitions',
                    'title' => $title,
                    'field_exhibitiontitle' => $exhibition['ExhibitionTitle'],
                    'field_exhibitionid' => $exhibition['ExhibitionId'],
                    'field_exhibitionlocation' => $exhibition['ExhibitionLocation'],
                    'field_exhibitiondate' => $exhibition['ExhibitionDate'],
                    'field_exhibitionenddate' => $updateEndDate,
                    'field_exhibitionenddateaccuracy' => $exhibition['ExhibitionEndDateAccuracy'],
                    'field_exhibitionenddateday' => $exhibition['ExhibitionEndDateDay'],
                    'field_exhibitionenddatemonth' => $exhibition['ExhibitionEndDateMonth'],
                    'field_exhibitionenddateyear' => $exhibition['ExhibitionEndDateYear'],
                    'field_exhibitionmemo' => $exhibition['ExhibitionMemo'],
                    'field_exhibitionstartdate' => $updateStartDate,
                    'field_exhibitionstartdateaccuracy' => $exhibition['ExhibitionStartDateAccuracy'],
                    'field_exhibitionstartdateday' => $exhibition['ExhibitionStartDateDay'],
                    'field_exhibitionstartdatemonth' => $exhibition['ExhibitionStartDateMonth'],
                    'field_exhibitionstartdateyear' => $exhibition['ExhibitionStartDateYear'],
                    'field_exhibitionsubject' => $exhibition['ExhibitionSubject'],
                    'field_exhibitionlink' => $exhibition['ExhibitionLink'],
                    'field_modificationdate' => $updateModificateDate,
                    'field_creationdate' => $updateCreationDate,
                    'field_modifiedbyusername' => $exhibition['ModifiedByUserName'],
                    'field_createdbyusername' => $exhibition['CreatedByUserName'],
                    'field_subscriptionid' => $exhibition['SubscriptionId'],
                    'field_exhibitionimageattachmentid' => $exhibition['ExhibitionImageAttachmentId'],
                    'field_fileurl' => $exhibitionImg['ExhibitionImageAttachment']['FileURL'],
                    'field_thumbsizeurl' => $exhibitionImg['ExhibitionImageAttachment']['ThumbSizeURL'],
                    'field_midsizeurl' => $exhibitionImg['ExhibitionImageAttachment']['MidSizeURL'],
                    'field_detailurl' => $exhibitionImg['ExhibitionImageAttachment']['DetailURL'],
                    'field_detaillargeurl' => $exhibitionImg['ExhibitionImageAttachment']['DetailLargeURL'],
                    'field_detailxlargeurl' => $exhibitionImg['ExhibitionImageAttachment']['DetailXLargeURL'],
                    'field_iphoneurl' => $exhibitionImg['ExhibitionImageAttachment']['iPhoneURL'],
                    'field_ipadurl' => $exhibitionImg['ExhibitionImageAttachment']['iPadURL'],
                    'field_slideshowurl' => $exhibitionImg['ExhibitionImageAttachment']['SlideShowURL']
                ]);

                $node->save();
            }
        }



        //article is machine name of Article content type.
        $content_type = 'groups';
        $ids = \Drupal::entityQuery('node')->accessCheck(TRUE)
            ->condition('type', $content_type)
            ->execute();

        //Method 1: To delete multiple nodes by looping node entities.
        $nodes = Node::loadMultiple($ids);

        foreach($nodes as $node) {
            $node->delete();
        }

        // Fetching Groups data
        $groups = $result['data']['groups']['value'];
        $groupsImg = $result['imgData']['groups']['GroupImageAttachment']['value'];
        $groupCounter = 1;

        foreach ($groups as $key => $group) {
            if (isset($groupsImg[$key])) {
                $groupImg = $groupsImg[$key];

                $modificatedate = $group['ModificationDate'];
                $updateDate = new DrupalDateTime($modificatedate);
                $updateModificateDate = $updateDate->format('Y-m-d H:i:s');

                $creationdate = $group['CreationDate'];
                $updateDate = new DrupalDateTime($creationdate);
                $updateCreationDate = $updateDate->format('Y-m-d H:i:s');

                $title = 'Group ' . $groupCounter;
                $groupCounter++;

                $node = Node::create([
                    'type' => 'groups',
                    'title' => $title,
                    'field_groupid' => $group['GroupId'],
                    'field_groupmemo' => $group['GroupMemo'],
                    'field_groupdescription' => $group['GroupDescription'],
                    'field_modificationdate' => $updateModificateDate,
                    'field_creationdate' => $updateCreationDate,
                    'field_modifiedbyusername' => $group['ModifiedByUserName'],
                    'field_createdbyusername' => $group['CreatedByUserName'],
                    'field_subscriptionid' => $group['SubscriptionId'],
                    'field_groupimageattachmentid' => $group['GroupImageAttachmentId'],
                    'field_fileurl' => $groupImg['GroupImageAttachment']['FileURL'],
                    'field_thumbsizeurl' => $groupImg['GroupImageAttachment']['ThumbSizeURL'],
                    'field_midsizeurl' => $groupImg['GroupImageAttachment']['MidSizeURL'],
                    'field_detailurl' => $groupImg['GroupImageAttachment']['DetailURL'],
                    'field_detaillargeurl' => $groupImg['GroupImageAttachment']['DetailLargeURL'],
                    'field_detailxlargeurl' => $groupImg['GroupImageAttachment']['DetailXLargeURL'],
                    'field_iphoneurl' => $groupImg['GroupImageAttachment']['iPhoneURL'],
                    'field_ipadurl' => $groupImg['GroupImageAttachment']['iPadURL'],
                    'field_slideshowurl' => $groupImg['GroupImageAttachment']['SlideShowURL']
                ]);

                $node->save();
            }
        }


        //article is machine name of Article content type.
        $content_type = 'collections';
        $ids = \Drupal::entityQuery('node')->accessCheck(TRUE)
            ->condition('type', $content_type)
            ->execute();

        //Method 1: To delete multiple nodes by looping node entities.
        $nodes = Node::loadMultiple($ids);

        foreach($nodes as $node) {
            $node->delete();
        }

        // Fetching Collections data
        $collections = $result['data']['collections']['value'];
        $collectionsImg = $result['imgData']['collections']['CollectionImageAttachment']['value'];

        foreach ($collections as $key => $collection) {
            if (isset($collectionsImg[$key])) {
                $collectionImg = $collectionsImg[$key];

                $modificatedate = $collection['ModificationDate'];
                $updateDate = new DrupalDateTime($modificatedate);
                $updateModificateDate = $updateDate->format('Y-m-d H:i:s');

                $creationdate = $collection['CreationDate'];
                $updateDate = new DrupalDateTime($creationdate);
                $updateCreationDate = $updateDate->format('Y-m-d H:i:s');

                $node = Node::create([
                    'type' => 'collections',
                    'title' => $collection['CollectionName'],
                    'field_collectionid' => $collection['CollectionId'],
                    'field_parentcollectionid' => $collection['ParentCollectionId'],
                    'field_fullcollectionname' => $collection['FullCollectionName'],
                    'field_rootcollectionid' => $collection['RootCollectionId'],
                    'field_modificationdate' => $updateModificateDate,
                    'field_creationdate' => $updateCreationDate,
                    'field_modifiedbyusername' => $collection['ModifiedByUserName'],
                    'field_createdbyusername' => $collection['CreatedByUserName'],
                    'field_subscriptionid' => $collection['SubscriptionId'],
                    'field_leftextent' => $collection['LeftExtent'],
                    'field_rightextent' => $collection['RightExtent'],
                    'field_descendentcount' => $collection['DescendentCount'],
                    'field_collectionimageattachmenti' => $collection['CollectionImageAttachmentId'],
                    'field_fileurl' => $collectionImg['CollectionImageAttachment']['FileURL'],
                    'field_thumbsizeurl' => $collectionImg['CollectionImageAttachment']['ThumbSizeURL'],
                    'field_midsizeurl' => $collectionImg['CollectionImageAttachment']['MidSizeURL'],
                    'field_detailurl' => $collectionImg['CollectionImageAttachment']['DetailURL'],
                    'field_detaillargeurl' => $collectionImg['CollectionImageAttachment']['DetailLargeURL'],
                    'field_detailxlargeurl' => $collectionImg['CollectionImageAttachment']['DetailXLargeURL'],
                    'field_iphoneurl' => $collectionImg['CollectionImageAttachment']['iPhoneURL'],
                    'field_ipadurl' => $collectionImg['CollectionImageAttachment']['iPadURL'],
                    'field_slideshowurl' => $collectionImg['CollectionImageAttachment']['SlideShowURL']
                ]);

                $node->save();
            }
        }

        //article is machine name of Article content type.
        $content_type = '_artists';
        $ids = \Drupal::entityQuery('node')->accessCheck(TRUE)
            ->condition('type', $content_type)
            ->execute();

        //Method 1: To delete multiple nodes by looping node entities.
        $nodes = Node::loadMultiple($ids);

        foreach($nodes as $node) {
            $node->delete();
        }

        // Fetching Artists data
        $artists = $result['data']['artists']['value'];
        $artistsImg = $result['imgData']['artists']['ArtistPhotoAttachment']['value'];

        foreach ($artists as $key => $artist) {
            if (isset($artistsImg[$key])) {
                $artistImg = $artistsImg[$key];

                $artistFullName = $artist['ArtistFirst'] . ', ' . $artist['ArtistLast'];
                $modificatedate = $artist['ModificationDate'];
                $updateDate = new DrupalDateTime($modificatedate);
                $updateModificateDate = $updateDate->format('Y-m-d H:i:s');

                $creationdate = $artist['CreationDate'];
                $updateDate = new DrupalDateTime($creationdate);
                $updateCreationDate = $updateDate->format('Y-m-d H:i:s');

                $node = Node::create([
                    'type' => '_artists',
                    'title' => $artistFullName,
                    'field_artistname' => $artist['ArtistName'],
                    'field_artistid' => $artist['ArtistId'],
                    'field_artistfirst' => $artist['ArtistFirst'],
                    'field_artistlast' => $artist['ArtistLast'],
                    'field_artistyears' => $artist['ArtistYears'],
                    'field_modificationdate' => $updateModificateDate,
                    'field_modifiedbyusername' => $artist['ModifiedByUserName'],
                    'field_subscriptionid' => $artist['SubscriptionId'],
                    'field_artistnationality' => $artist['ArtistNationality'],
                    'field_artistcompany' => $artist['ArtistCompany'],
                    'field_artistaliasfirst' => $artist['ArtistAliasFirst'],
                    'field_artistaliaslast' => $artist['ArtistAliasLast'],
                    'field_artistlocale' => $artist['ArtistLocale'],
                    'field_artistbio' => $artist['ArtistBio'],
                    'field_artistmemo' => $artist['ArtistMemo'],
                    'field_creationdate' => $updateCreationDate,
                    'field_createdbyusername' => $artist['CreatedByUserName'],
                    'field_artistphotoattachmentid' => $artist['ArtistPhotoAttachmentId'],
                    'field_artistlink' => $artist['ArtistLink'],
                    'field_gettyid' => $artist['GettyID'],
                    'field_artistalias' => $artist['ArtistAlias'],
                    'field_artistrace' => $artist['ArtistRace'],
                    'field_artistethnicity' => $artist['ArtistEthnicity'],
                    'field_artistgenderid' => $artist['ArtistGenderId'],
                    'field_artistschool' => $artist['ArtistSchool'],
                    'field_fileurl' => $artistImg['ArtistPhotoAttachment']['FileURL'],
                    'field_thumbsizeurl' => $artistImg['ArtistPhotoAttachment']['ThumbSizeURL'],
                    'field_midsizeurl' => $artistImg['ArtistPhotoAttachment']['MidSizeURL'],
                    'field_detailurl' => $artistImg['ArtistPhotoAttachment']['DetailURL'],
                    'field_detaillargeurl' => $artistImg['ArtistPhotoAttachment']['DetailLargeURL'],
                    'field_detailxlargeurl' => $artistImg['ArtistPhotoAttachment']['DetailXLargeURL'],
                    'field_iphoneurl' => $artistImg['ArtistPhotoAttachment']['iPhoneURL'],
                    'field_ipadurl' => $artistImg['ArtistPhotoAttachment']['iPadURL'],
                    'field_slideshowurl' => $artistImg['ArtistPhotoAttachment']['SlideShowURL'],
                ]);

                $node->save();
            }
        }

        return new JsonResponse($result);
    }
}
