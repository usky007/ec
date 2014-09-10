<?php
class Camp_Br_UserInfos_Model extends ORM_Cached
{
	protected $_primary_key = 'uid';
	protected $_table_name = 'Camp_Br_UserInfos';
	protected $_table_names_plural = FALSE;
	protected $_created_column = array ("column" => 'created');
	
	public static function deploy() 
	{
		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Camp_Br_UserInfos` (
					 `uid` INT(11) NOT NULL ,
					 `name` VARCHAR( 200 ) NOT NULL COMMENT  '真实姓名',
					 `email` VARCHAR( 100 ) NOT NULL ,
					 `weibo` VARCHAR( 200 ) NOT NULL COMMENT  '微博链接',
					 `mobile` CHAR( 20 ) NOT NULL ,
					 `created` INT NOT NULL ,
					PRIMARY KEY (  `uid` )
					) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT =  '用户信息表';
					");
					echo  __CLASS__.' deployed.<br />';
					return 1;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}		
	}
}