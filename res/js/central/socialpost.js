function include(filename) {
	document.write("<scr" + "ipt language=\"JavaScript\" " +
		"type=\"text/javascript\" src=\"" + js_context.res_url +
		 filename + "\"><\/script>");
}

include("js/lib/jquery-1.7.2.js");
include("js/lib/tools/scrollable.js");

// ZXC
include("js/zxc/core/zxc.js");
include("js/zxc/core/zxc.resource.js");
include("js/zxc/core/zxc.resource.zh-cn.js");
include("js/zxc/util/zxc.util.js");

// page
include("js/page/page.js");
include("js/page/page.socialpost.js");
include("js/page/page.finalize.js");