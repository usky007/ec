/**
 * Panel Implement
 *
 */

ZXC.Require("ZXC.util");
ZXC.Require("ZXC.Resource");

ZXC.Namespace("ZXC.UI");

ZXC.UI.Export("Panel", "Dock");

/**
 * Generic ui panel implements.
 *
 * This panel implements support dock and automatic resize.
 *
 * Supportted options including:
 * element: HTML element represents the panel, create a new element
 *			if not specified;
 * cssClass:Class attribute of element.
 * innerElement:
 			HTML element represents the panel viewport, reuse element if not specified;
 * innerClass:
 			Class attribute of innerElement, new viewport element will be created
 			if innerClass specified and not equals to cssClass;
 * parent:	Parent "Panel" object;
 * dock:	Dock settings, default to be dock to top, with lock none;
 */
ZXC.UI.Panel = ZXC.Class({
name: "ZXC.UI.Panel",
construct:
	function(name, options) {
		if (!name)
			return;

		this.disable = false;
		this.client;	// Outline element that decides the size of panel.
		this.padding = {top:0, right:0, bottom:0, left:0};
		this.viewport;	// Inner element in which layout displays.
		this.layout;	// Acture element that contains contents.
		this.dock;		// Dock setting the panel applies.
		this.parent;	// Parent panel
		this.scrollers = {length:0};

		this.getName = function() { return name; };

		this.initialize(options);
		if (this.parent)
			this.parent.addPanel(this);
	},
methods: {
	initialize: function(options) {
		if (!options)
			options = {};

		this.parent = options.parent ? options.parent : null;

		// client settings
		if (!options.element)
		{
			options.element = document.createElement("div");
			if (!this.parent)
				$(options.element).appendTo(document.body);
		}
		this.client = options.element;
		var jClient = $(this.client);
		if (options.cssClass)
			jClient.attr("class", options.cssClass);
		if (!jClient.css("position"))
			jClient.css("position", "relative");

		// dock settings
		this.dock = options.dock ? options.dock : new ZXC.UI.Dock();

		// layout settings
		if (options.innerElement)
		{
			this.viewport = this.layout = options.innerElement;
			if (options.innerClass)
				this.layout.className = options.innerClass;
			if (!this.layout.parentNode || this.layout.parentNode != this.client)
				$(this.layout).appendTo(this.client);
			var position = $(this.layout).position();
			with (this.padding) {
				top = position.top;
				left = position.left;
				bottom = this.client.clientHeight - top - $(this.layout).outerHeight(true);
				right = this.client.clientWidth - left - $(this.layout).outerWidth(true);
			}
			$(this.layout).css("position", "relative");
		}
		else if (options.innerClass && options.innerClass != jClient.attr("class"))
		{
			// create a layout element;
			this.viewport = this.layout = document.createElement("div");
			$(this.layout).css("position", "relative").appendTo(this.client);
		}
		else
			this.viewport = this.layout = this.client;

		var obj = this;
		this.onResize(function(event){
			obj.resizeHandler.apply(obj, arguments);
		});
		this.onViewportResize(function(event){
			obj.viewportResizeHandler.apply(obj, arguments);
		});
		this.onLayoutResize(function(event){
			obj.layoutResizeHandler.apply(obj, arguments);
		});
	},
	enable: function(enable, effect, callback) {
		var obj = this;
		var disable = enable === false;
		var resize = true //this.disable != disable;
		this.disable = disable;
		if (resize) {
			if (effect) {
				var direction = ["up","down","left","right"][this.dock.side];
				var wapper = this._prepareWrapper();
				$(wapper)[this.disable ? "hide" : "show"](
					effect,  { direction: direction}, 1000, function() {
						obj._removeWrapper(wapper);
						$(obj.client).trigger("panelResize");
						if (callback) callback();
					});
			}
			else
				$(this.client).trigger("panelResize");
		}
	},
	available: function() {
		return !this.disable;
	},
	setHeight: function(height) {
		ZXC.util.height(this.client, height);
	},
	setWidth: function(width) {
		ZXC.util.width(this.client, width);
	},
	getHeight: function() {
		return ZXC.util.height(this.client);
	},
	getWidth: function() {
		return ZXC.util.width(this.client);
	},
	getScrollWidth: function() {
		if (this.layout === this.viewport)
			return 0;
		return ZXC.util.width(this.layout) - $(this.viewport).width();
	},
	getScrollHeight: function() {
		if (this.layout === this.viewport)
			return 0;
		return ZXC.util.height(this.layout) - $(this.viewport).height();
	},
	addScroller: function(type, vertical, options) {
		if (!type)
			return null;
		// ensure viewport and layout separated;
		this._generateViewport();

		this.scrollers[this.scrollers.length++] = new type(this, vertical, options);
		if (!this.parent || (this.parent.available() && !this.parent.suspendLayout)) {
			$(this.viewport).trigger("panelResize");
		}
		return this.scrollers[this.scrollers.length - 1];
	},
	scrollTo: function(pos, vertical) {
		var max = vertical ? this.getScrollHeight() : this.getScrollWidth();

		pos = pos > max ? max : pos;
		pos = pos < 0 ? 0 : pos;		// place here to avoid max itself < 0;
		$(this.layout).css(vertical ? "top" : "left", -pos)
			.trigger("scroll", [pos, vertical]);
		return pos;
	},
	scrollToBegin: function(vertical) {
		this.scrollTo(0, vertical);
	},
	scrollToEnd: function(vertical) {
		var max = vertical ? this.getScrollHeight() : this.getScrollWidth();
		this.scrollTo(max, vertical);
	},
	addPanel: function(child) {},
	setToggler : function(options) {
		if (!this.parent) return;
		var obj = this;
		// intialize toggler;
		var toggler = options.element || function(options){
				var toggler = document.createElement("div");
				if (options.cssClass)
					$(toggler).attr("class", options.cssClass);
				return toggler;
			}();
		this.toggler = new ZXC.UI.Panel(this.getName() + "_toggler",
			{element:toggler, parent:this.parent, dock:this.dock});
		// set status
		var applyStatus = function(enable) {
			if (options.closeClass)
				$(toggler)[(!enable ? "add" : "remove") + "Class"](options.closeClass);
		}
		applyStatus(!this.disable);
		// register event
		$(toggler).click(function(ev) {
			ev.preventDefault();
			var status = obj.disable;
			obj.enable(status, options.effect);
			applyStatus(status);
		});
	},
	// events
	onScroll: function(func) {
		$(this.layout).bind("scroll",func);
		if (!this.eventHandlers)
			this.eventHandlers = new Array();
		this.eventHandlers.push(["layout", ["scroll", func]]);
	},
	onResize: function(func) {
		$(this.client).bind("panelResize", func);
		if (!this.eventHandlers)
			this.eventHandlers = new Array();
		this.eventHandlers.push(["client", ["panelResize", func]]);
	},
	// privates
	onViewportResize: function(func) {
		$(this.viewport).bind("panelResize", func);
		if (!this.eventHandlers)
			this.eventHandlers = new Array();
		this.eventHandlers.push(["viewport", ["panelResize", func]]);
	},
	onLayoutResize: function(func) {
		$(this.layout).bind("panelResize", func);
		if (!this.eventHandlers)
			this.eventHandlers = new Array();
		this.eventHandlers.push(["layout", ["panelResize", func]]);
	},
	resizeHandler: function(event) {
		event.stopPropagation();
		if (this.viewport !== this.client) {
			ZXC.util.width(this.viewport, this.client.clientWidth - this.padding.left - this.padding.right);
			ZXC.util.height(this.viewport, this.client.clientHeight - this.padding.top - this.padding.bottom);
			$(this.viewport).trigger("panelResize");
		}
	},
	viewportResizeHandler: function(event, size) {
		event.stopPropagation();
		for (var i = 0; i < this.scrollers.length; i++)
			size = this.scrollers[i].validateViewport(size);
		if (this.layout !== this.viewport) {
			$(this.layout).trigger("panelResize");
		}
	},
	layoutResizeHandler: function(event) {
		event.stopPropagation();
		if (this.layout !== this.viewport) {
			// reset layout
			$(this.layout).css("width", "auto").css("height", "auto");
			// set layout to fit viewport
			if (ZXC.util.width(this.layout) < $(this.viewport).width())
				ZXC.util.width(this.layout, $(this.viewport).width());
			if (ZXC.util.height(this.layout) < $(this.viewport).height())
				ZXC.util.height(this.layout, $(this.viewport).height());
			for (var i = 0; i < this.scrollers.length; i++)
				this.scrollers[i].layoutResizeHandler();
		}
	},
	// privates
	_generateViewport: function() {
		if (this.layout !== this.viewport)
			return;

		// generate layout block
//		this.viewport = document.createElement("div");
//		// move event handler
//		if (this.eventHandlers) {
//			for (var i = 0; i < this.eventHandlers.length; i++) {
//				if (this.eventHandlers[i][0] == "viewport" ) {
//					jQuery.prototype.unbind.apply($(this.layout), this.eventHandlers[i][1]);
//					jQuery.prototype.bind.apply($(this.viewport), this.eventHandlers[i][1]);
//				}
//			}
//		}
//		$(this.viewport).css("position", "relative").css("overflow", "hidden")
//			.width(ZXC.util.width(this.layout))
//			.height(ZXC.util.height(this.layout));
//		$(this.viewport).insertBefore(this.layout);
//		$(this.layout).css("position", "absolute").appendTo(this.viewport);
		this.layout = document.createElement("div");
		// move event handler
		if (this.eventHandlers) {
			for (var i = 0; i < this.eventHandlers.length; i++) {
				if (this.eventHandlers[i][0] == "layout" ) {
					jQuery.prototype.unbind.apply($(this.viewport), this.eventHandlers[i][1]);
					jQuery.prototype.bind.apply($(this.layout), this.eventHandlers[i][1]);
				}
			}
		}
		// move padding style
		$(this.viewport).width($(this.viewport).innerWidth());
		$(this.viewport).height($(this.viewport).innerHeight());
		var styles = ["left", "right", "top", "bottom"];
		for (var i = 0; i < styles.length; i++) {
			$(this.layout).css("padding-"+styles[i], $(this.viewport).css("padding-"+styles[i]));
			$(this.viewport).css("padding-"+styles[i], 0);
		}
		// move content
		$(this.viewport).css("position", "relative").css("overflow", "hidden")
			.children().appendTo(this.layout);
		$(this.layout).css("position", "absolute")
			.width(ZXC.util.width(this.viewport))
			.height(ZXC.util.height(this.viewport)).appendTo(this.viewport);
	},
	_prepareWrapper: function() {
		if (!this.toggler)
			return this.client;

		var wrapper = $.effects.createWrapper($(this.client));
		var dimension = (this.dock.side / 2) ? "Width" : "Height";
		var styleDirection = ["Bottom","Top","Right","Left"][this.dock.side];
		wrapper.css("padding" + styleDirection, this.toggler["get" + dimension]());
		$(this.toggler.client).appendTo(wrapper)
			.css("top", styleDirection == "Bottom" ? wrapper.height() : 0)
			.css("left", styleDirection == "Right" ? wrapper.width() : 0);
		$(this.client).css("display","block");
		return wrapper[0];
	},
	_removeWrapper: function(wrapper) {
		if (this.toggler) {
			$(this.toggler.client).insertAfter(wrapper);
			$.effects.removeWrapper($(this.client));
		}
	}
},
statics: {
	// dragMonitor;
	dragMonitor: {
		debugLog:false,
		dragging:false,
		dragClass:"",
		x:0, y:0,
		initialize: function(log) {
			var obj = this;
			if (log)
			{
				this.debugLog = $(document.createElement("div"));
				this.debugLog.css("position", "absolute")
					.css("background-color", "white")
					.css("border", "1px black solid")
					.css("top", 0).css("left", 0)
					.width(500).height(20).appendTo(document.body);
			}

			$(document).bind("dragstart", function(event) {
				obj.x = event.clientX;
				obj.y = event.clientY;
				obj.dragging = true;
				obj.log();
			}).bind("drag", function(event) {
				obj.x = event.clientX;
				obj.y = event.clientY;
				obj.log();
			}).bind("dragstop", function(event) {
				obj.x = event.clientX;
				obj.y = event.clientY;
				obj.dragging = false;
				obj.log();
			})
		},
		log: function() {
			if (!this.debugLog)
				return;
			var html = "";
			for (key in this)
				if (typeof this[key] != "function" && key != "debugLog")
					html += " " + key + ":" + this[key];
			this.debugLog.html(html);
		}
	}
}
});

