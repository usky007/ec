/**
 * ZXC utility functions
 *
 * namespace: ZXC.util
 * function: locate, copy
 */

ZXC.Namespace("ZXC.util");

/**
 * get css length style without unit, eg:px
 */
ZXC.util.noUnitCss = function(elem, style, val) {
	if (val === undefined || val == null) {
		var val = jQuery(elem).css(style);
		if (val && "string" == typeof val)
			val = val.replace(/[a-zA-Z]/g, "");
		return val ? parseInt(val) : 0;
	}
	else
		jQuery(elem).css(style,val + "px");
};
/**
 * locate target element to a position related to base element.
 * edge: center, top-left, top-right, bottom-left, bottom-right
 * direct: center, left-up, left-down, right-up, right-down;
 */
ZXC.util.locate = function(target, base, edge, direct, offsetY, offsetX, options) {
	// calculate position
	var elem = (base === undefined || base == null) ? target : base;
	var offset = jQuery(elem).offset();
	if (base === undefined || base == null)
		return [offset.top, offset.left];

	if (base == 'screen')
		return ZXC.util.locateFix(target, edge, offsetY, offsetX);
	else if (base == 'innerFix')
		return ZXC.util.locateInnerFix(target, offsetY, offsetX, options);

	var stop = jQuery(target).offsetParent();
//	while (stop.length > 0 && stop[0] != document.body &&
//		(!(stop.css("position")) || stop.css("position") == "static" || stop.css("position") == "absolute")) {
//		stop = 	stop.parent();
//	}
//	alert(offset.top + ":" + offset.left);
	if (stop.length > 0 && stop[0] != document.body) {
		var stopOffset = stop.offset();
		offset.top -= stopOffset.top + this.noUnitCss(stop, "borderTopWidth");
		offset.left -= stopOffset.left + this.noUnitCss(stop, "borderLeftWidth");
	}
	direct = direct ? direct : "";
	offsetY = offsetY ? parseInt(offsetY) : 0;
	offsetX = offsetX ? parseInt(offsetX) : 0;
	var targetWidth = this.width(target);
	var targetHeight = this.height(target);
	var baseWidth = this.width(base);
	var baseHeight = this.height(base);
	switch (edge) {
		case "center":
			offset.top += baseHeight / 2;
			offset.left += baseWidth / 2;
			break;
		case "top-left":
			break;
		case "top-right":
			offset.left += baseWidth;
			break;
		case "bottom-right":
			offset.top += baseHeight;
			offset.left += baseWidth;
			break;
		case "bottom-left":
		default:
			offset.top += baseHeight;
			break;
	}

	var direct_arr = direct.split("-");
	var x_coordinate = direct_arr[0];
	var y_coordinate = direct_arr[1];
	switch(x_coordinate) {
		case "left":
			offset.left -= targetWidth + offsetX;
			break;
		case "center":
			offset.left -= targetWidth / 2;
			break;
		case "right":
			offset.left += offsetX;
			break;
	}
	switch(y_coordinate) {
		case "up":
			offset.top -= targetHeight + offsetY;
			break;
		case "center":
		case undefined:
			offset.top -= targetHeight / 2;
			break;
		case "down":
			offset.top += offsetY;
			break;
	}

	jQuery(target).css("top", offset.top).css("left", offset.left);
};

/**
 * location: center, top-left, top-right, bottom-left, bottom-right
 */
