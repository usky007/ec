<?php defined('SYSPATH') OR die('No direct access allowed.');
class BritainReceiver extends Receiver {
	protected $prfKey = 'britain.keywords';
	protected $TweetModel = 'Camp_Br_Tweet_Model';
	protected $TweetCommentsModel = 'Camp_Br_TweetComments_Model';

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
		//do nothing
	}
}