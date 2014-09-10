<div id="content">
	<div id="sidebar_pos" >
	<div id="sidebar">
		<?php if ($mypage) { ?>
		<ul id="nav" class="unread" href="<?php echo miurl::subsite("maggie/", "ajax/unread"); ?>">
			<li class="nav_home"><a href="<?php echo miurl::subsite("maggie/", ""); ?>"><b>我的首页</b><span class="updateinfo" id="u_status" mark="new_status"></span></a></li>
			<li class="nav_home"><a href="javascript:void(0);" op="newpost" class="newpost"><b>+</b> 发新微博</a></li>
			<li class="nav_home"><a href="<?php echo miurl::subsite("maggie/", "mentions"); ?>">@提到我的<span class="updateinfo" id="u_mentions" mark="mentions"></span></a></li>
			<li class="nav_home"><a href="http://weibo.com/comments" target="_blank">我的评论<span class="updateinfo" id="u_comments" mark="comments"></span></a></li>
			<li class="nav_home"><a href="http://weibo.com/<?php echo $identity?>/fans" target="_blank" id="fanurl">我的粉丝<span class="updateinfo" id="u_followers" mark="followers"></span></a></li>
			<li class="nav_home"><a href="<?php echo miurl::subsite("maggie/", "user/$identity"); ?>">我的微博</a></li>
			<li class="nav_home"><a href="<?php echo miurl::subsite("maggie/", "favorites"); ?>">我收藏的微博</a></li>
			<li class="disable">精选推荐微博</li>
			<li class="disable" style="font-size: 13px;">
				<?php if ($uptodate) { ?>
					上一页
				<?php } else { ?>
					<a href="<?php echo miurl::subsite("maggie/", "$uri?since=$upto"); ?>">上一页</a>
				<?php } ?>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<?php if ($nomore) { ?>
					下一页
				<?php } else { ?>
					<a href="<?php echo miurl::subsite("maggie/", "$uri?upto=$since"); ?>">下一页</a>
				<?php } ?>
			</li>
		<?php } else { ?>
		<ul id="nav">
			<li><a href="<?php echo miurl::subsite("maggie/", "$uri"); ?>">最新微博</a></li>
			<li class="disable" style="font-size: 13px;">
				<?php if ($uptodate) { ?>
					上一页
				<?php } else { ?>
					<a href="<?php echo miurl::subsite("maggie/", "$uri?since=$upto"); ?>">上一页</a>
				<?php } ?>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<?php if ($nomore) { ?>
					下一页
				<?php } else { ?>
					<a href="<?php echo miurl::subsite("maggie/", "$uri?upto=$since"); ?>">下一页</a>
				<?php } ?>
			</li>
			<li><a href="<?php echo miurl::subsite("maggie/", ""); ?>">我的首页</a></li>
		<?php } ?>
		</ul>
		
		<div id="about">Designed by <br><a href="http://fennivel.com">Fennivel Chai</a>.<br><br>For updates follow <a href="http://weibo.com/fennivel" target="_blank">@fennivel</a> on Weibo.</div>
	</div>
	</div>
	<div id="posts" class="animating">
	<?php echo $timeline; ?>
	</div>
	<div id="footnav"><ul class="pagenav">
		<li><a href="<?php echo miurl::subsite("maggie/", ""); ?>"><div class="pagebtn">首页</div></a></li>
		<li>
			<?php if ($uptodate) { ?>
				<div class="pagebtn disable">上一页</div>
			<?php } else { ?>
				<a href="<?php echo miurl::subsite("maggie/", "$uri?since=$upto"); ?>"><div class="pagebtn">上一页</div></a>
			<?php } ?>
		</li>
		<li>
			<?php if ($nomore) { ?>
				<div class="pagebtn disable">下一页</div>
			<?php } else { ?>
				<a href="<?php echo miurl::subsite("maggie/", "$uri?upto=$since"); ?>"><div class="pagebtn">下一页</div></a>
			<?php } ?>
		</li>
	</ul></div>
</div>

<div id="fwtwitter" class="tform" style="padding:20px;display:none">
	<form name="forwardForm" id="forwardForm" method="POST" action="<?php echo url::site('ajax/repost')?>" enctype="multipart/form-data">
	<input type="hidden" name="sid" id="forwardsid" >
	<div style="margin-bottom: 5px"><textarea id="forwardtwittertext" name="status" rows="5" cols="50"></textarea></div>
	
	<div style="min-height:30px">
		
		<div style="float: left">
			<a class="doing_face" op="face" facediv="div_repost_face" txtid="forwardtwittertext"></a>
			<input type="checkbox" name="is_comment" value="3"/><span>&nbsp;同时发评论</span></div>
			<div style="float: right"><a href="javascript: void(0)" id="twitterit" op="repost" class="sbutton">发送</a>
			<div class="loading"></div>
			
		</div>
		
	</div>
	<div class="facediv" id="div_repost_face" style="display:none;">
			</div>
			
	</form>
</div> 

<div id="successdialog" class="tform" style="padding:20px;display:none">
	<div> 转发成功... </div>
</div>
