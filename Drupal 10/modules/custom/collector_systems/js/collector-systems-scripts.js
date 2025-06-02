(function ($, Drupal) {
  Drupal.behaviors.myCustomBehavior = {
    attached: false,

    attach: function (context, settings) {
      $('a').click(function(event){
        event.preventDefault()
        alert("test")
      })

    }
  }
})



function getmoredetailsForArtist(artistactionurl,str){
  $durl = artistactionurl+"/artist-detail?dataId="+str;
  window.location.href= $durl;
}
function getmoredetailsForCollection(collectionactionurl,str){
  $durl = collectionactionurl+"/collection-detail?dataId="+str;
  window.location.href= $durl;
}
function getmoredetailsForExhibition(exhibitionactionurl,str){
  $durl = exhibitionactionurl+"/exhibition-detail?dataId="+str;
  window.location.href= $durl;
}
function getmoredetailsForGroup(groupactionurl,str){
  $durl = groupactionurl+"/group-detail?dataId="+str;
  window.location.href= $durl;
}



function displayWPThemeDefaultImage(control, imageFor, imageSize){
  $defaultImgName = "";
  switch(imageFor)
  {
    case "Artists":
      $siteUrl = get_bloginfo("template_directory");
      defaultImgName=$siteUrl + "/img/artist.png";
      break;
    case "Objects":
      switch (imageSize) {
        case "FullSize":
        case "DetailXLarge":
          defaultImgName = "https://cdn.collectorsystems.com/images/noimage500.png";
          break;
        case "SlideShow":
        case "DetailLarge":
          defaultImgName = "https://cdn.collectorsystems.com/images/noimage300.png";
          break;
        case "iPad":
          defaultImgName = "https://cdn.collectorsystems.com/images/noimage200.png";
          break;
        case "iPhone":
        case "ThumbSize":
          defaultImgName = "https://cdn.collectorsystems.com/images/noimage100.png";
          break;
        case "Detail":
        case "MidSize":
          defaultImgName = "https://cdn.collectorsystems.com/images/noimage150.png";
          break;
      }
      break;
    case "Collections":
    case "Exhibitions":
      defaultImgName = "https://cdn.collectorsystems.com/images/noimage200.png";
      break;
  }
  return defaultImgName;
}


function pagingForGroupLevelObjects(ajaxpage,listPageSize,groupLevelPageNoValue)
{
  //debugger;
var searchValue=document.getElementById("searchindata").value;
var groupLevelOrderBy=document.getElementById("dlGroupLevelObjectsOrderBy").value;
var pagenamevalue=ajaxpage;
var pagetopvalue=document.getElementById("groupLevelTopCount").value;
var pageskipvalue=document.getElementById("groupLevelSkipCount").value;
var pageTotalCount=document.getElementById("groupLevelTotalCount").value;
var groupTypeId=document.getElementById("groupTypeId").value;

var collectionLeftExtent=jQuery("#collectionLeftExtent").length > 0 ? jQuery("#collectionLeftExtent").val() : 0;
var collectionRightExtent=jQuery("#collectionRightExtent").length > 0 ? jQuery("#collectionRightExtent").val() : 0;

if(groupLevelPageNoValue==1)
{
  pageskipvalue=0;
}
else
{
  pageskipvalue=(Number(groupLevelPageNoValue)-1) *Number(pagetopvalue);
}

const origin = window.location.origin;

jQuery.ajax({
    url: origin+'/v1/group-level-objects-searching-page',
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'groupLevelObjects_searching_page',
      pagename:pagenamevalue,
      groupTypeId:groupTypeId,
      collectionLeftExtent:collectionLeftExtent,
      collectionRightExtent:collectionRightExtent,
      groupLevelPageNo:groupLevelPageNoValue,
      groupLevelTopCount:pagetopvalue,
      groupLevelSkipCount:pageskipvalue,
      groupLevelOrderBy:groupLevelOrderBy,
      groupLevelSearch:searchValue},
    success: function(data) {
        document.getElementById("groupLevelOrderBy").value =  document.getElementById("dlGroupLevelObjectsOrderBy").value;
        jQuery('#groupLevelObjectsData').html(data.groupLevelSearchHtml);
        jQuery('#groupLevelPageNo').val(groupLevelPageNoValue)
        htmlForGroupLevelObjectsPaging(listPageSize,groupTypeId,pagenamevalue,groupLevelPageNoValue,pageTotalCount,groupLevelOrderBy,searchValue);
  }
});



