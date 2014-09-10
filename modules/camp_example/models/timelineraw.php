<?php
defined ( 'SYSPATH' ) or die ( 'No direct access allowed.' );
/**
 * TimelineRaw model
 *
 *
 * @package    TimelineRaw model
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2009-2011 UUTUU
 */
class Timelineraw_Model extends ORM {
	
	const DB_CONFIG_SET = "camp_example";

	protected $_db = self::DB_CONFIG_SET;

	protected $_primary_key = 'uuid';
	protected $_table_name = 'MAGTimelineRaw';

	public function __construct($id = NULL) {
		parent::__construct ( $id );
	}

	/**
	 * install scripts
	 */
	public static function deploy() {
		$db = & Database::instance(self::DB_CONFIG_SET);
		try {
			$result = $db->query("
CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}MAGTimelineRaw`
(
	uuid VARCHAR(32) NOT NULL,
	raw TEXT NOT NULL,
	PRIMARY KEY (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
			echo  __CLASS__.' deployed.<br />';
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}

}
?>