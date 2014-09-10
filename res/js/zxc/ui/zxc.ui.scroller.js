/**
 * Scroller Implement
 *
 */
ZXC.Require("ZXC.UI.Panel");

ZXC.Namespace("ZXC.UI");

ZXC.UI.Export("BarScroller", "SliderScroller");

/**
 * Bar scroller implements.
 *
 * Supportted options including:
 * upleftElement: 	HTML element that will be displayed as up/left scroll bar
 * downrightElement:HTML element that will be displayed as down/right scroll bar
 * upleftClass	   :[normalClass, [hoverClass, [activeClass, [hideClass]]]]
 * downrightClass  :[normalClass, [hoverClass, [activeClass, [hideClass]]]]
 * scrollStep      :Minimum scroll offset.
 * step            :Offset per click
 */
ZXC.Class({
name: "ZXC.UI.BarScroller",
construct:
	function(panel, vertical, options) {
		if (!panel)
			return;
		this.panel = panel;
		this.vertical = vertical;
		this.scrollBar = [null, null];
		this.options = {
			cssClass : ["", ""],
			step : 72,
			scrollStep : 18,
			scrollDelay : 500,
			scrollCycle : 50,
			scrollOnMouseOver : false,
			onScroll : null
		};

		// private
		this.lastClassIndex = [0, 0];
		this.scrollBarStatus = [false, false];
		this.lastPosition = 0;
		this.scrollTimerId = 0;

		this.initialize(options);
	},
methods: {
	initialize: function(options) {
		options = options || {};

		var objClass = ZXC.UI.BarScroller;
		var obj = this;

		// scroll bar and its style initialize
		var type = ["upleft", "downright"];
		for (var idx = 0; idx < 2; idx++)
		{
			this.scrollBar[idx] = options[type[idx] + "Element"];
			// bar class
			this.options.cssClass[idx] = options[type[idx] + "Class"] ?
				options[type[idx] + "Class"] : (
				(!this.scrollBar[idx] || !$(this.scrollBar[idx]).attr("class")) ? "" :
				$(this.scrollBar[idx]).attr("class"));
			// construct class array
			if (this.options.cssClass[idx].constructor == String)
				this.options.cssClass[idx] = [this.options.cssClass[idx]];
			// set default class
			for (var i = objClass.HOVER_CLASS_INDEX; i < objClass.HIDE_CLASS_INDEX; i++)
				if (!this.options.cssClass[idx][i])
					this.options.cssClass[idx][i] = this.options.cssClass[idx][i - 1];
			this.lastClassIndex[idx] = objClass.NORMAL_CLASS_INDEX;
			// create bar if not exists
			if (!this.scrollBar[idx]) {
				this.scrollBar[idx] = document.createElement("div");
				$(this.scrollBar[idx]).attr("class",
					this.options.cssClass[idx][objClass.NORMAL_CLASS_INDEX]);
			} else if (!this.scrollBar[idx].parentNode ||
				this.scrollBar[idx].parentNode != this.panel.client) {
				$(this.scrollBar[idx]).appendTo(this.panel.client);
			}
			$(this.scrollBar[idx]).css("position", "absolute")
				.bind("mouseover", {direction:idx}, ZXC.Callback(this, "barMouseOverHandler"))
				.bind("mouseout", {direction:idx}, ZXC.Callback(this, "barMouseOutHandler"))
//				}).Droppable({
//					accept: function() {return ZXC.UI.Panel.dragMonitor.dragClass;},
//					onHover: function() {$(this).trigger("mouseover");},
//					onOut: function() {$(this).trigger("mouseout");}
				.bind("mousedown", {direction:idx}, ZXC.Callback(this, "barMouseDownHandler"))
				.bind("mouseup", {direction:idx}, ZXC.Callback(this, "barMouseUpHandler"))
				.bind("click", function(event) { event.preventDefault(); });
		}

		with (this.options) {
			step = options.step || step;
			scrollStep = options.scrollStep || scrollStep;
			scrollDelay = options.scrollDelay || scrollDelay;
			scrollCycle = options.scrollCycle || scrollCycle;
			scrollOnMouseOver = options.scrollOnMouseOver || scrollOnMouseOver;
			onScroll = options.onScroll || onScroll;
		}

		// register onScroll handler
		this.panel.onScroll(ZXC.Callback(this, "panelScrollHandler"));
	},
	enableScrollOnMouseOver: function() {
		this.options.scrollOnMouseOver = true;
	},
	disableScrollOnMouseOver: function() {
		this.options.scrollOnMouseOver = false;
	},
	startScroll: function(forward, limit) {
		if (this.scrollTimerId)
			return;

		var obj = this;
		var remains = limit;
		var start = -$(this.panel.layout).position()[this.vertical ? "top" : "left"];
		var pos = obj.scroll(forward, remains, start);
		if (pos == start ||
			(limit && 0 >= (remains -= obj.options.scrollStep)))
			return; //we are done;

		this.scrollTimerId = window.setInterval(function() {
			start = pos;
			pos = obj.scroll(forward, remains, start);
			if (pos == start ||
				(limit && 0 >= (remains -= obj.options.scrollStep)))
				obj.stopScroll();
		}, this.options.scrollCycle);
	},
	/**
	* scroll a relative distance in a direction from a position
	* @param forward Direction. true to scroll ahead.
	* @param limit The max relative distance could be scrolled. However, actual scroll distance
	*        is determined by "scrollStep" option, omittable.
	* @param start The start position, if omitted, current position will be calculated.
    * @return Current position if "start" is given, or actual offset will be returned.
	*/
	scroll: function(forward, limit, start) {
		var pos = (start != undefined) ? start : -$(this.panel.layout).position()[this.vertical ? "top" : "left"];
		var offset = Math.min(limit || this.options.scrollStep, this.options.scrollStep);
		pos = pos + (forward ? offset : -offset);
		var diff = pos - this.panel.scrollTo(pos, this.vertical);
		if (this.options.onScroll) {
			this.options.onScroll(forward);
		}
		return (start != undefined) ? pos : diff;
	},
	stopScroll: function() {
		if (!this.scrollTimerId)
			return;

		window.clearInterval(this.scrollTimerId);
		this.scrollTimerId = 0;
	},
	setScrollBarClass: function(scrollIdx, classIndex) {
		this.lastClassIndex[scrollIdx] = classIndex;
		$(this.scrollBar[scrollIdx])
			.attr("class", this.options.cssClass[scrollIdx][classIndex]);
	},
	toggleScrollBar: function(scrollIdx, show) {
		this.scrollBarStatus[scrollIdx] = show;
		if (this.options.cssClass[scrollIdx][this.BarScroller.HIDE_CLASS_INDEX])
		{
			if (show)
				$(this.scrollBar[scrollIdx]).attr("class",
					this.options.cssClass[scrollIdx][this.lastClassIndex[scrollIdx]]);
			else
				$(this.scrollBar[scrollIdx]).attr("class",
					this.options.cssClass[scrollIdx][this.BarScroller.HIDE_CLASS_INDEX]);
		}
		else if (show)
			$(this.scrollBar[scrollIdx]).css("visibility", "");
		else
			$(this.scrollBar[scrollIdx]).css("visibility", "hidden");
	},
	validateScrollBar: function() {
		var max = this.vertical ? this.panel.getScrollHeight() :
			this.panel.getScrollWidth();

		if(max <= 0 || this.lastPosition <= 0){
			this.toggleScrollBar(this.BarScroller.SCROLL_UPLEFT, false);
		} else {
			this.toggleScrollBar(this.BarScroller.SCROLL_UPLEFT, true);
		}

		if(max <= 0 || this.lastPosition >= max){
			this.toggleScrollBar(this.BarScroller.SCROLL_DOWNRIGHT, false);
		} else {
			this.toggleScrollBar(this.BarScroller.SCROLL_DOWNRIGHT, true);
		}
	},
	panelScrollHandler: function(event, pos, vertical) {
		if (vertical != this.vertical)
			return;
		var max = vertical ? this.panel.getScrollHeight() :
			this.panel.getScrollWidth();

		// in case scrolling
		if(pos <= 0 || pos >= max)
			this.stopScroll();

		this.lastPosition = pos;
		this.validateScrollBar();
	},
	barMouseDownHandler: function(event) {
		var scrollIdx = event.data.direction;
		if (!this.scrollBarStatus[scrollIdx] || this.options.scrollOnMouseOver)
			return;

		var obj = this;
		this.setScrollBarClass(scrollIdx, this.BarScroller.ACTIVE_CLASS_INDEX);
		this.startScroll(scrollIdx, this.options.step);
//		if (this.scrollTimerId)
//			return;

		this.delayTimerId = window.setTimeout(function() {
			delete obj.delayTimerId;
			obj.stopScroll();
			obj.startScroll(scrollIdx);
		}, this.options.scrollDelay);
	},
	barMouseUpHandler: function(event) {
		var scrollIdx = event.data.direction;
		if (!this.scrollBarStatus[scrollIdx] || this.options.scrollOnMouseOver)
			return;

		this.setScrollBarClass(scrollIdx, this.BarScroller.HOVER_CLASS_INDEX);
		if (this.delayTimerId) {
			window.clearInterval(this.delayTimerId);
			delete this.delayTimerId;
		}
		else
			this.stopScroll();
	},
	barMouseOverHandler: function(event) {
		var scrollIdx = event.data.direction;
		if (!this.scrollBarStatus[scrollIdx])
			return;

		this.setScrollBarClass(scrollIdx ,	this.BarScroller.HOVER_CLASS_INDEX);

		if (!this.options.scrollOnMouseOver &&
			!ZXC.UI.Panel.dragMonitor.dragging)
			return;

		this.startScroll(scrollIdx);
	},
	barMouseOutHandler: function(event) {
		var scrollIdx = event.data.direction;
		if (!this.scrollBarStatus[scrollIdx])
			return;

		this.setScrollBarClass(scrollIdx ,	this.BarScroller.NORMAL_CLASS_INDEX);
		this.stopScroll();
	},
	validateViewport: function(size) {
		var jViewport = $(this.panel.viewport);
		var jUpleftBar = $(this.scrollBar[this.BarScroller.SCROLL_UPLEFT]);
		var jDownrightBar = $(this.scrollBar[this.BarScroller.SCROLL_DOWNRIGHT]);
		var util = ZXC.util;

		if (!size) {
			var position = jViewport.position();
			size = {
				top: position.top,
				left: position.left,
				width: util.width(jViewport[0]),
				height: util.height(jViewport[0])
			};
		}
		var limit = this.vertical ? size.height : size.width;
		var upleftBoundary = this.vertical ? util.height(jUpleftBar) : util.width(jUpleftBar);
		var downrightBoundary = this.vertical ? util.height(jDownrightBar) : util.width(jDownrightBar);
		var newLimit = limit - upleftBoundary - downrightBoundary;

		// if no room for display scroller ,hide them
		jUpleftBar.css("display", (newLimit < 0) ? "none" : "block");
		jDownrightBar.css("display", (newLimit < 0) ? "none" : "block");

		if (newLimit > 0) {
			jViewport.css("position", "absolute");
			jUpleftBar.css("top", size.top + (this.vertical ? util.noUnitCss(jViewport[0], "margin-top") : 0))
				.css("left", size.left + (this.vertical ? 0 : util.noUnitCss(jViewport[0], "margin-left")));
			if (this.vertical) {
				size.height = newLimit;
				size.top += upleftBoundary;
				util.height(this.panel.viewport, newLimit);
				util.width(this.scrollBar[this.BarScroller.SCROLL_UPLEFT], size.width);
				util.width(this.scrollBar[this.BarScroller.SCROLL_DOWNRIGHT], size.width);
				jViewport.css("top", size.top);
				jDownrightBar.css("top", size.top + newLimit - util.noUnitCss(jViewport[0], "margin-bottom")).css("left", size.left);
			} else {
				size.width = newLimit;
				size.left += upleftBoundary
				util.width(this.panel.viewport, newLimit);
				util.height(this.scrollBar[this.BarScroller.SCROLL_UPLEFT], size.height);
				util.height(this.scrollBar[this.BarScroller.SCROLL_DOWNRIGHT], size.height);
				jViewport.css("left", size.left);
				jDownrightBar.css("top", size.top).css("left", size.left + newLimit - util.noUnitCss(jViewport[0], "margin-right"));
			}
		}

		// scroll to last position
		this.panel.scrollTo(this.lastPosition, this.vertical);
		return size;
	},
	layoutResizeHandler: function() {
		var max = this.vertical ? this.panel.getScrollHeight() :
			this.panel.getScrollWidth();
		if (this.lastPosition < 0 || this.lastPosition > max) {
			this.panel.scrollTo(this.lastPosition, this.vertical);
			return;
		}
		this.validateScrollBar();
	}
},
statics: {
	SCROLL_UPLEFT: 0,
	SCROLL_DOWNRIGHT: 1,
	NORMAL_CLASS_INDEX: 0,
	HOVER_CLASS_INDEX: 1,
	ACTIVE_CLASS_INDEX: 2,
	HIDE_CLASS_INDEX: 3
}
});