return false;
}


function applySorting(){
  var baseUrl = window.location.origin + window.location.pathname ;


  var pagingUrl = "";

  var searchValue=document.getElementById("searchindata").value;
  var sortByValue=document.getElementById("seldataorderby").value;
  var pagerec=document.getElementById("totrec").value;

  if(sortByValue != "")
  {
    sortByValue = encodeURIComponent(sortByValue);
  }
  if(searchValue != "")
  {
    searchValue = encodeURIComponent(searchValue);
  }

  if(sortByValue != "" && searchValue == "")
  {
    pagingUrl = baseUrl+"?sortBy="+sortByValue;
  }
  if(sortByValue == "" && searchValue != "")
  {
    pagingUrl = baseUrl+"?qSearch="+searchValue;
  }
  if(sortByValue != "" && searchValue != "")
  {
    pagingUrl = baseUrl+"?sortBy="+sortByValue+"&qSearch="+searchValue;
  }
  if(sortByValue == "" && searchValue == "")
  {
    pagingUrl = baseUrl;
  }
  window.location.href = pagingUrl;
}


function chksearchfordata(ajaxpage){
if(ajaxpage=="artist-detail" || ajaxpage=="exhibition-detail" || ajaxpage=="group-detail"|| ajaxpage=="collection-detail")
{
  event.preventDefault();
  searchingForGroupLevelObjects(ajaxpage);
}
else
{
  if(ajaxpage=="artobject-detail")
  {
    //redirect it to objects list page
    ajaxpage = "objects";
  }
  applySearchToTopLevelTabs(ajaxpage);
  //searchingForTopLevelTabs(ajaxpage);
}

function applySearchToTopLevelTabs(pageName){

  var baseUrl =  window.location.origin + window.location.pathname;
  var sortByValue=document.getElementById("seldataorderby").value;
  var qSearchValue=document.getElementById("searchindata").value;

  window.location.href = baseUrl+"?sortBy="+encodeURIComponent(sortByValue)+"&qSearch="+qSearchValue;
}


return false;
}


function searchingForGroupLevelObjects(ajaxpage){
var objectListSearchValue=document.getElementById("searchindata").value;
var objectListOrderByValue=document.getElementById("dlGroupLevelObjectsOrderBy").value;
var objectListRecTopValue=document.getElementById("groupLevelTopCount").value;
var objectListRecSkipValue=document.getElementById("groupLevelSkipCount").value;
var objectListTotalRecValue=document.getElementById("groupLevelTotalCount").value;
var groupTypeId=document.getElementById("groupTypeId").value;

var collectionLeftExtent=jQuery("#collectionLeftExtent").length > 0 ? jQuery("#collectionLeftExtent").val() : 0;
var collectionRightExtent=jQuery("#collectionRightExtent").length > 0 ? jQuery("#collectionRightExtent").val() : 0;

var listPageSize = objectListRecTopValue;
objectListRecSkipValue = 0;

var groupLevelPageNoValue =1;
const origin = window.location.origin;

jQuery.ajax({
      url: origin+'/v1/group-level-objects-searching-page',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'groupLevelObjects_searching_page',
        pagename:ajaxpage,
        groupTypeId:groupTypeId,
        collectionLeftExtent:collectionLeftExtent,
        collectionRightExtent:collectionRightExtent,
        searchWord:objectListSearchValue,
        groupLevelTopCount:objectListRecTopValue,
        groupLevelSkipCount:objectListRecSkipValue,
        groupLevelOrderBy:objectListOrderByValue
      },
      success: function(data) {
        console.log(data)
       //debugger;
       jQuery('#groupLevelObjectsData').html(data.groupLevelSearchHtml);
       document.getElementById("groupLevelTotalCount").value = document.getElementById("hdnTotalGroupLevelObjectCount").value;
       objectListTotalRecValue = document.getElementById("groupLevelTotalCount").value;
       htmlForGroupLevelObjectsPaging(listPageSize,groupTypeId,ajaxpage,groupLevelPageNoValue,objectListTotalRecValue,objectListOrderByValue,objectListSearchValue);
       if(Number(objectListTotalRecValue) > Number(objectListRecTopValue))
       {
        jQuery(".cs-grouplevel-total-count").html("("+objectListTotalRecValue+" results)");
       }
       else
       {
        jQuery(".cs-grouplevel-total-count").html("");
       }
     }
  });
