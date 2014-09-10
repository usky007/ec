/**
 * ZXC resource
 *
 * namespace: ZXC
 */

ZXC.Namespace("ZXC");

ZXC.Export("Resource");

ZXC.Resource = ZXC.Class({
name: "ZXC.Resource",
construct:
	function () {
		for (className in ZXC.Classes) {
			ZXC.Classes[className].prototype.resource = this;
		}
		this.lang = this.Resource.LANG_DEFAULT;
		this.library = {};
	},
methods: {
	entry: function(category, code) {
		return this.langEntry(this.lang, category, code);
	},
	langEntry: function(lang, category, code) {
		if (!this.library[lang])
			return null;

		if (!category)
			return this.library[lang][code];
		else if (this.library[lang][category] && this.library[lang][category][code] !== undefined)
			return this.library[lang][category][code];
		else if (ZXC.Classes[category] && ZXC.Classes[category].prototype.superclass)
			return this.langEntry(lang, ZXC.Classes[category].prototype.superclass.classname, code);
		else
			return this.library[lang][code];
	},
	use: function(lang) {
		if (this.library[lang])
			this.lang = lang;
	},
	register: function(lang, entries) {
		if (this.library[lang] == undefined) {
			this.library[lang] = entries;
		}
		else {
			for (var key in entries) {
				this.library[lang][key] = entries[key];
			}
		}
	}
},
statics: {
	LANG_DEFAULT: "en",
	getResource: function() {
		if (!ZXC.Resource.prototype.resource)
			ZXC.Resource.prototype.resource = new ZXC.Resource();
		return ZXC.Resource.prototype.resource;
	},
	register: function(lang, entries, apply) {
		var resource = this.getResource();
		resource.register(lang, entries);
		if (apply)
			resource.use(lang);
	}
}
});