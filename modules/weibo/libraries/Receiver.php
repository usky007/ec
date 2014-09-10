<?php defined('SYSPATH') OR die('No direct access allowed.');
abstract class Receiver {

	protected $prfKey = '';
	protected $TweetModel = '';
	protected $TweetCommentsModel = '';

	abstract function getPrekey();
	abstract function getTweetModel();
	abstract function getTweetCommentsModel();

	abstract function setupCategory($tweet);

	public function receive($value) {
	
		if($this->filter($value['text']))
		{
			$wb = isset($value['retweeted_status']) ? $value['retweeted_status'] : $value;	
	
			if(isset($wb['deleted']) && $wb['deleted'] == 1)
				return;

			if(count($wb['pic_urls']) == 0)
				return;
			$tmodelname = $this->getTweetModel();
			$tm = new $tmodelname;
			$tm->where('tweet_id', $wb['idstr'])->find();
			if(!$tm->loaded)
			{
				$tm->tweet_id = $wb['idstr'];
				foreach ($wb['pic_urls'] as $k => $pic_url) {
					$wb['pic_urls'][$k]['thumbnail_pic'] = str_ireplace('thumbnail', 'bmiddle', $pic_url['thumbnail_pic']);
				}
				$tm->pic = json_encode($wb['pic_urls']);
				$tm->content = $wb['text'];
				$tm->uid = $wb['user']['id'];
				$tm->name = $wb['user']['name'];
				$tm->avatar = $wb['user']['profile_image_url'];
				$tm->link = $wb['user']['profile_url'];
				$tm->heat = $wb['reposts_count'] + $wb['comments_count'] + $wb['attitudes_count'];
				$tm->source = strip_tags($wb['source']);
				$tm->save();

				$this->setupCategory($tm);
			}
			if(isset($value['retweeted_status']))
			{
				$tcmodelname = $this->getTweetCommentsModel();
				$tcm = new $tcmodelname;
				$tcm->where('tweetComments_id', $value['idstr'])->find();
				if(!$tcm->loaded)
				{
					$tcm->tweetComments_id = $value['idstr'];
					$tcm->tweet_id = $wb['idstr'];
					$tcm->content = $value['text'];
					$tcm->name = $value['user']['name'];
					$tcm->avatar = $value['user']['profile_image_url'];
					$tcm->link = $value['user']['profile_url'];
					$tcm->save();
				}
			}
		
			return true;
		}
		else
			return false;
	}

	protected function filter($text)
	{
		$tmp = strtolower($text);
		$keywords = config::ditem($this->getPrekey());
		if(is_null($keywords))
			return false;
		$keywords = json_decode($keywords);
		
		foreach ($keywords as $key => $value) {
			if(preg_match('/'.$value.'/', $tmp))
				return true;
		}

		return false;
	}
}