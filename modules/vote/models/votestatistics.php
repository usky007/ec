<?php
class Votestatistics_Model extends ORM_Cached
{
	private $_obj;
	protected $_primary_key = 'id';
	protected $_table_name = 'vote_statistics';

	public static function deploy() {

		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}vote_statistics` (
					  `id` int(11) NOT NULL,
					  `votekey` varchar(20) NOT NULL,
					  `col` varchar(50) NOT NULL,
					  `option` varchar(100) NOT NULL,
					  `text` text NOT NULL,
					  `count` int(11) NOT NULL,
					  `created` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `vote_key` (`votekey`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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