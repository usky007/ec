<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Shared memory based Cache driver.
 *
 * $Id: SharedMem.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_SharedMem_Driver extends Cache_Abstract_Driver {
	const NAME = "SharedMemCache";
	
	const WRAPPER_KEY_EXPIRE = 'ex';
	const WRAPPER_KEY_DATA = 'd';
	
	// Cache backend object and flags
	protected $resource = false;
	
	protected $config = null;

	/**
	 * Memcached Driver Construct
	 *
	 * @param string config_name Instances with same config name share one connection.
	 * default is "cache_memcached", which related to config file "cache_memcached.php"
	 * in config directory.
	 */
	public function __construct($config_name = null)
	{
		// initialize once.
		if ( ! extension_loaded('sysvshm'))
			throw new Kohana_Exception('cache.extension_not_loaded', 'sysvshm');
	
		if (is_null($config_name))
			$config_name = "cache_sharedmem";
		
		$this->config = config::item($config_name, false, array());
		$this->initialize($this->config);

		Kohana::log("debug", "Cache_SharedMem_Driver initialized");
	}
	
	public function __destruct()
	{
		@shm_detach($this->resource);
	}
	
	protected function initialize($config)
	{
		$file_name = config::subitem($config, 'name', false, self::NAME);
		$file_path = config::subitem($config, 'tempdir', false, isset($_SERVER["TMPDIR"]) ? $_SERVER["TMPDIR"] : '/tmp').'/'.$file_name;
		@touch($file_path);
		$key = ftok($file_path, 'c');
		if ($key < 0) {
			throw new Kohana_Exception('cache.unwritable', $file_path);
		}
		
		$size = config::subitem($config, 'options.size', false, 0);
		if ($size > 0) {
			$this->resource = @shm_attach($key, $size);
		}
		else {
			$this->resource = @shm_attach($key);
		}
		
		if ($this->resource === false) {
			throw new Kohana_Exception('cache.driver_error', 'Unable to create the shared memory segment.');
		}
	}

	public function find($tag)
	{
		Kohana::log('error', 'Cache: tags are unsupported by the SharedMem driver');
		return array();
	}

	public function get($id)
	{
		$result = @shm_get_var($this->resource, $id);
		if ($result === FALSE || $result === NULL) {
			return NULL;
		}
		if (!isset($result[self::WRAPPER_KEY_DATA]) || !isset($result[self::WRAPPER_KEY_EXPIRE])) {
			throw new Kohana_Exception('cache.driver_error', 'Unrecognizable data format.');
		}
		if ($result[self::WRAPPER_KEY_EXPIRE] > 0 && $result[self::WRAPPER_KEY_EXPIRE] < time()) {
			return NULL;
		}
		return $result[self::WRAPPER_KEY_DATA];
	}

	public function set($id, $data, array $tags = NULL, $lifetime)
	{
		if ($lifetime !== 0) {
			// Transform to unix timestamp
			$lifetime += time();
		}

		if ( ! empty($tags)) {
			Kohana::log('error', 'Cache: tags are unsupported by the SharedMem driver');
		}

		// Set a new value
		if (!@shm_put_var($this->resource, $id, array(self::WRAPPER_KEY_DATA => $data, self::WRAPPER_KEY_EXPIRE => $lifetime))) {
			log::warn("Not enough memory for shared memory cache");
			return false;
		}
		return true;
	}

	public function delete($id, $tag = FALSE)
	{
		if ($id === TRUE) {
			if ($status = @shm_remove($this->resource)) {
				$this->resource = false;
				$this->initialize($this->config);
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
			return @shm_remove_var($this->resource, $id);
		}
	}

	public function delete_expired()
	{
		// We handle expiration with data wrapper.
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
				return FALSE;
			default:
				return FALSE;
		}
	}
} // End Cache Memcache Driver
?>