<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * MemCache based Cache driver.
 *
 * $Id: Memcache.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_Memcache_Driver extends Cache_Abstract_Driver {
	// Cache backend object and flags
	protected static $backends = array();

	// Cache backend object and flags
	protected $backend = null;
	protected $flags;

	public function __construct($config_name = null)
	{
		// initialize once.
		if ( ! extension_loaded('memcache'))
			throw new Kohana_Exception('cache.extension_not_loaded', 'memcache');

		if (is_null($config_name))
			$config_name = "cache_memcache";

		$this->flags = Kohana::config("$config_name.compression") ? MEMCACHE_COMPRESSED : FALSE;
		if (isset(self::$backends[$config_name]))
		{
			$this->backend = & self::$backends[$config_name];
			return;
		}

		$this->backend = new Memcache;

		$servers = Kohana::config("$config_name.servers");
		foreach ($servers as $server)
		{
			// Make sure all required keys are set
			$server += array('host' => '127.0.0.1', 'port' => 11211, 'persistent' => FALSE);

			// Add the server to the pool
			$this->backend->addServer($server['host'], $server['port'], (bool) $server['persistent'])
				or Kohana::log('error', 'Cache: Connection failed: '.$server['host']);
		}
		self::$backends[$config_name] = & $this->backend;
	}

	public function find($tag)
	{
		Kohana::log('error', 'Cache: tags are unsupported by the Memcache driver');
		return array();
	}

	public function get($id)
	{
		$result = @$this->backend->get($id);
		return ($result !== FALSE) ? $result : (is_array($id) ? array() : NULL);
	}

	public function set($id, $data, array $tags = NULL, $lifetime)
	{
		if ($lifetime !== 0)
		{
			// Memcache driver expects unix timestamp
			$lifetime += time();
		}

		if ( ! empty($tags))
		{
			Kohana::log('error', 'Cache: tags are unsupported by the Memcache driver');
		}

		// Set a new value
		return @$this->backend->set($id, $data, $this->flags, $lifetime);
	}

	public function delete($id, $tag = FALSE)
	{
		if ($id === TRUE)
		{
			if ($status = @$this->backend->flush())
			{
				// We must sleep after flushing, or overwriting will not work!
				// @see http://php.net/manual/en/function.memcache-flush.php#81420
				sleep(1);
			}

			return $status;
		}
		elseif ($tag === TRUE)
		{
			Kohana::log('error', 'Cache: tags are unsupported by the Memcache driver');
			return TRUE;
		}
		else
		{
			return @$this->backend->delete($id);
		}
	}

	public function delete_expired()
	{
		// Memcache handles garbage collection internally
		return TRUE;
	}

	/**
	 * If specified feature is supported.
	 */
	public function is_supported($feature) {
		switch ($feature)
		{
			case self::FEATURE_TAG:
				return FALSE;
			case self::FEATURE_MULTIGET:
				return TRUE;
			default:
				return FALSE;
		}
	}
} // End Cache Memcache Driver
?>