/**
 * Dialog implement, transplant from ZXC.UI.Panel.messageBox
 *
 * Options:
 * title: Title of dialog
 *
 * MessageBox supported extra options:
 * template: { TYPE : {"dlgType": BTN_TEMPLATE, "title": TITLE, "cssClass": DLG_BODY_CLASS}}
 */

ZXC.Require("ZXC.Resource");

ZXC.Namespace("ZXC.UI");

ZXC.UI.Export("Dialog");

ZXC.UI.Dialog = ZXC.Class({
name: "ZXC.UI.Dialog",
construct:
	function(elem, id, options) {
		this.guid = id;
		this.invalid = false;
		this.timeoutId = 0;
		this.element = null;
		this.container = null;
		this._content = null;
		this._buttons = {
			"ok" : {label:"", element:null, callback:null},
			"deny" : {label:"", element:null, callback:null},
			"cancel" : {label:"", element:null, callback:null}
		};
		this.status = {
			binded : false,
			type : null,
			hidden : false
		};
		this.options = {
			alterClass : null,
			width : 0, // >0 for custom width, -1 to apply window width.
			height : 0, // >0 for custom height, -1 to apply window height.
			title: "",
			overlap: false, // Whether dialog triggered dialog will overlap on old dialog or not.
			template: null // messagebox only option
		};

		this.initialize(elem, options);
	},
methods: {
	initialize: function(elem, options) {
		if (options)
			for (key in this.options)
				if (options[key] !== undefined)
					this.options[key] = options[key];
		if (!elem)
			return;
		if (!this.guid)
			this.guid = $(elem).attr("id") ? $(elem).attr("id") :
				"dlg_box" + ZXC.UI.Dialog.getGuid();
		$(elem).attr("id", this.guid);

		var obj = this;
		this.element = document.createElement("div");
		$(this.element).append($(elem).children())
			.appendTo(elem)
			.bind("databind", function() {
				if (!obj.Dialog._dataRegistry[obj.guid])
					return;
				for (var key in obj.Dialog._dataRegistry[obj.guid]) {
					if (key == "sequence")
						continue;

					var match = key.match(/^key_(.+)$/);
					if (!match)
						obj.bind(obj.Dialog._dataRegistry[obj.guid][key]);
					else
						obj.bind(obj.Dialog._dataRegistry[obj.guid][key], key);
				}
			});
		this.container = elem;
		$(this.container).dialog({
				autoOpen:false,zIndex:500,width:"auto",
				minHeight:0,minWidth:200,bgiframe:true,
				close: function(event, ui) { obj._closeCallback(event, ui); }
			})
			.css({display:"block",position:"static",visibility:"visible"})

		// Detect default dialog template.
		this._content = $("#dlg_body", this.element)[0];
		jQuery.each(this._buttons, function(key, button){
			button.element = $("#dlg_" + key, obj.element)[0];
			if (button.element) {
				$(button.element).attr("op", key);
			}
			$("[op=" + key + "]", obj.element).click(function(ev) {
				var exeDefault = true;
				if (button.callback)
					exeDefault = button.callback(key);
				// use sequence to ensure closing the messageBox opened by this procedure.
				if (exeDefault !== false)
					obj.close();
			});
		});
	},
	getHandler : function() {
		return this.element;
	},
	resetTimeout: function(timeout, timeoutCallback) {
		// clear old timeout
		if (this.timeoutId) {
			window.clearTimeout(this.timeoutId);
			this.timeoutId = 0;
		}

		// no timeout
		if (timeout == -1)
			return;

		// reset timeout
		if (!timeout) {
			timeout = ZXC.UI.Dialog.DEFAULT_TIMEOUT;
		}
		else if ("function" == typeof timeout) {
			timeoutCallback = timeout;
			timeout = ZXC.UI.Dialog.DEFAULT_TIMEOUT;
		}

		// set default timeoutCallback
		var obj = this;
		if (!timeoutCallback)
			timeoutCallback = function() {
				ZXC.UI.Dialog.alert(obj.resource("INFO_TIMEOUT"));
			};

		// set timeout
		this.timeoutId = window.setTimeout(timeoutCallback, timeout);
	},
	show: function(modal) {
		this.modal = modal !== false ? true : false;

		// If dialog block owns dlg_body block according to protocol, handle it.
		if (this._content) {
			this._borrowContent(arguments[1] || "");
		}

		// databinding
		try {
			if (!this.status.binded) {
				$(this.element).trigger("databind");
				this.status.binded = true;
			}
		}
		catch (ex) {
			if (this._content) {
				this._borrowContent("reset");
			}
			var msg = ex.message ? ex.message : this.resource("ERROR_DATABIND");
			ZXC.UI.Dialog.alert(msg);
			return false;
		}

		// Arbitary dimesion support.
		var dim = ['Width', 'Height'];
		for (var i = 0; i < dim.length; i++) {
			var val = this.options[dim[i].toLowerCase()];
			if (val != 0) {
				$(this.container).dialog( 'option', dim[i].toLowerCase(),
					val > 0 ? val : (ZXC.util['getInner' + dim[i]]() - 50));
			}
		}

		// IE compatible hack
		if ($.browser.msie && $.browser.version < 8.0) {
			var tc = $(document.createElement("div"));
			tc.css({visibility:"hidden",position:"absolute"})
				.attr("class", $(this.element).attr("class")) // copy "class" to ensure style correctness.
				.append($(this.element).children().clone())
				.appendTo(document.body);
			for (var i = 0; i < dim.length; i++) {
				var minVal = ZXC.util.noUnitCss(this._content, "min" + dim[i]);
				if (minVal > 0) {
					$(this._content).css(dim[i].toLowerCase(), "");
					if ($(this._content)[dim[i].toLowerCase()]() < minVal) {
						$(this._content)[dim[i].toLowerCase()](minVal);
					}
				}
			}
			var width = Math.max(ZXC.util.width(tc), 200);
			tc.remove();
			$(this.container).dialog( 'option', 'width', width);
		}

		if (this.modal) {
			// add dialogs to modal stack for restore
			var stack = ZXC.UI.Dialog._modalStack;
			var last = stack.pop();
			if (last && last.guid != this.guid) {
				stack.push(last);
				// close last message
				if (last.status.type == ZXC.UI.Dialog.TYPE_MESSAGE) {
					last.close();
				}
				else if (!this.options.overlap){
					// avoid overlapping on screen.
					last.status.hidden = true;
					$(last.container).dialog( 'close' );
				}
			}
			stack.push(this);
		}

		this.invalid = false;
		this.status.hidden = false;
		$(this.container).dialog( 'option', 'modal', this.modal )
			.dialog( 'option', 'dialogClass', this.options.alterClass || "")
			.dialog('option','title',this.options.title)
			.dialog( 'open' );

		this.onDisplayed();
		return this.getHandler();
	},
	close: function() {
		if (this.invalid) return;
		// reset timeout;
		this.resetTimeout(-1);

		if (this.modal) {
			var stack = ZXC.UI.Dialog._modalStack;
			var last = stack.pop();
			stack.push(last);
			// if not top dialog, just flag and go.
			if (last.guid == this.guid) {
				$(this.container).dialog( 'close' );
			}
			else {
				this.invalid = true;
			}
		}
		else {
			$(this.container).dialog( 'close' );
		}
	},
	useDialogType: function (type, callbacks) {
		// callback start index
		var diagtype = type;
		var cbstart = 1;
		if (!type || "function" == typeof type) {
			diagtype = ZXC.UI.Dialog.TYPE_OKCANCEL;
			cbstart = 0;
		}
		var labels = this.resource(diagtype);
		// no resource available? use diagtype as labels.
		if (!labels || labels.length == 0)
			labels = diagtype;
		this.status.type = labels;
		labels = labels.split(",");

		// set ui status and register callback
		var items = {
			"ok": labels[0],
			"deny" : labels[1],
			"cancel" : labels[2]};
		for (var key in items) {
			// hide unnessacary predefined buttons
			if (items[key] === undefined || items[key] == "") {
				if (this._buttons[key].element) {
					$(this._buttons[key].element).css("display", "none");
				}
				continue;
			}
			// change text of predefined buttons
			if (this._buttons[key].element) {
				$(this._buttons[key].element).css("display", "inline").val(items[key]);
			}
			// register callbacks
			this._buttons[key].callback = null;
			if (arguments[cbstart])
				this._buttons[key].callback = arguments[cbstart];
			cbstart++;
		}
	},
	applyMessageBoxTemplate : function (main) {
		if (!ZXC.UI.Dialog._messageBox)
			ZXC.UI.Dialog.initializeMessageBox();
		var newElem = $(ZXC.UI.Dialog._messageBox.element).clone();
		var main = $(main);
		$("#dlg_body", newElem).attr("id","").empty().append(main.children());
		newElem.children().appendTo(main);
		this.initialize(main[0]);
	},
	bind : function (data) {
		if (data && "function" == typeof data)
			data = data();
		if (data && "object" == typeof data){
			ZXC.util.bind(this.element, data, "eval");
		}
	},
	onDisplayed : function() { },
	_borrowContent : function(content) {
		if (!this._content) return;
		// reset
		if (this.lastData) {
			$(this.lastData).append($(this._content).children());
			this.lastData = undefined;
		}
		else {
			$(this._content).empty();
		}
		if (content == "reset") return;
		// borrow
		if (!content) return;
		if ("string" == typeof content) {
			// test for jQuery compatible selector
			try {
				if ($(document.body).find(content).length == 0) {
					throw "not a selector";
				}
			}
			catch (e) {
				$(this._content).html(content);
				return;
			}
		}
		$(this._content).append($(content).children());
		this.lastData = content;
	},
	_closeCallback : function(event, ui) {
		if (!this.invalid && this.status.hidden)
			return;

		// reset timeout;
		this.resetTimeout(-1);
		// return browered
		if (this._content) {
			this._borrowContent("reset");
		}
		this.status.binded = false;
		this.invalid = true;

		if (this.modal) {
			var stack = ZXC.UI.Dialog._modalStack;
			// pop top of stack
			stack.pop();
			if (stack.length == 0) return;

			// check next top of stack
			var last = stack.pop();
			stack.push(last);
			if (last.invalid) {
				$(last.container).dialog( 'close' );
			}
			else if (!this.options.overlap) {
				last.status.hidden = false
				$(last.container).dialog( 'open' );
			}
		}
	}
},
statics: {
	TYPE_MESSAGE: ",,",
	TYPE_OK : "TYPE_OK",
	TYPE_OKCANCEL: "TYPE_OKCANCEL",
	TYPE_YESNO: "TYPE_YESNO",
	TYPE_YESNOCANCEL: "TYPE_YESNOCANCEL",
	DEFAULT_TIMEOUT: 60000,
	getGuid : function() {
		return this._guid_sequence++;
	},
	initializeMessageBox : function(elem, options) {
		var jElement;
		if (elem) {
			jElement = jQuery(elem);
		} else {
			jElement = jQuery(document.createElement("div"));
			jElement.css({display:"none",minWidth:200})
				.html("<div id=\"dlg_body\"></div>" +
				"<div style=\"text-align:center;padding:5px 0px;\">" +
				"<input id=\"dlg_ok\" type=\"button\" value=\"\"/>" +
				"<input id=\"dlg_deny\" type=\"button\" value=\"\"/>" +
				"<input id=\"dlg_cancel\" type=\"button\" value=\"\"/>" +
				"</div>");
		}

		this._messageBox = new ZXC.UI.Dialog(jElement[0], "dlg_mb", options);
	},
	getMessageBox : function() {
		return this._messageBox;
	},
	closeMessageBox : function() {
		if (this._messageBox)
			this._messageBox.close();
	},
	registerMessageBoxType : function(type, template) {
		var tem = this._messageBox.options.template || {};
		tem[type] = template;
		this._messageBox.options.template = tem;
	},
	resetMessageBoxType : function(type) {
		var options = this._messageBox.options;
		if (this._messageBox._lastClass) {
			$(this._messageBox.element).removeClass(this._messageBox._lastClass);
			this._messageBox._lastClass = undefined;
		}
		options.title = "";
		if (options.template && options.template[type] !== undefined) {
			var template = options.template[type];
			options.title = template.title || options.title;
			$(this._messageBox.element).addClass(template.cssClass);
			this._messageBox._lastClass = template.cssClass;
			return template.dlgType || type;
		}
		return type;
	},
	message: function(msg, timeout, timeoutCallback) {
		if (!this._messageBox)
			this.initializeMessageBox();
		// reset timeout;
		this._messageBox.resetTimeout(timeout, timeoutCallback);

		// reset style
		var type = this.resetMessageBoxType(this.TYPE_MESSAGE);

		// set ui status;
		this._messageBox.useDialogType(type);

		this._messageBox.show(true, msg);
	},
	alert: function(msg, type, okCallback) {
		if (!type || "function" == typeof type)
			type = this.TYPE_OK;
		return this.confirm(msg, type, okCallback);
	},
	confirm: function(msg, type, callbacks) {
		if (!this._messageBox)
			this.initializeMessageBox();
		// reset timeout;
		this._messageBox.resetTimeout(-1);

		var args = new Array();
		for (var i = 1; i < arguments.length; i++) {
			args.push(arguments[i]);
		}
		if (!type || "function" == typeof type) {
			type = ZXC.UI.Dialog.TYPE_OKCANCEL;
			args.unshift(type);
		}

		// reset style;
		args[0] = this.resetMessageBoxType(args[0]);

		// set ui status;
		ZXC.UI.Dialog.prototype.useDialogType.apply(
			this._messageBox, args);

		var handle = this._messageBox.show(true, msg);
		return handle;
	},
	register: function(id, controller) {
		this[id] = controller;
	},
	registered: function(id) {
		return this[id] || false;
	},
	/**
	 * event : {dlgid:xxx, data:{}, addr:xxxx} or JQuery Event object.
	 */
	request: function(event, ctrljs) {
		var id,addr,options;
		// detect event data type
		if ("string" == typeof event) {
			addr = event;
		}
		else if (event.dlgid) {
			options = event;
		}
		else
		{
			try {
				event.preventDefault();
				var target = $(event.currentTarget);
				id = target.attr("id");
				addr = target.attr("href");
				addr = addr.replace(/^#/, "");
				ctrljs = ctrljs || target.attr("js");
			} catch(e) {}
		}
		// parse query
		options = options || this._parseQuery(addr);
		options.dlgid = options.dlgid || id;
		id = options.dlgid;
		if (!id) return;
		if (id.length<=4 || id.substring(0,4)!="dlg_")
			id = "dlg_" + id;

		if (event.data) {
			this.registerBindData(id, event.data, 'default');
		}

		var dlg = this[id];
		if (!dlg) {
			if (!options.addr) {
				this.alert(this.resource("ERROR_NO_UI_SRC"));
				return;
			}
			this.message(this.resource("INFO_UI_LOADING"));

			//if ctrljs is null, no script will be loaded
			var thisobj = this;
			if (ctrljs === undefined) {
				var jsrevision = (js_context && js_context.jsrevision) ? js_context.jsrevision : "";
				ctrljs = js_context.base_url + "res/js/zxc/app/udialog" + jsrevision + ".js"
			}
			if (ctrljs && !this._scriptLoaded[ctrljs]) {
				ZXC.util.loadScript(ctrljs, function() {
					thisobj._scriptLoaded[ctrljs] = true;
					thisobj.request(options, null);
				});
				return;
			}

			ZXC.util.jQueryAjaxHelper({
				url : options.addr,
				type: 'GET',
				dataType: 'json',
				data : {dlgId:id},
				success: function(data){
					if (!data.success) {
						thisobj.alert(data.message);
					}
					else if (options.callback != undefined) {
						options.callback(thisobj, data);
					}
					else if (data.dialog.callback !== undefined) {
						eval("val callback = " + data.dialog.callback);
						callback(thisobj, data);
					}
				}
			});
		}
		else {
			this.launch(id);
		}
	},
	launch: function(id, modal) {
		if (!this.registered(id)) {
			this.register(id, this);
		}
		if (!(this[id] instanceof this)) {
			var cls = this[id];
			if ("string" == typeof cls && ZXC.Defined(cls)) {
				cls = ZXC.Classes[cls];
			}
			if (cls == this) {
				this[id] = new this("#" + id);
			}
			else if ("function" == typeof cls) {
				this[id] = new cls(id);
			}
		}
		if (this[id] instanceof ZXC.UI.Dialog)
			this[id].show(modal, arguments[2]);
		else
			ZXC.util.log("ERROR", "Can't load " + this[id].toString());
	},
	registerBindData : function(id, data, key) {
		if (!this._dataRegistry[id])
			this._dataRegistry[id] = {sequence:0};

		if (key !== undefined && key != null && key != "")
			this._dataRegistry[id]["key_" + key] = data;
		else
			this._dataRegistry[id][this._dataRegistry[id].sequence++] = data;
	},
	_parseQuery: function(query) {
		var options = {};
		if (!query) {return options;}// return empty object

		query = query.split(/\??UU_/);
		options.addr = query[0];
		query = query[1];
		if (!query) {return options;}

		var pairs = query.split(/[;&]/);
		for ( var i = 0; i < pairs.length; i++ ) {
	      var keyval = pairs[i].split('=');
	      if ( !keyval) {continue;}
	      var key = unescape( keyval[0] );
	      var val = unescape( keyval[1] || "true" );
	      val = val.replace(/\+/g, ' ');
	      options[key] = val;
	   }
	   return options;
	},
	_guid_sequence : 0,
	_messageBox : null,
	_modalStack : new Array(),
	_dataRegistry : {},
	_scriptLoaded : {}
}
});