return false;
}



function sortingForGroupLevelObjects(ajaxpage,listPageSize){

var searchValue=document.getElementById("searchindata").value;
var selordrtype=document.getElementById("dlGroupLevelObjectsOrderBy").value;
var pagenamevalue=ajaxpage;
var pagetopvalue=document.getElementById("groupLevelTopCount").value;
var pageskipvalue=document.getElementById("groupLevelSkipCount").value;
var pageTotalCount=document.getElementById("groupLevelTotalCount").value;
var groupTypeId=document.getElementById("groupTypeId").value;
var objectListRecTopValue=document.getElementById("groupLevelTopCount").value;

var collectionLeftExtent=jQuery("#collectionLeftExtent").length > 0 ? jQuery("#collectionLeftExtent").val() : 0;
var collectionRightExtent=jQuery("#collectionRightExtent").length > 0 ? jQuery("#collectionRightExtent").val() : 0;

var groupLevelPageNoValue =1;

const origin = window.location.origin;

jQuery.ajax({
    url: origin+'/v1/group-level-objects-searching-page',
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'groupLevelObjects_searching_page',
      pagename:pagenamevalue,
      groupTypeId:groupTypeId,
      collectionLeftExtent:collectionLeftExtent,
      collectionRightExtent:collectionRightExtent,
      groupLevelTopCount:pagetopvalue,
      groupLevelSkipCount:pageskipvalue,
      groupLevelOrderBy:selordrtype,
      searchWord:searchValue
    },
    success: function(data) {
      jQuery('#groupLevelObjectsData').html(data.groupLevelSearchHtml);
      //document.getElementById("groupLevelTotalCount").value = document.getElementById("hdnTotalGroupLevelObjectCount").value;
      var objectListTotalRecValue = document.getElementById("groupLevelTotalCount").value;


        document.getElementById("groupLevelOrderBy").value =  document.getElementById("dlGroupLevelObjectsOrderBy").value;
        jQuery('#groupLevelObjectsData').html(data.groupLevelSearchHtml);

        htmlForGroupLevelObjectsPaging(listPageSize,groupTypeId,pagenamevalue,groupLevelPageNoValue,objectListTotalRecValue,groupLevelOrderBy,searchValue);

        if(Number(objectListTotalRecValue) > Number(objectListRecTopValue))
        {
         jQuery(".cs-grouplevel-total-count").html("("+objectListTotalRecValue+" results)");
        }
        else
        {
         jQuery(".cs-grouplevel-total-count").html("");
        }

  }
});

return false;
}

function clearSearchData(pageName){
  document.getElementById("searchindata").value = "";

  if(pageName=="artist-detail" || pageName=="exhibition-detail" || pageName=="group-detail" || pageName=="collection-detail")
  {
    clearSearchForGroupLevelObjects(pageName);
  }
  else
  {
    clearSearchForTopLevelTabs(pageName);
  }
  return false;
}


