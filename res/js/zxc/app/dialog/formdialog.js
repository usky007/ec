ZXC.Require("ZXC.UI.Dialog");
ZXC.Namespace("ZXC.App.Dialog");

ZXC.App.Dialog.FormDialog = ZXC.Class({
name: "ZXC.App.Dialog.FormDialog",
extend: ZXC.UI.Dialog,
construct:
	function (id) {
		if (!id || $("#"+id).length == 0) return;
		var obj = this;
		this.validator = new ZXC.util.Validator();
		this.Dialog();
		this.Dialog.prototype.applyMessageBoxTemplate.apply(this, ["#"+id]);
		this.Dialog.prototype.useDialogType.apply(this, [function() {
			return obj.ok();
		}]);
		$("form", this.element).submit(function(ev) {
			ev.preventDefault();
			$(obj.buttons.ok.element).trigger("click");
			return false;
		});
	},
methods: {
	bind :  function(data) {
		if (data && "function" == typeof data)
			data = data();
		if (data && "object" == typeof data){
			ZXC.util.bind(this.element, data, "eval");
		}
	},
	ok : function() {
		var obj = this;
		var frm = $("form", this.element);
		var addr = frm.attr("action");
		if (!addr)
			return;

		if (!this.validator.validate(frm[0]))
			return false;

		var option = ZXC.util.buildFormData(frm);
		ZXC.UI.Dialog.message(this.resource("INFO_REQUESTING"));
		ZXC.util.jQueryAjaxHelper({
			url : option.url,
			type: 'POST',
			dataType: 'json',
			data : option.data,
            error: function(request, status, ex) {
                if (obj.onError) {
                    obj.onError(request, status, ex);
                    return;
                }
                ZXC.UI.Dialog.alert(ex.message || ex);
            },
			success : function (data) {
                if (!data || !data.success)
					throw new Error(data  ? data.message : "");
                obj.onSuccess(data);
			}
		});
		return true;
	},
    onError : undefined,
	onSuccess : function(data) {
		ZXC.UI.Dialog.getMessageBox().close();
	}
}
});
