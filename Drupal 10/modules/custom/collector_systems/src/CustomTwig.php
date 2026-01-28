<?php

namespace Drupal\collector_systems;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Drupal\collector_systems\Csconstants;
use Drupal\Component\Utility\UrlHelper;


/**
 * Custom twig functions.
 */
class CustomTwig extends AbstractExtension {
  public function getFunctions() {
    return [
      new TwigFunction('getObjectslistHtml', [$this, 'getObjectslistHtml']),
      new TwigFunction('customPaginationForTopLevelTabs', [$this, 'customPaginationForTopLevelTabs']),
      new TwigFunction('base64_encode', [$this, 'base64_encode']),
      new TwigFunction('GetCustomizedObjectDetailsForTheme', [$this, 'GetCustomizedObjectDetailsForTheme']),
      new TwigFunction('customPaginationForGroupLevelObjects', [$this, 'customPaginationForGroupLevelObjects']),
    ];
  }


  public function base64_encode($data){
    return base64_encode($data);
  }

  public function getObjectslistHtml($value=[], $dataOrderBy, $datapageNo, $dataSearch,$delaytm, $default_image_url=NULL){

    if (is_object($value)) {
        $value = get_object_vars($value);
    }

    $customized_fields = $this->getCommaSeparatedFieldsForListPage();
    // $customized_fields = 'Title, test'; //temp only

    //echo "field:" .$customized_fields;
    $customized_fields_array = explode(',', $customized_fields);
    $object_detail_link = "/artobject-detail?dataId=". $value['ObjectId']."&sortBy=".$dataOrderBy."&pageNo=".$datapageNo;
    $showImagesOnListPages =  \Drupal::config('collector_systems.settings')->get('show_images_objects');
    ?>

    <div class="card col-lg-4 col-md-6 col-sm-6 col-12 mb-3 cs-object-list wow fadeInDown" data-wow-delay="<?php echo $delaytm; ?>">
      <?php if($showImagesOnListPages){ ?>    
                    <div class="card-body d-flex flex-column">
                        <div class="image-wrapper">
                            <a href="<?php echo $object_detail_link; ?>" class="image-wrapper-link">
                                <?php
                                $main_image_attachment_description = isset($value['main_image_attachment_description']) && $value['main_image_attachment_description'] !== ''
                                ? $value['main_image_attachment_description']
                                : 'Image description is not available.';

                                $object_img = !empty($value['main_image_attachment']) ? 'data:image/jpeg;base64,' . base64_encode($value['main_image_attachment']) : "";
                                $server_path = $value['main_image_path'];
                                if($server_path){
                                  $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $server_path);
                                }
                                else{
                                  $relative_path = '';
                                }

                                $image_url = '';
                                if (!empty($relative_path)) {
                                  $encoded_path = UrlHelper::encodePath($relative_path);
                                  $image_url = \Drupal::request()->getSchemeAndHttpHost() . '/' . ltrim($encoded_path, '/');
                                } else {
                                  $image_url = '';
                                }


                                if(empty($object_img) && empty($server_path)){
                                ?>
                                    <img class="img-fluid" src="<?php echo $default_image_url; ?>" alt="<?php echo $main_image_attachment_description ?>"/>
                                    <?php } else {
                                    if (empty($server_path)) {
                                    ?>
                                    <img class="img-fluid" src="<?php echo $object_img; ?>" alt="<?php echo $main_image_attachment_description ?>"/>
                                    <?php
                                    } else {
                                    ?>
                                    <img class="img-fluid" src="<?php echo $image_url; ?>" alt="<?php echo $main_image_attachment_description ?>"/>
                                    <?php
                                    }
                                    }
                                    ?>
                            </a>
                          </div>
                    </div>
          <?php } ?>
                    <div class="card-footer text-muted">
                    <?php

                      /*get first 3 array fields*/
                      //$customized_fields_array = array_slice($customized_fields_array, 0, 3);
                      $this->fillObjectListHtml($customized_fields_array, $value, $dataOrderBy, $datapageNo,$dataSearch,$delaytm, $default_image_url);
          