function clearSearchForTopLevelTabs(pageName)
{
var baseUrl = window.location.origin + "/" + pageName;
var pagingUrl = "";

document.getElementById("searchindata").value = "";
var sortByValue=document.getElementById("seldataorderby").value;
if(sortByValue != "")
{
  sortByValue = encodeURIComponent(sortByValue);
}
if(pageName == "artobject-detail")
{
  var dataId = document.getElementById("hdnObjectId").value;
  var pageNo = document.getElementById("pageNo").value;
  pagingUrl = baseUrl+"/"+pageName+"?dataId="+dataId;
  if(sortByValue != "")
  {
    pagingUrl = pagingUrl+"&sortBy="+sortByValue;
  }
  if(pageNo != "")
  {
    pagingUrl = pagingUrl+"&pageNo="+pageNo;
  }
}
else
{
  if(sortByValue != "")
  {
    pagingUrl = baseUrl+"?sortBy="+sortByValue;
  }
}

window.location.href = pagingUrl;

}

function clearSearchForGroupLevelObjects(ajaxpage)
{
window.location.reload();
}


function htmlForGroupLevelObjectsPaging(listPageSize,groupTypeId,ajaxpage,requested_page,total_records,sortBy,qSearch)
{
  var pagingHtmlContent = "";
  var showItemsPerPage = listPageSize;
  if(requested_page=="") requested_page = 1;

  var calculated_pages = Math.ceil(total_records / listPageSize);
  if(!calculated_pages)
  {
    calculated_pages = 1;
  }

  if(1 != calculated_pages)
  {
    if(requested_page != 1) pagingHtmlContent+= "<a href='javascript:;' onclick=pagingForGroupLevelObjects(\'"+ajaxpage+"\',"+listPageSize+","+(requested_page - 1)+")><i class=\'fas fa-chevron-left\'></i></a>";

      for (i=1; i <= calculated_pages; i++)
      {
          if (1 != calculated_pages &&( !(i >= requested_page+showItemsPerPage+1 || i <= requested_page-showItemsPerPage-1) || calculated_pages <= showItemsPerPage ))
          {
            pagingHtmlContent+= (requested_page == i)? "<span class=\'current\'>"+i+"</span>":"<a href='javascript:;' onclick=pagingForGroupLevelObjects(\'"+ajaxpage+"\',"+listPageSize+","+i+") class=\'inactive\' >"+i+"</a>";
          }
      }

      if (requested_page != calculated_pages) pagingHtmlContent+= "<a href='javascript:;'onclick=pagingForGroupLevelObjects(\'"+ajaxpage+"\',"+listPageSize+","+(requested_page+1)+")><i class=\'fas fa-chevron-right\'></i></a>";
  }
  jQuery("#groupLevelPagingData").html(pagingHtmlContent);
}


jQuery(document).ready(function($){
  var minimized_elements = $('div.cstheme-show-more-richtext');
  var maxchars = 500;

  minimized_elements.each(function () {
      var $this = $(this);
      if ($this.text().length >= 500) {
          $this.hide();
          var children = $this.contents();
          var $shortDesc = $('<div />');
          var len = children.length;
          var count = 0;
          var i = 0;
          while (i < len) {
              var $elem = $(children[i]).clone();
              var text = $elem.text();
              var l = text.length;
              if (count + l > maxchars) {
                  var newText = text.slice(0, maxchars - count);
                  if ($elem.get(0).nodeType === 3) {
                      $elem = document.createTextNode(newText);
                  } else {
                      $elem.text(newText);
                  }
                  $shortDesc.append($elem);
                  break;
              }
              count += l;
              $shortDesc.append($elem);
              i++;
          }

          $shortDesc.append($('<span>... </span>'));
          $this.after($shortDesc);
          $shortDesc.append($('<a style="font-size:0.8rem;text-decoration: underline;" href="#" class="more">Show More</a>').on('click', function (ev) {
              ev.preventDefault();
              $this.show();
              $shortDesc.hide();
          }));
          $this.append($('<a style="font-size:0.8rem;text-decoration: underline;" href="#" class="less">Show Less</a>').on('click', function (ev) {
              ev.preventDefault();
              $shortDesc.show();
              $this.hide();
          }));
      }
      else {

      }
  });
});
