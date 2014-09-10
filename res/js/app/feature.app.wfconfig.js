var js_context;
if (js_context.base_url === undefined) {
	js_context.base_url = "http://" + window.location.host + "/";

}
if (js_context.res_url === undefined) {
	js_context.res_url = js_context.base_url + "feature/res/";
}

if (js_context.waterfall === undefined) {
	js_context.waterfall = js_context.base_url + "waterfall/waterfall";
}