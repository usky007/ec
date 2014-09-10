<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Preference
 *
 * Preference settings, defined as arrays, or "groups". If no group name is
 * used when loading the cache library, the group named "default" will be used.
 *
 * Each group can be used independently, and multiple groups can be used at once.
 *
 * Group Options:
 *  driver   - Cache backend driver. Kohana comes with file, database, and memcache drivers.
 *              > File cache is fast and reliable, but requires many filesystem lookups.
 *              > Database cache can be used to cache items remotely, but is slower.
 *              > Memcache is very high performance, but prevents cache tags from being used.
 */
$config['default'] = array
(
	'benchmark'=> false,
	'driver'   => 'Preference_Dictionary_Driver',
	'params'   => array('manual_update' => false)
);

$config['pinyin'] = array
(
	"groups" => 25
);
