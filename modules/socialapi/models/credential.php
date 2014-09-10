<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * A simple class handle dictionary table.
 *
 * $Id: credential.php 2529 2011-04-06 11:42:19Z zhangjyr $
 *
 * @package    Dictionary
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Credential_Model extends ORM {
	protected $_table_name = "UserCredentials";
	protected $_primary_key = 'credid';

	protected $_sorting = array('credid' => 'asc');
	
	protected $_belongs_to = array ("user" => array("foreign_key" => "uid", "model" => "User"));

	protected $_updated_column = array('column'=>"updated");
	protected $_created_column = array('column'=>"created");
	
	protected $_reload_on_wakeup = FALSE;

	public function find_user_credentials($user)
	{
		if (!isset($user->uid))
			return array();

		return $this->where("uid", $user->uid)->where("status", 0)->find_all();
	}

	/**
	 * Logically delete(set status to 1) the current object. This does NOT delete
	 * thie record in database;
	 *
	 * @chainable
	 * @return  ORM
	 */
	public function delete()
	{
		if ( ! $this->empty_pk()) {
			$this->status = 1;
			$this->save();
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
CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}UserCredentials` (
	credid INTEGER NOT NULL,
	uid INTEGER NOT NULL,
	provider VARCHAR(20) NOT NULL,
	identity VARCHAR(32) NOT NULL,
	token VARCHAR(32) NOT NULL,
	tokenTimeout INTEGER,
	secret VARCHAR(32),
	privateKey VARCHAR(32),
	status TINYINT NOT NULL DEFAULT 0,
	updated INTEGER NOT NULL,
	created INTEGER NOT NULL,
	PRIMARY KEY (credid),
	UNIQUE (provider, identity),
	INDEX IDX_UserCredentials_user (uid ASC, provider ASC)
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