<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Memcache-based Cache driver.
 * If you want to group memcache servers by usage, specify config file name in parameter.
 *
 * $Id: TagAdapter.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_TagAdapter_Driver extends Cache_Abstract_Driver {
	const META_ID = "11350bd282714c8e9a8d8004c1202b00";

	// Cache backend object and flags
	protected $driver = null;

	// Tag drvier
	private static $tag_driver = null;

	public function __construct(Cache_Driver &$driver)
	{
		$this->driver = $driver;
	}

	public function find($tag)
	{
		$preference = & $this->get_preference($tag);

		$now = time();

		$ids = array();
		foreach ($preference->entries() as $id => $entry)
		{
			if ($now >= intval($entry->value) )
			{
				$preference->delete($id);
				continue;
			}
			$ids[] = $id;
		}
		if (empty($ids)) {
			return array();
		}

		// get multiple objects.
		if ($this->is_supported(self::FEATURE_MULTIGET))
		{
			$vals = $this->driver->get($ids);
		}
		else
		{
			foreach ($ids as $id)
			{
				$vals[$id] = $this->driver->get($id);
			}
		}

		// gc and unwrap part.
		foreach ($ids as $id)
		{
			if (!isset($vals[$id]))
			{
				// clean up staff
				$preference->delete($id);
				// may be null, unset whatever.
				unset($vals[$id]);
			}
			else if (is_array($vals[$id]) && isset($vals[$id][self::META_ID]))
				$vals[$id] = $vals[$id][0];
		}
		return $vals;
	}

	public function get($id)
	{
		$return = $this->driver->get($id);

		if (is_array($id))
		{
			// FEATRUE_MULTIGET compatible logic
			foreach ($return as $key => $val)
			{
				if (is_array($val) && isset($val[self::META_ID]))
					$return[$key] = $val[0];
			}
			return $return;
		}
		else if (is_array($return) && isset($return[self::META_ID]))
			return $return[0];
		else
			return $return;
	}

	public function set($id, $data, array $tags = NULL, $lifetime)
	{
		if (!$this->driver->set($id, $data, array(), $lifetime))
			return FALSE;

		if ($lifetime !== 0)
		{
			// Memcache driver expects unix timestamp
			$lifetime += time();
		}

		if ( ! empty($tags))
		{
			$tags = $this->sanitize_tag($tags);
			$data = array($data, self::META_ID=>join(",", $tags));
			foreach ($tags as $tag)
			{
				// Add the id to each tag
				$this->get_preference($tag, true)->set($id, $lifetime);
			}
		}

		// Set a new value
		return TRUE;
	}

	public function delete($id, $tag = FALSE)
	{
		if ($id === TRUE)
		{
			return $this->driver->delete(TRUE);
		}
		elseif ($tag === TRUE)
		{
			$time = time();
			$preference = & $this->get_preference($id);
			$ids = $preference->entries();
			foreach ($ids as $key => $entry)
			{
				// Delete each id in the tag
				$this->driver->delete($key);
			}
			$preference->delete_all();
			return TRUE;
		}
		else
		{
			$val = $this->driver->get($id);
			if (is_null($val))
			{
				return true;
			}
			else if (is_array($val) && isset($val[self::META_ID]))
			{
				$tags = explode(",", $val[self::META_ID]);
				foreach ($tags as $tag)
				{
					$this->get_preference($tag)->delete($id);
				}
			}
			return $this->driver->delete($id);
		}
	}

	public function delete_expired()
	{
		// Memcache handles garbage collection internally
		$model = $this->get_tag_driver()->get_backend();
		$model->clear();
		$model->gc();
		return $this->driver->delete_expired();
	}

	/**
	 * If specified feature is supported.
	 */
	public function is_supported($feature) {
		switch ($feature)
		{
			case self::FEATURE_TAG:
				return TRUE;
			default:
				return $this->driver->is_supported($feature);
		}
	}

	private function & get_preference($tag, $safe_tag = FALSE)
	{
		$category = $safe_tag ? $tag : $this->sanitize_tag($tag);
		$preference = & Preference::instance($category, $this->get_tag_driver());
//		if ($preference->get_driver() instanceof Preference_Cache_Driver) {
//			throw new Kohana_User_Exception('Unsupported Feature', 'Tags are unsupported with current configuration');
//		}
		return $preference;
	}

	private function get_tag_driver()
	{
		if (self::$tag_driver === NULL)
		{
			self::$tag_driver = new Preference_Dictionary_Driver();
			self::$tag_driver->set_backend(new Cachetag_Dicentry_Model());
		}
		return self::$tag_driver;
	}

	private function sanitize_tag($tag)
	{
		if (is_array($tag)) {
			for ($i = 0; $i < count($tag); $i++)
				$tag[$i] = $this->sanitize_tag($tag[$i]);
			return $tag;
		}
		else
			return str_replace(array(','), '_', $tag);
	}

} // End Cache Memcache Driver
?>