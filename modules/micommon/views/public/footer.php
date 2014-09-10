<div class="" id="footer">
	<div class="block">
		<h2>欢迎登陆旅行者</h2>
		<div class="partial-site-cities">
			<?php
			$col = 7;
			if(isset($allcities) && isset($allcities['open']))
			{
					$minsize = 7;
					if (count($allcities['open']) <= $col * $minsize) {
							// if total cities less than 50, use simple layout
							// keep minimum column
							$size = ceil(count($allcities['open']) / $col);
							echo '<ul class="column">';
							for($i = 0;$i < $size * $col; $i++) {
									if($i > 0 && $i % $size == 0)
											echo '</ul><ul class="column">';
									$j = $i % $size * $col + floor($i / $size);
									if ($j >= count($allcities['open'])) {
											continue;
									}
									echo '<li><a href="'.url::site('/city/'.$allcities['open'][$j]->citycode).'">'.$allcities['open'][$j]->cityname.'</a></li>';
							}
							echo '</ul>';
					} else {
							// advanced layout with alphabit index
							$last_group = substr($allcities['open'][0]->citycode, 0, 1);
							$last_index = substr($allcities['open'][0]->citycode, 0, 1);
							$city_groups = array();
							$city_groups[$last_group] = array();
							$limit = ceil(count($allcities['open']) / 5);
							for ($i = 0; $i < count($allcities['open']); $i++) {
							$letter = substr($allcities['open'][$i]->citycode, 0, 1);
							if (count($city_groups[$last_group]) >= $limit && $letter != $last_index) {
									if ($last_index != $last_group) {
											$city_groups["{$last_group} - {$last_index}"] = $city_groups[$last_group];
											unset($city_groups[$last_group]);
									}
									$last_group = $letter;
									$last_index = $letter;
									$city_groups[$last_group] = array();
							}
							else if ($letter != $last_index) {
									$last_index = $letter;
							}
							$city_groups[$last_group][] = $allcities['open'][$i];
							}
							if ($last_index != $last_group) {
							$city_groups["{$last_group} - {$last_index}"] = $city_groups[$last_group];
							unset($city_groups[$last_group]);
							}
							foreach ($city_groups as $index => $city_group) {
									$size = ceil(count($city_group) / $col);
									echo '<ul class="first column" style="width:40px;"><li><index>'.strtoupper($index).'</index></li>';
									for($i = 1; $i < $size; $i++) {
											echo '<li><br/></li>';
									}
									echo '</ul><ul class="first column">';
									for($i = 0;$i < $size * $col; $i++)
									{
											if($i > 0 && $i % $size == 0)
													echo '</ul><ul class="column">';
											$j = $i % $size * $col + floor($i / $size);
											if ($j >= count($city_group)) {
													echo '<li><br/></li>';
											}
											else {
													echo '<li><a href="'.url::site('/city/'.$city_group[$j]->citycode).'">'.$city_group[$j]->cityname.'</a></li>';
											}
									}
									echo '</ul>';
							}
					}
			}
			?>
			<div class="clear"></div>
		</div>
		<?php
		if(isset($allcities) && isset($allcities['unlaunched'])) {
		?>
		<h3>即将开放</h3>
		<div class="partial-site-cities unlaunched">
			<?php
			$size = ceil(count($allcities['unlaunched']) / $col);
			echo '<ul class="column">';
			for($i = 0;$i < $size * $col; $i++)
			{
				if($i > 0 && $i % $size == 0)
					echo '</ul><ul class="column">';
				$j = $i % $size * $col + floor($i / $size);
				if ($j >= count($allcities['unlaunched'])) {
					continue;
				}
				echo '<li>'.$allcities['unlaunched'][$j]->cityname.'</li>';
			}
			echo '</ul>';
			?>
			<div class="clear"></div>
		</div>
		<?php } ?>
		<?php
		if(isset($allcities) && !empty($allcities['unlaunchedAboard'])) {
		?>
		<h3>海外</h3>
		<div class="partial-site-cities unlaunched">
			<?php
			$size = ceil(count($allcities['unlaunchedAboard']) / $col);
			echo '<ul class="column">';
			for($i = 0;$i < $size * $col; $i++)
			{
				if($i > 0 && $i % $size == 0)
					echo '</ul><ul class="column">';
				$j = $i % $size * $col + floor($i / $size);
				if ($j >= count($allcities['unlaunchedAboard'])) {
					continue;
				}
				echo '<li>'.$allcities['unlaunchedAboard'][$j]->cityname;
				echo "({$allcities['unlaunchedAboard'][$j]->parentname})</li>";
			}
			echo '</ul>';
			?>
			<div class="clear"></div>
		</div>
		<?php } ?>
	</div>
