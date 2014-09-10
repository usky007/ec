<?php
/*
 * Created on 2010-7-6
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class Backservice {
	public $valid_type_set = array(
		'user', 'Location', 'Contribution', 'CommentID', 'Tools',
		'Post' );

	private $model;
	private $config;
	public function Backservice()
	{
		//$this->actionlog = new actionlog();
		$this->model = new actionlog_Model();
		$this->config = Kohana::config('log');
	}


	public function log_process($id=0)
	{
		set_time_limit(600);

		$logs = $this->model->get_action_logs_cache($id);


		foreach ($logs as $log) {
			// Call the logservice module to process the log

			//$msg = Message_Model::Create_Message_By_Log($log);


			$processed = $this->process($log);

			if ( !$processed ) {
				break;
			}
		}

	}



	public function process($log, &$err_msg = "") {

		Kohana::log("debug","process log [".$log->logid."]");
		$action = $log->action;
		//var_dump(json_decode($log->remark));
 		//$sataconfig = $this->config["ActionProcessCheck"][$action]["statistic"]; //处理统计

		$sataconfig = config::item("log.ActionProcessCheck.{$action}.statistic", false, null); //处理统计
		$achvconfig = config::item("log.ActionProcessCheck.{$action}.achievement", false, null); //处理成就
		$reconfig = config::item("log.ActionProcessCheck.{$action}.randomevent", false, null); //随机事件

		Kohana::log("debug","process log stat ");
		$this->_process_config($log,$sataconfig);//echo "finish stat<br>";
		Kohana::log("debug","process log achv ");
		$this->_process_config($log,$achvconfig);//echo "finish achv<br>";
		Kohana::log("debug","process log randomevent ");
		$this->_process_randomevent_config($log,$reconfig);//echo "finish RE<br>";
		Kohana::log("debug","process log feed ");
		$fd = new Feed();
		$rst = $fd->checkFeed($log);//echo "finish RE<br>";

		$actionlog=new Actionlog_Model($log->logid);

		if($actionlog->find()->loaded())
		{
			$actionlog->upd_status = Actionlog_Model::DONE_STATUS ;
			$actionlog->save();
			Kohana::log("debug","process log ok".$log->logid);

		}
	}


	/*
	 * function : 通用根据config来处理 统计,成就,等
	 * param:
	 * $log : 触发的动作
	 * $cnfg_funcs : 对应的config
	 */
	private function _process_config($log,$cnfg_funcs)
	{
		if(is_null($cnfg_funcs))
			return true;
		foreach ( $cnfg_funcs as $Aryfunc )
		{

			$funname = $Aryfunc[0];
			$params = $Aryfunc[1];

			//array(array('vid'),'buy',array('remark'=>'type'),array('remark'=>'price'))
			$param =array();
			foreach($params as $p)
			{
				if(is_array($p))//字段
				{

					if(key($p)===0)
					{
						array_push($param,$log->$p[0]);
					}
					else
					{

						$c_name = key($p);
						$c_value = json_decode($log->$c_name);
						if(!array_key_exists($p[$c_name], $c_value))
							return false;

						array_push($param,$c_value->$p[$c_name]);

					}
				}
				else
				{
					array_push($param,$p);
				}

			}
			//Userstat_landelord::setBuySell(72,'bug','美食',1722);
			try
			{
				call_user_func_array ( $funname, $param );
			}
			catch(Exception $ex)
			{
				Kohana::log('error',"do Async stat or achievment error function: ".json_encode($funname).";param:".json_encode($param));
			}


		}
	}

	private function _process_randomevent_config($log,$reconfig)
	{
		if(!is_null($reconfig))
		{

			try
			{
				$randomeventgroup = $reconfig;
				$re = new Randomevent();
				$re->checkRandomEvent($log,$randomeventgroup);
			}
			catch(Exception $ex)
			{
				Kohana::log('error',"do Async randomevent function error ");
			}
		}
	}



//////////////////



}
?>