Page.onPageLoad.add(function(){
	// page level js
	/*var search = $("#search");
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
		onValidateComplete: function(sender, validEntry) {
			$(sender.target).attr("citycode", validEntry ? validEntry.citycode : "");
		},
		onSelect: function(sender, entry) {
			return entry.citycode;
		}
	});*/
    $('form[name="form_filter"]').append('<input type="hidden" name="id" value="'+js_context.camp_id+'" />');

    $('.status_open_op').each(function(){
        $(this).click(function(e){
            if(confirm('是否确认要开启这个活动？')){
                return true;
            }else{
                return false;
            }
        });
    });
    $('.status_close_op').each(function(){
        $(this).click(function(e){
            if(confirm('是否确认要关闭这个活动？')){
                return true;
            }else{
                return false;
            }
        });
    });
    $('.status_init_op').each(function(){
        $(this).click(function(e){
            if(confirm('是否确认要初始化这个活动？')){
                return true;
            }else{
                return false;
            }
        });
    });

});