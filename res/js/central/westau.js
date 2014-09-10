function include(filename) {
	document.write("<scr" + "ipt language=\"JavaScript\" " +
		"type=\"text/javascript\" src=\"" + js_context.base_url + "res/" +
		 filename + "\"><\/script>");
}

include("js/lib/jquery-1.7.2.js");

// ZXC
include("js/zxc/core/zxc.js");
include("js/zxc/core/zxc.resource.js");
include("js/zxc/core/zxc.resource.zh-cn.js");
include("js/zxc/util/zxc.util.js");
include("js/zxc/ui/zxc.ui.panel.js");
include("js/zxc/ui/zxc.ui.scroller.js");
include("js/zxc/ui/zxc.ui.container.js");

// Page
include("js/page/page.js");
include("js/page/page.westau.js");
include("js/app/yanzi.app.weibo.js");
include("js/page/page.finalize.js");