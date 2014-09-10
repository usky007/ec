<?php if( !isset($original_foot_data['hidefooter']) || !$original_foot_data['hidefooter']) {  ?>	
	<div class="newF">
	    <div class="newFooter">
	  		<a href="<?php  echo url::site("/m?authorize_wechat_type=1");  ?>">首页</a>   |
	  		<?php if(isset($original_foot_data['uid']) && !empty($original_foot_data['uid'])) { ?>
			<a href="<?php  echo url::site("m/user/glist/".$original_foot_data['uid']);  ?>">我的地图</a>    |
			<?php }else{ ?>
			我的地图    |
			<?php } ?>

	  		<a href="<?php if(isset($original_foot_data['url'])) { echo $original_foot_data['url']; } ?>">电脑版</a>   |    
	  		<a href="<?php echo url::site('/m/logout?override_cred_check=1'); ?>">登出</a>
	  		<br>
	    	Copyright 2007-<?php echo date('Y', time());  ?>, uutuu.com. all rights reserved.
		</div>
	</div>
<?php } ?>

<?php if(!(isset($original_foot_data['uid']) && !empty($original_foot_data['uid']))) { ?> 
	<div id="blank_height" style="height:60px;z-index:0"></div>
<?php } ?>

