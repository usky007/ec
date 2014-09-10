ZXC.Require("ZXC.util");
ZXC.Namespace("YANZI.App");

YANZI.App.Export("StatusBlock");

ZXC.Class({
name: "YANZI.App.StatusBlock",
construct:
	function (elem) {
		this.block = $(elem);
		this.comments;
		this.commentPage = 1;
		this.tcid = this.block.attr("id").substring(4);
		
		ZXC.util.bind(this, $(".comments", this.block), "submit");
		$("form", elem).attr("op", "");
		ZXC.util.bind(this, elem, "click");
		
		this.block.mouseover(function(event){
			$(".notes", elem).show();
		}).mouseout(function(event){
			$(".notes", elem).hide();
		});
		
		this.detect();
	},
methods: {
	detect: function() {
		if (this.block.attr("type")) {
			return;
		}
		
		var outerlink = $('.mag_outerlink', this.block).attr("href");
		if (!outerlink) {
			return;
		}
		else if (js_context.medias[this.tcid]) {
			var media = js_context.medias[this.tcid];
			if (media.type && this["render" + media.type]) {
				this["render" + media.type](media);
			}
			return;
		}
		
		var url_i = 0;
		var _this = this;
		ZXC.util.jQueryAjaxHelper({
			url: js_context.base_url+'maggie/ajax/url_decode',
			data:{'url':outerlink, 'tcid':_this.tcid},
			type: 'get',
			dataType: 'json',
//			beforeSend: function() {
//				_this.after('<img style="margin-left:-16px;" class="loadding_video" src="'+js_context.res_url+'/images/loading.gif" border="0" alt="loadding...">');
//				_this.css('visibility','hidden');
//			},
//	        error: function(request, status, ex) {
//	        	_this.css('visibility','visible');
//				$(".loadding_video").remove();
//				_this.click();
//	        },
			success : function (data) {
				if(!data.success) {
					return;
				}
					
				var media = this.media = data.media;
				if (media.type && _this["render" + media.type]) {
					_this["render" + media.type](media);
				}
				
			}
		});
	},
	rendervideo: function(media) {
		var _this = this;
		var outerlink = $('.mag_outerlink', this.block);
		
		this.block.addClass("video");
		outerlink.attr("toptions", "type = flash, effect = fade, width = 440, height = 356")
			.attr("href", media.video)
			.addClass("top_up");
			
		var trigger = $('.media a', this.block);
		var play = $('.play', trigger);
		if (trigger.length > 0) {
			play.css("opacity", 0.6);
			trigger.mouseover(function() {
				play.css("opacity", 0.8);
			}).mouseout(function() {
				play.css("opacity", 0.6);
			});
		}
			
		if (this.block.hasClass("photo")) {
			var bound = $(".media", this.block);
			var width = bound.width();
			var height = bound.height();;
			$("a", bound).unbind("click").click(function(event) {
				event.preventDefault();
				event.stopImmediatePropagation();
				bound.height(height);
				$(this).unbind("click").attr("id", "video-" + _this.tcid);
//				.flash({
//					swf:media.video,
//					quality:"high",
//					wmode:"transparent",
//					allowfullscreen:"true"
//					width:width,height:height,
//				});
				swfobject.embedSWF(media.video, "video-" + _this.tcid, width, height, "10", null, 
					null,
//					{playMovie:true,auto:1,adss:0},
					{width:width,height:height,quality:"high", wmode:"transparent", allowfullscreen:"true"});
				_this.invalidateLayout();
			});
		}
		else if (this.block.hasClass("mixed")) {
			$(".media a", this.block).attr("toptions", "type = flash, effect = fade, width = 440, height = 356")
				.attr("href", media.video)
				.addClass("top_up");
		}
	},
	show_comments: function(event) {
		var _this = this;
		event.preventDefault();
		
		var comments = $(".comments", this.block);
		if (comments.length == 0)
			return;
		
		var url = $(event.target).attr("href").replace(/^#/, "");	
		if (this.comments) {
			this.comments = false;
			this.invalidateLayout();
			return;
		}
		else if (this.comments !== undefined) {
			this.comments = true;
			this.pageview(event, url);
			this.invalidateLayout();
			return;
		}
		
		// Following code executes only once.
		var img_name = this.block.hasClass("photo") ? "loading_view_dark.gif" : "loading_view.gif";
		var loading = comments.find('.loading').html('<img src="'+js_context.res_url('/images/' + img_name)+'">');
		
		//comments.show();
		this.comments = true;
		this.invalidateLayout();
		
		ZXC.util.jQueryAjaxHelper({
			url : url,
			type: 'GET',
			dataType: 'json',
			error: function() {
				delete _this.comments;
			},
			success: function(data){
				loading.hide();
				if (!data.success) {
					throw new Error(data.message);
				}
				
				html  = '<ul><li style="display:none;"><a href="#" class="light" pg="1">上一页</a></li>';
				html += '<li class="page_line" style="display:none;">|</li>';
				
				data = data.comments;
				var show_next = '';
				if ( data.count < 5 ) {
					show_next = ' style="display:none;"';
				}
				html += '<li'+show_next+'><a href="#" class="light" pg="2">下一页</a></li></ul>';
				
				var list = $("ul", comments);
				if (list.length && list.html() != data['0']) {
					comments.height(comments.height());
					list.prepend(data['0']);
					$('.navi', list).html(html);
					_this.invalidateLayout();
				}
			
				$('.replybtn').click( function() {
					var sw = $(this).attr('sw');
					$(':text[name=status]', comments).val(sw).focus();
				});
				
				$("[pg]", comments).bind('click',function(event){
					_this.pageview(event, url);
				});
				
			},
			complete: function() {
				loading.hide();
			}
		});
	},
	comment: function(event) {
		var _this = this;
		event.preventDefault();
		
		var info = ZXC.util.buildFormData(event.currentTarget);
		ZXC.util.jQueryAjaxHelper({
			url : info.url,
			type: info.method,
			data: info.data,
			dataType: 'json',
			success: function(data){
				if (!data.success) {
					throw new Error(data.message);
				}
				$(".comments > ul", _this.block).prepend(data.comment);
				$(window).trigger('smartresize');
			}
		});
	},
	pageview: function(event,url){
		var _this = this;
		event.preventDefault();

		var comments = $(".comments", this.block);
		var list = $("ul:first", comments);		
		var loading = $(".loading", comments);		
		var page = $(event.target).attr('pg') || this.commentPage;

		loading.show();
		ZXC.util.jQueryAjaxHelper({
			url : url,
			data: {'page':page},
			type: 'GET',
			dataType: 'json',
			success: function(data){
				if (!data.success) {
					throw new Error(data.message);
				}
				
				data = data.comments;
				_this.commentPage = data.page;
				if (!list.length || list.html() == data['0']) {
					return;
				}
				
				list.find('.comment').remove();
				var navi = list.find(".navi");
				
				navi.before(data['0']);
				_this.invalidateLayout();
				
				var nppage = parseInt(data.page) - 1;
				var nnpage = parseInt(data.page) + 1;
				navi.find('ul li').hide();
				if (nppage < 1 && data.count == 5) {
					$("[pg]:eq(1)", navi).attr('pg',2).parent().show();
				}
				else if (nppage >= 1 && data.count < 5) {
					$("[pg]:eq(0)", navi).attr('pg',nppage).parent().show();
				}
				else if (nppage >= 1 && data.count == 5) {
					navi.find('ul li').show();
					$("[pg]:eq(1)", navi).attr('pg',nnpage);
					$("[pg]:eq(0)", navi).attr('pg',nppage);

				}
			},
			complete: function() {
				loading.hide();
			}
		});
	},
	invalidateLayout : function() {		
		var comments = $(".comments", this.block);
		var toggle = this.comments ^ (comments.css("display") == "block");
		var cb = function() {
			comments.height("auto");
			$(window).trigger('invalidateLayout');
			setTimeout(function() {
				comments.css("z-index", "auto");
			}, 1000);
		}
		comments.css("z-index", 100);
		if (!this.comments) {
			comments.hide();
//			comments.slideUp('slow', cb);
		}
		else if (toggle) {
			comments.show();
//			comments.slideDown('slow', cb);
		}
//		else {
//			comments.animate({height:comments.attr("scrollHeight")+"px"}, 'slow', cb);
//		}
		cb();	
	}
}
});