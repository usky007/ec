<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php echo html::script(array('res/js/jquery-1.3.2.js'), FALSE);?>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo html::specialchars($title) ?></title>

	<style type="text/css">
	body { width: 52em; margin: 50px auto 2em; font-size: 76%; font-family: Arial, sans-serif; color: #273907; line-height: 1.5; text-align: center; }
	h1 { font-size: 3em; font-weight: normal; text-transform: uppercase; color: #000; }
	</style>

</head>
<body>
	<h1><?php echo html::specialchars($title) ?></h1>
	<?php echo $content ?>
</body>
</html>