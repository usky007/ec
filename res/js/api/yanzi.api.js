/**
 * Support API Ajax call support.
 */
ZXC.Namespace("Yanzi");

Yanzi.Export("API");

/**
 * Implements manipulate operations of locations in a guide.
 *
 * Supportted options including:
 * locationRemoved: Event
 * locationAdded: Event
 */
ZXC.Class({
name: "Yanzi.API",
construct:
	function () {
	},
methods: {
	test: function(params, callback) {
		this.get("api/test", params || null, callback || function(result) {
			alert("result:" + result);
		});
	},
	get: function(uri, params, callback) {
		return this.call({
			url:uri,
			data:params,
			type:"GET",
			dataType:"json",
			success: callback
		});
	},
	post: function(uri, params, callback) {
		return this.call({
			url:uri,
			data:params,
			type:"POST",
			dataType:"json",
			success: callback
		});
	},
	put: function(uri, params, callback) {
		return this.call({
			url:uri.replace(/\/?(\?.*)?$/, "/put$1"),   // Append "/put" to the end of url, with querys intact.
			data:params,
			type:"POST",
			dataType:"json",
			success: callback
		});
	},
	del: function(uri, params, callback) {
		return this.call({
			url:uri.replace(/\/?(\?.*)?$/, "/delete$1"), // Append "/delete" to the end of url, with querys intact.
			data:params,
			type:"POST",
			dataType:"json",
			success: callback
		});
	},
	call: (function() {
		var credential;
		var getToken = function(cb) {
			if (credential) {
				if (cb) {
					cb(credential);
				}
				return credential;
			}
			jQuery.ajax({
				url:js_context.base_url + "oauth2/access_token",
				data:{grant_type:"client_credentials"},
				type:"POST",
				dataType:"json",
				success: function(data) {
					credential = data.access_token;
					if (cb) {
						cb(credential);
					}
				}
			});
		};
		
		var invoke = function(options) {
			var _this = this;
			getToken(function(token) {
				if (options.url.substring(0, 4) != "http") {
					options.url = js_context.base_url + options.url
				}
				options.headers = options.headers || {};
				options.headers["Authorization"] = "Bearer " + token;
				options.success && (options.success = _this._normalizeWrapper(options.success));
				jQuery.ajax(options);
			});
		};
		return invoke;
	})(),
	normalize: function(result) {
		// deal with array;
		if (jQuery.isArray(result)) {
			for (var i = 0; i < result.length; i++) {
				if ("object" == typeof result[i]) {
					this.normalize(result[i]);
				}
			}
			return result;
		}
		
		// normalize
		var link;
		for (var key in result) {
			if (key == "link") {
				result.link = this.API.parseLink(result.link);
			}
			else if ("object" == typeof result[key]) {
				this.normalize(result[key]);
			}
		}
		return result;
	},
	_normalizeWrapper: function(callback) {
		var _this = this;
		var handler = function(data, textStatus, jqXHR) {
			_this.normalize(data);
			callback(data, textStatus, jqXHR);
		}
		return handler;
	}
},
statics: {
	instance : (function() {
		var inst;
		return function() {
			if (!inst) {
				inst = new this();
			}
			return inst;
		};
	})(),
	parseLink : function(link) {
		if (!jQuery.isArray(link)) {
			return link;
		}
	
		var links = {};
		if (link.length) {
			for (var i = 0; i < link.length; i++) {
				links[link[i].rel] = link[i].href;
			}
		}
		else {
			links[link.rel] = link.href;
		}
		return links;
	}
}
});