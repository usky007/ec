/**
 * Ajax Suggestion Utility
 *
 * What's new
 * 2012.11.12
 * Add "fit" for option "autoWidth".
 * Add "headerFactory" and "footerFactory" option.
 * Add onHighlight event.
 *
 * 2012.11.6
 * Add option "matchClass".
 *
 * 2009.4.25
 * Code refactor.
 * New setting option: defaultOption.
 * Add support to callback field of option entry.
 * Optimize browser compatibility.
 *
 * Feature:
 * Input suggestion with ajax.
 * Customizable suggest option entry.
 *   User defined key field.
 *   Support extra user customized fields.
 *   Support callback field.
 *     A callback field is a property of option entry with name "callback".
 *     It can be a Function of a String refer to a function, which may override
 *     the default action on selecting that option entry. defined as
 *       function(sender,optionEntry), returns false to prevent default action.
 * TODO: More features to be documented.
 *
 * This module is designed to be compatible with jQuery.
 * Use "$.ajaxSuggestion(options)" to register, support multiple block a time.
 * Also, you may register your block use "new ZXC.Widget.Suggestion(target, options)".
 *
 * Options including:
 * Widget behavior & style
 * source:			Required. Address to acquire suggest options.
 * multiple:		Allow multiple input at a time, default false.
 * delimiter:		String separator if multiple = true, default ";".
 * alternatives:	Alternative delimiter, will be convert to delimiter if entered.
 * escape:			Escape delimiter used to contain phrase that contains delimiter.
 * queryKey:     	Key of query string, default "q".
 * keyField:  		Name of field in result json object which act as key of entry, default "name".
 * scrollLimit:		Scroller will be generated if # of options larger than limit,
 *                 	default "0", will means no scroller will be generated.
 * autoFill:		Fill highlighted value into input controller, default false.
 * suggestOnFail:	Request for result form server if cache miss. default true.
 * suggestOnHit:	Request for result from server regardless of cache hit, this may help
 *                 	acquiring new options without refresh the current page. default false.
 * suggestOnLoad:  	Request for result form server on initialize, set to request
 *                 	parameter to enable request. default false.
 * guess:			Display options on guesswork, means options will be displayed
 *					even no input entered.
 * maxlength:		Max options displayed if exceeded.
 * autoWidth:		Auto calculate the width of option panel. default true.
 *					New! pass "fit" to keep width aligned with text input;
 * zIndex:			Layer z-index style.
 * compatibleMode:	False or 'thickbox', default false.
 * validate:		Perform an extra check to validate input pattern, proper onMatch
 * 					implementation is required (return Suggestion.KEY_EXACT_MATCH(2) for exact match), default false.
 * initialData:		Initial data for library.
 * defaultOption:	Default option for user if no option is available.
 *                  Object that compatible with server, or a callback function is accepted.
 *                  Parameters: pattern.
 *                  Return: Object that compatible with server.
 * panelClass:		Class attribute of generated option panel.
 * highlightClass:  Class attribute of selected option.
 * optionClass:     Class attribute of unselected options.
 * matchClass:		Class attribute of matched part in option.
 * headerFactory	Html fragment to show as header of option panel, function is supported for dynamic header.
 * footerFactory	Html fragment to show as header of option panel, function is supported for dynamic header.
 * 
 * Callbacks
 * onSelect:        Callback function on option being selected.
 *                  Parameters: sender,optionEntry.
 *                  Return: String to display in input control.
 * onShow:      	Callback function on showing the options.
 *                  Parameters: sender,optionEntry.
 *                  Return: String to display in option panel.
 * onMatch:			Callback function on matching options.
 *                  Parameters: sender,optionEntry, pattern.
 *                  Return: True if matchs, return Suggestion.KEY_EXACT_MATCH(2) on exact match to support "validate" feature.
 * onSort:			Callback function on sort options to display.
 *                  Parameters: sender, optionEntries, pattern.
 *					Return: resorted entries, change orders in passed "optionEntries" argument also works.
 * onGuess:			Callback function on showing guess options.
 *					Parameters: sender.
 *					Return: ZXC.Event.preventDefault() to prevent default operation.
 * onGuessMatch:	Callback function on matching guess options.
 *                  Parameters: sender, optionEntries.
 *                  Return: False to exclude this entry from guess list.
 * 
 * Events
 * onError:			Triggers on something wrong.
 *					Parameters: sender, msg.
 * onLoading:       Triggers on loading the request options.
 *					Parameters: sender.
 * onComplete:      Triggers after options acquired.
 *					Parameters: sender.
 * onValidateComplete:	Triggers on validate complete.
 *						Parameters: sender,validOptionEntry.
 * onHighlight:		Trigger on highlight an option.
 *					Parameters: sender, optionElem, optionEntry
 */