                    ?>
          </div>
        </div>
      <?php
    }

  public function fillObjectListHtml($customized_fields_array, $value, $dataOrderBy, $datapageNo,$dataSearch,$delaytm, $default_image_url)
  {
    $site_url = \Drupal::request()->getSchemeAndHttpHost();
    $object_detail_link = "/artobject-detail?dataId=". $value['ObjectId']."&sortBy=".$dataOrderBy."&pageNo=".$datapageNo;

    foreach($customized_fields_array as $object_field)
    {
    switch($object_field)
    {   
      case csconstants::InventoryNumber:
        if(!empty($value['InventoryNumber'])){ ?>            
          <h6 class="font-normal" title="<?php echo $value['InventoryNumber']; ?>" >
            <small class="flex-fill">
              <a href="<?php echo $object_detail_link ?>" ><?php echo $value['InventoryNumber']  ?></a>
            </small>
          </h6>
        <?php }    
        break;
  
    case csconstants::Title:       
      if(!empty($value['Title'])){ ?>            
          <h6 class="font-normal cs-theme-label-withunderline">
            <small class="flex-fill">
              <a href="<?php echo $object_detail_link ?>"><?php echo $value['Title']  ?></a>
            </small>
          </h6>
        <?php }    
        break;
  
    case  csconstants::FullCollectionName:
        if(!empty($value['FullCollectionName'])){
          ?>            
        <h6 class="font-normal" title="<?php echo $value['FullCollectionName']; ?>" >
          <small class="flex-fill">
          <a href="javascript:;"onclick="return getmoredetailsForCollection('<?php echo  $site_url ?>', <?php echo $value['CollectionId']; ?>)"><?php echo $value['FullCollectionName']  ?></a>
          </small>
        </h6>
      <?php
        }
        break;
  
    case csconstants::ArtistName:
    case csconstants::ArtistFirst:
    case csconstants::ArtistLast:
      if(!empty($value[$object_field])){
            ?>            
          <h6 class="font-normal" title="<?php echo $value[$object_field]; ?>" >
            <small class="flex-fill">
            <a href="<?php echo $site_url ?>/artist-detail?dataId=<?php echo $value['ArtistId']; ?>"><?php echo $value[$object_field] ?></a>
            </small>
          </h6>
        <?php
          }
          break;
  
      case csconstants::AdditionalArtists:
        if(!empty($value['AdditionalArtists'])){ 
            $AdditionalArtists = json_decode($value['AdditionalArtists'], true);
          if(!empty($AdditionalArtists)){ ?>
            
            <h6 class="font-normal cs-theme-card-title">
              <small class="flex-fill">
                <?php echo $this->implodeChildArrayProperty($AdditionalArtists,"Artist","ArtistId","ArtistName");  ?>
              </small>
            </h6>

          <?php } 
        }    
        break;
  
      case csconstants::ArtistMakerName:
      case csconstants::ArtistMakerFirst:
      case csconstants::ArtistMakerLast:
        if(!empty($value[$object_field])){ ?>            
          <h6 class="font-normal" title="<?php echo $value[$object_field]; ?>" >
            <small class="flex-fill">
            <a href="<?php echo  $site_url ?>/artist-detail?dataId=<?php echo $value['ArtistMakerId']; ?>"><?php echo $value[$object_field] ?></a>
            </small>
          </h6>
          <?php }
          break;
  
      case csconstants::AdditionalArtistMakers:
      if(!empty($value['AdditionalArtistMakers'])){ 
        $AdditionalArtistMakers = json_decode($value['AdditionalArtistMakers'], true);
        ?>
          <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $this->implodeChildArrayProperty($AdditionalArtistMakers,"ArtistMaker","ArtistMakerId","ArtistMakerName");  ?></small></h6>
        <?php }    
        break;
        
  
      //date fields
      case csconstants::InventoryDate:
      case csconstants::CatalogDate:
      case csconstants::CollectionDate:
      case csconstants::IdentifiedDate:
      case csconstants::SpeciesAuthorDate:
      case csconstants::SubspeciesAuthorDate:
      case csconstants::ManufactureDate: 
      case csconstants::ReleaseDate:
      case csconstants::ProductionDate:
      case csconstants::ThreatenedEndangeredDate: 
      case csconstants::CompletenessDate:
      case csconstants::MovementAuthorizationDate: 
      case csconstants::LocationConditionDate:
      if(!empty($value[$object_field])){ ?>
          <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo date('m/d/Y',strtotime($value[$object_field]))  ?></small></h6>
        <?php }
        break;
  
      //boolean fields
      case csconstants::CatalogFolder:
      case csconstants::ControlledProperty:
      case csconstants::ThreatenedEndangeredSpeciesSynonym:
      case csconstants::ThinSection:
        if(!empty($value[$object_field])){ ?>
          <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value[$object_field] == true ? 'Yes' : 'No'  ?></small></h6>   
        <?php }
        break;
  
      /*udf fields*/
  
      case csconstants::UserDefinedDate1:
      case csconstants::UserDefinedDate2:
      if(!empty($value[$object_field])){ ?>
          <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo date('m/d/Y',strtotime($value[$object_field]))  ?></small></h6>
        <?php }    
        break;
  
        default:
        if(!empty($value[$object_field])){ ?>
          <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value[$object_field]  ?></small></h6>
          <?php }
        break;
  
          /*end*/
    }
    }
  }


  public function customPaginationForTopLevelTabs($requested_page,$total_records,$sortBy,$qSearch)
  {
    $sortBy = $sortBy ? rawurlencode($sortBy) : '';

    $request = \Drupal::request();
    $baseUrl = $request->getBasePath();
    $pagingUrl = "";
    if(!empty($sortBy) && empty($qSearch))
    {

      $pagingUrl = $baseUrl ."?sortBy=".$sortBy;
    }
    if(empty($sortBy) && !empty($qSearch))
    {
      $pagingUrl = $baseUrl ."?qSearch=".$qSearch;
    }
    if(!empty($sortBy) && !empty($qSearch))
    {
      $pagingUrl = $baseUrl ."?sortBy=".$sortBy."&qSearch=".$qSearch;
    }
    if(empty($sortBy) && empty($qSearch))
    {
      $pagingUrl = $baseUrl;
    }
    $listPageSize =  \Drupal::config('collector_systems.settings')->get('items_per_page');
    if(!$listPageSize){
      return;
    }

    $showitems = $listPageSize;
    if(empty($requested_page)) $requested_page = 1;

    $pages = ceil($total_records / $listPageSize);
    if(!$pages)
    {
        $pages = 1;
    }

    if(1 != $pages)
    {
        echo "<div class='cs-custom-pagination d-flex'>";

      if($requested_page != 1) echo "<a href='".$pagingUrl."&pageNo=".($requested_page - 1)."'><i class='fas fa-chevron-left'></i></a>";

        for ($i=1; $i <= $pages; $i++)
        {
            if (1 != $pages &&( !($i >= $requested_page+$showitems+1 || $i <= $requested_page-$showitems-1) || $pages <= $showitems ))
            {
                echo ($requested_page == $i)? "<span class='current'>".$i."</span>":"<a href='".$pagingUrl."&pageNo=".($i)."' class='inactive' >".$i."</a>";
            }
        }

        //if ($requested_page < $pages && $showitems < $pages) echo "<a href='".\Drupal::request()->getHost()."/".$current_page."?pageNo=".($requested_page + 1)."'><i class='bi bi-chevron-right'></i></a>";
        //if ($requested_page < $pages-1 &&  $requested_page+$range-1 < $pages && $showitems < $pages) echo "<a href='".\Drupal::request()->getHost()."/".$current_page."?pageNo=".($pages)."'>&raquo;</a>";

        if ($requested_page != $pages) echo "<a href='".$pagingUrl."&pageNo=".($requested_page + 1)."'><i class='fas fa-chevron-right'></i></a>";

        echo "</div>\n";
    }
    }

    public function GetCustomizedObjectDetailsForTheme($object_field, $accountCustomizationData, $artObjData)
    {
      $site_url = \Drupal::request()->getSchemeAndHttpHost();

      $showFieldLabelNames =  \Drupal::config('collector_systems.settings')->get('show_field_labels');


      if (is_array($artObjData) || is_object($artObjData)) {
        $artObjData = is_object($artObjData) ? get_object_vars($artObjData) : $artObjData;
      }
      switch($object_field)
            {
              case csconstants::Title:
                if(!empty($artObjData['Title'])){ ?>
                      <p class="my-2"><?php echo $artObjData['Title'];  ?></p>
                    <?php }

                    break;
              case csconstants::CollectionName:
                  if (!empty($artObjData['CollectionName'])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo csconstants::CollectionNameFieldLabel ?>:</span>
                      <?php } ?>
                      <a href="javascript:;" onclick="return getmoredetailsForCollection('<?php echo $site_url; ?>','<?php echo $artObjData['CollectionId']; ?>')"><?php echo $artObjData['CollectionName']  ?></a>
                    </p>
                  <?php }

                break;
              case csconstants::FullCollectionName:
                  if (!empty($artObjData['FullCollectionName'])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo csconstants::FullCollectionNameFieldLabel ?>:</span>
                      <?php } ?>
                      <a href="javascript:;" onclick="return getmoredetailsForCollection('<?php echo $site_url ?>','<?php echo $artObjData['CollectionId']; ?>')"><?php echo $artObjData['FullCollectionName']  ?></a>
                    </p>
                  <?php }
                break;
              case csconstants::ArtistName:
                  if (!empty($artObjData['ArtistName'])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo csconstants::ArtistNameFieldLabel ?>:</span>
                      <?php } ?>
                      <a href="<?php echo $site_url ?>/artist-detail?dataId=<?php echo $artObjData['ArtistId']; ?>">
                        <?php echo $artObjData['ArtistName'] ?>
                      </a>
                    </p>
                  <?php }

                break;
              case csconstants::ArtistLast:
                  if (!empty($artObjDat[$object_field])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo constant('csconstants::' . $object_field . 'FieldLabel') ?>:</span>
                      <?php } ?>
                      <a href="<?php echo $site_url;?>/artist-detail?dataId=<?php echo $artObjData['ArtistId']; ?>">
                        <?php echo $artObjData[$object_field] ?>
                      </a>
                    </p>
                  <?php }

                break;
              case csconstants::ArtistMakerName:
                  if (!empty($artObjData['ArtistName'])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo csconstants::ArtistMakerNameFieldLabel ?>:</span>
                      <?php } ?>
                      <a href="<?php echo $site_url;?>/artist-detail?dataId=<?php echo $artObjData['ArtistId']; ?>">
                        <?php echo $artObjData['ArtistName'] ?>
                      </a>
                    </p>
                  <?php }

                break;
              case csconstants::ArtistMakerLast:
                  if (!empty($artObjData[$object_field])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo constant('csconstants::' . $object_field . 'FieldLabel') ?>:</span>
                      <?php } ?>
                      <a href="<?php echo $site_url?>/artist-detail?dataId=<?php echo $artObjData['ArtistId']; ?>">
                        <?php echo $artObjData[$object_field] ?>
                      </a>
                    </p>
                  <?php }

                break;
              case csconstants::AdditionalArtists:
                if(!empty($artObjData['AdditionalArtists'])){ 
                    $AdditionalArtists = json_decode($artObjData['AdditionalArtists'], true);
                  if(!empty($AdditionalArtists)){ ?>
                    
                    <h6 class="font-normal cs-theme-card-title">
                      <small class="flex-fill">
                        <?php echo $this->implodeChildArrayProperty($AdditionalArtists,"Artist","ArtistId","ArtistName");  ?>
                      </small>
                    </h6>
        
                  <?php } 
                }    
                break;
              case csconstants::AdditionalArtistMakers:
                if(!empty($artObjData['AdditionalArtistMakers'])){ 
                  $AdditionalArtistMakers = json_decode($artObjData['AdditionalArtistMakers'], true);
                  ?>
                    <h6 class="font-normal cs-theme-card-title">
                      <small class="flex-fill">
                        <?php echo $this->implodeChildArrayProperty($AdditionalArtistMakers,"ArtistMaker","ArtistMakerId","ArtistMakerName");  ?>
                      </small>
                    </h6>
                    
                  <?php }    
                  break;
              //richtext fields
              case csconstants::DimensionMemo:
              case csconstants::InventoryMemo:
              case csconstants::ObjectDescription:
              case csconstants::Signatures:
              case csconstants::Inscriptions:
              case csconstants::Labels:
              case csconstants::Provenance:
              case csconstants::ReferenceNotes:
              case csconstants::ResearchNotes:
              case csconstants::StaffNotes:
              case csconstants::RelatedCollections:
              case csconstants::KeyDescriptor:
              case csconstants::WithinSiteProveniance:
              case csconstants::SubspeciesDescriptiveName:
              case csconstants::History:
              case csconstants::Transcription:
              case csconstants::CastAndCrew:
              case csconstants::Synopsis:
              case csconstants::Waterbody:
              case csconstants::AssociatedSpecies:
              case csconstants::Drainage:
              case csconstants::ObjectUse:
              case csconstants::StartingInstructions:
              case csconstants::RegistrationNotes:
              case csconstants::TitleStatusNotes:
              case csconstants::RepairsMade:
              case csconstants::CompletenessNote:
              case csconstants::MovementMemo:
              case csconstants::LocationAccessMemo:
              case csconstants::LocationConditionMemo:
              case csconstants::LocationSecurityMemo:
              case csconstants::ObjectNameNote:
              case csconstants::FieldCollectionMemo:
              case csconstants::HabitatMemo:
              case csconstants::StratigraphicUnitMemo:
                if(!empty($artObjData[$object_field])){ ?>
                  <p class="my-2">
                  <?php if($showFieldLabelNames==1){

                      $ObjectFieldsService = \Drupal::service('customize_object_detail_fields.object_fields_service');
                      $fieldLabel = $ObjectFieldsService->getObjectFieldLabelFromDatabase($object_field);
                    ?>

                  <span class="object_detail_fieldlabel"><?php echo $fieldLabel ?>:</span>

                  <?php } ?>
                    <span class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData[$object_field]  ?></span>
                  </p>
                  <?php }
                break;
              case csconstants::UserDefinedRichText1:
              case csconstants::UserDefinedRichText2:
              case csconstants::UserDefinedRichText3:
              case csconstants::UserDefinedRichText4:
              case csconstants::UserDefinedRichText5:
              case csconstants::UserDefinedRichText6:
              case csconstants::UserDefinedRichText7:
              case csconstants::UserDefinedRichText8:
              case csconstants::UserDefinedRichText9:
              case csconstants::UserDefinedRichText10:
              case csconstants::UserDefinedRichText11:
              case csconstants::UserDefinedRichText12:
              case csconstants::UserDefinedRichText13:
              case csconstants::UserDefinedRichText14:
              case csconstants::UserDefinedRichText15:
              case csconstants::UserDefinedRichText16:
              case csconstants::UserDefinedRichText17:
              case csconstants::UserDefinedRichText18:
                if(!empty($artObjData[$object_field])){ ?>
                  <p class="my-2">
                  <?php if($showFieldLabelNames==1){

                      $ObjectFieldsService = \Drupal::service('customize_object_detail_fields.object_fields_service');
                      $fieldLabel = $ObjectFieldsService->getObjectFieldLabelFromDatabase($object_field);
                    ?>

                  <span class="object_detail_fieldlabel"><?php echo $fieldLabel ?>:</span>

                  <?php } ?>
                    <span class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData[$object_field]  ?></span>
                  </p>
                  <?php }
                break;

              default:
                if(!empty($artObjData[$object_field])){ ?>
                    <p class="my-2">
                    <?php if($showFieldLabelNames==1){

                        $ObjectFieldsService = \Drupal::service('customize_object_detail_fields.object_fields_service');
                        $fieldLabel = $ObjectFieldsService->getObjectFieldLabelFromDatabase($object_field);
                      ?>

                    <span class="object_detail_fieldlabel"><?php echo $fieldLabel ?>:</span>

                    <?php } ?>
                      <?php echo $artObjData[$object_field]  ?>
                    </p>
                    <?php }
                break;


      /*end*/

      }
    }


  public function customPaginationForGroupLevelObjects($groupTypeId,$ajaxpage,$requested_page,$total_records,$sortBy,$qSearch)
  {
    $listPageSize =  \Drupal::config('collector_systems.settings')->get('items_per_page');


    $showitems = $listPageSize;
    if(empty($requested_page)) $requested_page = 1;

    $pages = ceil($total_records / $listPageSize);

    if(!$pages)
    {
        $pages = 1;
    }

    if(1 != $pages)
    {
        if($requested_page != 1) echo "<a href='javascript:;' onclick=pagingForGroupLevelObjects('".$ajaxpage."',".$listPageSize.",".($requested_page - 1).")><i class='fas fa-chevron-left'></i></a>";

        for ($i=1; $i <= $pages; $i++)
        {
            if (1 != $pages &&( !($i >= $requested_page+$showitems+1 || $i <= $requested_page-$showitems-1) || $pages <= $showitems ))
            {
                echo ($requested_page == $i)? "<span class='current'>".$i."</span>":"<a href='javascript:;' onclick=pagingForGroupLevelObjects('".$ajaxpage."',".$listPageSize.",".($i).") class='inactive' >".$i."</a>";
            }
        }

        if ($requested_page != $pages) echo "<a href='javascript:;' onclick=pagingForGroupLevelObjects('".$ajaxpage."',".$listPageSize.",".($requested_page + 1).")><i class='fas fa-chevron-right'></i></a>";

        echo "\n";
    }
  }

  public function getCommaSeparatedFieldsForListPage(){
    $db = \Drupal::database();

    $tblnm = "collector_systems_clsobjects_fields";
    $settblnm = $tblnm;

    $query = $db->select($settblnm, 'c')
      ->fields('c', ['fieldname'])
      ->condition('fieldtype', 'ObjectList');

    $result = $query->execute()->fetchAllAssoc('fieldname');

    $values = implode(',', array_keys($result));

    return $values;

  }

  
  /**
   * Implode child array property with link for 'AdditonalArtists'  and 'AdditionalArtistMakers'.
   */
  public function implodeChildArrayProperty($additionalArrayObject,$additionalArray,$additionalPropertyId,$additionalProperty) {    
    $site_url = \Drupal::request()->getSchemeAndHttpHost();
    $commaSeperatedItem = "";  
    
    if(!is_array($additionalArrayObject) || count($additionalArrayObject) == 0){
      return $commaSeperatedItem;
    }
  
    foreach ($additionalArrayObject as $additionalItem) {        
      $artistId = isset($additionalItem[$additionalArray][$additionalPropertyId]) ? $additionalItem[$additionalArray][$additionalPropertyId] : '';
      if(!empty($artistId)){
        $commaSeperatedItem != "" && $commaSeperatedItem .= ", ";
        $commaSeperatedItem .= '<a href="'.$site_url.'/artist-detail?dataId='.$artistId.'">'.$additionalItem[$additionalArray][$additionalProperty].'</a>';   
      }
     
    } 
    return $commaSeperatedItem;     
  }

}
