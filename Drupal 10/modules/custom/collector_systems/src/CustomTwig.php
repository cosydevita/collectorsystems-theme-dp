<?php

namespace Drupal\collector_systems;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Drupal\collector_systems\Csconstants;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;


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
    $object_detail_link = "/artobject-detail?dataId=" . (int) $value['ObjectId'] . "&sortBy=" . rawurlencode($dataOrderBy) . "&pageNo=" . (int) $datapageNo;
    $showImagesOnListPages =  \Drupal::config('collector_systems.settings')->get('show_images_objects');
    ?>

    <div class="card col-lg-4 col-md-6 col-sm-6 col-12 mb-3 cs-object-list wow fadeInDown" data-wow-delay="<?php echo Html::escape($delaytm); ?>">
      <?php if($showImagesOnListPages){ ?>
                    <div class="card-body d-flex flex-column">
                        <div class="image-wrapper">
                            <a href="<?php echo Html::escape($object_detail_link); ?>" class="image-wrapper-link">
                                <?php
                                $main_image_attachment_description = isset($value['main_image_attachment_description']) && $value['main_image_attachment_description'] !== ''
                                ? Html::escape($value['main_image_attachment_description'])
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
                                    <img class="img-fluid" src="<?php echo Html::escape($default_image_url); ?>" alt="<?php echo $main_image_attachment_description ?>"/>
                                    <?php } else {
                                    if (empty($server_path)) {
                                    ?>
                                    <img class="img-fluid" src="<?php echo Html::escape($object_img); ?>" alt="<?php echo $main_image_attachment_description ?>"/>
                                    <?php
                                    } else {
                                    ?>
                                    <img class="img-fluid" src="<?php echo Html::escape($image_url); ?>" alt="<?php echo $main_image_attachment_description ?>"/>
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
    $object_detail_link = "/artobject-detail?dataId=" . (int) $value['ObjectId'] . "&sortBy=" . rawurlencode($dataOrderBy) . "&pageNo=" . (int) $datapageNo;

    foreach($customized_fields_array as $object_field)
    {
      $val = Csfieldresolver::getValue($object_field, $value);
      if ($val === '') {
        continue;
      }

      $link = Csfieldresolver::getLinkType($object_field);
      if ($link && $link['type'] === 'self') {
        $val = '<a href="' . Html::escape($object_detail_link) . '">' . $val . '</a>';
      }
      elseif ($link && $link['type'] === 'collection') {
        $val = '<a href="javascript:;" onclick="return getmoredetailsForCollection(\'' . Html::escape($site_url) . '\', ' . (int) $value[$link['id_column']] . ')">' . $val . '</a>';
      }
      elseif ($link && in_array($link['type'], ['artist', 'artist_maker'], TRUE)) {
        $val = '<a href="' . Html::escape($site_url) . '/artist-detail?dataId=' . (int) $value[$link['id_column']] . '">' . $val . '</a>';
      }
      ?>
      <h6 class="font-normal cs-theme-card-title"><small class="flex-fill"><?php echo $val ?></small></h6>
      <?php
    }
  }


  public function customPaginationForTopLevelTabs($requested_page,$total_records,$sortBy,$qSearch)
  {
    ob_start();
    $sortBy = $sortBy ? rawurlencode($sortBy) : '';
    $qSearchEncoded = $qSearch ? rawurlencode($qSearch) : '';

    $request = \Drupal::request();
    $baseUrl = $request->getBasePath();
    $pagingUrl = "";

    // Advanced Search takes priority over the plain search box (matches the
    // mutual-exclusivity rule everywhere else): if this request submitted
    // the advanced panel, carry advSearch + every adv_* param into the
    // pagination links instead of qSearch, or paging would silently drop
    // the active filter.
    if (!empty($_REQUEST['advSearch'])) {
      $advParts = ['advSearch=1'];
      foreach ($_REQUEST as $key => $value) {
        if (strpos($key, 'adv_') === 0 && is_string($value) && $value !== '') {
          $advParts[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
      }
      if (!empty($sortBy)) {
        $advParts[] = 'sortBy=' . $sortBy;
      }
      $pagingUrl = $baseUrl . '?' . implode('&', $advParts);
    }
    elseif(!empty($sortBy) && empty($qSearchEncoded))
    {

      $pagingUrl = $baseUrl ."?sortBy=".$sortBy;
    }
    elseif(empty($sortBy) && !empty($qSearchEncoded))
    {
      $pagingUrl = $baseUrl ."?qSearch=".$qSearchEncoded;
    }
    elseif(!empty($sortBy) && !empty($qSearchEncoded))
    {
      $pagingUrl = $baseUrl ."?sortBy=".$sortBy."&qSearch=".$qSearchEncoded;
    }
    else
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
        $pagingUrlSafe = Html::escape($pagingUrl);
        echo "<div class='cs-custom-pagination d-flex'>";

      if($requested_page != 1) echo "<a href='".$pagingUrlSafe."&pageNo=".((int) $requested_page - 1)."'><i class='fas fa-chevron-left'></i></a>";

        for ($i=1; $i <= $pages; $i++)
        {
            if (1 != $pages &&( !($i >= $requested_page+$showitems+1 || $i <= $requested_page-$showitems-1) || $pages <= $showitems ))
            {
                echo ($requested_page == $i)? "<span class='current'>".$i."</span>":"<a href='".$pagingUrlSafe."&pageNo=".((int) $i)."' class='inactive' >".$i."</a>";
            }
        }

        //if ($requested_page < $pages && $showitems < $pages) echo "<a href='".\Drupal::request()->getHost()."/".$current_page."?pageNo=".($requested_page + 1)."'><i class='bi bi-chevron-right'></i></a>";
        //if ($requested_page < $pages-1 &&  $requested_page+$range-1 < $pages && $showitems < $pages) echo "<a href='".\Drupal::request()->getHost()."/".$current_page."?pageNo=".($pages)."'>&raquo;</a>";

        if ($requested_page != $pages) echo "<a href='".$pagingUrlSafe."&pageNo=".((int) $requested_page + 1)."'><i class='fas fa-chevron-right'></i></a>";

        echo "</div>\n";
    }
    return ob_get_clean();
    }

    public function GetCustomizedObjectDetailsForTheme($object_field, $accountCustomizationData, $artObjData)
    {
      $site_url = \Drupal::request()->getSchemeAndHttpHost();
      $showFieldLabelNames = \Drupal::config('collector_systems.settings')->get('show_field_labels');

      if (is_array($artObjData) || is_object($artObjData)) {
        $artObjData = is_object($artObjData) ? get_object_vars($artObjData) : $artObjData;
      }

      $val = Csfieldresolver::getValue($object_field, $artObjData);
      if ($val === '') {
        return;
      }

      $link = Csfieldresolver::getLinkType($object_field);
      if ($link && $link['type'] === 'collection') {
        $val = '<a href="javascript:;" onclick="return getmoredetailsForCollection(\'' . Html::escape($site_url) . '\',\'' . (int) $artObjData[$link['id_column']] . '\')">' . $val . '</a>';
      }
      elseif ($link && in_array($link['type'], ['artist', 'artist_maker'], TRUE)) {
        $val = '<a href="' . Html::escape($site_url) . '/artist-detail?dataId=' . (int) $artObjData[$link['id_column']] . '">' . $val . '</a>';
      }
      // 'self' link type intentionally not applied here -- the object detail
      // page has no "self" to link to (already true before this refactor).

      $category = Csfieldresolver::getCategory($object_field);
      $label = $showFieldLabelNames == 1 ? Csfieldresolver::getLabel($object_field) : '';

      if (in_array($category, ['richtext', 'udf_richtext'], TRUE)) {
        ?>
        <p class="my-2">
          <?php if ($label !== '') { ?>
          <span class="object_detail_fieldlabel"><?php echo Html::escape($label) ?>:</span>
          <?php } ?>
            <span class="mb-2 cstheme-show-more-richtext"><?php echo $val ?></span>
        </p>
        <?php
      }
      else {
        ?>
        <p class="my-2">
          <?php if ($label !== '') { ?>
          <span class="object_detail_fieldlabel"><?php echo Html::escape($label) ?>:</span>
          <?php } ?>
          <?php echo $val ?>
        </p>
        <?php
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
  public static function implodeChildArrayProperty($additionalArrayObject,$additionalArray,$additionalPropertyId,$additionalProperty) {
    $site_url = \Drupal::request()->getSchemeAndHttpHost();
    $commaSeperatedItem = "";  
    
    if(!is_array($additionalArrayObject) || count($additionalArrayObject) == 0){
      return $commaSeperatedItem;
    }
  
    foreach ($additionalArrayObject as $additionalItem) {
      $artistId = isset($additionalItem[$additionalArray][$additionalPropertyId]) ? $additionalItem[$additionalArray][$additionalPropertyId] : '';
      if(!empty($artistId)){
        $commaSeperatedItem != "" && $commaSeperatedItem .= ", ";
        $commaSeperatedItem .= '<a href="' . Html::escape($site_url) . '/artist-detail?dataId=' . (int) $artistId . '">' . Html::escape($additionalItem[$additionalArray][$additionalProperty]) . '</a>';
      }

    }
    return $commaSeperatedItem;     
  }

}
