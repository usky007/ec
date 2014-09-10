Page.onPageLoad.add(function(){
	// page level js
	var video = $("#promo-video");
	var hover = false;
	video.scrollable({
		circular:true,
		speed:500,
		items:"promo-photos"
	}).mouseenter(function() { hover = true;}).mouseleave(function() { hover = false;});
	window.setInterval(function() {
		if (hover) return;
		video.scrollable().next();
	}, 3000);

	$(".view-all-cities").click(function(e){
		e.preventDefault();
		ZXC.util.locate($(".all-cities"), $(".partial-site-featured"), "top-left", "right-down");
		$(".all-cities").show();
	});

	$(".close").click(function(e){
		e.preventDefault();
		$(this).parent().hide();
	});
	
 
	var search = $("#search");
	var frm = search.parents("form");
	frm.submit(function(event) {
		var citycode = search.attr("citycode");
		var val = (citycode && citycode.length > 0) ? citycode : encodeURIComponent(search.val());
		if (val.length > 0) {
			window.open(frm.attr("action") + "/" + val, "_self");
		}
		return false;
	});
	
	search.focus(function() {
		$(this).prev().hide();
	}).blur(function() {
		if ($(this).val().length == 0) {
			$(this).prev().show();
		}
	}).suggestion({
		source: js_context.base_url + "ajax/city/all",
		scrollLimit: 8,
		onMatch:ZXC.callbacks.suggestion.onCityMatch,
		onShow:ZXC.callbacks.suggestion.onCityShow,
		multiple: false,
		suggestOnFail: false,
		suggestOnLoad: true,
		guess: false,
		validate:true,
		keyField:"name",
		autoWidth: "fit",
		autoFill: false,
		panelClass: "tag_panel suggestions",
		headerFactory: "<h3>可创建地图的目的地</h3>",
		onSort: ZXC.callbacks.suggestion.onCitySort,
		onValidateComplete: function(sender, validEntry) {
			$(sender.target).attr("citycode", validEntry ? validEntry.citycode : "");
		},
		onSelect: function(sender, entry) {
			window.setTimeout(ZXC.Callback(frm, "submit"), 10);
			return entry.name;
		}
	}).prev().click(function(event) {
		search.focus();
		return false;
	});
	
	$(".partial-site-featured .switch-city-tab").click(function(event) {
		event.preventDefault();
		
		$(".featured-tabs li.active").removeClass("active");
		$(this).parent().addClass("active");
		var cityBlock = $(".featured-city").hide().filter("#" + $(this).attr("name")).show();
		var cityImage = cityBlock.find(".feature-city-image img");
		cityImage.getImage(cityImage.attr("pic"));
	})
});