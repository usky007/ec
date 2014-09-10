/**
 * This module provide progressable ability for ui-server interaction.
 *
 *
 */
ZXC.Require("ZXC.util");
ZXC.Require("ZXC.UI.Dialog");

ZXC.Namespace("ZXC.UI");

ZXC.UI.Export("Progressable");

/**
 * Usage:
 * 	 jQuery("#id").click(ZXC.util.progressable(
 *     function(progressableObj, index, completeCallback){}, 10, options));
 * or
 *   var progressable = new ZXC.UI.Progressable(null, 10, options);
 *   progressable.start();
 *   progressable.setProgress(progress, prompt);
 *   progressable.end();
 *
 * Supported options including:
 * cssClass :     Css class for progress interface, see test/jsexample/progressable
 *                for css configuration, default "progress-ui".
 * nobreak :      Continue to process on error, default false.
 * endCallback :  Callback function when end() is called or process end.
 *                Arguments:statistic
 * customProgress:No default progress prompt will be displayed, default false.
 */
ZXC.UI.Progressable = ZXC.Class({
name: "ZXC.UI.Progressable",
construct:
	function(processFunc, repeats, options) {
		this.func = processFunc;
		this.max = repeats;
		this.dialog
		this.options = {
			cssClass : "progress-ui",
			nobreak : false,
			endCallback : null,
			customProgress: false
		};
		this.statistic = {
			total : repeats,
			succeeded : 0,
			failed  : 0,
			finished : 0,
			laseError: null
		};
		this.handlers = {
			container : null,
			prompt : null,
			progress : null
		};

		if (!options || options.constructor != Object)
			return;
		for (key in this.options) {
			if (options[key] !== undefined)
				this.options[key] = options[key];
		}
	},
methods: {
	proceed : function (progress) {
		if (!progress)
		{
			progress = 0;
			this.start();
		}
		else if (progress == this.max)
		{
			this.end();
			return;
		}

		var obj = this;
		for (var i = progress; i < this.max; i++)
		{
			try {
				this.setProgress(null,
					this.resource("INFO_ITEM_PROCESSING")
					.replace("@current@", i + 1).replace("@total@", this.max),
					true);
				var res = this.func(this, i, function(ex) {
					if (obj._next(i, ((ex === undefined || ex === true) ? true : false), ex))
						obj.proceed(i + 1);
					else
						obj.end();
				});
				// nonblock process
				if (res === false)
					return;
				this._next(i, true);
			}
			catch (ex) {
				if (!this._next(i, false, ex))
					break;
			}
		}
		setTimeout(function() {obj.end()}, 500);
	},
	start : function() {
		this.statistic.succeeded = this.statistic.failed = this.statistic.finished = 0;

		this.handlers.container = document.createElement("div");
		var element = document.createElement("div");
		$(element).attr("class", this.options.cssClass)
			.appendTo(this.handlers.container);

		this.handlers.prompt = document.createElement("div");
		$(this.handlers.prompt).attr("class", "prompt")
		.html(this.resource("INFO_PREPARING"))
		.appendTo(element);

		var border = document.createElement("div");
		$(border).attr("class", "border").appendTo(element);

		this.handlers.progress = document.createElement("div");
		$(this.handlers.progress).attr("class", "progress")
			.width("0%").appendTo(border);

		ZXC.UI.Dialog.message(this.handlers.container, -1);
	},
	end : function() {
		if (this.statistic.finished == this.statistic.total)
			this.setProgress(null, this.resource("INFO_COMPLETE"), true);

		if (this.options.endCallback)
			this.options.endCallback(this.statistic);
		else {
			ZXC.UI.Dialog.getMessageBox().close();
			ZXC.UI.Dialog.alert(this.handlers.container);
		}

		this.handlers = {};
	},
	setProgress : function (progress, label, customCheck) {
		if (customCheck && this.customProgress)
			return;

		if (this.handlers.prompt && label)
			$(this.handlers.prompt).html(label);
		if (this.handlers.progress && progress)
		{
			this.statistic.finished = progress;
			$(this.handlers.progress).width(
				Math.round(100 * progress / this.statistic.total).toString() + "%");
		}
	},
	// return true to continue;
	_next: function(progress, success, ex) {
		if (success) {
			this.statistic.succeeded++;
			this.setProgress(progress + 1, null, true);
			return true;
		}
		else {
			this.statistic.failed++;
			var errorMsg = (ex && ex.message && ex.message.length != 0) ?
				ex.message : this.resource("INFO_ITEM_ERROR");
			this.statistic.lastError = errorMsg;
			errorMsg += this.options.nobreak ?
				this.resource("INFO_ITEM_OP_SKIP") :
				this.resource("INFO_ITEM_OP_BREAK");
			this.setProgress(this.options.nobreak ? (progress + 1) : null, errorMsg, true);

			return this.options.nobreak;
		}
	}
},
statics: {
	install : function (processFunc, repeats, options) {
		var obj = new ZXC.UI.Progressable(processFunc, repeats, options);
		return function () { obj.proceed(); }
	}
}
});

ZXC.util.progressable = ZXC.UI.Progressable.install;
