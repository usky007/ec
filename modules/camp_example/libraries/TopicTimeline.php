<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: TopicTimeline.php 3 2011-06-07 03:00:48Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2009-2011 UUTUU
 */
class TopicTimeline extends Timeline implements Page_Iterator, ArrayAccess {

	protected $keyword	  = null;
	protected $topCache   = false;
	
	const TABLE_TIMELINE = 'TimelineCaches';
	const TABLE_REPLY    = 'TimelineReply';
	
	const TYPE_MUSIC = 'music';
	const TYPE_PIC 	 = 'pic';
	const TYPE_RADIO = 'radio';
	
	const GET_URL = "/trends/statuses.json";

	
	/**
	 * input credential and keyrod.
	 *
	 * @param Credential object
	 * @param keyword
	 */
	public function __construct(Credential $cred,$keyword) {
		parent::__construct($cred);
		$this->keyword = $keyword;
	}
	
	protected function get_api_url($uri) {
		if ($uri === Timeline::GET_URL)
			return parent::get_api_url(self::GET_URL);
			
		return parent::get_api_url($uri);
	}
	
	protected function & build_parameters() {
		$params = & parent::build_parameters();
		$params['trend_name'] = $this->keyword;
		return $params;
	}

	/**
	 * get timelines.
	 *
	 * @return TopicTimeline
	 */
	protected function get_timelines(){
		
		$timelines = new Timelinecaches_Model;
		$this->timelines = $timelines->getTlByIndex($this->keyword);
		if(count( $this->timelines ) == 0){
			return false;
		}
		return $this;
		
	}
	
	/**
	 * add timelines.
	 *
	 * @return TopicTimeline
	 */
	protected function add_timelines($timelines){
		if (count($timelines) == 0) {
			return false;
		}
		$next = null;
		foreach($timelines as $v){
			$timeline = $this->insertCache($v);
			$this->insertIndex($timeline,$next);
			$this->insertUser($timeline);
			$this->insertRaw($v,$timeline->tcid,self::TABLE_TIMELINE);
			$next = $timeline->tcid;
		}
		return $this;
	}
	
	/**
	 * add reply.
	 *
	 * @return TopicTimeline
	 */
	protected function add_reply($timelines){
		if (count($timelines) == 0) {
			return false;
		}
		$next = null;
		$user_cache_arr = array();
		foreach($timelines as $v){
			$timeline = $this->insertCache($v);
			$this->insertIndex($timeline,$next);
			//check user insert
			if (!in_array($timeline,$timeline->identily)) {
				$this->insertUser($timeline);
				$user_cache_arr[] = $timeline->identily;
			}
			
			$this->insertRaw($v,$timeline->tcid,self::TABLE_TIMELINE);
			$next = $timeline->tcid;
			
		}
		return $this;
	}
	
	/**
	 * insert  Timelinecaches data.
	 *
	 * @return Timelinecaches object
	 */
	protected function insertCache($timeline){
		$model = new Timelinecaches_Model();
		$model->tcid = ID_Factory::next_id ( 'TimelineCaches_tcid' );
		$model->srcAgent 	= $this->srcAgent;
		$model->srcid 		= $timeline['id'];//third party name uid
		$model->identily 	= $timeline['user']['name'];//third party name
		
		$user = $this->checkUser();
		if($user){
			$model->uid = $user->uid;
		}
		$model->username 	= $timeline['user']['name'];
		$model->text 		= $timeline['text'];
		if (isset($timeline['original_pic'])) {
			$model->mediaType = self::TYPE_PIC;
			$model->mediaLink 	= $timeline['original_pic'];
			
			//TODO get width .height
			$model->mediaWidth 	= $timeline['mediaWidth'];
			$model->mediaHeight = $timeline['mediaHeight'];
		}
		//TODO get retweet .reply
		$model->retweeted 	= $timeline['retweeted']; # of retweeted by others
		$model->replied 	= $timeline['replied']; //# of replied by users
		
		$model->published 	= strtotime($timeline['created_at']); //third party timeline created 
		return $model->save();
	}
	
	/**
	 * insert  Timelineindex data.
	 *
	 * @return Timelineindex object
	 */
	protected function insertIndex($timeline,$next=null){
		$model = new Timelineindex_Model();
		$model->tsid 	 = ID_Factory::next_id ( 'TimelineIndex_tsid' );
		$model->srcAgent = $this->srcAgent;
		$model->keyword  = $this->keyword;
		$model->tcid 	 = $timeline->tcid;
		$model->next 	 = $next;
		$model->sequence = $timeline->sequence;  //use srcid if possible
		return $model->save();
	}
	
	/**
	 * insert  Thirdpartyusers data.
	 *
	 * @return Thirdpartyusers object
	 */
	protected function insertUser($timeline){
		$model = new Thirdpartyusers_Model();
		$model->tpid = ID_Factory::next_id ( 'ThirdPartyUsers_tpid' );
		$model->srcAgent = $this->srcAgent;
		$model->srcid = $timeline->identily;//third party uid
		$model->uid = $timeline->uid;
		$model->avatar = $this->userurl;
		return $model->save();
	}
	
		/**
	 * insert  Thirdpartyusers data.
	 *
	 * @return Thirdpartyusers object
	 */
	protected function insertRaw($timeline,$tcid,$table){
		$model = new Timelineraw_Model();
		$model->uuid = $table.$tcid;
		$model->raw  = json_encode($timeline);
		return $model->save();
	}
	
	protected function checkUser(){
		//TODO. check is login user
		if($loginuser == $uid){
			//
		}
		$user_model = new Credential_Model();
		$user = $user_model->where('provider',$this->srcAgent)->where('identity',$timeline->identity)->find();
		return $user;
	}
	
	
}
?>