<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: sequence.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Sequence_Model extends ORM {
	protected $_table_name = "Sequences";
	protected $primary_key = 'name';
	protected $primary_val = 'id';

	public function __construct($name = NULL, $next = TRUE)
	{
		parent::__construct($next ? NULL : $name);
		if ($next)
			$this->next($name);
	}

	/**
	 * get next id(s)
	 *
	 * @param string name Name to identify sequence.
	 * @param int skip Skip n id.
	 * @param int num Num of Reserved ids for later use.
	 */
	public function next($name, $skip = 0, $num = 1, $base = 0)
	{
		if ($skip < 0)
			$skip = 0;
		if ($num < 1)
			$num = 1;

		// MySIAM implementation
//		$ts = time();
//		$table_name = $this->_db->table_prefix().$this->table_name;
//		$this->_db->query("LOCK TABLES {$table_name} WRITE");
//		$query = $this->db->query("SELECT id FROM {$table_name} WHERE name = ?", $name);
//		$id = ($query->count() == 0)? 1 : $query->current()->id + 1;
//		$this->_db->query("REPLACE INTO {$table_name} VALUES (?, ?, ?)", array($name, $id, $ts));
//		$this->_db->query("UNLOCK TABLES");
//
//		$this->load_values(array("name"=>$name, "id"=>$id, "timestamp"=>$ts));
//		return $this;
		// InnoDB implementation
		$ts = time();
		$table_name = $this->_db->table_prefix().$this->table_name;
		try
		{
			$offset = ($skip + 1) * $num;
			$this->_db->trans_start();
			$result = $this->_db->query("UPDATE {$table_name} SET id = id + $offset, timestamp = ? WHERE name = ?", $ts, $name);
			// insert a row if not available.
			if ($result->count() == 0)
			{
				try
				{
					$start = $base + $offset;
					$this->_db->query("INSERT INTO {$table_name} VALUES (?, $start, ?)", array($name, $ts));
				}
				catch (Kohana_Database_Exception $ex)
				{
					$result = $this->_db->query("UPDATE {$table_name} SET id = id + $offset, timestamp = ? WHERE name = ?", $ts, $name);
					if ($result->count() == 0)
						throw $ex;
				}
			}
			$result = $this->_db->query("SELECT id FROM {$table_name} WHERE name = ?", $name);
			$this->_db->trans_commit();
		}
		catch (Kohana_Database_Exception $ex)
		{
			$this->_db->trans_rollback();
			throw $ex;
		}

		$id = $result->current()->id - ($skip + 1) * ($num - 1);

		$this->_load_values(array("name"=>$name, "id"=>$id, "timestamp"=>$ts));
		return $this;
	}

	/**
	 * Deployment scripts
	 */
	public static function deploy() {
		$db = & Database::instance();
		try {
			$result = $db->query("
CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Sequences` (
  `name` varchar(100) NOT NULL,
  `id` BIGINT NOT NULL,
  `timestamp` INTEGER NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
			");
			echo __CLASS__." deployed.<br/>";
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br/>";
		}
	}
}
?>