</div>

<div class="clear"></div>



<div data-center="100" id="login-dialog" class="modal jqmID1" style="z-index: 20000; display: none; position: absolute; top: 93px;">
	<div class="modal-header noheader">
		<h1>Log in to Stay.com!</h1>
		<span class="textlink-right">New here? <a class="toggle-view" href="#">Sign up</a></span>
		<span title="close" class="close jqmClose">x</span>
	</div>
	<div class="modal-content">
		<div class="login-form">
			 <h2>Log in to Stay.com!</h2>
			<div style="display: none" class="merge-message">
				  Your guide to <span class="cities"></span> will be saved.		</div>

			<form method="post" action="/login/" id="login-form">
				<div class="facebook-login">
					 					  <a class="button fb-signin facebook xxlarge" href="#"><span class="fb-icon"></span>Log in using Facebook</a>
						 		</div>

				<div class="regular-login">

					<span class="or"><span>or</span></span>


					<div class="login-error">Email or password is incorrect.</div>

					<div class="input-container username">
						  <label for="login-Login">Email</label>
						  <div class="input">
							   <input type="text" name="Login[Login]" value="" placeholder="Email" class="text-medium" id="login-Login">				   	</div>
					</div>

					<div class="input-container password">
						<div class="input">
							   <label for="login-Password">Password</label>
							   <input type="password" name="Login[Password]" value="" placeholder="Password" class="text-medium" id="login-Password">					</div>
					</div>
					<div class="login-remember">
						  <input type="checkbox" name="Login[RememberMe]" value="1" checked="checked" id="login-RememberMe">					  <label for="login-RememberMe">Stay logged in</label>
					</div>
					<a href="/forgot/" class="forgot-password">Forgot password?</a>				   <input type="button" value="Log in with Stay.com" name="yt0" tabindex="5" class="button purple xlarge" id="login-button">				<input type="submit" style="visibility:hidden; width:0px;">

				</div>
			</form>
		</div>
	</div>
</div>

<div style="height:380px; background:url(<?php echo $layout->resource_path('images/bg_box.jpg'); ?>) center bottom no-repeat;"></div>
	<div id="box_bottom" style="background-image: url(<?php echo $layout->resource_path('images/bg_bottom.jpg'); ?>);">
		<div id="content_bottom">
			<div class="bmain">
				<div id="memu">
					<a href="http://www.tclub.cn" target="_blank">公司官网</a> |
					<a href="http://www.uutuu.com/tops/explain" target="_blank">关于我们 About Us</a> |
					<a href="http://www.uutuu.com/tops/explain" target="_blank">联系我们 Contact Us</a><br>
				  	<a href="http://www.uutuu.com/tops/privacy" target="_blank">隐私保护</a> |
				 	<a href="http://www.uutuu.com/static/link.html" target="_blank">友情链接</a>
				</div>
				<div class="album_bottom">
					<div class="album_bottomleft"><a href="javascript:"><img src="<?php echo $layout->resource_path('images/btn_bottom_left.gif'); ?>"></a></div>
					<div class="album_bottomcenter">
						<div class="photo"></div>
				</div>
				<div class="album_bottomright"><a href="javascript:"><img src="<?php echo $layout->resource_path('images/btn_bottom_right.gif'); ?>"></a></div>
			</div>
		</div>
		<div id="u_copyright">Copyright 2007-2012, uutuu.com. all rights reserved. <a href="http://www.miibeian.gov.cn/state/outPortal/loginPortal.action" target="_blank">沪ICP备11036621号</a> 增值电信业务经营许可证沪B2-20070198号<a href="http://www.uutuu.com/files/xingzhe.jpg" target="_blank"> 营业执照</a><br>
		</div>
		<div style="text-align:center;"> <a target="_blank" href="http://sh.cyberpolice.cn/infoCategoryListAction.do?act=initjpg"><img src="http://www.uutuu.com/images/base/icp_110.png"></a> <a style="margin-left:18px" target="_blank" href="http://www.zx110.org/"><img src="http://www.uutuu.com/images/base/icp_zx110.png"></a> <a style="margin-left:18px" target="_blank" href="http://www.sgs.gov.cn/lz/licenseLink.do?method=licenceView&amp;entyId=2011122717063481"><img src="http://www.uutuu.com/images/base/icp_ic.png"></a></div>
	</div>
</div>
