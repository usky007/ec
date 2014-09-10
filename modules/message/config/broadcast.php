<?php defined('SYSPATH') OR die('No direct access allowed.');
$config['driver'] = 'nginxhttppush';
$config['params'] = array
(
	'server_host' => 'http://test.yanzi.com',
	'server_port' => '80',
	'send_path' => 'publish',
	'listen_path' => 'activity',
	'channel_arg' => 'id',
	'channel' => 'yanzimsg',
);