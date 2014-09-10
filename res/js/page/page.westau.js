var _gaq;
Page.onPageLoad.add(function(){
	var lockPage = function() {
		var offset = ZXC.util.getPageYOffset();
		var oldMarginTop = ZXC.util.noUnitCss($(".bg"), "margin-top");
		$("#ad_header").css("top", -offset);
		$(".bg").addClass("lock").css("margin-top", oldMarginTop - offset).data("oldMarginTop", oldMarginTop);
		$(document.body).css("background-position", "center -" + offset + "px");
	};
	
	var unlockPage = function() {
		var offset = ZXC.util.noUnitCss($(".bg"), "margin-top");
		var oldMarginTop = $(".bg").data("oldMarginTop");
		$(document.body).css("background-position", "center 0");
		$(".bg").css("margin-top", oldMarginTop).removeClass("lock");
		$("#ad_header").css("top", 0);
		ZXC.util.pageYOffset(oldMarginTop - offset);
	};
	
	var showDialog = function(cla){
		var imgObj = arguments.length > 1 ? arguments[1] : '';
		var box_pic = arguments.length>2 ? arguments[2] : '';
		$('#TB_overlay').show();
		$('#TB_window').show();
		//微博查看大图
		if(imgObj){
			$(cla).find('.photo').find('a').remove();
			for(var i=0;i<imgObj.length;i++){
				var imgSrc = $(imgObj[i]).attr('src');
				var imgApp = "<a href='"+imgSrc+"' target='_blank'><img src='"+imgSrc+"' style='max-width:750px;'/></a>";
				$(cla).find('.photo').append(imgApp);
			}
			//TODO：：加载评论
			if(box_pic){
				var weibo = $(box_pic).parent();
				var content = $(weibo).find('.box_test a').text();
				var fb = $(weibo).find('.user_fb').clone().attr('class', 'more_user_fb');
				var el_a = fb.find('.user_fb_id').prev('a');
				fb.find('.user_fb_id').before('<div style="width:60px; float:left"></div>');
				var el_div = fb.find('.user_fb_id').prev('div');
				el_a.appendTo(el_div);
				fb.find('.user_fb_id').append(' : '+content);
				fb.find('.user_fb_id').attr('class', 'more_user_fb_id');
				var zf = $(weibo).find('.user_zf').clone().attr('class', 'more_user_zf');
				zf.find('.user_zf_id').attr('class', 'more_user_zf_id');
				$(cla).append(fb).append(zf);
				lockPage();
				$(window).trigger("windowResize", ZXC.util.WindowSizeMonitor);
				$('#TB_window').width($(cla).outerWidth());
				//TODO::弹出框是否在头部
				if($(cla).outerHeight() < $(window).height()){
					var top = ($(window).height() - $(cla).outerHeight())/2;
					$('#TB_window').css('top', top+'px');
					$(cla).find('.box_close').css('top', top+'px');
				}else{
					$('#TB_window').css('top', '0px');
					$(cla).find('.box_close').css('top', '0px');
					$('#TB_window').css('position', 'relative');
				}
				var left = ($(window).width() - $(cla).outerWidth())/2; 
				$('#TB_window').css('left', left+'px');
				$(cla).find('.box_close').css('left', left+$('#TB_window').outerWidth()+10+'px').css('position', 'fixed');
			}
		}else{
			$('#TB_window').width($(cla).outerWidth());
			if($(cla).outerHeight() >= $(window).height()){
				var top= 0;
			}else{
				var top= ($(window).height() - $(cla).outerHeight())/2;
			}
			var left = ($(window).width() - $(cla).outerWidth())/2; 
			$('#TB_window').css('top', top+'px');
			$('#TB_window').css('left', left+'px');
		}
		$(cla).show();
		$(cla).find('.box_close').click(function(){
			closeDialog(cla);
			if(box_pic){
				$(cla).find(fb).remove();
				$(cla).find(zf).remove();
				unlockPage();
				$('#TB_window').css('position', 'fixed');
				$(window).trigger("windowResize", ZXC.util.WindowSizeMonitor);
				$(cla).find('.box_close').css('left', left+$('#TB_window').outerWidth()+'px').css('position', 'fixed');
			}
		});
	}
	
	var closeDialog = function(cla){
		$('#TB_overlay').hide();
		$('#TB_window').hide();
		$(cla).hide();
	}
	
	//微博弹框查看大图
	$('.box_pic').find('a').live('click', function(e){
		e.preventDefault();
		tricker('/open_photo_dialog');
		var box_pic = $(this).parent();
		var img = $(this).find('img');
		showDialog('.photo_box', img, box_pic);
	});
	
	$('.box_zoom').find('a').live('click', function(e){
		e.preventDefault();
		showDialog('.photo_box');
	});
	
	//活动规则
	$('#rule').click(function(e){
		e.preventDefault();
		tricker('/open_rule_dialog');
		showDialog('.rule_box');
	});
	
	//我要参加
	$('#join').click(function(e){
		e.preventDefault();
		tricker('/open_join_dialog');
		showDialog('.jion_box');
	});
	
	//ajax判断是否登录
//	var showLogin = function(){
//		$.ajax({
//			url : js_context.base_url+'ajax/user/is_login',
//			type: 'POST',
//			success:function(data){
//				data = JSON.parse(data);
//				if(data.success == true){
//					if(data.is_login == false){
//						location.href = js_context.base_url+"login?return="+location.href;
//					}else{
//						showJoin();
//					}
//				}
//			}
//		});
//	}
//	
	//ajax判断是否提交了用户信息
//	var showJoin = function(){
//		$.ajax({
//			url : js_context.base_url+'ajax/user/is_join',
//			type: 'POST',
//			success:function(data){
//				data = JSON.parse(data);
//				if(data.success == true){
//					if(data.msg == '您已经提交了用户信息'){
//						alert(data.msg + ', 请勿重复提交');
//					}else{
//						showDialog('.jion_box');
//					}
//				}
//			}
//		});
//	}
	
	//join submit:
	$('#join_submit').click(function(e){
		e.preventDefault();
		tricker('/submit_userInfos');
		join_submit();
	});
	
	var tricker = function(Pageview){
		_gaq = _gaq || [];
		_gaq.push(['_trackPageview', location.href+Pageview]);
	}
	
	var join_submit = function() 
	{
		var name  = $('#realName').val();
		var email = $('#email').val();
		var weibo = $('#weibo').val();
		var mobile= $('#mobile').val();
		var reMail = /^.+@.+$/;
		$('#join_info_error_alert').html('');
		if($('#realName').css('border-color') == 'rgb(255, 0, 0)')
			$('#realName').css('border-color', '#ededed');
		if($('#email').css('border-color') == 'rgb(255, 0, 0)')
			$('#email').css('border-color', '#ededed');
		if(name == '')
		{
			$('#join_info_error_alert').html('真实姓名不能为空');
			$('#realName').css('border-color', 'red');
			return　false;
		}
		if(!reMail.test(email))
		{
			$('#join_info_error_alert').html('邮箱格式错误');
			$('#email').css('border-color', 'red');
			return false;
		}
		$.ajax({
			url: js_context.base_url+'ajax/user/s_join',
			type: 'POST',
			data:{name: name, email: email, weibo: weibo, mobile: mobile, userinfo: js_context.userinfo},
			dataType: 'json',
			success: function(data){
				if(data.success == true){
					if(data.submited){
						alert(data.msg);
						closeDialog('.jion_box');
					}else{
						$('#join_info_error_alert').html(data.msg);
					}
				}
			}
		});
	}
	
	//定义keyword链接
	var order = js_context.order;
	var keyword = js_context.keyword;
	$('.sub_nav .newhot li').find('a').removeClass('current');
	if(order == 'new'){
		$('.new').css('background-position', 'left top');
		$('.hot').css('background-position', 'left bottom');
		$('.new').find('a').addClass('current');
	}else if(order == 'hot'){
		$('.new').css('background-position', 'left bottom');
		$('.hot').css('background-position', 'left top');
		$('.hot').find('a').addClass('current');
	}
	
	//定义瀑布流
	var $num = 0;
	var loadWaterFall = true;
	var loadNum = 5
	var waterfall = function()
	{
//		console.log('num='+$num +' | loadWaterFall='+loadWaterFall);
		if(loadWaterFall){
			$('.loading').hide();
			$('#loading_more').hide();
			$('#loading_end').hide();
			if(($num+1)%loadNum == 0){ //每瀑布加载5次，出现一个 加载更多的 按钮
				loadWaterFall = false;
			}
			$('.loading').show();
			$('#loading_start').show();
			$.ajax({
				url : js_context.waterfall,
				type: 'POST',
				data: {
						'num': $num++, 
						'keyword': keyword,
						'order' : order,
						'tweet' : js_context.tweet,
						'tweetComments' : js_context.tweetComments
					  },
				dataType: 'json',
				success:function(data){
					$('.loading').hide();
					$('#loading_start').hide();
					if(data.success == true){
						var weibo,$row,iheight,temp_h;
						var weiboObj = data.weibo;
						for(var i=0;i<weiboObj.length;i++){
							iheight  =  -1;
//							console.log('i=' + i);
							$("#waterfall li").each(function(index){
								temp_h = Number($(this).height());
//								console.log(index +' : '+temp_h);
								if(iheight == -1 || iheight >temp_h){
									iheight = temp_h;
									$row = $(this); 
								}
							});
							//渲染瀑布流
							var lastBox = renderWaterFall($row, weiboObj[i]);
						}
						if(weiboObj.length < loadNum){ //如果返回微博的数目小于预期加载的数目，则显示没有更多加载项
							$('.loading').show();
							$('#loading_more').hide();
							$('#loading_end').show();
						}
						var timer = setInterval(adjust, 2500);
						lastBox.find('.box_pic').find('img').load(function(){
							setTimeout(function(){clearInterval( timer );}, 3000);
						});
					}
				}
			});
		}else{
			addLoadWaterFallHtml();
		}
	}
	
	var addLoadWaterFallHtml = function()
	{
		$('.loading').show();
		$('#loading_more').show();
		$('.loading').click(function(){
			loadWaterFall = true;
			waterfall();
		});
	}

	//TODO::最后调整瀑布流(图片加载慢，导致瀑布流显示不准确时)
	var adjust = function()
	{
		var maxHeight = 0;
		var liArr = new Array();
		var tepAr = new Array();
		$('#waterfall li').each(function(k, v){
			var h = $(v).height();
			liArr.push(h);
			tepAr.push(h);
		});
		tepAr.sort();
		var maxLi = tepAr[3];
		var minLi = tepAr[0];
		var maxIn = arraySearch(liArr, maxLi);
		var minIn = arraySearch(liArr, minLi);
		maxHeight = maxLi - minLi;
		var box = $('#waterfall li:eq('+maxIn+')').find('.box_weibo');
		var lastIn = box.length - 1;
		var lastBox = $(box[lastIn]);
		if(lastBox.height() < maxHeight){
			lastBox.appendTo($('#waterfall li:eq('+minIn+')'));
		}else{
			return ;
		}
	}
	
	var arraySearch = function(arr,val) 
	{
	    for (var i=0; i<arr.length; i++)
	    if (arr[i] == val)
	    return i;
	    return false;
	}
	
	var renderWaterFall = function(row, data)
	{
		var tweet = data.tweet;
		var comments = data.comments;
		var $row = $('<div class="box_weibo"></div>').appendTo(row);
		addPic(tweet.img, $row);
		addContent(tweet, $row);
		addComments(comments, $row);
		if(data.comments.length == 0){  //如果没有返回微博评论，原创微博不显示下边框
			$row.find('.user_fb').addClass('no_bottom');
		}
		return $row;
	}
	
	//添加微博图片
	var addPic = function(pic, $row)
	{
		var pic = JSON.parse(pic);
		var currentPic = '<div class="box_pic"><a>';
		for(var i in pic)
		{
			currentPic += '<img src="'+pic[i].thumbnail_pic+'" style="max-width:232px;"/>';
		}
		currentPic += '</a></div>';
		insert($(currentPic), $row);
	}
	
	//添加微博内容
	var addContent = function(tweet, $row)
	{
		var currentCon = '<div class="box_test" id="'+tweet.id+'"><a>'+tweet.content+'</a></div>';
		currentCon += '<div class="user_fb"><a href="http://weibo.com/'+tweet.link+'" target="_blank"><img src="'+tweet.avatar+'" width="40" height="40"/></a>';
		currentCon += '<div class="user_fb_id"><a href="http://weibo.com/'+tweet.link+'" target="_blank">'+tweet.name+'</a> 微博原创发布</div>';
		currentCon += '</div>';
		insert($(currentCon), $row);
	}
	
	//添加微博评论
	var addComments = function(comments, $row)
	{
		if(comments){
			var currentCom = '';
			for(var i in comments){
				currentCom += '<div class="user_zf" id="'+comments[i].id+'"><a href="http://weibo.com/'+comments[i].link+'" target="_blank"><img src="'+comments[i].avatar+'" width="40" height="40"/></a>';
				currentCom += '<div class="user_zf_id"><a href="http://weibo.com/'+comments[i].link+'" target="_blank">'+comments[i].name+'</a> ：'+comments[i].content+'</div>';
				currentCom += '</div>';
			}
			insert($(currentCom), $row);
		}
	}
	
	var insert = function($element, $row)
	{
		$element.css('opacity',0).appendTo($row).fadeTo(600, 1);
	}
	
	waterfall();
	var loadheight = 0;
	$(window).scroll(function()
	{
		var doc_height,s_top,now_height;
		doc_height = $(document).height(); 
		s_top = $(this).scrollTop(); 
		now_height = $(this).height(); 
		//控制  返回顶部html标签 的 显示与隐藏
		if(s_top > 0){
			$('#toolBackTop').show();
		}else{
			$('#toolBackTop').hide();
		}
		
		var minheight = 9999999999;
		$('#waterfall li').each(function(k, v){
			var t = $(v).height();
			if(t < minheight)
			{
				minheight = t;
			}
		});

		if((((s_top + now_height) - (minheight)) > 500) || ((s_top + now_height) == doc_height))
		{
			if(loadheight != minheight)
			{
				waterfall();
			}
			loadheight = minheight;
		}
	});

	// var showDialog = function(cla){
	// 	$('#TB_overlay').show();
	// 	$('#TB_window').show();
	// 	$('#TB_window').width($(cla).outerWidth());
	// 	var top= ($(window).height() - $(cla).outerHeight())/2;
	// 	var left = ($(window).width() - $(cla).outerWidth())/2; 
	// 	$('#TB_window').css('top', top+'px');
	// 	$('#TB_window').css('left', left+'px');
	// 	$(cla).show();
	// 	$(cla).find('.box_close').click(function(){
	// 		closeDialog(cla);
	// 	});
	// }
	// var closeDialog = function(cla){
	// 	$('#TB_overlay').hide();
	// 	$('#TB_window').hide();
	// 	$(cla).hide();
	// }
	
	//弹框的点击事件
	$('#menu1').click(function(e){
		e.preventDefault();
		showDialog('.menu1_box');
	});
	$('#menu2').click(function(e){
		e.preventDefault();
		showDialog('.menu2_box');
	});
	$('#menu3').click(function(e){
		e.preventDefault();
		showDialog('.menu3_box');
	});
	$('#menu4').click(function(e){
		e.preventDefault();
		showDialog('.menu4_box');
	});
	$('#menu5').click(function(e){
		e.preventDefault();
		showDialog('.menu5_box');
	});
	$('#menu6').click(function(e){
		e.preventDefault();
		showDialog('.menu6_box');
	});
	$('#menu7').click(function(e){
		e.preventDefault();
		showDialog('.menu7_box');
	});
	$('#menu8').click(function(e){
		e.preventDefault();
		showDialog('.menu8_box');
	});
	$('#menu9').click(function(e){
		e.preventDefault();
		showDialog('.menu9_box');
	});
	$('#menu10').click(function(e){
		e.preventDefault();
		showDialog('.menu10_box');
	});
	$('#prize').click(function(e){
		e.preventDefault();
		showDialog('.prize_box');
	});

	var dosomething = function(){
	    var url = location.href;
	    var regex = new RegExp('^.*#(.*)$', 'i');
	    if(regex.test(url))
	    {
	      var result = regex.exec(url);
	      if(result[1] != null)
	      {
	        $('#' + result[1]).click();
	      }
	    }
 	}
 	dosomething();
});