ZXC.util.locateFix = function(target, location, offsetY, offsetX) {
	var doLocateFix = function(elem, location, offsetY, offsetX) {
		location = location || "bottom-right";
		offsetY = offsetY || 0;
		offsetX = offsetX || 0;
		var isIE6 = (jQuery.browser.msie && jQuery.browser.version=="6.0") ? true : false;
		var loc;//层的绝对定位位置
		var scrollLeft,scrollTop;
		if (isIE6) {
			scrollLeft = ZXC.util.getPageXOffset();
			scrollTop = ZXC.util.getPageYOffset();
		}

	    if (location==undefined || location.constructor == String){
	        switch(location) {
	            case("bottom-right")://右下角
	                loc = {right:offsetX+"px",bottom:offsetY+"px"};
	                break;
	            case("bottom-left")://左下角
	            	var l = offsetX;//居左
	                var b = offsetY;//居上
	                if (isIE6) {
	                	l += scrollLeft;
	                }
	                loc = {left:l+"px",bottom:b+"px"};
	                break;
	            case("top-left")://左上角
	            	var l = offsetX;//居左
	                var t = offsetY;//居上
	                if (isIE6) {
	                	l += scrollLeft;
	                	t += scrollTop;
	                }
	                loc = {left:l+"px",top:t+"px"};
	                break;
	            case("top-right")://右上角
	            	var r = offsetX;//居左
	                var t = offsetY;//居上
	                if (isIE6) {
	                	t += scrollTop;
	                }
	                loc = {right:r+"px",top:t+"px"};
	                break;
	            case("center")://居中
	                var l = 0;//居左
	                var t = 0;//居上
	                var windowWidth,windowHeight;//窗口的高和宽

	                //取得窗口的高和宽
	                if (self.innerHeight) {
	                    windowWidth = self.innerWidth;
	                    windowHeight = self.innerHeight;
	                } else if (document.documentElement && document.documentElement.clientHeight) {
	                    windowWidth = document.documentElement.clientWidth;
	                    windowHeight = document.documentElement.clientHeight;
	                } else if (document.body) {
	                    windowWidth = document.body.clientWidth;
	                    windowHeight = document.body.clientHeight;
	                }

	                l = windowWidth / 2 - jQuery(elem).width() / 2 + offsetX;
	                t = windowHeight / 2 - jQuery(elem).height() / 2 + offsetY;

	                if (isIE6) {
	                	l += scrollLeft;
	                	t += scrollTop;
	                }

	                loc = {left:l+"px",top:t+"px"};
	                break;
	        }
	    } else {
	        loc=location;
	    }
	    jQuery(elem).css("z-index","100").css(loc).css("position","fixed");

	    if (isIE6)
	        jQuery(elem).css("position","absolute");
	};

	if (jQuery(target).length == 0)
		return ;
	jQuery(target).show();
	if (jQuery.browser.msie && jQuery.browser.version=="6.0"){
		doLocateFix(target, location, offsetY, offsetX);
		jQuery(target).attr("lasttime", 0);
		jQuery(window).scroll(function(){
			var last_t = parseInt(jQuery(target).attr("lasttime"));
			var now = (new Date()).getTime();
			if (last_t > 0 && now - last_t > 20)
			{
				doLocateFix(target, location, offsetY, offsetX);
				jQuery(target).attr("lasttime", now);
			}
			else
			{
				if (last_t == 0)
					jQuery(target).attr("lasttime", now);
				window.setTimeout(function(){
					var last_time = parseInt(jQuery(target).attr("lasttime"));
					var now_time = (new Date()).getTime();
					if (now_time - last_time > 20)
					{
						doLocateFix(target, location, offsetY, offsetX);
						jQuery(target).attr("lasttime", now);
					}
				}, 40);
			}
		});

	} else {
		doLocateFix(target, location, offsetY, offsetX);
		jQuery(window).resize(function(){
			doLocateFix(target, location, offsetY, offsetX);
		});
	}
};

/**
 * offsetY 上边距
 * offsetX 左边距
 * options
 *		maxRTop 相对于页面的（不是显示屏）最大垂直偏移量。
 *      id      Unique Id, to identify function call
 */
ZXC.util.locateInnerFix = function(elem, offsetY, offsetX, options) {
	var target = jQuery(elem);
	if (target.length == 0)
		return;
	target = target[0];

	// Reset styles
	jQuery(target).css({'position':'', 'top':'', 'left':''});
	var postfix = options.id ? ("." + options.id) : "";
	if (postfix.length > 0) {
		jQuery(window).unbind("scroll" + postfix);
	}

	// Give js engine some time.
	setTimeout(function() {
		offsetY = offsetY || 0;
		offsetX = offsetX || 0;
		options = options || {};

		var pNode = target.parentNode;
		var offset = jQuery(target).offset();
		var top = offset.top.valueOf();
		var left = offset.left.valueOf() + offsetX;

		jQuery(window).bind("scroll" + postfix, function(){
			var elemHeight = target.scrollHeight;
			var pHeight = pNode.scrollHeight;
			var maxRTop = pHeight - elemHeight - 80;
			var scrollTop = ZXC.util.getPageYOffset();
			var rTop = scrollTop - top + 10;

	//		ZXC.util.log("debug", "maxRTop,scrollTop,top,rTop:"+maxRTop+","+scrollTop+","+top+","+rTop+",");

			if (typeof options.maxRTop != "undefined")
				maxRTop = options.maxRTop;

			if (rTop > 0 && rTop < maxRTop) {
				if (jQuery.browser.msie && parseInt(jQuery.browser.version) <= 6) {
					jQuery(target).css('position', 'relative');
					jQuery(target).css('top', rTop+'px');
				} else {
					jQuery(target).css('position', 'fixed');
					jQuery(target).css('top', offsetY+'px');
					jQuery(target).css('left', left+'px');
				}
			} else if (rTop >= maxRTop) {
				jQuery(target).css('position', 'relative');
				jQuery(target).css('top', maxRTop+'px');
				jQuery(target).css('left', '');
			} else {
				jQuery(target).css('position', '');
				jQuery(target).css('top', '');
				jQuery(target).css('left', '');
			}
		}).trigger("scroll");
	}, 100);
};

