<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Provides a driver-based interface for finding, creating, and deleting cached
 * resources. Caches are identified by a unique string. Tagging of caches is
 * also supported, and caches can be found and deleted by id or tag.
 * In this extension, factory method is refactored by using "category" instead of
 * "config" name to identified the cache object.
 *
 * $Id: U_Cache.php 330 2011-06-21 09:46:50Z zhangjyr $
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
interface Cache_Id_Generator {
	public function get_id($id);

	public function recover_id($id);
}

class Cache extends Cache_Core {
	protected static $instances = array();
	protected static $profiler_log = null;

	// Driver object
	protected $base_driver;

	// Id generator
	protected $id_gen = null;
	protected $delimiter = ":";

	// New interface supported?
	private $tag_manager = null;
	private $is_tag_supported = true;

	/**
	 * Returns a singleton instance of Cache.
	 *
	 * @param   string  category name
	 * @param	string  specify a driver to override config setting.
	 * @param   mixed	driven parameter, ignore if drive_name ungiven.
	 * @return  Cache
	 */
	public static function & instance($category = NULL, $driver_name = NULL, $param = NULL)
	{
		$name = is_null($category) ? 0 : $category;
		if ( ! isset(Cache::$instances[$name]))
		{
			// prepare configuration
			$config = null;
			if (!is_null($category))
			{
				$config = config::item('cache.'.$category);
			}
			if (is_array($config))
			{
				// Append the default configuration options
				$config += config::item('cache.default', true);
			}
			else
			{
				// Load the default group
				$config = config::item('cache.default', true);
			}

			// Set driver name

			if (is_null($driver_name)) {
				$driver_name = isset($config['driver']) ? $config['driver'] : "Script";
				$param = isset($config['params']) ? $config['params'] : NULL;
			}
			if (strstr($driver_name, "_") === FALSE)
				$driver_name = 'Cache_'.ucfirst($driver_name).'_Driver';

			// Load the driver
			if ( ! Kohana::auto_load($driver_name))
				throw new Kohana_Exception('core.driver_not_found', $driver_name, "Cache");

			// Initialize the driver
			$driver = new $driver_name($param);

			// Validate the driver
			if ( ! ($driver instanceof Cache_Driver))
				throw new Kohana_Exception('core.driver_implements', $driver_name, "Cache", 'Cache_Driver');

			// Create a new instance
			$manager = (!is_null($category) && $config['manageable']) ? new Cache_Category_Manager($name) : null;
			Cache::$instances[$name] = new Cache($driver, $config, $manager);
		}

		return Cache::$instances[$name];
	}

	public static function cached($category,$key,$callback)
	{

		$cache = Cache::instance($category);
		$val = $cache->get($key);
		if (!is_null($val)) {
			return $val;
		}

		if (!is_callable($callback)) {
			throw new Kohana_Exception('core.invalid_parameter', 'callback', __CLASS__, __FUNCTION__);
		}

		$args = NULL;
		if (func_num_args() > 3) {
			$args = func_get_args();
			$args = array_slice($args,3);
		}
		else {
			$args = array();
		}
		$val = call_user_func_array($callback, $args);
		if (!is_null($val)) {
			$cache->set($key, $val);
		}
		return $val;
	}

	public static function category_manager($category) {
		if (is_null($category))
			return NULL;

		if (isset(Cache::$instances[$category]))
			return Cache::$instances[$category]->id_gen;
		return new Cache_Category_Manager($category);
	}

	public static function categories() {
		return Cache_Category_Manager::categories();
	}

	/**
	 * Loads the configured driver and validates it.
	 *
	 * @param   array|string  custom configuration or config group name
	 * @return  void
	 */
	public function __construct(Cache_Driver &$driver, $config, Cache_Id_Generator $generator = null)
	{
		$this->config = $config;
		if (isset($this->config['delimiter'])) {
			$this->delimiter = $this->config['delimiter'];
		}
		$this->id_gen = $generator;

		$this->driver = $this->base_driver = $driver;
		if (!($this->driver instanceof Cache_Script_Driver) &&
			(!isset($this->config['no_script_cache']) || !$this->config['no_script_cache']))
		{
			$this->driver = new Cache_Script_Driver($this->driver);
		}
		if (!$this->driver->is_supported(Cache_Abstract_Driver::FEATURE_TAG))
		{
			$this->driver = new Cache_TagAdapter_Driver($this->driver);
		}
		if ($this->config['benchmark'])
		{
			$this->driver = new Cache_Benchmark_Driver($this->driver);
		}

		Kohana::log('debug', 'Cache Library initialized');

		//gc
		$this->config['requests'] = (int) $this->config['requests'];
		if ($this->config['requests'] > 0 AND mt_rand(1, $this->config['requests']) === 1)
		{
			// Do garbage collection
			$this->driver->delete_expired();
			Kohana::log('debug', 'Cache: Expired caches deleted.');
		}
	}

