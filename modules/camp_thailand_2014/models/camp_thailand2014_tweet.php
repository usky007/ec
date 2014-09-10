<?php
class Camp_Thailand2014_Tweet_Model extends ORM_Cached
{
	protected $_primary_key = 'tweet_id';
	protected $_table_name = 'Camp_Thailand2014_Tweet';
	protected $_table_names_plural = FALSE;
	protected $_created_column = array ("column" => 'created');
	protected $_updated_column = array ("column" => 'updated');
	
	public static function deploy() 
	{
		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Camp_Thailand2014_Tweet` (
					`tweet_id` BIGINT(20) NOT NULL COMMENT  '微博id',
					`pic` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '微博图片',
					`content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '微博内容',
					`uid` BIGINT NOT NULL COMMENT  '微博作者新浪id',
					`name` VARCHAR( 200 ) NOT NULL COMMENT  '作者名字',
					`avatar` VARCHAR( 200 ) NOT NULL COMMENT  '作者头像',
					`link` VARCHAR( 200 ) NOT NULL COMMENT  '作者链接',
					`heat` INT( 11 ) NOT NULL COMMENT  '热度',
					`source` VARCHAR( 50 ) NOT NULL COMMENT  '来源',
					`created` INT(11) NOT NULL COMMENT  '抓取时间',
					`updated` INT NOT NULL COMMENT  '最后更新时间',
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
			$preference = Preference::instance('application');
			$preference->set('thailand2014-keywords', json_encode(array('#泰国风情#')));
			echo  __CLASS__.' deployed. keywords has been added.<br />';
			return 2;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}	
	}
}