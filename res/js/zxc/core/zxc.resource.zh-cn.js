/**
 * ZXC zh-cn resource file
 *
 */

ZXC.Require("ZXC.Resource");

ZXC.Resource.register("zh-cn", {
	"thickbox": {
		LABEL_ESC_CLOSE: "　"
	},
	"ZXC.UI.Dialog": {
		TYPE_OK: "确定,,",
		TYPE_OKCANCEL: "确定,,取消",
		TYPE_YESNO: "是,否,",
		TYPE_YESNOCANCEL: "是,否,取消",
		INFO_TIMEOUT: "操作超时",
		INFO_UI_LOADING: "正在加载界面，请稍等……",
		ERROR_DATABIND: "无法加载数据，请稍候再试。",
		ERROR_NO_UI_SRC: "无法加载界面:界面资源未指定。"
	},
	"ZXC.UI.Panel": {
		MESSAGEBOX_LABEL_OK: "确定",
		MESSAGEBOX_TYPE_OKCANCEL: "确定,,取消",
		MESSAGEBOX_TYPE_YESNO: "是,否,",
		MESSAGEBOX_TYPE_YESNOCANCEL: "是,否,取消",
		INFO_MESSAGEBOX_TIMEOUT: "操作超时"
	},
	"ZXC.util.Validator": {
		ERROR_VALIDATE: "@display@不正确，请验证您的输入",
		ERROR_SELECT: "您需要选择@display@才能继续",
		ERROR_REQUIRE: "您需要填写@display@才能继续",
		ERROR_MAXLENGTH: "请将@display@限制在@0@字内",
		ERROR_DATE: "您输入的@display@系统无法接受，请按YYYY.MM.DD格式输入",
		ERROR_URL: "@display@包含非法URL字符",
		ERROR_ALPHASPACE: "@display@只能是字母与空格的组合",
		ERROR_NUMERIC: "@display@只能是数字"
	},
	"ZXC.App.Album": {
		FIELD_ALBUM_TITLE: "标题",
		FIELD_ALBUM_BODY: "卷首语",
		FIELD_ALBUM_TAG: "标签",
		FIELD_DATE: "日期",
		LABEL_DEFAULT_DATE: "无拍摄日期",
		LABEL_ALBUM_SELECTION: "选择相册",
		LABEL_ALBUM_NEW: "新建相册",
		INFO_LOADING: "数据加载中……",
		INFO_SAVING: "正在将数据保存到服务器……",
		INFO_SAVE_SUCCEEDED: "保存成功",
		INFO_DELETE_CONFIRM: "您真的要删除吗，无法恢复的哟？<br/>（您的照片不会被删除）",
		INFO_DELETING: "正在删除……",
		INFO_DELETE_SUCCEEDED: "删除成功",
		INFO_UNLOAD_CONFIRM: "您已经更改了相册内容，是否保存并继续？",
		ERROR_INVALID_RESPONSE: "无效的服务器响应",
		ERROR_LOADADDRESS_NOT_SET: "尚未指定加载地址，无法继续",
		ERROR_LOAD_FAILED: "加载失败",
		ERROR_SAVE_FAILED: "保存失败",
		ERROR_DELETE_FAILED: "删除失败",
		ERROR_PHOTO_REQUIRED: "请先选择照片！"
	},
	"ZXC.App.Dialog.PhotoEditorDialog": {
		FIELD_PHOTO_TITLE: "标题",
		FIELD_PHOTO_BODY: "描述",
		INFO_LOADING: "数据加载中……",
		ERROR_INVALID_RESPONSE: "无效的服务器响应",
		ERROR_PARAM_MISSING: "参数缺失，无法加载",
		ERROR_DATA_NOT_FOUND: "数据不存在，加载失败",
		ERROR_LOAD_FAILED: "加载失败"
	},
	"ZXC.App.ThemeSelector": {
		FIELD_BG_MUSIC_ADDR: "背景音乐地址"
	},
	"ZXC.App.MagicAdmin": {
		INFO_DEFAULT_CONFIRM: "请确认\"@display@\"操作",
		INFO_LOADING: "正在加载管理项……",
		INFO_LOAD_COMPLETED: "加载管理项成功",
		ERROR_LOAD_FAILED: "加载管理项失败",
		ERROR_NOOPTIONS: "无管理项"
	},
	"ZXC.UI.Progressable": {
		INFO_PREPARING: "正在准备数据……",
		INFO_ITEM_PROCESSING: "正在处理第@current@项，总共@total@项……",
		INFO_ITEM_ERROR: "处理失败。",
		INFO_ITEM_OP_SKIP: "跳过此项。",
		INFO_ITEM_OP_BREAK: "中断处理。",
		INFO_COMPLETE: "完成"
	},
	"ZXC.App.Dialog.BatchDialog" : {
		ERROR_PHOTO_REQUIRED: "请先选择照片！",
		INFO_BATCH_SUCCESS: "操作成功。",
		INFO_BATCH_PARTSUCCESS: "操作失败（已完成@num@张照片）：<br/>@msg@",
		INFO_BATCH_FAIL: "操作失败：<br/>@msg@",
		INFO_UPDATING: "正在更新数据……"
	},
	"ZXC.App.Dialog.BatchUpdGeoDialog": {
		FIELD_GEOTAG: "拍摄地"
	},
	"ZXC.App.Dialog.BatchUpdTimeDialog": {
		FIELD_DATE: "日期"
	},
	"ZXC.App.Dialog.BatchTagDialog": {
		FIELD_TAG: "标签"
	},
	"ZXC.App.Dialog.BatchDelDialog": {
		INFO_DELETE_CONFIRM: "您真的要删除这些照片吗，无法恢复的哟？"
	},
	"ZXC.App.Dialog.ShareDialog": {
		FIELD_GROUP: "圈子",
		FIELD_TAG: "标签"
	},
	"ZXC.App.Dialog.NewGeotagDialog": {
		TYPE_START: "下一步,,取消",
		TYPE_NORMAL: "下一步,上一步,取消",
		TYPE_COMPLETE: "确定,,取消",
		FIELD_GEOTAG: "新地方所属地域",
		FIELD_COUNTRY: "新地方所属国家"
	},
	"ZXC.App.Dialog.FavoriteDialog": {
		TYPE_SAVECANCEL: "保存,,取消",
		INFO_SAVE_SUCCESS: "保存成功",
		INFO_ADD_SUCCESS: "收藏成功",
		LINK_FAVO_MANAGE: "管理收藏"
	},
	"ZXC.App.Dialog.ManageTagDialog": {
		FIELD_TAG: "标签"
	},
	"ZXC.App.Dialog.ReportLatlngDialog": {
		FIELD_LONGITUDE: "经度",
		FIELD_LATITUDE: "纬度"
	},
	"ZXC.App.BatchPool": {
		ERROR_PHOTO_REQUIRED: "请先选择照片！"
	},
	"ZXC.App.UserMenu": {
		ITEM_SPACE: "空间",
		ITEM_FOOTPRINT: "足迹",
		ITEM_FOTOLOG: "相册",
		ITEM_ARTICLE: "文章",
		ITEM_GROUP: "圈子",
		ITEM_FRIEND: "好友",
		ITEM_BROADCAST: "广播",
		ITEM_FAVORITE: "收藏",
		ITEM_SEND_MESSAGE: "发送短消息",
		ITEM_LEAVEWORDS: "给TA留言"
	},
	"ZXC.App.FotologPicShow": {
		INFO_NOCOMMENT_INPUT: "您还没有输入评论的内容。",
		INFO_COMMENT_SAVING: "正在保存评论信息，请稍候……",
		LABEL_GEOTAG: "拍摄地：",
		LABEL_COMMENT_COUNT: "条评论",
		LABEL_SHOW_SMALL_PHOTOS: "显示小图",
		LABEL_SHOW_LARGE_PHOTOS: "显示大图",
		LABEL_HIDE_COMMENTS: "隐藏评论",
		LABEL_SHOW_COMMENTS: "查看评论",
		INFO_PAGINATION: "共%s张照片 第 %s/%s 页 每页 %s 张",
		FEATURE_INFO: "这张照片被收入专题：",
		INFO_MORE_COMMENTS: "更多评论...",
		ERROR_GET_COMMENT: "出错啦，请%s重试%s！"
	},
	"error_report": {
		INFO_DETAIL_TITLE: "<br /><br />详细的异常信息如下（仅供参考） <br />",
		INFO_DETAIL_INFO: "非常抱歉，在刚才的操作过程中系统产生一个错误，您可以 <br />1. 点击“关闭”，重新操作一下 <br />2. 点击“查看”，查看一下错误的详细信息 <br />3. 点击“报告”，将错误信息发送给UUTUU，我们的工作人员将尽快与您联系帮助您解决问题 ",
		LABEL_CLOSE: "关闭",
		LABEL_VIEW: "查看",
		LABEL_REPORT: "报告",
		INFO_MAIL_SENDING: "正在发送报告，请稍候……",
		INFO_MAIL_SENT: "发送错误报告成功，非常感谢您对UUTUU的支持"
	},
	"ZXC.App.Dialog.AddImpressionDialog": {
		ITEM_IMPRESS: "印象",
		ITEM_IMPRESS_GOOD: "好印象",
		ITEM_IMPRESS_BAD: "差印象",
		ITEM_IMPRESS_OK_BTN: "添加印象",
		ITEM_IMPRESS_MODIFY_BTN: "修改我的印象",
		ITEM_FAVOR: "评价",
		ITEM_FAVOR_GOOD: "喜爱",
		ITEM_FAVOR_BAD: "不喜爱",
		ITEM_FAVOR_OK_BTN: "添加评价",
		ITEM_FAVOR_MODIFY_BTN: "修改我的评价",
		INFO_MTITLE: "你已经对%s留下过%s",
		INFO_DESTITLE: "%s描述",
		INFO_TITLE: "请留下你对%s的%s",
		INFO_BEENTO_TITLE: "现在你马上可以留下你对%s的%s",
		INFO_ATTENTION: "注：留下印象后，我们将把“%s”加入到您去过的目的地。",
		INFO_BEENTO: "“%s”已被加入到您去过的目的地",
		INFO_FOOTPRINT: "您还可以进入 %s 管理您去过的地方",
		INFO_DATA_SENDING: "正在提交数据...",
		INFO_PAGE_FRESHING: "正在刷新页面...",
		ERROR_SCORE_EMPTY: "请选择你对%s的%s",
		ERROR_COMMENT_TOO_LONG: "描述不多于500个字符",
		ERROR_COMMENT_EMPTY: "描述必须填写"
	},
	

	
	INFO_DATA_LOADING: "正在加载数据，请稍等……",
	INFO_REQUESTING: "请稍等，正在提交请求……",
	INFO_UI_LOADED: "界面已加载，正在启动……",
	ERROR_REQUEST_TIMEOUT: "请求超时",
	ERROR_REQUEST_PARSEERROR: "返回数据异常",
	ERROR_REQUEST_FAILED: "请求失败",
	LABEL_ANONYMOUS: "匿名",
	LABEL_CLOSE: "关闭"
}, true);