ZXC.util.width = function(elem, val, altStyle) {
	var jElement = jQuery(elem);
	var direction = ["Right", "Left"];
	var style = [["margin",""], ["border","Width"],["padding",""]];
	if (val === undefined || val == null) {
		return jElement.outerWidth(true);
	} else {
		for (var i = 0; i < direction.length; i++)
			for (var j = 0; j < style.length; j++)
			{
				var length = jElement.css(style[j][0] + direction[i] + style[j][1]);
				if (length && "string" == typeof length)
					length = length.replace(/[a-zA-Z]/g, "");
				val -= length ? parseInt(length) : 0;
			}
		if (val < 0) val = 0;
		jElement.css(altStyle || 'width', val);
	}
};
ZXC.util.height = function(elem, val, altStyle) {
	var jElement = jQuery(elem);
	var direction = ["Top", "Bottom"];
	var style = [["margin",""], ["border","Width"],["padding",""]];
	if (val === undefined || val == null) {
		return jElement.outerHeight(true);
	} else {
		for (var i = 0; i < direction.length; i++)
			for (var j = 0; j < style.length; j++)
			{
				var length = jElement.css(style[j][0] + direction[i] + style[j][1]);
				if (length && "string" == typeof length)
					length = length.replace(/[a-zA-Z]/g, "");
				val -= length ? parseInt(length) : 0;
			}
		if (val < 0) val = 0;
		jElement.css(altStyle || 'height', val);
	}
};
ZXC.util.selectInputText = function(input) {
	if (!input)
		return false;

	if (input.tagName.toLowerCase() != 'textarea' &&
		input.tagName.toLowerCase() != 'input' &&
		input.getAttribute('type') != 'text')
		return false;

	if (input.createTextRange) {
		var range = input.createTextRange();
		range.select();
		return true;
	}
	else if (input.select) {
		input.select();
		return true;
	}
	return false;
};
/**
 * Copyright (C) krikkit - krikkit@gmx.net
 * --> http://www.krikkit.net/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */
ZXC.util.copy = function(txt) {
	if (window.clipboardData)
	{
		// the IE-manier
		window.clipboardData.setData("Text", txt);

		return true;
	}
	else if (window.netscape)
	{
		try{
			// dit is belangrijk maar staat nergens duidelijk vermeld:
			// you have to sign the code to enable this, or see notes below
			netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');

			// maak een interface naar het clipboard
			var clip = Components.classes['@mozilla.org/widget/clipboard;1']
	           	.createInstance(Components.interfaces.nsIClipboard);
			if (!clip) return false;

			// maak een transferable
			var trans = Components.classes['@mozilla.org/widget/transferable;1']
               	.createInstance(Components.interfaces.nsITransferable);
			if (!trans) return false;

			// specificeer wat voor soort data we op willen halen; text in dit geval
			trans.addDataFlavor('text/unicode');

			// om de data uit de transferable te halen hebben we 2 nieuwe objecten
			// nodig om het in op te slaan
			var str = new Object();
			var len = new Object();

			var str = Components.classes["@mozilla.org/supports-string;1"]
				.createInstance(Components.interfaces.nsISupportsString);

			var copytext = txt;
			str.data=copytext;
			trans.setTransferData("text/unicode",str,copytext.length*2);

			var clipid=Components.interfaces.nsIClipboard;
			if (!clip) return false;

			clip.setData(trans,null,clipid.kGlobalClipboard);
			return true;
		}
		catch(e)
		{
			return false;
		}
	}
	return false;
};
/**
 * portable functions for querying window and document geometry
 *
 * This module defines functions for querying window and document geometry.
 *
 * getScreenX/Y( ): return the position of the window on the screen
 * getInnerWidth/Height( ): return the size of the browser viewport area
 * getScrollWidth/Height( ): return the size of the document
 * getPageXOffset( ): return the position of the horizontal scrollbar
 * getPageYOffset( ): return the position of the vertical scrollbar
 *
 * Note that there is no portable way to query the overall size of the
 * browser window, so there are no getWindowWidth/Height( ) functions.
 *
 * IMPORTANT: This module must be included in the <body> of a document
 *            instead of the <head> of the document.
 */
