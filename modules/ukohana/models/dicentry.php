<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * A simple class handle dictionary table.
 *
 * $Id: dicentry.php 626 2011-11-21 10:43:35Z zhangjyr $
 *
 * @package    Dictionary
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Dicentry_Model extends ORM_Cached {
	protected $_table_name = "Dictionary";
	protected $_primary_key = 'dic_id';
	protected $_primary_val = 'val';

	protected $_sorting = array();

	// TODO: implements a program to auto generate this.
	protected $_table_columns = array(
		"dic_id" => array(
    		"type" => "string",
			"length" => "100"
		),
		"category" => array(
			"type" => "string",
			"length" => "100"
  		),
		"val" => array(
			"type" => "string"
		),
		"auto" => array(
   			"type" => "int",
			"max" => 127,
   			"unsigned" => false
   		),
		"timestamp" => array(
    		"type" => "int",
    		"max" => 2147483647,
    		"unsigned" => false
    	)
    );

	public function __construct($id = NULL)	{
		parent::__construct($id);
	}

	/**
	 * Set category applied on fetching value by key.
	 */
	public function select_category($category, $by_prefix = false)
	{
		$this->__set("category", $category);
		if ($by_prefix) {
			return $this->like($this->table_name.'.category', $category."%", false);
		}
		else {
			return $this->where($this->table_name.'.category', $category);
		}
	}

	public function safe_update($val, $lock = null) {
		assertion::is_true($this->loaded(), "Dicentry must be loaded first.");
		assertion::is_true($lock == null || is_numeric($lock), "Given variable lock not a number.");

		if ($lock == null) {
			$lock = $this->timestamp;
		}

		$data["val"] = $val;
		$data["timestamp"] = ORM::get_time();
		$query = $this->_db->set($data)
			->where($this->_primary_key, $this->pk())
			->where("timestamp", $lock) // lock constraints
			->update($this->_table_name);

		if (count($query) > 0) {
			// success, update object data, and clear cache
			$this->_object["val"] = $data["val"];
			$this->_object["timestamp"] = $data["timestamp"];
			if ($this->_cached) {
				$this->get_cache()->delete($this->get_guid());
				$this->_cached = false;
			}
			return true;
		}
		return false;
	}

	public function find($condition = NULL, $field = NULL, $forupdate = false) {
		parent::find($condition, $field, $forupdate);
		if (!$forupdate && $this->_cachable && !$this->loaded() && !$this->cached()) {
			$key = NULL;
			if (!$this->empty_pk()) {
				$key = $this->pk();
			}
			else if ($condition != NULL && $field == NULL) {
				$key = $condition;
			}
			else {
				return $this;
			}
			$primary_guid = $this->get_guid($key);
			// save empty value whatsoever
			$this->get_cache()->set($primary_guid, $this->export_values());
			$this->_cached = true;
		}
		return $this;
	}

	/**
	 * Deployment scripts
	 */
	public static function deploy() {
		// TODO: SUPPORT Single Key md5(category+key)

		$db = & Database::instance();
		try {
			$result = $db->query("
CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Dictionary` (
  `dic_id` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `val` text NOT NULL,
  `auto` tinyint(4) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY  (`dic_id`),
  INDEX `main` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
			");
			echo __CLASS__." deployed.<br/>";
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br/>";
		}
	}
} // End Dicentry Model
?>