/**
 * Slider scroller implements.
 *
 * Supportted options including:
 * axisElement: 	HTML element
 * axisClass:
 * sliderElement:	HTML element act as slider
 * sliderClass:		[normalClass, [hoverClass, [activeClass, [hideClass]]]]
 */
ZXC.Class({
name: "ZXC.UI.SliderScroller",
construct:
	function(panel, vertical, options) {
		if (!panel)
			return;
		this.panel = panel;
		this.vertical = vertical;
		this.axis;
		this.slider;
		this.options = {
			sliderClass : "",
			scrollStep : 10,
			scrollCycle : 50
		};

		// private
		this.highlightStatus = [true, false, false, false];
		this.lastPosition = 0;
		this.sliderStatus = false;
		this.scrollTimerId = 0;
		this.mousedown = false;
		this.mousedownOffset = 0;
		this.sliding = false;

		this.initialize(options);
	},
methods: {
	initialize: function(options) {
		options = options ? options : {};
		var obj = this;

		// scroll bar and its style initialize
		this.axis = options.axisElement;
		this.slider = options.sliderElement;
		// bar class
		this.options.sliderClass = options.sliderClass ?
			options.sliderClass : (
			(!this.slider || !$(this.slider).attr("class")) ? "" :
			$(this.slider).attr("class"));
		// construct class array
		if (this.options.sliderClass.constructor == String)
			this.options.sliderClass = [this.options.sliderClass];
		// set default class
		for (var i = this.SliderScroller.HOVER_CLASS_INDEX; i < this.SliderScroller.HIDE_CLASS_INDEX; i++)
			if (!this.options.sliderClass[i])
				this.options.sliderClass[i] = this.options.sliderClass[i - 1];

		// create axis if not exists
		if (!this.axis) {
			this.axis = document.createElement("div");
			$(this.axis).attr("class", options.axisClass ? options.axisClass : "")
				.appendTo(this.panel.client);
		}
		else if (!this.axis.parentNode || this.axis.parentNode != this.panel.client) {
			$(this.axis).appendTo(this.panel.client);
		}
		// create slider if not exists
		if (!this.slider) {
			this.slider = document.createElement("div");
			$(this.slider).attr("class", this.options.sliderClass[this.SliderScroller.NORMAL_CLASS_INDEX])
				.appendTo(this.axis);
		} else if (!this.slider.parentNode || this.slider.parentNode != this.axis) {
			$(this.slider).appendTo(this.axis);
		}
		$(this.axis).css("position", "absolute")
			.click(function(event) {
				obj.axisClickHandler(event);
			});
		$(this.slider).css("position", "absolute")
			.mouseover(function(event) {
				obj.highlightSlider(obj.SliderScroller.HOVER_CLASS_INDEX);
			}).mouseout(function(event) {
				obj.highlightSlider(obj.SliderScroller.HOVER_CLASS_INDEX, true);
			}).mousedown(function(event) {
				obj.sliderMouseDownHandler(event);
			});
		if (this.vertical) {
			var offset = Math.floor(($(this.axis).width() - ZXC.util.width(this.slider)) / 2);
			$(this.slider).css("top", 0).css("left", offset);
		} else {
			var offset = Math.floor(($(this.axis).height() - ZXC.util.height(this.slider)) / 2);
			$(this.slider).css("top", offset).css("left", 0);
		}

		$(document.body).mousemove(function(event) {
				obj.sliderMouseMoveHandler(event);
			}).mouseup(function(event) {
				obj.sliderMouseUpHandler(event);
			});

		if (options.scrollStep)
			this.options.scrollStep = options.scrollStep;
		if (options.scrollCycle)
			this.options.scrollCycle = options.scrollCycle;

		// register onScroll handler
		this.panel.onScroll(function(event, pos, vertical) {
			obj.panelScrollHandler(event, pos, vertical);
		});
	},
	highlightSlider :  function (classIdx, deactivate) {
		if (classIdx != this.SliderScroller.NORMAL_CLASS_INDEX)
			this.highlightStatus[classIdx] = !deactivate;

		for (var i = this.highlightStatus.length - 1; i >= 0; i--)
			if (this.highlightStatus[i]) {
				$(this.slider).attr("class", this.options.sliderClass[i]);
				return i;
			}
	},
	toggleScrollBar: function(enable) {
		this.sliderStatus = enable;
		if (this.options.sliderClass[this.SliderScroller.HIDE_CLASS_INDEX])
		{
			this.highlightSlider(this.SliderScroller.HIDE_CLASS_INDEX, !enable);
		}
		else if (enable)
			$(this.slider).css("visibility", "");
		else
			$(this.slider).css("visibility", "hidden");
	},
	validateScrollBar: function() {
		var max = this.vertical ? this.panel.getScrollHeight() :
			this.panel.getScrollWidth();

		this.toggleScrollBar(max > 0);
	},
	offsetToPos: function(offset) {
		if (this.vertical) {
			var divider = this.axis.clientHeight - this.slider.offsetHeight
			return Math.round(offset * this.panel.getScrollHeight() /
				(divider == 0 ? 1 : divider));
		}
		else {
			var divider = this.axis.clientWidth - this.slider.offsetWidth;
			return Math.round(offset * this.panel.getScrollWidth() /
				(divider == 0 ? 1 : divider));
		}
	},
	posToOffset: function(pos) {
		if (this.vertical) {
			var divider = this.panel.getScrollHeight();
			return Math.round(pos * (this.axis.clientHeight - this.slider.offsetHeight) /
				(divider == 0 ? 1 : divider));
		}
		else {
			var divider = this.panel.getScrollWidth();
			return Math.round(pos * (this.axis.clientWidth - this.slider.offsetWidth) /
				(divider == 0 ? 1 : divider));
		}
	},
	scrollToOffset: function(offset) {
		// calculate offset limit
		var max = 0;
		if (this.vertical)
			max = this.axis.clientHeight - this.slider.offsetHeight;
		else
			max = this.axis.clientWidth - this.slider.offsetWidth;

		// round offset in range and set slider position
		offset = Math.min(max, offset);
		offset = Math.max(0, offset);
		$(this.slider).css(this.vertical ? "top" : "left", offset);

		// calculate scroll position
		var pos = this.offsetToPos(offset);
		if (pos != this.offsetToPos(max))	// ensure to reach end.
			pos = pos - pos % this.options.scrollStep;

		// if different with last, scroll
		if (pos != this.lastPosition)
			this.panel.scrollTo(pos, this.vertical);
	},
	panelScrollHandler: function(event, pos, vertical) {
		if (vertical != this.vertical)
			return;

		if (!this.sliding && this.sliderStatus)
			$(this.slider).css(vertical ? "top" : "left", this.posToOffset(pos));
		this.lastPosition = pos;
	},
	sliderMouseDownHandler: function(event) {
		event.preventDefault();
		this.highlightSlider(this.SliderScroller.ACTIVE_CLASS_INDEX);
		if (this.vertical)
			this.mousedownOffset = event.clientY - ZXC.util.locate(this.slider)[0];
		else
			this.mousedownOffset = event.clientX - ZXC.util.locate(this.slider)[1];
		this.mousedown = true;
	},
	sliderMouseUpHandler: function(event) {
		event.preventDefault();
		this.mousedown = this.sliding = false;
		this.highlightSlider(this.SliderScroller.ACTIVE_CLASS_INDEX, true);
	},
	sliderMouseMoveHandler: function(event) {
		event.preventDefault();
		if (!this.mousedown || !this.sliderStatus)
			return;

		var offset = -this.mousedownOffset;
		if (this.vertical)
			offset += event.clientY - ZXC.util.locate(this.axis)[0];
		else
			offset += event.clientX - ZXC.util.locate(this.axis)[1];

		this.sliding = true;
		this.scrollToOffset(offset);
		event.preventDefault();
	},
	axisClickHandler: function(event) {
		var offset = 0;
		if (this.vertical)
			offset = event.clientY - ZXC.util.locate(this.axis)[0] -
				Math.floor(this.slider.offsetHeight / 2);
		else
			offset = event.clientX - ZXC.util.locate(this.axis)[1] -
				Math.floor(this.slider.offsetWidth / 2);

		this.sliding = true;
		this.scrollToOffset(offset);
	},
	validateViewport: function(size) {
		var jViewport = $(this.panel.viewport);
		var jAxis = $(this.axis);
		var jSlider = $(this.slider);
		var funcGetLimit = this.vertical ?
			[jQuery.prototype.width, jQuery.prototype.height] :
			[jQuery.prototype.height, jQuery.prototype.width];
		var util = ZXC.util;

		if (!size) {
			var position = jViewport.position();
			size = {
				top: position.top,
				left: position.left,
				width: util.width(jViewport[0]),
				height: util.height(jViewport[0])
			};
		}
		var limit = this.vertical ? size.width : size.height;
		var offLimit = this.vertical ? size.height : size.width;
		var boundary = this.vertical ? util.width(this.axis) : util.height(this.axis);
		var offBoundary = this.vertical ? util.height(this.slider) : util.width(this.slider);
		var newLimit = limit - boundary;
		var newOffLimit = offLimit - offBoundary;

		// if no room to display axis or slider ,hide them
		jAxis.css("display", (newLimit < 0) ? "none" : "block");
		jSlider.css("display", (newOffLimit < 0) ? "none" : "block");

		if (newLimit > 0) {
			// set viewport size and scroller size
			jViewport.css("position", "absolute");
			if (this.vertical) {
				size.width = newLimit;
				util.width(this.panel.viewport, newLimit);
				util.height(this.axis, offLimit);
				jAxis.css("top", size.top).css("left", size.left  + newLimit - util.noUnitCss(jViewport[0], "margin-right"));
			} else {
				size.height = newLimit;
				util.height(this.panel.viewport, newLimit);
				util.width(this.axis, offLimit);
				jAxis.css("top", size.top + newLimit - util.noUnitCss(jViewport[0], "margin-bottom")).css("left", size.left);
			}
		}

		// scroll to last position
		this.panel.scrollTo(this.lastPosition, this.vertical);
		return size;
	},
	layoutResizeHandler: function() {
		var max = this.vertical ? this.panel.getScrollHeight() :
			this.panel.getScrollWidth();
		if (this.lastPosition < 0 || this.lastPosition > max) {
			this.panel.scrollTo(this.lastPosition, this.vertical);
			return;
		}
		this.validateScrollBar();
	}
},
statics: {
	SCROLL_UPLEFT: 0,
	SCROLL_DOWNRIGHT: 1,
	NORMAL_CLASS_INDEX: 0,
	HOVER_CLASS_INDEX: 1,
	ACTIVE_CLASS_INDEX: 2,
	HIDE_CLASS_INDEX: 3
}
});

