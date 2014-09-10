/**
 * Ajax Update Utility
 *
 * This module defines a single global symbol named "ZXC".
 * ZXC refers to a namespace object, and all utility functions
 * are stored as properties of this namespace.
 *
 * This module is designed to be compatible with jQuery.
 * Use "$.ajaxUpdate(options)" to register, support multiple block a time.
 * Also, you may register your block use "new ZXC.ajaxUpdate(target, options)".
 *
 * Options including:
 * action:			Required. Address to submit.
 * name:			Required. Control name or Array in form ["UpdateFieldName"[,"ConditionFieldName"[,...]]}
 *                  Can be a callback function if register through jQuery interface.
 *					Arguments:targetElem. return null to ignore corresponding element.
 * controlType:		Control type, support "text" and "textarea", "text" by default.
 * submitValue:		Text for submit link button, "提交" by default.
 * cancelValue:     Text for cancel link button, "取消" by default.
 * highlightClass:  Class attribute of block on activate.
 * formClass:		Class attribute of generated form.
 * controlClass:	Class attribute of generated control.
 * submitClass:		Class attribute of generated submit link button.
 * cancelClass:		Class attribute of generated cancel link button.
 * onError:			Callback function on error. Arguments:msg.
 * onSetEditorValue:Callback function on set editor's value. Arguments:editorControl, val, updateFieldName.
 * onGetPostValue:	Callback function on get value to post. Arguments:editorControl.
 * onSetResult:	Callback function on set returned value to label. Arguments:label, val, updateFieldName.
 */

if (!ZXC)
	var ZXC = {};
var undefined;

