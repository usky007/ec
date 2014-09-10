<html xmlns:wb=“http://open.weibo.com/wb”>
<script src="http://tjs.sjs.sinajs.cn/open/api/js/wb.js" type="text/javascript" charset="utf-8"></script>
<div id="boxmain">
  <div id="boxmain_kage"></div>
  <div id="contentmain">
    <div class="head">
      <div class="logo"><a href="#" target="_blank"></a></div>
      <div class="sinawb2">
      	<a href="http://e.weibo.com/visitbritain" target="_blank">英国旅游局 
      	<img src="<?php echo $layout->resource_path("images/britain/v.gif"); ?>" align="absmiddle"></a>
      </div>
      <div class="sinawb">
        <wb:follow-button uid="1721159394" type="red_3" width="100%" height="24" ></wb:follow-button>
      </div>
    </div>
    <div class="nav">
    <div class="nav_pop"><img src="<?php echo $layout->resource_path("images/britain/nav_pop.gif"); ?>" width="282" height="45" /></div>
      <ul>
        <li class="home"><a href="/<?php echo $britainUrl;?>/index.html" style="background-image:url(<?php echo $layout->resource_path("images/britain/nav_01.jpg"); ?>);"></a></li>
        <li><a href="/<?php echo $britainUrl;?>/sub_theme.html" style="background-image:url(<?php echo $layout->resource_path("images/britain/nav_02.jpg"); ?>);"></a></li>
        <li class='event'><a href="/<?php echo $britainUrl;?>/new" style="background-image:url(<?php echo $layout->resource_path("images/britain/nav_event.gif"); ?>);" class="current"></a></li>
        <li><a href="/<?php echo $britainUrl;?>/sub_video.html" style="background-image:url(<?php echo $layout->resource_path("images/britain/nav_04.jpg"); ?>);"></a></li>
        <li><a href="/<?php echo $britainUrl;?>/sub_tips.html" style="background-image:url(<?php echo $layout->resource_path("images/britain/nav_05.jpg"); ?>);"></a></li>
      </ul>
    </div>
  </div>
</div>
<div id="box">
  <div id="content">
    <div class="sub_nav">
      <ul class="newhot">
        <li class="new" style="background-image: url(http://wwww.uutuu.com/yanzi/GreatBritain/images/sub_icon01.jpg);"><a href="/<?php echo $britainUrl;?>/new/<?php echo $keyword;?>" class="current">最新</a></li>
        <li class="hot" style="background-image: url(http://wwww.uutuu.com/yanzi/GreatBritain/images/sub_icon02.jpg);"><a href="/<?php echo $britainUrl;?>/hot/<?php echo $keyword;?>">热门</a></li>
      </ul>
      <div class="sub_nav_city">
        <ul>
        <?php foreach($keywords as $key => $val):?>
          <li>
          	<a href="/<?php echo $britainUrl;?>/<?php echo $order;?>/<?php echo $val;?>"  <?php if($val == $keyword){echo 'class="current"';}?>>
          		<?php echo $key;?>
          	</a>
          </li>
        <?php endforeach;?>
        </ul>
      </div>
    </div>
    <div class="sub_nav_btn">
    	<a href="#" id="rule" target="_blank"><img src="<?php echo $layout->resource_path("images/britain/sub_rule.jpg"); ?>" /></a> 
    	<a href="#" id="join" ><img src="<?php echo $layout->resource_path("images/britain/sub_jion.jpg"); ?>" /></a>
    </div>
    <div class="clear"></div>
    <div class="main">
      <ul id="waterfall">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <div class="clear"></div>
      </ul>
      <div class="loading"  style="display: none;">
      	<img  id='loading_start' style='display:none;' src="<?php echo $layout->resource_path('images/britain/loading.gif'); ?>">
      	<span id='loading_more' style='display:none;cursor:pointer;'>点击加载更多...</span>
      	<img  id='loading_end' style='display:none;' src="<?php echo $layout->resource_path('images/britain/end.png'); ?>">
      </div>
      <!-- 返回顶部开始 -->
      	<div style="display:block;" class="back-to" id="toolBackTop">
			<a title="返回顶部" href="#top" class="back-top">
				<img src="<?php echo $layout->resource_path('images/britain/icon_up.gif'); ?>" />
			</a>
		</div>
      <!-- 返回顶部结束 -->
    </div>    
  </div>
</div>