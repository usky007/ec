function showHide(theHiddenMess) {
	var view = document.getElementById(theHiddenMess);
	if  (view.style.display == "none") {
		$(".exp_nav li.morecity").addClass("current");
		$(view).slideDown(800);
	} else {
      	$(view).slideUp(800);
      	$(".exp_nav li.morecity").removeClass("current");
    }
}


$(function(){
	$(".info p").each(function(){
		var txt = $(this).parent().siblings().attr("src");
		$(this).html(txt);
	})
	
	$(".exp_nav li.city").click(function(){
		var index = $(this).index();
		$(this).parent().find(".city.current").removeClass("current");
		$(this).addClass("current");
		$(".top10_content li").eq(index).show().siblings().hide();
	})
})
