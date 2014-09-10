function pic_updating(){
	$("#div_uploadpic").html($("#updatePicText").html());
	$("#div_uploadpic").show();
};

function insertAttach()
{
	$('#upform').submit();
}
function delAttach()
{
	$("#hidpicurl").val('');
	$("#div_uploadpic").hide();
	$("#div_uploadpic").html('');
	
}

Page.onPageLoad.add(function(){
	
	var $w = $(window), $p = $('#posts'), $d = $(document);
	var mo = {
		columnWidth: 205,
		itemSelector: '.post',
		saveOptions: false,
		resizeable: true,
		animate: true,
	};
	
	var checkSharedUnread = function() {
		var interval = setInterval(function(){
			var unread = $.cookie("unread");
			if (!unread) {
				clearInterval(interval);
				$(".unread").trigger('checkUnread');
				return;
			}
			
			unread = unread.split(",");
			var new_status = 0;
			if (parseInt(unread[1]) < js_context.last_status_id) {
				new_status = 1;
			}
			else if (unread[1] == js_context.last_status_id) {
				new_status = unread[0];
			}
			
			if (new_status != 0) {
				ZXC.util.bind($(".unread")[0], {new_status:'(有新微博)'}, "eval");
				clearInterval(interval);
			}
		}, 20000);
	};

	$(".unread").bind('checkUnread', function(){
		var block = $(this);
		var timer = setTimeout(function() {
			block.trigger("checkUnread");
		}, 30000);
		
		ZXC.util.jQueryAjaxHelper({
			url: block.attr("href"),
			data: js_context.last_status_id ? {'last_status_id':js_context.last_status_id} : null,
			type: 'get',
			dataType: 'json',
//		    error: function(request, status, ex) {
//		    },
			success : function (data) {
				if (!data.success)
					return;
					
				clearTimeout(timer);
					
				var formatted = {};
				data = data.unread;
				
				if (data.new_status !== undefined) {
					var timeout = (data.timeout || 60) * 1000;
					if (data.new_status == 0) {
						if (!data.yield) {
							timer = setTimeout(function() {
								block.trigger("checkUnread");
							}, timeout);
						} else {
							checkSharedUnread();
						}
					}
					
					// rewrite cookie;
					//$.cookie("unread", null);
					$.cookie("unread", data.new_status + "," + js_context.last_status_id, timeout);
					
					delete data.yield;
					delete data.timeout;
					
					formatted.new_status = data.new_status ? '(有新微博)' : "";
					delete data.new_status
				}
			
				for (var key in data) {
					formatted[key] = data[key] ? ('(' + data[key] + '新)') : "";
				}
				ZXC.util.bind(block[0], formatted, "eval");
			}
		});
	}).trigger('checkUnread');
	
	//ZXC.util.log("debug", "resize on document ready");
	var adjust = function () {
		$p.masonry(mo).css({ opacity: 1 });
	}	
	var t;
	$w.bind("invalidateLayout", function(event) {
		adjust();
	}).bind('smartresize', function(){

//		$("body").ezBgResize({
//			img     : "images/gray.jpg"
//		});
		
		wid = screen.width;

		if (window.innerWidth != undefined) { // All browsers but IE
    		wid =  window.innerWidth;
		}
		else if (document.documentElement && document.documentElement.clientWidth != undefined) {
    		// These functions are for IE 6 when there is a DOCTYPE
    		wid = document.documentElement.clientWidth;
		}
		else if (document.body.clientWidth != undefined) {
    		// These are for IE4, IE5, and IE6 without a DOCTYPE
    		wid = document.body.clientWidth;
 		}

//		alert( wid );			
		wid2 =  Math.floor(( wid - 175 ) / 410 ) * 410 + 156;
		$('#blogger').width( wid2 + 'px' );
		$('#blogger').css('margin', '10px auto');
		
		wid3 = wid2;
		$('#content').width( wid3 + 'px' );
		$('#content').css('margin', '15px auto 20px');
		
		$('.copyright').width( wid3 + 'px' );
		
		right1 = Math.floor((wid - wid2)/2) - 6;
		$('#sidebar').css('right', right1 + 'px');
		
		wid = wid2 - 156;
		$('#posts').width( wid + 'px');
		wid = wid - 15;
		$('#footnav').width( wid + 'px');
		
		if (t)	clearTimeout(t);
		t = setTimeout(function(){
			adjust();
		}, 300);
		
		ZXC.util.locateInnerFix($("#sidebar_pos"), 10, 0, {id:"sidebar"});
	});
	
	$w.trigger("smartresize");
	var timer = setInterval(adjust, 2500);
	$w.load(function(){
		clearInterval( timer );
		//ZXC.util.log("debug", "resize on windows load");
		adjust();
	});
	
	ZXC.Import("YANZI.App.Weibo");
 
	var newTwtCtrl = new Weibo();
	
 
	
	/*$('.fwd_report').click(function() {
		
		var ftext = $(this).attr('ft');
		var sid = $(this).attr('id');
		$('#forwardtwittertext').html(ftext);
		$('#fwtwitter').skygqbox({
			title: '转发微博'
		});
		$('#forwardsid').attr('value', sid);
		
		fwdTwtCtrl.resetblock($('#fwtwitter'));
		$('#forwardtwittertext').focus();
		
	});*/
 
	
	ZXC.Import("YANZI.App.StatusBlock");
	ZXC.Import("YANZI.App.FeatureBlock");
	$(".post:not(.feature)").each(function() {
		this.controller = new StatusBlock(this);
	});
	$(".feature").each(function() {
		this.controller = new FeatureBlock(this);
	});
	

	$('#upform').bind("submit",function()
	{
		pic_updating();
		return true;
	});
	
	$('#uppic').change(function(){
		$('#upform').submit();
	});
	
	$('focusblock a').click(function(event){
		event.preventDefault();
		var target = $(event.currentTarget);
		
		if (target.attr("processing") == "1") {
			return;
		}
		
		target.attr("processing", "1");
		var identity = target.attr("identity");
		var url = target.attr("href").replace(/^#/, "");
		var text = $("span", target).text();
		$("span", target).text("设置中...");
		
		ZXC.util.jQueryAjaxHelper({
			url: url,
			data: {identity:identity},
			type: 'POST',
			dataType: 'json',
			success: function (data) {
				if (!data.success)
					return;
				
				target.parent().attr("class", data.cssClass);
			},
			complete: function() {
				$("span", target).text(text);
				target.attr("processing", "");
			}
		});
	});
	
	
});


TopUp.host = js_context.res_url;
TopUp.images_path = "images/top_up/";
TopUp.players_path = "flv/top_up/";