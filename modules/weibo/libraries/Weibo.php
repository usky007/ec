<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: Weibo.php  2013-07-03 11:34:50Z Liaodd $
 *
 * @package    package_name
 * @author     UUTUU Liaodd
 * @copyright  (c) 2008-2013 UUTUU
 */
class Weibo {

	const MENTION = "MENTION";
	const USER = "USER";

	const PREFERENCE_KEY = 'WEIBO';
	const PREFERENCE_MENTION_SINCE_ID = 'MENTION_SINCE_ID';
	const PREFERENCE_USER_SINCE_ID = 'USER_SINCE_ID';

	const COMMENT_KEY = 'WBCOMM';

	const UPDATE_MENTION_MAX_ID = 'UPDATE_MAX_MENTION';
	const UPDATE_USER_MAX_ID = 'UPDATE_MAX_USER';
	const UPDATE_MENTION_SINCE_ID = 'UPDATE_SINCE_MENTION';
	const UPDATE_USER_SINCE_ID = 'UPDATE_SINCE_USER';

	const LIMIT_RATE = 70;

	private function read(Timeline $tl, $pfc_key, $limit = 98, $camp_list=array())
	{
		$pfc = Preference::instance(self::PREFERENCE_KEY);

		$since_id = $pfc->get($pfc_key);
		$since_id = isset($since_id) ? $since_id : 0;
		$params['since_id'] = $since_id;

		$tl->set_params($params);

		$weibo = array();
		$line = 0;
		$flag = true;
		while (count($tl->next_page($limit)) != 0) {
			foreach ($tl as $key => $value) {
				if($flag)
				{
					$pfc->set($pfc_key, $key);					
					$flag = false;
				}
                var_dump(2222222,'read');
                var_dump($key);
                var_dump($value['text']);
                echo "<br/><br/>";
                //continue;

                if(!empty($camp_list))
                    $this->notifyWithList($value,$camp_list);
                else
				    $this->notify($value);				
			}			
		}

		return $weibo;
	}

	private function notify($wb)
	{
		$camps = new Camp_Model();
		$camps = $camps->where(array("status" => 1, "db_status" => 3))->find_all();		
		foreach ($camps as $key => $camp) {
			$standardreceiver = new StandardReceiver($camp);
			$standardreceiver->receive($wb);
		}

		/** version : hooks
		$receivers = config::item('weibo.weibo.hooks');
		foreach ($receivers as $value) {			
			$obj = new $value;
			$obj->receive($wb);
		}*/
	}

	private function notifyWithList($wb,$camp_list)
	{
        foreach($camp_list as $camp){
            $obj = new StandardReceiver;
            $obj->receive($wb,$camp);
        }
	}

	public function test()
	{
		echo $this->filter('收着，不过也不知道有没有机会用到orz //@渥丹:A591真是美死了……下周去爬个山好了……Orz//@靡宝_周漪Zoe: 收着，将来有机会一定要去英国//@-沙拉娘-: @旅行者传媒 #晒Great英国照片，免费游Great英国！#');
	}

    public function getTweetStandard($camp_list){
        $utl = new UserTimeline();
        $this->read($utl, self::USER, 100 ,$camp_list);

        $mtl = new MentionTimeline();
        $this->read($mtl, self::MENTION, 200,$camp_list);
    }

    //get tweets of user and tweets of @user
	public function getTweet()
	{			
		$utl = new UserTimeline();
		$this->read($utl, self::PREFERENCE_USER_SINCE_ID, 100);

		$mtl = new MentionTimeline();
		$this->read($mtl, self::PREFERENCE_MENTION_SINCE_ID, 200);

		//update datas
		$updateutl = new UserTimeline();
		$this->update($updateutl, self::USER, 100);

		$updatemtl = new MentionTimeline();
		$this->update($updatemtl, self::MENTION, 200);
	}