ZXC.UI.Dock = function(side, lock, offsetX, offsetY)
{
	// Side to Dock
	this.side = side ? side : ZXC.UI.Dock.DOCK_TOP;
	// Indicate which dimension to be holding on parent resizing.
	this.lock = lock ? lock : ZXC.UI.Dock.LOCK_NONE;
	// Offset on x axis, the base value of positive offset is X of parent's
	// topleft corner, while negative offset is X of parents's topright corner.
	this.offsetX = offsetX ? offsetX : 0;
	// Offset on y axis, the base value of positive offset is Y of parent's
	// topleft corner, while negative offset is Y of parents's leftbottom corner.
	this.offsetY = offsetY ? offsetY : 0;
}
ZXC.UI.Dock.prototype.checkLock = function(lock) {
	return this.lock & lock;
}
ZXC.UI.Dock.LOCK_NONE = 0;
ZXC.UI.Dock.LOCK_WIDTH = 1;
ZXC.UI.Dock.LOCK_HEIGHT = 2;
ZXC.UI.Dock.LOCK_BOTH = 3;
ZXC.UI.Dock.DOCK_TOP = 0;
ZXC.UI.Dock.DOCK_BOTTOM = 1;
ZXC.UI.Dock.DOCK_LEFT = 2;
ZXC.UI.Dock.DOCK_RIGHT = 3;
