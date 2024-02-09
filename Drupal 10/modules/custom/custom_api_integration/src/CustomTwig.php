<?php

namespace Drupal\custom_api_integration;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Drupal\custom_api_integration\Csconstants;


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
      new TwigFunction('getExhibitionObjectsListHtml', [$this, 'getExhibitionObjectsListHtml']),
      new TwigFunction('getGroupObjectsListHtml', [$this, 'getGroupObjectsListHtml']),



    ];
  }


  public function base64_encode($data){
    return base64_encode($data);
  }

  public function getObjectslistHtml($objItemList,$value=[], $dataOrderBy, $datapageNo, $dataSearch,$delaytm, $default_image_url){

    if (is_object($value)) {
        $value = get_object_vars($value);
    }

    $customized_fields = $this->getCommaSeparatedFieldsForListPage();
    // $customized_fields = 'Title, test'; //temp only

    //echo "field:" .$customized_fields;
    $customized_fields_array = explode(',', $customized_fields);
    ?>

    <div class="card col-lg-4 col-md-6 col-sm-6 col-12 mb-3 cs-object-list wow fadeInDown" data-wow-delay="<?php echo $delaytm; ?>">
                    <div class="card-body d-flex flex-column">
                        <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId'] ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)">
                                <?php

                                $object_img = !empty($value['main_image_attachment']) ? 'data:image/jpeg;base64,' . base64_encode($value['main_image_attachment']) : "";
                                $server_path = $value['main_image_path'];

                                $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $server_path);
                                $image_url = \Drupal::request()->getSchemeAndHttpHost() . "/" .  $relative_path;
                                if(empty($object_img) && empty($server_path)){
                                ?>
                                    <img class="img-fluid" src="<?php echo $default_image_url; ?>" alt=""/>
                                    <?php } else {
                                    if (empty($server_path)) {
                                    ?>
                                    <img class="img-fluid" src="<?php echo $object_img; ?>" alt=""/>
                                    <?php
                                    } else {
                                    ?>
                                    <img class="img-fluid" src="<?php echo $image_url; ?>" alt=""/>
                                    <?php
                                    }
                                    }
                                    ?>
                    </a>
                    </div>
                    <div class="card-footer text-muted">
                        <?php

            /*get first 3 array fields*/
            //$customized_fields_array = array_slice($customized_fields_array, 0, 3);
            foreach($customized_fields_array as $object_field)
            {
           switch($object_field)
            {
              case '':
                break;
              default:
              ?>
                <h6 class="font-normal" title="<?php echo $value['ArtistName']; ?>" >
                <small class="flex-fill">
                  <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value[$object_field]  ?></a>
                </small>
                </h6>
              <?php
              break;
            }
          } ?>
          </div>
        </div>
      <?php
    }


  public function customPaginationForTopLevelTabs($current_pageName,$requested_page,$total_records,$sortBy,$qSearch)
  {
    $sortBy =rawurlencode($sortBy);

    $request = \Drupal::request();
    $baseUrl = $request->getSchemeAndHttpHost() . '/' . $current_pageName;

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
    $listPageSize = 9;
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
      //  if($requested_page > 2 && $requested_page > $range+1 && $showitems < $pages) echo "<a href='".\Drupal::request()->getHost()."/".$current_page."?pageNo=1'>&laquo;</a>";
      //  if($requested_page > 1 && $showitems < $pages) echo "<a href='".\Drupal::request()->getHost()."/".$current_page."?pageNo=".($requested_page - 1)."'><i class='bi bi-chevron-left'></i></a>";

      if($requested_page != 1) echo "<a href='".$pagingUrl."&pageNo=".($requested_page - 1)."'><i class='bi bi-chevron-left'></i></a>";

        for ($i=1; $i <= $pages; $i++)
        {
            if (1 != $pages &&( !($i >= $requested_page+$showitems+1 || $i <= $requested_page-$showitems-1) || $pages <= $showitems ))
            {
                echo ($requested_page == $i)? "<span class='current'>".$i."</span>":"<a href='".$pagingUrl."&pageNo=".($i)."' class='inactive' >".$i."</a>";
            }
        }

        //if ($requested_page < $pages && $showitems < $pages) echo "<a href='".\Drupal::request()->getHost()."/".$current_page."?pageNo=".($requested_page + 1)."'><i class='bi bi-chevron-right'></i></a>";
        //if ($requested_page < $pages-1 &&  $requested_page+$range-1 < $pages && $showitems < $pages) echo "<a href='".\Drupal::request()->getHost()."/".$current_page."?pageNo=".($pages)."'>&raquo;</a>";

        if ($requested_page != $pages) echo "<a href='".$pagingUrl."&pageNo=".($pages)."'><i class='bi bi-chevron-right'></i></a>";

        echo "</div>\n";
    }
    }

    public function GetCustomizedObjectDetailsForTheme($object_field, $accountCustomizationData, $artObjData)
    {
      $site_url = \Drupal::request()->getSchemeAndHttpHost();

      $showFieldLabelNames =  \Drupal::config('custom_api_integration.settings')->get('show_field_labels');


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

              default:
                if(!empty($artObjData[$object_field])){ ?>
                    <p class="my-2">
                    <?php if($showFieldLabelNames==1){ ?>

                    <span class="object_detail_fieldlabel"><?php echo constant('Drupal\custom_api_integration\csconstants::' . $object_field) ?>:</span>

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
    // global $listPageSize;
    $listPageSize = 9;

    $showitems = $listPageSize;
    if(empty($requested_page)) $requested_page = 1;

    $pages = ceil($total_records / $listPageSize);
    if(!$pages)
    {
        $pages = 1;
    }

  if(1 != $pages)
  {
      if($requested_page != 1) echo "<a href='javascript:;' onclick=pagingForGroupLevelObjects('".$ajaxpage."',".$listPageSize.",".($requested_page - 1).")><i class='bi bi-chevron-left'></i></a>";

      for ($i=1; $i <= $pages; $i++)
      {
          if (1 != $pages &&( !($i >= $requested_page+$showitems+1 || $i <= $requested_page-$showitems-1) || $pages <= $showitems ))
          {
              echo ($requested_page == $i)? "<span class='current'>".$i."</span>":"<a href='javascript:;' onclick=pagingForGroupLevelObjects('".$ajaxpage."',".$listPageSize.",".($i).") class='inactive' >".$i."</a>";
          }
      }

      if ($requested_page != $pages) echo "<a href='javascript:;' onclick=pagingForGroupLevelObjects('".$ajaxpage."',".$listPageSize.",".($pages).")><i class='bi bi-chevron-right'></i></a>";

      echo "\n";
  }
  }

  public function getExhibitionObjectsListHtml($value, $dataOrderBy, $datapageNo, $dataSearch,$delaytm, $default_image_url){

    // $customized_fields = getCommaSeperatedFieldsForListPage();
    $customized_fields = 'Title,test'; //temp only

    if (is_array($value) || is_object($value)) {
      $value = is_object($value) ? get_object_vars($value) : $value;
    }

  $customized_fields_array = explode(',', $customized_fields);
  $site_url = \Drupal::request()->getSchemeAndHttpHost();

    ?>
   <div class="card col-lg-4 col-md-6 col-sm-6 col-12 mb-3 cs-object-list wow fadeInDown" data-wow-delay="<?php echo $delaytm; ?>">
                  <div class="card-body d-flex flex-column">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId'] ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)">
                              <?php $object_img = !empty($value['ObjectImage']) ? 'data:image/jpeg;base64,' . base64_encode($value['ObjectImage']) : "";
                              $server_path = $value['ObjectImagePath'];
                              $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $server_path);
                              $image_url = $site_url. "/". $relative_path;
                              if(empty($object_img) && empty($server_path)){
                              ?>
                                  <img class="img-fluid" src="<?php echo $default_image_url; ?>" alt=""/>
                                  <?php } else {
                                  if (empty($server_path)) {
                                  ?>
                                  <img class="img-fluid" src="<?php echo $object_img; ?>" alt=""/>
                                  <?php
                                  } else {
                                  ?>
                                  <img class="img-fluid" src="<?php echo $image_url; ?>" alt=""/>
                                  <?php
                                  }
                                  }
                                  ?>

                  </a>
                  </div>
                  <div class="card-footer text-muted">
                      <?php

          /*get first 3 array fields*/
          //$customized_fields_array = array_slice($customized_fields_array, 0, 3);
          foreach($customized_fields_array as $object_field)
          {
          //echo "field:" .$object_field;

         switch($object_field)
          {
            case csconstants::InventoryNumber:
              if(!empty($value['InventoryNumber'])){ ?>
              <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>,<?php echo $datapageNo ?>')">
                <h6 class="font-normal" title="<?php echo $value['InventoryNumber']; ?>" ><small class="flex-fill"><?php echo $value['InventoryNumber']  ?></small></h6>
              </a>
              <?php }

              break;

              case csconstants::NomenclatureObjectName:
              if(!empty($value['NomenclatureObjectName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NomenclatureObjectName']  ?></small></h6>
                <?php }
              break;

            case csconstants::ObjectStatus:
              if(!empty($value['ObjectStatus'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectStatus']  ?></small></h6>
              <?php }

              break;

            case csconstants::ObjectType:
              if(!empty($value['ObjectType'])){
              if(!empty($value['ObjectType']['ObjectTypeName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectType']['ObjectTypeName']  ?></small></h6>
                <?php }
                }
              break;

            case csconstants::LocationName:
              if(!empty($value['Location'])){
              if(!empty($value['Location']['LocationName'])){ ?>
                  <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Location']['LocationName']  ?></small></h6>
              <?php }
              }
            break;

            case csconstants::FullLocationName:
              if(!empty($value['Location'])){
              if(!empty($value['Location']['FullLocationName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Location']['FullLocationName']  ?></small></h6>
              <?php }
              }
            break;

            case csconstants::PermanentLocationName:
              if(!empty($value['PermanentLocation'])){
            if(!empty($value['PermanentLocation']['LocationName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PermanentLocation']['LocationName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::PermanentFullLocationName:
              if(!empty($value['PermanentLocation'])){
            if(!empty($value['PermanentLocation']['FullLocationName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PermanentLocation']['FullLocationName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CollectionName:
              if(!empty($value['Collection'])){
              if(!empty($value['Collection']['CollectionName'])){ ?>
                <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForCollection('<?php echo site_url() ?>', <?php echo $value['Collection']['CollectionId']; ?>)"><?php echo $value['Collection']['CollectionName']  ?></a></small></h6>
              <?php }
              }

              break;

            case csconstants::FullCollectionName:
              if(!empty($value['Collection'])){
              if(!empty($value['Collection']['FullCollectionName'])){ ?>
                <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForCollection('<?php echo site_url() ?>', <?php echo $value['Collection']['CollectionId']; ?>)"><?php echo $value['Collection']['FullCollectionName']  ?></a></small></h6>
              <?php }
              }

              break;

            case csconstants::CreditLine:
            if(!empty($value['CreditLine'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CreditLine']  ?></small></h6>
              <?php }

              break;

              case csconstants::ArtistName:
                if(!empty($value['Artist']) && isset($value['Artist'])){
              if(!empty($value['Artist']['ArtistName'])){ ?>
                    <h6 class="font-normal"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForArtist('<?php echo site_url() ?>', <?php echo $value['Artist']['ArtistId']; ?>)">
                      <?php echo $value['Artist']['ArtistName'] ?>
                    </a></small></h6>
                  <?php }
                }

                  break;

            case csconstants::AdditionalArtists:
            if(!empty($value['AdditionalArtists'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo implodeChildArrayProperty($value['AdditionalArtists'],"Artist","ArtistId","ArtistName");  ?></small></h6>
              <?php }

              break;

            case csconstants::Maker:
            if(!empty($value['Maker'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Maker']  ?></small></h6>
              <?php }

              break;

            case csconstants::Title:
            if(!empty($value['Title'])){ ?>
              <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>,<?php echo $datapageNo ?>')">
                <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><?php echo $value['Title']  ?></small></h6>
              </a>
              <?php }

              break;

            case csconstants::AlternateTitle:
            if(!empty($value['AlternateTitle'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AlternateTitle']  ?></small></h6>
              <?php }

              break;

            case csconstants::ObjectDate:
            if(!empty($value['ObjectDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectDate']  ?></small></h6>
              <?php }

              break;

            case csconstants::Medium:
            if(!empty($value['Medium'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Medium']  ?></small></h6>
              <?php }

              break;

            case csconstants::LocationStatus:
            if(!empty($value['LocationStatus'])){
            if(!empty($value['LocationStatus']['Term'])){ ?>
                  <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LocationStatus']['Term']  ?></small></h6>
                <?php }
              }

              break;

            case csconstants::InventoryDate:
            if(!empty($value['InventoryDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo date('m/d/Y',strtotime($value['InventoryDate']))  ?></small></h6>
              <?php }

              break;

            case csconstants::InventoryContactName:
              if(!empty($value['InventoryContact'])){
            if(!empty($value['InventoryContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['InventoryContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::Form:
            if(!empty($value['Form'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Form']  ?></small></h6>
              <?php }

              break;

            case csconstants::Subject:
            if(!empty($value['Subject'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Subject']  ?></small></h6>
              <?php }

              break;

            case csconstants::CategoryStyle:
            if(!empty($value['CategoryStyle'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CategoryStyle']  ?></small></h6>
              <?php }

              break;

            case csconstants::CountryOrigin:
            if(!empty($value['CountryOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CountryOrigin']  ?></small></h6>
               <?php }

              break;

            case csconstants::Edition:
            if(!empty($value['Edition'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Edition']  ?></small></h6>
              <?php }

              break;

            case csconstants::SuitePortfolio:
            if(!empty($value['SuitePortfolio'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SuitePortfolio']  ?></small></h6>
              <?php }

              break;

            case csconstants::CatalogRaisonne:
            if(!empty($value['CatalogRaisonne'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogRaisonne']  ?></small></h6>
              <?php }

              break;

            case csconstants::RFIDTagNumber:
            if(!empty($value['RFIDTagNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['RFIDTagNumber']  ?></small></h6>
              <?php }

              break;

            case csconstants::Term:
            if(!empty($value['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Term']  ?></small></h6>
              <?php }

              //art musueum fields
              break;

            case csconstants::CatalogNumber:
            if(!empty($value['CatalogNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::OtherNumbers:
            if(!empty($value['OtherNumbers'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OtherNumbers']  ?></small></h6>
              <?php }
              break;

            case csconstants::ItemCount:
            if(!empty($value['ItemCount'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ItemCount']  ?></small></h6>
              <?php }
              break;

            case csconstants::CatalogerContactName:
              if(!empty($value['CatalogerContact'])){
              if(!empty($value['CatalogerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CatalogDate:
            if(!empty($value['CatalogDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['CatalogDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::CollectionTitle:
            if(!empty($value['CollectionTitle'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectionTitle']  ?></small></h6>
              <?php }
              break;

            case csconstants::CollectionNumber:
            if(!empty($value['CollectionNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectionNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::Material:
            if(!empty($value['Material'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Material']  ?></small></h6>
              <?php }
              break;

            case csconstants::Technique:
            if(!empty($value['Technique'])){
            if(!empty($value['Technique']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Technique']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Color:
            if(!empty($value['Color'])){
            if(!empty($value['Color']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Color']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::StateOfOrigin:
            if(!empty($value['StateOfOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StateOfOrigin']  ?></small></h6>
              <?php }
              break;

            case csconstants::CountyOfOrigin:
            if(!empty($value['CountyOfOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CountyOfOrigin']  ?></small></h6>
              <?php }
              break;

            case csconstants::CityOfOrigin:
            if(!empty($value['CityOfOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CityOfOrigin']  ?></small></h6>
              <?php }
              break;

            case csconstants::State:
            if(!empty($value['State'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['State']  ?></small></h6>
              <?php }
              break;

            case csconstants::Duration:
            if(!empty($value['Duration'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Duration']  ?></small></h6>
              <?php }

              break;

            case csconstants::RevisedNomenclature:
            if(!empty($value['RevisedNomenclature'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['RevisedNomenclature']  ?></small></h6>
              <?php }
              break;

            case csconstants::PreviousCatalogNumber:
            if(!empty($value['PreviousCatalogNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PreviousCatalogNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::FieldSpecimenNumber:
            if(!empty($value['FieldSpecimenNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FieldSpecimenNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::StatusDate:
            if(!empty($value['StatusDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StatusDate']  ?></small></h6>
              <?php }
              break;

            case csconstants::StorageUnit:
            if(!empty($value['StorageUnit'])){
            if(!empty($value['StorageUnit']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StorageUnit']['Term']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CollectionDate:
            if(!empty($value['CollectionDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['CollectionDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::CollectorContactName:
              if(!empty($value['CollectorContact'])){
            if(!empty($value['CollectorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CollectorPlace:
            if(!empty($value['CollectorPlace'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectorPlace']  ?></small></h6>
              <?php }
              break;

            case csconstants::CatalogFolder:
              if(!empty($value['CatalogFolder'])){  ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogFolder'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::IdentifiedByContactName:
              if(!empty($value['IdentifiedByContact'])){
            if(!empty($value['IdentifiedByContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['IdentifiedByContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::IdentifiedDate:
            if(!empty($value['IdentifiedDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['IdentifiedDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::EminentFigureContactName:
            if(!empty($value['EminentFigureContact'])){
              if(!empty($value['EminentFigureContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EminentFigureContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::EminentOrganizationContactName:
              if(!empty($value['EminentOrganizationContact'])){
            if(!empty($value['EminentOrganizationContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EminentOrganizationContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ControlledProperty:
            if(!empty($value['ControlledProperty'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ControlledProperty'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::ArtistMakerName:
              if(!empty($value["ArtistMaker"])){
              if(!empty($value["ArtistMaker"]['ArtistName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value["ArtistMaker"]['ArtistName']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TaxonomicSerialNumber:
            if(!empty($value['TaxonomicSerialNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TaxonomicSerialNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::Kingdom:
            if(!empty($value['Kingdom'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Kingdom']  ?></small></h6>
              <?php }
              break;

            case csconstants::PhylumDivision:
            if(!empty($value['PhylumDivision'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PhylumDivision']  ?></small></h6>
              <?php }
              break;

            case csconstants::CSClass:
            if(!empty($value['Class'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Class']  ?></small></h6>
              <?php }
              break;

            case csconstants::Order:
            if(!empty($value['Order'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Order']  ?></small></h6>
              <?php }
              break;

            case csconstants::Family:
            if(!empty($value['Family'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Family']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubFamily:
            if(!empty($value['SubFamily'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubFamily']  ?></small></h6>
              <?php }
              break;

            case csconstants::ScientificName:
            if(!empty($value['ScientificName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ScientificName']  ?></small></h6>
              <?php }
              break;

            case csconstants::CommonName:
            if(!empty($value['CommonName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CommonName']  ?></small></h6>
              <?php }
              break;

            case csconstants::Species:
            if(!empty($value['Species'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Species']  ?></small></h6>
              <?php }
              break;

            case csconstants::SpeciesAuthorName:
              if(!empty($value['SpeciesAuthor'])){
              if(!empty($value['SpeciesAuthor']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SpeciesAuthor']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SpeciesAuthorDate:
            if(!empty($value['SpeciesAuthorDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['SpeciesAuthorDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::Subspecies:
            if(!empty($value['Subspecies'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Subspecies']  ?></small></h6>
              <?php }
              break;

              case csconstants::SubspeciesAuthorityContactName:
              if(!empty($value['SubspeciesAuthorityContact'])){
              if(!empty($value['SubspeciesAuthorityContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesAuthorityContact']['ContactName']  ?></small></h6>
                <?php }
              }
                break;

            case csconstants::SubspeciesAuthorName:
              if(!empty($value['SubspeciesAuthor'])){
            if(!empty($value['SubspeciesAuthor']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesAuthor']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SubspeciesAuthorDate:
            if(!empty($value['SubspeciesAuthorDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['SubspeciesAuthorDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesYear:
            if(!empty($value['SubspeciesYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesVariety:
            if(!empty($value['SubspeciesVariety'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesVariety']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesVarietyAuthorityContactName:
              if(!empty($value['SubspeciesVarietyAuthorityContact'])){
              if(!empty($value['SubspeciesVarietyAuthorityContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesVarietyAuthorityContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SubspeciesVarietyYear:
            if(!empty($value['SubspeciesVarietyYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesVarietyYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesForma:
            if(!empty($value['SubspeciesForma'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesForma']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesFormaAuthorityContactName:
              if(!empty($value['SubspeciesFormaAuthorityContact'])){
            if(!empty($value['SubspeciesFormaAuthorityContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesFormaAuthorityContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SubspeciesFormaYear:
            if(!empty($value['SubspeciesFormaYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesFormaYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::StudyNumber:
              if(!empty($value['StudyNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StudyNumber']  ?></small></h6>
                <?php }
                break;

            case csconstants::AlternateName:
            if(!empty($value['AlternateName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AlternateName']  ?></small></h6>
              <?php }
              break;

            case csconstants::CulturalID:
            if(!empty($value['CulturalID'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CulturalID']  ?></small></h6>
              <?php }
              break;

            case csconstants::CultureOfUse:
            if(!empty($value['CultureOfUse'])){
            if(!empty($value['CultureOfUse']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CultureOfUse']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ManufactureDate:
            if(!empty($value['ManufactureDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ManufactureDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::UseDate:
            if(!empty($value['UseDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UseDate']  ?></small></h6>
              <?php }
              break;

            case csconstants::TimePeriod:
            if(!empty($value['TimePeriod'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TimePeriod']  ?></small></h6>
              <?php }
              break;

            case csconstants::HistoricCulturalPeriod:
            if(!empty($value['HistoricCulturalPeriod'])){
            if(!empty($value['HistoricCulturalPeriod']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['HistoricCulturalPeriod']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ManufacturingTechnique:
            if(!empty($value['ManufacturingTechnique'])){
            if(!empty($value['ManufacturingTechnique']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ManufacturingTechnique']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Material:
            if(!empty($value['Material'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Material']  ?></small></h6>
              <?php }
              break;

            case csconstants::BroadClassOfMaterial:
            if(!empty($value['BroadClassOfMaterial'])){
            if(!empty($value['BroadClassOfMaterial']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BroadClassOfMaterial']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SpecificClassOfMaterial:
            if(!empty($value['SpecificClassOfMaterial'])){
            if(!empty($value['SpecificClassOfMaterial']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SpecificClassOfMaterial']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Quantity:
            if(!empty($value['Quantity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Quantity']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureCountry:
            if(!empty($value['PlaceOfManufactureCountry'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureCountry']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureState:
            if(!empty($value['PlaceOfManufactureState'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureState']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureCounty:
            if(!empty($value['PlaceOfManufactureCounty'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureCounty']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureCity:
            if(!empty($value['PlaceOfManufactureCity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureCity']  ?></small></h6>
              <?php }
              break;

            case csconstants::OtherManufacturingSite:
            if(!empty($value['OtherManufacturingSite'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OtherManufacturingSite']  ?></small></h6>
              <?php }
              break;

            case csconstants::Latitude:
            if(!empty($value['Latitude'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Latitude']  ?></small></h6>
              <?php }
              break;

            case csconstants::Longitude:
            if(!empty($value['Longitude'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Longitude']  ?></small></h6>
              <?php }
              break;

            case csconstants::UTMCoordinates:
            if(!empty($value['UTMCoordinates'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UTMCoordinates']  ?></small></h6>
              <?php }
              break;

            case csconstants::TownshipRangeSection:
            if(!empty($value['TownshipRangeSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TownshipRangeSection']  ?></small></h6>
              <?php }
              break;

            case csconstants::FieldSiteNumber:
            if(!empty($value['FieldSiteNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FieldSiteNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::StateSiteNumber:
            if(!empty($value['StateSiteNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StateSiteNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::SiteName:
            if(!empty($value['SiteName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SiteName']  ?></small></h6>
              <?php }
              break;

            case csconstants::SiteNumber:
            if(!empty($value['SiteNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SiteNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::DecorativeMotif:
            if(!empty($value['DecorativeMotif'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DecorativeMotif']  ?></small></h6>
              <?php }
              break;

            case csconstants::DecorativeTechnique:
            if(!empty($value['DecorativeTechnique'])){
            if(!empty($value['DecorativeTechnique']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DecorativeTechnique']['Term']  ?></small></h6>
                <?php }
              }

              break;

            case csconstants::Reproduction:
            if(!empty($value['Reproduction'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Reproduction']  ?></small></h6>
              <?php }
              break;

            case csconstants::ObjectForm:
              if(!empty($value['ObjectForm'])){
              if(!empty($value['ObjectForm']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectForm']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectPart:
            if(!empty($value['ObjectPart'])){
            if(!empty($value['ObjectPart']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectPart']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ComponentPart:
            if(!empty($value['ComponentPart'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ComponentPart']  ?></small></h6>
              <?php }
              break;

            case csconstants::Temper:
            if(!empty($value['Temper'])){
            if(!empty($value['Temper']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Temper']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TypeName:
            if(!empty($value['TypeName'])){
            if(!empty($value['TypeName']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeName']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SlideNumber:
            if(!empty($value['SlideNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SlideNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::BagNumber:
            if(!empty($value['BagNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BagNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::TotalBags:
            if(!empty($value['TotalBags'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TotalBags']  ?></small></h6>
              <?php }
              break;

            case csconstants::BoxNumber:
            if(!empty($value['BoxNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BoxNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::TotalBoxes:
            if(!empty($value['TotalBoxes'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TotalBoxes']  ?></small></h6>
              <?php }
              break;

            case csconstants::MakersMark:
            if(!empty($value['MakersMark'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MakersMark']  ?></small></h6>
              <?php }
              break;

            case csconstants::NAGPRA:
            if(!empty($value['NAGPRA'])){
            if(!empty($value['NAGPRA']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NAGPRA']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::OldNumber:
            if(!empty($value['OldNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OldNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::AdditionalAccessionNumber:
            if(!empty($value['AdditionalAccessionNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AdditionalAccessionNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::CatalogLevel:
            if(!empty($value['CatalogLevel'])){
            if(!empty($value['CatalogLevel']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogLevel']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::LevelOfControl:
            if(!empty($value['LevelOfControl'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LevelOfControl']  ?></small></h6>
              <?php }
              break;

            case csconstants::AlternateName:
            if(!empty($value['AlternateName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AlternateName']  ?></small></h6>
              <?php }
              break;

            case csconstants::AuthorName:
              if(!empty($value['Author'])){
            if(!empty($value['Author']['AuthorName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Author']['AuthorName']  ?></small></h6>
              <?php }
              }

              break;

            case csconstants::CreatorContactName:
              if(!empty($value['CreatorContact'])){
              if(!empty($value['CreatorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CreatorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ComposerContactName:
              if(!empty($value['ComposerContact'])){
            if(!empty($value['ComposerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ComposerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::NarratorContactName:
              if(!empty($value['NarratorContact'])){
            if(!empty($value['NarratorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NarratorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::EditorContactName:
              if(!empty($value['EditorContact'])){
            if(!empty($value['EditorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EditorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::PublisherContactName:
              if(!empty($value['PublisherContact'])){
            if(!empty($value['PublisherContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PublisherContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::IllustratorContactName:
              if(!empty($value['IllustratorContact'])){
            if(!empty($value['IllustratorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['IllustratorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ContributorContactName:
              if(!empty($value['ContributorContact'])){
            if(!empty($value['ContributorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ContributorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::StudioContactName:
              if(!empty($value['StudioContact'])){
            if(!empty($value['StudioContact'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StudioContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::DirectorContactName:
              if(!empty($value['DirectorContact'])){
            if(!empty($value['DirectorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DirectorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ArtDirectorContactName:
              if(!empty($value['ArtDirectorContact'])){
            if(!empty($value['ArtDirectorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ArtDirectorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ProducerContactName:
              if(!empty($value['ProducerContact'])){
            if(!empty($value['ProducerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ProducerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ProductionDesignerContactName:
              if(!empty($value['ProductionDesignerContact'])){
            if(!empty($value['ProductionDesignerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ProductionDesignerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ProductionCompanyContactName:
              if(!empty($value['ProductionCompanyContact'])){
            if(!empty($value['ProductionCompanyContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ProductionCompanyContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::DistributionCompany:
              if(!empty($value['DistributionCompany'])){
            if(!empty($value['DistributionCompany']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DistributionCompany']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::WriterContactName:
              if(!empty($value['WriterContact'])){
            if(!empty($value['WriterContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['WriterContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CinematographerContactName:
              if(!empty($value['CinematographerContact'])){
            if(!empty($value['CinematographerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CinematographerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::PhotographyContactName:
              if(!empty($value['PhotographyContact'])){
            if(!empty($value['PhotographyContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PhotographyContact']['ContactName']  ?></small></h6>
              <?php }
              }

              break;

            case csconstants::PublisherLocation:
            if(!empty($value['PublisherLocation'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PublisherLocation']  ?></small></h6>
              <?php }
              break;

            case csconstants::Event:
            if(!empty($value['Event'])){
            if(!empty($value['Event']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Event']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::PeopleContent:
            if(!empty($value['PeopleContent'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PeopleContent']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceContent:
            if(!empty($value['PlaceContent'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceContent']  ?></small></h6>
              <?php }
              break;

            case csconstants::TownshipRangeSection:
            if(!empty($value['TownshipRangeSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TownshipRangeSection']  ?></small></h6>
              <?php }
              break;

            case csconstants::ISBN:
            if(!empty($value['ISBN'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ISBN']  ?></small></h6>
              <?php }
              break;

            case csconstants::ISSN:
            if(!empty($value['ISSN'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ISSN']  ?></small></h6>
              <?php }
              break;

            case csconstants::CallNumber:
            if(!empty($value['CallNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CallNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::CoverType:
            if(!empty($value['CoverType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CoverType']  ?></small></h6>
              <?php }
              break;

            case csconstants::TypeOfBinding:
            if(!empty($value['TypeOfBinding'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeOfBinding']  ?></small></h6>
              <?php }
              break;

            case csconstants::Language:
            if(!empty($value['Language'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Language']  ?></small></h6>
              <?php }
              break;

            case csconstants::NumberOfPages:
            if(!empty($value['NumberOfPages'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NumberOfPages']  ?></small></h6>
              <?php }
              break;

            case csconstants::NegativeNumber:
            if(!empty($value['NegativeNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NegativeNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::FilmSize:
            if(!empty($value['FilmSize'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FilmSize']  ?></small></h6>
              <?php }
              break;

            case csconstants::Process:
            if(!empty($value['Process'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Process']  ?></small></h6>
              <?php }
              break;

            case csconstants::ImageNumber:
            if(!empty($value['ImageNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ImageNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::ImageRights:
            if(!empty($value['ImageRights'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ImageRights']  ?></small></h6>
              <?php }
              break;

            case csconstants::Copyrights:
            if(!empty($value['Copyrights'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Copyrights']  ?></small></h6>
              <?php }
              break;

            case csconstants::FindingAids:
            if(!empty($value['FindingAids'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FindingAids']  ?></small></h6>
              <?php }
              break;

            case csconstants::VolumeNumber:
            if(!empty($value['VolumeNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['VolumeNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::CompletionYear:
            if(!empty($value['CompletionYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CompletionYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::Format:
            if(!empty($value['Format'])){
            if(!empty($value['Format']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Format']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Genre:
            if(!empty($value['Genre'])){
            if(!empty($value['Genre']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Genre']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Subgenre:
            if(!empty($value['Subgenre'])){
            if(!empty($value['Subgenre']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Subgenre']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ReleaseDate:
            if(!empty($value['ReleaseDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ReleaseDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::ProductionDate:
            if(!empty($value['ProductionDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ProductionDate']))  ?></small></h6>
              <?php }


              break;

            case csconstants::Genus:
            if(!empty($value['Genus'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Genus']  ?></small></h6>
              <?php }
              break;

            case csconstants::Stage:
            if(!empty($value['Stage'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Stage']  ?></small></h6>
              <?php }
              break;

            case csconstants::Section:
            if(!empty($value['Section'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Section']  ?></small></h6>
              <?php }
              break;

            case csconstants::QuarterSection:
            if(!empty($value['QuarterSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['QuarterSection']  ?></small></h6>
              <?php }
              break;

            case csconstants::Age:
            if(!empty($value['Age'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Age']  ?></small></h6>
              <?php }
              break;

            case csconstants::Locality:
            if(!empty($value['Locality'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Locality']  ?></small></h6>
              <?php }
              break;

            case csconstants::HabitatCommunity:
            if(!empty($value['HabitatCommunity'])){
            if(!empty($value['HabitatCommunity']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['HabitatCommunity']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TypeSpecimen:
            if(!empty($value['TypeSpecimen'])){
            if(!empty($value['TypeSpecimen']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeSpecimen']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Sex:
            if(!empty($value['Sex'])){
            if(!empty($value['Sex']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Sex']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ExoticNative:
            if(!empty($value['ExoticNative'])){
            if(!empty($value['ExoticNative']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ExoticNative']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TaxonomicNotes:
            if(!empty($value['TaxonomicNotes'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TaxonomicNotes']  ?></small></h6>
              <?php }
              break;

            case csconstants::Rare:
            if(!empty($value['Rare'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Rare']  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredDate:
            if(!empty($value['ThreatenedEndangeredDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ThreatenedEndangeredDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredSpeciesSynonym:
            if(!empty($value['ThreatenedEndangeredSpeciesSynonym'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThreatenedEndangeredSpeciesSynonym'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredSpeciesSynonymName:
            if(!empty($value['ThreatenedEndangeredSpeciesSynonymName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThreatenedEndangeredSpeciesSynonymName']  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredSpeciesStatus:
            if(!empty($value['ThreatenedEndangeredSpeciesStatus'])){
            if(!empty($value['ThreatenedEndangeredSpeciesStatus']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThreatenedEndangeredSpeciesStatus']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SubspeciesSynonym:
              if(!empty($value['SubspeciesSynonym'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesSynonym']  ?></small></h6>
                <?php }
                break;

            case csconstants::ContinentWorldRegion:
              if(!empty($value['ContinentWorldRegion'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ContinentWorldRegion']  ?></small></h6>
                <?php }
                break;

            case csconstants::ReproductionMethod:
            if(!empty($value['ReproductionMethod'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ReproductionMethod']  ?></small></h6>
              <?php }
              break;

            case csconstants::ReferenceDatum:
            if(!empty($value['ReferenceDatum'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ReferenceDatum']  ?></small></h6>
              <?php }
              break;

            case csconstants::Aspect:
            if(!empty($value['Aspect'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Aspect']  ?></small></h6>
              <?php }
              break;

            case csconstants::FormationPeriodSubstrate:
            if(!empty($value['FormationPeriodSubstrate'])){
                if(!empty($value['FormationPeriodSubstrate']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FormationPeriodSubstrate']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SoilType:
            if(!empty($value['SoilType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SoilType']  ?></small></h6>
              <?php }
              break;

            case csconstants::Slope:
            if(!empty($value['Slope'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Slope']  ?></small></h6>
              <?php }
              break;

            case csconstants::Unit:
            if(!empty($value['Unit'])){
            if(!empty($value['Unit']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Unit']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::DepthInMeters:
            if(!empty($value['DepthInMeters'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DepthInMeters']  ?></small></h6>
              <?php }
              break;

            case csconstants::ElevationInMeters:
            if(!empty($value['ElevationInMeters'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ElevationInMeters']  ?></small></h6>
              <?php }

              break;

            case csconstants::EthnologyCulture:
            if(!empty($value['EthnologyCulture'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EthnologyCulture']  ?></small></h6>
              <?php }
              break;

            case csconstants::Alternate1EthnologyCulture:
            if(!empty($value['Alternate1EthnologyCulture'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Alternate1EthnologyCulture']  ?></small></h6>
              <?php }
              break;

            case csconstants::Alternate2EthnologyCulture:
            if(!empty($value['Alternate2EthnologyCulture'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Alternate2EthnologyCulture']  ?></small></h6>
              <?php }
              break;

            case csconstants::AboriginalName:
            if(!empty($value['AboriginalName'])){
            if(!empty($value['AboriginalName']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AboriginalName']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::AdditionalArea:
            if(!empty($value['AdditionalArea'])){
            if(!empty($value['AdditionalArea']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AdditionalArea']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::AdditionalGroup:
            if(!empty($value['AdditionalGroup'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AdditionalGroup']  ?></small></h6>
              <?php }
              break;

            case csconstants::DescriptiveName:
            if(!empty($value['DescriptiveName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DescriptiveName']  ?></small></h6>
              <?php }
              break;

            case csconstants::PeriodSystem:
            if(!empty($value['PeriodSystem'])){
            if(!empty($value['PeriodSystem']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PeriodSystem']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::EpochSeries:
            if(!empty($value['EpochSeries'])){
            if(!empty($value['EpochSeries']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EpochSeries']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::AgeStage:
            if(!empty($value['AgeStage'])){
            if(!empty($value['AgeStage']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AgeStage']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Composition:
            if(!empty($value['Composition'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Composition']  ?></small></h6>
              <?php }
              break;

            case csconstants::StrunzClass:
            if(!empty($value['StrunzClass'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StrunzClass']  ?></small></h6>
              <?php }
              break;

            case csconstants::StrunzDivision:
            if(!empty($value['StrunzDivision'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StrunzDivision']  ?></small></h6>
              <?php }
              break;

            case csconstants::StrunzID:
            if(!empty($value['StrunzID'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StrunzID']  ?></small></h6>
              <?php }
              break;

            case csconstants::LithologyPedotype:
            if(!empty($value['LithologyPedotype'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LithologyPedotype']  ?></small></h6>
              <?php }
              break;

            case csconstants::Formation:
            if(!empty($value['Formation'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Formation']  ?></small></h6>
              <?php }
              break;

            case csconstants::VerticalDatum:
            if(!empty($value['VerticalDatum'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['VerticalDatum']  ?></small></h6>
              <?php }
              break;

            case csconstants::Datum:
            if(!empty($value['Datum'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Datum']  ?></small></h6>
              <?php }
              break;

            case csconstants::DepositionalEnvironment:
            if(!empty($value['DepositionalEnvironment'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DepositionalEnvironment']  ?></small></h6>
              <?php }
              break;

            case csconstants::Member:
            if(!empty($value['Member'])){
            if(!empty($value['Member']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Member']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::GeoUnit:
            if(!empty($value['GeoUnit'])){
            if(!empty($value['GeoUnit']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['GeoUnit']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ThinSection:
            if(!empty($value['ThinSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThinSection'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::PatentDate:
            if(!empty($value['PatentDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PatentDate']  ?></small></h6>
              <?php }
              break;

            case csconstants::Copyright:
              if(!empty($value['Copyright'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Copyright']  ?></small></h6>
                <?php }
                break;

            case csconstants::School:
              if(!empty($value['School'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['School']  ?></small></h6>
                <?php }
                break;

            case csconstants::Lithology:
            if(!empty($value['Lithology'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Lithology']  ?></small></h6>
              <?php }
              break;

            case csconstants::Horizon:
            if(!empty($value['Horizon'])){
            if(!empty($value['Horizon']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Horizon']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::InsituFloat:
            if(!empty($value['InsituFloat'])){
            if(!empty($value['InsituFloat']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['InsituFloat']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Taphonomy:
            if(!empty($value['Taphonomy'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Taphonomy']  ?></small></h6>
              <?php }
              break;

            case csconstants::Model:
            if(!empty($value['Model'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Model']  ?></small></h6>
              <?php }
              break;

            case csconstants::Stones:
            if(!empty($value['Stones'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Stones']  ?></small></h6>
              <?php }
              break;

            case csconstants::Karats:
            if(!empty($value['Karats'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Karats']  ?></small></h6>
              <?php }
              break;

            case csconstants::Carats:
            if(!empty($value['Carats'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Carats']  ?></small></h6>
              <?php }
              break;

            case csconstants::Cut:
            if(!empty($value['Cut'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Cut']  ?></small></h6>
              <?php }
              break;

            case csconstants::Clarity:
            if(!empty($value['Clarity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Clarity']  ?></small></h6>
              <?php }
              break;

            case csconstants::TypeOfGemstone:
            if(!empty($value['TypeOfGemstone'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeOfGemstone']  ?></small></h6>
              <?php }
              break;

            case csconstants::Size:
            if(!empty($value['Size'])){
            if(!empty($value['Size']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Size']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::MetalType:
            if(!empty($value['MetalType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MetalType']  ?></small></h6>
              <?php }
              break;

            case csconstants::DrivenBy:
            if(!empty($value['DrivenBy'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DrivenBy']  ?></small></h6>
              <?php }
              break;

            case csconstants::VIN:
            if(!empty($value['VIN'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['VIN']  ?></small></h6>
              <?php }
              break;

            case csconstants::ChassisNumber:
            if(!empty($value['ChassisNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ChassisNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::Mileage:
            if(!empty($value['Mileage'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Mileage']  ?></small></h6>
              <?php }
              break;

            case csconstants::Power:
            if(!empty($value['Power'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Power']  ?></small></h6>
              <?php }
              break;

            case csconstants::EngineType:
            if(!empty($value['EngineType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EngineType']  ?></small></h6>
              <?php }
              break;

            case csconstants::EnginePosition:
            if(!empty($value['EnginePosition'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EnginePosition']  ?></small></h6>
              <?php }
              break;

            case csconstants::Transmission:
            if(!empty($value['Transmission'])){
            if(!empty($value['Transmission']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Transmission']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Passengers:
            if(!empty($value['Passengers'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Passengers']  ?></small></h6>
              <?php }
              break;

            case csconstants::FuelHighway:
            if(!empty($value['FuelHighway'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FuelHighway']  ?></small></h6>
              <?php }
              break;

            case csconstants::Acceleration:
            if(!empty($value['Acceleration'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Acceleration']  ?></small></h6>
              <?php }
              break;

            case csconstants::TopSpeed:
            if(!empty($value['TopSpeed'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TopSpeed']  ?></small></h6>
              <?php }
              break;

            case csconstants::EngineNumber:
            if(!empty($value['EngineNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EngineNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::LicensePlateNumber:
            if(!empty($value['LicensePlateNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LicensePlateNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::TransmissionFluid:
            if(!empty($value['TransmissionFluid'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TransmissionFluid']  ?></small></h6>
              <?php }
              break;

            case csconstants::BrakeFluid:
            if(!empty($value['BrakeFluid'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BrakeFluid']  ?></small></h6>
              <?php }
              break;

            case csconstants::OilType:
            if(!empty($value['OilType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OilType']  ?></small></h6>
              <?php }
              break;

            case csconstants::FuelType:
            if(!empty($value['FuelType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FuelType']  ?></small></h6>
              <?php }
              break;

            case csconstants::RegistrationStatus:
            if(!empty($value['RegistrationStatus'])){
            if(!empty($value['RegistrationStatus']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['RegistrationStatus']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TitleStatus:
            if(!empty($value['TitleStatus'])){
            if(!empty($value['TitleStatus']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TitleStatus']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Paint:
            if(!empty($value['Paint'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Paint']  ?></small></h6>
              <?php }
              break;

            case csconstants::Battery:
            if(!empty($value['Battery'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Battery']  ?></small></h6>
              <?php }
              break;

            case csconstants::ShiftPattern:
            if(!empty($value['ShiftPattern'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ShiftPattern']  ?></small></h6>
              <?php }
              break;

            case csconstants::DashLayout:
            if(!empty($value['DashLayout'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DashLayout']  ?></small></h6>
              <?php }
              break;

            case csconstants::TypeOfWine:
            if(!empty($value['TypeOfWine'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeOfWine']  ?></small></h6>
              <?php }
              break;

            case csconstants::Maturity:
            if(!empty($value['Maturity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Maturity']  ?></small></h6>
              <?php }
              break;

            case csconstants::Grape:
            if(!empty($value['Grape'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Grape']  ?></small></h6>
              <?php }
              break;

            case csconstants::Region:
            if(!empty($value['Region'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Region']  ?></small></h6>
              <?php }
              break;

            case csconstants::BottleSize:
            if(!empty($value['BottleSize'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BottleSize']  ?></small></h6>
              <?php }
              break;

            case csconstants::FermentationPeriod:
            if(!empty($value['FermentationPeriod'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FermentationPeriod']  ?></small></h6>
              <?php }
              break;

            case csconstants::DesignerName:
              if(!empty($value['Designer'])){
            if(!empty($value['Designer']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Designer']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::Brand:
            if(!empty($value['Brand'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Brand']  ?></small></h6>
              <?php }
              break;

            case csconstants::FabricMaterial:
            if(!empty($value['FabricMaterial'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FabricMaterial']  ?></small></h6>
              <?php }
              break;

            case csconstants::SKU:
            if(!empty($value['SKU'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SKU']  ?></small></h6>
              <?php }
              break;case csconstants::InventoryNumber:
              if(!empty($value['InventoryNumber'])){ ?>
              <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>,<?php echo $datapageNo ?>')">
                <h6 class="font-normal" title="<?php echo $value['InventoryNumber']; ?>" ><small class="flex-fill"><?php echo $value['InventoryNumber']  ?></small></h6>
              </a>
              <?php }

              break;

              case csconstants::NomenclatureObjectName:
              if(!empty($value['NomenclatureObjectName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NomenclatureObjectName']  ?></small></h6>
                <?php }
              break;

            case csconstants::ObjectStatus:
              if(!empty($value['ObjectStatus'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectStatus']  ?></small></h6>
              <?php }

              break;

            case csconstants::ObjectType:
              if(!empty($value['ObjectType'])){
              if(!empty($value['ObjectType']['ObjectTypeName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectType']['ObjectTypeName']  ?></small></h6>
                <?php }
                }
              break;

            case csconstants::LocationName:
              if(!empty($value['Location'])){
              if(!empty($value['Location']['LocationName'])){ ?>
                  <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Location']['LocationName']  ?></small></h6>
              <?php }
              }
            break;

            case csconstants::FullLocationName:
              if(!empty($value['Location'])){
              if(!empty($value['Location']['FullLocationName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Location']['FullLocationName']  ?></small></h6>
              <?php }
              }
            break;

            case csconstants::PermanentLocationName:
              if(!empty($value['PermanentLocation'])){
            if(!empty($value['PermanentLocation']['LocationName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PermanentLocation']['LocationName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::PermanentFullLocationName:
              if(!empty($value['PermanentLocation'])){
            if(!empty($value['PermanentLocation']['FullLocationName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PermanentLocation']['FullLocationName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CollectionName:
              if(!empty($value['Collection'])){
              if(!empty($value['Collection']['CollectionName'])){ ?>
                <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForCollection('<?php echo site_url() ?>', <?php echo $value['Collection']['CollectionId']; ?>)"><?php echo $value['Collection']['CollectionName']  ?></a></small></h6>
              <?php }
              }

              break;

            case csconstants::FullCollectionName:
              if(!empty($value['Collection'])){
              if(!empty($value['Collection']['FullCollectionName'])){ ?>
                <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForCollection('<?php echo site_url() ?>', <?php echo $value['Collection']['CollectionId']; ?>)"><?php echo $value['Collection']['FullCollectionName']  ?></a></small></h6>
              <?php }
              }

              break;

            case csconstants::CreditLine:
            if(!empty($value['CreditLine'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CreditLine']  ?></small></h6>
              <?php }

              break;

              case csconstants::ArtistName:
                if(!empty($value['Artist']) && isset($value['Artist'])){
              if(!empty($value['Artist']['ArtistName'])){ ?>
                    <h6 class="font-normal"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForArtist('<?php echo site_url() ?>', <?php echo $value['Artist']['ArtistId']; ?>)">
                      <?php echo $value['Artist']['ArtistName'] ?>
                    </a></small></h6>
                  <?php }
                }

                  break;

            case csconstants::AdditionalArtists:
            if(!empty($value['AdditionalArtists'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo implodeChildArrayProperty($value['AdditionalArtists'],"Artist","ArtistId","ArtistName");  ?></small></h6>
              <?php }

              break;

            case csconstants::Maker:
            if(!empty($value['Maker'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Maker']  ?></small></h6>
              <?php }

              break;

            case csconstants::Title:
            if(!empty($value['Title'])){ ?>
              <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>,<?php echo $datapageNo ?>')">
                <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><?php echo $value['Title']  ?></small></h6>
              </a>
              <?php }

              break;

            case csconstants::AlternateTitle:
            if(!empty($value['AlternateTitle'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AlternateTitle']  ?></small></h6>
              <?php }

              break;

            case csconstants::ObjectDate:
            if(!empty($value['ObjectDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectDate']  ?></small></h6>
              <?php }

              break;

            case csconstants::Medium:
            if(!empty($value['Medium'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Medium']  ?></small></h6>
              <?php }

              break;

            case csconstants::LocationStatus:
            if(!empty($value['LocationStatus'])){
            if(!empty($value['LocationStatus']['Term'])){ ?>
                  <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LocationStatus']['Term']  ?></small></h6>
                <?php }
              }

              break;

            case csconstants::InventoryDate:
            if(!empty($value['InventoryDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo date('m/d/Y',strtotime($value['InventoryDate']))  ?></small></h6>
              <?php }

              break;

            case csconstants::InventoryContactName:
              if(!empty($value['InventoryContact'])){
            if(!empty($value['InventoryContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['InventoryContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::Form:
            if(!empty($value['Form'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Form']  ?></small></h6>
              <?php }

              break;

            case csconstants::Subject:
            if(!empty($value['Subject'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Subject']  ?></small></h6>
              <?php }

              break;

            case csconstants::CategoryStyle:
            if(!empty($value['CategoryStyle'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CategoryStyle']  ?></small></h6>
              <?php }

              break;

            case csconstants::CountryOrigin:
            if(!empty($value['CountryOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CountryOrigin']  ?></small></h6>
               <?php }

              break;

            case csconstants::Edition:
            if(!empty($value['Edition'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Edition']  ?></small></h6>
              <?php }

              break;

            case csconstants::SuitePortfolio:
            if(!empty($value['SuitePortfolio'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SuitePortfolio']  ?></small></h6>
              <?php }

              break;

            case csconstants::CatalogRaisonne:
            if(!empty($value['CatalogRaisonne'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogRaisonne']  ?></small></h6>
              <?php }

              break;

            case csconstants::RFIDTagNumber:
            if(!empty($value['RFIDTagNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['RFIDTagNumber']  ?></small></h6>
              <?php }

              break;

            case csconstants::Term:
            if(!empty($value['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Term']  ?></small></h6>
              <?php }

              //art musueum fields
              break;

            case csconstants::CatalogNumber:
            if(!empty($value['CatalogNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::OtherNumbers:
            if(!empty($value['OtherNumbers'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OtherNumbers']  ?></small></h6>
              <?php }
              break;

            case csconstants::ItemCount:
            if(!empty($value['ItemCount'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ItemCount']  ?></small></h6>
              <?php }
              break;

            case csconstants::CatalogerContactName:
              if(!empty($value['CatalogerContact'])){
              if(!empty($value['CatalogerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CatalogDate:
            if(!empty($value['CatalogDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['CatalogDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::CollectionTitle:
            if(!empty($value['CollectionTitle'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectionTitle']  ?></small></h6>
              <?php }
              break;

            case csconstants::CollectionNumber:
            if(!empty($value['CollectionNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectionNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::Material:
            if(!empty($value['Material'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Material']  ?></small></h6>
              <?php }
              break;

            case csconstants::Technique:
            if(!empty($value['Technique'])){
            if(!empty($value['Technique']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Technique']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Color:
            if(!empty($value['Color'])){
            if(!empty($value['Color']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Color']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::StateOfOrigin:
            if(!empty($value['StateOfOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StateOfOrigin']  ?></small></h6>
              <?php }
              break;

            case csconstants::CountyOfOrigin:
            if(!empty($value['CountyOfOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CountyOfOrigin']  ?></small></h6>
              <?php }
              break;

            case csconstants::CityOfOrigin:
            if(!empty($value['CityOfOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CityOfOrigin']  ?></small></h6>
              <?php }
              break;

            case csconstants::State:
            if(!empty($value['State'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['State']  ?></small></h6>
              <?php }
              break;

            case csconstants::Duration:
            if(!empty($value['Duration'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Duration']  ?></small></h6>
              <?php }

              break;

            case csconstants::RevisedNomenclature:
            if(!empty($value['RevisedNomenclature'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['RevisedNomenclature']  ?></small></h6>
              <?php }
              break;

            case csconstants::PreviousCatalogNumber:
            if(!empty($value['PreviousCatalogNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PreviousCatalogNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::FieldSpecimenNumber:
            if(!empty($value['FieldSpecimenNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FieldSpecimenNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::StatusDate:
            if(!empty($value['StatusDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StatusDate']  ?></small></h6>
              <?php }
              break;

            case csconstants::StorageUnit:
            if(!empty($value['StorageUnit'])){
            if(!empty($value['StorageUnit']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StorageUnit']['Term']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CollectionDate:
            if(!empty($value['CollectionDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['CollectionDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::CollectorContactName:
              if(!empty($value['CollectorContact'])){
            if(!empty($value['CollectorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CollectorPlace:
            if(!empty($value['CollectorPlace'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectorPlace']  ?></small></h6>
              <?php }
              break;

            case csconstants::CatalogFolder:
              if(!empty($value['CatalogFolder'])){  ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogFolder'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::IdentifiedByContactName:
              if(!empty($value['IdentifiedByContact'])){
            if(!empty($value['IdentifiedByContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['IdentifiedByContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::IdentifiedDate:
            if(!empty($value['IdentifiedDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['IdentifiedDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::EminentFigureContactName:
            if(!empty($value['EminentFigureContact'])){
              if(!empty($value['EminentFigureContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EminentFigureContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::EminentOrganizationContactName:
              if(!empty($value['EminentOrganizationContact'])){
            if(!empty($value['EminentOrganizationContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EminentOrganizationContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ControlledProperty:
            if(!empty($value['ControlledProperty'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ControlledProperty'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::ArtistMakerName:
              if(!empty($value["ArtistMaker"])){
              if(!empty($value["ArtistMaker"]['ArtistName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value["ArtistMaker"]['ArtistName']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TaxonomicSerialNumber:
            if(!empty($value['TaxonomicSerialNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TaxonomicSerialNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::Kingdom:
            if(!empty($value['Kingdom'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Kingdom']  ?></small></h6>
              <?php }
              break;

            case csconstants::PhylumDivision:
            if(!empty($value['PhylumDivision'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PhylumDivision']  ?></small></h6>
              <?php }
              break;

            case csconstants::CSClass:
            if(!empty($value['Class'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Class']  ?></small></h6>
              <?php }
              break;

            case csconstants::Order:
            if(!empty($value['Order'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Order']  ?></small></h6>
              <?php }
              break;

            case csconstants::Family:
            if(!empty($value['Family'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Family']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubFamily:
            if(!empty($value['SubFamily'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubFamily']  ?></small></h6>
              <?php }
              break;

            case csconstants::ScientificName:
            if(!empty($value['ScientificName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ScientificName']  ?></small></h6>
              <?php }
              break;

            case csconstants::CommonName:
            if(!empty($value['CommonName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CommonName']  ?></small></h6>
              <?php }
              break;

            case csconstants::Species:
            if(!empty($value['Species'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Species']  ?></small></h6>
              <?php }
              break;

            case csconstants::SpeciesAuthorName:
              if(!empty($value['SpeciesAuthor'])){
              if(!empty($value['SpeciesAuthor']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SpeciesAuthor']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SpeciesAuthorDate:
            if(!empty($value['SpeciesAuthorDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['SpeciesAuthorDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::Subspecies:
            if(!empty($value['Subspecies'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Subspecies']  ?></small></h6>
              <?php }
              break;

              case csconstants::SubspeciesAuthorityContactName:
              if(!empty($value['SubspeciesAuthorityContact'])){
              if(!empty($value['SubspeciesAuthorityContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesAuthorityContact']['ContactName']  ?></small></h6>
                <?php }
              }
                break;

            case csconstants::SubspeciesAuthorName:
              if(!empty($value['SubspeciesAuthor'])){
            if(!empty($value['SubspeciesAuthor']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesAuthor']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SubspeciesAuthorDate:
            if(!empty($value['SubspeciesAuthorDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['SubspeciesAuthorDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesYear:
            if(!empty($value['SubspeciesYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesVariety:
            if(!empty($value['SubspeciesVariety'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesVariety']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesVarietyAuthorityContactName:
              if(!empty($value['SubspeciesVarietyAuthorityContact'])){
              if(!empty($value['SubspeciesVarietyAuthorityContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesVarietyAuthorityContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SubspeciesVarietyYear:
            if(!empty($value['SubspeciesVarietyYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesVarietyYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesForma:
            if(!empty($value['SubspeciesForma'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesForma']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesFormaAuthorityContactName:
              if(!empty($value['SubspeciesFormaAuthorityContact'])){
            if(!empty($value['SubspeciesFormaAuthorityContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesFormaAuthorityContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SubspeciesFormaYear:
            if(!empty($value['SubspeciesFormaYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesFormaYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::StudyNumber:
              if(!empty($value['StudyNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StudyNumber']  ?></small></h6>
                <?php }
                break;

            case csconstants::AlternateName:
            if(!empty($value['AlternateName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AlternateName']  ?></small></h6>
              <?php }
              break;

            case csconstants::CulturalID:
            if(!empty($value['CulturalID'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CulturalID']  ?></small></h6>
              <?php }
              break;

            case csconstants::CultureOfUse:
            if(!empty($value['CultureOfUse'])){
            if(!empty($value['CultureOfUse']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CultureOfUse']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ManufactureDate:
            if(!empty($value['ManufactureDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ManufactureDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::UseDate:
            if(!empty($value['UseDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UseDate']  ?></small></h6>
              <?php }
              break;

            case csconstants::TimePeriod:
            if(!empty($value['TimePeriod'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TimePeriod']  ?></small></h6>
              <?php }
              break;

            case csconstants::HistoricCulturalPeriod:
            if(!empty($value['HistoricCulturalPeriod'])){
            if(!empty($value['HistoricCulturalPeriod']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['HistoricCulturalPeriod']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ManufacturingTechnique:
            if(!empty($value['ManufacturingTechnique'])){
            if(!empty($value['ManufacturingTechnique']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ManufacturingTechnique']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Material:
            if(!empty($value['Material'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Material']  ?></small></h6>
              <?php }
              break;

            case csconstants::BroadClassOfMaterial:
            if(!empty($value['BroadClassOfMaterial'])){
            if(!empty($value['BroadClassOfMaterial']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BroadClassOfMaterial']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SpecificClassOfMaterial:
            if(!empty($value['SpecificClassOfMaterial'])){
            if(!empty($value['SpecificClassOfMaterial']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SpecificClassOfMaterial']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Quantity:
            if(!empty($value['Quantity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Quantity']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureCountry:
            if(!empty($value['PlaceOfManufactureCountry'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureCountry']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureState:
            if(!empty($value['PlaceOfManufactureState'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureState']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureCounty:
            if(!empty($value['PlaceOfManufactureCounty'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureCounty']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureCity:
            if(!empty($value['PlaceOfManufactureCity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureCity']  ?></small></h6>
              <?php }
              break;

            case csconstants::OtherManufacturingSite:
            if(!empty($value['OtherManufacturingSite'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OtherManufacturingSite']  ?></small></h6>
              <?php }
              break;

            case csconstants::Latitude:
            if(!empty($value['Latitude'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Latitude']  ?></small></h6>
              <?php }
              break;

            case csconstants::Longitude:
            if(!empty($value['Longitude'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Longitude']  ?></small></h6>
              <?php }
              break;

            case csconstants::UTMCoordinates:
            if(!empty($value['UTMCoordinates'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UTMCoordinates']  ?></small></h6>
              <?php }
              break;

            case csconstants::TownshipRangeSection:
            if(!empty($value['TownshipRangeSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TownshipRangeSection']  ?></small></h6>
              <?php }
              break;

            case csconstants::FieldSiteNumber:
            if(!empty($value['FieldSiteNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FieldSiteNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::StateSiteNumber:
            if(!empty($value['StateSiteNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StateSiteNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::SiteName:
            if(!empty($value['SiteName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SiteName']  ?></small></h6>
              <?php }
              break;

            case csconstants::SiteNumber:
            if(!empty($value['SiteNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SiteNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::DecorativeMotif:
            if(!empty($value['DecorativeMotif'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DecorativeMotif']  ?></small></h6>
              <?php }
              break;

            case csconstants::DecorativeTechnique:
            if(!empty($value['DecorativeTechnique'])){
            if(!empty($value['DecorativeTechnique']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DecorativeTechnique']['Term']  ?></small></h6>
                <?php }
              }

              break;

            case csconstants::Reproduction:
            if(!empty($value['Reproduction'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Reproduction']  ?></small></h6>
              <?php }
              break;

            case csconstants::ObjectForm:
              if(!empty($value['ObjectForm'])){
              if(!empty($value['ObjectForm']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectForm']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectPart:
            if(!empty($value['ObjectPart'])){
            if(!empty($value['ObjectPart']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectPart']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ComponentPart:
            if(!empty($value['ComponentPart'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ComponentPart']  ?></small></h6>
              <?php }
              break;

            case csconstants::Temper:
            if(!empty($value['Temper'])){
            if(!empty($value['Temper']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Temper']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TypeName:
            if(!empty($value['TypeName'])){
            if(!empty($value['TypeName']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeName']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SlideNumber:
            if(!empty($value['SlideNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SlideNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::BagNumber:
            if(!empty($value['BagNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BagNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::TotalBags:
            if(!empty($value['TotalBags'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TotalBags']  ?></small></h6>
              <?php }
              break;

            case csconstants::BoxNumber:
            if(!empty($value['BoxNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BoxNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::TotalBoxes:
            if(!empty($value['TotalBoxes'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TotalBoxes']  ?></small></h6>
              <?php }
              break;

            case csconstants::MakersMark:
            if(!empty($value['MakersMark'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MakersMark']  ?></small></h6>
              <?php }
              break;

            case csconstants::NAGPRA:
            if(!empty($value['NAGPRA'])){
            if(!empty($value['NAGPRA']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NAGPRA']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::OldNumber:
            if(!empty($value['OldNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OldNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::AdditionalAccessionNumber:
            if(!empty($value['AdditionalAccessionNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AdditionalAccessionNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::CatalogLevel:
            if(!empty($value['CatalogLevel'])){
            if(!empty($value['CatalogLevel']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogLevel']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::LevelOfControl:
            if(!empty($value['LevelOfControl'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LevelOfControl']  ?></small></h6>
              <?php }
              break;

            case csconstants::AlternateName:
            if(!empty($value['AlternateName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AlternateName']  ?></small></h6>
              <?php }
              break;

            case csconstants::AuthorName:
              if(!empty($value['Author'])){
            if(!empty($value['Author']['AuthorName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Author']['AuthorName']  ?></small></h6>
              <?php }
              }

              break;

            case csconstants::CreatorContactName:
              if(!empty($value['CreatorContact'])){
              if(!empty($value['CreatorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CreatorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ComposerContactName:
              if(!empty($value['ComposerContact'])){
            if(!empty($value['ComposerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ComposerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::NarratorContactName:
              if(!empty($value['NarratorContact'])){
            if(!empty($value['NarratorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NarratorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::EditorContactName:
              if(!empty($value['EditorContact'])){
            if(!empty($value['EditorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EditorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::PublisherContactName:
              if(!empty($value['PublisherContact'])){
            if(!empty($value['PublisherContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PublisherContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::IllustratorContactName:
              if(!empty($value['IllustratorContact'])){
            if(!empty($value['IllustratorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['IllustratorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ContributorContactName:
              if(!empty($value['ContributorContact'])){
            if(!empty($value['ContributorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ContributorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::StudioContactName:
              if(!empty($value['StudioContact'])){
            if(!empty($value['StudioContact'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StudioContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::DirectorContactName:
              if(!empty($value['DirectorContact'])){
            if(!empty($value['DirectorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DirectorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ArtDirectorContactName:
              if(!empty($value['ArtDirectorContact'])){
            if(!empty($value['ArtDirectorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ArtDirectorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ProducerContactName:
              if(!empty($value['ProducerContact'])){
            if(!empty($value['ProducerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ProducerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ProductionDesignerContactName:
              if(!empty($value['ProductionDesignerContact'])){
            if(!empty($value['ProductionDesignerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ProductionDesignerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ProductionCompanyContactName:
              if(!empty($value['ProductionCompanyContact'])){
            if(!empty($value['ProductionCompanyContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ProductionCompanyContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::DistributionCompany:
              if(!empty($value['DistributionCompany'])){
            if(!empty($value['DistributionCompany']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DistributionCompany']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::WriterContactName:
              if(!empty($value['WriterContact'])){
            if(!empty($value['WriterContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['WriterContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CinematographerContactName:
              if(!empty($value['CinematographerContact'])){
            if(!empty($value['CinematographerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CinematographerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::PhotographyContactName:
              if(!empty($value['PhotographyContact'])){
            if(!empty($value['PhotographyContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PhotographyContact']['ContactName']  ?></small></h6>
              <?php }
              }

              break;

            case csconstants::PublisherLocation:
            if(!empty($value['PublisherLocation'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PublisherLocation']  ?></small></h6>
              <?php }
              break;

            case csconstants::Event:
            if(!empty($value['Event'])){
            if(!empty($value['Event']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Event']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::PeopleContent:
            if(!empty($value['PeopleContent'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PeopleContent']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceContent:
            if(!empty($value['PlaceContent'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceContent']  ?></small></h6>
              <?php }
              break;

            case csconstants::TownshipRangeSection:
            if(!empty($value['TownshipRangeSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TownshipRangeSection']  ?></small></h6>
              <?php }
              break;

            case csconstants::ISBN:
            if(!empty($value['ISBN'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ISBN']  ?></small></h6>
              <?php }
              break;

            case csconstants::ISSN:
            if(!empty($value['ISSN'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ISSN']  ?></small></h6>
              <?php }
              break;

            case csconstants::CallNumber:
            if(!empty($value['CallNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CallNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::CoverType:
            if(!empty($value['CoverType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CoverType']  ?></small></h6>
              <?php }
              break;

            case csconstants::TypeOfBinding:
            if(!empty($value['TypeOfBinding'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeOfBinding']  ?></small></h6>
              <?php }
              break;

            case csconstants::Language:
            if(!empty($value['Language'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Language']  ?></small></h6>
              <?php }
              break;

            case csconstants::NumberOfPages:
            if(!empty($value['NumberOfPages'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NumberOfPages']  ?></small></h6>
              <?php }
              break;

            case csconstants::NegativeNumber:
            if(!empty($value['NegativeNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NegativeNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::FilmSize:
            if(!empty($value['FilmSize'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FilmSize']  ?></small></h6>
              <?php }
              break;

            case csconstants::Process:
            if(!empty($value['Process'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Process']  ?></small></h6>
              <?php }
              break;

            case csconstants::ImageNumber:
            if(!empty($value['ImageNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ImageNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::ImageRights:
            if(!empty($value['ImageRights'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ImageRights']  ?></small></h6>
              <?php }
              break;

            case csconstants::Copyrights:
            if(!empty($value['Copyrights'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Copyrights']  ?></small></h6>
              <?php }
              break;

            case csconstants::FindingAids:
            if(!empty($value['FindingAids'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FindingAids']  ?></small></h6>
              <?php }
              break;

            case csconstants::VolumeNumber:
            if(!empty($value['VolumeNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['VolumeNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::CompletionYear:
            if(!empty($value['CompletionYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CompletionYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::Format:
            if(!empty($value['Format'])){
            if(!empty($value['Format']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Format']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Genre:
            if(!empty($value['Genre'])){
            if(!empty($value['Genre']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Genre']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Subgenre:
            if(!empty($value['Subgenre'])){
            if(!empty($value['Subgenre']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Subgenre']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ReleaseDate:
            if(!empty($value['ReleaseDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ReleaseDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::ProductionDate:
            if(!empty($value['ProductionDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ProductionDate']))  ?></small></h6>
              <?php }


              break;

            case csconstants::Genus:
            if(!empty($value['Genus'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Genus']  ?></small></h6>
              <?php }
              break;

            case csconstants::Stage:
            if(!empty($value['Stage'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Stage']  ?></small></h6>
              <?php }
              break;

            case csconstants::Section:
            if(!empty($value['Section'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Section']  ?></small></h6>
              <?php }
              break;

            case csconstants::QuarterSection:
            if(!empty($value['QuarterSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['QuarterSection']  ?></small></h6>
              <?php }
              break;

            case csconstants::Age:
            if(!empty($value['Age'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Age']  ?></small></h6>
              <?php }
              break;

            case csconstants::Locality:
            if(!empty($value['Locality'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Locality']  ?></small></h6>
              <?php }
              break;

            case csconstants::HabitatCommunity:
            if(!empty($value['HabitatCommunity'])){
            if(!empty($value['HabitatCommunity']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['HabitatCommunity']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TypeSpecimen:
            if(!empty($value['TypeSpecimen'])){
            if(!empty($value['TypeSpecimen']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeSpecimen']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Sex:
            if(!empty($value['Sex'])){
            if(!empty($value['Sex']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Sex']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ExoticNative:
            if(!empty($value['ExoticNative'])){
            if(!empty($value['ExoticNative']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ExoticNative']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TaxonomicNotes:
            if(!empty($value['TaxonomicNotes'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TaxonomicNotes']  ?></small></h6>
              <?php }
              break;

            case csconstants::Rare:
            if(!empty($value['Rare'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Rare']  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredDate:
            if(!empty($value['ThreatenedEndangeredDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ThreatenedEndangeredDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredSpeciesSynonym:
            if(!empty($value['ThreatenedEndangeredSpeciesSynonym'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThreatenedEndangeredSpeciesSynonym'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredSpeciesSynonymName:
            if(!empty($value['ThreatenedEndangeredSpeciesSynonymName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThreatenedEndangeredSpeciesSynonymName']  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredSpeciesStatus:
            if(!empty($value['ThreatenedEndangeredSpeciesStatus'])){
            if(!empty($value['ThreatenedEndangeredSpeciesStatus']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThreatenedEndangeredSpeciesStatus']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SubspeciesSynonym:
              if(!empty($value['SubspeciesSynonym'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesSynonym']  ?></small></h6>
                <?php }
                break;

            case csconstants::ContinentWorldRegion:
              if(!empty($value['ContinentWorldRegion'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ContinentWorldRegion']  ?></small></h6>
                <?php }
                break;

            case csconstants::ReproductionMethod:
            if(!empty($value['ReproductionMethod'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ReproductionMethod']  ?></small></h6>
              <?php }
              break;

            case csconstants::ReferenceDatum:
            if(!empty($value['ReferenceDatum'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ReferenceDatum']  ?></small></h6>
              <?php }
              break;

            case csconstants::Aspect:
            if(!empty($value['Aspect'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Aspect']  ?></small></h6>
              <?php }
              break;

            case csconstants::FormationPeriodSubstrate:
            if(!empty($value['FormationPeriodSubstrate'])){
                if(!empty($value['FormationPeriodSubstrate']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FormationPeriodSubstrate']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SoilType:
            if(!empty($value['SoilType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SoilType']  ?></small></h6>
              <?php }
              break;

            case csconstants::Slope:
            if(!empty($value['Slope'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Slope']  ?></small></h6>
              <?php }
              break;

            case csconstants::Unit:
            if(!empty($value['Unit'])){
            if(!empty($value['Unit']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Unit']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::DepthInMeters:
            if(!empty($value['DepthInMeters'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DepthInMeters']  ?></small></h6>
              <?php }
              break;

            case csconstants::ElevationInMeters:
            if(!empty($value['ElevationInMeters'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ElevationInMeters']  ?></small></h6>
              <?php }

              break;

            case csconstants::EthnologyCulture:
            if(!empty($value['EthnologyCulture'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EthnologyCulture']  ?></small></h6>
              <?php }
              break;

            case csconstants::Alternate1EthnologyCulture:
            if(!empty($value['Alternate1EthnologyCulture'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Alternate1EthnologyCulture']  ?></small></h6>
              <?php }
              break;

            case csconstants::Alternate2EthnologyCulture:
            if(!empty($value['Alternate2EthnologyCulture'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Alternate2EthnologyCulture']  ?></small></h6>
              <?php }
              break;

            case csconstants::AboriginalName:
            if(!empty($value['AboriginalName'])){
            if(!empty($value['AboriginalName']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AboriginalName']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::AdditionalArea:
            if(!empty($value['AdditionalArea'])){
            if(!empty($value['AdditionalArea']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AdditionalArea']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::AdditionalGroup:
            if(!empty($value['AdditionalGroup'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AdditionalGroup']  ?></small></h6>
              <?php }
              break;

            case csconstants::DescriptiveName:
            if(!empty($value['DescriptiveName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DescriptiveName']  ?></small></h6>
              <?php }
              break;

            case csconstants::PeriodSystem:
            if(!empty($value['PeriodSystem'])){
            if(!empty($value['PeriodSystem']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PeriodSystem']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::EpochSeries:
            if(!empty($value['EpochSeries'])){
            if(!empty($value['EpochSeries']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EpochSeries']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::AgeStage:
            if(!empty($value['AgeStage'])){
            if(!empty($value['AgeStage']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AgeStage']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Composition:
            if(!empty($value['Composition'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Composition']  ?></small></h6>
              <?php }
              break;

            case csconstants::StrunzClass:
            if(!empty($value['StrunzClass'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StrunzClass']  ?></small></h6>
              <?php }
              break;

            case csconstants::StrunzDivision:
            if(!empty($value['StrunzDivision'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StrunzDivision']  ?></small></h6>
              <?php }
              break;

            case csconstants::StrunzID:
            if(!empty($value['StrunzID'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StrunzID']  ?></small></h6>
              <?php }
              break;

            case csconstants::LithologyPedotype:
            if(!empty($value['LithologyPedotype'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LithologyPedotype']  ?></small></h6>
              <?php }
              break;

            case csconstants::Formation:
            if(!empty($value['Formation'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Formation']  ?></small></h6>
              <?php }
              break;

            case csconstants::VerticalDatum:
            if(!empty($value['VerticalDatum'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['VerticalDatum']  ?></small></h6>
              <?php }
              break;

            case csconstants::Datum:
            if(!empty($value['Datum'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Datum']  ?></small></h6>
              <?php }
              break;

            case csconstants::DepositionalEnvironment:
            if(!empty($value['DepositionalEnvironment'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DepositionalEnvironment']  ?></small></h6>
              <?php }
              break;

            case csconstants::Member:
            if(!empty($value['Member'])){
            if(!empty($value['Member']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Member']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::GeoUnit:
            if(!empty($value['GeoUnit'])){
            if(!empty($value['GeoUnit']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['GeoUnit']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ThinSection:
            if(!empty($value['ThinSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThinSection'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::PatentDate:
            if(!empty($value['PatentDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PatentDate']  ?></small></h6>
              <?php }
              break;

            case csconstants::Copyright:
              if(!empty($value['Copyright'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Copyright']  ?></small></h6>
                <?php }
                break;

            case csconstants::School:
              if(!empty($value['School'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['School']  ?></small></h6>
                <?php }
                break;

            case csconstants::Lithology:
            if(!empty($value['Lithology'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Lithology']  ?></small></h6>
              <?php }
              break;

            case csconstants::Horizon:
            if(!empty($value['Horizon'])){
            if(!empty($value['Horizon']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Horizon']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::InsituFloat:
            if(!empty($value['InsituFloat'])){
            if(!empty($value['InsituFloat']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['InsituFloat']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Taphonomy:
            if(!empty($value['Taphonomy'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Taphonomy']  ?></small></h6>
              <?php }
              break;

            case csconstants::Model:
            if(!empty($value['Model'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Model']  ?></small></h6>
              <?php }
              break;

            case csconstants::Stones:
            if(!empty($value['Stones'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Stones']  ?></small></h6>
              <?php }
              break;

            case csconstants::Karats:
            if(!empty($value['Karats'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Karats']  ?></small></h6>
              <?php }
              break;

            case csconstants::Carats:
            if(!empty($value['Carats'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Carats']  ?></small></h6>
              <?php }
              break;

            case csconstants::Cut:
            if(!empty($value['Cut'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Cut']  ?></small></h6>
              <?php }
              break;

            case csconstants::Clarity:
            if(!empty($value['Clarity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Clarity']  ?></small></h6>
              <?php }
              break;

            case csconstants::TypeOfGemstone:
            if(!empty($value['TypeOfGemstone'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeOfGemstone']  ?></small></h6>
              <?php }
              break;

            case csconstants::Size:
            if(!empty($value['Size'])){
            if(!empty($value['Size']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Size']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::MetalType:
            if(!empty($value['MetalType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MetalType']  ?></small></h6>
              <?php }
              break;

            case csconstants::DrivenBy:
            if(!empty($value['DrivenBy'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DrivenBy']  ?></small></h6>
              <?php }
              break;

            case csconstants::VIN:
            if(!empty($value['VIN'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['VIN']  ?></small></h6>
              <?php }
              break;

            case csconstants::ChassisNumber:
            if(!empty($value['ChassisNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ChassisNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::Mileage:
            if(!empty($value['Mileage'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Mileage']  ?></small></h6>
              <?php }
              break;

            case csconstants::Power:
            if(!empty($value['Power'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Power']  ?></small></h6>
              <?php }
              break;

            case csconstants::EngineType:
            if(!empty($value['EngineType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EngineType']  ?></small></h6>
              <?php }
              break;

            case csconstants::EnginePosition:
            if(!empty($value['EnginePosition'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EnginePosition']  ?></small></h6>
              <?php }
              break;

            case csconstants::Transmission:
            if(!empty($value['Transmission'])){
            if(!empty($value['Transmission']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Transmission']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Passengers:
            if(!empty($value['Passengers'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Passengers']  ?></small></h6>
              <?php }
              break;

            case csconstants::FuelHighway:
            if(!empty($value['FuelHighway'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FuelHighway']  ?></small></h6>
              <?php }
              break;

            case csconstants::Acceleration:
            if(!empty($value['Acceleration'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Acceleration']  ?></small></h6>
              <?php }
              break;

            case csconstants::TopSpeed:
            if(!empty($value['TopSpeed'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TopSpeed']  ?></small></h6>
              <?php }
              break;

            case csconstants::EngineNumber:
            if(!empty($value['EngineNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EngineNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::LicensePlateNumber:
            if(!empty($value['LicensePlateNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LicensePlateNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::TransmissionFluid:
            if(!empty($value['TransmissionFluid'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TransmissionFluid']  ?></small></h6>
              <?php }
              break;

            case csconstants::BrakeFluid:
            if(!empty($value['BrakeFluid'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BrakeFluid']  ?></small></h6>
              <?php }
              break;

            case csconstants::OilType:
            if(!empty($value['OilType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OilType']  ?></small></h6>
              <?php }
              break;

            case csconstants::FuelType:
            if(!empty($value['FuelType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FuelType']  ?></small></h6>
              <?php }
              break;

            case csconstants::RegistrationStatus:
            if(!empty($value['RegistrationStatus'])){
            if(!empty($value['RegistrationStatus']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['RegistrationStatus']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TitleStatus:
            if(!empty($value['TitleStatus'])){
            if(!empty($value['TitleStatus']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TitleStatus']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Paint:
            if(!empty($value['Paint'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Paint']  ?></small></h6>
              <?php }
              break;

            case csconstants::Battery:
            if(!empty($value['Battery'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Battery']  ?></small></h6>
              <?php }
              break;

            case csconstants::ShiftPattern:
            if(!empty($value['ShiftPattern'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ShiftPattern']  ?></small></h6>
              <?php }
              break;

            case csconstants::DashLayout:
            if(!empty($value['DashLayout'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DashLayout']  ?></small></h6>
              <?php }
              break;

            case csconstants::TypeOfWine:
            if(!empty($value['TypeOfWine'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeOfWine']  ?></small></h6>
              <?php }
              break;

            case csconstants::Maturity:
            if(!empty($value['Maturity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Maturity']  ?></small></h6>
              <?php }
              break;

            case csconstants::Grape:
            if(!empty($value['Grape'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Grape']  ?></small></h6>
              <?php }
              break;

            case csconstants::Region:
            if(!empty($value['Region'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Region']  ?></small></h6>
              <?php }
              break;

            case csconstants::BottleSize:
            if(!empty($value['BottleSize'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BottleSize']  ?></small></h6>
              <?php }
              break;

            case csconstants::FermentationPeriod:
            if(!empty($value['FermentationPeriod'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FermentationPeriod']  ?></small></h6>
              <?php }
              break;

            case csconstants::DesignerName:
              if(!empty($value['Designer'])){
            if(!empty($value['Designer']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Designer']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::Brand:
            if(!empty($value['Brand'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Brand']  ?></small></h6>
              <?php }
              break;

            case csconstants::FabricMaterial:
            if(!empty($value['FabricMaterial'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FabricMaterial']  ?></small></h6>
              <?php }
              break;

            case csconstants::SKU:
            if(!empty($value['SKU'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SKU']  ?></small></h6>
              <?php }
              break;


            /*dimension fields */

    case csconstants::HeightMetric:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['HeightMetric'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['HeightMetric']  ?></small></h6>
          <?php } }
          break;

    case csconstants::WidthMetric:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['WidthMetric'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['WidthMetric']  ?></small></h6>
          <?php } }
          break;

    case csconstants::DepthMetric:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['DepthMetric'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['DepthMetric']  ?></small></h6>
          <?php } }
          break;

    case csconstants::DiameterMetric:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['DiameterMetric'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['DiameterMetric']  ?></small></h6>
          <?php } }
          break;

    case csconstants::WeightMetric:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['WeightMetric'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['WeightMetric']  ?></small></h6>
          <?php } }
          break;

    case csconstants::WeightImperial:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['WeightImperial'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['WeightImperial']  ?></small></h6>
          <?php } }
          break;

    case csconstants::HeightImperial:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['HeightImperial'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['HeightImperial']  ?></small></h6>
          <?php } }
          break;

    case csconstants::WidthImperial:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['WidthImperial'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['WidthImperial']  ?></small></h6>
          <?php } }
          break;

    case csconstants::DepthImperial:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['DepthImperial'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['DepthImperial']  ?></small></h6>
          <?php } }
          break;

    case csconstants::DiameterImperial:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['DiameterImperial'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['DiameterImperial']  ?></small></h6>
          <?php } }
          break;

    case csconstants::SquareMeters:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['SquareMeters'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['SquareMeters']  ?></small></h6>
          <?php } }
          break;

      case csconstants::SquareFeet:
        if(!empty($value['Object']['MainDimension'])){
        if(!empty($value['Object']['MainDimension']['SquareFeet'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['SquareFeet']  ?></small></h6>
            <?php } }
            break;

    case csconstants::ImperialDims:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['ImperialDims'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['ImperialDims']  ?></small></h6>
          <?php } }
          break;

    case csconstants::MetricDims:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['MetricDims'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['MetricDims']  ?></small></h6>
          <?php } }
          break;

    case csconstants::DimensionDescription:
      if(!empty($value['Object']['MainDimension'])){
      if(!empty($value['Object']['MainDimension']['DimensionDescription'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MainDimension']['DimensionDescription']['Term']  ?></small></h6>
          <?php } }
          break;


              /*richtext fields*/


              /*spectrumobject fields*/


            case csconstants::OtherNumberType:
            if(!empty($value['Object']['OtherNumberType'])){
            if(!empty($value['Object']['OtherNumberType']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['OtherNumberType']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ResponsibleDepartment:
            if(!empty($value['Object']['ResponsibleDepartment'])){
            if(!empty($value['Object']['ResponsibleDepartment']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['ResponsibleDepartment']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Completeness:
            if(!empty($value['Object']['Completeness'])){
            if(!empty($value['Object']['Completeness']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['Completeness']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::CompletenessDate:
            if(!empty($value['Object']['CompletenessDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['Object']['CompletenessDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::CompletenessNote:
            if(!empty($value['Object']['CompletenessNote'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['CompletenessNote']  ?></small></h6>
              <?php }


              break;

            case csconstants::MovementReferenceNumber:
            if(!empty($value['Object']['MovementReferenceNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MovementReferenceNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::MovementAuthorizerContactName:
              if(!empty($value['Object']['MovementAuthorizer'])){
            if(!empty($value['Object']['MovementAuthorizer']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MovementAuthorizer']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::MovementAuthorizationDate:
            if(!empty($value['Object']['MovementAuthorizationDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['Object']['MovementAuthorizationDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::MovementContactName:
              if(!empty($value['Object']['MovementContact'])){
            if(!empty($value['Object']['MovementContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MovementContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::MovementMethod:
            if(!empty($value['Object']['MovementMethod'])){
            if(!empty($value['Object']['MovementMethod']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MovementMethod']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::MovementMemo:
            if(!empty($value['Object']['MovementMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MovementMemo']  ?></small></h6>
              <?php }
              break;

            case csconstants::MovementReason:
            if(!empty($value['Object']['MovementReason'])){
            if(!empty($value['Object']['MovementReason']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['MovementReason']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::PlannedRemoval:
            if(!empty($value['Object']['PlannedRemoval'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['PlannedRemoval']  ?></small></h6>
              <?php }
              break;

            case csconstants::LocationReferenceNameNumber:
            if(!empty($value['Object']['LocationReferenceNameNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['LocationReferenceNameNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::LocationType:
            if(!empty($value['Object']['LocationType'])){
            if(!empty($value['Object']['LocationType']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['LocationType']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::LocationAccessMemo:
            if(!empty($value['Object']['LocationAccessMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['LocationAccessMemo']  ?></small></h6>
              <?php }
              break;

            case csconstants::LocationConditionMemo:
            if(!empty($value['Object']['LocationConditionMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['LocationConditionMemo']  ?></small></h6>
              <?php }
              break;

            case csconstants::LocationConditionDate:
            if(!empty($value['Object']['LocationConditionDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['Object']['LocationConditionDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::LocationSecurityMemo:
            if(!empty($value['Object']['LocationSecurityMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['LocationSecurityMemo']  ?></small></h6>
              <?php }


              break;

            case csconstants::ObjectNameCurrency:
            if(!empty($value['Object']['ObjectNameCurrency'])){
            if(!empty($value['Object']['ObjectNameCurrency']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['ObjectNameCurrency']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectNameLevel:
            if(!empty($value['Object']['ObjectNameLevel'])){
            if(!empty($value['Object']['ObjectNameLevel']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['ObjectNameLevel']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectNameNote:
            if(!empty($value['Object']['ObjectNameNote'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['ObjectNameNote']  ?></small></h6>
              <?php }
              break;

            case csconstants::ObjectNameSystem:
            if(!empty($value['Object']['ObjectNameSystem'])){
            if(!empty($value['Object']['ObjectNameSystem']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['ObjectNameSystem']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectNameType:
            if(!empty($value['Object']['ObjectNameType'])){
            if(!empty($value['Object']['ObjectNameType']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['ObjectNameType']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectNameTitleLanguage:
            if(!empty($value['Object']['ObjectNameTitleLanguage'])){
            if(!empty($value['Object']['ObjectNameTitleLanguage']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['ObjectNameTitleLanguage']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::FieldCollectionMethod:
            if(!empty($value['Object']['FieldCollectionMethod'])){
            if(!empty($value['Object']['FieldCollectionMethod']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['FieldCollectionMethod']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::FieldCollectionPlace:
            if(!empty($value['Object']['FieldCollectionPlace'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['FieldCollectionPlace']  ?></small></h6>
              <?php }
              break;

            case csconstants::FieldCollectionSourceContactName:
            if(!empty($value['Object']['FieldCollectionSource']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['FieldCollectionSource']['ContactName']  ?></small></h6>
              <?php }
              break;

            case csconstants::FieldCollectionMemo:
            if(!empty($value['Object']['FieldCollectionMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['FieldCollectionMemo']  ?></small></h6>
              <?php }
              break;

            case csconstants::GeologicalComplexName:
            if(!empty($value['Object']['GeologicalComplexName'])){
            if(!empty($value['Object']['GeologicalComplexName']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['GeologicalComplexName']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Habitat:
            if(!empty($value['Object']['Habitat'])){
            if(!empty($value['Object']['Habitat']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['Habitat']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::HabitatMemo:
            if(!empty($value['Object']['HabitatMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['HabitatMemo']  ?></small></h6>
              <?php }
              break;

            case csconstants::StratigraphicUnitName:
            if(!empty($value['Object']['StratigraphicUnitName'])){
            if(!empty($value['Object']['StratigraphicUnitName']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['StratigraphicUnitName']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::StratigraphicUnitType:
            if(!empty($value['Object']['StratigraphicUnitType'])){
            if(!empty($value['Object']['StratigraphicUnitType']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['StratigraphicUnitType']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::StratigraphicUnitMemo:
            if(!empty($value['Object']['StratigraphicUnitMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['StratigraphicUnitMemo']  ?></small></h6>
              <?php }
              break;

              /*udf fields*/



            case csconstants::UserDefined1:
            if(!empty($value['Object']['UserDefined1'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined1']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined2:
            if(!empty($value['Object']['UserDefined2'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined2']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined3:
            if(!empty($value['Object']['UserDefined3'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined3']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined4:
            if(!empty($value['Object']['UserDefined4'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined4']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined5:
            if(!empty($value['Object']['UserDefined5'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined5']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined6:
            if(!empty($value['Object']['UserDefined6'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined6']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined7:
            if(!empty($value['Object']['UserDefined7'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined7']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined8:
            if(!empty($value['Object']['UserDefined8'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined8']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined9:
            if(!empty($value['Object']['UserDefined9'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined9']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined10:
            if(!empty($value['Object']['UserDefined10'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined10']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefined11:
            if(!empty($value['Object']['UserDefined11'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined11']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined12:
            if(!empty($value['Object']['UserDefined12'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined12']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined13:
            if(!empty($value['Object']['UserDefined13'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined13']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined14:
            if(!empty($value['Object']['UserDefined14'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined14']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined15:
            if(!empty($value['Object']['UserDefined15'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined15']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined16:
            if(!empty($value['Object']['UserDefined16'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined16']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined17:
            if(!empty($value['Object']['UserDefined17'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined17']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined18:
            if(!empty($value['Object']['UserDefined18'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined18']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined19:
            if(!empty($value['Object']['UserDefined19'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined19']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined20:
            if(!empty($value['Object']['UserDefined20'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined20']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefined21:
            if(!empty($value['Object']['UserDefined21'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined21']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined22:
            if(!empty($value['Object']['UserDefined22'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined22']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined23:
            if(!empty($value['Object']['UserDefined23'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined23']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined24:
            if(!empty($value['Object']['UserDefined24'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined24']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined25:
            if(!empty($value['Object']['UserDefined25'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined25']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined26:
            if(!empty($value['Object']['UserDefined26'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined26']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined27:
            if(!empty($value['Object']['UserDefined27'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined27']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined28:
            if(!empty($value['Object']['UserDefined28'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined28']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined29:
            if(!empty($value['Object']['UserDefined29'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined29']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined30:
            if(!empty($value['Object']['UserDefined30'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined30']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefined31:
            if(!empty($value['Object']['UserDefined31'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined31']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined32:
            if(!empty($value['Object']['UserDefined32'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined32']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined33:
            if(!empty($value['Object']['UserDefined33'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined33']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined34:
            if(!empty($value['Object']['UserDefined34'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined34']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined35:
            if(!empty($value['Object']['UserDefined35'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined35']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined36:
            if(!empty($value['Object']['UserDefined36'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined36']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined37:
            if(!empty($value['Object']['UserDefined37'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined37']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined38:
            if(!empty($value['Object']['UserDefined38'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined38']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined39:
            if(!empty($value['Object']['UserDefined39'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined39']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined40:
            if(!empty($value['Object']['UserDefined40'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefined40']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefinedDate1:
            if(!empty($value['Object']['UserDefinedDate1'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefinedDate1']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefinedDate2:
            if(!empty($value['Object']['UserDefinedDate2'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefinedDate2']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefinedNumber1:
            if(!empty($value['Object']['UserDefinedNumber1'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefinedNumber1']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefinedNumber2:
            if(!empty($value['Object']['UserDefinedNumber2'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefinedNumber2']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefinedCurrency1:
            if(!empty($value['Object']['UserDefinedCurrency1'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefinedCurrency1']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefinedCurrency2:
            if(!empty($value['Object']['UserDefinedCurrency2'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Object']['UserDefinedCurrency2']  ?></small></h6>
              <?php }

              break;

              default:
              break;

               /*end*/


          }
        } ?>
        </div>
      </div>
    <?php
  }

  public function getGroupObjectsListHtml($value, $dataOrderBy, $datapageNo,$dataSearch,$delaytm, $default_image_url){

    // $customized_fields = getCommaSeperatedFieldsForListPage();
    $customized_fields = 'Title,test'; //temp only
    $customized_fields_array = explode(',', $customized_fields);


    if (is_array($value) || is_object($value)) {
      $value = is_object($value) ? get_object_vars($value) : $value;
    }



    $site_url = \Drupal::request()->getSchemeAndHttpHost();



    ?>
    <div class="card col-lg-4 col-md-6 col-sm-6 col-12 mb-3 cs-object-list wow fadeInDown" data-wow-delay="<?php echo $delaytm; ?>">
                  <div class="card-body d-flex flex-column">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId'] ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)">
                              <?php $object_img = !empty($value['ObjectImage']) ? 'data:image/jpeg;base64,' . base64_encode($value['ObjectImage']) : "";
                              $server_path = $value['ObjectImagePath'];
                              $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $server_path);
                              $image_url = $site_url . "/" . $relative_path;
                              if(empty($object_img) && empty($server_path)){
                              ?>
                                  <img class="img-fluid" src="<?php echo $default_image_url; ?>" alt=""/>
                                  <?php } else {
                                  if (empty($server_path)) {
                                  ?>
                                  <img class="img-fluid" src="<?php echo $object_img; ?>" alt=""/>
                                  <?php
                                  } else {
                                  ?>
                                  <img class="img-fluid" src="<?php echo $image_url; ?>" alt=""/>
                                  <?php
                                  }
                                  }
                                  ?>

                  </a>
                  </div>
                  <div class="card-footer text-muted">
                      <?php

          /*get first 3 array fields*/
          //$customized_fields_array = array_slice($customized_fields_array, 0, 3);
          foreach($customized_fields_array as $object_field)
          {
          //echo "field:" .$object_field;

         switch($object_field)
          {
            case csconstants::InventoryNumber:
              if(!empty($value['InventoryNumber'])){ ?>
              <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)">
                <h6 class="font-normal" title="<?php echo $value['InventoryNumber']; ?>" ><small class="flex-fill"><?php echo $value['InventoryNumber']  ?></small></h6>
              </a>
              <?php }

              break;

              case csconstants::NomenclatureObjectName:
              if(!empty($value['NomenclatureObjectName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NomenclatureObjectName']  ?></small></h6>
                <?php }
              break;

            case csconstants::ObjectStatus:
              if(!empty($value['ObjectStatus'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectStatus']  ?></small></h6>
              <?php }

              break;

            case csconstants::ObjectType:
              if(!empty($value['ObjectType'])){
              if(!empty($value['ObjectType']['ObjectTypeName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectType']['ObjectTypeName']  ?></small></h6>
                <?php }
                }
              break;

            case csconstants::LocationName:
              if(!empty($value['Location'])){
              if(!empty($value['Location']['LocationName'])){ ?>
                  <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Location']['LocationName']  ?></small></h6>
              <?php }
              }
            break;

            case csconstants::FullLocationName:
              if(!empty($value['Location'])){
              if(!empty($value['Location']['FullLocationName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Location']['FullLocationName']  ?></small></h6>
              <?php }
              }
            break;

            case csconstants::PermanentLocationName:
              if(!empty($value['PermanentLocation'])){
            if(!empty($value['PermanentLocation']['LocationName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PermanentLocation']['LocationName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::PermanentFullLocationName:
              if(!empty($value['PermanentLocation'])){
            if(!empty($value['PermanentLocation']['FullLocationName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PermanentLocation']['FullLocationName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CollectionName:
              if(!empty($value['Collection'])){
              if(!empty($value['Collection']['CollectionName'])){ ?>
                <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForCollection('<?php echo site_url() ?>', <?php echo $value['Collection']['CollectionId']; ?>)"><?php echo $value['Collection']['CollectionName']  ?></a></small></h6>
              <?php }
              }

              break;

            case csconstants::FullCollectionName:
              if(!empty($value['Collection'])){
              if(!empty($value['Collection']['FullCollectionName'])){ ?>
                <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForCollection('<?php echo site_url() ?>', <?php echo $value['Collection']['CollectionId']; ?>)"><?php echo $value['Collection']['FullCollectionName']  ?></a></small></h6>
              <?php }
              }

              break;

            case csconstants::CreditLine:
            if(!empty($value['CreditLine'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CreditLine']  ?></small></h6>
              <?php }

              break;

              case csconstants::ArtistName:
                if(!empty($value['Artist']) && isset($value['Artist'])){
              if(!empty($value['Artist']['ArtistName'])){ ?>
                    <h6 class="font-normal"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForArtist('<?php echo site_url() ?>', <?php echo $value['Artist']['ArtistId']; ?>)">
                      <?php echo $value['Artist']['ArtistName'] ?>
                    </a></small></h6>
                  <?php }
                }

                  break;

            case csconstants::AdditionalArtists:
            if(!empty($value['AdditionalArtists'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo implodeChildArrayProperty($value['AdditionalArtists'],"Artist","ArtistId","ArtistName");  ?></small></h6>
              <?php }

              break;

            case csconstants::Maker:
            if(!empty($value['Maker'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Maker']  ?></small></h6>
              <?php }

              break;

            case csconstants::Title:
            if(!empty($value['Title'])){ ?>
              <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)">
                <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><?php echo $value['Title']  ?></small></h6>
              </a>
              <?php }

              break;

            case csconstants::AlternateTitle:
            if(!empty($value['AlternateTitle'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AlternateTitle']  ?></small></h6>
              <?php }

              break;

            case csconstants::ObjectDate:
            if(!empty($value['ObjectDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectDate']  ?></small></h6>
              <?php }

              break;

            case csconstants::Medium:
            if(!empty($value['Medium'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Medium']  ?></small></h6>
              <?php }

              break;

            case csconstants::LocationStatus:
            if(!empty($value['LocationStatus'])){
            if(!empty($value['LocationStatus']['Term'])){ ?>
                  <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LocationStatus']['Term']  ?></small></h6>
                <?php }
              }

              break;

            case csconstants::InventoryDate:
            if(!empty($value['InventoryDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo date('m/d/Y',strtotime($value['InventoryDate']))  ?></small></h6>
              <?php }

              break;

            case csconstants::InventoryContactName:
              if(!empty($value['InventoryContact'])){
            if(!empty($value['InventoryContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['InventoryContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::Form:
            if(!empty($value['Form'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Form']  ?></small></h6>
              <?php }

              break;

            case csconstants::Subject:
            if(!empty($value['Subject'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Subject']  ?></small></h6>
              <?php }

              break;

            case csconstants::CategoryStyle:
            if(!empty($value['CategoryStyle'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CategoryStyle']  ?></small></h6>
              <?php }

              break;

            case csconstants::CountryOrigin:
            if(!empty($value['CountryOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CountryOrigin']  ?></small></h6>
               <?php }

              break;

            case csconstants::Edition:
            if(!empty($value['Edition'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Edition']  ?></small></h6>
              <?php }

              break;

            case csconstants::SuitePortfolio:
            if(!empty($value['SuitePortfolio'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SuitePortfolio']  ?></small></h6>
              <?php }

              break;

            case csconstants::CatalogRaisonne:
            if(!empty($value['CatalogRaisonne'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogRaisonne']  ?></small></h6>
              <?php }

              break;

            case csconstants::RFIDTagNumber:
            if(!empty($value['RFIDTagNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['RFIDTagNumber']  ?></small></h6>
              <?php }

              break;

            case csconstants::Term:
            if(!empty($value['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Term']  ?></small></h6>
              <?php }

              //art musueum fields
              break;

            case csconstants::CatalogNumber:
            if(!empty($value['CatalogNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::OtherNumbers:
            if(!empty($value['OtherNumbers'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OtherNumbers']  ?></small></h6>
              <?php }
              break;

            case csconstants::ItemCount:
            if(!empty($value['ItemCount'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ItemCount']  ?></small></h6>
              <?php }
              break;

            case csconstants::CatalogerContactName:
              if(!empty($value['CatalogerContact'])){
              if(!empty($value['CatalogerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CatalogDate:
            if(!empty($value['CatalogDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['CatalogDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::CollectionTitle:
            if(!empty($value['CollectionTitle'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectionTitle']  ?></small></h6>
              <?php }
              break;

            case csconstants::CollectionNumber:
            if(!empty($value['CollectionNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectionNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::Material:
            if(!empty($value['Material'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Material']  ?></small></h6>
              <?php }
              break;

            case csconstants::Technique:
            if(!empty($value['Technique'])){
            if(!empty($value['Technique']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Technique']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Color:
            if(!empty($value['Color'])){
            if(!empty($value['Color']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Color']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::StateOfOrigin:
            if(!empty($value['StateOfOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StateOfOrigin']  ?></small></h6>
              <?php }
              break;

            case csconstants::CountyOfOrigin:
            if(!empty($value['CountyOfOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CountyOfOrigin']  ?></small></h6>
              <?php }
              break;

            case csconstants::CityOfOrigin:
            if(!empty($value['CityOfOrigin'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CityOfOrigin']  ?></small></h6>
              <?php }
              break;

            case csconstants::State:
            if(!empty($value['State'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['State']  ?></small></h6>
              <?php }
              break;

            case csconstants::Duration:
            if(!empty($value['Duration'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Duration']  ?></small></h6>
              <?php }

              break;

            case csconstants::RevisedNomenclature:
            if(!empty($value['RevisedNomenclature'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['RevisedNomenclature']  ?></small></h6>
              <?php }
              break;

            case csconstants::PreviousCatalogNumber:
            if(!empty($value['PreviousCatalogNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PreviousCatalogNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::FieldSpecimenNumber:
            if(!empty($value['FieldSpecimenNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FieldSpecimenNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::StatusDate:
            if(!empty($value['StatusDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StatusDate']  ?></small></h6>
              <?php }
              break;

            case csconstants::StorageUnit:
            if(!empty($value['StorageUnit'])){
            if(!empty($value['StorageUnit']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StorageUnit']['Term']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CollectionDate:
            if(!empty($value['CollectionDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['CollectionDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::CollectorContactName:
              if(!empty($value['CollectorContact'])){
            if(!empty($value['CollectorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CollectorPlace:
            if(!empty($value['CollectorPlace'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CollectorPlace']  ?></small></h6>
              <?php }
              break;

            case csconstants::CatalogFolder:
              if(!empty($value['CatalogFolder'])){  ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogFolder'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::IdentifiedByContactName:
              if(!empty($value['IdentifiedByContact'])){
            if(!empty($value['IdentifiedByContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['IdentifiedByContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::IdentifiedDate:
            if(!empty($value['IdentifiedDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['IdentifiedDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::EminentFigureContactName:
            if(!empty($value['EminentFigureContact'])){
              if(!empty($value['EminentFigureContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EminentFigureContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::EminentOrganizationContactName:
              if(!empty($value['EminentOrganizationContact'])){
            if(!empty($value['EminentOrganizationContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EminentOrganizationContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ControlledProperty:
            if(!empty($value['ControlledProperty'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ControlledProperty'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::ArtistMakerName:
              if(!empty($value["ArtistMaker"])){
              if(!empty($value["ArtistMaker"]['ArtistName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value["ArtistMaker"]['ArtistName']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TaxonomicSerialNumber:
            if(!empty($value['TaxonomicSerialNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TaxonomicSerialNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::Kingdom:
            if(!empty($value['Kingdom'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Kingdom']  ?></small></h6>
              <?php }
              break;

            case csconstants::PhylumDivision:
            if(!empty($value['PhylumDivision'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PhylumDivision']  ?></small></h6>
              <?php }
              break;

            case csconstants::CSClass:
            if(!empty($value['Class'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Class']  ?></small></h6>
              <?php }
              break;

            case csconstants::Order:
            if(!empty($value['Order'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Order']  ?></small></h6>
              <?php }
              break;

            case csconstants::Family:
            if(!empty($value['Family'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Family']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubFamily:
            if(!empty($value['SubFamily'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubFamily']  ?></small></h6>
              <?php }
              break;

            case csconstants::ScientificName:
            if(!empty($value['ScientificName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ScientificName']  ?></small></h6>
              <?php }
              break;

            case csconstants::CommonName:
            if(!empty($value['CommonName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CommonName']  ?></small></h6>
              <?php }
              break;

            case csconstants::Species:
            if(!empty($value['Species'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Species']  ?></small></h6>
              <?php }
              break;

            case csconstants::SpeciesAuthorName:
              if(!empty($value['SpeciesAuthor'])){
              if(!empty($value['SpeciesAuthor']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SpeciesAuthor']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SpeciesAuthorDate:
            if(!empty($value['SpeciesAuthorDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['SpeciesAuthorDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::Subspecies:
            if(!empty($value['Subspecies'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Subspecies']  ?></small></h6>
              <?php }
              break;

              case csconstants::SubspeciesAuthorityContactName:
              if(!empty($value['SubspeciesAuthorityContact'])){
              if(!empty($value['SubspeciesAuthorityContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesAuthorityContact']['ContactName']  ?></small></h6>
                <?php }
              }
                break;

            case csconstants::SubspeciesAuthorName:
              if(!empty($value['SubspeciesAuthor'])){
            if(!empty($value['SubspeciesAuthor']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesAuthor']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SubspeciesAuthorDate:
            if(!empty($value['SubspeciesAuthorDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['SubspeciesAuthorDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesYear:
            if(!empty($value['SubspeciesYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesVariety:
            if(!empty($value['SubspeciesVariety'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesVariety']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesVarietyAuthorityContactName:
              if(!empty($value['SubspeciesVarietyAuthorityContact'])){
              if(!empty($value['SubspeciesVarietyAuthorityContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesVarietyAuthorityContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SubspeciesVarietyYear:
            if(!empty($value['SubspeciesVarietyYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesVarietyYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesForma:
            if(!empty($value['SubspeciesForma'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesForma']  ?></small></h6>
              <?php }
              break;

            case csconstants::SubspeciesFormaAuthorityContactName:
              if(!empty($value['SubspeciesFormaAuthorityContact'])){
            if(!empty($value['SubspeciesFormaAuthorityContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesFormaAuthorityContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::SubspeciesFormaYear:
            if(!empty($value['SubspeciesFormaYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesFormaYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::StudyNumber:
              if(!empty($value['StudyNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StudyNumber']  ?></small></h6>
                <?php }
                break;

            case csconstants::AlternateName:
            if(!empty($value['AlternateName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AlternateName']  ?></small></h6>
              <?php }
              break;

            case csconstants::CulturalID:
            if(!empty($value['CulturalID'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CulturalID']  ?></small></h6>
              <?php }
              break;

            case csconstants::CultureOfUse:
            if(!empty($value['CultureOfUse'])){
            if(!empty($value['CultureOfUse']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CultureOfUse']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ManufactureDate:
            if(!empty($value['ManufactureDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ManufactureDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::UseDate:
            if(!empty($value['UseDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UseDate']  ?></small></h6>
              <?php }
              break;

            case csconstants::TimePeriod:
            if(!empty($value['TimePeriod'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TimePeriod']  ?></small></h6>
              <?php }
              break;

            case csconstants::HistoricCulturalPeriod:
            if(!empty($value['HistoricCulturalPeriod'])){
            if(!empty($value['HistoricCulturalPeriod']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['HistoricCulturalPeriod']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ManufacturingTechnique:
            if(!empty($value['ManufacturingTechnique'])){
            if(!empty($value['ManufacturingTechnique']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ManufacturingTechnique']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Material:
            if(!empty($value['Material'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Material']  ?></small></h6>
              <?php }
              break;

            case csconstants::BroadClassOfMaterial:
            if(!empty($value['BroadClassOfMaterial'])){
            if(!empty($value['BroadClassOfMaterial']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BroadClassOfMaterial']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SpecificClassOfMaterial:
            if(!empty($value['SpecificClassOfMaterial'])){
            if(!empty($value['SpecificClassOfMaterial']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SpecificClassOfMaterial']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Quantity:
            if(!empty($value['Quantity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Quantity']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureCountry:
            if(!empty($value['PlaceOfManufactureCountry'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureCountry']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureState:
            if(!empty($value['PlaceOfManufactureState'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureState']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureCounty:
            if(!empty($value['PlaceOfManufactureCounty'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureCounty']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceOfManufactureCity:
            if(!empty($value['PlaceOfManufactureCity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceOfManufactureCity']  ?></small></h6>
              <?php }
              break;

            case csconstants::OtherManufacturingSite:
            if(!empty($value['OtherManufacturingSite'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OtherManufacturingSite']  ?></small></h6>
              <?php }
              break;

            case csconstants::Latitude:
            if(!empty($value['Latitude'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Latitude']  ?></small></h6>
              <?php }
              break;

            case csconstants::Longitude:
            if(!empty($value['Longitude'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Longitude']  ?></small></h6>
              <?php }
              break;

            case csconstants::UTMCoordinates:
            if(!empty($value['UTMCoordinates'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UTMCoordinates']  ?></small></h6>
              <?php }
              break;

            case csconstants::TownshipRangeSection:
            if(!empty($value['TownshipRangeSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TownshipRangeSection']  ?></small></h6>
              <?php }
              break;

            case csconstants::FieldSiteNumber:
            if(!empty($value['FieldSiteNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FieldSiteNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::StateSiteNumber:
            if(!empty($value['StateSiteNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StateSiteNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::SiteName:
            if(!empty($value['SiteName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SiteName']  ?></small></h6>
              <?php }
              break;

            case csconstants::SiteNumber:
            if(!empty($value['SiteNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SiteNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::DecorativeMotif:
            if(!empty($value['DecorativeMotif'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DecorativeMotif']  ?></small></h6>
              <?php }
              break;

            case csconstants::DecorativeTechnique:
            if(!empty($value['DecorativeTechnique'])){
            if(!empty($value['DecorativeTechnique']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DecorativeTechnique']['Term']  ?></small></h6>
                <?php }
              }

              break;

            case csconstants::Reproduction:
            if(!empty($value['Reproduction'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Reproduction']  ?></small></h6>
              <?php }
              break;

            case csconstants::ObjectForm:
              if(!empty($value['ObjectForm'])){
              if(!empty($value['ObjectForm']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectForm']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectPart:
            if(!empty($value['ObjectPart'])){
            if(!empty($value['ObjectPart']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectPart']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ComponentPart:
            if(!empty($value['ComponentPart'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ComponentPart']  ?></small></h6>
              <?php }
              break;

            case csconstants::Temper:
            if(!empty($value['Temper'])){
            if(!empty($value['Temper']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Temper']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TypeName:
            if(!empty($value['TypeName'])){
            if(!empty($value['TypeName']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeName']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SlideNumber:
            if(!empty($value['SlideNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SlideNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::BagNumber:
            if(!empty($value['BagNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BagNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::TotalBags:
            if(!empty($value['TotalBags'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TotalBags']  ?></small></h6>
              <?php }
              break;

            case csconstants::BoxNumber:
            if(!empty($value['BoxNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BoxNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::TotalBoxes:
            if(!empty($value['TotalBoxes'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TotalBoxes']  ?></small></h6>
              <?php }
              break;

            case csconstants::MakersMark:
            if(!empty($value['MakersMark'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MakersMark']  ?></small></h6>
              <?php }
              break;

            case csconstants::NAGPRA:
            if(!empty($value['NAGPRA'])){
            if(!empty($value['NAGPRA']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NAGPRA']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::OldNumber:
            if(!empty($value['OldNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OldNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::AdditionalAccessionNumber:
            if(!empty($value['AdditionalAccessionNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AdditionalAccessionNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::CatalogLevel:
            if(!empty($value['CatalogLevel'])){
            if(!empty($value['CatalogLevel']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CatalogLevel']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::LevelOfControl:
            if(!empty($value['LevelOfControl'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LevelOfControl']  ?></small></h6>
              <?php }
              break;

            case csconstants::AlternateName:
            if(!empty($value['AlternateName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AlternateName']  ?></small></h6>
              <?php }
              break;

            case csconstants::AuthorName:
              if(!empty($value['Author'])){
            if(!empty($value['Author']['AuthorName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Author']['AuthorName']  ?></small></h6>
              <?php }
              }

              break;

            case csconstants::CreatorContactName:
              if(!empty($value['CreatorContact'])){
              if(!empty($value['CreatorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CreatorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ComposerContactName:
              if(!empty($value['ComposerContact'])){
            if(!empty($value['ComposerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ComposerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::NarratorContactName:
              if(!empty($value['NarratorContact'])){
            if(!empty($value['NarratorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NarratorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::EditorContactName:
              if(!empty($value['EditorContact'])){
            if(!empty($value['EditorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EditorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::PublisherContactName:
              if(!empty($value['PublisherContact'])){
            if(!empty($value['PublisherContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PublisherContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::IllustratorContactName:
              if(!empty($value['IllustratorContact'])){
            if(!empty($value['IllustratorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['IllustratorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ContributorContactName:
              if(!empty($value['ContributorContact'])){
            if(!empty($value['ContributorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ContributorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::StudioContactName:
              if(!empty($value['StudioContact'])){
            if(!empty($value['StudioContact'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StudioContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::DirectorContactName:
              if(!empty($value['DirectorContact'])){
            if(!empty($value['DirectorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DirectorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ArtDirectorContactName:
              if(!empty($value['ArtDirectorContact'])){
            if(!empty($value['ArtDirectorContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ArtDirectorContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ProducerContactName:
              if(!empty($value['ProducerContact'])){
            if(!empty($value['ProducerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ProducerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ProductionDesignerContactName:
              if(!empty($value['ProductionDesignerContact'])){
            if(!empty($value['ProductionDesignerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ProductionDesignerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::ProductionCompanyContactName:
              if(!empty($value['ProductionCompanyContact'])){
            if(!empty($value['ProductionCompanyContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ProductionCompanyContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::DistributionCompany:
              if(!empty($value['DistributionCompany'])){
            if(!empty($value['DistributionCompany']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DistributionCompany']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::WriterContactName:
              if(!empty($value['WriterContact'])){
            if(!empty($value['WriterContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['WriterContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::CinematographerContactName:
              if(!empty($value['CinematographerContact'])){
            if(!empty($value['CinematographerContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CinematographerContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::PhotographyContactName:
              if(!empty($value['PhotographyContact'])){
            if(!empty($value['PhotographyContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PhotographyContact']['ContactName']  ?></small></h6>
              <?php }
              }

              break;

            case csconstants::PublisherLocation:
            if(!empty($value['PublisherLocation'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PublisherLocation']  ?></small></h6>
              <?php }
              break;

            case csconstants::Event:
            if(!empty($value['Event'])){
            if(!empty($value['Event']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Event']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::PeopleContent:
            if(!empty($value['PeopleContent'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PeopleContent']  ?></small></h6>
              <?php }
              break;

            case csconstants::PlaceContent:
            if(!empty($value['PlaceContent'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlaceContent']  ?></small></h6>
              <?php }
              break;

            case csconstants::TownshipRangeSection:
            if(!empty($value['TownshipRangeSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TownshipRangeSection']  ?></small></h6>
              <?php }
              break;

            case csconstants::ISBN:
            if(!empty($value['ISBN'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ISBN']  ?></small></h6>
              <?php }
              break;

            case csconstants::ISSN:
            if(!empty($value['ISSN'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ISSN']  ?></small></h6>
              <?php }
              break;

            case csconstants::CallNumber:
            if(!empty($value['CallNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CallNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::CoverType:
            if(!empty($value['CoverType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CoverType']  ?></small></h6>
              <?php }
              break;

            case csconstants::TypeOfBinding:
            if(!empty($value['TypeOfBinding'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeOfBinding']  ?></small></h6>
              <?php }
              break;

            case csconstants::Language:
            if(!empty($value['Language'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Language']  ?></small></h6>
              <?php }
              break;

            case csconstants::NumberOfPages:
            if(!empty($value['NumberOfPages'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NumberOfPages']  ?></small></h6>
              <?php }
              break;

            case csconstants::NegativeNumber:
            if(!empty($value['NegativeNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['NegativeNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::FilmSize:
            if(!empty($value['FilmSize'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FilmSize']  ?></small></h6>
              <?php }
              break;

            case csconstants::Process:
            if(!empty($value['Process'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Process']  ?></small></h6>
              <?php }
              break;

            case csconstants::ImageNumber:
            if(!empty($value['ImageNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ImageNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::ImageRights:
            if(!empty($value['ImageRights'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ImageRights']  ?></small></h6>
              <?php }
              break;

            case csconstants::Copyrights:
            if(!empty($value['Copyrights'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Copyrights']  ?></small></h6>
              <?php }
              break;

            case csconstants::FindingAids:
            if(!empty($value['FindingAids'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FindingAids']  ?></small></h6>
              <?php }
              break;

            case csconstants::VolumeNumber:
            if(!empty($value['VolumeNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['VolumeNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::CompletionYear:
            if(!empty($value['CompletionYear'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CompletionYear']  ?></small></h6>
              <?php }
              break;

            case csconstants::Format:
            if(!empty($value['Format'])){
            if(!empty($value['Format']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Format']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Genre:
            if(!empty($value['Genre'])){
            if(!empty($value['Genre']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Genre']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Subgenre:
            if(!empty($value['Subgenre'])){
            if(!empty($value['Subgenre']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Subgenre']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ReleaseDate:
            if(!empty($value['ReleaseDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ReleaseDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::ProductionDate:
            if(!empty($value['ProductionDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ProductionDate']))  ?></small></h6>
              <?php }


              break;

            case csconstants::Genus:
            if(!empty($value['Genus'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Genus']  ?></small></h6>
              <?php }
              break;

            case csconstants::Stage:
            if(!empty($value['Stage'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Stage']  ?></small></h6>
              <?php }
              break;

            case csconstants::Section:
            if(!empty($value['Section'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Section']  ?></small></h6>
              <?php }
              break;

            case csconstants::QuarterSection:
            if(!empty($value['QuarterSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['QuarterSection']  ?></small></h6>
              <?php }
              break;

            case csconstants::Age:
            if(!empty($value['Age'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Age']  ?></small></h6>
              <?php }
              break;

            case csconstants::Locality:
            if(!empty($value['Locality'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Locality']  ?></small></h6>
              <?php }
              break;

            case csconstants::HabitatCommunity:
            if(!empty($value['HabitatCommunity'])){
            if(!empty($value['HabitatCommunity']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['HabitatCommunity']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TypeSpecimen:
            if(!empty($value['TypeSpecimen'])){
            if(!empty($value['TypeSpecimen']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeSpecimen']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Sex:
            if(!empty($value['Sex'])){
            if(!empty($value['Sex']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Sex']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ExoticNative:
            if(!empty($value['ExoticNative'])){
            if(!empty($value['ExoticNative']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ExoticNative']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TaxonomicNotes:
            if(!empty($value['TaxonomicNotes'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TaxonomicNotes']  ?></small></h6>
              <?php }
              break;

            case csconstants::Rare:
            if(!empty($value['Rare'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Rare']  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredDate:
            if(!empty($value['ThreatenedEndangeredDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['ThreatenedEndangeredDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredSpeciesSynonym:
            if(!empty($value['ThreatenedEndangeredSpeciesSynonym'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThreatenedEndangeredSpeciesSynonym'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredSpeciesSynonymName:
            if(!empty($value['ThreatenedEndangeredSpeciesSynonymName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThreatenedEndangeredSpeciesSynonymName']  ?></small></h6>
              <?php }
              break;

            case csconstants::ThreatenedEndangeredSpeciesStatus:
            if(!empty($value['ThreatenedEndangeredSpeciesStatus'])){
            if(!empty($value['ThreatenedEndangeredSpeciesStatus']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThreatenedEndangeredSpeciesStatus']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SubspeciesSynonym:
              if(!empty($value['SubspeciesSynonym'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SubspeciesSynonym']  ?></small></h6>
                <?php }
                break;

            case csconstants::ContinentWorldRegion:
              if(!empty($value['ContinentWorldRegion'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ContinentWorldRegion']  ?></small></h6>
                <?php }
                break;

            case csconstants::ReproductionMethod:
            if(!empty($value['ReproductionMethod'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ReproductionMethod']  ?></small></h6>
              <?php }
              break;

            case csconstants::ReferenceDatum:
            if(!empty($value['ReferenceDatum'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ReferenceDatum']  ?></small></h6>
              <?php }
              break;

            case csconstants::Aspect:
            if(!empty($value['Aspect'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Aspect']  ?></small></h6>
              <?php }
              break;

            case csconstants::FormationPeriodSubstrate:
            if(!empty($value['FormationPeriodSubstrate'])){
                if(!empty($value['FormationPeriodSubstrate']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FormationPeriodSubstrate']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::SoilType:
            if(!empty($value['SoilType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SoilType']  ?></small></h6>
              <?php }
              break;

            case csconstants::Slope:
            if(!empty($value['Slope'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Slope']  ?></small></h6>
              <?php }
              break;

            case csconstants::Unit:
            if(!empty($value['Unit'])){
            if(!empty($value['Unit']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Unit']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::DepthInMeters:
            if(!empty($value['DepthInMeters'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DepthInMeters']  ?></small></h6>
              <?php }
              break;

            case csconstants::ElevationInMeters:
            if(!empty($value['ElevationInMeters'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ElevationInMeters']  ?></small></h6>
              <?php }

              break;

            case csconstants::EthnologyCulture:
            if(!empty($value['EthnologyCulture'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EthnologyCulture']  ?></small></h6>
              <?php }
              break;

            case csconstants::Alternate1EthnologyCulture:
            if(!empty($value['Alternate1EthnologyCulture'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Alternate1EthnologyCulture']  ?></small></h6>
              <?php }
              break;

            case csconstants::Alternate2EthnologyCulture:
            if(!empty($value['Alternate2EthnologyCulture'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Alternate2EthnologyCulture']  ?></small></h6>
              <?php }
              break;

            case csconstants::AboriginalName:
            if(!empty($value['AboriginalName'])){
            if(!empty($value['AboriginalName']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AboriginalName']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::AdditionalArea:
            if(!empty($value['AdditionalArea'])){
            if(!empty($value['AdditionalArea']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AdditionalArea']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::AdditionalGroup:
            if(!empty($value['AdditionalGroup'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AdditionalGroup']  ?></small></h6>
              <?php }
              break;

            case csconstants::DescriptiveName:
            if(!empty($value['DescriptiveName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DescriptiveName']  ?></small></h6>
              <?php }
              break;

            case csconstants::PeriodSystem:
            if(!empty($value['PeriodSystem'])){
            if(!empty($value['PeriodSystem']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PeriodSystem']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::EpochSeries:
            if(!empty($value['EpochSeries'])){
            if(!empty($value['EpochSeries']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EpochSeries']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::AgeStage:
            if(!empty($value['AgeStage'])){
            if(!empty($value['AgeStage']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['AgeStage']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Composition:
            if(!empty($value['Composition'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Composition']  ?></small></h6>
              <?php }
              break;

            case csconstants::StrunzClass:
            if(!empty($value['StrunzClass'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StrunzClass']  ?></small></h6>
              <?php }
              break;

            case csconstants::StrunzDivision:
            if(!empty($value['StrunzDivision'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StrunzDivision']  ?></small></h6>
              <?php }
              break;

            case csconstants::StrunzID:
            if(!empty($value['StrunzID'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StrunzID']  ?></small></h6>
              <?php }
              break;

            case csconstants::LithologyPedotype:
            if(!empty($value['LithologyPedotype'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LithologyPedotype']  ?></small></h6>
              <?php }
              break;

            case csconstants::Formation:
            if(!empty($value['Formation'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Formation']  ?></small></h6>
              <?php }
              break;

            case csconstants::VerticalDatum:
            if(!empty($value['VerticalDatum'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['VerticalDatum']  ?></small></h6>
              <?php }
              break;

            case csconstants::Datum:
            if(!empty($value['Datum'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Datum']  ?></small></h6>
              <?php }
              break;

            case csconstants::DepositionalEnvironment:
            if(!empty($value['DepositionalEnvironment'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DepositionalEnvironment']  ?></small></h6>
              <?php }
              break;

            case csconstants::Member:
            if(!empty($value['Member'])){
            if(!empty($value['Member']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Member']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::GeoUnit:
            if(!empty($value['GeoUnit'])){
            if(!empty($value['GeoUnit']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['GeoUnit']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ThinSection:
            if(!empty($value['ThinSection'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ThinSection'] == true ? 'Yes' : 'No'  ?></small></h6>
              <?php }
              break;

            case csconstants::PatentDate:
            if(!empty($value['PatentDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PatentDate']  ?></small></h6>
              <?php }
              break;

            case csconstants::Copyright:
              if(!empty($value['Copyright'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Copyright']  ?></small></h6>
                <?php }
                break;

            case csconstants::School:
              if(!empty($value['School'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['School']  ?></small></h6>
                <?php }
                break;

            case csconstants::Lithology:
            if(!empty($value['Lithology'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Lithology']  ?></small></h6>
              <?php }
              break;

            case csconstants::Horizon:
            if(!empty($value['Horizon'])){
            if(!empty($value['Horizon']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Horizon']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::InsituFloat:
            if(!empty($value['InsituFloat'])){
            if(!empty($value['InsituFloat']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['InsituFloat']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Taphonomy:
            if(!empty($value['Taphonomy'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Taphonomy']  ?></small></h6>
              <?php }
              break;

            case csconstants::Model:
            if(!empty($value['Model'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Model']  ?></small></h6>
              <?php }
              break;

            case csconstants::Stones:
            if(!empty($value['Stones'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Stones']  ?></small></h6>
              <?php }
              break;

            case csconstants::Karats:
            if(!empty($value['Karats'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Karats']  ?></small></h6>
              <?php }
              break;

            case csconstants::Carats:
            if(!empty($value['Carats'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Carats']  ?></small></h6>
              <?php }
              break;

            case csconstants::Cut:
            if(!empty($value['Cut'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Cut']  ?></small></h6>
              <?php }
              break;

            case csconstants::Clarity:
            if(!empty($value['Clarity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Clarity']  ?></small></h6>
              <?php }
              break;

            case csconstants::TypeOfGemstone:
            if(!empty($value['TypeOfGemstone'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeOfGemstone']  ?></small></h6>
              <?php }
              break;

            case csconstants::Size:
            if(!empty($value['Size'])){
            if(!empty($value['Size']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Size']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::MetalType:
            if(!empty($value['MetalType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MetalType']  ?></small></h6>
              <?php }
              break;

            case csconstants::DrivenBy:
            if(!empty($value['DrivenBy'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DrivenBy']  ?></small></h6>
              <?php }
              break;

            case csconstants::VIN:
            if(!empty($value['VIN'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['VIN']  ?></small></h6>
              <?php }
              break;

            case csconstants::ChassisNumber:
            if(!empty($value['ChassisNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ChassisNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::Mileage:
            if(!empty($value['Mileage'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Mileage']  ?></small></h6>
              <?php }
              break;

            case csconstants::Power:
            if(!empty($value['Power'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Power']  ?></small></h6>
              <?php }
              break;

            case csconstants::EngineType:
            if(!empty($value['EngineType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EngineType']  ?></small></h6>
              <?php }
              break;

            case csconstants::EnginePosition:
            if(!empty($value['EnginePosition'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EnginePosition']  ?></small></h6>
              <?php }
              break;

            case csconstants::Transmission:
            if(!empty($value['Transmission'])){
            if(!empty($value['Transmission']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Transmission']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Passengers:
            if(!empty($value['Passengers'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Passengers']  ?></small></h6>
              <?php }
              break;

            case csconstants::FuelHighway:
            if(!empty($value['FuelHighway'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FuelHighway']  ?></small></h6>
              <?php }
              break;

            case csconstants::Acceleration:
            if(!empty($value['Acceleration'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Acceleration']  ?></small></h6>
              <?php }
              break;

            case csconstants::TopSpeed:
            if(!empty($value['TopSpeed'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TopSpeed']  ?></small></h6>
              <?php }
              break;

            case csconstants::EngineNumber:
            if(!empty($value['EngineNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['EngineNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::LicensePlateNumber:
            if(!empty($value['LicensePlateNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LicensePlateNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::TransmissionFluid:
            if(!empty($value['TransmissionFluid'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TransmissionFluid']  ?></small></h6>
              <?php }
              break;

            case csconstants::BrakeFluid:
            if(!empty($value['BrakeFluid'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BrakeFluid']  ?></small></h6>
              <?php }
              break;

            case csconstants::OilType:
            if(!empty($value['OilType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OilType']  ?></small></h6>
              <?php }
              break;

            case csconstants::FuelType:
            if(!empty($value['FuelType'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FuelType']  ?></small></h6>
              <?php }
              break;

            case csconstants::RegistrationStatus:
            if(!empty($value['RegistrationStatus'])){
            if(!empty($value['RegistrationStatus']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['RegistrationStatus']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::TitleStatus:
            if(!empty($value['TitleStatus'])){
            if(!empty($value['TitleStatus']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TitleStatus']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Paint:
            if(!empty($value['Paint'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Paint']  ?></small></h6>
              <?php }
              break;

            case csconstants::Battery:
            if(!empty($value['Battery'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Battery']  ?></small></h6>
              <?php }
              break;

            case csconstants::ShiftPattern:
            if(!empty($value['ShiftPattern'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ShiftPattern']  ?></small></h6>
              <?php }
              break;

            case csconstants::DashLayout:
            if(!empty($value['DashLayout'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['DashLayout']  ?></small></h6>
              <?php }
              break;

            case csconstants::TypeOfWine:
            if(!empty($value['TypeOfWine'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['TypeOfWine']  ?></small></h6>
              <?php }
              break;

            case csconstants::Maturity:
            if(!empty($value['Maturity'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Maturity']  ?></small></h6>
              <?php }
              break;

            case csconstants::Grape:
            if(!empty($value['Grape'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Grape']  ?></small></h6>
              <?php }
              break;

            case csconstants::Region:
            if(!empty($value['Region'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Region']  ?></small></h6>
              <?php }
              break;

            case csconstants::BottleSize:
            if(!empty($value['BottleSize'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['BottleSize']  ?></small></h6>
              <?php }
              break;

            case csconstants::FermentationPeriod:
            if(!empty($value['FermentationPeriod'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FermentationPeriod']  ?></small></h6>
              <?php }
              break;

            case csconstants::DesignerName:
              if(!empty($value['Designer'])){
            if(!empty($value['Designer']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Designer']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::Brand:
            if(!empty($value['Brand'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Brand']  ?></small></h6>
              <?php }
              break;

            case csconstants::FabricMaterial:
            if(!empty($value['FabricMaterial'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FabricMaterial']  ?></small></h6>
              <?php }
              break;

            case csconstants::SKU:
            if(!empty($value['SKU'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['SKU']  ?></small></h6>
              <?php }
              break;


            /*dimension fields */

    case csconstants::HeightMetric:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['HeightMetric'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['HeightMetric']  ?></small></h6>
          <?php } }
          break;

    case csconstants::WidthMetric:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['WidthMetric'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['WidthMetric']  ?></small></h6>
          <?php } }
          break;

    case csconstants::DepthMetric:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['DepthMetric'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['DepthMetric']  ?></small></h6>
          <?php } }
          break;

    case csconstants::DiameterMetric:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['DiameterMetric'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['DiameterMetric']  ?></small></h6>
          <?php } }
          break;

    case csconstants::WeightMetric:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['WeightMetric'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['WeightMetric']  ?></small></h6>
          <?php } }
          break;

    case csconstants::WeightImperial:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['WeightImperial'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['WeightImperial']  ?></small></h6>
          <?php } }
          break;

    case csconstants::HeightImperial:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['HeightImperial'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['HeightImperial']  ?></small></h6>
          <?php } }
          break;

    case csconstants::WidthImperial:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['WidthImperial'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['WidthImperial']  ?></small></h6>
          <?php } }
          break;

    case csconstants::DepthImperial:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['DepthImperial'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['DepthImperial']  ?></small></h6>
          <?php } }
          break;

    case csconstants::DiameterImperial:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['DiameterImperial'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['DiameterImperial']  ?></small></h6>
          <?php } }
          break;

    case csconstants::SquareMeters:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['SquareMeters'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['SquareMeters']  ?></small></h6>
          <?php } }
          break;

      case csconstants::SquareFeet:
        if(!empty($value['MainDimension'])){
        if(!empty($value['MainDimension']['SquareFeet'])){ ?>
              <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['SquareFeet']  ?></small></h6>
            <?php } }
            break;

    case csconstants::ImperialDims:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['ImperialDims'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['ImperialDims']  ?></small></h6>
          <?php } }
          break;

    case csconstants::MetricDims:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['MetricDims'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['MetricDims']  ?></small></h6>
          <?php } }
          break;

    case csconstants::DimensionDescription:
      if(!empty($value['MainDimension'])){
      if(!empty($value['MainDimension']['DimensionDescription'])){ ?>
            <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MainDimension']['DimensionDescription']['Term']  ?></small></h6>
          <?php } }
          break;


              /*richtext fields*/


              /*spectrumobject fields*/


            case csconstants::OtherNumberType:
            if(!empty($value['OtherNumberType'])){
            if(!empty($value['OtherNumberType']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['OtherNumberType']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ResponsibleDepartment:
            if(!empty($value['ResponsibleDepartment'])){
            if(!empty($value['ResponsibleDepartment']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ResponsibleDepartment']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Completeness:
            if(!empty($value['Completeness'])){
            if(!empty($value['Completeness']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Completeness']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::CompletenessDate:
            if(!empty($value['CompletenessDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['CompletenessDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::CompletenessNote:
            if(!empty($value['CompletenessNote'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['CompletenessNote']  ?></small></h6>
              <?php }


              break;

            case csconstants::MovementReferenceNumber:
            if(!empty($value['MovementReferenceNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MovementReferenceNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::MovementAuthorizerContactName:
              if(!empty($value['MovementAuthorizer'])){
            if(!empty($value['MovementAuthorizer']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MovementAuthorizer']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::MovementAuthorizationDate:
            if(!empty($value['MovementAuthorizationDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['MovementAuthorizationDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::MovementContactName:
              if(!empty($value['MovementContact'])){
            if(!empty($value['MovementContact']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MovementContact']['ContactName']  ?></small></h6>
              <?php }
              }
              break;

            case csconstants::MovementMethod:
            if(!empty($value['MovementMethod'])){
            if(!empty($value['MovementMethod']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MovementMethod']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::MovementMemo:
            if(!empty($value['MovementMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MovementMemo']  ?></small></h6>
              <?php }
              break;

            case csconstants::MovementReason:
            if(!empty($value['MovementReason'])){
            if(!empty($value['MovementReason']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['MovementReason']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::PlannedRemoval:
            if(!empty($value['PlannedRemoval'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['PlannedRemoval']  ?></small></h6>
              <?php }
              break;

            case csconstants::LocationReferenceNameNumber:
            if(!empty($value['LocationReferenceNameNumber'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LocationReferenceNameNumber']  ?></small></h6>
              <?php }
              break;

            case csconstants::LocationType:
            if(!empty($value['LocationType'])){
            if(!empty($value['LocationType']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LocationType']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::LocationAccessMemo:
            if(!empty($value['LocationAccessMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LocationAccessMemo']  ?></small></h6>
              <?php }
              break;

            case csconstants::LocationConditionMemo:
            if(!empty($value['LocationConditionMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LocationConditionMemo']  ?></small></h6>
              <?php }
              break;

            case csconstants::LocationConditionDate:
            if(!empty($value['LocationConditionDate'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill">'.date('m/d/Y',strtotime($value['LocationConditionDate']))  ?></small></h6>
              <?php }
              break;

            case csconstants::LocationSecurityMemo:
            if(!empty($value['LocationSecurityMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['LocationSecurityMemo']  ?></small></h6>
              <?php }


              break;

            case csconstants::ObjectNameCurrency:
            if(!empty($value['ObjectNameCurrency'])){
            if(!empty($value['ObjectNameCurrency']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectNameCurrency']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectNameLevel:
            if(!empty($value['ObjectNameLevel'])){
            if(!empty($value['ObjectNameLevel']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectNameLevel']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectNameNote:
            if(!empty($value['ObjectNameNote'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectNameNote']  ?></small></h6>
              <?php }
              break;

            case csconstants::ObjectNameSystem:
            if(!empty($value['ObjectNameSystem'])){
            if(!empty($value['ObjectNameSystem']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectNameSystem']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectNameType:
            if(!empty($value['ObjectNameType'])){
            if(!empty($value['ObjectNameType']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectNameType']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::ObjectNameTitleLanguage:
            if(!empty($value['ObjectNameTitleLanguage'])){
            if(!empty($value['ObjectNameTitleLanguage']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['ObjectNameTitleLanguage']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::FieldCollectionMethod:
            if(!empty($value['FieldCollectionMethod'])){
            if(!empty($value['FieldCollectionMethod']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FieldCollectionMethod']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::FieldCollectionPlace:
            if(!empty($value['FieldCollectionPlace'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FieldCollectionPlace']  ?></small></h6>
              <?php }
              break;

            case csconstants::FieldCollectionSourceContactName:
            if(!empty($value['FieldCollectionSource']['ContactName'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FieldCollectionSource']['ContactName']  ?></small></h6>
              <?php }
              break;

            case csconstants::FieldCollectionMemo:
            if(!empty($value['FieldCollectionMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['FieldCollectionMemo']  ?></small></h6>
              <?php }
              break;

            case csconstants::GeologicalComplexName:
            if(!empty($value['GeologicalComplexName'])){
            if(!empty($value['GeologicalComplexName']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['GeologicalComplexName']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::Habitat:
            if(!empty($value['Habitat'])){
            if(!empty($value['Habitat']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['Habitat']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::HabitatMemo:
            if(!empty($value['HabitatMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['HabitatMemo']  ?></small></h6>
              <?php }
              break;

            case csconstants::StratigraphicUnitName:
            if(!empty($value['StratigraphicUnitName'])){
            if(!empty($value['StratigraphicUnitName']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StratigraphicUnitName']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::StratigraphicUnitType:
            if(!empty($value['StratigraphicUnitType'])){
            if(!empty($value['StratigraphicUnitType']['Term'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StratigraphicUnitType']['Term']  ?></small></h6>
                <?php }
              }
              break;

            case csconstants::StratigraphicUnitMemo:
            if(!empty($value['StratigraphicUnitMemo'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['StratigraphicUnitMemo']  ?></small></h6>
              <?php }
              break;

              /*udf fields*/



            case csconstants::UserDefined1:
            if(!empty($value['UserDefined1'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined1']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined2:
            if(!empty($value['UserDefined2'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined2']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined3:
            if(!empty($value['UserDefined3'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined3']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined4:
            if(!empty($value['UserDefined4'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined4']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined5:
            if(!empty($value['UserDefined5'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined5']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined6:
            if(!empty($value['UserDefined6'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined6']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined7:
            if(!empty($value['UserDefined7'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined7']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined8:
            if(!empty($value['UserDefined8'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined8']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined9:
            if(!empty($value['UserDefined9'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined9']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined10:
            if(!empty($value['UserDefined10'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined10']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefined11:
            if(!empty($value['UserDefined11'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined11']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined12:
            if(!empty($value['UserDefined12'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined12']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined13:
            if(!empty($value['UserDefined13'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined13']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined14:
            if(!empty($value['UserDefined14'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined14']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined15:
            if(!empty($value['UserDefined15'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined15']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined16:
            if(!empty($value['UserDefined16'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined16']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined17:
            if(!empty($value['UserDefined17'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined17']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined18:
            if(!empty($value['UserDefined18'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined18']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined19:
            if(!empty($value['UserDefined19'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined19']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined20:
            if(!empty($value['UserDefined20'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined20']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefined21:
            if(!empty($value['UserDefined21'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined21']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined22:
            if(!empty($value['UserDefined22'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined22']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined23:
            if(!empty($value['UserDefined23'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined23']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined24:
            if(!empty($value['UserDefined24'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined24']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined25:
            if(!empty($value['UserDefined25'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined25']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined26:
            if(!empty($value['UserDefined26'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined26']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined27:
            if(!empty($value['UserDefined27'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined27']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined28:
            if(!empty($value['UserDefined28'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined28']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined29:
            if(!empty($value['UserDefined29'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined29']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined30:
            if(!empty($value['UserDefined30'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined30']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefined31:
            if(!empty($value['UserDefined31'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined31']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined32:
            if(!empty($value['UserDefined32'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined32']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined33:
            if(!empty($value['UserDefined33'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined33']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined34:
            if(!empty($value['UserDefined34'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined34']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined35:
            if(!empty($value['UserDefined35'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined35']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined36:
            if(!empty($value['UserDefined36'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined36']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined37:
            if(!empty($value['UserDefined37'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined37']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined38:
            if(!empty($value['UserDefined38'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined38']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined39:
            if(!empty($value['UserDefined39'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined39']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefined40:
            if(!empty($value['UserDefined40'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefined40']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefinedDate1:
            if(!empty($value['UserDefinedDate1'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefinedDate1']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefinedDate2:
            if(!empty($value['UserDefinedDate2'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefinedDate2']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefinedNumber1:
            if(!empty($value['UserDefinedNumber1'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefinedNumber1']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefinedNumber2:
            if(!empty($value['UserDefinedNumber2'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefinedNumber2']  ?></small></h6>
              <?php }

              break;

            case csconstants::UserDefinedCurrency1:
            if(!empty($value['UserDefinedCurrency1'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefinedCurrency1']  ?></small></h6>
              <?php }
              break;

            case csconstants::UserDefinedCurrency2:
            if(!empty($value['UserDefinedCurrency2'])){ ?>
                <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $value['UserDefinedCurrency2']  ?></small></h6>
              <?php }

              break;

              default:
              break;

               /*end*/


          }
        } ?>
        </div>
      </div>
    <?php
  }
  public function getCommaSeparatedFieldsForListPage(){
    $db = \Drupal::database();

    $tblnm = "clsobjects_fields";
    $settblnm = $tblnm;

    $query = $db->select($settblnm, 'c')
      ->fields('c', ['fieldname'])
      ->condition('fieldtype', 'ObjectList');

    $result = $query->execute()->fetchAllAssoc('fieldname');

    $values = implode(',', array_keys($result));

    return $values;

  }


}