ZXC.ajaxUpdate = function(target, options)
{
	this.target = target;
	this.activated = false;
	this.action = null;
	this.name = null;
	this.updateFieldName = "";
	this.controlType = "text";
	this.controlDatePicker = false;
	this.controlAjaxSuggestion = false;
	this.controlGmenu = false;
	this.controlTagSelector = false;
	this.ts = null;
	this.buttonType = "a";
	this.submitValue = "提交";
	this.cancelValue = "取消";

	this.targetBlock = $(this.target);
	this.labelBlock = null;
	this.formBlock = null;
	this.controlBlock = null;
	this.headerInfo = null;
	this.extraInfo = null;
	this.submitBlock = null;
	this.cancelBlock = null;
	this.activeEntry = null;

	this.normalClass = this.targetBlock.attr("class") === undefined ? "" : this.targetBlock.attr("class");
	
	/*edit 101029 [B]*/
	this.highlightClass = null;
	this.savingClass=null;
	/*edit 101029 [E]*/
	
	this.editicon = null;
	this.formClass = null;
	this.controlClass = null;
	this.submitClass = "submit";
	this.cancelClass = "cancel";

	this.onError = null;
	this.onShowEditor = null;
	this.onHideEditor = null;
	this.onSetEditorValue = null;
	this.onGetPostValue = null;
	this.onFormatValue = null;

	this.initialize = function(options) {
		var obj = this;
		this.action = options.action;
		this.name = options.name;

		if (typeof this.name == "string")
			this.updateFieldName = this.name;
		else
			this.updateFieldName = this.name[0];

		if (options.controlType !== undefined)
			this.controlType = options.controlType;
		if (options.controlDatePicker !== undefined)
			this.controlDatePicker = options.controlDatePicker;
		if (options.controlAjaxSuggestion !== undefined)
			this.controlAjaxSuggestion = options.controlAjaxSuggestion;
		if (options.controlGmenu !== undefined)
			this.controlGmenu = options.controlGmenu;
		if (options.controlTagSelector !== undefined)
			this.controlTagSelector = options.controlTagSelector;
		if (options.buttonType !== undefined)
			this.buttonType = options.buttonType;
		if (options.activeEntry !== undefined)
			this.activeEntry = options.activeEntry;
		if (options.submitValue !== undefined)
			this.submitValue = options.submitValue;
		if (options.cancelValue !== undefined)
			this.cancelValue = options.cancelValue;
		if (options.headerInfo !== undefined)
			this.headerInfo = options.headerInfo;
		if (options.extraInfo !== undefined)
			this.extraInfo = options.extraInfo;

		// class definition
		if (options.formClass !== undefined)
			this.formClass = options.formClass;
		if (options.controlClass !== undefined)
			this.controlClass = options.controlClass;
		if (options.submitClass !== undefined)
			this.submitClass = options.submitClass;
		if (options.cancelClass !== undefined)
			this.cancelClass = options.cancelClass;
		/*edit 101029 [B]*/
		
		//if (options.highlightClass !== undefined)
		//	this.highlightClass = options.highlightClass;
		if(options.savingClass !== undefined)
			this.savingClass = options.savingClass;
		/*edit 101029 [E]*/	
		if (typeof options.editicon != "undefined")
			this.editicon = options.editicon;

		// event definition
		this.onError = this.getDefinedHandler(options.onError, this.defaultErrorHandler);
		this.onSetEditorValue = this.getDefinedHandler(options.onSetEditorValue, this.defaultSetEditorValueHandler);
		this.onGetPostValue = this.getDefinedHandler(options.onGetPostValue, this.defaultGetPostValueHandler);
		this.onSetResult = this.getDefinedHandler(options.onSetResult, this.defaultSetResultHandler);
		this.onShowEditor = this.getDefinedHandler(options.onShowEditor, function(){});
		this.onHideEditor = this.getDefinedHandler(options.onHideEditor, function(){});

		// generate form
		this.generateForm();

		if (!this.activeEntry)
			this.activeEntry = this.targetBlock;
		this.activeEntry.mouseover(function(){
			obj.onActivate();
		}).mouseout(function(){
			obj.onDeactivate();
		}).click(function(){
			obj.activate();
			return false;
		});

		this.submitBlock.click(function() {
			obj.submit();
			return false;
		});

		this.cancelBlock.click(function(){
			obj.deactivate();
			return false;
		});
	};

	this.onActivate = function()
	{
	
		//if (!this.activated)
		//	this.targetBlock.attr("class", this.highlightClass);
	};

	this.onDeactivate = function()
	{
		//if (!this.activated)
		//	this.targetBlock.attr("class", this.normalClass);
	};

	this.defaultErrorHandler = function(msg)
	{
		alert(msg);
	}

	this.defaultSetEditorValueHandler = function(ctrl, elem, attr)
	{

		var str = "";
		if ($(elem).attr(attr) !== undefined)
			str = $(elem).attr(attr);
		else
			str = $(elem).html();

		$(ctrl).val(str.replace(/<br[^>]*>\n?/gi, "\n"));
	}

	this.defaultGetPostValueHandler = function(ctrl)
	{
		return $(ctrl).val();
	}

	this.defaultSetResultHandler = function(label, result, updateFieldName)
	{
		var str = result[this.mame];
		str = str.replace(/\n\r?/g, "<br/>");

		if ($(label).attr(updateFieldName) !== undefined)
			$(label).attr(updateFieldName, str);
		$(label).html(str);
	}

	this.defaultHandler = function(target) {};

	this.getDefinedHandler = function(handler, defaultHandler)
	{
		if (handler !== undefined && handler instanceof Function)
			return handler;
		else if (defaultHandler !== undefined)
			return defaultHandler;
		else
			return this.defaultHandler;
	}

	this.activate = function()
	{
		if(this.targetBlock.children("div").attr("class")=="name_onsaving"){
			return false;
		}
		if (!this.activated)
		{
			this.onActivate();
			this.activated = true;
			this.labelBlock.hide();
			this.onSetEditorValue(this.controlBlock[0], this.labelBlock[0], this.updateFieldName);
			this.formBlock.show();
			this.onShowEditor();
		}
	};

	this.submit = function()
	{
		var obj = this;
		this.formBlock.hide();
		if (this.ts != null){
			this.ts.hide();
		}
		var old_val = this.labelBlock.html();
		
		//alert(this.savingClass);
		/*edit 101029 [B]*/
		//alert(this.targetBlock);
		this.labelBlock.attr("class", this.savingClass);
		/*edit 101029 [E]*/
		this.labelBlock.html("<span>正在保存，请稍候……</span>").show();

		if (typeof this.name == "string")
			var query = this.name + '=' + this.onGetPostValue(this.controlBlock[0]);
		else
		{
			var query = this.name[0] + '=' + this.onGetPostValue(this.controlBlock[0]);
			for (var i = 1; i < this.name.length; i++)
			{
				query += "&" + this.name[i] + '=' + $("input[name='" + this.name[i] + "']", this.formBlock).val();
			}
		}
		//alert( "\"" + query + "\"" );

		$.ajax({
			url: this.action,
			type: 'POST',
			data: query,
			dataType: 'json',
			timeout: 30000,
			error: function(){
				obj.onError("服务器没有响应。");
				obj.labelBlock.html(old_val);
				
				/*edit 101029 [B] return to default*/
				obj.labelBlock.attr("class", obj.normalClass);
				/*edit 101029 [E]*/

			},
			success: function(result){
				//alert("a");
				if (result.success)
				{
					obj.onSetResult(obj.labelBlock[0], result, obj.updateFieldName);
					/*edit 101029 [B]*/
					obj.labelBlock.attr("class", obj.normalClass);
					/*edit 101029 [E]*/

				}
				else
				{
					obj.onError(result.errmsg || result.message);
					obj.labelBlock.html(old_val);
					/*edit 101029 [B]*/
					obj.labelBlock.attr("class", obj.normalClass);
					/*edit 101029 [E]*/

				}
			}
		});

		this.activated = false;
		this.onDeactivate();
		this.onHideEditor();
	};

	this.deactivate = function()
	{
		this.formBlock.hide();
		this.labelBlock.show();
		this.activated = false;
		this.onDeactivate();
		this.onHideEditor();
	};

	this.generateForm = function() {
		var content = this.targetBlock.html();
		this.targetBlock.html("");
		this.targetBlock.append("<div/>");
		this.targetBlock.append("<form method=\"post\"/>");

		this.labelBlock = $("div", this.targetBlock);
		this.labelBlock.html(content);

		if (this.editicon != null)
			this.labelBlock.append(this.editicon);

		if (typeof this.name == "string")
		{
			if (this.targetBlock.attr(this.name) !== undefined)
			{
				this.labelBlock.attr(this.name, this.targetBlock.attr(this.name));
				this.targetBlock.removeAttr(this.name);
			}

		}
		else
		{
			for (var i = 0; i < this.name.length; i++)
			{
				var fieldName = this.name[i];
				if (i != 0)
					this.labelBlock.attr(fieldName, this.targetBlock.attr(fieldName));
				else if (this.targetBlock.attr(fieldName) !== undefined)
					this.labelBlock.attr(fieldName, this.targetBlock.attr(fieldName));

				this.targetBlock.removeAttr(fieldName);
			}
		}

		this.formBlock = $("form", this.targetBlock);
		this.formBlock.css("display", "none");
		this.formBlock.attr("class", this.formClass);
		switch (this.controlType)
		{
			case "textarea":
				this.formBlock.append("<textarea/>");
				break;
			default:
				this.formBlock.append("<input type=\""+this.controlType+"\"/>");
				break;
		}

		if (typeof this.name == "object" && this.name.length > 1)
			for (var i = 1; i < this.name.length; i++)
			{
				var fieldName = this.name[i];
				var fieldValue = this.labelBlock.attr(fieldName);
				this.formBlock.append("<input type=\"hidden\" name=\"" + fieldName + "\" value=\"" + fieldValue + "\" />");
			}

		this.controlBlock = $(":input[type='"+this.controlType+"']", this.formBlock);

		this.controlBlock.attr("name", this.updateFieldName);
		this.controlBlock.attr("class", this.controlClass);
		if (this.controlTagSelector)
		{
			this.controlBlock.after(this.controlTagSelector.entryHtml);
		}
		if (this.headerInfo)
			this.formBlock.prepend(this.headerInfo);
		if (this.extraInfo)
			this.formBlock.append(this.extraInfo);
		if (this.controlGmenu)
		{
			this.formBlock.append(this.controlGmenu.entryHtml);
		}

		if (this.controlAjaxSuggestion)
		{
			this.controlBlock.attr("idx", this.controlAjaxSuggestion.idx);
			this.formBlock.append(" &nbsp;&nbsp;<img id=\"suggestion_status_" + this.controlAjaxSuggestion.idx + "\" width=\"16\" height=\"16\" src=\"" + js_context.theme_url.get('image/space.gif') + "\" style=\"vertical-align:middle;\" /> ");
		}

		if (this.buttonType == 'a')
		{
			this.formBlock.append("<a class=\"" + this.submitClass + "\">" + this.submitValue + "</a>");
			this.formBlock.append("<a class=\"" + this.cancelClass + "\">" + this.cancelValue + "</a>");
			this.submitBlock = $("a[class='" + this.submitClass + "']", this.formBlock);
			this.cancelBlock = $("a[class='" + this.cancelClass + "']", this.formBlock);
		}
		else
		{
			this.formBlock.append("<input type=\"button\" value=\"" + this.submitValue + "\" class=\"" + this.submitClass + "\" style=\"margin:0px 5px 0px 0px;\" border=\"0\" />");
			this.formBlock.append("<input type=\"button\" value=\"" + this.cancelValue + "\" class=\"" + this.cancelClass + "\" border=\"0\" />");
			this.submitBlock = $("input[class='" + this.submitClass + "']", this.formBlock);
			this.cancelBlock = $("input[class='" + this.cancelClass + "']", this.formBlock);
		}

		if (this.controlDatePicker)
			this.controlBlock.datepicker(this.controlDatePicker);
		if (this.controlAjaxSuggestion)
			this.controlBlock.ajaxSuggestion(this.controlAjaxSuggestion);
		if (this.controlGmenu)
		{
			var gmenuOptions = {};
			for (var key in this.controlGmenu)
				gmenuOptions[key] = this.controlGmenu[key];
			gmenuOptions.targetItem = this.controlBlock;
			$(this.controlGmenu.entry).Gmenu(gmenuOptions);

			var obj = this;
			this.controlBlock.click(function(){
				var geotag = $(this).val();
				if (geotag == "")
					$(obj.controlGmenu.entry).trigger("click");
			}).keyup(function(){
				var geotag = $(this).val();
				if (geotag.length > 0)
				{
					try {
						ZXC.App.Gmenu.CURRENT.hide();
					} catch(e) {
					}
				}
			});
		}

		if (this.controlTagSelector) {
			var tagSelectorOptions = {};
			for (var key in this.controlTagSelector) {
				tagSelectorOptions[key] = this.controlTagSelector[key];
			}
			this.ts = new ZXC.Widget.TagSelector(tagSelectorOptions.id, tagSelectorOptions.entry, tagSelectorOptions.target, {
				type: tagSelectorOptions.type,
				data: tagSelectorOptions.data,
				location: tagSelectorOptions.location
			});
		}
	}

	this.initialize(options);
};

ZXC.ajaxUpdate.register = function(options)
{
	if (options.action === undefined || !options.name === undefined)
		return;

	return this.each(
		function() {
			var copy = {};
			for(var property in options)
				copy[property] = options[property];
			if (copy.name instanceof Function)
			{
				copy.name = copy.name(this);
				if (copy.name == null) return;
			}
			if (copy.controlType !== undefined && copy.controlType instanceof Function)
			{
				copy.controlType = copy.controlType(this);
				if (copy.controlType == null)
					copy.controlType = undefined;
			}
			if (copy.controlClass !== undefined && copy.controlClass instanceof Function)
			{
				copy.controlClass = copy.controlClass(this);
				if (copy.controlClass == null)
					copy.controlClass = undefined;
			}

			this.ajaxUpdate = new ZXC.ajaxUpdate(this, copy);
		}
	);
};

jQuery.fn.ajaxUpdate = ZXC.ajaxUpdate.register;
