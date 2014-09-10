/**
 * Container Implement
 *
 */
ZXC.Require("ZXC.util");
ZXC.Require("ZXC.UI.Panel");

ZXC.Namespace("ZXC.UI");

ZXC.UI.Export("Container", "FullScreenContainer");

/**
 * Generic ui container implements.
 *
 * This panel implements support dock and automatic resize.
 *
 * Supportted options including:
 * element: HTML element the panel will represents, create a new element
 *			if not specified;
 * parent:	Parent "Panel" object;
 * dock:	Dock settings, default to be dock to top, with lock none;
 */
ZXC.UI.Container = ZXC.Class({
name: "ZXC.UI.Container",
extend: ZXC.UI.Panel,
construct:
	function(name, options) {
		if (!name)
			return;

		this.Panel(name, options);
		this.children = {length:0};
		this.suspendLayout = false;
	},
methods: {
	initialize: function(options) {
		this.Panel.prototype.initialize.apply(this, [options]);
	},
	addPanel: function(panel) {
		// check whether panel is a subclass of ZXC.UI.Panel or not
		if (!panel || !panel instanceof this.Panel)
			throw new Error("Container:addPanel( ):invalid argument \"panel\".");
		// check if panel is in container already.
		if (this.children[panel.getName()])
			return;

		// register
		var obj = this;
		panel.onResize(function(event, noInformParent) {
			if (!noInformParent && !obj.suspendLayout)
				obj.validateLayout();
		});

		// add panel and validateLayout if possible
		this.children[panel.getName()] = this.children[this.children.length++] = panel;
		//if (!panel.client.parentNode || panel.client.parentNode != this.client)
			$(panel.client).appendTo(document).appendTo(this.client);
		if (!this.suspendLayout)
			this.validateLayout();
	},
	invalidateLayout: function() {
		this.suspendLayout = true;
	},
	validateLayout: function() {
		this.suspendLayout = false;
		if (!this.available())
			return;
		if (this.parent && this.parent.available() && this.parent.suspendLayout)
			return;

		// 4 elements in following array are each for top,bottom,left and right.
		var Dock = ZXC.UI.Dock;
		var direction = ["Top", "Bottom", "Left", "Right"];
		var offsets = [0, 0, 0, 0];
		var windowWidth = this.client.clientWidth;
		var windowHeight = this.client.clientHeight;
		for (var i = 0; i < offsets.length; i++)
		{
			var padding = $(this.client).css("padding" + direction[i]);
			if (padding && padding.constructor == String)
					padding = padding.replace(/[a-zA-Z]/g, "");
			offsets[i] = padding ? parseInt(padding) : 0;
			// outerXXX = margin + border + padding + css
			// offsetXXX = border + padding + css
			// clientXXX = padding + css
			// innerXXX = css
			if (i > 1)
				windowWidth -= offsets[i];
			else
				windowHeight -= offsets[i];
		}

		for (var i = 0; i < this.children.length; i++) {
			var client = $(this.children[i].client);
			if (!this.children[i].available()) {
				client.css("display", "none");
				continue;
			}

			// no room to display panel?
			if (windowWidth <= 0 || windowHeight <= 0) {
				client.css("display", "none");
				continue;
			}

			var dock = this.children[i].dock;

			// set position style
			client.css("position", "absolute").css("display", "block");
			dock.checkLock(Dock.LOCK_WIDTH) || this.children[i].setWidth(windowWidth);
			dock.checkLock(Dock.LOCK_HEIGHT) || this.children[i].setHeight(windowHeight);
			var width = this.children[i].getWidth();
			var height = this.children[i].getHeight();

			// no room to for current panel?
			if (windowWidth < width || windowHeight < height) {
				client.css("display", "none");
				continue;
			}

			// set key variables
			switch (dock.side) {
				case Dock.DOCK_TOP:
					windowHeight -= height;
					client.css("top", offsets[Dock.DOCK_TOP])
						.css("left", offsets[Dock.DOCK_LEFT]);
					offsets[dock.side] += height;
					break;
				case Dock.DOCK_BOTTOM:
					windowHeight -= height;
					client.css("top", offsets[Dock.DOCK_TOP] + windowHeight)
						.css("left", offsets[Dock.DOCK_LEFT]);
					offsets[dock.side] += height;
					break;
				case Dock.DOCK_LEFT:
					windowWidth -= width;
					client.css("top", offsets[Dock.DOCK_TOP])
						.css("left", offsets[Dock.DOCK_LEFT]);
					offsets[dock.side] += width;
					break;
				case Dock.DOCK_RIGHT:
					windowWidth -= width;
					client.css("top", offsets[Dock.DOCK_TOP])
						.css("left", offsets[Dock.DOCK_LEFT] + windowWidth);
					offsets[dock.side] += width;
					break;
			}

			client.trigger("panelResize", [true]);
		}

		if (this.viewport !== this.client)
		{
			var size = {
				top:offsets[Dock.DOCK_TOP],
				left:offsets[Dock.DOCK_LEFT],
				width:windowWidth,
				height:windowHeight
			};

			$(this.viewport).css("position", "absolute")
				.css("top", size.top)
				.css("left", size.left);
			ZXC.util.width(this.viewport, size.width);
			ZXC.util.height(this.viewport, size.height);
			$(this.viewport).trigger("panelResize", [size]);
		}
	},
	resizeHandler: function(event) {
		event.stopPropagation();
		if (!this.suspendLayout)
			this.validateLayout();
	}
}
});

