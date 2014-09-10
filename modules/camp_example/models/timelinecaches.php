<?php
defined ( 'SYSPATH' ) or die ( 'No direct access allowed.' );
/**
 * Timelinecaches model
 *
 *
 * @package    Timelinecaches model
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2009-2011 UUTUU
 */
class Timelinecaches_Model extends ORM_Cached {
	const PROVIDER_SINA = 'sina';
	
	const MEDIA_TYPE_VIDEO = 'video';
	const MEDIA_TYPE_PHOTO = 'photo';
	const MEDIA_TYPE_HTML = 'html';
	
	const DB_CONFIG_SET = "camp_example";

	protected $_db = self::DB_CONFIG_SET;

	protected $_primary_key = 'tcid';
	protected $_table_name = 'MAGTimelineCaches';

	protected $_updated_column = array ("column" => "updated" );
	protected $_created_column = array ("column" => "created" );
	
	public $rtInfo = array();
	public $mediaInfo = array();
	protected static $_meta_map = array(
		"id" => "id",
		"u" => "identity",
		"n" => "username"
	);
	protected static $_link_map = array(
		"u" => "url",
		"p" => "pic",
		"v" => "video"
	);

	public function __construct($id = NULL) {
		parent::__construct ( $id );
	}

	public static function from_status($provider, $status){
		switch($provider) {
			case self::PROVIDER_SINA:
				return self::from_sina_status($status);
			default:
				throw new Kohana_Exception("core.invalid_parameter", "provider", __CLASS__, __FUNCTION__);
		}
	}
	
	public static function from_sina_status($status) {
		$obj = new Timelinecaches_Model();
		$obj->find(array("srcAgent"=>self::PROVIDER_SINA, "srcId"=>$status['id']));
		if ($obj->loaded()) {
			return $obj;
		}
		
		$obj->srcAgent 	= self::PROVIDER_SINA;
		$obj->srcId 		= (string)$status['id'];
		$obj->identity 	= (string)$status['user']['id'];
		$obj->username 	= $status['user']['name'];
		$obj->text		= $status['text'];
		$obj->published = strtotime($status['created_at']);
		
		if (!empty($status['bmiddle_pic'])) {
			$obj->mediaType = self::MEDIA_TYPE_PHOTO;
			$obj->mediaLink = $status['bmiddle_pic'];
		}
		
		if (isset($status['retweeted_status'])) {
			$rtobj = self::from_sina_status($status['retweeted_status']);
			if (!$rtobj->saved()) {
				$rtobj->save();
			}
			else {
				$obj->mediaType = $rtobj->mediaType;
				$obj->mediaLink = $rtobj->mediaLink;
				$obj->mediaWidth = $rtobj->mediaWidth;
				$obj->mediaHeight = $rtobj->mediaHeight;
				$obj->unpack($obj->mediaLink, $obj->mediaInfo, self::$_link_map);
			}
			
			$obj->rtTcid = $rtobj->tcid;
			$obj->rtText = $rtobj->text;
			
			$obj->rtInfo = array (
				"id" => $rtobj->srcId,
				"identity" => $rtobj->identity,
				"username" => $rtobj->username);
			$obj->rtMeta = $obj->pack($obj->rtInfo, self::$_meta_map);
		}
		
		return $obj;
	}
	
	public function find($condition = NULL, $field = NULL) {
		parent::find($condition, $field);
		if (isset($this->rtMeta)) {
			$this->unpack($this->rtMeta, $this->rtInfo, self::$_meta_map);
		}
		if (isset($this->mediaLink)) {
			$this->unpack($this->mediaLink, $this->mediaInfo, self::$_link_map);
		}
		return $this;
	}
	
	public function save() {
		if ($this->empty_pk()) {
			$this->tcid = ID_Factory::next_id($this);
		}
		$this->mediaLink = $this->pack($this->mediaInfo, self::$_link_map);
		return parent::save();
	}
	
	protected function pack($info, $map = null) {
		if (empty($map))
			return null;
		
		$buffer = array();
		foreach ($map as $key => $val) {
			if (isset($info[$val])) {
				$buffer[] = (is_int($key) ? $val : $key) .':"'.addslashes($info[$val]).'"';
			}
		}
		if (count($buffer) == 0) {
			return null;
		}
		return '{'.implode(',', $buffer)."}";
	}
	
	protected function unpack($str, &$info, $map = null) {
		if (empty($str) || empty($map))
			return;
			
		preg_match_all('/([_a-zA-Z0-9]+):("?)(.*?)\2(?=,|}|\])/', $str, $matches);
		$buffer = array();
		for ($idx = 0; $idx < count($matches[0]); $idx++) {
			$buffer[$matches[1][$idx]] = $matches[3][$idx];
		}
		foreach ($map as $key => $val) {
			$key = is_int($key) ? $val : $key;
			if (isset($buffer[$key])) {
				$info[$val] = stripslashes($buffer[$key]);
			}
			else {
				unset($info[$val]);
			}
		}
	}

	/**
	 * install scripts
	 */
	public static function deploy() {
		$db = & Database::instance(self::DB_CONFIG_SET);
		try {
			$result = $db->query("
CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}MAGTimelineCaches`
(
	tcid INTEGER NOT NULL,
	srcAgent VARCHAR(10) NOT NULL,
	srcId VARCHAR(32) NOT NULL,
	identity VARCHAR(32) NOT NULL,
	uid INTEGER NOT NULL DEFAULT 0,
	username VARCHAR(30) NOT NULL,
	text VARCHAR(255) NOT NULL,
	rtTcid VARCHAR(32),
	rtText VARCHAR(255),
	rtMeta VARCHAR(255),
	mediaType VARCHAR(10),
	mediaLink TEXT,
	mediaWidth INTEGER,
	mediaHeight INTEGER,
	retweeted INTEGER NOT NULL DEFAULT 0,
	replied INTEGER NOT NULL DEFAULT 0,
	published INTEGER NOT NULL,
	created INTEGER NOT NULL,
	updated INTEGER NOT NULL,
	PRIMARY KEY (tcid),
	UNIQUE (srcAgent, srcid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

			echo  __CLASS__.' deployed.<br />';
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}

}
?>