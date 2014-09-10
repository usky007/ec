<?php
/*
 * Created on 2010-6-30
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class Actionlog_Model extends ORM  {
 	protected $_primary_key = 'logid';
	protected $_table_name = "Logs";
	const DONE_STATUS = 1;
	const PENDING_STATUS = 0;
	public function __construct($id = NULL) {
		parent::__construct ( $id );
	}

	static public function finish_log($logid)
	{
		$al = new Actionlog_Model($logid);
		$al->logid = $logid;
		$al->upd_status = 1 ;
		$al->save();
	}

	function insert_action_logs_cache($data)
	{
		if (empty($data)) {
			return false;
		}
		//$data["id"] = db_next_id("Logs");
		//print_r($data);
		//exit;

		$logid = ID_Factory::next_id($this);
		$data[$this->_primary_key]=$logid;
		$db = new Database();

		$query = $db->insert("Logs", $data);

		return $logid;
	}

	/*
	*	读取action logs记录
	*/
	function get_action_logs_cache($id = 0, $limit = 1, $action = "")
	{
		/*$db = new Database();
		$db->select("*");
		$db->from("Logs");
		// status filter
		$db->where("upd_status <>", self::DONE_STATUS);


		if(!empty($id))
		{
			if ($limit == 1)
				$db->where("logid", $id);
			else
			{
				$db->where("logid >= {$id}");
				$db->orderby("logid", "asc");
				$db->limit($limit);
			}
		}
		else
		{
//			$time = time() - config_item("async_interval");
//			$this->db->where("vtime >= {$time}");
			if (!empty($action))
				$db->where("action", $action);
			$db->orderby("logid", "asc");
			$db->limit($limit);
		}
		$query = $db->get();

		return $query->result();


		**/
		$actionlogs = new Actionlog_Model();
		$actionlogs->where('upd_status',self::PENDING_STATUS);
		if(!empty($id))
		{
			$actionlogs->where('logid',$id);
		}
		else
		{
//			$time = time() - config_item("async_interval");
//			$this->db->where("vtime >= {$time}");
			if (!empty($action))
			{
				$actionlogs->where('action',$action);

			}
			$actionlogs->orderby(array('logid'=>'asc'));
		}
		$rows=$actionlogs->find_all($limit);
		//var_dump($rows);exit;
		return $rows;
	}


	public static function deploy() {
		// TODO: SUPPORT Single Key md5(category+key)

		$db = & Database::instance();
		try {
			$result = $db->query(
			"CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Logs`
			(
				logid BIGINT NOT NULL,
				vid BIGINT NOT NULL,
				action SMALLINT NOT NULL,
				oid INTEGER NOT NULL,
				type VARCHAR(10) NOT NULL,
				ouid BIGINT NOT NULL,
				roid INTEGER NOT NULL,
				rotype VARCHAR(10) NOT NULL,
				rouid BIGINT NOT NULL,
				url VARCHAR(255) NOT NULL,
				ip VARCHAR(32) NOT NULL,
				lid INTEGER,
				created INTEGER NOT NULL,
				remark VARCHAR(255),
				`upd_status` smallint(6) NOT NULL default '0',
				PRIMARY KEY (logid)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 "


			);

			echo __CLASS__." deployed.<br>";
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br>";
		}



	}
 }
?>