ZXC.util.getScreenX = function() {
	if (window.screenLeft != undefined) // IE and others
	    return window.screenLeft;
	else // Firefox and others
	    return window.screenX;
}
ZXC.util.getScreenY = function() {
	if (window.screenTop != undefined) // IE and others
	    return window.screenTop;
	else // Firefox and others
	    return window.screenY;
}
ZXC.util.getInnerWidth = function() {
	if (jQuery != undefined) {
		return jQuery(window).width();
	}
	else if (window.innerWidth != undefined) { // All browsers but IE
	    return window.innerWidth;
	}
	else if (document.documentElement && document.documentElement.clientWidth != undefined) {
	    // These functions are for IE 6 when there is a DOCTYPE
	    return document.documentElement.clientWidth;
	}
	else if (document.body.clientWidth != undefined) {
	    // These are for IE4, IE5, and IE6 without a DOCTYPE
	    return document.body.clientWidth;
	}
}
ZXC.util.getInnerHeight = function() {
	if (jQuery != undefined) {
		return jQuery(window).height();
	}
	else if (window.innerHeight != undefined) { // All browsers but IE
	    return window.innerHeight;
	}
	else if (document.documentElement && document.documentElement.clientHeight != undefined) {
	    // These functions are for IE 6 when there is a DOCTYPE
	    return document.documentElement.clientHeight;
	}
	else if (document.body.clientHeight != undefined) {
	    // These are for IE4, IE5, and IE6 without a DOCTYPE
	    return document.body.clientHeight;
	}
}
ZXC.util.pageXOffset = function(val) {
	var obj, attr;
	if (window.pageXOffset != undefined) { // All browsers but IE
		obj = window;
		attr = "pageXOffset";
	}
	else if (document.documentElement && document.documentElement.scrollLeft != undefined) {
	    // These functions are for IE 6 when there is a DOCTYPE
	    obj = document.documentElement;
		attr = "scrollLeft";
	}
	else if (document.body.scrollLeft != undefined) {
	    // These are for IE4, IE5, and IE6 without a DOCTYPE
	    obj = document.body;
		attr = "scrollLeft";
	}
	if (val === undefined) {
		return obj[attr];
	}
	else {
		jQuery("html").scrollLeft(val);
	}
}
ZXC.util.getPageXOffset = ZXC.util.setPageXOffset = ZXC.util.pageXOffset;
ZXC.util.pageYOffset = function(val) {
	var obj, attr;
	if (window.pageYOffset != undefined) { // All browsers but IE
		obj = window;
		attr = "pageYOffset";
	}
	else if (document.documentElement && document.documentElement.scrollTop != undefined) {
	    // These functions are for IE 6 when there is a DOCTYPE
	    obj = document.documentElement;
		attr = "scrollTop";
	}
	else if (document.body.scrollTop != undefined) {
	    // These are for IE4, IE5, and IE6 without a DOCTYPE
	    obj = document.body;
		attr = "scrollTop";
	}
	if (val === undefined) {
		return obj[attr];
	}
	else {
		jQuery("html").scrollTop(val);
	}
}
ZXC.util.getPageYOffset = ZXC.util.setPageYOffset = ZXC.util.pageYOffset;
// These functions return the size of the document. They are not window
// related, but they are useful to have here anyway.
ZXC.util.getScrollWidth = function() {
	if (document.documentElement && document.documentElement.scrollWidth != undefined) {
	    return document.documentElement.scrollWidth;
	}
	else if (document.body.scrollWidth != undefined) {
	    return document.body.scrollWidth;
	}
}
ZXC.util.getScrollHeight = function() {
	if (document.documentElement && document.documentElement.scrollHeight != undefined) {
	    return document.documentElement.scrollHeight;
	}
	else if (document.body.scrollHeight != undefined) {
	    return document.body.scrollHeight;
	}
}
ZXC.util.encodeQueryContext = function(key, withoutPostfix) {
	var encodekey = encodeURIComponent(key);
	encodekey = encodekey.replace(/\!/g, "%21");
	encodekey = encodekey.replace(/\~/g, "%7E");
	encodekey = encodekey.replace(/\*/g, "%2A");
	encodekey = encodekey.replace(/\'/g, "%27");
	encodekey = encodekey.replace(/\(/g, "%28");
	encodekey = encodekey.replace(/\)/g, "%29");
	encodekey = encodekey.replace(/\./g, "~2E");
	encodekey = encodekey.replace(/\-/g, "~2D");
	encodekey = encodekey.replace(/\%2F/g, "-");
	encodekey = encodekey.replace(/\%/g, "~");
	return withoutPostfix ? encodekey : (encodekey + ".htm");
}
// Dynamic load javascript
ZXC.util.loadScript = function(url, callback) {
	var script = jQuery(document.createElement('script')).load(callback)[0];
	script.type = 'text/javascript';
	document.getElementsByTagName('head')[0].appendChild(script);
	script.src = url;
	/*ZXC.util.jQueryAjaxHelper({
		type: "GET",
		url: url,
		success: callback,
		dataType: "script",
		cache: true
	});*/
}
// Dynamic load javascript
ZXC.util.loadStyle = function(url) {
	var link = document.createElement('link');
	link.rel = 'stylesheet';
	link.type = "text/css";
	document.getElementsByTagName('head')[0].appendChild(link);
	link.href = url;
};

// Load Image with a transition process animation.
ZXC.util.loadImage = function(elem, url, loadingImg) {
	if (elem.tagName.toLowerCase() != 'img') return;
	var inst = ZXC.util.loadImage;

	// already loaded
	inst.loaded = inst.loaded || {};
	if (inst.loaded[url]) {
		jQuery(elem).attr("src", url);
		return;
	}
	// create loader
	if (!elem.loader) {
		elem.loader = {
			loading:false, defH:0, defW:0, link:null, bg:null,
			proxy:jQuery(document.createElement("img")).load(function() {
				var target = this.target;
				with(target.loader) {
					inst.loaded[link] = true;
					loading = false;
					jQuery(target).height(defH).width(defW)
						.css("background", bg)
						.attr("src", link);
				}
			}).error(function() {
				jQuery(this.target).trigger("error");
			})
		};
		elem.loader.proxy[0].target = elem;
	}

	var oldbg = jQuery(elem).css("background");
	var newbg = loadingImg || js_context.res_url.get("images/loading.gif");
	with(elem.loader) {
		if (!loading) {
			defH = $(elem).css("height") || "auto";
			defW = $(elem).css("width") || "auto";
			jQuery(elem).height(jQuery(elem).height())
				.width(jQuery(elem).width())
				.css("background", "url(\"" + newbg + "\") no-repeat center center")
				.attr("src", js_context.res_url.get("images/space.gif"));
			loading = true;
		}
		link = url;
		bg = oldbg;
		proxy.attr("src", link);
	}
};
jQuery.fn.getImage = function(url) {
	if (this.length > 0)
		ZXC.util.loadImage(this[0], url);
	return this;
};
ZXC.util.safeHtml = function(content) {
	var re, arr, code = '';
	var func = function(co) {
		var ZXCHTML = '';
		var old=document.write;
		document.write=function(c){ZXCHTML += c;};
		window.eval(co);
		document.write=old;
		return ZXCHTML;
	};

	//search js code
	re = /<script(?:.*?)>((?:.|\s)*?)<\/script>/ig;
	while ((arr = re.exec(content)) != null) {
		if (arr[1] != '') {
			code += arr[1];
		}
	}

	//search .js file, and gets its code
	re = /<script(?:.*?)src=(?:'|")(.*?)(?:'|")(?:(?:.|\n|\r)*?)\/script>/ig;
	while ((arr = re.exec(content)) != null) {
		jQuery.ajax({
			type: "GET",
			url : arr[1],
			async : false,
			dataType: "text",
			cache:true,
			success : function(data){
				code += data;
			}
		});
	}

	code = code.replace(/\n/ig, "")
		.replace(/<!--\/\//ig, "").replace(/<!\[CDATA\[/ig, "")
		.replace(/\/\/ \]\]>/ig, "").replace(/-->/ig, "");
	try {
		return func(code);
	} catch(e) {
		return '';
	}
}
jQuery.fn.safeHtml = function(content, callback) {
	if (this.length > 0) {
		var content = ZXC.util.safeHtml(content);
		if (typeof(callback) == 'function')
			content = callback(content);
		this.html(content);
	}
	return this;
};

ZXC.util.registerUnloadMonitor = function(elem, callback) {
	if (elem && "function" == typeof elem)
	{
		callback = elem;
		elem = null;
	}
	if (!elem) elem = ".ZXC_unload";
	if (!callback)
		callback = function(event) {
			ZXC.UI.Dialog.message(ZXC.Resource.getResource().entry(null, "INFO_REQUESTING"), -1);
			event.preventDefault();
			event.data(event);
		};

	jQuery(elem).each(function() {
		// link or button
		var obj = jQuery(this);
		var func = obj.attr("onclick");
		if (func) {
			obj.attr("onclick", "");
			if ("string" == typeof func) {
				eval("c = function(event) {" + func + "};");
				func = c;
			}
		}
		else if (obj.attr("href")) {
			func = function(event) {
				var link = obj.attr("href");
				link = link.replace(/@([^@]*)@/g, function(pattern, key) {
					if (key == "") return "@";
					else if (ZXC.util.context(key))
						 return ZXC.util.context(key);
				});
				if (obj.attr("target"))
					window.open(link, obj.attr("target"));
				else
					window.location = link;
			};
		}
		if (!func)
			return;

		// reset click handler
		obj.bind("click", func, callback);
	});
};
ZXC.util.jQueryAjaxHelper = function(ajaxOptions, errorHandler) {
	ajaxOptions.error = errorHandler || ajaxOptions.error ||
		function(request, status, ex){
			if (ex && ex.message) {
				ZXC.util.log("ERROR", ex.message);
				return;
			}
			var infokey = "ERROR_REQUEST_FAILED";
			switch (status) {
				case "timeout": infokey = "ERROR_REQUEST_TIMEOUT"; break;
				case "parsererror": infokey = "ERROR_REQUEST_PARSEERROR"; break;
			}
			ZXC.util.log("ERROR", ZXC.Resource.getResource().entry(null, infokey));
			//ZXC.UI.Dialog.alert(ZXC.Resource.getResource().entry(null, infokey));
		};

	var successCallback = ajaxOptions.success;
	ajaxOptions.success = function(data, textStatus) {
		if (ajaxOptions.dataType == 'json' && data && data.profiler) {
			ZXC.util.log("profiler", data.profiler._CDATA);
		}
		if (ajaxOptions.dataType == 'json' && data && data.dialog) {
			var dialog = data.dialog;
			if (dialog.type == 'custom' && !ZXC.UI.Dialog[dialog.id]) {
				var dlg = document.createElement("div");
				jQuery(dlg).css({display:"none"})
					.html(dialog.message.replace(/@res_url@/g, js_context.res_url))
					.appendTo(document.body);
			}
			// for better user experience, launch after some delays;
			ZXC.UI.Dialog.message(ZXC.Resource.getResource().entry(null, "INFO_UI_LOADED"));
			setTimeout(function() {ZXC.UI.Dialog.launch(dialog.id);}, 300);
			return;
		}
		if (!successCallback)
			return;
		try {
			successCallback(data, textStatus)
		}
		catch (ex) {
			ajaxOptions.error(null, "responseException", ex);
		}
	}

	try {
		jQuery.ajax(ajaxOptions);
	}
	catch (ex) {
		ajaxOptions.error(null, "requestException", ex);
	}
};
ZXC.util.buildFormData = function(frm) {
	var option = {};
	frm = jQuery(frm);
	var addr = frm.attr("action");

	var data = null;
	var url = "";
	var context = "";
	jQuery(":input[method!='skip']", frm).each(function(){
		if (this.tagName == "INPUT")
			switch (this.type) {
				case "radio":
				case "checkbox":
					if (!this.checked) return;
				case "text":
				case "password":
				case "hidden":
					break;
				default:
					return;
			}

		var val = jQuery(this).val();
		if (!val || val.length == 0) return;
		val = val.replace(/(^\s+)|(\s+$)/g, "");
		if (val.length == 0) return;

		var name = jQuery(this).attr("name");
		switch (jQuery(this).attr("method")) {
			case "url" :
				url += val + "/";
				break;
			case "context":
				if (!name) return;
				context += name + "." + ZXC.util.encodeQueryContext(val, true) + ".";
				break;
			default:
				if (!name) return;
				data = data || {};
				if (!data[name]) {
					data[name] = val;
				}
				else if (data[name].push) {
					data[name].push(val);
				}
				else {
					data[name] = [data[name], val];
				}
				break;
		}
	});
	url = url.substring(0, url.length - 1);

	if (addr) {
		context = (context.length == 0) ? context : (context + "htm");
		option.url = addr.replace(/^(.+?)\/*$/, "$1/") + url + context;
		/**/
	}

	option.method = frm.attr("method") || "POST";
	option.data = data;
	return option;
};
ZXC.util.context = function(key, val) {
	if (!ZXC.globalNS.js_context)
		ZXC.globalNS.js_context = {};
	if (key === undefined)
		return ZXC.globalNS.js_context;

	if (val === undefined)
		return ZXC.globalNS.js_context[key];
	else
		ZXC.globalNS.js_context[key] = val;
};
ZXC.util.bind = function(target, data, type, more)
{
	var mode = type;
	if (type != "eval" && type != "member")
		mode = "event";

	if (mode == "event") {
		var triggers = ("string" == typeof data) ? jQuery("[obj=" + data + "][op]") : jQuery("[op]", data);
		if ("string" != typeof data && jQuery(data).attr("op")) {
			triggers = triggers.add(data); // include root element itself.
		}

		triggers.each(function() {

			var eventname = type;
			if (type == "event") {
				eventname = jQuery(this).attr("event") || "click";
			}
			var op = jQuery(this).attr("op");
			if (op.length == 0)
				return;
			op = [op, op + "Handler", "on" + op];
			for (var i = 0; i < op.length; i++) {
				if (target[op[i]] && "function" == typeof target[op[i]]) {
					jQuery(this).bind(eventname, more, function(event) {
						if (!event.currentTarget)
							event.currentTarget = this;
						target[op[i]](event, event.data);
					});
					break;
				}
			}
		});
	}
	else if (mode == "member") {
		var infos = ("string" == typeof data) ? jQuery("[obj=" + data + "][var]") : jQuery("[var]", data);
		infos.each(function() {
			var member = jQuery(this).attr("var");
			target[member] = this;
		});
	}
	else {
		jQuery("[mark]", jQuery(target)).each(function() {
			var marks = jQuery(this).attr("mark");
			marks = marks.split(",");
			for (var i = 0; i < marks.length; i++) {
				var mark = marks[i];
				if (mark.length == 0) continue;

				var is_attr = false;
				if (mark.charAt(0) == "@") {
					is_attr = true;
					mark = mark.substring(1);
				}

				var marks = mark.split(".");
				var mainVal = data;
				for (var i = 0; i < marks.length && marks[i].length > 0 && mainVal; i++) {
					mark = marks[i];
					mainVal = mainVal[mark];
				}
				if (mainVal === undefined) {
					continue;
				}
				if ("object" == typeof mainVal) {
					// deal with object value, set attributes
					for (var key in mainVal) {
						if (jQuery(this).attr(key) !== undefined) {
							jQuery(this).attr(key, mainVal[key]);
						}
					}
					mainVal = mainVal[0];
				}
				if (mainVal === undefined) {
					continue;
				}

				if (is_attr) {
					jQuery(this).attr(mark, mainVal.toString());
					continue;
				}
				var tagname = this.tagName;
				switch(tagname) {
					case 'INPUT':
						if (this.type == "radio" || this.type == "checkbox") {
							if (this.value == mainVal) {
								this.checked = true;
							}
							break;
						}
					case 'TEXTAREA':
					case 'SELECT':
						jQuery(this).val(mainVal.toString());break;
					case 'IMG':
						jQuery(this).getImage(mainVal.toString());break;
					default:
						jQuery(this).html(mainVal.toString());break;
				}
			} // end of for each mark
		});
	}
};
ZXC.util.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        var path = options.path ? '; path=' + options.path : '';
        var domain = options.domain ? '; domain=' + options.domain : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
	}
};

