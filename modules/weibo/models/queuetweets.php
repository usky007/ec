<?php
class QueueTweets_Model extends ORM
{
	
 	protected $_primary_key = 'queueid';
	protected $_table_name = 'QueueTweets';
	protected $_table_names_plural = FALSE;
	protected $_created_column = array ("column" => 'created');
 	protected $_updated_column = array ("column" => 'updated');

 	

	public static function deploy() {

		$db = & Database::instance();
		try {
			$result = $db->query("
				CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}QueueTweets` (
				  `queueid` int(11) NOT NULL COMMENT 'queueid',
				  `tweet_id` BIGINT(20) NOT NULL COMMENT  '微博id',		
				  `name` varchar(150) NOT NULL COMMENT '活动代号',		  		 
				  `created` int(11) NOT NULL,
				  `updated` int(11) NOT NULL,				  
				  PRIMARY KEY (`queueid`),
				  UNIQUE KEY `tweet` (`tweet_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			");
			echo  __CLASS__.' deployed.<br />';
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
			return;
		}

        return 1;		
	}

	
}