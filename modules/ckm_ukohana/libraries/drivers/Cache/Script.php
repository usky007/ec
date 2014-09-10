<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Script level cache implementation.
 *
 * $Id: Script.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Cache
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Cache_Script_Driver extends Cache_Abstract_Driver {

	const TAGS_KEY = 'memcache_tags_array';

	// Cache backend object and flags
	protected $backend = array();
	protected $driver = null;

	// Tags array
	protected $tags = array();
	protected $metas = array();

	public function __construct(Cache_Driver $driver = null)
	{
		if (!is_null($driver) && get_class($driver) != get_class($this))
			$this->driver = $driver;
	}

	public function find($tag)
	{
		if (!is_null($this->driver))
		{
			return $this->driver->find($tag);
		}
		$tag = $this->sanitize_tag($tag);
		return isset($this->tags[$tag]) ? $this->tags[$tag] : array();
	}

	public function get($id)
	{
		if (!is_array($id))
		{
			$cached = array_key_exists($id, $this->backend);
			if ($cached)
				return is_object($this->backend[$id]) ? clone($this->backend[$id]) : $this->backend[$id];
			else if (!is_null($this->driver))
			{
				$val = $this->driver->get($id);
				// set local cache
				$this->backend[$id] = $val;
				return $val;
			}
			else
				return null;
		}
		else
		{
			$result = array();
			$lefts = array();
			$multiget = !is_null($this->driver) && ($this->driver instanceof Cache_Abstract_Driver) &&
				$this->driver->is_supported(self::FEATURE_MULTIGET);

			// Block driver level fetching loop if multiget is supported.
			if ($multiget) {
				$old_driver = $this->driver;
				$this->driver = NULL;
			}
			// Fetch script level cache and driver level if necessary.
			foreach ($id as $unit)
			{
				$var = $this->get($unit);
				if (is_null($var))
					$lefts[] = $unit;
				else
					$result[$unit] = $var;
			}
			if ($multiget)
			{
				$this->driver = $old_driver;
				// Get multiple cache once for all.
				$lefts = $this->driver->get($lefts);
				// Set script level cache.
				foreach ($lefts as $key => $val)
				{
					$this->backend[$key] = $val;
					$result[$key] = $val;
				}
			}
			return $result;
		}
	}

	public function set($id, $data, array $tags = NULL, $lifetime)
	{
		if (!is_null($this->driver))
		{
			$result = $this->driver->set($id, $data, $tags, $lifetime);
            if ($result === FALSE)
                return FALSE;
		}

        if ( ! empty($tags) && is_null($this->driver))
        {
            $safe_tags = $this->sanitize_tag($tags);
            foreach ($safe_tags as $tag)
            {
                // Add the id to each tag
				$this->tags[$tag][$id] = & $data;
			}
            $this->metas[$id] = join(",", $safe_tags);
        }

		$this->backend[$id] = & $data;
		return TRUE;
	}

	public function delete($id, $tag = FALSE)
	{
		if (!is_null($this->driver))
		{
			// failed;
			$result = $this->driver->delete($id, $tag);
			if ($result === FALSE)
				return FALSE;
			else if ($tag === TRUE)
				return TRUE; // we've done here
		}

		if ($id === TRUE)
		{
			$this->reset("backend");
			$this->reset("tags");
			$this->reset("metas");
			return TRUE;
		}

		// deal tag clean
		$itd = array();
		if ($tag === TRUE)
		{
			$id = $this->sanitize_tag($id);
			if (isset($this->tags[$id]))
			{
				foreach ($this->tags[$id] as $_id => $data)
				{
					// Delete each id in the tag
					$itd[] = $_id;
				}

				// Delete the tag
				unset($this->tags[$id]);
			}
		}
		else
		{
			$itd[] = $id;
		}

		// final clean
		foreach ($itd as $_id)
		{
			unset($this->backend[$_id]);
			if (!isset($this->metas[$_id]))
				continue;

			$tags = explode(",", $this->metas[$_id]);
			unset($this->metas[$_id]);

			foreach ($tags as $_tag)
			{
				unset($this->tags[$_tag][$_id]);
			}
		}
		return TRUE;
	}

	public function delete_expired()
	{
		if (!is_null($this->driver))
		{
			return $this->driver->delete_expired();
		}
		return TRUE;
	}

	/**
	 * If specified feature is supported.
	 */
	public function is_supported($feature) {
		switch ($feature)
		{
			case self::FEATURE_TAG:
				if (is_null($this->driver))
					return TRUE;
				break;
			case self::FEATURE_MULTIGET: return TRUE;
		}
		if (!is_null($this->driver) && $this->driver instanceof Cache_Abstract_Driver)
		{
			return $this->driver->is_supported($feature);
		}
		return parent::is_supported($feature);
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

	private function reset($var)
	{
		if (!empty($this->$var))
		{
			unset($this->$var);
			$this->$var = array();
		}
	}
} // End Cache Script Driver
?>