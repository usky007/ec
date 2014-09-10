function include(filename) {
	ZXC.util.jQueryAjaxHelper({
		async: false,
		type: "GET",
		url: js_context.res_url + "js/lib/" + filename,
		dataType: "script",
		cache: true
	});
}

include("jui/jquery.ui.core.js");
include("jui/jquery.ui.draggable.js");