	private function update(Timeline $tl, $code, $limit = 98)
	{
		switch ($code) {
			case self::MENTION:
				$max_key = self::UPDATE_MENTION_MAX_ID;
				$since_key = self::UPDATE_MENTION_SINCE_ID;				
				break;
			case self::USER:
				$max_key = self::UPDATE_USER_MAX_ID;
				$since_key = self::UPDATE_USER_SINCE_ID;				
				break;			
			default:
				throw new Kohana_Exception("传入参数不正确 ！", __CLASS__, __FUNCTION__);
				break;
		}

		$pfc = Preference::instance(self::PREFERENCE_KEY);
		$dic_comm = Preference::instance(self::COMMENT_KEY);
		$tweets = array();
		$since_id = $pfc->get($since_key);
		$since_id = isset($since_id) ? $since_id : 0;	
		$params['since_id'] = $since_id;
		$tl->set_params($params);

		$weibo = array();
		$line = 0;
		$flag = true;

		$max_id = $pfc->get($max_key);
		$max_id = isset($max_id) ? $max_id : 0;	
		
		//$bdd_end_id = 0;
		$bdd_since_id = 0;
		$total = 0;

		echo "-----$code-----begin max_id : ".$max_id."<br>";
		
		if (count($tl->load_one_page($limit,0,$max_id)) != 0) {
			$total = $tl->total();

			$first = null;

			foreach ($tl as $key => $value) {
				
				if($flag)
				{					
					$flag = false;
					$first = $value;
					//var_dump($first);
					continue;
					//$bdd_since_id = $key;
					//$bdd_end_id = $key;

				}

                var_dump(333333,'read');
                var_dump($key);
                //var_dump($value);
                var_dump($value['text']);
                //var_dump($value['comments_count']);
                //var_dump($dic_comm->get($key));
                //var_dump($value['comments_count']);
                //var_dump($dic_comm->get($key) != $value['comments_count']);

                if($dic_comm->get($key) != $value['comments_count'] && !is_null($tw_un = $this->check_Tweet_valid($key))) {
                	$dic_comm->set($key,$value['comments_count']);
                	$this->add_in_queen($tw_un);
                	var_dump("tweet {$key} has a new comment, on add it into queen ");
                }
                echo "<br/><br/>";
                //continue;
               	if($bdd_since_id == 0) {
               		$bdd_since_id = $key;
               	} else {
               		$bdd_since_id = $bdd_since_id < $key ? $bdd_since_id : $key;
               	}

                $tweets[$key] = $value;
                
                //$bdd_end_id  = $bdd_end_id > $key ? $bdd_end_id : $key;
			}	

			if(isset($first) && ($first['id'] <= $max_id && $first['id'] > $bdd_since_id) )  {
				$first_key = $first['id'];
				var_dump(4333334,'read');
                var_dump($first_key);
                if($dic_comm->get($first_key) != $first['comments_count'] && !is_null($tw_un = $this->check_Tweet_valid($first_key))) {
                	$dic_comm->set($first_key,$first['comments_count']);
                	$this->add_in_queen($tw_un);
                	var_dump("tweet {$first_key} has a new comment, on add it into queen ");
                }
                echo "<br/><br/>";
                $tweets[$first_key] = $first;
   			}

			//set max_id = $bdd_since_id			
			$pfc->set($max_key, $bdd_since_id);
		}

		if($total <= 2 ) {
			var_dump("key 归 0");
			$pfc->set($max_key, 0);
		}
	
		if(!$flag && $max_id > $bdd_since_id) {			
			//暂时不删除
			//$this->updateTweetData($tweets, $bdd_since_id, $max_id, $code);		
		}				
		return $weibo;
	}	

	public function getSinauid()
	{
		$cfg = config::ditem('activity.offical_account');
		$user = new User_Model();
		$user->where('uid', $cfg)->find();
		if(!$user->loaded)
		{
			throw new Kohana_Exception("找不到该用户", __CLASS__, __FUNCTION__);
		}		
        
		$cm = new Credential_Model();
		$c = $cm->find_user_credentials($user);
		return $c[0]->identity;
	}

