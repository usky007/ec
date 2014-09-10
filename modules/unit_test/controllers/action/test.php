<?php

/**
 * Class description.
 *
 * $Id: test.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */
class Test_Controller extends Controller {

 	public function index()
 	{
		$user = new User_Model();
		$user->set_friend_lastFeedUpdated(5,time());
 	}
	public function hm()
	{
		$aa = Home_Controller::_getusers();
		var_dump($aa);
	}
 	public function feed2()
	{
		$fd = new Feed();
		//for($i=47;$i<55;$i++)
		//{
			$this->model = new Actionlog_Model();

			$log = $this->model->get_action_logs_cache(52,1);
			$log = $log[0];
	 		$rst = $fd->checkFeed($log);
	 		var_dump($rst);

		//}

	}

	public function add_action($act)
	{
		call_user_func(array($this,"add_action_$act"));
	}
 	public function add_action_find()
 	{
 		$am = new Actionlog_Model();
 		$am->logid = ID_Factory::next_id($am);
 		$am->vid = 1;
 		$am->action =300;
 		$am->oid = 4;
 		$am->type = 'location';
 		$am->ouid = 3;
 		$am->lid = 4;
 		$am->created = 1280260800;
 		$am->remark = '{"uid":1,"lid":4,"price":"22123"}';
 		$am->upd_status = 0;
 		$am->save();
 	}
 	public function add_action_buy()
 	{
 		$actlog = new Actionlog();
 		$uid = 1;
 		$lid = 4;
 		//$action = $logcfg['valid_action']['Location.Buy'];
 		$action = Actionlog::LOCATION_BUY;
 		$targetobj = $actlog->createObject(4,'location',3); //lid,ltype.seller
 		$remark = array('lid'=>4,'cityid'=>0,
							'price'=>2333,
							'ltype'=>'酒店;经济型酒店',
							'buyRstCash'=>10000,'buyDiffAssets'=>20000,'sellRstCash'=>333,'tax'=>300);
 		$actlog->log_action($uid,$lid,$action,$targetobj,null,false,$remark);

		echo "finish";
 	}
	public function add_action_find2()
 	{
 		$actlog = new Actionlog();
 		$uid = 1;
 		$lid = 4;
 		//$action = $logcfg['valid_action']['Location.Buy'];
 		$action = Actionlog::LOCATION_BUY;
 		$targetobj = $actlog->createObject(4,'location',0); //lid,ltype.seller
 		$remark = array('lid'=>4,'cityid'=>0,
							'price'=>2333,
							'ltype'=>'酒店;经济型酒店',
							'buyRstCash'=>10000,'buyDiffAssets'=>20000,'sellRstCash'=>0,'tax'=>300);
 		$actlog->log_action($uid,$lid,$action,$targetobj,null,false,$remark);

	////////////////////////////////////////////////////////////////////////////////

		$action = Actionlog::LOCATION_DISCOVER;
		$targetobj = $actlog->createObject(4,'location',0); //lid,ltype.seller
		$remark = array('cityid'=>0,'ltype'=>'酒店;经济型酒店'
						);
		$actlog->log_action($uid,$lid,$action,$targetobj,null,false,$remark);
		echo "finish";
 	}

	public function add_action_visited_add()
 	{
 		$actlog = new Actionlog();
 		$uid = 1;
 		$lid = 4;
 		//$action = $logcfg['valid_action']['Location.Buy'];

 		$action = Actionlog::FAVORITE_ADD_VISITED_LOC;
		//$action = Actionlog::FAVORITE_ADD_LOVE_LOC;
		//$action = Actionlog::FAVORITE_REMOVE_VISITED_LOC;
		//$action = Actionlog::FAVORITE_REMOVE_LOVE_LOC;
		//$action = Actionlog::FAVORITE_ADD_VISITED_CITY;
		//$action = Actionlog::FAVORITE_ADD_LOVE_CITY;
		//$action = Actionlog::FAVORITE_REMOVE_VISITED_CITY;
		//$action = Actionlog::FAVORITE_REMOVE_LOVE_CITY;
 		$targetobj = $actlog->createObject(4,'location',3); //lid,ltype.seller or landlord
 		$remark = array();
 		$actlog->log_action($uid,$lid,$action,$targetobj,null,false,$remark);

	////////////////////////////////////////////////////////////////////////////////

		$action = Actionlog::LOCATION_DISCOVER;
		$targetobj = $actlog->createObject(4,'location',0); //lid,ltype.seller
		$remark = array('cityid'=>0,'ltype'=>'酒店;经济型酒店'
						);
		$actlog->log_action($uid,$lid,$action,$targetobj,null,false,$remark);
		echo "finish";
 	}

 	public function add_action_705()
 	{
 		$am = new Actionlog_Model();
 		$am->logid = ID_Factory::next_id($am);
 		$am->vid = 1;
 		$am->action =705;
 		$am->lid = 0;
 		$am->oid = 1;
 		$am->type = 'tool';
 		$am->created = 1280260800;
 		$am->remark = json_encode(array('tool'=>'禁售卡','limittime'=>'3小时'));
 		$am->upd_status = 0;
 		$am->save();
 		echo $am->remark;
 	}

 	public function add_action_lv()
 	{
 		$am = new Actionlog_Model();
 		$am->logid = ID_Factory::next_id($am);
 		$am->vid = 1;
 		$am->action =102;
 		$am->lid = 4;
 		$am->created = 1280260800;
 		$am->remark = '{"lv":4}';
 		$am->upd_status = 0;
 		$am->save();
 	}

 	public function add_action_500()
 	{
 		$am = new Actionlog_Model();
 		$am->logid = ID_Factory::next_id($am);
 		$am->vid = 1;
 		$am->action =500;
 		$am->lid = 0;
 		$am->oid = 1;
 		$am->type = 'comment';
 		$am->created = 1280260800;
 		$am->remark = json_encode(array('cityid'=>1,'comment'=>'ASDFASDFASDFASDF'));
 		$am->upd_status = 0;
 		$am->save();
 		echo $am->remark;
 	}

 		public function add_action_501()
 	{
 		$am = new Actionlog_Model();
 		$am->logid = ID_Factory::next_id($am);
 		$am->vid = 1;
 		$am->action =501;
 		$am->lid = 0;
 		$am->oid = 1;
 		$am->type = 'comment';
 		$am->created = 1280260800;
 		$am->remark = json_encode(array('cityid'=>1,'score'=>'85.5','impression'=>'asdfasfdasfasfd'));
 		$am->upd_status = 0;
 		$am->save();
 		echo $am->remark;
 	}


}
?>