<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * A safer memcached library implementation. Divide memcached servers into
 * active group and backup group, switch if any active grouup fails.
 *
 * $Id: SafeMemcached.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class Cache_SafeMemcached_Driver extends Cache_Memcached_Driver {
	// Cache backend object and flags
	protected $bak_backend = null;
	protected $dead_servers = array();
	protected $config_name = null;

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
		if (is_null($config_name))
			$config_name = "cache_safememcached";
		$this->config_name = $config_name;

		parent::__construct($config_name);

		if (isset(self::$backends["backup_$config_name"]))
		{
			$this->bak_backend = & self::$backends["backup_$config_name"];
			return;
		}

		$persistent_id = config::item("$config_name.persistent", TRUE, FALSE);
		if ($persistent_id !== FALSE) {
			if (!is_string($persistent_id) || $persistent_id == "TRUE") {
				 $persistent_id = $config_name.self::PERSISTENT_ID;
			}
			$persistent_id = "backup_".$persistent_id;
		}
		$this->bak_backend = $this->init_backend(
			config::item("$config_name.bak_servers", true),
			config::item("$config_name.options"),
			$persistent_id);
		self::$backends["backup_$config_name"] = & $this->bak_backend;

		Kohana::log("debug", "Cache_SafeMemcached_Driver initialized");
	}

	public function get($id)
	{
		$null = null;
		if (is_array($id)) {
			$result = @$this->backend->getMulti($id, $null, Memcached::GET_PRESERVE_ORDER);
		}
		else {
			$result = @$this->backend->get($id);
			$code = $this->backend->getResultCode();
			Kohana::log("debug", "Memcached::get $id, rescode $code");
			if ($code != Memcached::RES_SUCCESS && $code != Memcached::RES_NOTFOUND) {
				// $error!, try backups
//				Kohana::log("debug", "aaa");
				$result = @$this->bak_backend->get($id);

//				Kohana::log("debug", "cdd");
				$code = $this->bak_backend->getResultCode();
//				Kohana::log("debug", "ddd");
//				if ($code == Memcached::RES_SUCCESS) {
//					$this->switch_backend($id);
//				}
			}
		}
		return ($code == Memcached::RES_SUCCESS) ? $result : (is_array($id) ? array() : NULL);
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
		$result = @$this->backend->set($id, $data, $lifetime);
		@$this->bak_backend->set($id, $data, $lifetime);
		return $result;
	}

	protected function switch_backend($server_key)
	{
		$configure = config::item($this->config_name, true);

		$server = @$this->backend->getServerByKey($server_key);
		$sig = $server['host'].":".$server['port'];
		if (isset($this->dead_servers[$sig])) {
			return true;
		}
		else {
			$this->dead_servers[$sig] = true;
		}

		$bak_server = @$this->bak_backend->getServerByKey($server_key);
		Kohana::log("debug", "Memcached::getServerByKey $server_key, rescode ".$this->bak_backend->getResultCode());
		Kohana::log("alert", "SafeMemcached:switch dead server($sig) to {$bak_server['host']}:{$bak_server['port']}");

		foreach ($configure["servers"] as $key => $conf) {
			if ($conf['host'] == $server['host'] && $conf['port'] == $server['port']) {
				$configure["servers"][$key] = $this->update_backend($conf, $bak_server);
				break;
			}
		}
		foreach ($configure["bak_servers"] as $key => $conf) {
			if ($conf["host"] == $bak_server['host'] && $conf["port"] == $bak_server['port']) {
				$configure["bak_servers"][$key] = $this->update_backend($conf, $server);
				break;
			}
		}
		Kohana::config_clear($this->config_name);
		Kohana::config_set($this->config_name, $configure);
		return true;
	}

	private function update_backend($conf, $host) {
		$conf["host"] = $host["host"];
		$conf["port"] = $host["port"];
		if (isset($host["weight"])) {
			$conf["weight"] = $host["weight"];
		}
		else {
			unset($conf["weight"]);
		}
		return $conf;
	}
}
?>