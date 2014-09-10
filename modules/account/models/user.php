<?php
define("USER_FIELD_GENDER", "gender");
define("USER_FIELD_INTRODUCE", "introduce");
class User_Model extends ORM_Cached
{
	const GUEST = 0;

 	protected $_primary_key = 'uid';
	protected $_table_name = "Users";
	protected $_table_names_plural = true;

	protected $_created_column = array("column" => "created");

	protected $_userfield = array(USER_FIELD_GENDER,USER_FIELD_INTRODUCE);
	/**
	 * 插入用户信息
	 * @param $ary = array('email','password','nickname');
	 * */
	public static function new_user($array)
	{
		$user = new User_Model();
		$salt = self::getSalt(6);
		$user->uid = ID_Factory::next_id($user);
		$user->salt = $salt;
		$user->lastAccessed = $user->lastLoggedIn = ORM::get_time();
		$user->createIp = $user->lastIp = Input::instance()->ip_address();

		assertion::is_true(isset($array['password']), "password must set to create a user").
		$array['password'] = md5(strtolower($array['password']).'@'.$salt);
		foreach ($array as $key => $val)
		{
			$user->$key = $val;
		}
		return $user->save();
	}

	public static function new_guest()
	{
		return new User_Model(self::GUEST);
	}

	public function is_guest() {
		return isset($this->uid) && ($this->uid == self::GUEST);
	}

	public function to_api($brief = true)
	{
		$data = array();
		if (!$this->is_guest()) {
			$data['@uid'] = $this->uid;
		}
		$data['nickname'] = empty($this->nickname) ? Kohana::lang("general.anonymous") : $this->nickname;
		$data['avatar'] = empty($this->avatar) ? "" : $this->avatar;
		return $data;
	}

	protected static function getSalt($no)
	{
		if($no <= 13)
		{
			$long = 13 - $no;
			return substr(uniqid(),$long,$no);
		}return FALSE;
	}

	public function __get($val)
	{
		if(in_array($val, $this->_userfield))
		{
			$user = $this->_object;
			$userinfos = new Userinfo_Model();
			$userinfo = $userinfos->where('item',$val)->where('uid',$user['uid'])->find();
			return $userinfo->value;
		}
		else
		{
			return parent::__get($val);
		}
	}

	public function getNewMessageCount()
	{
		return 0;
		// if(!$this->loaded())
		// 	return 0;
		// if($this->uid == 0 )
		// 	return 0;
		// $message =new Message_Model();
		// $message->where(array('recipientId'=>$this->uid,
		// 					  'status'=>Message_Model::STATUS_NEW));
		// return $message->count_all();
	}

	public function getMessages()
	{
		return null;
		// if(!$this->loaded())
		// 	return null;
		// if($this->uid == 0 )
		// 	return null;

		// $message =new Message_Model();
		// $message->where(array('recipientId'=>$this->uid,
		// 					  'status'=>Message_Model::STATUS_NEW));
		// $message->orderby('created','desc')->limit(10);
		// return $message->find_all();

	}

	public static function deploy() {

		$db = & Database::instance();
		try {
			$result = $db->query("
				CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Users` (
				  `uid` int(11) NOT NULL,
				  `email` varchar(50) NOT NULL,
				  `password` char(32) NOT NULL,
				  `nickname` varchar(30) NOT NULL,
				  `avatar` varchar(100) NOT NULL,
				  `created` int(11) NOT NULL,
				  `updated` int(11) NOT NULL,
				  `lastAccessed` int(11) NOT NULL COMMENT '暂时无用',
				  `lastLoggedIn` int(11) NOT NULL COMMENT '最后登录时间',
				  `createIp` varchar(32) NOT NULL COMMENT '创建时ip',
				  `lastIp` varchar(32) NOT NULL COMMENT '最后登录ip',
				  `salt` char(6) NOT NULL COMMENT '密码辅助验证字段',
				  `status` tinyint(1) NOT NULL default '1' COMMENT '1:有效 0:删除',
				  PRIMARY KEY  (`uid`),
				  UNIQUE KEY `user_email` (`email`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			");
			echo  __CLASS__.' deployed.<br />';
			return 1;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}
	
	public static function deploy_1() {

		$db = & Database::instance();
		try {
			$result = $db->query("
				ALTER TABLE  `{$db->table_prefix()}Users` CHANGE  `avatar`  `avatar` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
			");
			echo  __CLASS__.' add field success.<br />';
			return 2;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}
	
	public static function deploy_2() {

		$db = & Database::instance();
		try {
			$result = $db->query("
				ALTER TABLE  `{$db->table_prefix()}Storages` CHANGE  `uri`  `uri` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
			");
			echo  __CLASS__.' add field success.<br />';
			return 3;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}
}