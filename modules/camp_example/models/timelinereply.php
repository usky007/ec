<?php
defined ( 'SYSPATH' ) or die ( 'No direct access allowed.' );
/**
 * TimelineReply model
 *
 *
 * @package    TimelineReply model
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2009-2010 UUTUU
 */
class Timelinereply_Model extends ORM {

	const DB_CONFIG_SET = "camp_example";

	protected $_db = self::DB_CONFIG_SET;

	protected $_primary_key = 'trid';
	protected $_table_name = 'MAGTimelineReply';

	protected $_created_column = array ("column" => "created" );

	public function __construct($id = NULL) {
		parent::__construct ( $id );
	}

	public function insert($timeline,$arr){
		$this->trid 		= ID_Factory::next_id ( $this );
		$this->tcid 		= $timeline->tcid;
		$this->srcAgent 	= $timeline->srcAgent;
		$this->srcid 		= $timeline->srcid;
		$this->username 	= $timeline->username;
		$this->text 		= $timeline->text;
		$this->mediaType 	= $timeline->mediaType; //refer link type
		$this->mediaLink 	= $timeline->mediaLink;
		$this->published 	= $this->published; //third party timeline created
		$this->save();
	}

	public function get_revisions($template){
		return $this->with("definition")
					->where($this->_table_name.'.template',$template)
					->where($this->_table_name.'.status',0)
					->orderby ( $this->_table_name.'.priority','ASC' );
	}

	/**
	 * install scripts
	 */
	public static function deploy() {
		$db = & Database::instance(self::DB_CONFIG_SET);
		try {
			$result = $db->query("
CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}MAGTimelineReply`
(
	trid INTEGER NOT NULL,
	tcid INTEGER NOT NULL,
	srcAgent VARCHAR(10) NOT NULL,
	srcid VARCHAR(32) NOT NULL,
	username VARCHAR(30) NOT NULL,
	text VARCHAR(255) NOT NULL,
	mediaType VARCHAR(10),
	mediaLink VARCHAR(255),
	published INTEGER NOT NULL,
	created INTEGER NOT NULL,
	PRIMARY KEY (trid)
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