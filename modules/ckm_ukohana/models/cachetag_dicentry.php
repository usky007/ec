<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Mutation of dictionary table for cache tag persistence.
 *
 * $Id: cachetag_dicentry.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Dictionary
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Cachetag_Dicentry_Model extends Dicentry_Model {

	protected $_table_name = "DictionaryCachetag";

	protected $_sorting = array();

	public function __construct($id = NULL)
	{
		parent::__construct($id);
	}

	/**
	 * Finds and loads a single database row into the object.
	 *
	 * @chainable
	 * @param   mixed  primary key or an array of clauses
	 * @return  ORM
	 */
	public function gc()
	{
		$ts = time();
		$this->_db->where("val <", $ts);
		return $this->delete_all();
	}

	/**
	 * Deployment scripts
	 */
	public static function deploy() {
		$db = & Database::instance();
		try {
			$result = $db->query("
CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}DictionaryCachetag` (
  `dic_id` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `val` int NOT NULL,
  `auto` tinyint(4) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY  (`dic_id`),
  INDEX `main` (`category`),
  INDEX `timeout` (`val`)
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