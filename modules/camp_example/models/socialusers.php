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
class Socialusers_Model extends ORM_Cached {
	const PROVIDER_SINA = 'sina';
	
	const DB_CONFIG_SET = "camp_example";

	protected $_db = self::DB_CONFIG_SET;

	protected $_primary_key = 'suid';
	protected $_table_name = 'MAGSocialUsers';

	protected $_updated_column = array ("column" => "updated" );
	protected $_created_column = array ("column" => "created" );

	public function __construct($id = NULL) {
		parent::__construct ( $id );
	}
	
	public static function from_profile($provider, $profile){
		switch($provider) {
			case self::PROVIDER_SINA:
				return self::from_sina_profile($profile);
			default:
				throw new Kohana_Exception("core.invalid_parameter", "provider", __CLASS__, __FUNCTION__);
		}
	}
	
	public static function from_sina_profile($profile) {
		$obj = new Socialusers_Model();
		$obj->find(array("srcAgent"=>self::PROVIDER_SINA, "srcId"=>$profile['id']));
		if (!$obj->loaded()) {
			$obj->srcAgent 	= self::PROVIDER_SINA;
			$obj->srcId 	= (string)$profile['id'];
			$obj->username 	= $profile['name'];
			$obj->avatar 	= $profile['profile_image_url'];	
		}
		
		// update link if possible
		if (isset($profile['status']) && isset($profile['status']['bmiddle_pic'])) {
			$obj->recentMediaLink = $profile['status']['bmiddle_pic'];
			$obj->lastStatusId = (string)$profile['status']['id'];
		}
		
		return $obj;
	}

	public function save() {
		if ($this->empty_pk()) {
			$this->suid = ID_Factory::next_id($this);
		}
		return parent::save();
	}

	/**
	 * install scripts
	 */
	public static function deploy() {
		$db = & Database::instance(self::DB_CONFIG_SET);
		try {
			$result = $db->query("
CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}MAGSocialUsers`
(
	suid INTEGER NOT NULL,
	srcAgent VARCHAR(10) NOT NULL,
	srcId VARCHAR(32) NOT NULL,
	username VARCHAR(30) NOT NULL,
	uid INTEGER NOT NULL DEFAULT 0,
	avatar VARCHAR(255) NOT NULL,
	lastStatusId VARCHAR(32),
	recentMediaLink TEXT,
	created INTEGER NOT NULL,
	updated INTEGER NOT NULL,
	PRIMARY KEY (suid),
	UNIQUE (srcAgent, srcid)
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