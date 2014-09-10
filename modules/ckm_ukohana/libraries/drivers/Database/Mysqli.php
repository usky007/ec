<?php defined('SYSPATH') OR die('No direct access allowed.');

if (!defined('MYSQLI_OPT_READ_TIMEOUT')) {
        define('MYSQLI_OPT_READ_TIMEOUT',  11);
}
if (!defined('MYSQLI_OPT_WRITE_TIMEOUT')) {
        define('MYSQLI_OPT_WRITE_TIMEOUT', 12);
}

/**
 * MySQLi Database Driver
 *
 * $Id: Mysqli.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Mysqli_Driver extends Database_Mysql_Driver {

	// Database connection link
	protected $link;
	protected $db_config;
	protected $statements = array();

	/**
	 * Sets the config for the class.
	 *
	 * @param  array  database configuration
	 */
	public function __construct($config)
	{
		$this->db_config = $config;

		Kohana::log('debug', 'MySQLi Database Driver Initialized');
	}

	/**
	 * Closes the database connection.
	 */
	public function __destruct()
	{
		is_object($this->link) and $this->link->close();
	}

	public function connect($pwd = null)
	{
		// Check if link already exists
		if (is_object($this->link))
			return $this->link;

		// Import the connect variables
		extract($this->db_config['connection']);
		if (isset($pwd)) {
			$pass = $pwd;
		}

		// Build the connection info
		$host = isset($host) ? $host : $socket;

		// Make the connection and select the database
		$this->link = mysqli_init();
		$this->link->options(MYSQLI_OPT_CONNECT_TIMEOUT,
			isset($this->db_config['connect_timeout']) ? $this->db_config['connect_timeout'] : 1);
		$this->link->options(MYSQLI_OPT_READ_TIMEOUT,
			isset($this->db_config['read_timeout']) ? $this->db_config['read_timeout'] : 3);
		$this->link->options(MYSQLI_OPT_WRITE_TIMEOUT,
			isset($this->db_config['write_timeout']) ? $this->db_config['write_timeout'] : 1);
		if ($this->link->real_connect($host, $user, $pass, $database, $port))
		{
			if ($charset = $this->db_config['character_set'])
			{
				$this->set_charset($charset);
			}

			// Clear password after successful connect
			$this->pwd = $this->db_config['connection']['pass'];
			$this->db_config['connection']['pass'] = NULL;

			return $this->link;
		}
		else {
			$this->link = null;
		}

		return FALSE;
	}

	public function query($sql)
	{
		// Only cache if it's turned on, and only cache if it's not a write statement
		if ($this->db_config['cache'] AND ! preg_match('#\b(?:INSERT|UPDATE|REPLACE|SET|DELETE|TRUNCATE)\b#i', $sql))
		{
			$hash = $this->query_hash($sql);

			if ( ! isset($this->query_cache[$hash]))
			{
				// Set the cached object
				$this->query_cache[$hash] = new Kohana_Mysqli_Result($this->link, $this->db_config['object'], $sql);
			}
			else
			{
				// Rewind cached result
				$this->query_cache[$hash]->rewind();
			}

			// Return the cached query
			return $this->query_cache[$hash];
		}

		try {
			return new Kohana_Mysqli_Result($this->link, $this->db_config['object'], $sql);
		}
		catch (Kohana_Database_Exception $kde) {
			switch ($this->link->errno) {
				case 2013: // Lost connection to MySQL server during query
					if (isset($this->db_config['reconnect']) && $this->db_config['reconnect']) {
						Kohana::log("info", "Query timeout, try reconnect.");
						$this->link->close();
						$this->link = null;
						$this->connect($this->pwd); // Query timeout, try reconnect.
					}
					break;
			}
			throw $kde;
		}
	}

	public function set_charset($charset)
	{
		if ($this->link->set_charset($charset) === FALSE)
			throw new Kohana_Database_Exception('database.error', $this->show_error());
	}

	public function escape_str($str)
	{
		if (!$this->db_config['escape'])
			return $str;

		is_object($this->link) or $this->connect();

		return $this->link->real_escape_string($str);
	}

	public function show_error()
	{
		return $this->link->error;
	}

} // End Database_Mysqli_Driver Class

/**
 * MySQLi Result
 */
class Kohana_Mysqli_Result extends Database_Result {

	// Database connection
	protected $link;

	// Data fetching types
	protected $fetch_type  = 'fetch_object';
	protected $return_type = MYSQLI_ASSOC;

	/**
	 * Sets up the result variables.
	 *
	 * @param  object    database link
	 * @param  boolean   return objects or arrays
	 * @param  string    SQL query that was run
	 */
	public function __construct($link, $object = TRUE, $sql)
	{
		$this->link = $link;

		if ( ! $this->link->multi_query($sql))
		{
			// SQL error
			Kohana::log("error", "db error:({$this->link->errno}){$this->link->error}");
			throw new Kohana_Database_Exception('database.error', $this->link->error.' - '.$sql);
		}
		else
		{
			$this->result = $this->link->store_result();

			// If the query is an object, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
			if (is_object($this->result))
			{
				$this->current_row = 0;
				$this->total_rows  = $this->result->num_rows;
				$this->fetch_type = ($object === TRUE) ? 'fetch_object' : 'fetch_array';
			}
			elseif ($this->link->error)
			{
				// SQL error
				Kohana::log("error", "db error:({$this->link->errno}){$this->link->error}");
				throw new Kohana_Database_Exception('database.error', $this->link->error.' - '.$sql);
			}
			else
			{
				// Its an DELETE, INSERT, REPLACE, or UPDATE query
				$this->insert_id  = $this->link->insert_id;
				$this->total_rows = $this->link->affected_rows;
			}
		}

		// Set result type
		$this->result($object);

		// Store the SQL
		$this->sql = $sql;
	}

