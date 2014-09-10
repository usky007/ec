<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Provides a driver-based interface for creating, updating, deleting and finding
 * system preferences. Preference items are organized by categories.
 *
 * $Id: Preference.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Preference
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Preference {

	protected static $instances = array();

	// Driver object
	protected $base_driver;
	protected $driver;
	protected $category;

	/**
	 * Returns a singleton instance of Preference.
	 *
	 * @return  Preference
	 */
	public static function & instance($category, $driver = null)
	{
		if (!isset(Preference::$instances[$category])) {
			// prepare configuration
			$config = null;
			if (!is_null($category))
			{
				$config = config::item('preference.'.$category);
			}
			if (is_array($config))
			{
				// Append the default configuration options
				$config += config::item('preference.default', true);
			}
			else
			{
				// Load the default group
				$config = config::item('preference.default', true);
			}

			// driver not given , create driver by configuration
			if (is_null($driver)) {
				$driver_name = isset($config['driver']) ? $config['driver'] : "Preference_Dictionary_Driver";
				$params = isset($config['params']) ? $config['params'] : NULL;
				if (!Kohana::auto_load($driver_name))
					throw new Kohana_Exception('core.driver_not_found', $driver_name, "Preference");
				else
					$driver = new $driver_name($params);
			}
			// cache adapter
			if ($driver instanceof Cache_Driver) {
				$driver = new Preference_Cache_Driver($driver);
			}
			Preference::$instances[$category] = new Preference($category, $driver);
		}

		return Preference::$instances[$category];
	}

	/**
	 * Loads the configured driver and validates it.
	 */
	protected function __construct($category, $driver)
	{
		$this->category = $category;
		$this->base_driver = $this->driver = $driver;

		if (config::item("preference.default.benchmark"))
		{
			$this->driver = new Preference_Benchmark_Driver($this->driver);
		}

		Kohana::log('debug', "$category Preference Library initialized");
	}

	/**
	 * Set a preference item by key. Non-string data is automatically serialized.
	 *
	 * @param   string        preferences id
	 * @param   mixed         data to cache
	 * @param   double        last update timestamp
	 * @return  boolean
	 */
	function set($id, $data, $lock = null)
	{
		if (is_resource($data))
			throw new Kohana_Exception('cache.resources');

		// Sanitize the ID
		$id = $this->sanitize_id($id);

		return $this->driver->set($this->category, $id, $data, $lock);
	}

	/**
	 * Delete a preferences item by key.
	 *
	 * @param   string   preferences id
	 * @param   double        last update timestamp
	 * @return  boolean
	 */
	public function delete($id, $lock = null)
	{
		// Sanitize the ID
		$id = $this->sanitize_id($id);

		return $this->driver->delete($this->category, $id, $lock);
	}

	/**
	 * Delete all preference items.
	 *
	 * @return  boolean
	 */
	public function delete_all()
	{
		return $this->driver->delete($this->category, TRUE);
	}

	/**
	 * Fetches a preference by key. NULL is returned when a preference item is not found.
	 *
	 * @param   string  preferences id
	 * @param   double  last update timestamp
	 * @return  mixed   cached data or NULL
	 */
	public function get($id, & $lock = null)
	{
		// Sanitize the ID
		$id = $this->sanitize_id($id);
		return $this->driver->get($this->category, $id, $lock);
	}

	/**
	 * Fetches all of the preferences for a given category. An empty array will be
	 * returned when no matching preferences are found.
	 *
	 * @return  array   preference items in category.
	 */
	public function & entries($limit = NULL, $offset = NULL)
	{
		return $this->driver->entries($this->category, $limit, $offset);
	}

	/**
	 * Get driver instance
	 */
	public function get_driver()
	{
		return $this->base_driver;
	}

	/**
	 * Get category this preference belongs to.
	 */
	public function get_category()
	{
		return $this->category;
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
		return str_replace(array('/', '\\', ' '), '_', $id);
	}

} // End Cache
?>