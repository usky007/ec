<?php
class Voteoption_Model extends ORM_Cached
{
	private $_obj;
	protected $_primary_key = 'id';
	protected $_table_name = 'vote_option';

	public static function deploy() {

		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}vote_option` (
					  `id` int(11) NOT NULL,
					  `voteKey` varchar(20) NOT NULL,
					  `option_key` varchar(50) NOT NULL,
					  `option_value` text NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `vote_key` (`voteKey`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='投票项目表';
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