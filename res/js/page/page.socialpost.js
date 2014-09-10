Page.onPageLoad.add(function(){
	var ret = js_context.redirect || js_context.base_url;	
	
	jQuery.ajax({
		url:js_context.base_url + "ajax/social/post_process/" + js_context.provider,
		data:null,
		type:"POST",
		dataType:"json",
		complete: function() {
			window.setTimeout(function() {
				window.location = ret;
			}, 3000)
		}
	});
})