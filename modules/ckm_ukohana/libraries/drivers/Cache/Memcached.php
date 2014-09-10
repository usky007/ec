<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * MemCache based Cache driver.
 *
 * $Id: Memcached.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_Memcached_Driver extends Cache_Abstract_Driver {
	const PERSISTENT_ID = "39c0cc13";
	// Cache backend object and flags
	protected static $backends = array();

	// Cache backend object and flags
	protected $backend = null;

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
		if ( ! extension_loaded('memcached'))
			throw new Kohana_Exception('cache.extension_not_loaded', 'memcached');

		if (is_null($config_name))
			$config_name = "cache_memcached";

		if (isset(self::$backends[$config_name]))
		{
			$this->backend = & self::$backends[$config_name];
			return;
		}

		$persistent_id = config::item("$config_name.persistent", TRUE, FALSE);
		if ($persistent_id !== FALSE && (!is_string($persistent_id) || $persistent_id == "TRUE"))
			    $persistent_id = $config_name.self::PERSISTENT_ID;
		$this->backend = $this->init_backend(
			config::item("$config_name.servers", true),
			config::item("$config_name.options"),
			$persistent_id);
		self::$backends[$config_name] = & $this->backend;

		Kohana::log("debug", "Cache_Memcached_Driver initialized");


	}

	public function find($tag)
	{
		Kohana::log('error', 'Cache: tags are unsupported by the Memcache driver');
		return array();
	}

	public function get($id)
	{
		$null = null;
		if (is_array($id)) {
			$result = @$this->backend->getMulti($id, $null, Memcached::GET_PRESERVE_ORDER);
			foreach($result as $k=>$v)
				$v = $v===null?false:$v;
		}
		else {
			$result = @$this->backend->get($id);
		}
//		Kohana::log("debug", "Memcached::get rescode ".@$this->backend->getResultCode());
		return (@$this->backend->getResultCode() == Memcached::RES_SUCCESS) ? $result : (is_array($id) ? array() : NULL);
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
		return @$this->backend->set($id, $data, $lifetime);
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

	protected function init_backend($servers, $options, $persistent_id = false) {
		// initialize once.
		if ( ! extension_loaded('memcached'))
			throw new Kohana_Exception('cache.extension_not_loaded', 'memcached');

		$backend = null;
		if ($persistent_id === FALSE)
		{
		    $backend = new Memcached();
		}
		else
		{
			$backend = new Memcached($persistent_id);
		}

		if (!empty($options))
		{
			// option filter
			if (isset($options[Memcached::OPT_SERIALIZER]))
			{
				switch ($options[Memcached::OPT_SERIALIZER])
				{
					case Memcached::SERIALIZER_IGBINARY:
						if (!Memcached::HAVE_IGBINARY)
						{
							Kohana::log("alert", "Cache_Memcached_Driver:SERIALIZER_IGBINARY unsupported, use default.");
							unset($options[Memcached::OPT_SERIALIZER]);
						}
						break;
					case Memcached::SERIALIZER_JSON:
						if (!Memcached::HAVE_JSON)
						{
							Kohana::log("alert", "Cache_Memcached_Driver:SERIALIZER_JSON unsupported, use default.");
							unset($options[Memcached::OPT_SERIALIZER]);
						}
						break;
				}
			}
			foreach ($options as $key => $val)
			{
				@$backend->setOption($key, $val);
			}
		}

		// Persistent connection share server settings between requests
		// No duplicated server(s) will be added to instance after first initialization
		// Refer to: http://www.php.net/manual/en/memcached.construct.php for details
		// CAUTION: update of server config might require to restart PHP service.
		$exist_servers = @$backend->getServerList();
		if (empty($exist_servers))
		{
			$daemons = array();
			foreach ($servers as $conf)
			{
				// Make sure all required keys are set
				$conf += array('host' => '127.0.0.1', 'port' => 11211);

				// Add the server to the pool
				$daemon = array($conf['host'], $conf['port']);
				if (isset($conf['weight']))
				{
					$daemon[] = $conf['weight'];
				}
				$daemons[] = $daemon;
			}
			$backend->addServers($daemons)
				or Kohana::log('error', 'Cache: Connection failed: '.$daemons);
		}
		return $backend;
	}
} // End Cache Memcache Driver
?>