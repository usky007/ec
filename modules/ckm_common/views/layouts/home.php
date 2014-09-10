<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="google-site-verification" content="9DGc9J_s2Hv7mAJnoAuhcV2g2LzdtoukRLiqqYkUQP4" />
	<?php echo $meta ?>
	<?php echo $title ?>
	<?php echo $keywords ?>
	<?php echo $description ?>
	<?php echo $css ?>
</head>
<body class="lang-en<?php echo $body_classes;?> <?php echo $themeclass?>"
	 <?php echo $bodypic?>  >
	<?php echo $templates; ?>
    <?php echo $dialogs ?>
	<div class="bg theme" <?php echo $bgpic?>>

		<?php echo $headers; ?>
		<div class="clear"></div>
		<div id="page" class="page-venue-index page_line">

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
<script type="text/javascript">
document.domain = location.href.replace(/^(.+?)\.([0-9a-zA-Z-_]+.[0-9a-zA-Z-_]+).*$/, '$2');
</script>
<?php echo $js ?>
<?php echo $tracker ?>
</html>
