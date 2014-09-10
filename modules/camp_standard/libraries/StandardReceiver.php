<?php defined('SYSPATH') OR die('No direct access allowed.');
class StandardReceiver extends Receiver {
	protected $prfKey = '';
	protected $TweetModel = '';
	protected $TweetCommentsModel = '';
    protected $camp;

    public function __construct($camp)
    {
        if(is_null($camp)) {
            throw new Kohana_Exception("camp 不能为空", __CLASS__, __FUNCTION__);
        }

        $this->camp = $camp;
    }

	public function getPrekey()
	{
		return $this->prfKey;
	}

	public function getTweetModel()
	{
        return "Camp_".$this->camp->name."_Tweet_Model";
		//return $this->TweetModel;
	}

	public function getTweetCommentsModel()
	{        
        return "Camp_".$this->camp->name."_TweetComments_Model";
		//return $this->TweetCommentsModel;
	}

    public function receive($value) {
        if($this->filter($value['text'],$this->camp->keywords))
        {
            $wb = isset($value['retweeted_status']) ? $value['retweeted_status'] : $value;
            if(isset($wb['deleted']) && $wb['deleted'] == 1)
                return;

            if(count($wb['pic_urls']) == 0)
                return;

            $tm = new Camp_Standard_Tweet_Model(null,$this->camp->name);
            $tm->where('tweet_id', $wb['idstr'])->find();//var_dump($tm->loaded);exit;
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
                $this->setupCategoryStandard($tm,$this->camp);
            }

            if(isset($value['retweeted_status']))
            {
                $tcm = new Camp_Standard_TweetComments_Model(null,$this->camp->name);
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

    protected function filter($text,$keywords)
    {

        $tmp = strtolower($text);
        if(is_null($keywords))
            return false;
        $keywordsArr = json_decode($keywords);
        if($keywordsArr==false){
            return false;
        }else{
            foreach ($keywordsArr as $value) {
                if(preg_match('/'.$value.'/', $tmp))
                    return true;
            }
        }
        return false;
    }

    public function setupCategory($tweet){
        return true;
    }

	public function setupCategoryStandard($tweet)
	{		
        $category = $this->camp->category;
		$category = json_decode($category, true);
	
		foreach ($category as $key => $value) {
			foreach ($value as $item) {
				if(preg_match('/'.$item.'/', $tweet->content))
				{
					$ctr = new Camp_Standard_Category_Model(null,$this->camp->name);
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