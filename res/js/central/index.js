function include(filename) {
	document.write("<scr" + "ipt language=\"JavaScript\" " +
		"type=\"text/javascript\" src=\"" + js_context.res_url +
		 filename + "\"><\/script>");
}

include("js/lib/jquery-1.7.2.js");
include("js/lib/tools/scrollable.js");
include("js/lib/doT.js");
// ZXC
include("js/zxc/core/zxc.js");
include("js/zxc/core/zxc.resource.js");
include("js/zxc/core/zxc.resource.zh-cn.js");
include("js/zxc/util/zxc.util.js");
include("js/zxc/ui/zxc.ui.panel.js");
include("js/zxc/ui/zxc.ui.container.js");
include("js/zxc/ui/zxc.ui.scroller.js");

include("js/zxc/widget/zxc.widget.dotemplate.js");
include("js/api/yanzi.api.js");
include("js/zxc/widget/zxc.widget.suggestion.js");
// page
include("js/page/page.js");
//include("js/app/yanzi.app.askpanel.js");
include("js/page/page.index.js");
include("js/page/page.finalize.js");

