<?php
class Vote_Model extends ORM_Cached
{
	private $_obj;
	protected $_primary_key = 'id';
	protected $_table_name = 'vote';

	const PREFERENCE_KEY = 'votecfg';
	const PREFERENCE_PREFIX = 'votekey_';

	const SETTING_TIME = 'time';
	const SETTING_TIME_START = 'starttime';
	const SETTING_TIME_END = 'endtime';
	const SETTING_UNIQUE = 'unique';
	const SETTING_ACCUMULATION = 'accumulation';
	const SETTING_UPDATE = 'update';
	const SETTING_SINGLE_VOTE_TIME_LIMIT = 'single_vote_time_limit';
	const SETTING_LIMIT_REST_PERIOD = 'limit_rest_period';
	const SETTING_COL_SETTING = 'col_setting';
	
	public static function deploy() {
	
		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}vote` (
					  `id` int(11) NOT NULL,
					  `voteKey` varchar(20) NOT NULL,
					  `name` varchar(50) NOT NULL,
					  `starttime` int(11) NOT NULL,
					  `endtime` int(11) NOT NULL,
					  `setting` text NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `key` (`voteKey`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='投票列表';
			");
			echo  __CLASS__.' deployed.<br />';
			return 1;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}

	public static function sgetSetting($voteKey, $columns = array())
	{
		$pfc = Preference::instance(self::PREFERENCE_KEY);

		$setting = $pfc->get(self::PREFERENCE_PREFIX.$voteKey);

		if($setting == null)
		{
			$vm = new Vote_Model();
			$vm->where('voteKey', $voteKey)->find();
			if($vm->loaded)
			{
				$setting = $vm->getSetting();
				$rst[self::SETTING_TIME] = $setting[self::SETTING_TIME];
				foreach ($columns as $col) {
					if($col === '')
						continue;
					if(isset($setting[self::SETTING_COL_SETTING]->$col))
						$rst[self::SETTING_COL_SETTING][$col] = $setting[self::SETTING_COL_SETTING]->$col;
				}
				return $rst;
			}
			else
				throw new U_Exception('该投票不存在');
		}

		return json_decode($setting);
	}

	public function getSetting()
	{
		$setting = array();
		$setting[self::SETTING_TIME][self::SETTING_TIME_START] = $this->starttime;
		$setting[self::SETTING_TIME][self::SETTING_TIME_END] = $this->endtime;
		if($this->setting !== '')
		{
			$s = (array)json_decode($this->setting);
			if(is_array($s))
			{
				$setting = array_merge($setting, $s);
			}
			else
			{
				throw new U_Exception('投票设置有误');
			}
		}

		return $setting;
	}
}