<?php
class Camp_Jaguar_TweetComments_Model extends ORM_Cached
{
	protected $_primary_key = 'tweetComments_id';
	protected $_table_name = 'Camp_Jaguar_TweetComments';
	protected $_table_names_plural = FALSE;
	protected $_created_column = array ("column" => 'created');
	protected $_belongs_to = array (
		"tweet_id" => array("model" => "camp_Jaguar_tweet", "foreign_key" => 'tweet_id')
	);

	public static function deploy() 
	{
		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Camp_Jaguar_TweetComments` (
					 `tweetComments_id` BIGINT(20) NOT NULL COMMENT  '微博评论id',
					 `tweet_id` BIGINT(20) NOT NULL COMMENT  '微博id',
					 `content` TEXT NOT NULL COMMENT  '评论内容',
					 `name` VARCHAR( 200 ) NOT NULL COMMENT  '评论者姓名',
					 `avatar` VARCHAR( 200 ) NOT NULL COMMENT  '评论者头像',
					 `link` VARCHAR( 200 ) NOT NULL COMMENT  '评论者链接',
					 `created` INT NOT NULL ,
					 PRIMARY KEY (  `tweetComments_id` ),
					 KEY `tweet_id` (`tweet_id`)
					) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT =  '微博评论表';
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