ZXC.util.formatString = function(str, len) {
	var ll = str.length; 	//total # of bytes
	var i = 0; 			//# of bytes
	var l = 0; 			//# of bytes to display
	var s = str;
	while (i < ll) {
		if (str.charCodeAt(i) < 0x80) { //if in ascii
			l++;
		} else { //chineses
			l+=2;
		}
		i++;

		if (l >= len) {
			s = str.substring(0, i);
			if (i < ll) {
				s = s + "...";
				break;
			}
			break;
		}
	}
	return s;
};

var uneval;
ZXC.util.clone = function(obj) {
	if (uneval)
		return eval(uneval(obj));
	else if (typeof obj == "object") {
		var cloned = new obj.constructor();
		for ( var name in obj ) {
			var copy = obj[name];
			// Prevent never-ending loop
			if ( copy === obj )
				cloned[name] = copy;
			else if (typeof copy == "object")
				cloned[name] = ZXC.util.clone(copy);
			else
				cloned[name] = copy;
		}
		return cloned;
	}
	else return obj;
};

ZXC.util.WindowSizeMonitor = {
	available:0,
	width:ZXC.util.getInnerWidth(),
	height:ZXC.util.getInnerHeight(),
	enable : function() {
		var obj = this;
		if (!this.available) {
			this.available = true;
			jQuery(window).bind("resize.ZXC", function(event) {
				event.stopPropagation();
				obj.changedHandler();
			});
			obj.changedHandler();
		}
	},
	disable : function() {
		jQuery(window).unbind("resize.ZXC");
		this.available = false;
	},
	changedHandler : function() {
		var width = ZXC.util.getInnerWidth();
		var height = ZXC.util.getInnerHeight();
		if (width != this.width || height != this.height)
		{
			this.width = width;
			this.height = height;
			jQuery(window).trigger("windowResize", [{"width":width, "height":height}]);
		}
	}
};

