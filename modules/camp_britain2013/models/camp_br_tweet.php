<?php
class Camp_Br_Tweet_Model extends ORM_Cached
{
	protected $_primary_key = 'tweet_id';
	protected $_table_name = 'Camp_Br_Tweet';
	protected $_table_names_plural = FALSE;
	protected $_created_column = array ("column" => 'created');
	protected $_updated_column = array ("column" => 'updated');
	
	public static function deploy() 
	{
		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Camp_Br_Tweet` (
					`tweet_id` BIGINT(20) NOT NULL COMMENT  '微博id',
					`pic` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '微博图片',
					`content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '微博内容',
					`uid` BIGINT NOT NULL COMMENT  '微博作者新浪id',
					`name` VARCHAR( 200 ) NOT NULL COMMENT  '作者名字',
					`avatar` VARCHAR( 200 ) NOT NULL COMMENT  '作者头像',
					`link` VARCHAR( 200 ) NOT NULL COMMENT  '作者链接',
					`created` INT(11) NOT NULL COMMENT  '抓取时间',
					PRIMARY KEY (  `tweet_id` )
					) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT =  '微博表';
					");
					echo  __CLASS__.' deployed.<br />';
					return 1;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}	
	}
	
	public static function deploy_1()
	{
		$db = & Database::instance();
		try {
			$result = $db->query("
					ALTER TABLE  `{$db->table_prefix()}Camp_Br_Tweet` 
					ADD  `comments_num` INT NOT NULL COMMENT  '评论数量' AFTER  `link` ,
					ADD  `city` VARCHAR( 50 ) NOT NULL COMMENT  '城市关键字' AFTER  `comments_num` ;
					");
					echo  __CLASS__.' updated.<br />';
					return 2;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}
	
	public static function deploy_2()
	{
		$db = & Database::instance();
		try {
			$result = $db->query("
					ALTER TABLE  `{$db->table_prefix()}Camp_Br_Tweet` CHANGE  `comments_num`  `heat` INT( 11 ) NOT NULL COMMENT  '热度'
					");
					echo  __CLASS__.' updated.<br />';
					return 3;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}

	public static function deploy_3()
	{
		$db = & Database::instance();
		try {
			$result = $db->query("
					ALTER TABLE  `{$db->table_prefix()}Camp_Br_Tweet` ADD  `updated` INT NOT NULL COMMENT  '最后更新时间';
					");
					echo  __CLASS__.' updated.<br />';
					return 4;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}

	public static function deploy_4()
	{
		//ALTER TABLE  `Camp_Ba_Tweet` CHANGE  `city`  `source` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '来源'
		$db = & Database::instance();
		try {
			$result = $db->query("
						ALTER TABLE  `{$db->table_prefix()}Camp_Br_Tweet` CHANGE  `city`  `source` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '来源'
					");
					echo  __CLASS__.' deployed.<br />';
					return 5;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}	
	}
}