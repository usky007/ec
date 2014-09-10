			
			var airportLoc=[25];
			var initFunc=function(){
				var count=$(".ad_box .ad").length;
				var pointAvgWidth=955/(count-1);
				var panelAvgWidth=955/(count-1);
				for(i=2;i<=count-1;i++){
					airportLoc.push(panelAvgWidth*(i-1)-15);
					$(".tabs").append("<a href=\"#\" style=\"left:"+pointAvgWidth*(i-1)+"px;\">"+i+"</a>")
				}
				 airportLoc.push(890);
					$(".tabs").append("<a href=\"#\" style=\"left:"+950+"px;\">"+count+"</a>")
			}
			initFunc();

			 
			
			var scroll=$(".scrollable").scrollable({
				circular:true,
				speed:200,
				onBeforeSeek:function(){
					//flyto = this.getIndex()+2>this.getSize()?0:this.getIndex()+1;
					//flyto = $("div.tabs a.current").index();
					//planeFly(flyto);
				},
				onSeek:function(){
				}
			}).autoscroll({
				autoplay: true,
				interval:6000
			}).navigator({
				navi:'div.tabs',
				activeClass:'current'
			});
			
			
			
			var planeFly=function(loc){
				$(".plane img").animate({"left":airportLoc[loc]},{duration:2500})
			};
			
			/*$("div.tabs a").click(function(event){
				currnetIdx = $(this).index();
				planeFly(currnetIdx);
			})
			*/ 
//
var t;//计时器
$(".operation").toggle(function(){
	clearTimeout(t);
$(".big_ad .ad").stop().animate({"height":0},{duration:1000,step:function(val){
			$(".album_bottomcenter").css("top",$(".album_bottomleft").offset().top);
			if(val>290){
				$("#index_banner").height(val);
			}else{
				$("#index_banner").height(290);
			}
		},complete:function(){
			$(".album_bottomcenter").css("top",$(".album_bottomleft").offset().top);
		}
	});
	$(this).html("<img src='images/ad_check01.png' class='abc'>");
},function(){
	
	$(".big_ad .ad").stop().animate({"height":600},{duration:1000,step:function(val){
			$(".album_bottomcenter").css("top",$(".album_bottomleft").offset().top);
			if(val>290){
				$("#index_banner").height(val);
			}else{
				$("#index_banner").height(290);
			}
		},complete:function(){
			$(".album_bottomcenter").css("top",$(".album_bottomleft").offset().top);
			
		}
	});
	$(this).html("<img src='images/ad_check02.png' class='abc'>");
});

$(document).ready(function(){
	
		$(".ad_box .tabs a").each(function(i){
			setTimeout(function(){
				$(".ad_box .tabs a").eq(i).css("background-position","bottom");
				setTimeout(function(){
					$(".ad_box .tabs a").eq(i).css("background-position","");
					setTimeout(function(){
							$(".ad_box .tabs a").eq(i).css("background-position","bottom");
							setTimeout(function(){
								$(".ad_box .tabs a").eq(i).css("background-position","");
							},300)
					},200)
				},300)

			},2000*i);
		})
	setInterval(function(){//导航条效果
	
	//	var i=Math.floor(0+Math.random()*$(".ad_box .ad").length);
		//if($(".ad_box .tabs a:eq("+i+")").hasClass("current"))return false;
		
		//$(".ad_box .tabs a").css("background-position","bottom");
		//setTimeout(function(){
	//		$(".ad_box .tabs a").css("background-position","");
		//},1000);
		
		
		$(".ad_box .tabs a").each(function(i){
			setTimeout(function(){
				$(".ad_box .tabs a").eq(i).css("background-position","bottom");
				setTimeout(function(){
					$(".ad_box .tabs a").eq(i).css("background-position","");
					setTimeout(function(){
							$(".ad_box .tabs a").eq(i).css("background-position","bottom");
							setTimeout(function(){
								$(".ad_box .tabs a").eq(i).css("background-position","");
							},300)
					},200)
				},300)

			},2000*i);
		})
		
		
	},2000*$(".ad_box .ad").length);
	
	
	if($(".big_ad")[0]){
		if($(".big_ad .ad").children()[0].tagName=="IMG"){ //判断标签类型
			$(".big_ad .ad img").bind("load",function(){
				$("#index_banner").css("height",600);
				$(".big_ad .ad").css("height",600);
				//定时关闭
				window.t=setTimeout(function(){
					$(".operation").trigger("click");
				},5000);
			});
			$(".big_ad .ad img").attr("src",$(".big_ad .ad img").attr("src")+"#1");//重新更新下SRC，这样就可以再次触发onload事件
		}else if($(".big_ad .ad").children()[0].tagName=="OBJECT"){
			//$("#index_banner").css("height",600);
			//$(".big_ad .ad").css("height",600);
			window.t=setTimeout(function(){
				$(".operation").trigger("click");
			},5000);
			window.flash_show=function(){
				$("#index_banner").css("height",600);
				$(".big_ad .ad").css("height",600);
			}
			window.flash_break_timer=function(){
				clearTimeout(t);
			}
		}
	};


})
