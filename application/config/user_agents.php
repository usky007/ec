<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Core
 *
 * This file contains four arrays of user agent data.  It is used by the
 * User Agent library to help identify browser, platform, robot, and
 * mobile device data. The array keys are used to identify the device
 * and the array values are used to set the actual name of the item.
 */
$config['platform']['windows nt 6.2'] = 'Windows 8';
$config['platform']['windows nt 6.1'] = 'Windows 7';

$config['engine'] = array
(
	'AppleWebKit' => 'Webkit',
	'like Gecko' => NULL, // ignore
	'Gecko' => 'Gecko',
	'MSIE' => 'IE',
	'Internet Explorer' => 'IE'
);

$config['browser'] = array
(
	'Opera'             => 'Opera',
	'MSIE 6'            => 'IE6',
	'MSIE 7'            => 'IE7',
	'MSIE 8'            => 'IE8',
	'MSIE 9'            => 'IE9',
	'MSIE'              => 'IE',
	'Internet Explorer' => 'IE',
	'Shiira'            => 'Shiira',
	'Firefox'           => 'Firefox',
	'Chimera'           => 'Chimera',
	'Phoenix'           => 'Phoenix',
	'Firebird'          => 'Firebird',
	'Camino'            => 'Camino',
	'Netscape'          => 'Netscape',
	'OmniWeb'           => 'OmniWeb',
	'Chrome'            => 'Chrome',
	'Safari'            => 'Safari',
	'Konqueror'         => 'Konqueror',
	'Epiphany'          => 'Epiphany',
	'Galeon'            => 'Galeon',
	'Mozilla'           => 'Mozilla',
	'icab'              => 'iCab',
	'lynx'              => 'Lynx',
	'links'             => 'Links',
	'hotjava'           => 'HotJava',
	'amaya'             => 'Amaya',
	'IBrowse'           => 'IBrowse',
);