	/**
	 * Magic __destruct function, frees the result.
	 */
	public function __destruct()
	{
		if (is_object($this->result))
		{
			$this->result->free_result();

			// this is kinda useless, but needs to be done to avoid the "Commands out of sync; you
			// can't run this command now" error. Basically, we get all results after the first one
			// (the one we actually need) and free them.
			if (is_resource($this->link) AND $this->link->more_results())
			{
				do
				{
					if ($result = $this->link->store_result())
					{
						$result->free_result();
					}
				} while ($this->link->next_result());
			}
		}
	}

	public function result($object = TRUE, $type = MYSQLI_ASSOC)
	{
		$this->fetch_type = ((bool) $object) ? 'fetch_object' : 'fetch_array';

		// This check has to be outside the previous statement, because we do not
		// know the state of fetch_type when $object = NULL
		// NOTE - The class set by $type must be defined before fetching the result,
		// autoloading is disabled to save a lot of stupid overhead.
		if ($this->fetch_type == 'fetch_object')
		{
			$this->return_type = (is_string($type) AND Kohana::auto_load($type)) ? $type : 'stdClass';
		}
		else
		{
			$this->return_type = $type;
		}

		return $this;
	}

	public function as_array($object = NULL, $type = MYSQLI_ASSOC)
	{
		return $this->result_array($object, $type);
	}

	public function result_array($object = NULL, $type = MYSQLI_ASSOC)
	{
		$rows = array();

		if (is_string($object))
		{
			$fetch = $object;
		}
		elseif (is_bool($object))
		{
			if ($object === TRUE)
			{
				$fetch = 'fetch_object';

				// NOTE - The class set by $type must be defined before fetching the result,
				// autoloading is disabled to save a lot of stupid overhead.
				$type = (is_string($type) AND Kohana::auto_load($type)) ? $type : 'stdClass';
			}
			else
			{
				$fetch = 'fetch_array';
			}
		}
		else
		{
			// Use the default config values
			$fetch = $this->fetch_type;

			if ($fetch == 'fetch_object')
			{
				$type = (is_string($type) AND Kohana::auto_load($type)) ? $type : 'stdClass';
			}
		}

		if ($this->result->num_rows)
		{
			// Reset the pointer location to make sure things work properly
			$this->result->data_seek(0);

			while ($row = $this->fetchImpl($fetch, $type))
			{
				$rows[] = $row;
			}
		}

		return isset($rows) ? $rows : array();
	}

	public function list_fields()
	{
		$field_names = array();
		while ($field = $this->result->fetch_field())
		{
			$field_names[] = $field->name;
		}

		return $field_names;
	}

	public function seek($offset)
	{
		if ($this->offsetExists($offset) AND $this->result->data_seek($offset))
		{
			// Set the current row to the offset
			$this->current_row = $offset;

			return TRUE;
		}

		return FALSE;
	}

	public function offsetGet($offset)
	{
		if ( ! $this->seek($offset))
			return FALSE;

		return $this->fetchImpl($this->fetch_type, $this->return_type);
	}

	protected function fetchImpl($fetch_type, $return_type) {
		if ($fetch_type == "fetch_object" && is_subclass_of($return_type, "ORM")) {
			// The reason why mysqli->fetch_object doesn't behavior as doced is unknown.
			// Reference from php.net:
			// Note that mysqli_fetch_object() sets the properties of the object before calling the object constructor.
			if ($arr = $this->result->fetch_assoc()) {
				return new $return_type($arr);
			}
			else {
				return $arr;
			}
        }
		return $this->result->$fetch_type($return_type);
	}

} // End Mysqli_Result Class

/**
 * MySQLi Prepared Statement (experimental)
 */
class Kohana_Mysqli_Statement {

	protected $link = NULL;
	protected $stmt;
	protected $var_names = array();
	protected $var_values = array();

	public function __construct($sql, $link)
	{
		$this->link = $link;

		$this->stmt = $this->link->prepare($sql);

		return $this;
	}

	public function __destruct()
	{
		$this->stmt->close();
	}

	// Sets the bind parameters
	public function bind_params($param_types, $params)
	{
		$this->var_names = array_keys($params);
		$this->var_values = array_values($params);
		call_user_func_array(array($this->stmt, 'bind_param'), array_merge($param_types, $var_names));

		return $this;
	}

	public function bind_result($params)
	{
		call_user_func_array(array($this->stmt, 'bind_result'), $params);
	}

	// Runs the statement
	public function execute()
	{
		foreach ($this->var_names as $key => $name)
		{
			$$name = $this->var_values[$key];
		}
		$this->stmt->execute();
		return $this->stmt;
	}
}
