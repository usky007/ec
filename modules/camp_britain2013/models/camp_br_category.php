<?php
class Camp_Br_Category_Model extends ORM_Cached
{
	protected $_primary_key = 'id';
	protected $_table_name = 'Camp_Br_Category';
	protected $_table_names_plural = FALSE;
	// protected $_created_column = array ("column" => 'created');
	// protected $_updated_column = array ("column" => 'updated');
	
	public static function deploy() 
	{
		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE IF NOT EXISTS  `{$db->table_prefix()}Camp_Br_Category` (
					  `id` int(11) NOT NULL COMMENT 'id',
					  `name` varchar(20) NOT NULL COMMENT '分类名称',
					  `key` varchar(20) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分类表';
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