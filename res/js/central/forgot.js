function include(filename) {
	document.write("<scr" + "ipt language=\"JavaScript\" " +
		"type=\"text/javascript\" src=\"" + js_context.res_url +
		 filename + "\"><\/script>");
}

include("js/lib/jquery-1.7.2.js");