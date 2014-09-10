ZXC.Namespace("ZXC.Widget");

ZXC.Widget.Export("Debug");

ZXC.Widget.Debug = ZXC.Class({
	name: "ZXC.Widget.Debug",
	construct:
		function() {
			this.dragging = false;
			this.last
			this.init();
		},
	methods: {
		init: function() {
			var debugwindow = document.createElement("div");
			debugwindow.id = ZXC.Widget.Debug.DEBUG_WINDOW_ID;
			debugwindow.className = "debug_window";
			$(document.body).append($(debugwindow));
			
			var debugtitle = document.createElement("div");
			debugtitle.id = ZXC.Widget.Debug.DEBUG_TITLE_ID;
			debugtitle.className = "debug_title";
			debugtitle.innerHTML = "<span style='margin-left:5px;font-weight:bold;'>调试信息</span><span style='margin-left:5px;'><a id='debugToggleDetail' href='#toggleDetail' style='display:none;'>隐藏详细信息</a></span><span style='margin-left:5px;'><a id='pauseDebugEntry' href='#pauseDebug' style='display:none;'>暂停(当前为：开启模式)</a></span>";
			$(debugwindow).append($(debugtitle));
			
			var debugpanel = document.createElement("div");
			debugpanel.id = ZXC.Widget.Debug.DEBUG_PANEL_ID;
			debugpanel.className = "debug_panel";
			$(debugwindow).append($(debugpanel));
			
			window.setTimeout(this.initAdvanceOpt,3000);
		},
		initAdvanceOpt: function() {
			$("#" + ZXC.Widget.Debug.DEBUG_WINDOW_ID).draggable({
				appendTo: $(document.body),
				handle: $("#" + ZXC.Widget.Debug.DEBUG_TITLE_ID),
				opacity: 0.7,
				tolerance: 'pointer',
				revert: false,
				start: function (e, ui) {
					$(document.body).trigger("mousedown");
				},
				drag: function (e, ui) {
					$(document.body).trigger("mousemove", [e]);
				},
				stop: function (e, ui) {
					$(document.body).trigger("mouseup");
				}
			});
			
			$("#debugToggleDetail").click(function(evt){
				evt.preventDefault();
				if ($("#" + ZXC.Widget.Debug.DEBUG_PANEL_ID).css("display") == "none") {
					$("#" + ZXC.Widget.Debug.DEBUG_PANEL_ID).show();
					$(this).html("隐藏详细信息");
				} else {
					$("#" + ZXC.Widget.Debug.DEBUG_PANEL_ID).hide();
					$(this).html("显示详细信息");
				}
			}).show();
			
			$("#pauseDebugEntry").click(function(evt){
				evt.preventDefault();
				if ((typeof js_context.debug == "undefined") || !js_context.debug) {
					js_context.debug = true;
					$(this).html("暂停(当前为：开启模式)");
				} else {
					js_context.debug = false;
					$(this).html("开启(当前为：暂停模式)");
				}
			}).show();
		}
	},
	statics: {
		DEBUG_AUTO_SCROLL: true,
		DEBUG_WINDOW_ID: "ZXCDebugWindow",
		DEBUG_PANEL_ID: "ZXCDebugPanel",
		DEBUG_TITLE_ID: "ZXCDebugTitle",
		log_message: function(str) {
			var debugpanel = $("#" + ZXC.Widget.Debug.DEBUG_PANEL_ID);
			
			if (debugpanel.length == 0)
			{
				new ZXC.Widget.Debug();
				window.setTimeout(function(){
					ZXC.Widget.Debug.log_message(str);
				},20);
			}
			else if (debugpanel.length == 1)
			{
				debugpanel.append("<div>" + str + "</div>");
				if (ZXC.Widget.Debug.DEBUG_AUTO_SCROLL)
					debugpanel[0].scrollTop = debugpanel[0].scrollHeight;
			}
		}
	}
});