<?php
class Camp_Thailand2014_Category_Model extends ORM_Cached
{
	protected $_primary_key = 'id';
	protected $_table_name = 'Camp_Thailand2014_Category';
	protected $_table_names_plural = FALSE;
	// protected $_created_column = array ("column" => 'created');
	// protected $_updated_column = array ("column" => 'updated');

	public function getTweets($keyword, $start, $range)
	{

		$db = & Database::instance();
 		$db->from($this->_table_name);
 		$db->join('Camp_Thailand2014_Tweet', array($this->_table_name.'.tweet_id' => 'Camp_Thailand2014_Tweet.tweet_id'), '', "INNER");
 		$db->where(array($this->_table_name.'.keyword' => $keyword));
 		$db->orderby('Camp_Thailand2014_Tweet.created', 'desc');
 		$db->select('*');
 		
 		$db->limit($range, $start);
 		$tweets = $db->get();
 		return $tweets;
	}

	public function reCate()
	{
		$category = config::ditem('thailand2014.category');
		$category = json_decode($category, true);
		if(count($category) > 0)
		{
			$cwcm = new Camp_Thailand2014_Category_Model();
			$cwcm->where('tweet_id >', 0)->delete_all();

			$cwtm = new Camp_Thailand2014_Tweet_Model();
			$cwts = $cwtm->find_all();
			foreach ($cwts as $tweet) {
				foreach ($category as $key => $cate) {
					foreach ($cate as $value) {
						if(preg_match('/'.$value.'/', $tweet->content))
						{
							$ctr = new Camp_Thailand2014_Category_Model();
							$ctr->where(array('tweet_id' => $tweet->tweet_id, 'keyword' => $key))->find();
							if(!$ctr->loaded)
							{
								$ctr->id = ID_Factory::next_id($ctr);
								$ctr->tweet_id = $tweet->tweet_id;
								$ctr->keyword = $key;
								$ctr->save();
							}
							break;
						}
					}
				}
			}
		}
	}
	
	public static function deploy() 
	{
		$db = & Database::instance();
		try {
			$result = $db->query("
					CREATE TABLE  IF NOT EXISTS  `{$db->table_prefix()}Camp_Thailand2014_Category` (
					  `id` int(11) NOT NULL COMMENT 'id',
					  `tweet_id` bigint(20) NOT NULL,
					  `keyword` varchar(20) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `tweet_id_keyword` (`keyword`, `tweet_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					");
					echo  __CLASS__.' deployed.<br />';
					return 1;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}	
	}

	public static function deploy_1() 
	{
		$db = & Database::instance();
		try {
			$preference = Preference::instance('application');
			$preference->set('thailand2014-category', json_encode(array()));
			echo  __CLASS__.' deployed. category has been added.<br />';
			return 2;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}	
	}
}