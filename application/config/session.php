<?php

/**
 * Class description.
 *
 * $Id: session.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */
 $config = array
	(
	'driver'         => 'cache',
	'storage'        => 'session',
	'name'           => 'featuresession',
	'validate'       => array(),
	'encryption'     => FALSE,
	'expiration'     => 0,
	'regenerate'     => 0,
	'gc_probability' => 2
	);
?>