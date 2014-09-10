ZXC.Namespace("ZXC.util");

ZXC.util.Export("Tracker");

ZXC.util.Tracker = ZXC.Class({
name: "ZXC.util.Tracker",
construct:
	function (code) {
		if (code)
			this.code = code;
		else if (code = window.location.href.match(/(?:\?|&)trace=([%a-zA-Z0-9]+)(?:&|$)/))
			this.code = code[1];
	},
methods: {
	generateTracker : function(offworld) {
		if (!this.code)
			return;

		var obj = this;
		jQuery("a").each(function() {
			if (!this.href) {
				return;
			}
			else if (offworld && this.href.match(/^http:\/\/[a-z0-9]+\.ZXC\.com(\/.*)?$/)) {
				return;
			}
			else {
			 	if (!offworld) {
					this.href = obj.normalize_url(this.href);
				}
				jQuery(this).click(function() {
					obj.log("debug", "trace click recorded: " + obj.code);
					if (pageTracker && pageTracker._trackPageview)
					{
						pageTracker._trackPageview("/trace/" + obj.code);
					}
				});
			}
		});
	},
	normalize_url : function(url) {
		if (!this.code)
			return url;

		// formalize url
		var newUrl = url;
		if (url.indexOf("/") == 0)
			newUrl = "http://" + window.location.host + url;
		else if (url.indexOf("://") < 0 && !url.match(/^[a-zA-Z0-9]+:.*$/))
			newUrl = window.location.href.match(/^(.*\/)(?:[^\/]*)$/)[1] + url;
		// filter invalid url
		for (var i = 0; i < ZXC.util.Tracker.filter.length; i++)
			if (!newUrl.match(ZXC.util.Tracker.filter[i]))
				return url;
		// transform url
		var segment = "trace." + ZXC.util.encodeQueryContext(this.code);
		return url.replace(/^(.+?)\/*(?:((?:[^\/.]+\.[^\/.]*\.)+)htm)?$/,"$1/$2" + segment);
	},
	log : function(lvl, msg) {
		if (ZXC.util.log) {
			ZXC.util.log(lvl, this.classname, msg);
		}
	}
},
statics: {
	filter : [
		/^http:\/\/www\.ZXC\.com(\/.*)?$/,
		/^http:\/\/www\.ZXC\.com(?!\/static)(\/.*)?$/,
		/^http:\/\/www\.ZXC\.com(?!\/forum)(\/.*)?$/]
}
});

var pageTracker;
