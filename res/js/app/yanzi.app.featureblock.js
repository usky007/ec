ZXC.Require("ZXC.util");
ZXC.Namespace("YANZI.App");

YANZI.App.Export("FeatureBlock");

ZXC.Class({
name: "YANZI.App.FeatureBlock",
construct:
	function (elem) {
		this.block = $(elem);
		this.overlay = $(".overlay", this.block);
		this.photo = $(".media img", this.block);
		this.suid = this.block.attr("id").substring(2);
		this.staleTTL = 60;
		
		ZXC.util.bind(this, elem, "click");
		
		var overlay = $(".overlay", this.block);
		var name = this.block.find(".name");
		var btnDelete = this.block.find(".delete");
		overlay.mouseover(function(event){
			$(".delete", elem).show();
			overlay.addClass("hover");
		}).mouseout(function(event){
			$(".delete", elem).hide();
			overlay.removeClass("hover");
		});
		name.mouseover(function(event){
			overlay.trigger("mouseover");
		}).mouseout(function(event){
			if (event.relatedTarget != overlay[0] && event.relatedTarget != btnDelete[0]) {
				overlay.trigger("mouseout");
			}
		});
		btnDelete.mouseout(function(event){
			if (event.relatedTarget != name[0]) {
				overlay.trigger("mouseout");
			}
		});
		
		this.refresh();
	},
methods: {
	refresh: function() {
		if (parseInt(this.block.attr("ttl")) < this.staleTTL) {
			return;
		}
		
		var _this = this;
		ZXC.util.jQueryAjaxHelper({
			url: js_context.base_url+'maggie/ajax/favo_refresh/' + _this.suid,
			type: 'get',
			dataType: 'json',
			success : function (data) {
				if(!data.success) {
					return;
				}

				var favorite = data.favorite;
				if (favorite.photo && favorite.photo != _this.photo.attr("src")) {
					ZXC.util.loadImage(_this.photo[0], favorite.photo, js_context.res_url("images/timeline/loading_view_dark.gif"));
				}	
			}
		});
	},
	remove: function(event) {
		var _this = this;
		
		event.preventDefault();
		var target = $(event.currentTarget);
		
		if (target.attr("processing") == "1") {
			return;
		}
		
		target.attr("processing", "1");
		var identity = target.attr("identity");
		var url = target.attr("href").replace(/^#/, "");
		
		ZXC.util.jQueryAjaxHelper({
			url: url,
			data: {identity:identity},
			type: 'POST',
			dataType: 'json',
			error: function (data) {
				target.attr("processing", "");
			},
			success: function (data) {
				if (!data.success)
					return;
				
				_this.block.remove();
				$(window).trigger('invalidateLayout');
			}
		});
	}
}
});