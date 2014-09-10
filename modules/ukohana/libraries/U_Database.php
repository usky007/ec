<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Database transaction extension.
 *
 * $Id: U_Database.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Database extends Database_Core {
    /**
     * Transaction status variable
     *
     * @var in_transaction
     */
    private $in_transaction = 0;
    private $success_trans = FALSE;


    public function __construct($config='default')
    {
        parent::__construct($config);
    }

    public function __destruct()
    {
    	$this->success_trans = FALSE;
    	$this->trans_complete(TRUE);
    }

    /**
     * Status of transaction
     *
     * @return  void
     */
    public function is_in_trans()
    {
        return $this->in_transaction > 0;
    }

    /**
     * Start a transaction
     *
     * @return  void
     */
    public function trans_start()
    {
        if ($this->in_transaction++ == 0) {
            $this->query('SET AUTOCOMMIT=0');
            $this->query('START TRANSACTION');
            $this->success_trans = TRUE;
        }
    }

    /**
     * Commit the transaction
     *
     * @return  void
     */
    public function trans_commit()
    {
        $this->trans_complete();
    }

    /**
     * Undo the transaction
     *
     * @return  void
     */
    public function trans_rollback()
    {
        $this->success_trans = FALSE;
        $this->trans_complete();
    }

    private function trans_complete($force = FALSE)
    {
		if (($force && $this->is_in_trans()) || --$this->in_transaction == 0) {
			if ($this->success_trans)
			{
				$this->query('COMMIT');
			}
			else
			{
				$this->query('ROLLBACK');
			}
            $this->query('SET AUTOCOMMIT=1');
            $this->in_transaction = 0;
        }
        if ($this->in_transaction < 0)
        	$this->in_transaction = 0;
    }

    /**
	 * Runs a query into the driver and returns the result.
	 *
	 * @param   string  SQL query to execute
	 * @return  Database_Result
	 */
	public function query($sql = '')
	{
		if ($sql == '') return FALSE;

		// No link? Connect!
		$this->link or $this->connect();

		// Start the benchmark
		$start = microtime(TRUE);

		if (func_num_args() > 1) //if we have more than one argument ($sql)
		{
			$argv = func_get_args();
			$binds = (is_array(next($argv))) ? current($argv) : array_slice($argv, 1);
		}

		// Compile binds if needed
		if (isset($binds))
		{
			$sql = $this->compile_binds($sql, $binds);
		}

		// Fetch the result
		$result = $this->driver->query($this->last_query = $sql);

		//Kohana::log('debug', "query trace:$this->last_query");

		// Stop the benchmark
		$stop = microtime(TRUE);

		if ($this->config['benchmark'] == TRUE)
		{
			// Benchmark the query
			// By Tianium: Add Time Ticks
			Database::$benchmarks[] = array('query' => $sql, 'time' => $stop - $start, 'rows' => count($result), 'tick' => $stop);
		}

		return $result;
	}


    public function set($key, $value = '')
	{

		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}


		foreach ($key as $k => $v)
		{
			// Add a table prefix if the column includes the table.
			if (strpos($k, '.'))
				$k = $this->config['table_prefix'].$k;

			if(is_array($v))
			{
				$express = $v[0];
				$search = array();
				$replace = array();
				for($i=1;$i<count($v);$i++)
				{
					$search[$i-1] = $i."%";
					$replace[$i-1] = $v[$i];
				}

				$express = str_replace($search,$replace,$express);


				$this->set[$k]= $express;

			}
			else
			{
				$this->set[$k] = $this->driver->escape($v);
			}

		}

		return $this;
	}

    /* Allows key/value pairs to be set for inserting or updating.
	 *
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @return  Database_Core        This Database object.
	 */
	public function set_relative($key, $value = 1, $op = "+")
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			// Add a table prefix if the column includes the table.
			if (strpos($k, '.'))
				$k = $this->config['table_prefix'].$k;

			$this->set[$k] = $this->escape_column($k)." $op ".$this->driver->escape($v);
		}

		return $this;
	}


	public function set_expression($formatestring)
	{
		$argsnum = func_num_args();
		if($argsnum==0)
		{
			throw new U_Exception("error","missing Parameter formatstring ");
			return ;
		}

		$args = func_get_args();
		$expression=$args[0];
		for($i=1;$i<$argsnum;$i++)
		{
			$expression = str_replace($i."%",$args[$i],$expression);
		}
		return $expression;
	}

	public function set_function($key, $value)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$countv = count($v);

			$funname = $v[0];
			if (strpos($k, '.'))
				$k = $this->config['table_prefix'].$k;
			$stritem = "$funname(";
			for($i=1;$i<$countv;$i++)
			{
				if(is_array($v[$i]))
				{
					$param=count($v[$i])==1?$v[$i][0]:$v[$i][0].".".$v[$i][1];
					$stritem = $stritem.$param .",";
				}
				else
				{
					$stritem = $stritem.$this->driver->escape($v[$i]).",";
				}

			}


			if($countv>1)
			{
				$stritem = substr($stritem,0,strlen($stritem)-1);
			}
			$stritem  =  $stritem.")";
			//echo $stritem;exit;
			// Add a table prefix if the column includes the table.


			//$this->set[$k] = $this->escape_column($k)." $op ".$this->driver->escape($v);
			$this->set[$k] = $stritem;
		}

		return $this;
	}

	/**
	 * Generates the JOIN portion of the query.
	 *
	 * @param   string        table name
	 * @param   string|array  where key or array of key => value pairs
	 * @param   string        where value
	 * @param   string        type of join
	 * @return  Database_Core        This Database object.
	 */
	public function join($table, $key, $value = NULL, $type = '')
	{
		$join = array();

		if ( ! empty($type))
		{
			$type = strtoupper(trim($type));

			if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'), TRUE))
			{
				$type = '';
			}
			else
			{
				$type .= ' ';
			}
		}

		$cond = array();
		$keys  = is_array($key) ? $key : array($key => $value);
		foreach ($keys as $key => $value)
		{
			$key    = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'].$key : $key;

			if (is_string($value))
			{
				// Only escape if it's a string
				$value = $this->driver->escape_column($this->config['table_prefix'].$value);
			}
			else if (is_array($value) && isset($value['value']))
			{
				$value = $this->driver->escape($value['value']);
			}

			$cond[] = $this->driver->where($key, $value, 'AND ', count($cond), FALSE);
		}

		if ( ! is_array($this->join))
		{
			$this->join = array();
		}

		if ( ! is_array($table))
		{
			$table = array($table);
		}

		foreach ($table as $t)
		{
			if (is_string($t))
			{
				// TODO: Temporary solution, this should be moved to database driver (AS is checked for twice)
				if (stripos($t, ' AS ') !== FALSE)
				{
					$t = str_ireplace(' AS ', ' AS ', $t);

					list($table, $alias) = explode(' AS ', $t);

					// Attach prefix to both sides of the AS
					$t = $this->config['table_prefix'].$table.' AS '.$this->config['table_prefix'].$alias;
				}
				else
				{
					$t = $this->config['table_prefix'].$t;
				}
			}

			$join['tables'][] = $this->driver->escape_column($t);
		}

		$join['conditions'] = '('.trim(implode(' ', $cond)).')';
		$join['type'] = $type;

		$this->join[] = $join;

		return $this;
	}

	public function sum($table = FALSE,$columname, $where = NULL)
	{
		if (count($this->from) < 1)
		{
			if ($table == FALSE)
				throw new Kohana_Database_Exception('database.must_use_table');

			$this->from($table);
		}

		if ($where !== NULL)
		{
			$this->where($where);
		}

		$query = $this->select('SUM('.$columname.') AS '.
		$this->escape_column('sum_result'))->get()->result(TRUE);
		return (int) $query->current()->sum_result;
	}

	public function max($table = FALSE,$columname, $where = NULL)
	{
		if (count($this->from) < 1)
		{
			if ($table == FALSE)
				throw new Kohana_Database_Exception('database.must_use_table');

			$this->from($table);
		}

		if ($where !== NULL)
		{
			$this->where($where);
		}

		$query = $this->select('MAX('.$columname.') AS '.
		$this->escape_column('max_result'))->get()->result(TRUE);
		return (int) $query->current()->max_result;
	}
}
?>