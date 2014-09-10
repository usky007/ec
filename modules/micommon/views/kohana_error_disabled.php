<?php defined('SYSPATH') OR die('No direct access allowed.');
$layout = new AppLayout_View();
?>
<!DOCTYPE HTML>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $error ?></title>
	<link href="<?php echo $layout->resource_path("central.css"); ?>" rel="stylesheet" type="text/css"/>
</head>
<body class="lang-en gecko win">
	<div class="bg">
		<div id="page" class="page-venue-index" style="height:500px;">
			 <div id="header">
				<div class="logo">
					<a href="<?php echo url::site("/"); ?>"></a>
				</div><!-- #logo -->
				<div id="user-menu">
					<ul>
				<?php 
					$account = new Account();
					$loginuser = $account->get_loginuser();
			 
					if(is_null($loginuser))
					{
				
				?>
				
						<li><a class="show-signup-button" href="<?php echo url::site("signup"); ?>">注册</a></li>
						<li>或</li>
						<li><a class="show-login-button" href="<?php echo url::site("login"); ?>">登录</a></li>
					
				<?php 
					}else{
				 
				?>
						<li class="user">
							<a href="<?php echo format::getLink_UserGuides($loginuser->uid)?>">
								<img width="60" height="60" src="<?php echo user::avatar($loginuser, $layout->resource_path("images/fce8c0181.png"), 24, 24)?>" />
								<span><?php echo $loginuser->nickname?>的地图</span>
							</a>
						</li>
				
				<?php }?>
					</ul>
				</div><!-- #user-menu -->
			</div><!-- #header -->
			
			<div id="breadcrumbs">
				<br/>
			</div>
			
			<h1><?php echo html::specialchars($error) ?></h1>
			
			<div class="error"><?php echo $message."（#{$code}）"; ?></div>
			<!-- page -->
	  	
			<div class="clear"></div>
		</div>
	
		<div style="height:380px; background:url(http://www.uutuu.com/images/base/bg_box.jpg) center bottom no-repeat;"></div>
		<div id="box_bottom" style="background-image: url(http://www.uutuu.com/static/sample/bg_bottom.jpg);">
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
				<div id="u_copyright">
					Copyright 2007-2012, uutuu.com. all rights reserved. <a href="http://www.miibeian.gov.cn/state/outPortal/loginPortal.action" target="_blank">沪ICP备11036621号</a> 增值电信业务经营许可证沪B2-20070198号<a href="http://www.uutuu.com/files/xingzhe.jpg" target="_blank"> 营业执照</a><br>
				</div>
				<div style="text-align:center;"> <a target="_blank" href="http://sh.cyberpolice.cn/infoCategoryListAction.do?act=initjpg"><img src="http://www.uutuu.com/images/base/icp_110.png"></a> <a style="margin-left:18px" target="_blank" href="http://www.zx110.org/"><img src="http://www.uutuu.com/images/base/icp_zx110.png"></a> <a style="margin-left:18px" target="_blank" href="http://www.sgs.gov.cn/lz/licenseLink.do?method=licenceView&amp;entyId=2011122717063481"><img src="http://www.uutuu.com/images/base/icp_ic.png"></a></div>
			</div>
		</div>
	</div>
	<script type="text/javascript" >
		 var js_context = {res_url:"/res/"};
	</script>
	<script type="text/javascript" src="<?php echo url::site("res/js/central/100101.js"); ?>" ></script>
</body>
</html>