ZXC.util.log = function(){
	var logmsgpool = [];
	var enablereg = /logenable\.true\./i;
	var enablereg2 = /logenable=1/i;
	var logenable = enablereg.test(location.href) || enablereg2.test(location.href);

	var log = function(type, cls, msg) {
		// normalize arguments
		if (arguments.length == 2)
		{
			msg = cls;
			cls = 'default';
		}

		if (ZXC.Defined("ZXC.util.Logger")) {
			var logger = ZXC.util.Logger.getLogger();
			if (!logger.initialized) {
				logger.initialize({enable:true});
			}
			return logger.log(type, cls, msg);
		}
		// logger not defined ? initialize or skip if disabled.
		if (!logenable)
			return;

		if (logmsgpool.length == 0)
		{
			ZXC.util.loadStyle(js_context.res_url + 'css/widget/debug.css');
			ZXC.util.loadScript(js_context.base_url + 'res/js/zxc/util/zxc.util.logger.js', function() {
				var logger = ZXC.util.Logger.getLogger();
				logger.initialize({enable:true});
				while(logmsgpool.length > 0)
				{
					var logmsg = logmsgpool.shift();
					logger.log(logmsg[0], logmsg[1], logmsg[2]);
				}
			});
		}
		logmsgpool.push([type, cls, msg]);
	}
	return log;
}();