	private function updateTweetData($tweets,$since_id,$end_id, $code)
	{
		$sinaUid = $this->getSinauid();
		$where = array();

		switch ($code) {
			case self::MENTION:
				$pfc_key = self::UPDATE_MENTION_SINCE_ID;
				$where = array("uid != " => $sinaUid);
				break;
			case self::USER:
				$pfc_key = self::UPDATE_USER_SINCE_ID;
				$where = array("uid" => $sinaUid);
				break;			
			default:
				throw new Kohana_Exception("传入参数不正确 ！", __CLASS__, __FUNCTION__);
				break;
		}

		$pfc = Preference::instance(self::PREFERENCE_KEY);	
		$camps = new Camp_Model();
		$camps = $camps->where(array("status" => 1, "db_status" => 3))->find_all();		
		foreach ($camps as $key => $camp) {
			$pfc = Preference::instance($camp->name);
			$tmObjs = new Camp_Standard_Tweet_Model(null,$camp->name);
			$tmObjs = $tmObjs->where("tweet_id >=",$since_id)->where("tweet_id <=",$end_id)->where($where)->find_all();
			foreach ($tmObjs as $tmObj) {
				if(!array_key_exists($tmObj->tweet_id,$tweets)) {
					var_dump("delete comments for tweet_id : ".$tmObj->tweet_id);
					$tcm = new Camp_Standard_TweetComments_Model(null,$camp->name);
					//$tcm->where('tweet_id', $tmObj->tweet_id)->delete_all();
					log::debug("delete tweet_id : ".$tmObj->tweet_id);
					//$tmObj->delete();					
				} else {
					$oldheat = $pfc->get($tmObj->tweet_id) != null ? $pfc->get($tmObj->tweet_id) : 0;
					$wbTweet = $tweets[$tmObj->tweet_id];
					$newheat = $wbTweet['reposts_count'] + $wbTweet['comments_count'] + $wbTweet['attitudes_count'];
					$pfc->set($tmObj->tweet_id, $newheat);
					log::debug("update tweet_id : ".$tmObj->tweet_id . "heat : ".$newheat);
					$tmObj->heat = $newheat - $oldheat;		
					$tmObj->save();
				}
			}			
		}
	}

	private function check_Tweet_valid($tweet_id)
	{
		$camps = new Camp_Model();
		$camps = $camps->where(array("status" => 1, "db_status" => 3))->find_all();		
		foreach ($camps as $key => $camp) {
			$tweet_mod = new  Camp_Standard_Tweet_Model($tweet_id,$camp->name);
			if($tweet_mod->find()->loaded()) {
				return array("tweet_id" => $tweet_id, "camp_name" => $camp->name);
			} 
		}
		var_dump("tweet_id is not find ".$tweet_id);
		return null;
	}

	private function add_in_queen($tw)
	{
		$queue = new QueueTweets_Model();
		$queue->where(array('tweet_id' => $tw['tweet_id']))->find();
		if(!$queue->loaded)
		{
			$queue->queueid = ID_Factory::next_id($queue);
			$queue->tweet_id = $tw['tweet_id'];
			$queue->name = $tw['camp_name'];
			$queue->save();
		}
	}

	public function getComments1()
	{
		$ca = config::ditem('activity.comment_account');
		if($ca == null )
		{
			throw new Kohana_Exception("请先配置comment_account", __CLASS__, __FUNCTION__);
		}

		$cui = new User_Model();
		$cui->where('uid', $ca)->find();

		$queue = new QueueTweets_Model();
		$tweets = $queue->orderby('updated', 'ASC')->limit(self::LIMIT_RATE)->find_all();

		foreach ($tweets as $key => $tw) {
			$cmt = new Comments($cui);
			$cmt->set_params(array('id' => $tw->tweet_id));
			if(count($cmt->next_page(5)) != 0)
			{
				foreach ($cmt as $key => $value) {
					var_dump("add comment ".$value['text']);
					$tcm = new Camp_Standard_TweetComments_Model(null,$tw->name);
					//$tcm = new $tcmName();
					$tcm->where('tweetComments_id', $value['id'])->find();
					if(!$tcm->loaded)
					{
						$tcm->tweetComments_id = $value['id'];
						$tcm->tweet_id = $tw->tweet_id;
						$tcm->content = $value['text'];
						$tcm->name = $value['user']['name'];
						$tcm->avatar = $value['user']['profile_image_url'];
						$tcm->link = $value['user']['profile_url'];
						$tcm->save();
					}						
				}		
			}
			$tw->delete();
		}
	}

