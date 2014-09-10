<?php
class BritainCategory {
	public function category()
	{
		$category = config::ditem('britain.category');
		if(is_null($category))
			return false;
		$category = json_decode($category);

		$tm = new Camp_Br_Tweet_Model();
		$all = $tm->orderby('updated', 'ASC')->find_all();
		foreach ($all as $tweet) {
			$this->do_category($tweet, $category);
		}
	}

	public function initcategory()
	{
		$category = config::ditem('britain.category');
		if(is_null($category))
			return false;
		$category = json_decode($category);

		foreach ($category as $key => $value) {
			echo $key.' '.$value->id.' '.$value->key.'<br/>';
			$cm = new Camp_Br_Category_Model();
			$cm->where(array('name' => $key, 'key' => $value->key))->find();
			if(!$cm->loaded)
			{
				$cm->id = $value->id;
				$cm->name = $key;
				$cm->key = $value->key;
				$cm->save();
			}
		}
	}

	public function removecategory()
	{
		$dcategory = config::ditem('britain.deletecategory');
		if(is_null($dcategory))
			return false;
		//var_dump($dcategory);exit;
		$dcategory = json_decode($dcategory);
		
		$filter = array();
		foreach ($dcategory as $value) {
			$filter[] = $value;
		}

		if(count($filter) > 0)
		{
			$cm = new Camp_Br_Category_Model();
			$cm->in('name', $filter)->delete_all();
		}
	}

	private function do_category(Camp_Br_Tweet_Model $tweet, $categorycfg)
	{
		foreach ($categorycfg as $key => $value) {
			foreach ($value->value as $item) {
				if(preg_match('/'.$item.'/', $tweet->content))
				{
					$ctr = new Camp_Br_CateTweetRelation_Model();
					$ctr->where(array('tweet_id' => $tweet->tweet_id, 'cate_id' => $value->id))->find();
					if(!$ctr->loaded)
					{
						$ctr->id = ID_Factory::next_id($ctr);
						$ctr->tweet_id = $tweet->tweet_id;
						$ctr->cate_id = $value->id;
						$ctr->save();
					}
					break;
				}
			}
		}
	}
}