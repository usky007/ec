<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="apple-itunes-app" content="app-id=622459512"/>
	<?php echo $title ?>
	<?php echo $keywords ?>
	<?php echo $description ?>
	<?php echo $css ?>
</head>
<body class="lang-en<?php echo $body_classes;?>">
	<?php echo $templates; ?>
    <?php echo $dialogs ?>
	<div class="bg">
	<div id="page" class="page-venue-index">
	    <?php echo $headers; ?>
	    <?php echo $content ?>
	    <?php if (isset($rightbar)) {  ?>
    	<div id="layout-guide-sidebar" style="position: fixed; visibility:hidden;" >
		    	<?php echo $rightbar; ?>
		    	<div class="sidebar-mask"></div>
		</div>
		<?php }?>
		<div class="clear"></div>
	</div>
	<?php echo $footers ?>
	</div>
</body>
<?php echo $js ?>
<?php echo $tracker ?>
</html>
