<?php
/**
 * class description 
 * 
 *  waterfall
 *  
 * @author cuiyulei
 *
 */
class Waterfall_Controller extends ServiceController
{
	const ITEM_NUM = 5;
	const COMMENT_NUM = 4;
	public function waterfall()
	{
		$num = Input::instance()->query('num', 0);
		$order = Input::instance()->query('order', 'tweet_id');
		$keyword = Input::instance()->query('keyword', 'all');
		$tweetsModel = Input::instance()->query('tweet');
		$tweetCommentsModel = Input::instance()->query('tweetComments');
		$cateModel = Input::instance()->query('cateModel', '');
		$camp_name = Input::instance()->query('camp_name', ''); 
		$tweets = empty($camp_name) ? $this->_getTweets_by_Model($num, $order, $keyword, $tweetsModel, $cateModel) : $this->_getTweets_by_campName($num, $order, $keyword, $camp_name);
//		$tweets = $this->_getTweets($num, $order, $keyword, $tweetsModel, $cateModel, $camp_name);
		$datas = array();
		$sourceCfg = config::item('weibo.weibo.client');
		foreach ($tweets as $k => $tt){
			$comments = $this->_getComments($tt, $tweetCommentsModel, $camp_name);
			$t = array('id'=>$tt->tweet_id, 'content' => $tt->content, 'img' => $tt->pic, 'name'=>$tt->name, 'avatar'=>$tt->avatar, 'link'=>$tt->link);
			if(isset($sourceCfg[$tt->source]))
			{
				$source = array('name' => $tt->source, 'link' => $sourceCfg[$tt->source]);
				$t['source'] = $source;
			}
			$datas[$k]['tweet']    = $t;
			$datas[$k]['comments'] = $comments;
		}
		$this->set_output(array('weibo' => $datas));
	}

	private function _getComments($tweet, $tweetCommentsModel, $camp_name = '')
	{
        if($camp_name!=''){
            $twcMod = new Camp_Standard_TweetComments_Model(null,$camp_name);
        }else{
            $twcMod = new $tweetCommentsModel();
        }

		$comments = $twcMod->where('tweet_id', $tweet->tweet_id)->orderby('tweetComments_id', 'DESC')->limit(self::COMMENT_NUM)->find_all();
		$tComments = array();
		foreach ($comments as $cm){
			$tComments[] = array('id' =>$cm->tweetComments_id, 'content'=>$cm->content, 'name'=>$cm->name, 'avatar'=>$cm->avatar, 'link'=>$cm->link);
		}
		return $tComments;
	}
	
	/*private function _getTweets($num, $order, $keyword, $tweetsModel = '', $cateModel = '' , $camp_name = '')
	{
		$start = $num * self::ITEM_NUM;
		$tweets = array();
		$order = $order == 'hot' ? 'heat' : 'tweet_id';
		if(!$keyword || $keyword == 'all'){
            if($camp_name!=''){
                $twMod = new Camp_Standard_Tweet_Model(null,$camp_name);               
                $tweets = $twMod->getTweets('',  array($order=>'DESC'), self::ITEM_NUM, $start);
            }else{
                $twMod = new $tweetsModel();
                //$tweets = $twMod->getTweets('',  array($order=>'DESC'), self::ITEM_NUM, $start);
                $tweets = $twMod->orderby($order, 'DESC')->limit(self::ITEM_NUM, $start)->find_all();
            }			
		}
		else if($cateModel != ''){
            if($camp_name!=''){
                $ctm = new Camp_Standard_Category_Model(null,$camp_name);               
                $tweets = $ctm->getTweets($keyword,array(), self::ITEM_NUM,$start );               
            }else{
                $ctm = new $cateModel();
                $tweets = $ctm->getTweets($keyword, $start, self::ITEM_NUM);
            }			
		} else {
			$catMod = new Camp_Br_Category_Model();
			$curCat = $catMod->where('key', $keyword)->find();
			if($curCat->loaded){
				$db = & Database::instance();
				$sql = "SELECT * FROM `{$db->table_prefix()}Camp_Br_Tweet` WHERE `tweet_id` IN (SELECT `tweet_id` FROM `{$db->table_prefix()}Camp_Br_CateTweetRelation` WHERE `cate_id`={$curCat->id}) ORDER BY `{$db->table_prefix()}Camp_Br_Tweet`.`{$order}` DESC".' LIMIT '.$start.','.self::ITEM_NUM;
				log::debug('sql='.$sql);
				$tweets = $db->query($sql);
			}
		}
		return $tweets;	
	}*/

	private function _getTweets_by_Model($num, $order, $keyword, $tweetsModel = '', $cateModel = '')
	{
		$start = $num * self::ITEM_NUM;
		$tweets = array();
		$order = $order == 'hot' ? 'heat' : 'tweet_id';
		if(!$keyword || $keyword == 'all'){
            $twMod = new $tweetsModel();            
            $tweets = $twMod->orderby($order, 'DESC')->limit(self::ITEM_NUM, $start)->find_all();		
		}
		else if($cateModel != ''){
            $ctm = new $cateModel();
            $tweets = $ctm->getTweets($keyword, $start, self::ITEM_NUM);		
		} else {
			$catMod = new Camp_Br_Category_Model();
			$curCat = $catMod->where('key', $keyword)->find();
			if($curCat->loaded){
				$db = & Database::instance();
				$sql = "SELECT * FROM `{$db->table_prefix()}Camp_Br_Tweet` WHERE `tweet_id` IN (SELECT `tweet_id` FROM `{$db->table_prefix()}Camp_Br_CateTweetRelation` WHERE `cate_id`={$curCat->id}) ORDER BY `{$db->table_prefix()}Camp_Br_Tweet`.`{$order}` DESC".' LIMIT '.$start.','.self::ITEM_NUM;
				log::debug('sql='.$sql);
				$tweets = $db->query($sql);
			}
		}
		return $tweets;	
	}

	private function _getTweets_by_campName($num, $order, $keyword, $camp_name) {
		$start = $num * self::ITEM_NUM;
		$tweets = array();
		$order = $order == 'hot' ? 'heat' : 'tweet_id';
		if(!$keyword || $keyword == 'all'){
            $twMod = new Camp_Standard_Tweet_Model(null,$camp_name);               
            $tweets = $twMod->getTweets('',  array($order=>'DESC'), self::ITEM_NUM, $start);	
		} else {
            $ctm = new Camp_Standard_Category_Model(null,$camp_name);
            $tweets = $ctm->getTweets($keyword, $start, self::ITEM_NUM);		
		} /*else {
			$catMod = new Camp_Br_Category_Model();
			$curCat = $catMod->where('key', $keyword)->find();
			if($curCat->loaded){
				$db = & Database::instance();
				$sql = "SELECT * FROM `{$db->table_prefix()}Camp_Br_Tweet` WHERE `tweet_id` IN (SELECT `tweet_id` FROM `{$db->table_prefix()}Camp_Br_CateTweetRelation` WHERE `cate_id`={$curCat->id}) ORDER BY `{$db->table_prefix()}Camp_Br_Tweet`.`{$order}` DESC".' LIMIT '.$start.','.self::ITEM_NUM;
				log::debug('sql='.$sql);
				$tweets = $db->query($sql);
			}
		}*/
		return $tweets;	
	}


}