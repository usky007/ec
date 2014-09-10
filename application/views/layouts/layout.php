<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />	
	<?php echo $title ?>
	<?php echo $keywords ?>
	<?php echo $description ?>
	<?php echo $css ?>
</head>
<body class="lang-en<?php echo $body_classes;?>">	
    <?php echo $dialogs ?>
	<div class="bg">
	<div id="page" class="page-venue-index">
	    <?php echo $headers; ?>
	    <?php echo $content ?>	    
		<div class="clear"></div>
	</div>
	<?php echo $footers ?>
	</div>
</body>
<?php echo $js ?>
<?php echo $tracker ?>
</html>
