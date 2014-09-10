<?php
defined ( 'SYSPATH' ) or die ( 'No direct access allowed.' );
/**
 * TimelineIndex model
 *
 *
 * @package    TimelineIndex model
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2009-2010 UUTUU
 */
class Timelineindex_Model extends ORM {

	const DB_CONFIG_SET = "camp_example";

	protected $_db = self::DB_CONFIG_SET;

	protected $_primary_key = 'tsid';
	protected $_table_name = 'MAGTimelineIndex';

	public function __construct($id = NULL) {
		parent::__construct ( $id );
	}

	public function insert($timeline){
		$this->tsid 	= ID_Factory::next_id ( $this );
		$this->srcAgent = $timeline->srcAgent;
		$this->keyword 	= $timeline->keyword;
		$this->tcid 	= $timeline->tcid;
		$this->next 	= $timeline->next;
		$this->sequence = $timeline->sequence;  //use srcid if possible
		$this->save();
	}

	public function getIndexByKeyword($keyword){
		return $this;
	}

	/**
	 * install scripts
	 */
	public static function deploy() {
		$db = & Database::instance(self::DB_CONFIG_SET);
		try {
			$result = $db->query("
CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}MAGTimelineIndex`
(
	tsid INTEGER NOT NULL,
	srcAgent VARCHAR(10) NOT NULL,
	keyword VARCHAR(50) NOT NULL,
	tcid INTEGER NOT NULL,
	next INTEGER NOT NULL DEFAULT 0,
	sequence INTEGER NOT NULL,
	PRIMARY KEY (tsid)
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