	/**
	 * Fetches all of the caches for a given tag. An empty array will be
	 * returned when no matching caches are found.
	 *
	 * @param   string  cache tag
	 * @return  array   all cache items matching the tag
	 */
	public function find($tag)
	{
		$vals = parent::find($this->sanitize_id($tag));
		$ids = array_keys($vals);
		foreach ($ids as $id)
		{
			$vals[$this->recover_id($id)] = $vals[$id];
			unset($vals[$id]);
		}
		return $vals;
	}

	/**
	 * Set a cache item by id. Tags may also be added and a custom lifetime
	 * can be set. Non-string data is automatically serialized.
	 *
	 * @param   string        unique cache id
	 * @param   mixed         data to cache
	 * @param   array|string  tags for this item
	 * @param   integer       number of seconds until the cache expires
	 * @return  boolean
	 */
	public function set($id, $data, $tags = NULL, $lifetime = NULL)
	{
		$tags = (array) $tags;
		if (!empty($tags))
		{
			foreach ($tags as $key => $tag)
				$tags[$key] = $this->sanitize_id($tag);
		}
		return parent::set($id, $data, $tags, $lifetime);
	}

	/**
	 * Delete all cache items with a given tag.
	 *
	 * @param   string   cache tag name
	 * @return  boolean
	 */
	public function delete_tag($tag)
	{
		return parent::delete_tag($this->sanitize_id($tag));
	}

	/**
	 * Get driver instance
	 */
	public function get_driver()
	{
		return $this->driver;
	}

	/**
	 * Replaces troublesome characters with underscores.
	 *
	 * @param   string   cache id
	 * @return  string
	 */
	protected function sanitize_id($id)
	{
		// Change slashes and spaces to underscores
		$id = parent::sanitize_id($id);
		if (!is_null($this->id_gen))
			$id = $this->id_gen->get_id($id);
		if (isset($this->config['domain']) && !empty($this->config['domain']))
			$id = $this->config['domain'].$this->delimiter.$id;
		return $id;
	}

	/**
	 * Recover input id
	 *
	 * @param   string   cache id
	 * @return  string
	 */
	protected function recover_id($id)
	{
		// Change slashes and spaces to underscores
		if (isset($this->config['domain']) && !empty($this->config['domain']) &&
			$id == strstr($this->config['domain'].$this->delimiter, $id)) {
			$id = substr($id, strlen($this->config['domain']) + 1);
		}
		if (!is_null($this->id_gen))
			$id = $this->id_gen->recover_id($id);
		return $id;
	}
} // End Cache

class Cache_Category_Manager implements Cache_Id_Generator {
	const PREFERENCE_CATEGORY = "cache_category";

	protected $category;
	protected $preference;
	protected $delimiter;

	public function __construct($category) {
		$this->category = $category;
		$this->preference = & Preference::instance(self::PREFERENCE_CATEGORY);
		$this->delimiter = config::item("cache.default.delimiter", false, ":");
	}

	public function clear() {
		$lock = 0;
		$ver = intval($this->preference->get($this->category, $lock));
		$this->preference->set($this->category, $ver+1, $lock);
	}

	public function get_id($id) {
		$ver = $this->preference->get($this->category);
		if (is_null($ver)) {
			$ver = "";
		}
		return "{$this->category}{$ver}{$this->delimiter}{$id}";
	}

	public function recover_id($id) {
		return substr(strstr($id, $this->delimiter), 1);
	}

	public static function categories() {
		$preference = & Preference::instance(self::PREFERENCE_CATEGORY);
		return $preference->entries();
	}
}