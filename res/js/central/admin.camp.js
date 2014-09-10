function include(filename) {
    document.write("<scr" + "ipt language=\"JavaScript\" " +
        "type=\"text/javascript\" src=\"" + js_context.res_url +
        filename + "\"><\/script>");
}

// ZXC
include("js/zxc/core/zxc.js");
include("js/zxc/core/zxc.resource.js");
include("js/zxc/core/zxc.resource.zh-cn.js");
include("js/zxc/util/zxc.util.js");
include("js/zxc/widget/zxc.widget.suggestion.js");
// page
include("js/page/page.js");
include("js/page/page.admin.camp.js");
include("js/page/page.finalize.js");

