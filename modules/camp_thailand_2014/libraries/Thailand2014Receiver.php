<?php defined('SYSPATH') OR die('No direct access allowed.');
class Thailand2014Receiver extends Receiver {
	protected $prfKey = 'thailand2014.keywords';
	protected $TweetModel = 'Camp_Thailand2014_Tweet_Model';
	protected $TweetCommentsModel = 'Camp_Thailand2014_TweetComments_Model';

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
		$category = config::ditem('thailand2014.category');

		$category = json_decode($category, true);
	
		foreach ($category as $key => $value) {		
			foreach ($value as $item) {
				if(preg_match('/'.$item.'/', $tweet->content))
				{
					$ctr = new Camp_Thailand_Category_Model();
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