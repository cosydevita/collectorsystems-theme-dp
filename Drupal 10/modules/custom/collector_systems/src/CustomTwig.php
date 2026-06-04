<?php

namespace Drupal\collector_systems;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Drupal\collector_systems\Csconstants;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Html;


/**
 * Custom twig functions.
 */
class CustomTwig extends AbstractExtension {
  public function getFunctions() {
    return [
      new TwigFunction('getObjectslistHtml', [$this, 'getObjectslistHtml'], ['is_safe' => ['html']]),
      new TwigFunction('customPaginationForTopLevelTabs', [$this, 'customPaginationForTopLevelTabs'], ['is_safe' => ['html']]),
      new TwigFunction('base64_encode', [$this, 'base64_encode']),
      new TwigFunction('GetCustomizedObjectDetailsForTheme', [$this, 'GetCustomizedObjectDetailsForTheme'], ['is_safe' => ['html']]),
      new TwigFunction('customPaginationForGroupLevelObjects', [$this, 'customPaginationForGroupLevelObjects'], ['is_safe' => ['html']]),
    ];
  }


  public function base64_encode($data){
    return base64_encode($data);
  }

  public function getObjectslistHtml($value, $dataOrderBy, $datapageNo, $dataSearch, $delaytm, $default_image_url=NULL){
    ob_start();

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
    return ob_get_clean();
    }

  public function fillObjectListHtml($customized_fields_array, $value, $dataOrderBy, $datapageNo,$dataSearch,$delaytm, $default_image_url)
  {
    $site_url = \Drupal::request()->getSchemeAndHttpHost();
    $object_detail_link = "/artobject-detail?dataId=". $value['ObjectId']."&sortBy=".$dataOrderBy."&pageNo=".$datapageNo;

    foreach($customized_fields_array as $object_field)
    {
    switch($object_field)
    {   
      case Csconstants::InventoryNumber:
        if(!empty($value['InventoryNumber'])){ ?>            
          <h6 class="font-normal" title="<?php echo $value['InventoryNumber']; ?>" >
            <small class="flex-fill">
              <a href="<?php echo $object_detail_link ?>" ><?php echo $value['InventoryNumber']  ?></a>
            </small>
          </h6>
        <?php }    
        break;
  
    case Csconstants::Title:       
      if(!empty($value['Title'])){ ?>            
          <h6 class="font-normal cs-theme-label-withunderline">
            <small class="flex-fill">
              <a href="<?php echo $object_detail_link ?>"><?php echo $value['Title']  ?></a>
            </small>
          </h6>
        <?php }    
        break;
  
    case  Csconstants::FullCollectionName:
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
  
    case Csconstants::ArtistName:
    case Csconstants::ArtistFirst:
    case Csconstants::ArtistLast:
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
  
      case Csconstants::AdditionalArtists:
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
  
      case Csconstants::ArtistMakerName:
      case Csconstants::ArtistMakerFirst:
      case Csconstants::ArtistMakerLast:
        if(!empty($value[$object_field])){ ?>            
          <h6 class="font-normal" title="<?php echo $value[$object_field]; ?>" >
            <small class="flex-fill">
            <a href="<?php echo  $site_url ?>/artist-detail?dataId=<?php echo $value['ArtistMakerId']; ?>"><?php echo $value[$object_field] ?></a>
            </small>
          </h6>
          <?php }
          break;
  
      case Csconstants::AdditionalArtistMakers:
      if(!empty($value['AdditionalArtistMakers'])){ 
        $AdditionalArtistMakers = json_decode($value['AdditionalArtistMakers'], true);
        ?>
          <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $this->implodeChildArrayProperty($AdditionalArtistMakers,"ArtistMaker","ArtistMakerId","ArtistMakerName");  ?></small></h6>
        <?php }    
        break;
        
  
      //date fields
      case Csconstants::InventoryDate:
      case Csconstants::CatalogDate:
      case Csconstants::CollectionDate:
      case Csconstants::IdentifiedDate:
      case Csconstants::SpeciesAuthorDate:
      case Csconstants::SubspeciesAuthorDate:
      case Csconstants::ManufactureDate: 
      case Csconstants::ReleaseDate:
      case Csconstants::ProductionDate:
      case Csconstants::ThreatenedEndangeredDate: 
      case Csconstants::CompletenessDate:
      case Csconstants::MovementAuthorizationDate: 
      case Csconstants::LocationConditionDate:
      if(!empty($value[$object_field])){ ?>
          <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo date('m/d/Y',strtotime($value[$object_field]))  ?></small></h6>
        <?php }
        break;
  
      //boolean fields
      case Csconstants::CatalogFolder:
      case Csconstants::ControlledProperty:
      case Csconstants::ThreatenedEndangeredSpeciesSynonym:
      case Csconstants::ThinSection:
        if(!empty($value[$object_field])){ ?>
          <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value[$object_field] == true ? 'Yes' : 'No'  ?></small></h6>   
        <?php }
        break;
  
      /*udf fields*/
  
      case Csconstants::UserDefinedDate1:
      case Csconstants::UserDefinedDate2:
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
    ob_start();
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
    return ob_get_clean();
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
              case Csconstants::Title:
                if(!empty($artObjData['Title'])){ ?>
                      <p class="my-2"><?php echo $artObjData['Title'];  ?></p>
                    <?php }

                    break;
              case Csconstants::CollectionName:
                  if (!empty($artObjData['CollectionName'])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo Csconstants::CollectionNameFieldLabel ?>:</span>
                      <?php } ?>
                      <a href="javascript:;" onclick="return getmoredetailsForCollection('<?php echo $site_url; ?>','<?php echo $artObjData['CollectionId']; ?>')"><?php echo $artObjData['CollectionName']  ?></a>
                    </p>
                  <?php }

                break;
              case Csconstants::FullCollectionName:
                  if (!empty($artObjData['FullCollectionName'])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo Csconstants::FullCollectionNameFieldLabel ?>:</span>
                      <?php } ?>
                      <a href="javascript:;" onclick="return getmoredetailsForCollection('<?php echo $site_url ?>','<?php echo $artObjData['CollectionId']; ?>')"><?php echo $artObjData['FullCollectionName']  ?></a>
                    </p>
                  <?php }
                break;
              case Csconstants::ArtistName:
                  if (!empty($artObjData['ArtistName'])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo Csconstants::ArtistNameFieldLabel ?>:</span>
                      <?php } ?>
                      <a href="<?php echo $site_url ?>/artist-detail?dataId=<?php echo $artObjData['ArtistId']; ?>">
                        <?php echo $artObjData['ArtistName'] ?>
                      </a>
                    </p>
                  <?php }

