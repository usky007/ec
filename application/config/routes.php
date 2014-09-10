<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Core
 *
 * Sets the default route to "welcome"
 */
$config['_default'] = 'index';

$config['logout'] = 'login/logout';
$config['signup'] = 'login/signup';
$config['GreatBritain/(.+)'] = "britain/$1";
$config['greatbritain/(.+)'] = "britain/$1";
$config['WA/(.+)'] = "wa/$1";
