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
		'host' => '192.168.1.249',
		'port' => 11211
	)
);

/**
 * Enable cache data compression.
 */
$config['persistent'] = FALSE;
$config['options'] = array
(
	Memcached::OPT_COMPRESSION => TRUE,
	// prefered option, allow async I/O
	Memcached::OPT_NO_BLOCK => TRUE,
	Memcached::OPT_CONNECT_TIMEOUT => 10,
	// useful in non persistent connection, not evil anyway.
	Memcached::OPT_TCP_NODELAY => TRUE,
	Memcached::OPT_LIBKETAMA_COMPATIBLE => TRUE,
//	Memcached::OPT_NUMBER_OF_REPLICAS => 3,
//	Memcached::OPT_SERIALIZER => Memcached::SERIALIZER_IGBINARY,
	Memcached::OPT_BINARY_PROTOCOL => TRUE
);
