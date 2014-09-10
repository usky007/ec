<!DOCTYPE HTML><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>

</head>
<script src="/res/js/lib/jquery-1.7.2.js"></script>
<!-- <script src="/static/js/admin.js"></script>
<script src="/static/js/vendor.js"></script> -->
<?php echo $js?>
<?php echo $css?>
<style>
	a, body, div, ul, form {
	margin: 0px;
	padding: 0px;
	border:none;
	font-family:tahoma,"宋体";
	_font-family:宋体;
	font-size:13px;
	color:#FFFFFF;
	}
	body {
	background-color:#666;
	}
	.nav{font-size:14px;height:100px; background-color:#333; color:#FFF}

	.menu{float:left;clear:both;width:100px; background-color:#333;height:1200px;}
	.menu li{color:#FFF}
	.main{
		background-color:#666;
		color:#FFF;
		height:1200px;
		overflow:auto
	}
	.footer{
		background-color:#333;height:150px;clear:both; color:#FFF;
	}
	.active{background-color:#666;
	}
	.link{height:30px;line-height:30px;width:100px;display:block}
	.pagination span
	{
		display:block;
		width:55px;
		height:20px;
		line-height:20px;
		float:left;
		margin-right:2px;
		text-align:center;
	}

</style>
<body>
<?php
$act = new Account();

?>
<div class="nav"><font style="font-size:48px">Y.Z backend</font></div>
<!-- 当前用户：<?php //echo $act->loginuser->Username?><a href="/logout/index">[重登录]</a> -->
<div class="menu">
	<ul>
	<?php echo $leftbar?>	</ul>
</div>

<?php echo $content?>

<div class="footer">版权:旅行者uutuu</div>
</body>
</html>