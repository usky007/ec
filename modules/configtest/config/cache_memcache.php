<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Cache:Memcache
 *
 * memcache server configuration.
 */
$config['servers'] = array
(
	array
	(
		'host' => '192.168.1.26',
		//'host' => '192.168.1.108',
		'port' => 11221,
		'persistent' => false
	)
);

/**
 * Enable cache data compression.
 */
$config['compression'] = TRUE;