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
              case csconstants::InventoryNumber:
                if(!empty($value['InventoryNumber'])){ ?>
                  <h6 class="font-normal" title="<?php echo $value['InventoryNumber']; ?>" >
                    <small class="flex-fill">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value['InventoryNumber']  ?></a>
                    </small>
                  </h6>
                <?php }

                break;
           case csconstants::ArtistName:
              if(!empty($value['ArtistName'])){
                    ?>
                  <h6 class="font-normal" title="<?php echo $value['ArtistName']; ?>" >
                    <small class="flex-fill">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value['ArtistName']  ?></a>
                    </small>
                  </h6>
                <?php
                  }

                  break;
               case csconstants::ArtistFirst:
              if(!empty($value['ArtistFirst'])){
                    ?>
                  <h6 class="font-normal" title="<?php echo $value['ArtistName']; ?>" >
                    <small class="flex-fill">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value['ArtistFirst']  ?></a>
                    </small>
                  </h6>
                <?php
                  }

                  break;

              case csconstants::ArtistLast:
              if(!empty($value['ArtistLast'])){
                    ?>
                  <h6 class="font-normal" title="<?php echo $value['ArtistLast']; ?>" >
                    <small class="flex-fill">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value['ArtistLast']  ?></a>
                    </small>
                  </h6>
                <?php
                  }

                  break;
             case csconstants::ArtistYears:
              if(!empty($value['ArtistYears'])){
                    ?>
                  <h6 class="font-normal" title="<?php echo $value['ArtistYears']; ?>" >
                    <small class="flex-fill">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value['ArtistYears']  ?></a>
                    </small>
                  </h6>
                <?php
                  }

                  break;
             case csconstants::ArtistLocale:
              if(!empty($value['ArtistLocale'])){
                    ?>
                  <h6 class="font-normal" title="<?php echo $value['ArtistYears']; ?>" >
                    <small class="flex-fill">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value['ArtistLocale']  ?></a>
                    </small>
                  </h6>
                <?php
                  }

                  break;
               case csconstants::ArtistBio:
              if(!empty($value['ArtistBio'])){
                    ?>
                  <h6 class="font-normal" title="<?php echo $value['ArtistBio']; ?>" >
                    <small class="flex-fill">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value['ArtistBio']  ?></a>
                    </small>
                  </h6>
                <?php
                  }

                  break;
             case  csconstants::CollectionName:
                  if(!empty($value['CollectionName'])){
                    ?>
                  <h6 class="font-normal" title="<?php echo $value['CollectionName']; ?>" >
                    <small class="flex-fill">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value['CollectionName']  ?></a>
                    </small>
                  </h6>
                <?php
                  }


                  break;
              case  csconstants::FullCollectionName:
                  if(!empty($value['FullCollectionName'])){
                    ?>
                  <h6 class="font-normal" title="<?php echo $value['FullCollectionName']; ?>" >
                    <small class="flex-fill">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value['FullCollectionName']  ?></a>
                    </small>
                  </h6>
                <?php
                  }

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
                  <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForCollection('<?php echo \Drupal::request()->getHost() ?>', <?php echo $value['Collection']['CollectionId']; ?>)"><?php echo $value['Collection']['CollectionName']  ?></a></small></h6>
                <?php }
                }

                break;

              case csconstants::FullCollectionName:
                if(!empty($value['Collection'])){
                if(!empty($value['Collection']['FullCollectionName'])){ ?>
                  <h6 class="font-normal cs-theme-label-withunderline"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForCollection('<?php echo \Drupal::request()->getHost() ?>', <?php echo $value['Collection']['CollectionId']; ?>)"><?php echo $value['Collection']['FullCollectionName']  ?></a></small></h6>
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
                      <h6 class="font-normal"><small class="flex-fill"><a href="javascript:;"onclick="return getmoredetailsForArtist('<?php echo \Drupal::request()->getHost() ?>', <?php echo $value['Artist']['ArtistId']; ?>)">
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

                  <h6 class="font-normal cs-theme-label-withunderline">
                    <small class="flex-fill">
                      <a href="javascript:;" onclick="return getmoredetails(<?php echo $value['ObjectId']; ?>,'<?php echo $dataOrderBy ?>','<?php echo $dataSearch ?>',<?php echo $datapageNo ?>)"><?php echo $value['Title']  ?></a>
                    </small>
                  </h6>
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
      $showFieldLabelNames = 1; //temp only

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

              case csconstants::NomenclatureObjectName:
                if(!empty($artObjData['NomenclatureObjectName'])){ ?>
                    <p class="my-2">
                    <?php if($showFieldLabelNames==1){ ?>
                    <span class="object_detail_fieldlabel"><?php echo csconstants::NomenclatureObjectNameFieldLabel ?>:</span>
                    <?php } ?>
                      <?php echo $artObjData['NomenclatureObjectName']  ?>
                    </p>
                    <?php }
              break;

            case csconstants::ObjectStatus:
              if(!empty($artObjData['ObjectStatus'])){ ?>
                    <p class="my-2">
                    <?php if($showFieldLabelNames==1){ ?>
                    <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectStatusFieldLabel ?>:</span>
                    <?php } ?>
                    <?php echo $artObjData['ObjectStatus']  ?>
                    </p>
                <?php }

                  break;

            case csconstants::ObjectType:
              if(!empty($artObjData['ObjectType'])){
              if(!empty($artObjData['ObjectType']['ObjectTypeName'])){ ?>
                    <p class="my-2">
                    <?php if($showFieldLabelNames==1){ ?>
                    <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectTypeFieldLabel ?>:</span>
                    <?php } ?>
                      <?php echo $artObjData['ObjectType']['ObjectTypeName']  ?>
                    </p>
                  <?php }
                  }
                  break;

            case csconstants::InventoryNumber:
              // print_r($artObjData); die();
              if(!empty($artObjData['InventoryNumber'])){?>
                <p class="my-2">
                <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::InventoryNumberFieldLabel ?>:</span>
                  <?php } ?>
                    <?php echo $artObjData['InventoryNumber']  ?></p>
                <?php }

                  break;

            case csconstants::LocationName:
              if(!empty($artObjData['Location'])){
                if(!empty($artObjData['Location']['LocationName'])){ ?>
                      <p class="my-2">
                      <?php if($showFieldLabelNames==1){ ?>
                      <span class="object_detail_fieldlabel"><?php echo csconstants::LocationNameFieldLabel ?>:</span>
                      <?php } ?>
                        <?php echo $artObjData['Location']['LocationName']  ?></p>
                <?php }
              }
            break;

            case csconstants::FullLocationName:
                if(!empty($artObjData['Location'])){
                if(!empty($artObjData['Location']['FullLocationName'])){ ?>
                  <p class="my-2">
                  <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FullLocationNameFieldLabel ?>:</span>
                  <?php } ?>
                    <?php echo $artObjData['Location']['FullLocationName']  ?></p>
                <?php }
                }
            break;

            case csconstants::PermanentLocationName:
              if(!empty($artObjData['PermanentLocation'])){
              if(!empty($artObjData['PermanentLocation']['LocationName'])){ ?>
                    <p class="my-2">
                    <?php if($showFieldLabelNames==1){ ?>
                    <span class="object_detail_fieldlabel"><?php echo csconstants::PermanentLocationNameFieldLabel ?>:</span>
                    <?php } ?>
                      <?php echo $artObjData['PermanentLocation']['LocationName']  ?></p>
                  <?php }
                }
                break;

            case csconstants::PermanentFullLocationName:
              if(!empty($artObjData['PermanentLocation'])){
              if(!empty($artObjData['PermanentLocation']['FullLocationName'])){ ?>
                    <p class="my-2">
                    <?php if($showFieldLabelNames==1){ ?>
                    <span class="object_detail_fieldlabel"><?php echo csconstants::PermanentFullLocationNameFieldLabel ?>:</span>
                    <?php } ?>
                      <?php echo $artObjData['PermanentLocation']['FullLocationName']  ?></p>
                  <?php }
                }
                break;

        case csconstants::CollectionName:
          if(!empty($artObjData['Collection'])){
          if(!empty($artObjData['Collection']['CollectionName'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CollectionNameFieldLabel ?>:</span>
                  <?php } ?>
                <a href="javascript:;" onclick="return getmoredetailsForCollection('<?php echo site_url() ?>','<?php echo $artObjData['Collection']['CollectionId']; ?>')"><?php echo $artObjData['Collection']['CollectionName']  ?></a></p>
            <?php }
          }

            break;

    case csconstants::FullCollectionName:
      if(!empty($artObjData['Collection'])){
      if(!empty($artObjData['Collection']['FullCollectionName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FullCollectionNameFieldLabel ?>:</span>
                  <?php } ?>
            <a href="javascript:;" onclick="return getmoredetailsForCollection('<?php echo site_url() ?>','<?php echo $artObjData['Collection']['CollectionId']; ?>')"><?php echo $artObjData['Collection']['FullCollectionName']  ?></a></p>
        <?php }
      }

        break;

    case csconstants::CreditLine:
    if(!empty($artObjData['CreditLine'])){ ?>
          <p class="my-2">
                <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CreditLineFieldLabel ?>:</span>
                  <?php } ?>
                    <?php echo $artObjData['CreditLine']  ?></p>
        <?php }

        break;

    case csconstants::ArtistName:
      if(!empty($artObjData['Artist']) && isset($artObjData['Artist'])){
    if(!empty($artObjData['Artist']['ArtistName'])){ ?>
          <p class="mb-3">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ArtistNameFieldLabel ?>:</span>
                  <?php } ?>
            <a href="javascript:;" onclick="return getmoredetailsForArtist('<?php echo site_url() ?>','<?php echo $artObjData['Artist']['ArtistId']; ?>')">
            <?php echo $artObjData['Artist']['ArtistName'] ?>
          </a></p>
        <?php }
      }
        break;

    case csconstants::AdditionalArtists:
    if(!empty($artObjData['AdditionalArtists'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AdditionalArtistsFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo implodeChildArrayProperty($artObjData['AdditionalArtists'],"Artist","ArtistId","ArtistName"); ?></p>
        <?php }

        break;

    case csconstants::Maker:
    if(!empty($artObjData['Maker'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MakerFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Maker']  ?></p>
        <?php }

        break;


    case csconstants::AlternateTitle:
    if(!empty($artObjData['AlternateTitle'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AlternateTitleFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['AlternateTitle']  ?></p>
        <?php }

        break;

    case csconstants::ObjectDate:
    if(!empty($artObjData['ObjectDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ObjectDate']  ?></p>
        <?php }

        break;

    case csconstants::Medium:
    if(!empty($artObjData['Medium'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MediumFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Medium']  ?></p>
        <?php }

        break;

    case csconstants::LocationStatus:
    if(!empty($artObjData['LocationStatus'])){
    if(!empty($artObjData['LocationStatus']['Term'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LocationStatusFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['LocationStatus']['Term']  ?></p>
          <?php }
        }

        break;

    case csconstants::InventoryDate:
    if(!empty($artObjData['InventoryDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::InventoryDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['InventoryDate']))  ?></p>
        <?php }

        break;

    case csconstants::InventoryContactName:
      if(!empty($artObjData['InventoryContact'])){
    if(!empty($artObjData['InventoryContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::InventoryContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['InventoryContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::Form:
    if(!empty($artObjData['Form'])){ ?>
        <p class="my-2">
        <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FormFieldLabel ?>:</span>
                  <?php } ?>
          <?php echo $artObjData['Form']  ?></p>
        <?php }

        break;

    case csconstants::Subject:
    if(!empty($artObjData['Subject'])){ ?>
        <p class="my-2">
                <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubjectFieldLabel ?>:</span>
                  <?php } ?>
                    <?php echo $artObjData['Subject']  ?></p>
        <?php }

        break;

    case csconstants::CategoryStyle:
    if(!empty($artObjData['CategoryStyle'])){ ?>
        <p class="my-2">
        <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CategoryStyleFieldLabel ?>:</span>
                  <?php } ?>
          <?php echo $artObjData['CategoryStyle']  ?></p>
        <?php }

        break;

    case csconstants::CountryOrigin:
    if(!empty($artObjData['CountryOrigin'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CountryOriginFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CountryOrigin']  ?></p>
      <?php }

        break;

    case csconstants::Edition:
    if(!empty($artObjData['Edition'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::EditionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Edition']  ?></p>
        <?php }

        break;

    case csconstants::SuitePortfolio:
    if(!empty($artObjData['SuitePortfolio'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SuitePortfolioFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SuitePortfolio']  ?></p>
        <?php }

        break;

    case csconstants::CatalogRaisonne:
    if(!empty($artObjData['CatalogRaisonne'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CatalogRaisonneFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CatalogRaisonne']  ?></p>
        <?php }

        break;

    case csconstants::RFIDTagNumber:
    if(!empty($artObjData['RFIDTagNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::RFIDTagNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['RFIDTagNumber']  ?></p>
        <?php }

        break;

    case csconstants::Term:
    if(!empty($artObjData['Term'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TermFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Term']  ?></p>
        <?php }

        //art musueum fields
        break;

    case csconstants::CatalogNumber:
    if(!empty($artObjData['CatalogNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CatalogNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CatalogNumber']  ?></p>
        <?php }
        break;

    case csconstants::OtherNumbers:
    if(!empty($artObjData['OtherNumbers'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::OtherNumbersFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['OtherNumbers']  ?></p>
        <?php }
        break;

    case csconstants::ItemCount:
    if(!empty($artObjData['ItemCount'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ItemCountFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ItemCount']  ?></p>
        <?php }
        break;

    case csconstants::CatalogerContactName:
      if(!empty($artObjData['CatalogerContact'])){
      if(!empty($artObjData['CatalogerContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CatalogerContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CatalogerContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::CatalogDate:
    if(!empty($artObjData['CatalogDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
            <span class="object_detail_fieldlabel"><?php echo csconstants::CatalogDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['CatalogDate']))  ?></p>
        <?php }
        break;

    case csconstants::CollectionTitle:
    if(!empty($artObjData['CollectionTitle'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CollectionTitleFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CollectionTitle']  ?></p>
        <?php }
        break;

    case csconstants::CollectionNumber:
    if(!empty($artObjData['CollectionNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CollectionNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CollectionNumber']  ?></p>
        <?php }
        break;

    case csconstants::Material:
    if(!empty($artObjData['Material'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MaterialFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Material']  ?></p>
        <?php }
        break;

    case csconstants::Technique:
    if(!empty($artObjData['Technique'])){
    if(!empty($artObjData['Technique']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TechniqueFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Technique']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Color:
    if(!empty($artObjData['Color'])){
    if(!empty($artObjData['Color']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ColorFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Color']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::StateOfOrigin:
    if(!empty($artObjData['StateOfOrigin'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StateOfOriginFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['StateOfOrigin']  ?></p>
        <?php }
        break;

    case csconstants::CountyOfOrigin:
    if(!empty($artObjData['CountyOfOrigin'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CountyOfOriginFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CountyOfOrigin']  ?></p>
        <?php }
        break;

    case csconstants::CityOfOrigin:
    if(!empty($artObjData['CityOfOrigin'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CityOfOriginFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CityOfOrigin']  ?></p>
        <?php }
        break;

    case csconstants::State:
    if(!empty($artObjData['State'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['State']  ?></p>
        <?php }
        break;

    case csconstants::Duration:
    if(!empty($artObjData['Duration'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DurationFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Duration']  ?></p>
        <?php }

        break;

    case csconstants::RevisedNomenclature:
    if(!empty($artObjData['RevisedNomenclature'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::RevisedNomenclatureFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['RevisedNomenclature']  ?></p>
        <?php }
        break;

    case csconstants::PreviousCatalogNumber:
    if(!empty($artObjData['PreviousCatalogNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PreviousCatalogNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PreviousCatalogNumber']  ?></p>
        <?php }
        break;

    case csconstants::FieldSpecimenNumber:
    if(!empty($artObjData['FieldSpecimenNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FieldSpecimenNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['FieldSpecimenNumber']  ?></p>
        <?php }
        break;

    case csconstants::StatusDate:
    if(!empty($artObjData['StatusDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StatusDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['StatusDate']  ?></p>
        <?php }
        break;

    case csconstants::StorageUnit:
    if(!empty($artObjData['StorageUnit'])){
    if(!empty($artObjData['StorageUnit']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StorageUnitFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['StorageUnit']['Term']  ?></p>
        <?php }
        }
        break;

    case csconstants::CollectionDate:
    if(!empty($artObjData['CollectionDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CollectionDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['CollectionDate']))  ?></p>
        <?php }
        break;

    case csconstants::CollectorContactName:
      if(!empty($artObjData['CollectorContact'])){
    if(!empty($artObjData['CollectorContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CollectorContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CollectorContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::CollectorPlace:
    if(!empty($artObjData['CollectorPlace'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CollectorPlaceFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CollectorPlace']  ?></p>
        <?php }
        break;

    case csconstants::CatalogFolder:
        if(!empty($artObjData['CatalogFolder'])){  ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CatalogFolderFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CatalogFolder'] == true ? 'Yes' : 'No'  ?></p>
        <?php }
        break;

    case csconstants::IdentifiedByContactName:
    if(!empty($artObjData['IdentifiedByContact'])){
    if(!empty($artObjData['IdentifiedByContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::IdentifiedByContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['IdentifiedByContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::IdentifiedDate:
    if(!empty($artObjData['IdentifiedDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::IdentifiedDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['IdentifiedDate'])) ?></p>
        <?php }
        break;

    case csconstants::EminentFigureContactName:
    if(!empty($artObjData['EminentFigureContact'])){
      if(!empty($artObjData['EminentFigureContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::EminentFigureContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['EminentFigureContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::EminentOrganizationContactName:
      if(!empty($artObjData['EminentOrganizationContact'])){
    if(!empty($artObjData['EminentOrganizationContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::EminentOrganizationContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['EminentOrganizationContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::ControlledProperty:
    if(!empty($artObjData['ControlledProperty'])){ ?>
      <p class="my-2">
      <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ControlledPropertyFieldLabel ?>:</span>
                  <?php } ?>
        <?php echo $artObjData['ControlledProperty'] == true ? 'Yes' : 'No'  ?></p>
        <?php }
        break;

    case csconstants::ArtistMakerName:
      if(!empty($artObjData["ArtistMaker"])){
      if(!empty($artObjData["ArtistMaker"]['ArtistName'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ArtistMakerNameFieldLabel ?>:</span>
                  <?php } ?>
            <a href="javascript:;" onclick="return getmoredetailsForArtist('<?php echo site_url() ?>','<?php echo $artObjData['ArtistMaker']['ArtistId']; ?>')">
            <?php echo $artObjData['ArtistMaker']['ArtistName'] ?>
          </a></p>
          <?php }
      }
        break;

    case csconstants::AdditionalArtistMakers:
      if(!empty($artObjData['AdditionalArtistMakers'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                    <span class="object_detail_fieldlabel"><?php echo csconstants::AdditionalArtistMakersFieldLabel ?>:</span>
                    <?php } ?>
              <?php echo implodeChildArrayProperty($artObjData['AdditionalArtistMakers'],"ArtistMaker","ArtistId","ArtistName"); ?></p>
          <?php }

          break;

    case csconstants::TaxonomicSerialNumber:
    if(!empty($artObjData['TaxonomicSerialNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TaxonomicSerialNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TaxonomicSerialNumber']  ?></p>
        <?php }
        break;

    case csconstants::Kingdom:
    if(!empty($artObjData['Kingdom'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::KingdomFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Kingdom']  ?></p>
        <?php }
        break;

    case csconstants::PhylumDivision:
    if(!empty($artObjData['PhylumDivision'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PhylumDivisionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PhylumDivision']  ?></p>
        <?php }
        break;

    case csconstants::CSClass:
    if(!empty($artObjData['Class'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CSClassFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Class']  ?></p>
        <?php }
        break;

    case csconstants::Order:
    if(!empty($artObjData['Order'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::OrderFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Order']  ?></p>
        <?php }
        break;

    case csconstants::Family:
    if(!empty($artObjData['Family'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FamilyFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Family']  ?></p>
        <?php }
        break;

    case csconstants::SubFamily:
    if(!empty($artObjData['SubFamily'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubFamilyFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SubFamily']  ?></p>
        <?php }
        break;

    case csconstants::ScientificName:
    if(!empty($artObjData['ScientificName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ScientificNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ScientificName']  ?></p>
        <?php }
        break;

    case csconstants::CommonName:
    if(!empty($artObjData['CommonName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CommonNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CommonName']  ?></p>
        <?php }
        break;

    case csconstants::Species:
    if(!empty($artObjData['Species'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SpeciesFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Species']  ?></p>
        <?php }
        break;

    case csconstants::SpeciesAuthorName:
      if(!empty($artObjData['SpeciesAuthor'])){
      if(!empty($artObjData['SpeciesAuthor']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SpeciesAuthorNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SpeciesAuthor']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::SpeciesAuthorDate:
    if(!empty($artObjData['SpeciesAuthorDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SpeciesAuthorDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['SpeciesAuthorDate']))  ?></p>
        <?php }
        break;

    case csconstants::Subspecies:
    if(!empty($artObjData['Subspecies'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Subspecies']  ?></p>
        <?php }
        break;

      case csconstants::SubspeciesAuthorityContactName:
        if(!empty($artObjData['SubspeciesAuthorityContact'])){
      if(!empty($artObjData['SubspeciesAuthorityContact']['ContactName'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesAuthorityContactNameFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['SubspeciesAuthorityContact']['ContactName']  ?></p>
          <?php }
        }
          break;

    case csconstants::SubspeciesAuthorName:
      if(!empty($artObjData['SubspeciesAuthor'])){
    if(!empty($artObjData['SubspeciesAuthor']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesAuthorNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SubspeciesAuthor']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::SubspeciesAuthorDate:
    if(!empty($artObjData['SubspeciesAuthorDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesAuthorDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['SubspeciesAuthorDate']))  ?></p>
        <?php }
        break;

    case csconstants::SubspeciesYear:
    if(!empty($artObjData['SubspeciesYear'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesYearFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SubspeciesYear']  ?></p>
        <?php }
        break;

    case csconstants::SubspeciesVariety:
    if(!empty($artObjData['SubspeciesVariety'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesVarietyFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SubspeciesVariety']  ?></p>
        <?php }
        break;

    case csconstants::SubspeciesVarietyAuthorityContactName:
      if(!empty($artObjData['SubspeciesVarietyAuthorityContact'])){
      if(!empty($artObjData['SubspeciesVarietyAuthorityContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesVarietyAuthorityContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SubspeciesVarietyAuthorityContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::SubspeciesVarietyYear:
    if(!empty($artObjData['SubspeciesVarietyYear'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesVarietyYearFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SubspeciesVarietyYear']  ?></p>
        <?php }
        break;

    case csconstants::SubspeciesForma:
    if(!empty($artObjData['SubspeciesForma'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesFormaFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SubspeciesForma']  ?></p>
        <?php }
        break;

    case csconstants::SubspeciesFormaAuthorityContactName:
      if(!empty($artObjData['SubspeciesFormaAuthorityContact'])){
    if(!empty($artObjData['SubspeciesFormaAuthorityContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesFormaAuthorityContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SubspeciesFormaAuthorityContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::SubspeciesFormaYear:
    if(!empty($artObjData['SubspeciesFormaYear'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesFormaYearFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SubspeciesFormaYear']  ?></p>
        <?php }
        break;

    case csconstants::StudyNumber:
      if(!empty($artObjData['StudyNumber'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StudyNumberFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['StudyNumber']  ?></p>
          <?php }
          break;

    case csconstants::AlternateName:
    if(!empty($artObjData['AlternateName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AlternateNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['AlternateName']  ?></p>
        <?php }
        break;

    case csconstants::CulturalID:
    if(!empty($artObjData['CulturalID'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CulturalIDFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CulturalID']  ?></p>
        <?php }
        break;

    case csconstants::CultureOfUse:
    if(!empty($artObjData['CultureOfUse'])){
    if(!empty($artObjData['CultureOfUse']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CultureOfUseFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['CultureOfUse']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ManufactureDate:
    if(!empty($artObjData['ManufactureDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ManufactureDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['ManufactureDate']))  ?></p>
        <?php }
        break;

    case csconstants::UseDate:
    if(!empty($artObjData['UseDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::UseDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UseDate']  ?></p>
        <?php }
        break;

    case csconstants::TimePeriod:
    if(!empty($artObjData['TimePeriod'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TimePeriodFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TimePeriod']  ?></p>
        <?php }
        break;

    case csconstants::HistoricCulturalPeriod:
    if(!empty($artObjData['HistoricCulturalPeriod'])){
    if(!empty($artObjData['HistoricCulturalPeriod']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::HistoricCulturalPeriodFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['HistoricCulturalPeriod']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ManufacturingTechnique:
    if(!empty($artObjData['ManufacturingTechnique'])){
    if(!empty($artObjData['ManufacturingTechnique']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ManufacturingTechniqueFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ManufacturingTechnique']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Material:
    if(!empty($artObjData['Material'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MaterialFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Material']  ?></p>
        <?php }
        break;

    case csconstants::BroadClassOfMaterial:
    if(!empty($artObjData['BroadClassOfMaterial'])){
    if(!empty($artObjData['BroadClassOfMaterial']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::BroadClassOfMaterialFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['BroadClassOfMaterial']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::SpecificClassOfMaterial:
    if(!empty($artObjData['SpecificClassOfMaterial'])){
    if(!empty($artObjData['SpecificClassOfMaterial']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SpecificClassOfMaterialFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['SpecificClassOfMaterial']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Quantity:
    if(!empty($artObjData['Quantity'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::QuantityFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Quantity']  ?></p>
        <?php }
        break;

    case csconstants::PlaceOfManufactureCountry:
    if(!empty($artObjData['PlaceOfManufactureCountry'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PlaceOfManufactureCountryFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PlaceOfManufactureCountry']  ?></p>
        <?php }
        break;

    case csconstants::PlaceOfManufactureState:
    if(!empty($artObjData['PlaceOfManufactureState'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PlaceOfManufactureStateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PlaceOfManufactureState']  ?></p>
        <?php }
        break;

    case csconstants::PlaceOfManufactureCounty:
    if(!empty($artObjData['PlaceOfManufactureCounty'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PlaceOfManufactureCountyFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PlaceOfManufactureCounty']  ?></p>
        <?php }
        break;

    case csconstants::PlaceOfManufactureCity:
    if(!empty($artObjData['PlaceOfManufactureCity'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PlaceOfManufactureCityFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PlaceOfManufactureCity']  ?></p>
        <?php }
        break;

    case csconstants::OtherManufacturingSite:
    if(!empty($artObjData['OtherManufacturingSite'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::OtherManufacturingSiteFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['OtherManufacturingSite']  ?></p>
        <?php }
        break;

    case csconstants::Latitude:
    if(!empty($artObjData['Latitude'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LatitudeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Latitude']  ?></p>
        <?php }
        break;

    case csconstants::Longitude:
    if(!empty($artObjData['Longitude'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LongitudeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Longitude']  ?></p>
        <?php }
        break;

    case csconstants::UTMCoordinates:
    if(!empty($artObjData['UTMCoordinates'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::UTMCoordinatesFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UTMCoordinates']  ?></p>
        <?php }
        break;

    case csconstants::TownshipRangeSection:
    if(!empty($artObjData['TownshipRangeSection'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TownshipRangeSectionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TownshipRangeSection']  ?></p>
        <?php }
        break;

    case csconstants::FieldSiteNumber:
    if(!empty($artObjData['FieldSiteNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FieldSiteNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['FieldSiteNumber']  ?></p>
        <?php }
        break;

    case csconstants::StateSiteNumber:
    if(!empty($artObjData['StateSiteNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StateSiteNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['StateSiteNumber']  ?></p>
        <?php }
        break;

    case csconstants::SiteName:
    if(!empty($artObjData['SiteName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SiteNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SiteName']  ?></p>
        <?php }
        break;

    case csconstants::SiteNumber:
    if(!empty($artObjData['SiteNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SiteNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SiteNumber']  ?></p>
        <?php }
        break;

    case csconstants::DecorativeMotif:
    if(!empty($artObjData['DecorativeMotif'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DecorativeMotifFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['DecorativeMotif']  ?></p>
        <?php }
        break;

    case csconstants::DecorativeTechnique:
    if(!empty($artObjData['DecorativeTechnique'])){
    if(!empty($artObjData['DecorativeTechnique']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DecorativeTechniqueFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['DecorativeTechnique']['Term']  ?></p>
          <?php }
        }

        break;

    case csconstants::Reproduction:
    if(!empty($artObjData['Reproduction'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ReproductionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Reproduction']  ?></p>
        <?php }
        break;

    case csconstants::ObjectForm:
        if(!empty($artObjData['ObjectForm'])){
        if(!empty($artObjData['ObjectForm']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectFormFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ObjectForm']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ObjectPart:
    if(!empty($artObjData['ObjectPart'])){
    if(!empty($artObjData['ObjectPart']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectPartFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ObjectPart']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ComponentPart:
    if(!empty($artObjData['ComponentPart'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ComponentPartFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ComponentPart']  ?></p>
        <?php }
        break;

    case csconstants::Temper:
    if(!empty($artObjData['Temper'])){
    if(!empty($artObjData['Temper']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TemperFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Temper']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::TypeName:
    if(!empty($artObjData['TypeName'])){
    if(!empty($artObjData['TypeName']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TypeNameFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['TypeName']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::SlideNumber:
    if(!empty($artObjData['SlideNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SlideNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SlideNumber']  ?></p>
        <?php }
        break;

    case csconstants::BagNumber:
    if(!empty($artObjData['BagNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::BagNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['BagNumber']  ?></p>
        <?php }
        break;

    case csconstants::TotalBags:
    if(!empty($artObjData['TotalBags'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TotalBagsFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TotalBags']  ?></p>
        <?php }
        break;

    case csconstants::BoxNumber:
    if(!empty($artObjData['BoxNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::BoxNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['BoxNumber']  ?></p>
        <?php }
        break;

    case csconstants::TotalBoxes:
    if(!empty($artObjData['TotalBoxes'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TotalBoxesFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TotalBoxes']  ?></p>
        <?php }
        break;

    case csconstants::MakersMark:
    if(!empty($artObjData['MakersMark'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MakersMarkFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['MakersMark']  ?></p>
        <?php }
        break;

    case csconstants::NAGPRA:
    if(!empty($artObjData['NAGPRA'])){
    if(!empty($artObjData['NAGPRA']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::NAGPRAFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['NAGPRA']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::OldNumber:
    if(!empty($artObjData['OldNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::OldNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['OldNumber']  ?></p>
        <?php }
        break;

    case csconstants::AdditionalAccessionNumber:
    if(!empty($artObjData['AdditionalAccessionNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AdditionalAccessionNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['AdditionalAccessionNumber']  ?></p>
        <?php }
        break;

    case csconstants::CatalogLevel:
    if(!empty($artObjData['CatalogLevel'])){
    if(!empty($artObjData['CatalogLevel']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CatalogLevelFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['CatalogLevel']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::LevelOfControl:
    if(!empty($artObjData['LevelOfControl'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LevelOfControlFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['LevelOfControl']  ?></p>
        <?php }
        break;

    case csconstants::AlternateName:
    if(!empty($artObjData['AlternateName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AlternateNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['AlternateName']  ?></p>
        <?php }
        break;

    case csconstants::AuthorName:
      if(!empty($artObjData['Author'])){
    if(!empty($artObjData['Author']['AuthorName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AuthorNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Author']['AuthorName']  ?></p>
        <?php }
      }

        break;

    case csconstants::AdditionalAuthors:
      if(!empty($artObjData['AdditionalAuthors'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                    <span class="object_detail_fieldlabel"><?php echo csconstants::AdditionalAuthorsFieldLabel ?>:</span>
                    <?php } ?>
              <?php echo implodeChildArrayPropertyWithoutLink($artObjData['AdditionalAuthors'],"Author","AuthorId","AuthorName"); ?></p>
          <?php }

          break;

    case csconstants::CreatorContactName:
      if(!empty($artObjData['CreatorContact'])){
      if(!empty($artObjData['CreatorContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CreatorContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CreatorContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::ComposerContactName:
      if(!empty($artObjData['ComposerContact'])){
    if(!empty($artObjData['ComposerContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ComposerContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ComposerContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::NarratorContactName:
      if(!empty($artObjData['NarratorContact'])){
    if(!empty($artObjData['NarratorContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::NarratorContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['NarratorContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::EditorContactName:
      if(!empty($artObjData['EditorContact'])){
    if(!empty($artObjData['EditorContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::EditorContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['EditorContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::PublisherContactName:
      if(!empty($artObjData['PublisherContact'])){
    if(!empty($artObjData['PublisherContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PublisherContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PublisherContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::IllustratorContactName:
      if(!empty($artObjData['IllustratorContact'])){
    if(!empty($artObjData['IllustratorContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::IllustratorContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['IllustratorContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::ContributorContactName:
      if(!empty($artObjData['ContributorContact'])){
    if(!empty($artObjData['ContributorContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ContributorContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ContributorContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::StudioContactName:
      if(!empty($artObjData['StudioContact'])){
    if(!empty($artObjData['StudioContact'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StudioContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['StudioContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::DirectorContactName:
      if(!empty($artObjData['DirectorContact'])){
    if(!empty($artObjData['DirectorContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DirectorContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['DirectorContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::ArtDirectorContactName:
      if(!empty($artObjData['ArtDirectorContact'])){
    if(!empty($artObjData['ArtDirectorContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ArtDirectorContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ArtDirectorContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::ProducerContactName:
      if(!empty($artObjData['ProducerContact'])){
    if(!empty($artObjData['ProducerContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ProducerContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ProducerContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::ProductionDesignerContactName:
      if(!empty($artObjData['ProductionDesignerContact'])){
    if(!empty($artObjData['ProductionDesignerContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ProductionDesignerContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ProductionDesignerContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::ProductionCompanyContactName:
      if(!empty($artObjData['ProductionCompanyContact'])){
    if(!empty($artObjData['ProductionCompanyContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ProductionCompanyContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ProductionCompanyContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::DistributionCompany:
      if(!empty($artObjData['DistributionCompany'])){
    if(!empty($artObjData['DistributionCompany']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DistributionCompanyFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['DistributionCompany']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::WriterContactName:
      if(!empty($artObjData['WriterContact'])){
    if(!empty($artObjData['WriterContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::WriterContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['WriterContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::CinematographerContactName:
      if(!empty($artObjData['CinematographerContact'])){
    if(!empty($artObjData['CinematographerContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CinematographerContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CinematographerContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::PhotographyContactName:
      if(!empty($artObjData['PhotographyContact'])){
    if(!empty($artObjData['PhotographyContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PhotographyContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PhotographyContact']['ContactName']  ?></p>
        <?php }
      }

        break;

    case csconstants::PublisherLocation:
    if(!empty($artObjData['PublisherLocation'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PublisherLocationFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PublisherLocation']  ?></p>
        <?php }
        break;

    case csconstants::Event:
    if(!empty($artObjData['Event'])){
    if(!empty($artObjData['Event']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::EventFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Event']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::PeopleContent:
    if(!empty($artObjData['PeopleContent'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PeopleContentFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PeopleContent']  ?></p>
        <?php }
        break;

    case csconstants::PlaceContent:
    if(!empty($artObjData['PlaceContent'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PlaceContentFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PlaceContent']  ?></p>
        <?php }
        break;

    case csconstants::TownshipRangeSection:
    if(!empty($artObjData['TownshipRangeSection'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TownshipRangeSectionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TownshipRangeSection']  ?></p>
        <?php }
        break;

    case csconstants::ISBN:
    if(!empty($artObjData['ISBN'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ISBNFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ISBN']  ?></p>
        <?php }
        break;

    case csconstants::ISSN:
    if(!empty($artObjData['ISSN'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ISSNFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ISSN']  ?></p>
        <?php }
        break;

    case csconstants::CallNumber:
    if(!empty($artObjData['CallNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CallNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CallNumber']  ?></p>
        <?php }
        break;

    case csconstants::CoverType:
    if(!empty($artObjData['CoverType'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CoverTypeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CoverType']  ?></p>
        <?php }
        break;

    case csconstants::TypeOfBinding:
    if(!empty($artObjData['TypeOfBinding'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TypeOfBindingFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TypeOfBinding']  ?></p>
        <?php }
        break;

    case csconstants::Language:
    if(!empty($artObjData['Language'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LanguageFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Language']  ?></p>
        <?php }
        break;

    case csconstants::NumberOfPages:
    if(!empty($artObjData['NumberOfPages'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::NumberOfPagesFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['NumberOfPages']  ?></p>
        <?php }
        break;

    case csconstants::NegativeNumber:
    if(!empty($artObjData['NegativeNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::NegativeNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['NegativeNumber']  ?></p>
        <?php }
        break;

    case csconstants::FilmSize:
    if(!empty($artObjData['FilmSize'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FilmSizeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['FilmSize']  ?></p>
        <?php }
        break;

    case csconstants::Process:
    if(!empty($artObjData['Process'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ProcessFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Process']  ?></p>
        <?php }
        break;

    case csconstants::ImageNumber:
    if(!empty($artObjData['ImageNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ImageNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ImageNumber']  ?></p>
        <?php }
        break;

    case csconstants::ImageRights:
    if(!empty($artObjData['ImageRights'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ImageRightsFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ImageRights']  ?></p>
        <?php }
        break;

    case csconstants::Copyrights:
    if(!empty($artObjData['Copyrights'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CopyrightsFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Copyrights']  ?></p>
        <?php }
        break;

    case csconstants::FindingAids:
    if(!empty($artObjData['FindingAids'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FindingAidsFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['FindingAids']  ?></p>
        <?php }
        break;

    case csconstants::VolumeNumber:
    if(!empty($artObjData['VolumeNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::VolumeNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['VolumeNumber']  ?></p>
        <?php }
        break;

    case csconstants::CompletionYear:
    if(!empty($artObjData['CompletionYear'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CompletionYearFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CompletionYear']  ?></p>
        <?php }
        break;

    case csconstants::Format:
    if(!empty($artObjData['Format'])){
    if(!empty($artObjData['Format']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FormatFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Format']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Genre:
    if(!empty($artObjData['Genre'])){
    if(!empty($artObjData['Genre']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::GenreFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Genre']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Subgenre:
    if(!empty($artObjData['Subgenre'])){
    if(!empty($artObjData['Subgenre']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubgenreFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Subgenre']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ReleaseDate:
    if(!empty($artObjData['ReleaseDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ReleaseDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['ReleaseDate']))  ?></p>
        <?php }
        break;

    case csconstants::ProductionDate:
    if(!empty($artObjData['ProductionDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ProductionDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['ProductionDate']))  ?></p>
        <?php }
        break;

    case csconstants::Genus:
    if(!empty($artObjData['Genus'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::GenusFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Genus']  ?></p>
        <?php }
        break;

    case csconstants::Stage:
    if(!empty($artObjData['Stage'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StageFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Stage']  ?></p>
        <?php }
        break;

    case csconstants::Section:
    if(!empty($artObjData['Section'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SectionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Section']  ?></p>
        <?php }
        break;

    case csconstants::QuarterSection:
    if(!empty($artObjData['QuarterSection'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::QuarterSectionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['QuarterSection']  ?></p>
        <?php }
        break;

    case csconstants::Age:
    if(!empty($artObjData['Age'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AgeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Age']  ?></p>
        <?php }
        break;

    case csconstants::Locality:
    if(!empty($artObjData['Locality'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LocalityFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Locality']  ?></p>
        <?php }
        break;

    case csconstants::HabitatCommunity:
    if(!empty($artObjData['HabitatCommunity'])){
    if(!empty($artObjData['HabitatCommunity']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::HabitatCommunityFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['HabitatCommunity']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::TypeSpecimen:
    if(!empty($artObjData['TypeSpecimen'])){
    if(!empty($artObjData['TypeSpecimen']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TypeSpecimenFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['TypeSpecimen']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Sex:
    if(!empty($artObjData['Sex'])){
    if(!empty($artObjData['Sex']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SexFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Sex']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ExoticNative:
    if(!empty($artObjData['ExoticNative'])){
    if(!empty($artObjData['ExoticNative']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ExoticNativeFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ExoticNative']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::TaxonomicNotes:
    if(!empty($artObjData['TaxonomicNotes'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TaxonomicNotesFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TaxonomicNotes']  ?></p>
        <?php }
        break;

    case csconstants::Rare:
    if(!empty($artObjData['Rare'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::RareFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Rare']  ?></p>
        <?php }
        break;

    case csconstants::ThreatenedEndangeredDate:
    if(!empty($artObjData['ThreatenedEndangeredDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ThreatenedEndangeredDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['ThreatenedEndangeredDate']))  ?></p>
        <?php }
        break;

    case csconstants::ThreatenedEndangeredSpeciesSynonym:
    if(!empty($artObjData['ThreatenedEndangeredSpeciesSynonym'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ThreatenedEndangeredSpeciesSynonymFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ThreatenedEndangeredSpeciesSynonym'] == true ? 'Yes' : 'No'  ?></p>
        <?php }
        break;

    case csconstants::ThreatenedEndangeredSpeciesSynonymName:
    if(!empty($artObjData['ThreatenedEndangeredSpeciesSynonymName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ThreatenedEndangeredSpeciesSynonymNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ThreatenedEndangeredSpeciesSynonymName']  ?></p>
        <?php }
        break;

    case csconstants::ThreatenedEndangeredSpeciesStatus:
    if(!empty($artObjData['ThreatenedEndangeredSpeciesStatus'])){
    if(!empty($artObjData['ThreatenedEndangeredSpeciesStatus']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ThreatenedEndangeredSpeciesStatusFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ThreatenedEndangeredSpeciesStatus']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::SubspeciesSynonym:
      if(!empty($artObjData['SubspeciesSynonym'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesSynonymFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['SubspeciesSynonym']  ?></p>
          <?php }
          break;

    case csconstants::ContinentWorldRegion:
      if(!empty($artObjData['ContinentWorldRegion'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ContinentWorldRegionFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ContinentWorldRegion']  ?></p>
          <?php }
          break;

    case csconstants::ReproductionMethod:
    if(!empty($artObjData['ReproductionMethod'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ReproductionMethodFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ReproductionMethod']  ?></p>
        <?php }
        break;

    case csconstants::ReferenceDatum:
    if(!empty($artObjData['ReferenceDatum'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ReferenceDatumFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ReferenceDatum']  ?></p>
        <?php }
        break;

    case csconstants::Aspect:
    if(!empty($artObjData['Aspect'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AspectFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Aspect']  ?></p>
        <?php }
        break;

    case csconstants::FormationPeriodSubstrate:
    if(!empty($artObjData['FormationPeriodSubstrate'])){
          if(!empty($artObjData['FormationPeriodSubstrate']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FormationPeriodSubstrateFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['FormationPeriodSubstrate']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::SoilType:
    if(!empty($artObjData['SoilType'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SoilTypeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SoilType']  ?></p>
        <?php }
        break;

    case csconstants::Slope:
    if(!empty($artObjData['Slope'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SlopeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Slope']  ?></p>
        <?php }
        break;

    case csconstants::Unit:
    if(!empty($artObjData['Unit'])){
    if(!empty($artObjData['Unit']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::UnitFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Unit']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::DepthInMeters:
    if(!empty($artObjData['DepthInMeters'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DepthInMetersFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['DepthInMeters']  ?></p>
        <?php }
        break;

    case csconstants::ElevationInMeters:
    if(!empty($artObjData['ElevationInMeters'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ElevationInMetersFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ElevationInMeters']  ?></p>
        <?php }

        break;

    case csconstants::EthnologyCulture:
    if(!empty($artObjData['EthnologyCulture'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::EthnologyCultureFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['EthnologyCulture']  ?></p>
        <?php }
        break;

    case csconstants::Alternate1EthnologyCulture:
    if(!empty($artObjData['Alternate1EthnologyCulture'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::Alternate1EthnologyCultureFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Alternate1EthnologyCulture']  ?></p>
        <?php }
        break;

    case csconstants::Alternate2EthnologyCulture:
    if(!empty($artObjData['Alternate2EthnologyCulture'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::Alternate2EthnologyCultureFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Alternate2EthnologyCulture']  ?></p>
        <?php }
        break;

    case csconstants::AboriginalName:
    if(!empty($artObjData['AboriginalName'])){
    if(!empty($artObjData['AboriginalName']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AboriginalNameFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['AboriginalName']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::AdditionalArea:
    if(!empty($artObjData['AdditionalArea'])){
    if(!empty($artObjData['AdditionalArea']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AdditionalAreaFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['AdditionalArea']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::AdditionalGroup:
    if(!empty($artObjData['AdditionalGroup'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AdditionalGroupFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['AdditionalGroup']  ?></p>
        <?php }
        break;

    case csconstants::DescriptiveName:
    if(!empty($artObjData['DescriptiveName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DescriptiveNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['DescriptiveName']  ?></p>
        <?php }
        break;

    case csconstants::PeriodSystem:
    if(!empty($artObjData['PeriodSystem'])){
    if(!empty($artObjData['PeriodSystem']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PeriodSystemFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['PeriodSystem']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::EpochSeries:
    if(!empty($artObjData['EpochSeries'])){
    if(!empty($artObjData['EpochSeries']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::EpochSeriesFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['EpochSeries']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::AgeStage:
    if(!empty($artObjData['AgeStage'])){
    if(!empty($artObjData['AgeStage']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AgeStageFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['AgeStage']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Composition:
    if(!empty($artObjData['Composition'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CompositionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Composition']  ?></p>
        <?php }
        break;

    case csconstants::StrunzClass:
    if(!empty($artObjData['StrunzClass'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StrunzClassFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['StrunzClass']  ?></p>
        <?php }
        break;

    case csconstants::StrunzDivision:
    if(!empty($artObjData['StrunzDivision'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StrunzDivisionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['StrunzDivision']  ?></p>
        <?php }
        break;

    case csconstants::StrunzID:
    if(!empty($artObjData['StrunzID'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StrunzIDFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['StrunzID']  ?></p>
        <?php }
        break;

    case csconstants::LithologyPedotype:
    if(!empty($artObjData['LithologyPedotype'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LithologyPedotypeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['LithologyPedotype']  ?></p>
        <?php }
        break;

    case csconstants::Formation:
    if(!empty($artObjData['Formation'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FormationFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Formation']  ?></p>
        <?php }
        break;

    case csconstants::VerticalDatum:
    if(!empty($artObjData['VerticalDatum'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::VerticalDatumFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['VerticalDatum']  ?></p>
        <?php }
        break;

    case csconstants::Datum:
    if(!empty($artObjData['Datum'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DatumFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Datum']  ?></p>
        <?php }
        break;

    case csconstants::DepositionalEnvironment:
    if(!empty($artObjData['DepositionalEnvironment'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DepositionalEnvironmentFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['DepositionalEnvironment']  ?></p>
        <?php }
        break;

    case csconstants::Member:
    if(!empty($artObjData['Member'])){
    if(!empty($artObjData['Member']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MemberFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Member']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::GeoUnit:
    if(!empty($artObjData['GeoUnit'])){
    if(!empty($artObjData['GeoUnit']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::GeoUnitFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['GeoUnit']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ThinSection:
    if(!empty($artObjData['ThinSection'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ThinSectionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ThinSection'] == true ? 'Yes' : 'No'  ?></p>
        <?php }
        break;

    case csconstants::PatentDate:
    if(!empty($artObjData['PatentDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PatentDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PatentDate']  ?></p>
        <?php }
        break;

    case csconstants::Copyright:
      if(!empty($artObjData['Copyright'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CopyrightFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Copyright']  ?></p>
          <?php }
          break;

    case csconstants::School:
      if(!empty($artObjData['School'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SchoolFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['School']  ?></p>
          <?php }
          break;

    case csconstants::Lithology:
    if(!empty($artObjData['Lithology'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LithologyFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Lithology']  ?></p>
        <?php }
        break;

    case csconstants::Horizon:
    if(!empty($artObjData['Horizon'])){
    if(!empty($artObjData['Horizon']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::HorizonFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Horizon']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::InsituFloat:
    if(!empty($artObjData['InsituFloat'])){
    if(!empty($artObjData['InsituFloat']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::InsituFloatFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['InsituFloat']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Taphonomy:
    if(!empty($artObjData['Taphonomy'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TaphonomyFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Taphonomy']  ?></p>
        <?php }
        break;

    case csconstants::Model:
    if(!empty($artObjData['Model'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ModelFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Model']  ?></p>
        <?php }
        break;

    case csconstants::Stones:
    if(!empty($artObjData['Stones'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StonesFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Stones']  ?></p>
        <?php }
        break;

    case csconstants::Karats:
    if(!empty($artObjData['Karats'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::KaratsFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Karats']  ?></p>
        <?php }
        break;

    case csconstants::Carats:
    if(!empty($artObjData['Carats'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CaratsFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Carats']  ?></p>
        <?php }
        break;

    case csconstants::Cut:
    if(!empty($artObjData['Cut'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CutFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Cut']  ?></p>
        <?php }
        break;

    case csconstants::Clarity:
    if(!empty($artObjData['Clarity'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ClarityFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Clarity']  ?></p>
        <?php }
        break;

    case csconstants::TypeOfGemstone:
    if(!empty($artObjData['TypeOfGemstone'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TypeOfGemstoneFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TypeOfGemstone']  ?></p>
        <?php }
        break;

    case csconstants::Size:
    if(!empty($artObjData['Size'])){
    if(!empty($artObjData['Size']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SizeFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Size']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::MetalType:
    if(!empty($artObjData['MetalType'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MetalTypeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['MetalType']  ?></p>
        <?php }
        break;

    case csconstants::DrivenBy:
    if(!empty($artObjData['DrivenBy'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DrivenByFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['DrivenBy']  ?></p>
        <?php }
        break;

    case csconstants::VIN:
    if(!empty($artObjData['VIN'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::VINFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['VIN']  ?></p>
        <?php }
        break;

    case csconstants::ChassisNumber:
    if(!empty($artObjData['ChassisNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ChassisNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ChassisNumber']  ?></p>
        <?php }
        break;

    case csconstants::Mileage:
    if(!empty($artObjData['Mileage'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MileageFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Mileage']  ?></p>
        <?php }
        break;

    case csconstants::Power:
    if(!empty($artObjData['Power'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PowerFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Power']  ?></p>
        <?php }
        break;

    case csconstants::EngineType:
    if(!empty($artObjData['EngineType'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::EngineTypeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['EngineType']  ?></p>
        <?php }
        break;

    case csconstants::EnginePosition:
    if(!empty($artObjData['EnginePosition'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::EnginePositionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['EnginePosition']  ?></p>
        <?php }
        break;

    case csconstants::Transmission:
    if(!empty($artObjData['Transmission'])){
    if(!empty($artObjData['Transmission']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TransmissionFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Transmission']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Passengers:
    if(!empty($artObjData['Passengers'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PassengersFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Passengers']  ?></p>
        <?php }
        break;

    case csconstants::FuelHighway:
    if(!empty($artObjData['FuelHighway'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FuelHighwayFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['FuelHighway']  ?></p>
        <?php }
        break;

    case csconstants::Acceleration:
    if(!empty($artObjData['Acceleration'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::AccelerationFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Acceleration']  ?></p>
        <?php }
        break;

    case csconstants::TopSpeed:
    if(!empty($artObjData['TopSpeed'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TopSpeedFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TopSpeed']  ?></p>
        <?php }
        break;

    case csconstants::EngineNumber:
    if(!empty($artObjData['EngineNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::EngineNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['EngineNumber']  ?></p>
        <?php }
        break;

    case csconstants::LicensePlateNumber:
    if(!empty($artObjData['LicensePlateNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LicensePlateNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['LicensePlateNumber']  ?></p>
        <?php }
        break;

    case csconstants::TransmissionFluid:
    if(!empty($artObjData['TransmissionFluid'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TransmissionFluidFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TransmissionFluid']  ?></p>
        <?php }
        break;

    case csconstants::BrakeFluid:
    if(!empty($artObjData['BrakeFluid'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::BrakeFluidFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['BrakeFluid']  ?></p>
        <?php }
        break;

    case csconstants::OilType:
    if(!empty($artObjData['OilType'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::OilTypeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['OilType']  ?></p>
        <?php }
        break;

    case csconstants::FuelType:
    if(!empty($artObjData['FuelType'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FuelTypeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['FuelType']  ?></p>
        <?php }
        break;

    case csconstants::RegistrationStatus:
    if(!empty($artObjData['RegistrationStatus'])){
    if(!empty($artObjData['RegistrationStatus']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::RegistrationStatusFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['RegistrationStatus']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::TitleStatus:
    if(!empty($artObjData['TitleStatus'])){
    if(!empty($artObjData['TitleStatus']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TitleStatusFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['TitleStatus']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Paint:
    if(!empty($artObjData['Paint'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PaintFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Paint']  ?></p>
        <?php }
        break;

    case csconstants::Battery:
    if(!empty($artObjData['Battery'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::BatteryFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Battery']  ?></p>
        <?php }
        break;

    case csconstants::ShiftPattern:
    if(!empty($artObjData['ShiftPattern'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ShiftPatternFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ShiftPattern']  ?></p>
        <?php }
        break;

    case csconstants::DashLayout:
    if(!empty($artObjData['DashLayout'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DashLayoutFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['DashLayout']  ?></p>
        <?php }
        break;

    case csconstants::TypeOfWine:
    if(!empty($artObjData['TypeOfWine'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::TypeOfWineFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['TypeOfWine']  ?></p>
        <?php }
        break;

    case csconstants::Maturity:
    if(!empty($artObjData['Maturity'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MaturityFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Maturity']  ?></p>
        <?php }
        break;

    case csconstants::Grape:
    if(!empty($artObjData['Grape'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::GrapeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Grape']  ?></p>
        <?php }
        break;

    case csconstants::Region:
    if(!empty($artObjData['Region'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::RegionFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Region']  ?></p>
        <?php }
        break;

    case csconstants::BottleSize:
    if(!empty($artObjData['BottleSize'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::BottleSizeFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['BottleSize']  ?></p>
        <?php }
        break;

    case csconstants::FermentationPeriod:
    if(!empty($artObjData['FermentationPeriod'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FermentationPeriodFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['FermentationPeriod']  ?></p>
        <?php }
        break;

    case csconstants::DesignerName:
      if(!empty($artObjData['Designer'])){
    if(!empty($artObjData['Designer']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DesignerNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Designer']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::Brand:
    if(!empty($artObjData['Brand'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::BrandFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['Brand']  ?></p>
        <?php }
        break;

    case csconstants::FabricMaterial:
    if(!empty($artObjData['FabricMaterial'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FabricMaterialFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['FabricMaterial']  ?></p>
        <?php }
        break;

    case csconstants::SKU:
    if(!empty($artObjData['SKU'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SKUFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['SKU']  ?></p>
        <?php }
        break;

      /* dimension fields */

      case csconstants::HeightMetric:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['HeightMetric'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::HeightMetricFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['HeightMetric']  ?></p>
            <?php } }
            break;

      case csconstants::WidthMetric:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['WidthMetric'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::WidthMetricFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['WidthMetric']  ?></p>
            <?php } }
            break;

      case csconstants::DepthMetric:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['DepthMetric'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DepthMetricFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['DepthMetric']  ?></p>
            <?php } }
            break;

      case csconstants::DiameterMetric:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['DiameterMetric'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DiameterMetricFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['DiameterMetric']  ?></p>
            <?php } }
            break;

      case csconstants::WeightMetric:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['WeightMetric'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::WeightMetricFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['WeightMetric']  ?></p>
            <?php } }
            break;

      case csconstants::WeightImperial:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['WeightImperial'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::WeightImperialFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['WeightImperial']  ?></p>
            <?php } }
            break;

      case csconstants::HeightImperial:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['HeightImperial'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::HeightImperialFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['HeightImperial']  ?></p>
            <?php } }
            break;

      case csconstants::WidthImperial:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['WidthImperial'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::WidthImperialFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['WidthImperial']  ?></p>
            <?php } }
            break;

      case csconstants::DepthImperial:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['DepthImperial'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DepthImperialFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['DepthImperial']  ?></p>
            <?php } }
            break;

      case csconstants::DiameterImperial:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['DiameterImperial'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DiameterImperialFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['DiameterImperial']  ?></p>
            <?php } }
            break;

      case csconstants::SquareMeters:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['SquareMeters'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SquareMetersFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['SquareMeters']  ?></p>
            <?php } }
            break;

        case csconstants::SquareFeet:
          if(!empty($artObjData['MainDimension'])){
          if(!empty($artObjData['MainDimension']['SquareFeet'])){ ?>
                <p class="my-2">
                <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::SquareFeetFieldLabel ?>:</span>
                  <?php } ?>
                  <?php echo $artObjData['MainDimension']['SquareFeet']  ?></p>
              <?php } }
              break;

      case csconstants::ImperialDims:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['ImperialDims'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ImperialDimsFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['ImperialDims']  ?></p>
            <?php } }
            break;

      case csconstants::MetricDims:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['MetricDims'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MetricDimsFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['MetricDims']  ?></p>
            <?php } }
            break;

      case csconstants::DimensionDescription:
        if(!empty($artObjData['MainDimension'])){
        if(!empty($artObjData['MainDimension']['DimensionDescription'])){ ?>
              <p class="my-2">
              <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::DimensionDescriptionFieldLabel ?>:</span>
                  <?php } ?>
                <?php echo $artObjData['MainDimension']['DimensionDescription']['Term']  ?></p>
            <?php } }
            break;


      /*richtext fields*/

    case csconstants::DimensionMemo:
      if(!empty($artObjData['MainDimension'])){
      if(!empty($artObjData['MainDimension']['DimensionMemo'])){ ?>
            <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::DimensionMemoFieldLabel ?>:</span></p>
                  <?php } ?>
            <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['MainDimension']['DimensionMemo']  ?></div>
            <?php } }

          break;

    case csconstants::InventoryMemo:
    if(!empty($artObjData['InventoryMemo'])){ ?>
            <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::InventoryMemoFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="my-2 cstheme-show-more-richtext"><?php echo $artObjData['InventoryMemo']  ?></div>
        <?php }

        break;

    case csconstants::ObjectDescription:
    if(!empty($artObjData['ObjectDescription'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::ObjectDescriptionFieldLabel ?>:</span></p>
                  <?php } ?>
            <div class="my-2 cstheme-show-more-richtext"><?php echo $artObjData['ObjectDescription']  ?></div>
        <?php }
        break;

    case csconstants::Signatures:
    if(!empty($artObjData['Signatures'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::SignaturesFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['Signatures']  ?></div>
        <?php }

        break;

    case csconstants::Inscriptions:
    if(!empty($artObjData['Inscriptions'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::InscriptionsFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['Inscriptions']  ?></div>
        <?php }

        break;

    case csconstants::Labels:
    if(!empty($artObjData['Labels'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::LabelsFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['Labels']  ?></div>
        <?php }

        break;

    case csconstants::Provenance:
    if(!empty($artObjData['Provenance'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::ProvenanceFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['Provenance']  ?></div>
        <?php }

        break;

    case csconstants::ReferenceNotes:
    if(!empty($artObjData['ReferenceNotes'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::ReferenceNotesFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['ReferenceNotes']  ?></div>
        <?php }

        break;

    case csconstants::ResearchNotes:
    if(!empty($artObjData['ResearchNotes'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::ResearchNotesFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['ResearchNotes']  ?></div>
        <?php }

        break;

    case csconstants::StaffNotes:
    if(!empty($artObjData['StaffNotes'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::StaffNotesFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['StaffNotes']  ?></div>
        <?php }

        break;

    case csconstants::RelatedCollections:
    if(!empty($artObjData['RelatedCollections'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::RelatedCollectionsFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['RelatedCollections']  ?></div>
        <?php }
        break;

    case csconstants::KeyDescriptor:
    if(!empty($artObjData['KeyDescriptor'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::KeyDescriptorFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['KeyDescriptor']  ?></div>
        <?php }
        break;

    case csconstants::WithinSiteProveniance:
    if(!empty($artObjData['WithinSiteProveniance'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::WithinSiteProvenianceFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['WithinSiteProveniance']  ?></div>
        <?php }
        break;

    case csconstants::SubspeciesDescriptiveName:
    if(!empty($artObjData['SubspeciesDescriptiveName'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::SubspeciesDescriptiveNameFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['SubspeciesDescriptiveName']  ?></div>
        <?php }

        break;

    case csconstants::History:
    if(!empty($artObjData['History'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::HistoryFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['History']  ?></div>
        <?php }
        break;

    case csconstants::Transcription:
    if(!empty($artObjData['Transcription'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::TranscriptionFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['Transcription']  ?></div>
        <?php }
        break;

    case csconstants::CastAndCrew:
    if(!empty($artObjData['CastAndCrew'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::CastAndCrewFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['CastAndCrew']  ?></div>
        <?php }
        break;

    case csconstants::Synopsis:
    if(!empty($artObjData['Synopsis'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::SynopsisFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['Synopsis']  ?></div>
        <?php }
        break;

    case csconstants::Waterbody:
    if(!empty($artObjData['Waterbody'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::WaterbodyFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['Waterbody']  ?></div>
        <?php }
        break;

    case csconstants::AssociatedSpecies:
    if(!empty($artObjData['AssociatedSpecies'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::AssociatedSpeciesFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['AssociatedSpecies']  ?></div>
        <?php }
        break;

    case csconstants::Drainage:
    if(!empty($artObjData['Drainage'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::DrainageFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['Drainage']  ?></div>
        <?php }
        break;

    case csconstants::ObjectUse:
    if(!empty($artObjData['ObjectUse'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::ObjectUseFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['ObjectUse']  ?></div>
        <?php }

        break;

    case csconstants::StartingInstructions:
    if(!empty($artObjData['StartingInstructions'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::StartingInstructionsFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['StartingInstructions']  ?></div>
        <?php }
        break;

    case csconstants::RegistrationNotes:
    if(!empty($artObjData['RegistrationNotes'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::RegistrationNotesFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['RegistrationNotes']  ?></div>
        <?php }
        break;

    case csconstants::TitleStatusNotes:
    if(!empty($artObjData['TitleStatusNotes'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::TitleStatusNotesFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['TitleStatusNotes']  ?></div>
        <?php }
        break;

    case csconstants::RepairsMade:
    if(!empty($artObjData['RepairsMade'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::RepairsMadeFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['RepairsMade']  ?></div>
        <?php }
        break;


    /*spectrumobject fields*/

    case csconstants::OtherNumberType:
    if(!empty($artObjData['OtherNumberType'])){
    if(!empty($artObjData['OtherNumberType']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::OtherNumberTypeFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['OtherNumberType']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ResponsibleDepartment:
    if(!empty($artObjData['ResponsibleDepartment'])){
    if(!empty($artObjData['ResponsibleDepartment']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ResponsibleDepartmentFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ResponsibleDepartment']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Completeness:
    if(!empty($artObjData['Completeness'])){
    if(!empty($artObjData['Completeness']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CompletenessFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Completeness']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::CompletenessDate:
    if(!empty($artObjData['CompletenessDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CompletenessDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['CompletenessDate']))  ?></p>
        <?php }

        break;

    case csconstants::CompletenessNote:
    if(!empty($artObjData['CompletenessNote'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::CompletenessNoteFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['CompletenessNote']  ?></p>
        <?php }

        break;

    case csconstants::MovementReferenceNumber:
    if(!empty($artObjData['MovementReferenceNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MovementReferenceNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['MovementReferenceNumber']  ?></p>
        <?php }
        break;

    case csconstants::MovementAuthorizerContactName:
      if(!empty($artObjData['MovementAuthorizer'])){
    if(!empty($artObjData['MovementAuthorizer']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MovementAuthorizerContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['MovementAuthorizer']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::MovementAuthorizationDate:
    if(!empty($artObjData['MovementAuthorizationDate'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MovementAuthorizationDateFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo date('m/d/Y',strtotime($artObjData['MovementAuthorizationDate']))  ?></p>
        <?php }
        break;

    case csconstants::MovementContactName:
      if(!empty($artObjData['MovementContact'])){
    if(!empty($artObjData['MovementContact']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MovementContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['MovementContact']['ContactName']  ?></p>
        <?php }
      }
        break;

    case csconstants::MovementMethod:
    if(!empty($artObjData['MovementMethod'])){
    if(!empty($artObjData['MovementMethod']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MovementMethodFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['MovementMethod']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::MovementMemo:
    if(!empty($artObjData['MovementMemo'])){ ?>
        <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::MovementMemoFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['MovementMemo']  ?></div>
        <?php }
        break;

    case csconstants::MovementReason:
    if(!empty($artObjData['MovementReason'])){
    if(!empty($artObjData['MovementReason']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::MovementReasonFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['MovementReason']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::PlannedRemoval:
    if(!empty($artObjData['PlannedRemoval'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::PlannedRemovalFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['PlannedRemoval']  ?></p>
        <?php }
        break;

    case csconstants::LocationReferenceNameNumber:
    if(!empty($artObjData['LocationReferenceNameNumber'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LocationReferenceNameNumberFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['LocationReferenceNameNumber']  ?></p>
        <?php }
        break;

    case csconstants::LocationType:
    if(!empty($artObjData['LocationType'])){
    if(!empty($artObjData['LocationType']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LocationTypeFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['LocationType']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::LocationAccessMemo:
    if(!empty($artObjData['LocationAccessMemo'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::LocationAccessMemoFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['LocationAccessMemo']  ?></div>
        <?php }
        break;

    case csconstants::LocationConditionMemo:
    if(!empty($artObjData['LocationConditionMemo'])){ ?>
          <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::LocationConditionMemoFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['LocationConditionMemo']  ?></div>
        <?php }
        break;

    case csconstants::LocationConditionDate:
    if(!empty($artObjData['LocationConditionDate'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::LocationConditionDateFieldLabel ?>:</span>
                  <?php } ?>
          <?php echo date('m/d/Y',strtotime($artObjData['LocationConditionDate']))  ?></div>
        <?php }
        break;

    case csconstants::LocationSecurityMemo:
    if(!empty($artObjData['LocationSecurityMemo'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::LocationSecurityMemoFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['LocationSecurityMemo']  ?></div>
        <?php }
        break;

    case csconstants::ObjectNameCurrency:
    if(!empty($artObjData['ObjectNameCurrency'])){
    if(!empty($artObjData['ObjectNameCurrency']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectNameCurrencyFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ObjectNameCurrency']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ObjectNameLevel:
    if(!empty($artObjData['ObjectNameLevel'])){
    if(!empty($artObjData['ObjectNameLevel']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectNameLevelFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ObjectNameLevel']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ObjectNameNote:
    if(!empty($artObjData['ObjectNameNote'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectNameNoteFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['ObjectNameNote']  ?></p>
        <?php }
        break;

    case csconstants::ObjectNameSystem:
    if(!empty($artObjData['ObjectNameSystem'])){
    if(!empty($artObjData['ObjectNameSystem']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectNameSystemFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ObjectNameSystem']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ObjectNameType:
    if(!empty($artObjData['ObjectNameType'])){
    if(!empty($artObjData['ObjectNameType']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectNameTypeFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ObjectNameType']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::ObjectNameTitleLanguage:
    if(!empty($artObjData['ObjectNameTitleLanguage'])){
    if(!empty($artObjData['ObjectNameTitleLanguage']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::ObjectNameTitleLanguageFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['ObjectNameTitleLanguage']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::FieldCollectionMethod:
    if(!empty($artObjData['FieldCollectionMethod'])){
    if(!empty($artObjData['FieldCollectionMethod']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FieldCollectionMethodFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['FieldCollectionMethod']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::FieldCollectionPlace:
    if(!empty($artObjData['FieldCollectionPlace'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FieldCollectionPlaceFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['FieldCollectionPlace']  ?></p>
        <?php }
        break;

    case csconstants::FieldCollectionSourceContactName:
    if(!empty($artObjData['FieldCollectionSource']['ContactName'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::FieldCollectionSourceContactNameFieldLabel ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['FieldCollectionSource']['ContactName']  ?></p>
        <?php }
        break;

    case csconstants::FieldCollectionMemo:
    if(!empty($artObjData['FieldCollectionMemo'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::FieldCollectionMemoFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['FieldCollectionMemo']  ?></div>
        <?php }
        break;

    case csconstants::GeologicalComplexName:
    if(!empty($artObjData['GeologicalComplexName'])){
    if(!empty($artObjData['GeologicalComplexName']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::GeologicalComplexNameFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['GeologicalComplexName']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::Habitat:
    if(!empty($artObjData['Habitat'])){
    if(!empty($artObjData['Habitat']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::HabitatFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['Habitat']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::HabitatMemo:
    if(!empty($artObjData['HabitatMemo'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::HabitatMemoFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['HabitatMemo']  ?></div>
        <?php }
        break;

    case csconstants::StratigraphicUnitName:
    if(!empty($artObjData['StratigraphicUnitName'])){
    if(!empty($artObjData['StratigraphicUnitName']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StratigraphicUnitNameFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['StratigraphicUnitName']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::StratigraphicUnitType:
    if(!empty($artObjData['StratigraphicUnitType'])){
    if(!empty($artObjData['StratigraphicUnitType']['Term'])){ ?>
            <p class="my-2">
            <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo csconstants::StratigraphicUnitTypeFieldLabel ?>:</span>
                  <?php } ?>
              <?php echo $artObjData['StratigraphicUnitType']['Term']  ?></p>
          <?php }
        }
        break;

    case csconstants::StratigraphicUnitMemo:
    if(!empty($artObjData['StratigraphicUnitMemo'])){ ?>
        <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo csconstants::StratigraphicUnitMemoFieldLabel ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['StratigraphicUnitMemo']  ?></div>
        <?php }
        break;

        /*udf fields*/



    case csconstants::UserDefined1:
    if(!empty($artObjData['UserDefined1'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined1,csconstants::UserDefined1FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined1']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined2:
    if(!empty($artObjData['UserDefined2'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined2,csconstants::UserDefined2FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined2']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined3:
    if(!empty($artObjData['UserDefined3'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined3,csconstants::UserDefined3FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined3']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined4:
    if(!empty($artObjData['UserDefined4'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined4,csconstants::UserDefined4FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined4']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined5:
    if(!empty($artObjData['UserDefined5'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined5,csconstants::UserDefined5FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined5']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined6:
    if(!empty($artObjData['UserDefined6'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined6,csconstants::UserDefined6FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined6']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined7:
    if(!empty($artObjData['UserDefined7'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined7,csconstants::UserDefined7FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined7']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined8:
    if(!empty($artObjData['UserDefined8'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined8,csconstants::UserDefined8FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined8']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined9:
    if(!empty($artObjData['UserDefined9'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined9,csconstants::UserDefined9FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined9']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined10:
    if(!empty($artObjData['UserDefined10'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined10,csconstants::UserDefined10FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined10']  ?></p>
        <?php }

        break;

    case csconstants::UserDefined11:
    if(!empty($artObjData['UserDefined11'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined11,csconstants::UserDefined11FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined11']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined12:
    if(!empty($artObjData['UserDefined12'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined12,csconstants::UserDefined12FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined12']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined13:
    if(!empty($artObjData['UserDefined13'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined13,csconstants::UserDefined13FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined13']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined14:
    if(!empty($artObjData['UserDefined14'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined14,csconstants::UserDefined14FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined14']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined15:
    if(!empty($artObjData['UserDefined15'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined15,csconstants::UserDefined15FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined15']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined16:
    if(!empty($artObjData['UserDefined16'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined16,csconstants::UserDefined16FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined16']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined17:
    if(!empty($artObjData['UserDefined17'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined17,csconstants::UserDefined17FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined17']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined18:
    if(!empty($artObjData['UserDefined18'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined18,csconstants::UserDefined18FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined18']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined19:
    if(!empty($artObjData['UserDefined19'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined19,csconstants::UserDefined19FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined19']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined20:
    if(!empty($artObjData['UserDefined20'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined20,csconstants::UserDefined20FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined20']  ?></p>
        <?php }

        break;

    case csconstants::UserDefined21:
    if(!empty($artObjData['UserDefined21'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined21,csconstants::UserDefined21FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined21']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined22:
    if(!empty($artObjData['UserDefined22'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined22,csconstants::UserDefined22FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined22']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined23:
    if(!empty($artObjData['UserDefined23'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined23,csconstants::UserDefined23FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined23']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined24:
    if(!empty($artObjData['UserDefined24'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined24,csconstants::UserDefined24FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined24']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined25:
    if(!empty($artObjData['UserDefined25'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined25,csconstants::UserDefined25FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined25']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined26:
    if(!empty($artObjData['UserDefined26'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined26,csconstants::UserDefined26FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined26']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined27:
    if(!empty($artObjData['UserDefined27'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined27,csconstants::UserDefined27FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined27']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined28:
    if(!empty($artObjData['UserDefined28'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined28,csconstants::UserDefined28FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined28']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined29:
    if(!empty($artObjData['UserDefined29'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined29,csconstants::UserDefined29FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined29']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined30:
    if(!empty($artObjData['UserDefined30'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined30,csconstants::UserDefined30FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined30']  ?></p>
        <?php }

        break;

    case csconstants::UserDefined31:
    if(!empty($artObjData['UserDefined31'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined31,csconstants::UserDefined31FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined31']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined32:
    if(!empty($artObjData['UserDefined32'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined32,csconstants::UserDefined32FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined32']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined33:
    if(!empty($artObjData['UserDefined33'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined33,csconstants::UserDefined33FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined33']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined34:
    if(!empty($artObjData['UserDefined34'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined34,csconstants::UserDefined34FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined34']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined35:
    if(!empty($artObjData['UserDefined35'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined35,csconstants::UserDefined35FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined35']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined36:
    if(!empty($artObjData['UserDefined36'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined36,csconstants::UserDefined36FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined36']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined37:
    if(!empty($artObjData['UserDefined37'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined37,csconstants::UserDefined37FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined37']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined38:
    if(!empty($artObjData['UserDefined38'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined38,csconstants::UserDefined38FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined38']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined39:
    if(!empty($artObjData['UserDefined39'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined39,csconstants::UserDefined39FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined39']  ?></p>
        <?php }
        break;

    case csconstants::UserDefined40:
    if(!empty($artObjData['UserDefined40'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefined40,csconstants::UserDefined40FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefined40']  ?></p>
        <?php }

        break;

    case csconstants::UserDefinedDate1:
    if(!empty($artObjData['UserDefinedDate1'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedDate1,csconstants::UserDefinedDate1FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefinedDate1']  ?></p>
        <?php }
        break;

    case csconstants::UserDefinedDate2:
    if(!empty($artObjData['UserDefinedDate2'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedDate2,csconstants::UserDefinedDate2FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefinedDate2']  ?></p>
        <?php }

        break;

    case csconstants::UserDefinedNumber1:
    if(!empty($artObjData['UserDefinedNumber1'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedNumber1,csconstants::UserDefinedNumber1FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefinedNumber1']  ?></p>
        <?php }
        break;

    case csconstants::UserDefinedNumber2:
    if(!empty($artObjData['UserDefinedNumber2'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedNumber2,csconstants::UserDefinedNumber2FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefinedNumber2']  ?></p>
        <?php }

        break;

    case csconstants::UserDefinedCurrency1:
    if(!empty($artObjData['UserDefinedCurrency1'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedCurrency1,csconstants::UserDefinedCurrency1FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefinedCurrency1']  ?></p>
        <?php }
        break;

    case csconstants::UserDefinedCurrency2:
    if(!empty($artObjData['UserDefinedCurrency2'])){ ?>
          <p class="my-2">
          <?php if($showFieldLabelNames==1){ ?>
                  <span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedCurrency2,csconstants::UserDefinedCurrency2FieldLabel) ?>:</span>
                  <?php } ?>
            <?php echo $artObjData['UserDefinedCurrency2']  ?></p>
        <?php }

        break;

    case csconstants::UserDefinedRichText1:
    if(!empty($artObjData['UserDefinedRichText1'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText1,csconstants::UserDefinedRichText1FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText1']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText2:
    if(!empty($artObjData['UserDefinedRichText2'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText2,csconstants::UserDefinedRichText2FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText2']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText3:
    if(!empty($artObjData['UserDefinedRichText3'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText3,csconstants::UserDefinedRichText3FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText3']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText4:
    if(!empty($artObjData['UserDefinedRichText4'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText4,csconstants::UserDefinedRichText4FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText4']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText5:
    if(!empty($artObjData['UserDefinedRichText5'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText5,csconstants::UserDefinedRichText5FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText5']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText6:
    if(!empty($artObjData['UserDefinedRichText6'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText6,csconstants::UserDefinedRichText6FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText6']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText7:
    if(!empty($artObjData['UserDefinedRichText7'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText7,csconstants::UserDefinedRichText7FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText7']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText8:
    if(!empty($artObjData['UserDefinedRichText8'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText8,csconstants::UserDefinedRichText8FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText8']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText9:
    if(!empty($artObjData['UserDefinedRichText9'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText9,csconstants::UserDefinedRichText9FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText9']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText10:
    if(!empty($artObjData['UserDefinedRichText10'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText10,csconstants::UserDefinedRichText10FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText10']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText11:
    if(!empty($artObjData['UserDefinedRichText11'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText11,csconstants::UserDefinedRichText11FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText11']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText12:
    if(!empty($artObjData['UserDefinedRichText12'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText12,csconstants::UserDefinedRichText12FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText12']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText13:
    if(!empty($artObjData['UserDefinedRichText13'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText13,csconstants::UserDefinedRichText13FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText13']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText14:
    if(!empty($artObjData['UserDefinedRichText14'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText14,csconstants::UserDefinedRichText14FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText14']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText15:
    if(!empty($artObjData['UserDefinedRichText15'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText15,csconstants::UserDefinedRichText15FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText15']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText16:
    if(!empty($artObjData['UserDefinedRichText16'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText16,csconstants::UserDefinedRichText16FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText16']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText17:
    if(!empty($artObjData['UserDefinedRichText17'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText17,csconstants::UserDefinedRichText17FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText17']  ?></div>
        <?php }
        break;

    case csconstants::UserDefinedRichText18:
    if(!empty($artObjData['UserDefinedRichText18'])){ ?>
      <?php if($showFieldLabelNames==1){ ?>
            <p class="my-2"><span class="object_detail_fieldlabel"><?php echo getUserDefinedCustomizedText($accountCustomizationData,csconstants::UserDefinedRichText18,csconstants::UserDefinedRichText18FieldLabel) ?>:</span></p>
                  <?php } ?>
          <div class="mb-2 cstheme-show-more-richtext"><?php echo $artObjData['UserDefinedRichText18']  ?></div>
        <?php }
        break;

        default:
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
