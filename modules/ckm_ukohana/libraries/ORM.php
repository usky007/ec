<?php defined('SYSPATH') or die('No direct script access.');
/**
 * [Object Relational Mapping][ref-orm] (ORM) is a method of abstracting database
 * access to standard PHP calls. All table rows are represented as model objects,
 * with object properties representing row data. ORM in Kohana generally follows
 * the [Active Record][ref-act] pattern.
 *
 * [ref-orm]: http://wikipedia.org/wiki/Object-relational_mapping
 * [ref-act]: http://wikipedia.org/wiki/Active_record
 *
 * Inheritence hint:
 *
 * _belongs_to, _has_one, simple _has_many format:
 * array (alias => array("foreign_key" => related_column, "model" => related_model))
 * if related_column = alias/_table_name + _foreign_key_suffix, "foreign_key" can be omitted.
 * if related_model = alias, "model" can be ommitted.
 *
 * formal _has_many format:
 * array (alias => array("foreign_key" => through_column, "model" => related_model,
 *   "through" => relation_table, "far_key" => related_column),
 *   ["where" => array(through_column => value), "orderby" => array(through_column => direction)])
 * if through_column = _table_name + _foreign_key_suffix, "foreign_key" can be omitted.
 * if related_column = alias + _foreign_key_suffix, "far_key" can be omitted.
 * if related_model = alias, "model" can be ommitted.
 *
 * _updated_column, _created_column format:
 * array ("column" => column_name, "format" => date_format)
 * if use unix time() as timestamp, "format" can be omitted.
 *
 * _sorting format:
 * array (column_name => direction)
 *
 * $Id: ORM.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ORM
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class ORM_Core {

	// Current relationships
	protected static $time = NULL;
	protected $_has_one    = array();	// configurable on extending
	protected $_belongs_to = array();	// configurable on extending
	protected $_has_many   = array();	// configurable on extending

	// Relationships that should always be joined
	protected $_load_with = array();	// configurable on extending

	// Validation members
	protected $_validate  = NULL;
	protected $_rules     = array();
	protected $_callbacks = array();
	protected $_filters   = array();
	protected $_labels    = array();

	// Current object
	protected $_object  = array();
	protected $_changed = array();
	protected $_related = array();
	protected $_loaded  = FALSE;
	protected $_saved   = FALSE;
	protected $_sorting;	// configurable on extending

	// Foreign key suffix
	protected $_foreign_key_suffix = '_id';	// configurable on extending

	// Model table information
	protected $_object_name;
	protected $_object_plural;
	protected $_table_name;	// configurable on extending
	protected $_table_columns;
	protected $_ignored_columns = array();	// configurable on extending

	// Auto-update columns for creation and updates
	protected $_updated_column = NULL;	// configurable on extending
	protected $_created_column = NULL;	// configurable on extending

	// Table primary key and value
	protected $_primary_key  = 'id';	// configurable on extending
	protected $_primary_val  = 'name';	// configurable on extending

	// Model configuration
	protected $_table_names_plural = TRUE;	// configurable on extending
	protected $_reload_on_wakeup   = FALSE;	// configurable on extending

	// Database configuration
	protected $_db         = 'default';	// configurable on extending
	protected $_db_applied = array();
	protected $_db_pending = array();
	protected $_db_reset   = TRUE;

	// With calls already applied
	protected $_with_applied = array();

	// Data to be loaded into the model from a database call cast
	protected $_preload_data = array();

	// Stores column information for ORM models
	protected static $_column_cache = array();

	protected $_last_query_result;

	// Callable database methods
	protected static $_db_methods = array
	(
		'in', 'where', 'orwhere', 'where_open', 'and_where_open', 'or_where_open', 'where_close',
		'and_where_close', 'or_where_close', 'like', 'orlike', 'distinct', 'select', 'from', 'join', 'groupby',
		'having', 'orhaving', 'having_open', 'and_having_open', 'or_having_open',
		'having_close', 'and_having_close', 'or_having_close', 'orderby', 'limit', 'offset', 'cached'
	);

	// Members that have access methods
	protected static $_properties = array
	(
		'object_name', 'object_plural', 'loaded', 'saved', // Object
		'primary_key', 'primary_val', 'table_name', 'table_columns', // Table
		'has_one', 'belongs_to', 'has_many', 'has_many_through', 'load_with', // Relationships
		'validate', 'rules', 'callbacks', 'filters', 'labels' // Validation
	);

	/**
	 * Creates and returns a new model.
	 *
	 * @chainable
	 * @param   string  model name
	 * @param   mixed   parameter for find()
	 * @return  ORM
	 */
	public static function factory($model, $id = NULL)
	{
		// Set class name
		$model = ucfirst($model).'_Model';

		return new $model($id);
	}

	/**
	 * Prepares the model database connection and loads the object.
	 *
	 * @param   mixed  Data to be initialized into model, or id if not an array.
	 * @return  void
	 */
	public function __construct($data = NULL)
	{
		// Set the object name and plural name
		$this->_object_name   = strtolower(substr(get_class($this), 0, -6));
		$this->_object_plural = inflector::plural($this->_object_name);

		if ( ! isset($this->_sorting))
		{
			// Default sorting
			$this->_sorting = array($this->_primary_key => 'ASC');
		}

		if ( ! empty($this->_ignored_columns))
		{
			// Optimize for performance
			$this->_ignored_columns = array_combine($this->_ignored_columns, $this->_ignored_columns);
		}

		// Initialize database
		$this->_initialize();

		// Clear the object
		$this->clear();

		// Initialize data
		$this->_initialize_data($data);
	}

	/**
	 * Checks if object data is set.
	 *
	 * @param   string  column name
	 * @return  boolean
	 */
	public function __isset($column)
	{
		return
		(
			isset($this->_object[$column]) OR
			isset($this->_related[$column]) OR
			isset($this->_has_one[$column]) OR
			isset($this->_belongs_to[$column]) OR
			isset($this->_has_many[$column])
		);
	}

	/**
	 * Unsets object data.
	 *
	 * @param   string  column name
	 * @return  void
	 */
	public function __unset($column)
	{
		unset($this->_object[$column], $this->_changed[$column], $this->_related[$column]);
	}

	/**
	 * Displays the primary key of a model when it is converted to a string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return (string) $this->pk();
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
	 * Prepares the database connection and reloads the object.
	 *
	 * @return  void
	 */
	public function __wakeup()
	{
		// Initialize database
		$this->_initialize();

		if ($this->_reload_on_wakeup === TRUE)
		{
			// Reload the object
			$this->reload();
		}
	}

	/**
	 * Handles pass-through to database methods. Calls to query methods
	 * (query, get, insert, update) are not allowed. Query builder methods
	 * are chainable.
	 *
	 * @param   string  method name
	 * @param   array   method arguments
	 * @return  mixed
	 */
	public function __call($method, array $args)
	{
		if (in_array($method, ORM::$_properties))
		{
			if ($method === 'validate')
			{
				if ( ! isset($this->_validate))
				{
					// Initialize the validation object
					$this->_validate();
				}
			}

			// Return the property
			return $this->{'_'.$method};
		}
		elseif (in_array($method, ORM::$_db_methods))
		{
			// Add pending database call which is executed after query type is determined
			$this->_db_pending[] = array('name' => $method, 'args' => $args);

			return $this;
		}
		else
		{
			throw new Kohana_Exception('core.invalid_method', $method, get_class($this));
		}
	}

	/**
	 * Handles retrieval of all model values, relationships, and metadata.
	 *
	 * @param   string  column name
	 * @return  mixed
	 */
	public function __get($column)
	{
		if (array_key_exists($column, $this->_object))
		{
			return $this->_object[$column];
		}
		elseif (isset($this->_table_columns[$column]))
		{
			// load and return
			$this->_load();
			return $this->_object[$column];
		}
		elseif (isset($this->_related[$column]) AND $this->_related[$column]->_loaded)
		{

			// Return related model that has already been loaded
			return $this->_related[$column];
		}
		elseif (isset($this->_belongs_to[$column]))
		{
			$this->_load();

			$model = $this->_related($column);

			// Use this model's column and foreign model's primary key
			$col = $model->_primary_key;
			$val = $this->_object[$this->_belongs_to[$column]['foreign_key']];

			$model->_initialize_data(array($col => $val));
			//$model->where($model->_table_name.'.'.$col, $val);
			if (!isset($this->_belongs_to[$column]['variables'])) {
				$model->find();
			}

			return $this->_related[$column] = $model;
		}
		elseif (isset($this->_has_one[$column]))
		{
			$model = $this->_related($column);

			// Use this model's primary key value and foreign model's column
			$col = $this->_has_one[$column]['foreign_key'];
			$val = $this->pk();

			$model->_initialize_data(array($col => $val));
			//$model->where($model->_table_name.'.'.$col, $val);
			if (!isset($this->_has_one[$column]['variables'])) {
				$model->find();
			}

			return $this->_related[$column] = $model;
		}
		elseif (isset($this->_has_many[$column]))
		{
			$model = ORM::factory($this->_has_many[$column]['model']);

			if (isset($this->_has_many[$column]['through']))
			{
				// Grab has_many "through" relationship table
				$through = $this->_has_many[$column]['through'];
				$model->from($through);

				// Join on through model's target foreign key (far_key) and target model's primary key
				$join_col1 = $through.'.'.$this->_has_many[$column]['far_key'];
				$join_col2 = $model->_table_name.'.'.$model->_primary_key;

				$model->join($model->_table_name, $join_col1, $join_col2, "INNER");
				if (isset($this->_has_many[$column]['where'])) {
					foreach ($this->_has_many[$column]['where'] as $key => $val) {
						$model->where($through.'.'.$key, $val);
					}
				}
				if (isset($this->_has_many[$column]['orderby'])) {
					foreach ($this->_has_many[$column]['orderby'] as $key => $direction) {
						$model->orderby($through.'.'.$key, $direction);
					}
				}

				// Through table's source foreign key (foreign_key) should be this model's primary key
				$col = $through.'.'.$this->_has_many[$column]['foreign_key'];
				$val = $this->pk();
			}
			else
			{
				// Simple has_many relationship, search where target model's foreign key is this model's primary key
				$col = $model->_table_name.'.'.$this->_has_many[$column]['foreign_key'];
				$val = $this->pk();
			}

			return $model->where($col, $val);
		}
		if (in_array($column, ORM::$_properties))
		{
			return $this->$column();
		}
		else
		{
			throw new Kohana_Exception('core.invalid_property', $column, get_class($this));
		}
	}

	/**
	 * Handles setting of all model values, and tracks changes between values.
	 *
	 * @param   string  column name
	 * @param   mixed   column value
	 * @return  void
	 */
	public function __set($column, $value)
	{
		if ( ! isset($this->_object_name))
		{
			// Object not yet constructed, so we're loading data from a database call cast
			$this->_preload_data[$column] = $value;

			return;
		}

		if (array_key_exists($column, $this->_ignored_columns))
		{
			// No processing for ignored columns, just store it
			$this->_object[$column] = $value;
		}
		elseif (array_key_exists($column, $this->_object))
		{
			if (isset($this->_table_columns[$column]) && $this->_object[$column] !== $value)
			{
				$this->_object[$column] = $value;

				// Data has changed
				$this->_changed[$column] = $column;

				// Object is no longer saved
				$this->_saved = FALSE;
			}
		}
		elseif (isset($this->_belongs_to[$column]))
		{
			// Update related object itself
			$this->_related[$column] = $value;

			// Update the foreign key of this model
			$this->_object[$this->_belongs_to[$column]['foreign_key']] = $value->pk();

			$this->_changed[$column] = $this->_belongs_to[$column]['foreign_key'];
		}
		else
		{
			throw new Kohana_Exception('core.invalid_property', $column, get_class($this));
		}
	}

	/**
	 * Set values from an array with support for one-one relationships.  This method should be used
	 * for loading in post data, etc.
	 *
	 * @param   array  array of key => val
	 * @return  ORM
	 */
	public function values($values)
	{
		foreach ($values as $key => $value)
		{
			if (array_key_exists($key, $this->_object) OR array_key_exists($key, $this->_ignored_columns))
			{
				// Property of this model
				$this->__set($key, $value);
			}
			elseif (isset($this->_belongs_to[$key]) OR isset($this->_has_one[$key]))
			{
				// Value is an array of properties for the related model
				$this->_related[$key] = $value;
			}
		}

		return $this;
	}

	protected function _initialize_data($data)
	{
		if ($data !== NULL)
		{
			if (is_array($data))
			{
//				foreach ($data as $column => $value)
//				{
//					$this->_object[$column] = $value;
//				}
				$this->_load_values($data);
			}
			else
			{
				// Passing the primary key

				// Set the object's primary key, but don't load it until needed
				$this->_object[$this->_primary_key] = $data;
			}
			// consider be saved
			$this->_saved = TRUE;
		}
		elseif ( ! empty($this->_preload_data))
		{
			// Load preloaded data from a database call cast
			$this->_load_values($this->_preload_data);

			$this->_preload_data = array();
		}
	}

	/**
	 * Prepares the model database connection, determines the table name,
	 * and loads column information.
	 *
	 * @return  void
	 */
	protected function _initialize()
	{
		if ( ! is_object($this->_db))
		{
			// Get database instance
			$this->_db = Database::instance($this->_db);
		}

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

		foreach ($this->_belongs_to as $alias => $details)
		{
			$defaults['model']       = $alias;
			$defaults['foreign_key'] = $alias.$this->_foreign_key_suffix;

			$this->_belongs_to[$alias] = array_merge($defaults, $details);
		}

		foreach ($this->_has_one as $alias => $details)
		{
			$defaults['model']       = $alias;
			$defaults['foreign_key'] = $this->_object_name.$this->_foreign_key_suffix;

			$this->_has_one[$alias] = array_merge($defaults, $details);
		}

		foreach ($this->_has_many as $alias => $details)
		{
			$defaults['model']       = inflector::singular($alias);
			$defaults['foreign_key'] = $this->_object_name.$this->_foreign_key_suffix;
			$defaults['through']     = NULL;
			$defaults['far_key']     = NULL;

			$details = $this->_has_many[$alias] = array_merge($defaults, $details);

			if ($details['through'] != NULL && $details['far_key'] == NULL)
				$this->_has_many[$alias]['far_key'] = inflector::singular($alias).$this->_foreign_key_suffix;

		}

		// Load column information
		$this->reload_columns();
	}

	/**
	 * Initializes validation rules, callbacks, filters, and labels
	 *
	 * @return void
	 */
	protected function _validate()
	{
		$this->_validate = Validate::factory($this->_object);

		foreach ($this->_rules as $field => $rules)
		{
			$this->_validate->rules($field, $rules);
		}

		foreach ($this->_filters as $field => $filters)
		{
			$this->_validate->filters($field, $filters);
		}

		// Use column names by default for labels
		$columns = array_keys($this->_table_columns);

		// Merge user-defined labels
		$labels = array_merge(array_combine($columns, $columns), $this->_labels);

		foreach ($labels as $field => $label)
		{
			$this->_validate->label($field, $label);
		}

		foreach ($this->_callbacks as $field => $callbacks)
		{
			foreach ($callbacks as $callback)
			{
				if (is_string($callback) AND method_exists($this, $callback))
				{
					// Callback method exists in current ORM model
					$this->_validate->callback($field, array($this, $callback));
				}
				else
				{
					// Try global function
					$this->_validate->callback($field, $callback);
				}
			}
		}
	}

	/**
	 * Returns the values of this object as an array, including any related one-one
	 * models that have already been loaded using with()
	 *
	 * @return  array
	 */
	public function as_array()
	{
		$object = array();

		foreach ($this->_object as $key => $val)
		{
			// Call __get for any user processing
			$object[$key] = $this->__get($key);
		}

		foreach ($this->_related as $key => $model)
		{
			// Include any related objects that are already loaded
			$object[$key] = $model->as_array();
		}

		return $object;
	}

	/**
	 * Binds another one-to-one object to this model.  One-to-one objects
	 * can be nested using 'object1:object2' syntax
	 *
	 * @param   string  target model to bind to
	 * @return  void
	 */
	public function with($target_path, $required = true, $more_key = null, $more_val = null)
	{
		if (isset($this->_with_applied[$target_path]))
		{
			// Don't join anything already joined
			return $this;
		}

		// Split object parts
		$aliases = explode(':', $target_path);
		$target	 = $this;
		foreach ($aliases as $alias)
		{
			// Go down the line of objects to find the given target
			$parent = $target;
			$target = $parent->_related($alias);

			if ( ! $target)
			{
				// Can't find related object
				return $this;
			}
		}

		// Target alias is at the end
		$target_alias = $alias;

		// Pop-off top alias to get the parent path (user:photo:tag becomes user:photo - the parent table prefix)
		array_pop($aliases);
		$parent_path = implode(':', $aliases);

		if (empty($parent_path))
		{
			// Use this table name itself for the parent path
			$parent_path = $this->_table_name;
		}
		else
		{
			if( ! isset($this->_with_applied[$parent_path]))
			{
				// If the parent path hasn't been joined yet, do it first (otherwise JOINs fail)
				$this->with($parent_path);
			}
		}

		// Add to with_applied to prevent duplicate joins
		$this->_with_applied[$target_path] = TRUE;

		// Use the keys of the empty object to determine the columns
		foreach (array_keys($target->_object) as $column)
		{
			$name   = $target_path.'.'.$column;
			$alias  = $target_path.':'.$column;

			// Add the prefix so that load_result can determine the relationship
			$this->select("$name AS $alias");
		}

		// Extension: check more conditions
		$more_con = null;
		if ($more_key !== NULL) {
			if (!is_array($more_key)) {
				$more_con = array($more_key => $more_val);
			}
			else {
				$more_con = $more_key;
			}
		}
		else {
			$more_con = array();
		}

		$join_con = array();
		if (isset($parent->_belongs_to[$target_alias]))
		{
			// Parent belongs_to target, use target's primary key and parent's foreign key
			$join_col1 = $target_path.'.'.$target->_primary_key;
			$join_col2 = $parent_path.'.'.$parent->_belongs_to[$target_alias]['foreign_key'];

			if (isset($parent->_belongs_to[$target_alias]["variables"])) {
				$variables = explode(",", $parent->_belongs_to[$target_alias]["variables"]);
				foreach ($variables as $variable) {
					if (!isset($more_con[$variable]))
						throw new Kohana_Exception("Missing condition under current setting.");
					$join_con[$parent_path.'.'.$variable] = array("value"=>$more_con[$variable]);
				}
			}
		}
		else
		{
			// Parent has_one target, use parent's primary key as target's foreign key
			$join_col1 = $parent_path.'.'.$parent->_primary_key;
			$join_col2 = $target_path.'.'.$parent->_has_one[$target_alias]['foreign_key'];
			if (isset($parent->_has_one[$target_alias]["variables"])) {
				$variables = explode(",", $parent->_has_one[$target_alias]["variables"]);
				foreach ($variables as $variable) {
					if (!isset($more_con[$variable]))
						throw new Kohana_Exception("Missing condition under current setting.");
					$join_con[$target_path.'.'.$variable] = array("value"=>$more_con[$variable]);
				}
			}
		}
		$join_con[$join_col1] = $join_col2;

		// Join the related object into the result
		$this->join("$target->_table_name AS $target_path", $join_con, null, $required ? 'INNER' : 'LEFT OUTER');

		return $this;
	}

	/**
	 * Initializes the Database Builder to given query type
	 *
	 * @param   int  Type of Database query
	 * @return  ORM
	 */
	protected function _build()
	{
		// Process pending database method calls
		foreach ($this->_db_pending as $method)
		{
			$name = $method['name'];
			$args = $method['args'];

			$this->_db_applied[$name] = $name;

			switch (count($args))
			{
				case 0:
					$this->_db->$name();
				break;
				case 1:
					$this->_db->$name($args[0]);
				break;
				case 2:
					$this->_db->$name($args[0], $args[1]);
				break;
				case 3:
					$this->_db->$name($args[0], $args[1], $args[2]);
				break;
				case 4:
					$this->_db->$name($args[0], $args[1], $args[2], $args[3]);
				break;
				default:
					// Here comes the snail...
					call_user_func_array(array($this->_db, $name), $args);
				break;
			}
		}

		return $this;
	}

	/**
	 * Loads the given model
	 *
	 * @return  ORM
	 */
	protected function _load()
	{
		if ( ! $this->_loaded AND ! empty($this->_object) AND $this->_saved)
		{
			// Only load if it hasn't been loaded, and possible conditions available and hasn't been modified
			return $this->find();
		}
		return $this;
	}

	/**
	 * Finds and loads a single database row into the object.
	 *
	 * @chainable
	 * @param mixed condition An array of clauses or unique key value.
	 * @param string field The field name of unique key value or primary key if omitted, ignore if condition is an array.
	 * @return ORM
	 */
	public function find($condition = NULL, $field = NULL)
	{
		if ( ! empty($this->_load_with))
		{
			foreach ($this->_load_with as $alias)
			{
				// Bind relationship
				$this->with($alias);
			}
		}

		$this->_build();

		if (!isset($this->_db_applied["where"]) && $condition === NULL &&
			!empty($this->_object))
		{
			// use all non empty values as condition.
			$condition = array();
			foreach ($this->_object as $field => $value)
			{
				if ($value !== NULL)
					$condition[$field] = $value;
			}
		}

		if ($condition !== NULL && !empty($condition))
		{
			if (is_array($condition))
			{
				// Search for all clauses
				$this->_db->where($condition);
			}
			else
			{
				// Search for a specific column
				$this->_db->where($this->_table_name.'.'.($field !== NULL ? $field : $this->_primary_key), $condition);
			}
		}

		return $this->_load_result(FALSE);
	}

	/**
	 * Finds multiple database rows and returns an iterator of the rows found.
	 *
	 * @chainable
	 * @return  Database_Result
	 */
	public function find_all($limit = NULL, $offset = NULL)
	{
		if ( ! empty($this->_load_with))
		{
			foreach ($this->_load_with as $alias)
			{
				// Bind relationship
				$this->with($alias);
			}
		}

		$this->_build();

		if ($limit !== NULL)
		{
			// Set limit
			$this->_db->limit($limit);
		}

		if ($offset !== NULL)
		{
			// Set offset
			$this->_db->offset($offset);
		}

		return $this->_load_result(TRUE);
	}

	/**
	 * Validates the current model's data
	 *
	 * @return  boolean
	 */
	public function check()
	{
		if ( ! isset($this->_validate))
		{
			// Initialize the validation object
			$this->_validate();
		}
		else
		{
			// Validation object has been created, just exchange the data array
			$this->_validate->exchangeArray($this->_object);
		}

		if ($this->_validate->check())
		{
			// Fields may have been modified by filters
			$this->_object = array_merge($this->_object, $this->_validate->getArrayCopy());

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Is corresponding record of object not exists yet? 
	 */
	public function to_be_created()
	{
		return $this->empty_pk() || isset($this->_changed[$this->_primary_key]);
	}

	/**
	 * Saves the current object.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function save()
	{
		if (empty($this->_changed))
			return $this;

		$data = array();
		foreach ($this->_changed as $column)
		{
			// Compile changed data
			$data[$column] = $this->_object[$column];
		}

		if ( ! $this->empty_pk() AND ! isset($this->_changed[$this->_primary_key]))
		{
			// Primary key isn't empty and hasn't been changed so do an update

			if (is_array($this->_updated_column))
			{
				// Fill the updated column
				$column = $this->_updated_column['column'];
				$format = isset($this->_updated_column['format']) ? $this->_updated_column['format'] : TRUE;

				if(!isset($data[$column]))
				{
					$data[$column] = $this->_object[$column] = ($format === TRUE) ?
					ORM::get_time() : date($format, ORM::get_time());
				}

			}

			$query = $this->_db->set($data)
				->where($this->_primary_key, $this->pk())
				->update($this->_table_name);

			// Object has been saved
			$this->_saved = TRUE;
		}
		else
		{
			if (is_array($this->_created_column))
			{
				// Fill the created column
				$column = $this->_created_column['column'];
				$format = isset($this->_created_column['format']) ? $this->_created_column['format'] : TRUE;
				if(!isset($data[$column]))
				{
					$data[$column] = $this->_object[$column] = ($format === TRUE) ?
						ORM::get_time() : date($format, ORM::get_time());
				}
			}
			if (is_array($this->_updated_column))
			{
				// Fill the updated column
				$column = $this->_updated_column['column'];
				$format = isset($this->_updated_column['format']) ? $this->_updated_column['format'] : TRUE;

				if(!isset($data[$column]))
				{
					$data[$column] = $this->_object[$column] = ($format === TRUE) ?
					ORM::get_time() : date($format, ORM::get_time());
				}
			}

			$result = $this->_db->insert($this->table_name, $data);

			if ($result)
			{
				if ($this->empty_pk())
				{
					// Load the insert id as the primary key
					// $result is array(insert_id, total_rows)
					$this->_object[$this->_primary_key] = $result[0];
				}

				// Object is now loaded and saved
				$this->_loaded = $this->_saved = TRUE;
			}
		}

		if ($this->_saved === TRUE)
		{
			// All changes have been saved
			$this->_changed = array();
		}

		return $this;
	}

	/**
	 * Updates all existing records
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function save_all()
	{
		$this->_build();

		if (empty($this->_changed))
			return $this;

		$data = array();
		foreach ($this->_changed as $column)
		{
			// Compile changed data omitting ignored columns
			$data[$column] = $this->_object[$column];
		}

		if (is_array($this->_updated_column))
		{
			// Fill the updated column
			$column = $this->_updated_column['column'];
			$format = isset($this->_updated_column['format']) ? $this->_updated_column['format'] : TRUE;

			$data[$column] = $this->_object[$column] = ($format === TRUE) ?
				ORM::get_time() : date($format, ORM::get_time());
		}

		$this->_db->set($data)->update($this->_table_name);

		return $this;
	}

	/**
	 * Deletes the current object from the database. This does NOT destroy
	 * relationships that have been created with other objects.
	 *
	 * @chainable
	 * @param   mixed  id to delete
	 * @return  ORM
	 */
	public function delete($id = NULL)
	{
		if ($id === NULL)
		{
			// Use the the primary key value
			$id = $this->pk();
		}

		if ( ! empty($id) OR $id === '0')
		{
			// Delete the object
			$this->_last_query_result = $this->_db->where($this->primary_key, $id)->delete($this->table_name);
		}

		return $this->clear();
	}

	/**
	 * Delete all objects in the associated table. This does NOT destroy
	 * relationships that have been created with other objects.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function delete_all()
	{
		$this->_build();

		$this->_last_query_result = $this->_db->delete($this->_table_name);

		return $this->clear();
	}

	/**
	 * Unloads the current object and clears the status.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function clear()
	{
		// Create an array with all the columns set to NULL
		$values = array_combine(array_keys($this->_table_columns), array_fill(0, count($this->_table_columns), NULL));

		// Replace the object and reset the object status
		$this->_object = $this->_changed = $this->_related = array();

		// Replace the current object with an empty one
		$this->_load_values($values);

		$this->reset();

		return $this;
	}

	/**
	 * Reloads the current object from the database.
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function reload()
	{
		$primary_key = $this->pk();

		// Replace the object and reset the object status
		$this->_object = $this->_changed = $this->_related = array();

		return $this->find($primary_key);
	}

	/**
	 * Reload column definitions.
	 *
	 * @chainable
	 * @param   boolean  force reloading
	 * @return  ORM
	 */
	public function reload_columns($force = FALSE)
	{
		if ($force === TRUE OR empty($this->_table_columns))
		{
			if (isset(ORM::$_column_cache[$this->_object_name]))
			{
				// Use cached column information
				$this->_table_columns = ORM::$_column_cache[$this->_object_name];
			}
			else
			{
				// Grab column information from database
				$this->_table_columns = $this->list_columns(TRUE);

				// Load column cache
				ORM::$_column_cache[$this->_object_name] = $this->_table_columns;
			}
		}

		return $this;
	}

	/**
	 * Tests if this object has a relationship to a different model.
	 *
	 * @param   string   alias of the has_many "through" relationship
	 * @param   ORM      related ORM model
	 * @return  boolean
	 */
	public function has($alias, ORM $model)
	{
		// Return count of matches as boolean
		return $this->_db->select('COUNT("*") AS records_found')
			->from($this->_has_many[$alias]['through'])
			->where($this->_has_many[$alias]['foreign_key'], $this->pk())
			->where($this->_has_many[$alias]['far_key'], $model->pk())
			->get()
			->result()->current()->records_found;
	}

	/**
	 * Adds a new relationship to between this model and another.
	 *
	 * @param   string   alias of the has_many "through" relationship
	 * @param   ORM      related ORM model
	 * @param   array    additional data to store in "through"/pivot table
	 * @return  ORM
	 */
	public function add($alias, ORM &$model, $moredata = NULL)
	{
		$this->_build();
		$columns = array($this->_has_many[$alias]['foreign_key'], $this->_has_many[$alias]['far_key']);
		$values  = array($this->pk(), $model->pk());

		$data = array_combine($columns, $values);

		if ($moredata !== NULL)
		{
			// Additional data stored in pivot table
			$data = array_merge($data, $moredata);
		}

		$this->_last_query_result = $this->_db->insert($this->_has_many[$alias]['through'], $data);

		return $this;
	}

	/**
	 * Update the metadata of a relationship between this model and another
	 *
	 * @param string	alias of the has_many "through" relationship
	 * @param ORM 		related ORM model
	 * @param array     data to update in "through"/pivot table
	 * @return ORM
	 */
	public function change($alias, ORM $model, $data)
	{
		$this->_build();
		$this->_last_query_result = $this->_db->where($this->_has_many[$alias]['foreign_key'], $this->pk())
			->where($this->_has_many[$alias]['far_key'], $model->pk())

			->update($this->_has_many[$alias]['through'], $data);

		return $this;
	}

	/**
	 * Removes a relationship between this model and another.
	 *
	 * @param   string   alias of the has_many "through" relationship
	 * @param   ORM      related ORM model
	 * @return  ORM
	 */
	public function remove($alias, ORM $model)
	{
		$this->_build();
		$this->_last_query_result = $this->_db->where($this->_has_many[$alias]['foreign_key'], $this->pk())
			->where($this->_has_many[$alias]['far_key'], $model->pk())
			->delete($this->_has_many[$alias]['through']);

		return $this;
	}

	/**
	 * Count the number of records in the table.
	 *
	 * @return  integer
	 */
	public function count_all()
	{
		$selects = array();

		foreach ($this->_db_pending as $key => $method)
		{
			if ($method['name'] == 'select')
			{
				// Ignore any selected columns for now
				$selects[] = $method;
				unset($this->_db_pending[$key]);
			}
		}

		$this->_build();
		
		if ( ! isset($this->_db_applied['from'])) {
			$this->_db->from($this->_table_name);
		}

		$records = $this->_db
			->select('COUNT("*") as records_found')
			->get()
			->result()->current()->records_found;

		// Add back in selected columns
		$this->_db_pending += $selects;

		$this->reset();

		// Return the total number of records in a table
		return $records;
	}

	/**
	 * Proxy method to Database list_columns.
	 *
	 * @return  array
	 */
	public function list_columns()
	{
		// Proxy to database
		return $this->_db->list_fields($this->_table_name);
	}

	/**
	 * Proxy method to Database field_data.
	 *
	 * @chainable
	 * @param   string  SQL query to clear
	 * @return  ORM
	 */
	public function clear_cache($sql = NULL)
	{
		// Proxy to database
		$this->_db->clear_cache($sql);

		ORM::$_column_cache = array();

		return $this;
	}

	/**
	 * Returns an ORM model for the given one-one related alias
	 *
	 * @param   string  alias name
	 * @return  ORM
	 */
	protected function _related($alias)
	{
		if (isset($this->_related[$alias]))
		{
			return $this->_related[$alias];
		}
		elseif (isset($this->_has_one[$alias]))
		{
			return $this->_related[$alias] = ORM::factory($this->_has_one[$alias]['model']);
		}
		elseif (isset($this->_belongs_to[$alias]))
		{
			return $this->_related[$alias] = ORM::factory($this->_belongs_to[$alias]['model']);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Loads an array of values into into the current object.
	 *
	 * @chainable
	 * @param   array  values to load
	 * @return  ORM
	 */
	protected function _load_values(array $values)
	{
		if (array_key_exists($this->_primary_key, $values))
		{
			// Set the loaded and saved object status based on the primary key
			$this->_loaded = $this->_saved = ($values[$this->_primary_key] !== NULL);
		}

		// Related objects
		$related = array();

		foreach ($values as $column => $value)
		{
			if (strpos($column, ':') === FALSE)
			{
				if ( ! isset($this->_changed[$column]))
				{
					$this->_object[$column] = $value;
				}
			}
			else
			{
				list ($prefix, $column) = explode(':', $column, 2);

				$related[$prefix][$column] = $value;
			}
		}

		if ( ! empty($related))
		{
			foreach ($related as $object => $values)
			{
				// Load the related objects with the values in the result
				$this->_related($object)->_load_values($values);
			}
		}

		return $this;
	}

	/**
	 * Loads a database result, either as a new object for this model, or as
	 * an iterator for multiple rows.
	 *
	 * @chainable
	 * @param   boolean       return an iterator or load a single row
	 * @return  ORM           for single rows
	 * @return  ORM_Iterator  for multiple rows
	 */
	protected function _load_result($multiple = FALSE)
	{
		if ( ! isset($this->_db_applied['from'])) {
			$this->_db->from($this->_table_name);
		}

		if ($multiple === FALSE)
		{
			// Only fetch 1 record
			$this->_db->limit(1);
		}

		// Select all columns by default
		$this->_db->select($this->_table_name.'.*');

		if ( ! isset($this->_db_applied['orderby']) AND ! empty($this->_sorting))
		{
			foreach ($this->_sorting as $column => $direction)
			{
				if (strpos($column, '.') === FALSE)
				{
					// Sorting column for use in JOINs
					$column = $this->_table_name.'.'.$column;
				}

				$this->_db->orderby($column, $direction);
			}
		}

		$result = $this->_db->get();
		if ($multiple === TRUE)
		{
			// Return an iterated result
			$iterator = $this->get_iterator($result);
			$this->reset();
			return $iterator;
		}
		else if ($result->count() === 1)
		{
			// Load object values
			$this->_load_values($result->result(FALSE)->current());
		}
		else
		{
			// Clear the object, nothing was found
			$this->clear();
		}

		return $this;
	}

	/**
	 * Returns the value of the unique key
	 *
	 * @return  mixed  unique key
	 */
	public function pk()
	{
		return $this->_object[$this->_primary_key];
	}

	/**
	 * Returns whether or not primary key is empty
	 *
	 * @return  bool
	 */
	protected function empty_pk()
	{
		return (empty($this->_object[$this->_primary_key]) AND $this->_object[$this->_primary_key] !== '0');
	}

	/**
	 * Returns last executed query
	 *
	 * @return  string
	 */
	public function last_query()
	{
		return $this->_db->last_query();
	}

	/**
	 * Clears query builder.  Passing FALSE is useful to keep the existing
	 * query conditions for another query.
	 *
	 * @param  bool  Pass FALSE to avoid resetting on the next call
	 */
	public function reset($next = TRUE)
	{
		if ($next AND $this->_db_reset)
		{
			$this->_db_pending   = array();
			$this->_db_applied   = array();
			$this->_with_applied = array();
		}

		// Reset on the next call?
		$this->_db_reset = $next;

		return $this;
	}

	/**
	 * Use this function to get same time through the session.
	 */
	public static function get_time($micro = FALSE) {
		if (!isset(ORM::$time))
			ORM::$time = microtime(TRUE);

		if ($micro)
			return ORM::$time;
		else
			return intval(floor(ORM::$time));
	}

	public function get_last_query_result()
	{
		return $this->_last_query_result;
	}
	
	public function dump()
	{
		return var_export($this->_object, true);
	}

	protected function get_iterator($result)
	{
		return new ORM_Iterator($this, $result);
	}
} // End ORM
