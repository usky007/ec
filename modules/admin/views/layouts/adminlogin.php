<!DOCTYPE HTML><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<!--     <link href="/res/css/core/reset.css" rel="stylesheet" type="text/css"/> -->
</head>

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
	.nav{font-size:14px;height:50px; background-color:#333; color:#FFF}

	.menu{float:left;clear:both;width:150px; background-color:#333;height:600px;}
		.menu li{color:#FFF}
	.main{
		background-color:#666;
		height:600px;
		color:#FFF;
		overflow:hidden
	}
	.footer{
		background-color:#333;height:150px;clear:both; color:#FFF;
	}
	.active{background-color:#666;
	}
	.link{height:30px;line-height:30px;width:150px;display:block}

</style>
<body>
<div class="nav"></div>

<div class="main" align="center">
<div>
	<?php echo $content?>
</div>
</div>
<div class="footer"></div>
</body>
</html>