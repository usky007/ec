<?php defined('SYSPATH') OR die('No direct access allowed.');
//
$config['default'] = array
(
	'benchmark'     => true,
	'persistent'    => FALSE,
	'connection'    => array
	(
		'type'     => 'mysqli',
		'user'     => 'dev',
		'pass'     => 'tclub123',
		'host'     => '192.168.1.243',
		'port'     => FALSE,
		'socket'   => FALSE,
		'database' => 'britain',
		'params'   => NULL
	),
	'character_set' => 'utf8',
	'table_prefix'  => '',
	'object'        => TRUE,
	'cache'         => FALSE,
	'escape'        => TRUE,
	'connect_timeout' => 3,
	'read_timeout'  => 5,
	'write_timeout' => 1,
	'reconnect' => TRUE
);