	public function getComments()
	{
		$pfc = Preference::instance('britain_heat');

		$ca = config::ditem('activity.comment_account');
		$ta = config::ditem('activity.tweet_account');

		if($ca == null || $ta == null)
		{
			throw new Kohana_Exception("请先配置comment_account和tweet_account", __CLASS__, __FUNCTION__);
		}

		$user = new User_Model();
		$user->where('uid', $ta)->find();
		$cui = new User_Model();
		$cui->where('uid', $ca)->find();
		$max = 0;
		$cmts = array();
				
		$camps = new Camp_Model();
		$camps = $camps->where(array("status" => 1, "db_status" => 3))->find_all();

		foreach ($camps as $key => $camp) {
			$tm = new Camp_Standard_Tweet_Model(null,$camp->name);
			$tcmName = $camp->name;
			$all = $tm->orderby('updated', 'ASC')->find_all();
			//$all = $tm->getTweets('', array('updated' => "desc"));
			$cmts[$tcmName] = $all;
			$max = count($all) > $max ? count($all) : $max;
		}

		for ($i = 0; $i < $max; $i++) { 
			foreach ($cmts as $tcmName => $value) {				
				if(isset($value[$i]))
				{					
					$this->_getNewInfo($value[$i], $user, $cui, $pfc, $tcmName);
				}
			}
		}		
	}

	private function _getNewInfo($tweet, $tuser, $cuser, $heatpfc, $tcmName)
	{
		$st = new SingleTweet($tuser);
		$wbTweet = $st->get($tweet->tweet_id);
		
		if($wbTweet == null)
		{		
			//$tmObj = new Camp_Standard_Tweet_Model($tweet->tweet_id,$tcmName);
			//$tmObj->delete();
			$tweet->delete();

			$tcm = new Camp_Standard_TweetComments_Model(null,$tcmName);
			$tcm->where('tweet_id', $tweet->tweet_id)->delete_all();

			//$ctr = new Camp_Br_CateTweetRelation_Model();
			//$ctr->where('tweet_id', $tweet->tweet_id)->delete_all();
			return;
		}		
		
		$oldheat = $heatpfc->get($tweet->tweet_id) != null ? $heatpfc->get($tweet->tweet_id) : 0;
		$newheat = $wbTweet['reposts_count'] + $wbTweet['comments_count'] + $wbTweet['attitudes_count'];
		$heatpfc->set($tweet->tweet_id, $newheat);

		//$tmObj = new Camp_Standard_Tweet_Model($tweet->tweet_id,$tcmName);
		$tweet->heat = $newheat - $oldheat;
		$tweet->save();

		$cmt = new Comments($cuser);
		$cmt->set_params(array('id' => $tweet->tweet_id));
		if(count($cmt->next_page(5)) != 0)
		{
			foreach ($cmt as $key => $value) {
				
				$tcm = new Camp_Standard_TweetComments_Model(null,$tcmName);
				//$tcm = new $tcmName();
				$tcm->where('tweetComments_id', $value['id'])->find();
				if(!$tcm->loaded)
				{
					$tcm->tweetComments_id = $value['id'];
					$tcm->tweet_id = $tweet->tweet_id;
					$tcm->content = $value['text'];
					$tcm->name = $value['user']['name'];
					$tcm->avatar = $value['user']['profile_image_url'];
					$tcm->link = $value['user']['profile_url'];
					$tcm->save();
				}						
			}		
		}
	}
}