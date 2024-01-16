(function ($) {

  function getmoredetails(currentCSThemeObjectId,currentCSThemeOrderBy,currentCSThemeSearch,currentCSThemePageNo){	//alert(currentCSObjectId);
    document.getElementById('dataId').value = currentCSThemeObjectId;
    document.getElementById('sortBy').value = currentCSThemeOrderBy;
    document.getElementById('pageNo').value = currentCSThemePageNo;

    if(currentCSThemeSearch != "" && currentCSThemeSearch != undefined)
    {
    document.getElementById('qSearch').value = currentCSThemeSearch;
    }
    else{
      $("#qSearch").remove();
    }
    document.getElementById("frmdata").submit();
  }
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




})(jQuery);
