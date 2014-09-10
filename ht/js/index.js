			
			var airportLoc=[25];
			var initFunc=function(){
				var count=$(".driving_ad_box .ad").length;
				var pointAvgWidth=990/(count-1);
				var panelAvgWidth=990/(count-1);
				for(i=2;i<=count-1;i++){
					airportLoc.push(panelAvgWidth*(i-1)-15);
					$(".tabs").append("<a href=\"#\" style=\"left:"+pointAvgWidth*(i-1)+"px;\">"+i+"</a>")
				}
				 airportLoc.push(890);
					$(".tabs").append("<a href=\"#\" style=\"left:"+980+"px;\">"+count+"</a>")
			}
			initFunc();

			 
			
			var scroll=$(".scrollable").scrollable({
				circular:true,
				speed:1500,
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
			