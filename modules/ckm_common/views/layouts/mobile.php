<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta content="width=device-width, initial-scale=1, minimum-scale=0.1,maximum-scale=1.0, user-scalable=0" name="viewport">
	<?php echo $meta ?>
	<?php echo $title ?>
	<?php echo $keywords ?>
	<?php echo $description ?>
	<?php echo $css ?>
</head>
<body class="lang-en<?php echo $body_classes;?>">
	<div class="container" style="margin-top: 0px;">
		<div id="page">
		<?php echo $headers; ?>
		<?php echo $content ?>
		<?php echo $footers; ?>
		</div>
	</div>
<div class="global_overlay" <?php if(isset($overlay) && $overlay){ echo " style=\"display:block\"";} ?>  ></div>
<?php echo $dialogs ?>
</body>
<?php echo $js ?>
<?php echo $tracker ?>
</html>

