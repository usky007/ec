<?php
class weibo_Controller extends ServiceController {
	public function mention()
	{
		$wb = new Weibo();
		$wb->getTweet();
	}

	public function comment()
	{
		$wb = new Weibo();
		//$wb->getComments();
		$wb->getComments1();
	}

	public function init_update_since_id() 
	{
		//get min_tweet_id from BDD
		$since_id = 0;
		$camps = new Camp_Model();
		$camps = $camps->where(array("status" => 1, "db_status" => 3))->find_all();		
		foreach ($camps as $key => $camp) {
			$tmObjs = new Camp_Standard_Tweet_Model(null,$camp->name);
			$tmObjs = $tmObjs->find_all();
			foreach ($tmObjs as $tmObj) {
				if($since_id == 0) {
					$since_id = $tmObj->tweet_id;
				} else {
					$since_id = $since_id > $tmObj->tweet_id ? $tmObj->tweet_id : $since_id;
				}				
			}
		}

		$pfc = Preference::instance(Weibo::PREFERENCE_KEY);
		if($since_id == 0) {			
			$since_id_mention = $pfc->get(Weibo::PREFERENCE_MENTION_SINCE_ID);
			$since_id_user = $pfc->get(Weibo::PREFERENCE_USER_SINCE_ID);
			$pfc->set(Weibo::UPDATE_USER_SINCE_ID,$since_id_user);
			$pfc->set(Weibo::UPDATE_MENTION_SINCE_ID,$since_id_mention);
		} else {
			$pfc->set(Weibo::UPDATE_USER_SINCE_ID,$since_id);
			$pfc->set(Weibo::UPDATE_MENTION_SINCE_ID,$since_id);
		}
	}
}