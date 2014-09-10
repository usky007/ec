<?php
class Camp_Br_CateTweetRelation_Model extends ORM_Cached
{
	protected $_primary_key = 'id';
	protected $_table_name = 'Camp_Br_CateTweetRelation';
	protected $_table_names_plural = FALSE;
	// protected $_created_column = array ("column" => 'created');
	// protected $_updated_column = array ("column" => 'updated');
	
	public static function deploy() 
	{
		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE  IF NOT EXISTS  `{$db->table_prefix()}Camp_Br_CateTweetRelation` (
					  `id` int(11) NOT NULL COMMENT 'id',
					  `tweet_id` bigint(20) NOT NULL,
					  `cate_id` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `tweet_id` (`tweet_id`,`cate_id`)
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
}