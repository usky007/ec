Page.onPageLoad.add(function(){
	// page level js
	var search = $("#search");
	search.suggestion({
		source: js_context.base_url + "ajax/city/all",
		scrollLimit: 10,
		onMatch:ZXC.callbacks.suggestion.onCityMatch,
		onShow:ZXC.callbacks.suggestion.onCityShow,
		multiple: false,
		suggestOnFail: false,
		suggestOnLoad: true,
		guess: false,
		validate:true,
		keyField:"name",
		autoWidth: true,
		autoFill: false,
		panelClass: "tag_panel suggestions",
		//onSort: ZXC.callbacks.suggestion.onCitySort,
		onValidateComplete: function(sender, validEntry) {
			$(sender.target).attr("citycode", validEntry ? validEntry.citycode : "");
		},
		onSelect: function(sender, entry) {
			return entry.citycode;
		}
	});
});