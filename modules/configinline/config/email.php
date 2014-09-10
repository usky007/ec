<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['options'] = array(
					'hostname'=>'mail.uutuumail.com', 
					'port'=>'25', 
					'username'=>'ditu@mail.uutuumail.com', 
					'password'=>'ditu1qaz@WSX'
);

$config['resettime'] = 60;
$config['password']['timeout'] = 604800;

$config['password']['from'] = '旅行者传媒';
$config['password']['frommail'] = 'ditu@mail.uutuumail.com';
$config['password']['subject'] = '找回密码';

$config['register']['from'] = '旅行者传媒';
$config['register']['frommail'] = 'ditu@mail.uutuumail.com';
$config['register']['subject'] = '旅行者欢迎您!';

$config['traveler']['from'] = '旅行者传媒';
$config['traveler']['frommail'] = 'ditu@mail.uutuumail.com';
$config['traveler']['subject'] = '旅行者欢迎您!';

$config['share']['from'] = '旅行者传媒';
$config['share']['frommail'] = 'ditu@mail.uutuumail.com';
$config['share']['subject'] = '旅行者欢迎您!';


$config['modules'] = array(
	'password',
	'register',
	'traveler',
	'share'
);