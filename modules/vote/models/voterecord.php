<?php
class Voterecord_Model extends ORM_Cached
{
	private $_obj;
	protected $_primary_key = 'id';
	protected $_table_name = 'vote_record';
	
	protected $_created_column = array ("column" => 'created');

	public static function deploy() {

		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}vote_record` (
					  `id` int(11) NOT NULL,
					  `user` varchar(64) NOT NULL,
					  `votekey` varchar(20) NOT NULL,
					  `col` varchar(50) NOT NULL,
					  `option` varchar(100) NOT NULL,
					  `text` text NOT NULL,
					  `created` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `vote_key_col_option` (`votekey`,`col`,`option`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='投票记录表';
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