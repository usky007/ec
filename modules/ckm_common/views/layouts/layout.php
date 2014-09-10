<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php echo $meta ?>
	<?php echo $title ?>
	<?php echo $keywords ?>
	<?php echo $description ?>
	<?php echo $css ?>
</head>
<body class="lang-en<?php echo $body_classes;?> <?php echo $themeclass;?>"
	 <?php echo $bodypic?>  >
	<?php echo $templates; ?>
    <?php echo $dialogs ?>
	<div class="bg <?php echo $themeclass?>" <?php echo $bgpic?>>
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