ZXC.Require("ZXC.util");

ZXC.Namespace("ZXC.Widget");

ZXC.Widget.Export("Suggestion");

ZXC.Widget.Suggestion = ZXC.Class({
name: "ZXC.Widget.Suggestion",
construct: function(target, settings) {
		this.target = target;
		this.options = this.Suggestion.options;
		this.library = null;
		this.settings = {
			multiple:false,
			delimiter:";",
			alternatives:/\uFF1B/g,
			escape:"\"",
			queryKey:"q",
			keyField:"name",
			scrollLimit:0,
			autoFill:false,
			suggestOnHit:false,
			suggestOnFail:true,
			suggestOnLoad:false,
			guess:false,
			maxlength:20,
			autoWidth:true,
			zIndex:'',
			compatibleMode: false,
			validate: false,
			initialData: null,
			defaultOption: null,
			headerFactory: null,
			footerFactory: null,
			
			panelClass : "widget_suggestion",
			optionsClass : "options",
			optionClass : "option",
			highlightClass : "selected",
			matchClass : "matched"
//			buttonClass : "tag_button"
		};
		

		this.onSelect = this.defaultOnSelectHandler;
		this.onSort = this.defaultOnSortHandler;
		this.onShow = this.defaultOnShowHandler;
		this.onMatch = this.defaultOnMatchHandler;
		this.onGuess = this.defaultOnGuessHandler;
		
		this.onLoading = ZXC.Event();
		this.onError = ZXC.Event();
		this.onComplete = ZXC.Event();
		this.onValidateComplete = ZXC.Event();
		this.onHighlight = ZXC.Event();

		this.targetBlock = jQuery(this.target);
		this.parts = {};

		this.initialize(settings);
		this.Suggestion.monitorChange();
	},
methods: {
	initialize: function(settings)
	{
		var obj = this;
		this.targetBlock.keydown(ZXC.Callback(this, "keydownHandler"))
			.keyup(ZXC.Callback(this, "keyupHandler"))
			.focus(ZXC.Callback(this, "focusHandler"))
			.blur(ZXC.Callback(this, "blurHandler"))
			.bind("ZXC.widget.suggestion.keyChanged", ZXC.Callback(this, "changeHandler"));
		if (!this.targetBlock.attr("type") || this.targetBlock.attr("type") == "text")
			this.targetBlock.attr('autocomplete', 'off');

		// settings
		for (var key in this.settings) {
			if (settings[key] !== undefined) {
				this.settings[key] = settings[key];
			}
		}
		if (settings.alternatives !== undefined)
		{
			if (settings.alternatives.constructor === RegExp)
				this.settings.alternatives = settings.alternatives;
			else if (settings.alternatives.constructor === String)
				this.settings.alternatives =
					new RegExp("\\" + settings.alternatives, "g");
			else if (settings.alternatives.constructor === Array)
				this.settings.alternatives =
					new RegExp("\\" + settings.alternatives.join("|\\"), "g");
			else
				this.settings.alternatives = settings.alternatives;
		}

		// callback and events
		this.onSelect = this.getDefinedHandler(settings.onSelect, this.onSelect);
		this.onSort = this.getDefinedHandler(settings.onSort, this.onSort);
		this.onShow = this.getDefinedHandler(settings.onShow, this.onShow);
		this.onMatch = this.getDefinedHandler(settings.onMatch, this.onMatch);
		this.onGuess = this.getDefinedHandler(settings.onGuess, this.onGuess);
		this.onGuessMatch = this.getDefinedHandler(settings.onGuessMatch, this.onGuessMatch);
		
		settings.onError && this.onError.add(settings.onError);
		settings.onLoading && this.onLoading.add(settings.onLoading);
		settings.onComplete && this.onComplete.add(settings.onComplete);
		settings.onValidateComplete && this.onValidateComplete.add(settings.onValidateComplete);
		settings.onHighlight && this.onHighlight.add(settings.onHighlight);

		this.resetLibrary(settings.source, false);
		if (this.settings.initialData) {
			var data = this.settings.initialData;
			var arr = new Array();
			for (var i = 0; i < data.length; i++)
				arr.push(new this.Suggestion.Entry(
					data[i][obj.settings.keyField], data[i]));
			this.library.expand(arr)
		}
	},
	// show options panel
	// options: filtered options array
	// pattern: uncompleted words that will be bold in suggest options
	show: function(options, pattern)
	{
		if (!options || options.length == 0) {
			this.hide();
		}
		
		var obj = this;
		// Create panel and set overall property.
		if (this.options.panel == null)
		{	
			jQuery(this.options.panel = document.createElement("div")).mousedown(ZXC.Callback(this, "panelMouseDownHandler"));
		}
		var optionPanel = jQuery(this.options.panel).attr("class", "").css("width","");
		
		// Assign container
		if (!this.parts.container) {
			if (!this.settings.headerFactory && !this.settings.footerFactory) {
				this.parts.container = optionPanel;
			}
			else {
				this.parts.container = jQuery(document.createElement("div")).mousedown(ZXC.Callback(this, "panelMouseDownHandler"));
				if (this.settings.headerFactory && "function" != typeof this.settings.headerFactory) {
					this.parts.header = jQuery(this.settings.headerFactory).prependTo(this.parts.container);
				}
				if (this.settings.footerFactory && "function" != typeof this.settings.footerFactory) {
					this.parts.footer = jQuery(this.settings.footerFactory).appendTo(this.parts.container);
				}
			}
		}
		if (this.parts.container[0] !== optionPanel[0]) {
			optionPanel.css({position:"relative", top:0, left:0, display:"block"});
			if (!this.parts.footer) {
				optionPanel.appendTo(this.parts.container);
			}
			else {
				optionPanel.insertBefore(this.parts.footer);
			}
		}
		var container = this.parts.container;
		var header = this.parts.header;
		var footer = this.parts.footer;

		// Callback, chance to resort options.
		options = this.onSort(this, options, pattern) || options;

		// Rebuild options in panel.
		optionPanel.empty();
		if ("function" == typeof this.settings.headerFactory) {
			header = jQuery(this.settings.headerFactory(this, options));
			if (!this.parts.header || this.parts.header[0] !== header[0]) {
				if (this.parts.header) this.parts.header.remove();
				container.prepend(header);
				this.parts.header = header;
			}
		}
		this.options.length = options.length;
		for (var i = 0; i < options.length; i++)
		{
			this.options[i] = options[i];

			// Callback, chance to customize final html to be displayed.
			var innerTxt = this.onShow(this, options[i]);

			// Highlight matching characters, support html.
			var patternReg = new RegExp("(" + pattern.replace(/(\W)/g,"\\\$1") + ")", "gi");
			innerTxt = innerTxt.replace(/(^|>)((?:.|\n|\r)*?)(<|$)/g, function(match){
				return (arguments[1] || "") 
					+ arguments[2].replace(patternReg, "<span class='" + obj.settings.matchClass + "'>$1</span>") 
					+ (arguments[3] || "");
			})

			var option = document.createElement("div");
			jQuery(option)
				.attr("class", this.settings.optionClass)
				.html(innerTxt)
				.bind("mouseover", i, function(event) {
					obj.highlight(event.data);
				}).mousemove(function(event) {
					obj.tagMousemoveHandler(event);
				}).mousedown(function(event){
					ZXC.util.log("debug", this.classname, "mousedown:suggestion option entry");
					obj.select();
					event.preventDefault();
					//It's option panel's work to recapture focus.
					//if (jQuery.browser.mozilla)
					//	obj.targetBlock.trigger("blur");
				}).appendTo(optionPanel);
		}
		if ("function" == typeof this.settings.footerFactory) {
			footer = jQuery(this.settings.footerFactory(this, options));
			if (!this.parts.footer || this.parts.footer[0] !== footer[0]) {
				if (this.parts.footer) this.parts.footer.remove();
				container.append(footer);
				this.parts.footer = footer;
			}
		}

		// set per target style and locate
		container
			.attr("class", obj.settings.panelClass)
			.css("display", "block")
			.css("position", "absolute")
			.css("z-index", this.settings.zIndex)
			.appendTo(document.body);
		optionPanel.addClass(obj.settings.optionsClass);
		switch (this.settings.compatibleMode) {
			case "thickbox":
				container.appendTo($("#TB_ajaxContent"));
				break;
		}

		// emulate dropdown list
		// style.width = tagbox.offsetWidth + tagbox.button.offsetWidth;
		// remove scroll list in ie
		//if (jQuery.browser.msie) {
		//	optionPanel.css("overflow", "hidden");
		//}
		// show scroll if exceeds limit
		var offlimit = this.settings.scrollLimit > 0 && options.length > this.settings.scrollLimit;
		if (this.settings.autoWidth == "fit") {
			ZXC.util.width(container, ZXC.util.width(this.target));
		}
		else if (this.settings.autoWidth) {
			optionPanel.width("");
			// set outerWidth;
			var obWidth = ZXC.util.width(optionPanel);
			if (offlimit) {
				// expand width to avoid horizon scroller
				ZXC.util.width(container, obWidth + 20);
			}
			else
			{
				container.width("");
			}
		}
		if (offlimit) {
			ZXC.util.height(optionPanel, this.getOptionItem(this.settings.scrollLimit).offsetTop);
		}
		else {
			optionPanel.height("auto");
		}
		optionPanel.prop("scrollTop", 0);

		// locate
		var offsetY = ZXC.util.locate(this.target)[0] - ZXC.util.getPageYOffset();
		if (offsetY + $(this.target).height() + container.height() > ZXC.util.getInnerHeight() &&
			offsetY - container.height() > 0)
			ZXC.util.locate(container, this.target, "top-left", "right-up", '1');
		else
			ZXC.util.locate(container, this.target, "bottom-left", "right-down", '1');

		with (this.options)
		{
			hotIdx = 0;
			display = true;
			instance = this;
			key = pattern;
			offset = optionPanel.offset();
			scrollRange = optionPanel[0].scrollHeight - optionPanel.innerHeight();
		}
		// high light first tag
		this.highlight(this.options.hotIdx);
	},
	showDefault: function(pattern) {
		var o = this.settings.defaultOption;
		o = o instanceof Function ? o(pattern) : o;
		if (o)
			this.show([new this.Suggestion.Entry(o[this.settings.keyField], o)], pattern);
	},
	// hide option panel
	hide: function() {
		if (this.options.display) {
			this.parts.container.hide();
			this.options.display = false;
		}
	},
	// get option item
	// idx: index of option item.
	getOptionItem: function(idx)
	{
		if (idx < 0 || idx >= this.options.length)
			return null;
		return jQuery("div:eq(" + idx + ")", this.options.panel)[0];
	},
	// get scrollTop max value
	getScrollRange: function()
	{
		return this.options.scrollRange;
	},
	// change selected tag index according to offset to current selected.
	// offset: negetive:move up, position:move down.
	offsetHighlight: function(offset)
	{
		return this.highlight((this.options.hotIdx + this.options.length + offset) % this.options.length);
	},
	// change selected tag index directly
	// idx: index of selected suggestion item.
	highlight: function(idx)
	{
		idx = idx % this.options.length;
		this.options.hotIdx = idx;

		jQuery("div." + this.settings.highlightClass, this.options.panel).removeClass(this.settings.highlightClass);
		var highlighted = jQuery("div:eq(" + idx + ")", this.options.panel).addClass(this.settings.highlightClass);
		
		this.onHighlight(this, highlighted[0], this.options[idx]);
		
		return idx;
	},
	// append complete word to textbox value
	fill: function()
	{
		var val = this.targetBlock.val();
		if (this.settings.alternatives)
			val = val.replace(this.settings.alternatives, this.settings.delimiter);
	//	val = val.substring(0, val.lastIndexOf(this.settings.delimiter) + 1);

		var regexp = "^(?:[\\;]*\\s*)(\\'(?:[^\\']*)(?:\\'\\'[^\\']*)*\\'|(?:[^\\';]+))";
		regexp = regexp.replace(/;/g, this.settings.delimiter)
			.replace(/'/g, this.settings.escape);
		var valRegexp = "(?:[\\;\\s]*)(.*)";
		valRegexp = valRegexp.replace(/;/g, this.settings.delimiter);
		var reg = new RegExp(regexp);
		var lastMatch = null;
		var match = reg.exec(val);
		while(match != null)
		{
			lastMatch = match;
			val = val.substring(lastMatch[0].length);
			match = reg.exec(val);
		}
		if (val.length > 0)
			val = (val.match(valRegexp)[1]).toLowerCase();
		else if (lastMatch != null)
			val =  lastMatch[1].toLowerCase();
		else
			val = "";

		var finalVal = this.targetBlock.val();
		finalVal = finalVal.substring(0, finalVal.length - val.length);
		var selectVal = this.onSelect(this, this.options[this.options.hotIdx]);
		this.targetBlock.val(finalVal + selectVal +
			(this.settings.multiple ? this.settings.delimiter : ""));

		if (this.settings.multiple)
			this.options.key = "";
		else
			this.options.key = selectVal;

		if (this.settings.validate) {
			this.onValidateComplete(this,  this.options[this.options.hotIdx]);
		}
	},
	// extract incomplete word from textbox value
	extractPrefix: function()
	{
		var val = this.targetBlock.val();
		if (this.settings.alternatives)
			val = val.replace(this.settings.alternatives, this.settings.delimiter);
	//	var regexp = "(?:(?:^|\\;)\\s*)((?:$)|(?:\\S[^\\;]*$))";
	//	var reg = new RegExp(regexp.replace(/;/g, this.settings.delimiter));
	//	return (val.match(reg)[1]).toLowerCase();
		var regexp = "^(?:[\\;]*\\s*)(\\'(?:[^\\']*)(?:\\'\\'[^\\']*)*\\'|(?:[^\\';]+))";
		regexp = regexp.replace(/;/g, this.settings.delimiter)
			.replace(/'/g, this.settings.escape);
		var valRegexp = "(?:[\\;\\s\\']*)(.*)";
		valRegexp = valRegexp.replace(/;/g, this.settings.delimiter)
			.replace(/'/g, this.settings.escape);
		var reg = new RegExp(regexp);
		var lastMatch = null;
		var match = reg.exec(val);
		while(match != null)
		{
			lastMatch = match;
			val = val.substring(lastMatch[0].length);
			match = reg.exec(val);
		}
		if (val.length > 0)
			return (val.match(valRegexp)[1]).toLowerCase();
		else if (lastMatch != null)
			return lastMatch[1].toLowerCase();
		else
			return "";
	},
	// get suggestion options by extracted incomplete word
	// prefix:incomplete word
	extractSubArray: function(pattern)
	{
		if (pattern.length == 0)
			return new Array();

		var result = this.library.match(pattern, this.onMatch, this.settings.validate);
		if (this.settings.validate)
			this.onValidateComplete(this, result.entry);
		return result;
	},
	// reset focus on textbox and set caret to end
	captureFocus: function()
	{
		ZXC.util.log("debug", this.name, "Try recapture focus");
		this.targetBlock.focus();
		if (this.target.createTextRange) {
			var range = this.target.createTextRange();
			range.collapse(false);
			range.select();
		}
	//	else if (this.target.setSelectionRange) {
	//		this.target.setSelectionRange(this.target.value.length, this.target.value.length);
	//	}
	},
	select: function()
	{
		var option = this.options[this.options.hotIdx];
		if (option.callback) {
			var result = true;
			if (option.callback instanceof Function)
				result = option.callback(this, option);
			else if (option.callback instanceof String)
			 	result = eval(option.callback + "(this, option);");

			if (!result) {
				this.hide();
				return;
			}
		}

		this.fill();
		if (this.settings.guess && this.settings.multiple)
			this.changeHandler();
		else
			this.hide();
	},
	// mousemove handler of suggestion item
	tagMousemoveHandler: function(event)
	{
		if (this.options.length > this.settings.scrollLimit)
		{
			var windowRange = jQuery(this.options.panel).innerHeight();
//			ZXC.util.log("debug", this.classname, "windowRange:" + windowRange);
			var mouse2middon = event.pageY - this.options.offset.top -
				(windowRange / 2);
//			ZXC.util.log("debug", this.classname, "event.pageY:" + event.pageY + 
//				",offsetTop:" + this.options.offset.top + 
//				",mouse2middon:" + mouse2middon);
			// set scroll position of selected option item.
			this.options.panel.scrollTop = this.getScrollRange() *
				(windowRange / 2 + mouse2middon * 1.1) /
				(windowRange);
//			ZXC.util.log("debug", this.classname, "scrollTop:" + this.options.panel.scrollTop);
		}
	},
	// blur handler of textbox
	blurHandler: function(event)
	{
		ZXC.util.log("debug", this.classname, "blur:suggestion target");
		// initiate focus regain sequence;
		if (this.options.lentFocus == this.Suggestion.LENT_SEQ_ACTION) {
			this.options.lentFocus = this.Suggestion.LENT_SEQ_CONFIRM;
			// let handler flys, then try recapturing focus.
			window.setTimeout(ZXC.Callback(this, "captureFocus"), 10);
			return;
		}
		// save key in options temperarily.
		this.options.key = this.extractPrefix();
		if (this.options.key.length > 0 && this.settings.autoFill && this.options.display)
			this.fill();
		else if (this.settings.validate)
			this.changeHandler();
		this.hide();
		this.options.instance = null;
	},
	panelMouseDownHandler: function(event) 
	{
		var _this = this;
		// declare textbox owned focus before focus moved to suggestion panel
		ZXC.util.log("debug", this.classname, "mousedown:suggestion panel");
		this.options.lentFocus = this.Suggestion.LENT_SEQ_ACTION;
		// set timeout to confirm lent.
		window.setTimeout(function() {
			if (_this.options.lentFocus == _this.Suggestion.LENT_SEQ_ACTION)
				_this.options.lentFocus = _this.Suggestion.LENT_SEQ_RETURN;
		}, 10);
		event.stopPropagation();
	},
	// keydown Handler of textbox
	keydownHandler: function(event)
	{
		var tagHeight
		var limit = this.settings.scrollLimit;
		switch (event.keyCode)
		{
			// key "up", select previous option
			case 38:
				if (this.options.display)
				{
					this.offsetHighlight(-1);
					// adjust scroll postion
					if (this.options.length > limit)
					{
						this.options.panel.scrollTop = this.getScrollRange() *
							this.options.hotIdx / (this.options.length - 1);
					}
					event.preventDefault();
					return;
				}
				break;
			// key "right", show all options
			case 39:
				if (this.extractPrefix().length > 0) {
					break;
				}
				else if (this.options.display) {
					this.hide();
					return;
				}
				else {
					var result = this.library.listAll();
					if (result.length > 0)
					{
						this.show(result, "");
						return;
					}
				}
				break;
			// key "down", select previous option
			case 40:
				if (this.options.display)
				{
					this.offsetHighlight(1);
					// adjust scroll postion
					if (this.options.length > limit)
					{
						this.options.panel.scrollTop = this.getScrollRange() *
							this.options.hotIdx / (this.options.length - 1);
					}
					event.preventDefault();
					return;
				}
				else
				{
					this.changeHandler();
				}
				break;
			// key "return", select option
			case 13:
				if (this.options.display)
				{
					this.select();
					event.preventDefault();
					return;
				}
				else
				{
	//				this.target.form.submit();
	//				event.preventDefault();
					return;
				}
				break;
			// key ";" ,ie(186),gecko(59)
	//		case 59:
	//		case 186:
	//			if (!event.shiftKey && !this.settings.multiple)
	//			{
	//				event.preventDefault();
	//				return;
	//			}
	//			break;
		}
	},
	// keyup Handler of textbox
	keyupHandler: function(event)
	{
		switch (event.keyCode)
		{
			case 37:
			case 38:
			case 39:
			case 40:
				return;
				break;
//			case 13:
//				if (this.options.lentFocus)
//				{
//					this.captureFocus();
//					return;
//				}
//				break;
	//		case 59:
	//		case 186:
	//			if (!event.shiftKey && !this.settings.multiple)
	//			{
	//				return;
	//			}
	//			break;
		}
		//this.changeHandler();
	},
	// textbox value change handler
	focusHandler: function(event)
	{
		ZXC.util.log("debug", this.classname, "focus:suggestion target");
		if (this.options.lentFocus == this.Suggestion.LENT_SEQ_CONFIRM)
		{
			this.options.lentFocus = this.Suggestion.LENT_SEQ_RETURN;
			return;
		}
		this.options.lentFocus = this.Suggestion.LENT_SEQ_RETURN;
		this.options.instance = this;
		// let handler flys, then try suggesting.
		window.setTimeout(ZXC.Callback(this, "changeHandler"), 10);
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

		var sub = this.extractSubArray(prefix);
		if (sub.length == 0 || (sub.length == 1 && sub.entry))
			this.hide();
		else
			this.show(sub, prefix);

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
	defaultOnSortHandler: function(sender, options, pattern)
	{
		if (pattern && pattern != "") {
			for (var i = 0; i < options.length; i++)
				options[i].idx = options[i].key().indexOf(pattern);
			options.sort(function(a, b){
				var result = 0;
				if (a.idx < 0 && b.idx < 0)
					result = 0;
				else if (a.idx < 0)
					return -1;
				else if (b.idx < 0)
					return 1;
				else
					result = a.idx - b.idx;

				if (result || !a.priority || !b.priority) return result;
				else return a.priority - b.priority;
			});
		}
		options.length = Math.min(options.length, this.settings.maxlength);
	},
	defaultOnShowHandler: function(sender, entry)
	{
		return entry.key();
	},
	defaultOnMatchHandler: function(sender, entry, pattern)
	{
		return entry.isMatch(pattern);
	},
	defaultOnSelectHandler: function(sender, entry)
	{
		if (entry.key().indexOf(this.settings.delimiter) >= 0 ||
			this.settings.alternatives.exec(entry.key()) != null)
			return this.settings.escape + entry.key() + this.settings.escape;
		else
			return entry.key();
	},
	defaultOnGuessHandler: function(sender)
	{
		var entries = this.library.listAll();
		var filtered = [];
		for (var i = 0; i < entries.length; i++) {
			if (this.onGuessMatch(this, entries[i]) !== false) {
				filtered.push(entries[i]);
			}
		}
		this.show(filtered, "");
	},
	defaultHandler: function(sender) {},
	getDefinedHandler: function(handler, defaultHandler)
	{
		if (handler !== undefined && handler instanceof Function)
			return handler;
		else if (defaultHandler !== undefined)
			return defaultHandler;
		else
			return this.defaultHandler;
	},
	suggest: function(pattern) {
		var obj = this;
		var method = 'GET';
		var query = null;
		var source = this.library.source;
		
		this.onLoading(this);
		var ajaxOptions = {
			url: source,
			type: method,
			data: query,
			dataType: 'json',
			timeout: 30000,
			error: function(request, status, ex){
				obj.onError(obj, ex);
			},
			success: function(result){
				obj.successHandler(pattern, result);
				obj.onComplete(obj);
			}
		};
		var overrides = this.getRequestOptions(pattern);
		for (var key in overrides) {
			ajaxOptions[key] = overrides[key];
		}
		ZXC.util.jQueryAjaxHelper(ajaxOptions);
	},
	resetLibrary: function(source, force)
	{
		force = (force !== false) ? true : false;
		this.library = this.Suggestion.Library.getLibrary(source);
		if (this.settings.suggestOnLoad && (force || !this.library.loaded)) {
			var pattern = typeof this.settings.suggestOnLoad != "string" ? "" :
				this.settings.suggestOnLoad;
			this.suggest(pattern);
			this.library.loaded = true;
		}
	},
	resetData: function(data)
	{
		this.library.clear();
		if (data != null && data != '' && data != undefined)
		{
			var arr = new Array();
			for (var i = 0; i < data.length; i++)
				arr.push(new this.Suggestion.Entry(
					data[i][this.settings.keyField], data[i]));

			this.library.expand(arr);
		}
	},
	// Functions for customize.
	getRequestOptions: function(pattern)
	{
		var ajaxOptions = {url:this.library.source, data:null};
		
		// unify default pattern for convenience
		if (!pattern) pattern = "";
		if (pattern.length > 0) {
			ajaxOptions.data = {};
			ajaxOptions.data[this.settings.queryKey] = pattern;
		}
		
		return ajaxOptions;
	},
	successHandler: function(pattern, result)
	{
		if (!result.success) {
			throw new Error(result.message);
		}
		var arr = new Array();
		for (var i = 0; i < result.data.length; i++) {
			arr.push(new this.Suggestion.Entry(
				result.data[i][this.settings.keyField], result.data[i]));
		}

		this.library.expand(arr, pattern);
		// filter with client side match function and trigger validation if set.
		if (pattern.length > 0)
			arr = this.extractSubArray(pattern);
		else if (!this.settings.guess)
			arr = new Array();	// set to empty if guess setting disabled.
		// arr not empty? request key not change? focus holds?
		// show suggestion panel.
		if (this.options.key == pattern && this.options.instance === this) {
			if (arr.length > 0)
				this.show(arr, pattern);
			else if (pattern.length > 0 && this.settings.defaultOption)
				this.showDefault(pattern);
		}
	}
},
statics: {
	LENT_SEQ_RETURN: 0,
	LENT_SEQ_ACTION: 1,
	LENT_SEQ_CONFIRM: 2,
	options: {
		length:0,
		hotIdx:0,
		panel:null,
		offset:[0,0],	// panel offset related to viewport
		scrollRange:0,	// panel's range of value of scrollTop.
		display:false,
		lentFocus:0,
		instance:null,
		key:""
	},
	monitorChange: function() {
		var obj = this;
		if (this._monitorInstance === undefined) {
			this._monitorInstance = null;
			window.setInterval(function() {
				obj.monitorChange();
			}, 1000);
			return;
		}
		else if (!this.options.instance) {
			return;
		}
		else if (this._monitorInstance != this.options.instance) {
			this._monitorInstance = this.options.instance;
			return;
		}

		var key = this._monitorInstance.extractPrefix();
		if (key != this.options.key) {
			this.options.key = key;
			$(this._monitorInstance.target).trigger("ZXC.widget.suggestion.keyChanged");
		}
	}
}
});

/**
 * ajaxSuggestion library entry.
 */
ZXC.Widget.Suggestion.Entry = ZXC.Class({
name: "ZXC.Widget.Suggestion.Entry",
construct:
	function(key, fields) {
		this.key = function() {
			return key;
		};
		this.value = null;

		if (fields !== undefined && fields != null)
			this.addFields(fields);
	},
methods: {
	addFields: function(fields) {
		if (fields.constrctor === String)
			this.value = fields;
		else if (fields.constructor === Object)
		{
			for (var entry in fields)
				this[entry] = fields[entry];
		}
	},
	// default match function, 2 for exact match;
	isMatch: function(pattern) {
		if (pattern.constructor === RegExp) {
			var matchs = this.key().match(pattern);
			if (!matchs) return 0;
			else return (matchs[0] == pattern) ? 2 : 1;
		}
		else {
			if (this.key().toLowerCase() == pattern) return 2;
			else return (this.key().toLowerCase().indexOf(pattern.toLowerCase(), 0) >= 0) ? 1 : 0;
		}
	}
}
});

/**
 * ajaxSuggestion library history.
 */
ZXC.Widget.Suggestion.History = ZXC.Class({
name: "ZXC.Widget.Suggestion.History",
construct:
	function(keys, matchKey) {
		this.keys = {length:0};
		this.lastUpdate = null;
		this.matchKey; // The key of exact match if any.

		this.update(keys, matchKey);
	},
methods: {
	update: function(keys, matchKey) {
		// merge keys
		for (var i = 0; i < keys.length; i++)
		{
			if (this.keys[keys[i]] === undefined)
			{
				this.keys[keys[i]] = keys[i];
				this.keys[this.keys.length++] = keys[i];
			}
		}
		this.lastUpdate = new Date().getTime();
		if (matchKey)
			this.matchKey = matchKey;
	},
	isExpired: function(time) {
		return this.lastUpdate < time;
	}
}
});

/**
 * ajaxSuggestion library
 */
ZXC.Widget.Suggestion.Library = ZXC.Class({
name: "ZXC.Widget.Suggestion.Library",
construct:
	function() {
		this.source = null;
		this.entries = {};
		this.histories = {};
		this.lastUpdate = new Date().getTime();
		this.loaded = false;
		this.localMatch = true; // No local match function will be called if set this option to false.(Exception "validate" option is set to true)
	},
methods: {
	initialize: function(source) {
		this.source = source;
	},
	match: function(pattern, callback, validate) {
		var result = new Array();
		result.bingo = false;
		result.entry = null;

		var cached = this.histories[pattern];
		if (cached !== undefined && (!this.localMatch || !cached.isExpired(this.lastUpdate)))
		{
			for (var i = 0; i < cached.keys.length; i++) {
				result[i] = this.entries[cached.keys[i]];
				if (!validate || cached.matchKey !== undefined)
					continue;
				// revalidate
				var match = false;
				if (callback !== undefined)
					match = callback(this, result[i], pattern);
				else
					match = result[i].isMatch(pattern);
				if (match == 2)
					cached.matchKey = result[i].key();
			}
			result.bingo = true;
			result.entry = cached.matchKey ? this.entries[cached.matchKey] : null;
		}
		else if (this.localMatch)
		{
			candidates = new Array();
			for (var key in this.entries)
			{
				candidates.push(key);
			}

			var keys = new Array();
			var matchKey;
			for (var i = 0; i < candidates.length; i++)
			{
				var key = candidates[i];
				var match = false;
				if (callback !== undefined)
					match = callback(this, this.entries[key], pattern);
				else
					match = this.entries[key].isMatch(pattern);

				if (match)
				{
					result.push(this.entries[key]);
					keys.push(key);
					if (match == 2) {
						matchKey = key;
						result.entry = this.entries[key];
					}
				}
			}
			if (cached === undefined)
				this.histories[pattern] = new ZXC.Widget.Suggestion.History(keys, matchKey);
			else
				cached.update(keys, matchKey);
		}
		return result;
	},
	listAll: function()
	{
		var result = new Array();
		for (var key in this.entries)
		{
			result.push(this.entries[key]);
		}
		return result;
	},
	expand: function(entries, pattern) {
		var keys = new Array();
		var newCnt = 0;
		if (entries.length > 0)
		{
			for (var i = 0; i < entries.length; i++)
			{
				if (this.entries[entries[i].key()] === undefined)
				{
					newCnt++;
					this.entries[entries[i].key()] = entries[i];
				}
				keys.push(entries[i].key());
			}
			if (newCnt > 0)
				this.lastUpdate = new Date().getTime();
		}
		if (pattern && pattern != "" && (newCnt > 0 || !this.localMatch))
		{
			var cached = this.histories[pattern];
			if (cached === undefined)
				this.histories[pattern] = new ZXC.Widget.Suggestion.History(keys);
			else
				cached.update(keys);
		}
	},
	clear: function() {
		this.entries = {};
		this.histories = {};
		this.lastUpdate = new Date().getTime();
	}
},
statics: {
	getLibrary: function(source) {
		if (this.manager === undefined)
			this.manager = {};
		if (this.manager[source] === undefined)
		{
			this.manager[source] = new this();
			this.manager[source].initialize(source);
		}
		return this.manager[source];
	}
}
});

// jQuery Plugin Support
jQuery.fn.suggestion = function(settings) {
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

			this.widget_suggestion = new ZXC.Widget.Suggestion(this, copy);
		}
	);
};
