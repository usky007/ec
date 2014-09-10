ZXC.Namespace("ZXC.util");

ZXC.util.Export("Logger");

ZXC.util.Logger = ZXC.Class({
name: "ZXC.util.Logger",
construct:
	function() {
		this.container = null;
		this.titlebar = null;
		this.logpanel = null;
		this.enabled = false;
		this.paused = false;
		this.initialized = false;
		this.types = ['all'];
		this.classes = ['all'];
		this.filter = {type:'all', cls:'all'};
	},
methods: {
	initialize: function(options) {
		this.enabled = options.enable || false;
		var cssClass = options.cssClass || "logger";

		if (!this.enabled)
			return false;

		this.container = jQuery(options.container || this.Logger.getDefaultContainer(cssClass));
		this.container.attr("id", this.Logger.LOGGER_CONTAINER_ID)
			.addClass(cssClass).height("auto");
		if (!this.container.parent) {
			this.container.appendTo(this.Logger.BODY_CONTAINER());
		}

		this.titlebar = jQuery(document.createElement("div"));
		this.titlebar.attr("id", this.Logger.LOGGER_TITLEBAR_ID)
			.addClass(cssClass + "_titlebar")
			.html("<span><a id='loggerToggleDetail' href='#toggleDetail' style='display:none;'>Packup</a></span><span><a id='pauseLoggerEntry' href='#pauseLogger' style='display:none;'>Pause</a></span><span><a id='switchDock' href='#switchDock' style='display:none;'>Switch</a></span><span>Cls:<select id='loggerClassFilter'><option value='all'>all</option></select></span><span>Type:<select id='loggerTypeFilter'><option value='all'>all</option></select></span><b>Logger</b><br style='clear:both'/>")
			.appendTo(this.container);

		var obj = this;

		this.container.bind("dockresize", function() {
			obj.logpanel.height(obj.container.height() - obj.titlebar.height());
			obj.container.height("auto");
		});

		jQuery("#loggerToggleDetail", this.titlebar).click(function(evt){
			evt.preventDefault();
			if (obj.logpanel.css("display") == "none") {
				obj.logpanel.show();
				jQuery(this).html("Packup");
			} else {
				obj.logpanel.hide();
				jQuery(this).html("Expand");
			}
			obj.container.resize();
		}).show();

		jQuery("#pauseLoggerEntry", this.titlebar).click(function(evt){
			evt.preventDefault();
			if (!obj.paused) {
				obj.setPause(true);
				jQuery(this).html("Start");
			} else {
				obj.setPause(false);
				jQuery(this).html("Pause");
			}
		}).show();

		jQuery("#loggerTypeFilter", this.titlebar).change(function(){
			var filter = jQuery(this).val();
			obj.filter.type = $.trim(filter);
			obj.show();
		});

		jQuery("#loggerClassFilter", this.titlebar).change(function(){
			var filter = jQuery(this).val();
			obj.filter.cls = $.trim(filter);
			obj.show();
		});

		//this.draggable();

		this.logpanel = jQuery(document.createElement("div"));
		this.logpanel.attr("id", this.Logger.LOGGER_PANEL_ID).addClass(cssClass + "_panel").appendTo(this.container);

		if (this.container[0].dockable && this.container[0].dockable()) {
			jQuery("#switchDock", this.titlebar).click(function(evt){
				evt.preventDefault();
				if (obj.container[0].dock.side != "top") {
					obj.container[0].dockToTop();
				}
				else {
					obj.container[0].dockToLeft();
				}
			}).show();
			this.container[0].dockToTop();
		}
		this.initialized = true;
	},
	draggable: function(){
		if (typeof jQuery.ui != "undefined" && typeof jQuery.ui.draggable != "undefined")
		{
			this.setDraggable();
		}
		else
		{
			ZXC.util.loadScript(js_context.base_url + 'res/js/lib/jui_draggable.js', function(){
				ZXC.util.Logger.getLogger().setDraggable();
			});
		}
	},
	setDraggable: function(){
		var _this = this;
		jQuery("#" + ZXC.util.Logger.LOGGER_TITLEBAR_ID).css("cursor","move");
		jQuery("#" + ZXC.util.Logger.LOGGER_CONTAINER_ID).draggable({
			appendTo: jQuery(_this.Logger.BODY_CONTAINER()),
			handle: jQuery("#" + ZXC.util.Logger.LOGGER_TITLEBAR_ID),
			opacity: 0.7,
			tolerance: 'pointer',
			revert: false,
			start: function (e, ui) {
				jQuery(_this.Logger.BODY_CONTAINER()).trigger("mousedown");
			},
			drag: function (e, ui) {
				jQuery(_this.Logger.BODY_CONTAINER()).trigger("mousemove", [e]);
			},
			stop: function (e, ui) {
				jQuery(_this.Logger.BODY_CONTAINER()).trigger("mouseup");
			}
		});
	},
	inArray: function (val,arr)	{
		for (var i = 0; i < arr.length; i++)
		{
			if (arr[i] == val) {
				return true;
			}
		}
		return false;
	},
	addOption: function (sel, val) {
		var oOption = document.createElement("OPTION");
		oOption.text = val;
		oOption.value = val;
		sel.options.add(oOption);
	},
	log: function(type, cls, msg) {
		if (!this.enabled || this.paused || !this.container)
			return;

		type = type.toUpperCase();

		if (!this.inArray(type, this.types))
		{
			this.types.push(type);
			this.addOption(jQuery("#loggerTypeFilter")[0], type);
		}

		if (!this.inArray(cls, this.classes))
		{
			this.classes.push(cls);
			this.addOption(jQuery("#loggerClassFilter")[0], cls);
		}

		var logBlock = jQuery(document.createElement("div"));
		var time = new Date();
		var html = time.toLocaleTimeString() + " " + time.getMilliseconds() +
			" <span class='logtype'>" + type + "</span> <span class='classname'>" + cls + "</span>: "
		var msgblock = jQuery(document.createElement("span")).html(msg);
		logBlock.html(html).append(msgblock).addClass(type).attr("classname", cls).prependTo(this.logpanel);
		if ((this.filter.type != "all" && this.filter.type != type) || (this.filter.cls != "all" && this.filter.cls != cls))
			logBlock.hide();
	},
	enable: function() {
		this.setEnable(true);
	},
	disable:function() {
		this.setEnable(false);
	},
	setEnable: function(enable) {
		this.enabled = enable !== false;
	},
	pause: function() {
		this.setPause(true);
	},
	resume: function() {
		this.setPause(false);
	},
	setPause: function(pause) {
		this.paused = pause !== false;
	},
	show: function() {
		var filter = '';

		if (this.filter.type != "all")
			filter += "." + this.filter.type;
		if (this.filter.cls != "all")
			filter += "[classname='" + this.filter.cls + "']";

		if (filter == "")
		{
			jQuery("div", this.logpanel).show();
		}
		else
		{
			jQuery("div", this.logpanel).hide();
			jQuery(filter, this.logpanel).show();
		}
	}
},
statics: {
	BODY_CONTAINER: function() {
		return (Page && Page.container) || document.body;
	},
	MAIN_CONTAINER: function() {
		return (Page && Page.main) || jQuery(".container")[0];
	},
	LOGGER_CONTAINER_ID: "ZXCLoggerContainer",
	LOGGER_TITLEBAR_ID: "ZXCLoggerTitlebar",
	LOGGER_PANEL_ID: "ZXCLoggerPanel",
	getLogger: function() {
		if (!this._log)
			this._log = new this();
		return this._log;
	},
	getDefaultContainer: function(cssClass) {
		var cls = this;
		var container = document.createElement("div");
		container.dock = document.createElement("div");
		$(container).appendTo(container.dock)
			.addClass(cssClass + "_container")
			.resize(function(event) {
				switch (this.dock.side) {
					case "left":
					case "right":
						$(this.dock).width($(this).width()).height("auto");
						break;
					case "top":
					case "bottom":
						$(this.dock).height($(this).height()).width("100%");
						break;
					default:
						$(container.dock).height($(this).height()).width($(this).width());
				}
			});
		$(window).bind("windowResize", function(event, width, height) {
			switch (container.dock.side) {
				case "left":
				case "right":
					$(container).height(height).trigger("dockresize");
					break;
				case "top":
				case "bottom":
					$(container).width(width);
					break;
			}
		});
		container.dockable = function () {
			return true;
		};
		container.dockToTop = function() {
			this.dock.side = "top";
			$(this.dock).prependTo(cls.BODY_CONTAINER()).css({
				"position":"static",
				"float":"none",
				"width":"100%",
				"top":0,"left":0});
			$(this).css({
				"width":"100%",
				"height":"180px",
				"position":"fixed"})
				.trigger("dockresize")
				.resize();
		};
		container.dockToLeft = function() {
			this.dock.side = "left";
			$(this.dock).prependTo(cls.BODY_CONTAINER()).css({
				"position":"static",
				"float":"left",
				"top":0,"left":0})
				.height(ZXC.util.getScrollHeight());
			$(this).width((ZXC.util.getInnerWidth() - $(cls.MAIN_CONTAINER()).outerWidth()) / 2)
				.height(ZXC.util.getInnerHeight())
				.css("position", "fixed")
				.trigger("dockresize")
				.resize();
		};
		return container;
	}
}
});
