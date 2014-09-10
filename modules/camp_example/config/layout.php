<?php
/**
 * Class description.
 *
 * $Id: deployment.php 21 2011-06-21 03:15:47Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
$config['maggie/features'] = array
(
	//"layout" => "layouts/timeline",
	"layout_class" => "AppLayout_View",
	"meta" => array (
		"css"	=> "css/central/timeline.css"
	),
//	"index" => array (
		"view" => "timeline/features"
//	)
);
?>