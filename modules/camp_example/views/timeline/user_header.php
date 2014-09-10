<div id="blogger">
	<div class="blogger">
		<div class="inside">
			<div class="content" id="t-blogger">
	  			<div class="media">
	  				<div class="avatar"><a href="<?php echo miurl::subsite("maggie/", "user/$id"); ?>"><img src="<?php echo $profile_image_url ?>"></a></div>
	  			</div>
	  			<div class="info">
	  				<div class="hot" style="float: right">
	  					<ul>
							<li><a href="http://weibo.com/<?php echo $id; ?>/follow" class="tu_iframe_1024x600"><div><div class="n"><?php echo $friends_count; ?></div><div class="w">关注</div></div></a></li>
							<li><a href="http://weibo.com/<?php echo $id; ?>/fans" class="tu_iframe_1024x600"><div><div class="n"><?php echo $followers_count; ?></div><div class="w">粉丝</div></div></a></li>
						</ul>
					</div>
					<div class="name">
						<a href="http://weibo.com/<?php echo $id; ?>/info" class="tu_iframe_1024x600"><?php echo $screen_name; if ($verified) echo ' <img src="http://img.t.sinajs.cn/t35/style/images/index/sina.png" style="height: 20px">'; ?></a>
						<focusblock id="followblock" class="<?php echo ($mypage || !isset($followed)) ? "" : ($followed ? "followed" : "followit"); ?>">
							<span class="followed">已关注</span>
							<a identity="<?php echo $id; ?>" op="unfollow" href="#<?php echo miurl::subsite("maggie/", "ajax/unfollow"); ?>"><span class="followed op">取消关注</span></a>
							<a identity="<?php echo $id; ?>" op="follow" href="#<?php echo miurl::subsite("maggie/", "ajax/follow"); ?>"><span class="followit op">+ 加关注</span></a>
						</focusblock>
						<focusblock id="markblock" class="<?php echo ($mypage || !isset($marked)) ? "" : ($marked ? "followed" : "followit"); ?>">
							<span class="followed">已收藏</span>
							<a identity="<?php echo $id; ?>" op="mark" href="#<?php echo miurl::subsite("maggie/", "ajax/mark"); ?>"><span class="followit op">+ 收藏</span></a>
						</focusblock>
					</div>
					<div class="url">
					<?php  if (!empty($url)) {
						echo '<a href="'.$url.'">'.$url.'</a>';
					}?><br/>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="newtwitter" class="tform" style="padding:20px;display:none"> 
	<form name="newform" id="newform"  method="POST" action="<?php echo url::site('ajax/post')?>" enctype="multipart/form-data">
	<div style="margin-bottom: 5px"><textarea id="newtwittertext" name="status" rows="5" cols="50"></textarea>
	<br><input type="hidden" id="hidpicurl" name="hidpicurl" /><div class="loading"></div>
	</div></form>
	<a class="doing_face" op="face" facediv="div_post_face" txtid="newtwittertext"></a>
	
	<label class="doing_att" onmouseout="document.getElementById('getattach').style.display='none';" 
	onmouseover="document.getElementById('getattach').style.display='block';" for="getattach" 
	style="">
		<form id="upform" name="uploadForm" style="float: left" enctype="multipart/form-data"  
		action="<?php echo url::site('ajax/uploadpic')?>" method="post"  target="iframeUpload">
			<span id="myfile">
			<input id="getattach" class="file" type="file" name="attach" onchange="insertAttach(0);this.style.display='none';" 
			style="display: block; filter:alpha(opacity=0);opacity:0">
			</span>
			
			<span id="localfile"></span>
			<input id="uploadsubmit" type="hidden" value="true" name="uploadsubmit">
			<input type="hidden" value="896dec8d" name="formhash">
		</form>
	</label>
	

	<div style="float: right">
		<a href="javascript: void(0)" id="twitterit" op="post" class="sbutton">发送</a>
	</div>
 
	
	<br>
	<div class="uploaddiv" id="div_uploadpic" style="display:none;">
		
	</div>
	<iframe src="<?php echo url::site('ajax/uploadpic')?>" id="iframeUpload" name="iframeUpload" style="display:none"></iframe>
	<div id="updatePicText" style="display:none">
		<img src="<?php echo $layout->resource_path("images/loading_view.gif") ?>" />图片上传中...
	</div>
	<div class="facediv" id="div_post_face" style="display:none;"></div>
	<div class="facediv" id="div_face" style="display:none;">
		<ul>
		<?php 
			$facelist = config::item('timeline.face');
			foreach($facelist as $k=>$v)
			{
		?>
			<li title="<?php echo $k?>">
			<a class="facebtn" href="#" onclick="return false;"  title="<?php echo $k?>">
			<img alt="<?php echo $k?>" src="<?php echo $layout->resource_path("images/timeline/face/".$v);?>">
			</a>
			</li>
		<?php 		
			}
		?>
		</ul>
	</div>
	
</div>






