<?php
class Camp_Wa_Category_Model extends ORM_Cached
{
	protected $_primary_key = 'id';
	protected $_table_name = 'Camp_Wa_Category';
	protected $_table_names_plural = FALSE;
	// protected $_created_column = array ("column" => 'created');
	// protected $_updated_column = array ("column" => 'updated');

	public function getTweets($keyword, $start, $range)
	{
		// $db = & Database::instance();
		// $ctname = $db->table_prefix().$this->_table_name;
		// $tname = $db->table_prefix().'Camp_Wa_Tweet';

		$db = & Database::instance();
 		$db->from($this->_table_name);
 		$db->join('Camp_Wa_Tweet', array($this->_table_name.'.tweet_id' => 'Camp_Wa_Tweet.tweet_id'), '', "INNER");
 		$db->where(array($this->_table_name.'.keyword' => $keyword));
 		$db->orderby('Camp_Wa_Tweet.created', 'desc');
 		$db->select('*');
 	//	var_dump(func_get_args());exit;
 		$db->limit($range, $start);
 		$tweets = $db->get();
 		return $tweets;
	}

	public function reCate()
	{
		$category = config::ditem('wa.category');
		$category = json_decode($category, true);
		if(count($category) > 0)
		{
			$cwcm = new Camp_Wa_Category_Model();
			$cwcm->where('tweet_id >', 0)->delete_all();

			$cwtm = new Camp_Wa_Tweet_Model();
			$cwts = $cwtm->find_all();
			foreach ($cwts as $tweet) {
				foreach ($category as $key => $cate) {
					foreach ($cate as $value) {
						if(preg_match('/'.$value.'/', $tweet->content))
						{
							$ctr = new Camp_Wa_Category_Model();
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
					CREATE TABLE  IF NOT EXISTS  `{$db->table_prefix()}Camp_Wa_Category` (
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
}