/**
 * Slider scroller implements.
 *
 * Supportted options including:
 * axisElement: 	HTML element
 * axisClass:
 * sliderElement:	HTML element act as slider
 * sliderClass:		[normalClass, [hoverClass, [activeClass, [hideClass]]]]
 */
ZXC.Class({
name: "ZXC.UI.WheelScroller",
construct:
	function(panel, vertical, options) {
		if (!panel)
			return;
		this.panel = panel;
		this.vertical = vertical;
		this.options = {
		};

		// private
		this.wheelMultiplifier = 2;

		this.initialize(options);
	},
methods: {
	initialize: function(options) {
		var obj = this;
		$(this.panel.layout).bind('mousewheel', function(event, delta) {
			obj.scroll(delta * 2);
			event.preventDefault();
		});
	},
	/**
	* scroll a relative distance in a direction from a position
	* @param forward Direction. true to scroll ahead.
	* @param limit The max relative distance could be scrolled. However, actual scroll distance
	*        is determined by "scrollStep" option, omittable.
	* @param start The start position, if omitted, current position will be calculated.
    * @return Current position if "start" is given, or actual offset will be returned.
	*/
	scroll: function(delta) {
		var pos = -$(this.panel.layout).position()[this.vertical ? "top" : "left"];
		pos = pos + delta;
		var diff = pos - this.panel.scrollTo(pos, this.vertical);
		return diff;
	},
	validateViewport: function(size) {
		return size;
	},
	layoutResizeHandler: function() {
		
	}
}
});

