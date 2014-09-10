/**
 * menu
 */
ZXC.Namespace("ZXC.Widget");

ZXC.Widget.Export("SimpleMenu");

/**
 * Implements of simple html based menu.
 * To use this module, HTML should be organized like following:
 * <div class="menuTarget">
 *   <a class="menuTxt">Hover area<img src="xxx" alt="toggleButton"/></a>
 *   <div class="menu">your menu here</div>
 * </div>
 *
 * This module is designed to be compatible with jQuery.
 * Use "$.simpleMenu(options)" to register, support multiple blocks at a time.
 * Also, you may register your block use "new ZXC.Widget.SimpleMenu(target, options)".
 *
 * Supportted options including:
 * menu:			required, HTMLElement contains menu.
 * toggleButton:	HTMLElement activates menu.
 * cssClass: 		CSS class applied to target on status change.
 *                  [normalClass, [hoverClass, [activeClass]]]
 * imgList:  		Img applied to toggleButton on status change.
 *                  [normalImg, [hoverImg, [activeImg]]]
 * showOnHover:		Behavior option:display menu on mouse over.
 * hideOnOut:		Behavior option:hide menu on mouse out of menu area.
 * selectOnClick:	Behavior option:display menu option in MAIN element instead of taking action.
 * onSelect:		Callback function on select option, ignore if selectOnClick is false or not set.
 *                  Arguments:sender, selected HTMLElement.
 *                  Return: return false to deny select.
 * onShow:			Callback function on show menu
 *					Arguments:sender, current menu object.
 *					Return: return false to deny display.
 * onHide:			Callback function on hide menu
 *					Arguments:sender, current menu object.
 *
 * Note:
 * Set behavior options to "true" to change menu behavior.
 *
 * What's New:
 * 2009.5.26 More readable usage comment.
 */
