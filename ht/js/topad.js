// JScript 文件
function TopAd()
{
    var strTopAd="";
	
	//定义小图片内容
    var topSmallBanner="<div><img src=\"images/banner01a.gif\" /></div>";
	
	//判断在那些页面上显示大图变小图效果，非这些地址只显示小图（或FLASH）
    if (location == "http://www.uutuu.com" || location == "http://www.uutuu.com" || location == "http://www.uutuu.com" || true)
    {
		//定义大图内容
        strTopAd="<div id=adimage style=\"width:980px\">"+
                    "<div id=adBig><a href=\"http://www.lanrentuku.com/\" " + 
                    "target=_blank><img title=旅行者万花筒uutuu "+
                    "src=\"images/banner01a.jpg\" " +
                    "border=0></A></div>"+
                    "<div id=adSmall style=\"display: none\">";
        //strTopAd+=  topFlash;     
		strTopAd+=  topSmallBanner;  
        strTopAd+=  "</div></div>";
    }
    else
    {
        //strTopAd+=topFlash;
		strTopAd+=  topSmallBanner;  
    }
    strTopAd+="<div style=\"height:7px; clear:both;overflow:hidden\"></div>";
    return strTopAd;
}
document.write(TopAd());
$(function(){
	//过两秒显示 showImage(); 内容
    setTimeout("showImage();",3000);
    //alert(location);
});
function showImage()
{
    $("#adBig").slideUp(1000,function(){$("#adSmall").slideDown(1000);});
}

