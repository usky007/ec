<?php defined('SYSPATH') OR die('No direct access allowed.');
class PhReceiver extends Receiver {
	protected $prfKey = 'ph.keywords';
	protected $TweetModel = 'Camp_Ph_Tweet_Model';
	protected $TweetCommentsModel = 'Camp_Ph_TweetComments_Model';

	public function getPrekey()
	{
		return $this->prfKey;
	}

	public function getTweetModel()
	{
		return $this->TweetModel;
	}

	public function getTweetCommentsModel()
	{
		return $this->TweetCommentsModel;
	}

	public function setupCategory($tweet)
	{
		$category = config::ditem('ph.category');

		$category = json_decode($category, true);
	
		foreach ($category as $key => $value) {		
			foreach ($value as $item) {
				if(preg_match('/'.$item.'/', $tweet->content))
				{
					$ctr = new Camp_Ph_Category_Model();
					$ctr->where(array('keyword' => $key, 'tweet_id' => $tweet->tweet_id))->find();
					if(!$ctr->loaded)
					{
						$ctr->id = ID_Factory::next_id($ctr);
						$ctr->tweet_id = $tweet->tweet_id;
						$ctr->keyword = $key;
						$ctr->save();
					}
				}
			}
		}
	}
}