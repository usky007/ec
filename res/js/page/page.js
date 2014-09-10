// @author			mitchell
// @description		Common Page Level JS
// @lastmodified		$2010-7 - 14$

$.ajaxSetup({
	async:true,
	timeout:60000,

	error:function(XMLHttpRequest,textStatus,errorThrown){
		if(textStatus=="timeout") {
			ZXC.util.log("error", "连接超时或请求出错，请重试");
		} else if(textStatus=="error") {
			ZXC.util.log("error", errorThrown);
		} else {
			ZXC.util.log("error", errorThrown);
		}
	}
});

// js_context : A object that defined by the server.
// res_url : Url prefix for resources (image,js,css etc.)
// jsrevision : Resources are rarely changed, by add long expiration header, they are
//   cacheable, change jsrevision if you want to update resources by force.
// Following code change res_url from a simple string to a function, which changes
// given resource url in a jsrevision sensible style and also preserves the ability
// of being used as a string.
var js_context;
if (js_context && js_context.res_url) {
	js_context.res_url = function() {
		var res_url = js_context.res_url;
		var func = function(path) {
			var url = res_url;
			path = !path ? "" : path.replace(/(.+?)\.(js|css|jpg|gif|png|cur)$/i,
									"$1"+(js_context.jsrevision || "")+".$2");
			return url + path;
		};
		func.toString = function() {
			return res_url;
		};
		func.get = func;
		return func;
	}();
}

//去除IE7 IE6的链接样式
//alert($.browser.version);
if (($.browser.msie && $.browser.version < 8.0)||$.browser.mozilla){
	$("a,input[type=button],input[type=submit]").click(function(){
		$(this).blur();
	})
}
if ($.browser.msie && $.browser.version < 8.1){
	String.prototype.trim = function(){
		var str = this;
		str =str.replace(/^\s\s*/,''),
		ws=/\s/,
		i=str.length;
		while(ws.test(str.charAt(--i)));
		return str.slice(0,i+1);
	}
}

// common callbacks
ZXC.callbacks = {
	suggestion: {
		onCityMatch : function () {
			var dict = {};
			var fields = ["citycode"];
			var buildTree = function (entry) {
				var tree = {};
				for (var i = 0; i < fields.length; i++) {
					if (!entry[fields[i]])
						continue;
					var alts = entry[fields[i]].split(",");
					for (var j = 0; j < alts.length; j++) {
						var alt = alts[j].toLowerCase();
						var b = tree;
						for (var k = 0; k < alt.length; k++) {
							if (!b[alt.charAt(k)]) {
								b[alt.charAt(k)] = {val:alts[j]};
							}
							b = b[alt.charAt(k)];
						}
					}
				}
				return tree;
			};
			var match = function (entry, pattern) {
				if (!dict[entry.citycode])
					dict[entry.citycode] = buildTree(entry);
				var b = dict[entry.citycode];
				pattern = pattern.toLowerCase();
				for (var i = 0; i < pattern.length; i++) {
					if (!b[pattern.charAt(i)]) {
						break;
					}
					else {
						b = b[pattern.charAt(i)];
					}
				}
				if (i == pattern.length) {
					entry.matchVal = b.val;
					return 1;
				}
				return 0;
			};
			var ret = function (sender,entry, pattern) {
				if (entry.name == pattern) {
					return 2;
				}
				else if (entry.name.substring(0, pattern.length) == pattern) {
					return 1;
				}
				else {
					return match(entry, pattern);
				}
			};
			return ret;
		}(),
		onCityShow : function (sender, entry) {
			var html = entry.name;
			ZXC.util.log("debug", "matchVal of entry " + entry.name + ":" + entry.matchVal);
			if (entry.matchVal) {
				html += "(" + entry.matchVal + ")";
			}
			else {
				var field = "citycode";
				if (entry[field] && entry[field].length > 0) {
					html += "(" + entry[field].split(",")[0] + ")";
				}
			}
			return html;
		},
		onCitySort : function(sender,entries, pattern) {
			if (pattern && pattern != "") {
				entries.sort(function(a, b){
					var alen = a.matchVal ? a.matchVal.length : a.name.length;
					var blen = b.matchVal ? b.matchVal.length : b.name.length;
					return alen - blen;
				});
			}
			entries.length = Math.min(entries.length, sender.settings.maxlength);
		}
	}
}


ZXC.Namespace("Page");
Page.container = jQuery("body > div.bg")[0];
Page.main = jQuery("#page")[0];
Page.onPageLoad = ZXC.Event();
Page.afterPageLoad = ZXC.Event();
Page.onDeleteGuideLocation = ZXC.Event();
Page.afterDeleteGuideLocation = ZXC.Event();
Page.onAddGuideLocation = ZXC.Event();
Page.afterAddGuideLocation = ZXC.Event();
Page.onShowLocationInMap = ZXC.Event();
Page.onPageLoad.add(function(){
	// here comes the default page level js.

	// simulate :hover effect settings (eg:ie6)
	// To work as designed, buttons must define as following:
	// 1. define class: btn_script
	// 2. define class: btn_xxx  xxx could be the name of button.
	// 3. define css:
	//    .xxx_mouseover  { ... } as :hover effect
	//    .xxx_mousedown  { ... } as :active effect
	ZXC.util.buttonInit();

	$("[error]").bind("error", function() {
		this.src = $(this).attr("error");
	});

	$(".readedmsg").click(function(e){
		if($("#notifications-panel").css('display')=="none")
			$("#notifications-panel").show();
		else
			$("#notifications-panel").hide();

		$.ajax({
			   type: "GET",
			   url: "/ajax/message/readed",
			   data: {} ,
			   success: function(msg){
			     var obj = JSON.parse(msg);
			     //alert(obj.count);
			     if(obj.success)
			     {
					$(".notifications .count").remove();
			     }
			     else
			     {

			     }
			   }
		});
	});

	if ($.fn.scrollable) {
		$(".album_bottomcenter").load('/statics/bottom_data.html .photo',function(responseText, textStatus, XMLHttpRequest){
			if (XMLHttpRequest.status != 200) {
				return;
			}
			$(".album_bottomcenter").scrollable({
				circular:true,
				speed:500,
				prev:".album_bottomleft a",
				next:".album_bottomright a",
				items:"photo",
				keyboard:false
			})
		});
	}
});
