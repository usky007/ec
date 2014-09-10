<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * ORM extension with cache capability.
 * Support feature:
 * Object cache, only object load by function "find" will be cached.
 * Dirty free, update opration like "save" and "delete" will make data invalidated.
 * Multikey, support caching data by multiple unique key, while "Dirty free" feature holds.
 *
 * $Id: ORM_Cached.php 626 2011-11-21 10:43:35Z zhangjyr $
 *
 * @package    ORM
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class ORM_Cached extends ORM {
	const ALTER_KEY_PREFIX = "c0b21ccc4a84451bab2222e597bb698d";
	private $_cache_category = "dbobj";

	protected $_cache = null;
	protected $_cached = FALSE;
	protected $_cachable = false; // If last fetched item cachable.

	/**
	 * Prepares the model database connection and loads the object.
	 *
	 * @param   mixed  parameter for find or object to load
	 * @return  void
	 */
	public function __construct($id = NULL)
	{
		$this->_object_name   = strtolower(substr(get_class($this), 0, -6));

		if (empty($this->_table_name))
		{
			// Table name is the same as the object name
			$this->_table_name = $this->_object_name;

			if ($this->_table_names_plural === TRUE)
			{
				// Make the table name plural
				$this->_table_name = inflector::plural($this->_table_name);
			}
		}

		$this->_cache_category .= "_".$this->_table_name;
		parent::__construct($id);
	}

	/**
	 * Allows serialization of only the object data and state, to prevent
	 * "stale" objects being unserialized, which also requires less memory.
	 *
	 * @return  array
	 */
	public function __sleep()
	{
		// Store only information about the object
		return array('_object_name', '_object', '_changed', '_loaded', '_saved', '_sorting');
	}

	/**
	 * Finds and loads a single database row into the object.
	 *
	 * @chainable
	 * @param   mixed  primary key or an array of clauses
	 * @return  ORM
	 */
	public function find($condition = NULL, $field = NULL, $forupdate = false)
	{
		// no cache support for pending conditions for now
		$this->_cachable = empty($this->_db_pending) && empty($this->_load_with);

		// prepare conditions to generate cacheid
		$cachecon = $condition;
		if ($this->_cachable && $cachecon === NULL && !empty($this->_object))
		{
			// use all non empty values as condition.
			$cachecon = array();
			foreach ($this->_object as $field => $value)
			{
				if ($value !== NULL)
					$cachecon[$field] = $value;
			}
			$this->_cachable = !empty($cachecon);
		}
		if ($this->_cachable)
		{
			$guid = $this->get_guid($cachecon, $field);
			$cache = $this->get_cache();
			$vals = $cache->get($guid);
			if ($vals !== NULL)
			{
				if (is_array($vals))
				{
					$this->import_values($vals);
					$this->_cached = TRUE;
					return $this;
				}
				else if (is_string($vals) && strstr($vals, self::ALTER_KEY_PREFIX) === $vals)
				{
					// overwrite $id value.
					$this->find(substr($vals, strlen(self::ALTER_KEY_PREFIX)));
					if (!$this->_cached)
					{
						$cache->delete($guid);
					}
					return $this;
				}
				else
				{
					// not likely , assert
					$vals = NULL; // Sometimes, memcached returns "", which illegal, when matching server is down.
					//throw new Kohana_Exception('core.assert_failure', "Invalid value:".var_export($vals, true));
				}
			}

		}

		parent::find($cachecon, $field);

		if (!$forupdate && $this->_loaded && $this->_cachable)
		{
			$primary_guid = $this->get_guid();
            $cached = TRUE;
			if ($primary_guid != $guid)
			{
				// save a id cache for fetch
				$cached = $cache->set($guid, self::ALTER_KEY_PREFIX.$primary_guid);
                if (!$cached) {
                    log::error("Can't cache object in ORM_Cached, id:".$guid);
                }
			}
			// only save value by primary_key, if removed, alter keys will
            if ($cached && !$cache->set($primary_guid, $this->export_values())) {
                log::error("Can't cache object in ORM_Cached, id:".$primary_guid);
                $cached = FALSE;
            }
			$this->_cached = $cached;
		}

		return $this;
	}

	/**
	 * Saves the current object.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function save()
	{
		$removable =  !$this->to_be_created() && (!empty($this->_changed) || !empty($this->_changed_relations));
		$return = parent::save();

		if ($removable)
		{
			$this->get_cache()->delete($this->get_guid());
			$this->_cached = false;
		}

		return $return;
	}

	/**
	 * Deletes the current object from the database. This does NOT destroy
	 * relationships that have been created with other objects.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function delete($id = NULL)
	{
		if ($id === NULL AND $this->_loaded)
		{
			// Use the the primary key value
			$id = $this->pk();
		}

		$return = parent::delete($id);

		// cache operation
		if (!$this->_loaded || $this->_cached) {
			$guid = $this->get_guid($id);
			$cache = $this->get_cache();
			$cache->delete($guid);
			$this->_cached = false;
		}

		return $return;
	}

	/**
	 * Delete all objects in the associated table. This does NOT destroy
	 * relationships that have been created with other objects.
	 *
	 * @chainable
	 * @param   array  ids to delete
	 * @return  ORM
	 */
	public function delete_all()
	{
		$return = parent::delete_all();

		// Cache Operation
		$this->clear_cached_objs();

		return $return;
	}

	/**
	 * If object is loaded from Cache
	 */
	public function cached() {
		return $this->_cached;
	}

	/**
	 * Import an array of values into the current object.
	 *
	 * @chainable
	 * @param   array  values to import
	 * @return  ORM
	 */
	public function import_values($arr)
	{
		foreach ($arr as $key => $val) {
			$this->$key = $val;
		}
		return $this;
	}

	/**
	 * Export an array of values which can be used for importation.
	 *
	 * @chainable
	 * @return   array  exported values
	 */
	public function export_values()
	{
		$out = array();
		foreach (array("_object", "_loaded") as $key) {
			$out[$key] = $this->$key;
		}
		return $out;
	}

	public function clear()
	{
		parent::clear();
		$this->_cached = false;
	}

	protected function get_guid($id = NULL, $field = NULL)
	{
		// use primary key by default
		if ($id === NULL)
		{
			$id = $this->pk();
		}

		$modifier = "";
		// deal multifield key
		if (is_array($id))
		{
			$id_arr = $id;
			$id = "";
			// key order by definition of table
			foreach (array_keys($this->_table_columns) as $column)
			{
				if (isset($id_arr[$column]))
				{
					// remove '@' and ":" to avoid hacking.
					$id .= str_replace(array("@", ":"), "_", $id_arr[$column]).":";
				}
			}
			if (empty($id))
			{
				// not likely , assert
				throw new Kohana_Exception('core.assert_failure', "Id can't be empty.");
			}
			$id = substr($id, 0, strlen($id) - 1);
		}
		else if ($field !== NULL && $field != $this->_primary_key)
		{
			$modifier = "@$field:";
		}

		return "{$modifier}$id";
	}

	protected function get_cache()
	{
		if ($this->_cache === null)
		{
			$this->_cache = &Cache::instance($this->_cache_category);
		}
		return $this->_cache;
	}

	protected function clear_cached_objs()
	{
		$ca = Cache::category_manager($this->_cache_category);
		if (!is_null($ca))
			$ca->clear();
		return true;
	}

	/**
	 * Reload column definitions.
	 *
	 * @chainable
	 * @param   boolean  force reloading
	 * @return  ORM
	 */
//	public function reload_columns($force = FALSE)
//	{
//		if ($force === TRUE OR empty($this->_table_columns))
//		{
//			if (isset(ORM::$_column_cache[$this->_object_name]))
//			{
//				// Use cached column information
//				$this->_table_columns = ORM::$_column_cache[$this->_object_name];
//			}
//			else
//			{
//				// Load table columns
//				$columns = $this->get_cache()->get("TC_".$this->_table_name);
//
//				if ($columns == null) {
//					$columns = $this->list_columns();
//					$this->get_cache()->set("TC_".$this->_table_name, $columns);
//				}
//				ORM::$_column_cache[$this->_object_name] = $this->_table_columns = $columns;
//			}
//		}
//
//		return $this;
//	}
}
?>