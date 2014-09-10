ZXC.Require("ZXC.Widget.Suggestion");

ZXC.Namespace("ZXC.App");

ZXC.App.Export("LargeSetSuggestion");

ZXC.Class({
name: "ZXC.App.LargeSetSuggestion",
extend: ZXC.Widget.Suggestion,
construct: 
	function(target, settings) {
		this.pager = {
			pageChanged: ZXC.Event(),
			key: undefined,
			page: 1,
			pageToLoad: 1,
			total: 1,
			goToPage: function(page) {
				if (page.preventDefault) {
					page.preventDefault();
					var target = jQuery(page.currentTarget);
					page = parseInt(target.attr("page") || target.text());
				}
				if (page <= 0 || page > this.total || page == this.page) return;
				this.pageToLoad = page;
				this.pageChanged();
			},
			first: function(event) {
				if (event) event.preventDefault();
				this.goToPage(1);
			},
			prev: function(event) {
				if (event) event.preventDefault();
				this.goToPage(this.page - 1);
			},
			next: function(event) {
				if (event) event.preventDefault();
				this.goToPage(this.page + 1);
			},
			last: function(event) {
				if (event) event.preventDefault();
				this.goToPage(this.total);
			}
		};
		this.pager.pageChanged.add(ZXC.Callback(this, "changeHandler"));
		
		this.superclass(target, settings);
	},
methods: {
	initialize: function(settings) {
		this.settings['pageKey'] = "p";
		this.settings['pagerClass'] = 'widget_suggestion_pager';
		this.settings['pagerLimit'] = 5;
		
		settings.footerFactory = ZXC.Callback(this, "getPager");
		this.superclass.prototype.initialize.apply(this, [settings]);
		this.options.page = 1;
		this.proxyOnMatchHandler();
	},
	// show options panel
	// options: filtered options array
	// pattern: uncompleted words that will be bold in suggest options
	show: function(options, pattern)
	{
		this.pager.key = pattern;
		this.pager.page = this.pager.pageToLoad;
		this.superclass.prototype.show.apply(this, arguments);
	},
	// get suggestion options by extracted incomplete word
	// prefix:incomplete word
	extractSubArray: function(pattern, page)
	{
		if (pattern.length == 0) {
			return new Array();
		}
		page = page || 1;
		
		var result = this.library.match(this.getHistoryKey(pattern, page), this.onMatch, this.settings.validate);
		if (this.settings.validate)
			this.onValidateComplete(this, result.entry);
		return result;
	},
	// textbox value change handler
	changeHandler: function() 
	{
		var prefix = this.extractPrefix();
		this.options.key = prefix;
		ZXC.util.log("debug", this.classname, "prefix:[" + prefix + "]");
		if (prefix.length == 0 && this.settings.guess) {
			this.onGuess(this);
			return;
		}

		var sub = this.extractSubArray(prefix, (this.pager.pageToLoad != this.pager.page) ? this.pager.pageToLoad : this.pager.page);
		if ((sub.length == 1 && sub.entry) || (sub.length == 0 && prefix != this.pager.key))
			this.hide();
		else if (sub.length > 0) {
			this.show(sub, prefix);
		}

		// no ajax suggest if prefix too short and ascii only.
		if (prefix.length == 0 || prefix.match(/^[a-zA-Z0-9 -_']{1,2}$/))
			return;
		else if ((sub.bingo && this.settings.suggestOnHit) ||
			(!sub.bingo && this.settings.suggestOnFail)) {
			this.suggest(prefix);
		}
		// show default only if suggest options confirmed,
		// which is, no suggest action is required.
		else if (sub.length == 0 && this.settings.defaultOption) {
			this.showDefault(prefix);
		}
	},
	resetLibrary: function(source, force)
	{
		force = (force !== false) ? true : false;
		this.library = this.Suggestion.Library.getLibrary(source);
		this.library.localMatch = false;
		if (this.settings.suggestOnLoad && (force || !this.library.loaded)) {
			var pattern = typeof this.settings.suggestOnLoad != "string" ? "" :
				this.settings.suggestOnLoad;
			this.suggest(pattern);
			this.library.loaded = true;
		}
	},
	getRequestOptions: function(pattern) {
		var ajaxOptions = this.superclass.prototype.getRequestOptions.apply(this, [pattern]);
		
		var page = this.pager.pageToLoad;
		if (pattern !== this.pager.key) {
			page = this.pager.pageToLoad = 1;
		}
		if (page > 1) {
			!ajaxOptions.data && (ajaxOptions.data = {});
			ajaxOptions.data[this.settings.pageKey] = page;
		}
		return ajaxOptions;
	},
	defaultOnGuessHandler: function(sender)
	{
		page = (this.pager.pageToLoad != this.pager.page) ? this.pager.pageToLoad : this.pager.page;
		
		var sub = this.library.match(this.getHistoryKey("", page));
		if (sub.length == 0 && this.pager.key != "")
			this.hide();
		else if (sub.length > 0)
			this.show(sub, "");

		// no ajax suggest if prefix too short and ascii only.
		if ((sub.bingo && this.settings.suggestOnHit) ||
			(!sub.bingo && this.settings.suggestOnFail)) {
			this.suggest("");
		}
		// show default only if suggest options confirmed,
		// which is, no suggest action is required.
		else if (sub.length == 0 && this.settings.defaultOption) {
			this.showDefault("");
		}
	},
	proxyOnMatchHandler: function()
	{
		var onMatch = this.onMatch;
		this.onMatch = function(sender, entry, pattern) {
			return onMatch(sender, entry, pattern.replace(/@[0-9]+$/, ""));
		}
		return this.onMatch;
	},
	successHandler: function(pattern, result){
		if (!result.success) {
			throw new Error(result.message);
		}
		var arr = new Array();
		for (var i = 0; i < result.data.length; i++)
			arr.push(new this.Suggestion.Entry(
				result.data[i][this.settings.keyField], result.data[i]));
		
		this.pager.total = result.total;
		this.library.expand(arr, this.getHistoryKey(pattern, result.page));
		// filter with client side match function and trigger validation if set.
		if (pattern.length > 0)
			arr = this.extractSubArray(pattern, result.page);
		else if (!this.settings.guess)
			arr = new Array();	// set to empty if guess setting disabled.
		// arr not empty? request key not change? focus holds?
		// show suggestion panel.
		if (this.options.key == pattern && this.options.instance === this && this.pager.pageToLoad == result.page) {
			if (arr.length > 0)
				this.show(arr, pattern);
			else if (pattern.length > 0 && this.settings.defaultOption)
				this.showDefault(pattern);
		}
	},
	getHistoryKey: function(pattern, page) {
		return pattern + "@" + page;
	},
	getPager: function() {
		if (this.pager.total == 1) {
			return this.pager.view ? this.pager.view.hide() : "";
		}
	
		var template = this.pager.template;
		if (!template) {
			template = {};
			template.first = jQuery("<a class='pager_first image_button' href='#first'>　</a>").click(ZXC.Callback(this.pager, "first"));
			template.prev = jQuery("<a class='pager_prev image_button' href='#prev'>　</a>").click(ZXC.Callback(this.pager, "prev"));
			template.page = jQuery("<a class='pager_page' href='#page'></a>").click(ZXC.Callback(this.pager, "goToPage"));
			template.current = jQuery("<span class='pager_current'></span>");
			template.next = jQuery("<a class='pager_next image_button' href='#next'>　</a>").click(ZXC.Callback(this.pager, "next"));
			template.last = jQuery("<a class='pager_last image_button' href='#last'>　</a>").click(ZXC.Callback(this.pager, "last"));
			this.pager.template = template;
		}
		template.first.css("visibility", this.pager.page === 1 ? "hidden" : "visible");
		template.prev.css("visibility", this.pager.page === 1 ? "hidden" : "visible");
		template.next.css("visibility", this.pager.page === this.pager.total ? "hidden" : "visible");
		template.last.css("visibility", this.pager.page === this.pager.total ? "hidden" : "visible");
		
		if (!this.pager.view) {
			this.pager.view = jQuery(document.createElement('div')).attr("class", this.settings.pagerClass);
		}
		for (var key in this.pager.template) {
			this.pager.template[key].detach();
		}
		this.pager.view.empty();
		this.pager.view.append(template.first).append(template.prev);
		var limit = this.settings.pagerLimit || 5;
		var start = Math.min(this.pager.page - Math.floor(limit / 2), this.pager.total - limit + 1);
		start < 1 && (start = 1);
		var end = start + limit - 1;
		end > this.pager.total && (end = this.pager.total);
		for (var i = start; i < this.pager.page; i++) {
			template.page.clone(true).text(i).appendTo(this.pager.view);
		}
		template.current.text(this.pager.page).appendTo(this.pager.view);
		for (var i = this.pager.page + 1; i <= end; i++) {
			template.page.clone(true).text(i).appendTo(this.pager.view);
		}
		this.pager.view.append(template.next).append(template.last);
		
		return this.pager.view.show();
	}
}
});

// jQuery Plugin Support
jQuery.fn.largeSetSuggestion = function(settings) {
	if (!settings && this.length > 0) {
		return this[0].widget_suggestion;
	}
	if (settings.source === undefined)
		return this;

	return this.each(
		function() {
			if (this.tagName.toLowerCase() != 'textarea' &&
				this.tagName.toLowerCase() != 'input' &&
				this.getAttribute('type') != 'text')
				return;

			var copy = {};
			for(var property in settings)
				copy[property] = settings[property];

			this.widget_suggestion = new ZXC.App.LargeSetSuggestion(this, copy);
		}
	);
};
