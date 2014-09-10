<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Adapter driver for Cache drivers.
 *
 * $Id: Cache.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Preference
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Preference_Cache_Driver implements Preference_Driver {
	const DEFAULT_LIFETIME = 31536000; // 1 year.

	protected $backend;

	public function __construct(Cache_Driver $cache)
	{
		$this->backend = $cache;
	}

	public function set($category, $key, $data, $lock = null)
	{
		return $this->backend->set("{$category}_{$key}", $data, array($category), self::DEFAULT_LIFETIME);
	}

	public function delete($category, $key, $lock = null)
	{
		if ($key == TRUE)
			return $this->backend->delete($category, TRUE);
		else
			return $this->backend->delete("{$category}_{$key}");
	}

	public function get($category, $key, &$lock = null)
	{
		return $this->backend->get("{$category}_{$key}");
	}

	public function & entries($category, $limit = NULL, $offset = NULL)
	{
		return $this->backend->find($category);
	}

	public function is_lock_supported()
	{
		return false;
	}
}
?>