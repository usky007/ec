<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * An abstract cache driver, which add a new method to judge if tag feature is supported.
 *
 * $Id: Abstract.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Cache
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
abstract class Cache_Abstract_Driver implements Cache_Driver {
	const FEATURE_TAG = 1;
	const FEATURE_MULTIGET = 2;
	/**
	 * If specified feature is supported.
	 */
	public function is_supported($feature) {
		switch($feature)
		{
			case self::FEATURE_TAG: return TRUE;
			default: return FALSE;
		}
	}
}
?>