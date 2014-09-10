
<?php
/**
 * Class description.
 *
 * $Id: message.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */

class Message_Model extends Model  {

	public $logid;
	public $vid;
	public $action;
	public $oid;
	public $type;
	public $ouid;
	public $roid;
	public $rotype;
	public $rouid;
	public $url;
	public $ip;
	public $lid;
	public $created;
	public $remark;
	public $status;
	public $upd_status;
	public function Message_Model()
	{

	}

	static public function Create_Message_By_Log($log)
	{
		$msg = new Message_Model();
		$colunms = array('logid','vid','action','oid','type','ouid','roid','rotype','rouid',
		'url','ip','lid','created','remark','status','upd_status');
		foreach($colunms as $cl)
		{
			$msg->$cl = $log->$cl;
		}
		return $msg;
	}

	static public function Create_Message($messageBody)
	{
		return null;
	}


}


?>