                break;
              case Csconstants::ArtistLast:
                  if (!empty($artObjDat[$object_field])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo constant('Csconstants::' . $object_field . 'FieldLabel') ?>:</span>
                      <?php } ?>
                      <a href="<?php echo $site_url;?>/artist-detail?dataId=<?php echo $artObjData['ArtistId']; ?>">
                        <?php echo $artObjData[$object_field] ?>
                      </a>
                    </p>
                  <?php }

                break;
              case Csconstants::ArtistMakerName:
                  if (!empty($artObjData['ArtistName'])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo Csconstants::ArtistMakerNameFieldLabel ?>:</span>
                      <?php } ?>
                      <a href="<?php echo $site_url;?>/artist-detail?dataId=<?php echo $artObjData['ArtistId']; ?>">
                        <?php echo $artObjData['ArtistName'] ?>
                      </a>
                    </p>
                  <?php }

                break;
              case Csconstants::ArtistMakerLast:
                  if (!empty($artObjData[$object_field])) { ?>
                    <p class="my-2">
                      <?php if ($showFieldLabelNames == 1) { ?>
                        <span class="object_detail_fieldlabel"><?php echo constant('Csconstants::' . $object_field . 'FieldLabel') ?>:</span>
                      <?php } ?>
                      <a href="<?php echo $site_url?>/artist-detail?dataId=<?php echo $artObjData['ArtistId']; ?>">
                        <?php echo $artObjData[$object_field] ?>
                      </a>
                    </p>
                  <?php }

                break;
              case Csconstants::AdditionalArtists:
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
              case Csconstants::AdditionalArtistMakers:
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
              case Csconstants::DimensionMemo:
              case Csconstants::InventoryMemo:
              case Csconstants::ObjectDescription:
              case Csconstants::Signatures:
              case Csconstants::Inscriptions:
              case Csconstants::Labels:
              case Csconstants::Provenance:
              case Csconstants::ReferenceNotes:
              case Csconstants::ResearchNotes:
              case Csconstants::StaffNotes:
              case Csconstants::RelatedCollections:
              case Csconstants::KeyDescriptor:
              case Csconstants::WithinSiteProveniance:
              case Csconstants::SubspeciesDescriptiveName:
              case Csconstants::History:
              case Csconstants::Transcription:
              case Csconstants::CastAndCrew:
              case Csconstants::Synopsis:
              case Csconstants::Waterbody:
              case Csconstants::AssociatedSpecies:
              case Csconstants::Drainage:
              case Csconstants::ObjectUse:
              case Csconstants::StartingInstructions:
              case Csconstants::RegistrationNotes:
              case Csconstants::TitleStatusNotes:
              case Csconstants::RepairsMade:
              case Csconstants::CompletenessNote:
              case Csconstants::MovementMemo:
              case Csconstants::LocationAccessMemo:
              case Csconstants::LocationConditionMemo:
              case Csconstants::LocationSecurityMemo:
              case Csconstants::ObjectNameNote:
              case Csconstants::FieldCollectionMemo:
              case Csconstants::HabitatMemo:
              case Csconstants::StratigraphicUnitMemo:
                if(!empty($artObjData[$object_field])){ ?>
                  <p class="my-2">
                  <?php if($showFieldLabelNames==1){

                      $ObjectFieldsService = \Drupal::service('customize_object_detail_fields.object_fields_service');
                      $fieldLabel = $ObjectFieldsService->getObjectFieldLabelFromDatabase($object_field);
                      $value = $artObjData[$object_field];
                      $value = strip_tags($value);
                      $value = Html::decodeEntities($value);
                    ?>

                  <span class="object_detail_fieldlabel"><?php echo $fieldLabel ?>:</span>

                  <?php } ?>
                    <span class="mb-2 cstheme-show-more-richtext"><?php echo $value;?></span>
                  </p>
                  <?php }
                break;
              case Csconstants::UserDefinedRichText1:
              case Csconstants::UserDefinedRichText2:
              case Csconstants::UserDefinedRichText3:
              case Csconstants::UserDefinedRichText4:
              case Csconstants::UserDefinedRichText5:
              case Csconstants::UserDefinedRichText6:
              case Csconstants::UserDefinedRichText7:
              case Csconstants::UserDefinedRichText8:
              case Csconstants::UserDefinedRichText9:
              case Csconstants::UserDefinedRichText10:
              case Csconstants::UserDefinedRichText11:
              case Csconstants::UserDefinedRichText12:
              case Csconstants::UserDefinedRichText13:
              case Csconstants::UserDefinedRichText14:
              case Csconstants::UserDefinedRichText15:
              case Csconstants::UserDefinedRichText16:
              case Csconstants::UserDefinedRichText17:
              case Csconstants::UserDefinedRichText18:
                if(!empty($artObjData[$object_field])){ ?>
                  <p class="my-2">
                  <?php if($showFieldLabelNames==1){

                      $ObjectFieldsService = \Drupal::service('customize_object_detail_fields.object_fields_service');
                      $fieldLabel = $ObjectFieldsService->getObjectFieldLabelFromDatabase($object_field);
                      $value = $artObjData[$object_field];
                      $value = strip_tags($value);
                      $value = Html::decodeEntities($value);

                    ?>

                  <span class="object_detail_fieldlabel"><?php echo $fieldLabel ?>:</span>

                  <?php } ?>
                    <span class="mb-2 cstheme-show-more-richtext"><?php echo $value;?></span>
                  </p>
                  <?php }
                break;

              default:
                if(!empty($artObjData[$object_field])){ ?>
                    <p class="my-2">
                    <?php if($showFieldLabelNames==1){

                        $ObjectFieldsService = \Drupal::service('customize_object_detail_fields.object_fields_service');
                        $fieldLabel = $ObjectFieldsService->getObjectFieldLabelFromDatabase($object_field);
                        $value = $artObjData[$object_field];
                        $value = strip_tags($value);
                        $value = Html::decodeEntities($value);
                      ?>

                    <span class="object_detail_fieldlabel"><?php echo $fieldLabel ?>:</span>

                    <?php } ?>
                      <?php echo $value;  ?>
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