// feature detection for :hover pseudo-class
if (!jQuery.support) jQuery.support = {};
jQuery.support.hoverEffect = !jQuery.browser.msie || parseInt(jQuery.browser.version) >= 7;

// register button effect
ZXC.util.buttonInit = function() {
	if (!jQuery.support.hoverEffect) {
		jQuery("input.btn_script").each(function() {
			jQuery(this).removeClass("btn_script");
			var match = jQuery(this).attr("class").match(/(?:^| )btn_[^ ]+/);
			if (!match) return;
			jQuery(this).mouseover(function() {jQuery(this).addClass(match[0]+"_mouseover");})
				.mouseout(function() {jQuery(this).removeClass(match[0]+"_mouseover").removeClass(match[0]+"_mousedown");})
				.mousedown(function() {jQuery(this).removeClass(match[0]+"_mouseover").addClass(match[0]+"_mousedown");})
				.mouseup(function() {jQuery(this).removeClass(match[0]+"_mousedown").addClass(match[0]+"_mouseover");})
				.click(function() {jQuery(this).blur();});
		});
	}
}

ZXC.util.trace = function(action, type) {
	if (typeof pageTracker == "undefined" || !pageTracker)
		return;
	var url = location.href;
	var re = new RegExp("^http(s)?:\/\/[^\/]*" + js_context.base_url + "(.*)$","ig");
	url = url.replace(re, "$2");
	if (typeof type == "undefined" || !type)
		type = "unknown";
	var uri = "/trace/" + action + "/" + type + "/" + url;
	pageTracker._trackPageview(uri);
};