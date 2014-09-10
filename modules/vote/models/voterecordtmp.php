<?php
class Voterecordtmp_Model extends ORM_Cached
{
	private $_obj;
	protected $_primary_key = 'id';
	protected $_table_name = 'vote_record_tmp';

	protected $_created_column = array ("column" => 'created');

	public static function getSelected($votekey, $userkey, $columns)
	{
		$querycols = array();
		foreach ($columns as $col) {
			$querycols[] = "'".$col."'";
		}

		$votetmp = new Voterecordtmp_Model();
		$tmps = $votetmp->where(array('user' => $userkey, 'votekey' => $votekey))->in('col', implode(',', $querycols))->find_all();

		$rst = array();
		foreach ($tmps as $tmp) {
			$rst[$tmp->col][] = array('option' => $tmp->option, 'text' => $tmp->text);
		}

		return $rst;
	}

	public static function deploy() {

		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}vote_record_tmp` (
					  `id` int(11) NOT NULL,
					  `user` varchar(64) NOT NULL,
					  `votekey` varchar(20) NOT NULL,
					  `col` varchar(50) NOT NULL,
					  `option` varchar(100) NOT NULL,
					  `text` text NOT NULL,
					  `created` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `vote_user_key` (`user`,`votekey`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='投票临时记录表';
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