ZXC.Widget.SimpleMenu = ZXC.Class({
name: "ZXC.Widget.SimpleMenu",
construct:
	function(target, options) {
		this.element = target;
		this.toggleButton;
		this.entry;
		this.menu;
		this.options = {
			cssClass: "",
			imgList: "",
			showOnHover: false,
			hideOnOut: false,
			selectOnClick: false
		}

		// public event
		this.onClickMenuItem = null;

		// private
		this.highlightStatus = [true, false, false, false];
		this.hover = false;
		this.onSelect = null;
		this.onShow = null;
		this.onHide = null;

		this.initialize(options);
	},
methods: {
	initialize: function(options) {
		if (!options)
			return;

		this.options.showOnHover = 	options.showOnHover;
		this.options.hideOnOut = options.hideOnOut;
		this.options.selectOnClick = options.selectOnClick;
		this.onSelect = options.onSelect;
		this.onShow = options.onShow;
		this.onHide = options.onHide;

		this.onClickMenuItem = options.onClickMenuItem || null;

		this.toggleButton = jQuery(options.toggleButton, this.element)[0];
		this.entry = jQuery("a", this.element)[0];
		this.menu = jQuery(options.menu, this.element)[0];
		// override showOnHover setting if no toggleButton specified
		if (!this.toggleButton)
			this.options.showOnHover = true;
		// override showOnHover setting if no entry available
		if (!this.entry)
			this.options.hideOnOut = true;
		if (!this.menu)
			return;

		// set and map imgList;
		this.options.imgList = options.imgList;
		if (this.options.imgList) {
			if (this.options.imgList.constructor == String)
				this.options.imgList = [this.options.imgList];
			for (var i = 1; i < this.highlightStatus.length; i++)
				if (!this.options.imgList[i])
					this.options.imgList[i] = this.options.imgList[i - 1];
			this.options.imgList = this.statusMap(this.options.imgList, "imgList");
		}

		// set cssClass
		this.options.cssClass = options.cssClass ?
			options.cssClass : jQuery(this.ul).attr("class");
		if (!this.options.cssClass)
			this.options.cssClass = "";
		if (this.options.cssClass.constructor == String)
			this.options.cssClass = [this.options.cssClass];
		for (var i = 1; i < this.highlightStatus.length; i++)
			if (!this.options.cssClass[i])
					this.options.cssClass[i] = this.options.cssClass[i - 1];
		this.options.cssClass = this.statusMap(this.options.cssClass, "cssClass");

		var obj = this;
		jQuery(this.menu).css("display", "none").css("position", "relative");
		if (this.options.showOnHover) {
			jQuery(this.element).mouseover(function() {
				obj.hover = true;
				obj.show();
			}).mouseout(function() {
				obj.hover = false;
			});
		} else {
			jQuery(this.element).mouseover(function() {
				obj.hover = true;
				obj.highlight(obj.SimpleMenu.HOVER_CLASS_INDEX);
			}).mouseout(function() {
				obj.hover = false;
				obj.highlight(obj.SimpleMenu.HOVER_CLASS_INDEX, true);
			});
			jQuery(this.toggleButton).mouseover(function(event) {
				obj.hover = true;
				obj.highlight(obj.SimpleMenu.HANDELHOVER_CLASS_INDEX);
				event.preventDefault();
				event.stopPropagation();
			}).mouseout(function(event) {
				obj.hover = false;
				obj.highlight(obj.SimpleMenu.HANDELHOVER_CLASS_INDEX, true);
				event.preventDefault();
				event.stopPropagation();
			}).mousedown(function(event) {
				obj.toggle();
				event.preventDefault();
				event.stopPropagation();
			}).mouseup(function(event) {
				event.preventDefault();
				event.stopPropagation();
			}).click(function(event) {
				event.preventDefault();
				event.stopPropagation();
			});
		}

		if (this.options.hideOnOut) {
			jQuery(this.element).mouseout(function() {
				obj.hover = false;
				window.setTimeout(function() {
					if (!obj.hover)
						obj.hide(true);
				}, 10);
			})
		} else {
			jQuery(this.entry).blur(function() {
				if (!obj.hover)
					obj.hide(false);
				return false;
			})
		}

		jQuery("a", this.menu).click(function(event) {
			if (typeof obj.onClickMenuItem == "function" && obj.onClickMenuItem(obj, this, event) === false)
			{
				event.preventDefault();
				return;
			}

			obj.hide(true);
			if (obj.options.selectOnClick) {
				if (!obj.onSelect || obj.onSelect(obj, this) !== false) {
					jQuery(obj.entry).html($(this).html());
					jQuery(obj.entry).attr("href", $(this).attr("href"));
				}
				event.preventDefault();
			}
		});
	},
	toggle: function() {
		if (jQuery(this.menu).css("display") != "none")
			this.hide(true);
		else
			this.show();
	},
	show: function() {
		if (this.onShow) {
			if (!this.onShow(this))
				return false;
		}
		this.highlight(this.SimpleMenu.ACTIVE_CLASS_INDEX);
		jQuery(this.menu).css("display", "block");
		var zidx = jQuery(this.menu).css('z-index');
		if (!zidx || zidx == '' || zidx=='auto')
			jQuery(this.menu).css('z-index', '100');
		if (this.entry) {
			this.entry.focus();
			this.entry.hideFocus = true; // ie
		}
		return true;
	},
	hide: function(blur) {
		this.highlight(this.SimpleMenu.ACTIVE_CLASS_INDEX, true);
		jQuery(this.menu).css("display", "none");
		if (this.entry && blur)
			this.entry.blur();
		if (this.onHide)
			this.onHide(this);
	},
	highlight :  function (classIdx, deactivate) {
		// remove last class
		for (var i = this.highlightStatus.length - 1; i >= 0; i--)
			if (this.highlightStatus[i])
				jQuery(this.element).removeClass(this.options.cssClass[i]);

		// set flag
		if (classIdx != this.SimpleMenu.NORMAL_CLASS_INDEX)
			this.highlightStatus[classIdx] = !deactivate;

		// add new class
		for (var i = this.highlightStatus.length - 1; i >= 0; i--)
			if (this.highlightStatus[i]) {
				jQuery(this.element).addClass(this.options.cssClass[i]);
				if (this.toggleButton && this.options.imgList) {
					if (this.toggleButton.tagName.toLowerCase() == "img")
						jQuery(this.toggleButton).attr("src", this.options.imgList[i]);
					else
						jQuery("img", this.toggleButton).attr("src", this.options.imgList[i]);
				}
				return i;
			}
	},
	statusMap: function(states, type) {
		var newStates = new Array();
		switch (type) {
			case "imgList":
				newStates.push(states[this.SimpleMenu.OPTION_NORMAL_INDEX]);
				newStates.push(states[this.SimpleMenu.OPTION_NORMAL_INDEX]);
				newStates.push(states[this.SimpleMenu.OPTION_HOVER_INDEX]);
				newStates.push(states[this.SimpleMenu.OPTION_ACTIVE_INDEX]);
				break;
			case "cssClass":
				newStates.push(states[this.SimpleMenu.OPTION_NORMAL_INDEX]);
				newStates.push(states[this.SimpleMenu.OPTION_HOVER_INDEX]);
				newStates.push(states[this.SimpleMenu.OPTION_HOVER_INDEX]);
				newStates.push(states[this.SimpleMenu.OPTION_ACTIVE_INDEX]);
				break;
		}
		return newStates;
	}
},
statics: {
	OPTION_NORMAL_INDEX: 0,
	OPTION_HOVER_INDEX: 1,
	OPTION_ACTIVE_INDEX: 2,
	NORMAL_CLASS_INDEX: 0,
	HOVER_CLASS_INDEX: 1,
	HANDELHOVER_CLASS_INDEX: 2,
	ACTIVE_CLASS_INDEX: 3,
	register: function(options) {
		if (!options && this.length > 0) {
			return this[0].widget_simpleMenu;
		}
		
		if (!options.menu)
			return;

		return this.each(
			function() {
				var copy = {};
				for(var property in options)
					copy[property] = options[property];

				this.widget_simpleMenu = new ZXC.Widget.SimpleMenu(this, copy);
			}
		);
	}
}
});

jQuery.fn.simpleMenu = ZXC.Widget.SimpleMenu.register;