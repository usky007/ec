<?php
/*
 * Created on 2010-6-30
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class Actionlog  {

	//const MSG_SNS_TIPS 			= 6402;					//发布生活信息




 	public $config ;
 	public $model ;
	function __construct() {
		$this->config = Kohana::config('actionlog');
		$this->model = new actionlog_Model();
	}


	public function createObject($oid,$type,$userid )
	{
		return array(
			'oid' => $oid,
			'type' => $type,
			'userid' => $userid,
		);
	}

	/**
	* function : record and send action.
	* param:
	* $uid : action userid
	* $lid : action related location id
	* $action : action id. action id is a const in class Actionlog
	* $targetobject: action target object .It is an array that contains three parameters 'oid';'type';'userid';
	* $relatedObject: action related object like targetobject
	* $remark :other Necessary infomation .
	*/
	public function log_action($uid,$lid, $action, $targetObject=null, $relatedObject=null, $ignore_flood = false, $remark = '', $expands=array()) {

		$emptyobj = $this->createObject(0,'',0);
		$targetObject = is_null($targetObject)?$emptyobj:$targetObject;
		$relatedObject = is_null($relatedObject)?$emptyobj:$relatedObject;

		Kohana::log('debug', "new action log [$action]");
		try {
			$aid = $action;
			$remark = is_array($remark)?json_encode($remark):$remark;
			$formated_data = $this->_format_log_data($uid,$lid, $action, $targetObject, $relatedObject, $remark);
			$data = $formated_data["data"];
			$info = $formated_data["info"];

			// Get the publicflag
			//$data['publicflag'] = $this->_get_publicflag($uid, $data["ouid"], $action, $type, $expands);

			// Send broadcase message
			//$this->_log_uh($info, $data);

			$log_enable = config::item("async.mq_enable.async_log",false,false);
			if ($log_enable) {
				$log_percent = config::item("async.async_limit.log",false,0);
				$rand = rand(1, 100);
				if ($rand <= $log_percent) {
					return $this->_log_async($data, $info);
				} else {
					return $this->_log_sync($data, $info, $expands);
				}
			} else {
				return $this->_log_sync($data, $info, $expands);
			}

		} catch (UKohana_Exception $ex) {
			Kohana::log('error', $ex->getMessage());
		}

	}

	public function log_action_model($log_model,$expands=array()) {

		try {
			$formated_data = $this->_format_log_model($log_model);
			$data = $formated_data["data"];
			$info = $formated_data["info"];

			$log_enable = config::item("async.mq_enable.async_log",false,false);
			if ($log_enable) {
				$log_percent = config::item("async.async_limit.log",false,0);
				$rand = rand(1, 100);
				if ($rand <= $log_percent) {

					return $this->_log_async($data, $info);
				} else {
					return $this->_log_sync($data, $info, $expands);
				}
			} else {
				return $this->_log_sync($data, $info, $expands);
			}

		} catch (UKohana_Exception $ex) {
			Kohana::log('error', $ex->getMessage());
		}

	}


//core///////////////////////////////////////////////////////////////////////////////////////
	function _log_sync($data, $info, $expands) {
		// Process the sync action
		//return $this->process_sync_actions($data, $info, $expands);
		$alm = new actionlog_Model();
		$logid = $alm->insert_action_logs_cache($data);
		$bs = new Backservice();
		$bs->log_process($logid);
		return $logid;

	}

	function _log_async($data, $info) {

		$id = $this->model->insert_action_logs_cache($data);

		$recall_url = $this->config["async_recall_host"] . $this->config["async_recall_dir"] . $id ;

 		$async = new AsyncService();
		$async->send_log($recall_url);
		return $id;
	}
	function _format_log_data($uid=null,$lid, $action, $targetObject, $relatedObject, $remark = '')
	{


		$aid = $action ;////$this->config['valid_action'][$action];


		//$vurl = $this->input->server('REQUEST_URI');
		$vurl = isset($_REQUEST['url'])?$_REQUEST['url']:'' ;
		$vip = $_SERVER['REMOTE_ADDR'];

		if (!isset($targetObject) or empty($targetObject))
		{
			$targetObject = $this->createObject(0,'',0);
		}
		if (!isset($relatedObject) or empty($relatedObject))
		{
			$relatedObject = $this->createObject(0,'',0);
		}

		$targetObject = $this->_get_obj_attr($targetObject);
		$relatedObject = $this->_get_obj_attr($relatedObject);

		// get object's attributes and process them
		//$obj_attr = $this->_get_obj_attr($oid, $type, $remark);

		//$ouid = $this->_fix_attr($obj_attr, 'ouid');
		//$ornid = $this->_fix_attr($obj_attr, 'ornid');
		//$oruid = $this->_fix_attr($obj_attr, 'oruid');
		//$ortype = $this->_fix_attr($obj_attr, 'ortype');
		//$nickname = $this->_fix_attr($obj_attr, 'nickname');
		//$content = $this->_fix_attr($obj_attr, 'content');
		//$node_link = $this->_fix_attr($obj_attr, 'node_link');
		//$status = $this->_fix_attr($obj_attr, 'status');



		$data = array(

					'vid' => $uid,
					'action' => $aid,
					'type' => $targetObject['type'],
					'oid' => $targetObject['oid'],
					'ouid' => $targetObject['userid'],
					'roid' => $relatedObject['oid'],
					'rotype' => $relatedObject['type'],
					'rouid' => $relatedObject['userid'],
		 			'lid' => $lid,
					'url' => $vurl,
					'remark' => $remark,
					'ip' => $vip,
					'created' => time()
					);


		$info = array(
						//'nickname'	=> $nickname,
						//'content'	=> $content,
						//'node_link'	=> $node_link,
						//'status'	=> $status,
						'uid'	=> $uid,
						'action'	=> $action
						);
		return array('data' => $data, 'info' => $info);

	}

	function _format_log_model($log)
	{
		if (!isset($uid) or empty($uid)) {
			$uid = null;
		}

		$aid = $log->action ;////$this->config['valid_action'][$action];
		$vurl = isset($_REQUEST['url'])?$_REQUEST['url']:'' ;
		$vip = $_SERVER['REMOTE_ADDR'];

		$data = array(
					'vid' => $log->vid,
					'action' => $log->action,
					'type' => $log->type,
					'oid' => $log->oid,
					'ouid' => $log->ouid,
					'roid' => $log->roid,
					'rotype' => $log->rotype,
					'rouid' => $log->rouid,
		 			'lid' => $log->lid,
					'url' => $vurl,
					'remark' => $log->remark,
					'ip' => $vip,
					'created' => time()
					);


		$info = array(
						//'nickname'	=> $nickname,
						//'content'	=> $content,
						//'node_link'	=> $node_link,
						//'status'	=> $status,
						'uid'	=>  $log->vid,
						'action'	=> $log->action
						);
		return array('data' => $data, 'info' => $info);

	}
//private///////////////////////////////////////////////////////////////////////////////////////////////

	///暂时无用
	function _check_data($uid, $action,$targetObject, $relatedObject) {
		if (empty($uid) or empty($action)) {
			Kohana::log('error', "param error: uid=" . $uid . ";action=" . $action);
			return false;
		}
		return true;
	}
	///暂时无用
	function _get_obj_attr($obj)
	{
		if ($obj['userid']==0) {
			//get object
			if($obj['oid']==0 or $obj['type']=='')
			{
				$method = '_get_obj_attr_'.$obj['type'];
				if (method_exists($this, $method)) {
					$obj['userid'] = call_user_func(array('actionlog', $method),$obj['oid']);
				}
			}
		}
		return $obj;
	}


 }
?>