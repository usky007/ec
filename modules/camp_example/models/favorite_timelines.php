<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Mutation of dictionary table for cache tag persistence.
 *
 * $Id: favorite_timelines.php 53 2011-07-21 11:20:13Z zhangjyr $
 *
 * @package    Dictionary
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Favorite_Timelines_Model extends Dicentry_Model {

	const DB_CONFIG_SET = "camp_example";

	protected $_db = self::DB_CONFIG_SET;

	protected $_table_name = "MAGDictionaryFavTL";

	protected $_sorting = array('timestamp' => 'desc');

	public function __construct($id = NULL)
	{
		parent::__construct($id);
	}

	/**
	 * Deployment scripts
	 */
	public static function deploy() {
		$db = & Database::instance(self::DB_CONFIG_SET);
		try {
			$result = $db->query("
CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}MAGDictionaryFavTL` (
  `dic_id` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `val` int NOT NULL,
  `auto` tinyint(4) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY  (`dic_id`),
  INDEX IDX_MAGDictionaryFavTL_category (category ASC, val DESC)
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