ZXC.Class({
name: "ZXC.UI.FullScreenContainer",
extend: ZXC.UI.Container,
construct:
	function(name, options) {
		if (!name)
			return;
		this.Container(name, options);
	},
methods: {
	initialize: function(options) {
		this.Container.prototype.initialize.apply(this, [options]);
		var obj = this;
		var locate = function(data) {
			if (!data) data = ZXC.util.WindowSizeMonitor;
			var location = ZXC.util.locate(obj.client);
			ZXC.util.width(obj.client, data.width - location[1] + 
				ZXC.util.noUnitCss(obj.client,"marginLeft") + ZXC.util.getPageXOffset());
			ZXC.util.height(obj.client, data.height - location[0] + 
				ZXC.util.noUnitCss(obj.client,"marginTop") + ZXC.util.getPageYOffset());
		}
		$(document.body).css("overflow", "hidden");
		$(window).bind("windowResize", function(event, data){
			locate(data);
			$(obj.client).trigger("panelResize");
 		});
 		locate();
	}
}
});

ZXC.Class({
name: "ZXC.UI.FullHeightContainer",
extend: ZXC.UI.Container,
construct:
	function(name, options) {
		if (!name)
			return;
		this.Container(name, options);
	},
methods: {
	initialize: function(options) {
		this.Container.prototype.initialize.apply(this, [options]);
		var obj = this;
		var locate = function(data) {
			if (!data) data = ZXC.util.WindowSizeMonitor;
			var location = ZXC.util.locate(obj.client);
			ZXC.util.height(obj.client, data.height - location[0] + 
				ZXC.util.noUnitCss(obj.client,"marginTop") + ZXC.util.getPageYOffset());
		}
		$(document.body).css("overflow", "hidden");
		$(window).bind("windowResize", function(event, data){
			locate(data);
			$(obj.client).trigger("panelResize");
 		});
 		locate();
	}
}
});

ZXC.Class({
name: "ZXC.UI.FullWidthContainer",
extend: ZXC.UI.Container,
construct:
	function(name, options) {
		if (!name)
			return;
		this.Container(name, options);
	},
methods: {
	initialize: function(options) {
		this.Container.prototype.initialize.apply(this, [options]);
		var obj = this;
		var locate = function(data) {
			if (!data) data = ZXC.util.WindowSizeMonitor;
			var location = ZXC.util.locate(obj.client);
			ZXC.util.width(obj.client, data.width - location[1] + 
				ZXC.util.noUnitCss(obj.client,"marginLeft") + ZXC.util.getPageXOffset());
		}
		$(document.body).css("overflow", "hidden");
		$(window).bind("windowResize", function(event, data){
			locate(data);
			$(obj.client).trigger("panelResize");
 		});
 		locate();
	}
}
});