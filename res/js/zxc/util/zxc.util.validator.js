ZXC.Namespace("ZXC.util");

ZXC.util.Export("Validator");

ZXC.util.Validator = ZXC.Class({
name: "ZXC.util.Validator",
construct:
	function (settings) {
		this.settings = {};
		if (settings && settings.length)
			this.addFields(settings);
		else if (settings)
			this.addField(settings);
	},
methods: {
	addField : function (field, rule, prompt, func) {
		if (field instanceof Object)
			this.settings[field.name] = field;
		else
			this.settings[field] = {name:field, rule:rule, prompt:prompt, func:func};
	},
	addFields : function (fields) {
		if (!fields.length)
			return;

		for (var i = 0; i < fields.length; i++) {
			this.settings[fields[i].name] = fields[i];
		}
	},
	validate: function(frm) {
		if (!frm)
			frm = document.body;

		var regexp = /^\s*([_a-zA-Z0-9]+)(\[(.*)\])?\s*$/;

		// text fields
		for (field in this.settings) {
			var input = $(":input[name='" + field + "'][method!='skip']", frm);
			var val = "";
			for (var i = 0; i < input.length; i++) {
				if (input[i].tagName != "INPUT" ||
					(input[i].type != "radio" && input[i].type != "checkbox")) {
					val = $.trim(input.val());
					break;
				}
				else if (input[i].checked) {
					val = input.val();
					break;
				}
			}
			var rules = this.settings[field].rule.split("|");
			for (var j = 0; j < rules.length; j++) {
				var rule = this.Validator.Rules[rules[j].replace(regexp, "$1")];
				if (!rule)
					continue;
				// prepare variables
				var ruleExp = rule.rule;
				var errorMsg = this.resource(rule.msg);
				if (!errorMsg) errorMsg = rule.msg;
				var args = rules[j].replace(regexp, "$3");
				args = args.length == 0 ? [] : args.split(",");
				// validate
				var valid = true;
				if (ruleExp != "custom") {
					for (var k = 0; k < args.length; k++) {
						ruleExp = ruleExp.replace("@" + k +"@", args[k]);
						errorMsg = errorMsg.replace("@" + k +"@", args[k]);
					}
					valid = val.match(ruleExp);
				}
				else if (this.settings[field].func &&
					this.settings[field].func.constructor == Function) {
					args.unshift(val);
					valid = this.settings[field].func.apply(null, args);
					if (valid != true) {
						errorMsg = valid == false ? errorMsg : valid;
						valid = false;
					}
				}
				// invalid? prompt.
				if (!valid) {
					var prompt = this.settings[field].prompt ?
						this.settings[field].prompt : field;
					errorMsg = errorMsg.replace("@display@", prompt);
					ZXC.UI.Dialog.alert(errorMsg, function() {
						setTimeout(function(){input.focus();}, 100);
					});
					return false;
				}
			}
		}
		return true;
	}
},
statics: {
	Rules : {
		"custom": {rule: "custom", msg: "ERROR_VALIDATE"},
		"select": {rule: "^.+$", msg: "ERROR_SELECT"},
		"require": {rule: "^(.|\n|\r)+$", msg: "ERROR_REQUIRE"},
		"maxlength": {rule: "^(.|\n|\r){0,@0@}$", msg: "ERROR_MAXLENGTH"},
		"date": {rule: "^[0-9]{4}\\.[0-9]{1,2}\\.[0-9]{1,2}$", msg: "ERROR_DATE"},
		"url": {rule: "^[a-zA-Z0-9/~%.:_-]*$", msg: "ERROR_URL"},
		"alphaspace": {rule: "^[a-zA-Z0-9 ]*$", msg: "ERROR_ALPHASPACE"},
		"numeric": {rule: "^[0-9]*$", msg: "ERROR_NUMERIC"}
	}
}
});