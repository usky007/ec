ZXC.Import("ZXC.UI.Dialog");
Page.onPageLoad.add(function(){
	// thickbox and message box initialization
	var msgbox = $(document.createElement("div"));
	msgbox.css({display:'none',minWidth:200})
		.html(
	"<div id=\"dlg_body\"></div>" +
	"<div style=\"text-align:center;padding:5px 0px 25px;\">" +
	"	<input id=\"dlg_ok\"  type=\"button\" class=\"btn_blue_80\"  value=\"\" />" +
	"	<input id=\"dlg_deny\"  type=\"button\" class=\"btn_white_80\"  value=\"\" />" +
	"	<input id=\"dlg_cancel\"  type=\"button\" class=\"btn_white_80\"  value=\"\" />" +
	"</div>").appendTo(document.body);

	var resource = ZXC.Resource.getResource();
	var template = {};
	template[Dialog.TYPE_MESSAGE] = {dlgType : Dialog.TYPE_MESSAGE, title:resource.entry("Dialog", "TITLE_DEFAULT"), cssClass:"sys_default_msgbox"},
	template[Dialog.TYPE_OK] = {dlgType : Dialog.TYPE_OK, title:resource.entry("Dialog", "TITLE_DEFAULT"), cssClass:"sys_info_msgbox"},
	template["TYPE_INFO"] = {dlgType : Dialog.TYPE_OK, title:resource.entry("Dialog", "TITLE_INFO"), cssClass:"sys_info_msgbox"},
	template["TYPE_SUCCESS"] = {dlgType : Dialog.TYPE_OK, title:resource.entry("Dialog", "TITLE_SUCCESS"), cssClass:"sys_success_msgbox"},
	template["TYPE_ERROR"] = {dlgType : Dialog.TYPE_OK, title:resource.entry("Dialog", "TITLE_ERROR"), cssClass:"sys_error_msgbox"},
	template["TYPE_OKCANCEL"] = {dlgType : Dialog.TYPE_OKCANCEL, title:resource.entry("Dialog", "TITLE_DEFAULT"), cssClass:"sys_confirm_msgbox"},
	template["TYPE_CONFIRM"] = {dlgType : Dialog.TYPE_OKCANCEL, title:resource.entry("Dialog", "TITLE_CONFIRM"), cssClass:"sys_confirm_msgbox"},
	template["TYPE_SIMPLE"] = {dlgType : Dialog.TYPE_OK, title:resource.entry("Dialog", "TITLE_DEFAULT"), cssClass:""},
	Dialog.initializeMessageBox(msgbox[0], {"template": template});
});