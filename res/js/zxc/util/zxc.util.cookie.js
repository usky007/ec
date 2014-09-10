ZXC.Namespace("ZXC.util");

ZXC.util.Export("Cookie");

ZXC.Class({
name: "ZXC.util.Cookie",
construct:
	function() {		
	},
methods: {
	set : function(name, value, timeout, options){        
		if (typeof timeout == 'string') {
			var unit = timeout.replace(/[0-9]+/, "").toLowerCase();
			timeout = parseInt(timeout);
			if (timeout != 0) {
				switch(unit) {
					case "y":
					case "year":
					case "years":
						timeout *= 365;
					case "d":
					case "day":
					case "days":
						timeout *= 24;
					case "h":
					case "hour":
					case "hours":
						timeout *= 60;
					case "m":
					case "min":
					case "minute":
					case "minutes":
						timeout *= 60;
					case "s":
					case "sec":
					case "second":
					case "seconds":
						timeout *= 1000;
				}
			}
		}
		else if (typeof timeout == 'object') {
			options = timeout;
			delete timeout;
		}
		
		options = options || {};
		
		if (value === null) {
            value = '';
            timeout = -1;
        }
		
		var expires = '';
		var date = new Date();
		options.expires = options.expires || (timeout && date.setTime(date.getTime() + timeout) && date);
		if (options.expires && options.expires.toUTCString) {
			expires = '; expires=' + options.expires.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = '; path=' + (options.path || '/');
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', escape(value), expires, path, domain, secure].join('');
	},
	get : function(name) {
		var arr = document.cookie.match(new RegExp("(?:^| )"+name+"=([^;]*)(?:;|$)"));
		if(arr != null) return unescape(arr[1]); return null;
	},
	delete : function(name) {
		setCookie(name, null);
  	}
}
});

ZXC.util.Cookie = new ZXC.util.Cookie();

if (jQuery) {
	// jQuery Plugin Support
	jQuery.cookie = function(name, value, options) {
		if (typeof value != 'undefined') {
			ZXC.util.Cookie.set(name, value, options);
		}
		else {
			return ZXC.util.Cookie.get(name);
		}
	};
}
