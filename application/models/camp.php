<?php
class Camp_Model extends ORM_Cached
{
	const CAMP_CLOSE = 0;
	const CAMP_OPEN = 1;
	const CAMP_PENDING = 2;
 	protected $_primary_key = 'camp_id';
	protected $_table_name = 'Camps';
	protected $_table_names_plural = FALSE;
	protected $_created_column = array ("column" => 'created');
 	protected $_updated_column = array ("column" => 'updated');

 	/*protected $_has_many = array (
		'guides' => array("model" => "Guide", "foreign_key" => 'citycode'),
 		'type' => array("model" => "CityType", "foreign_key" => 'citycode'),
 		'details' => array("model" => "CityDetail", "foreign_key" => 'citycode'),
 		
	);*/

	/*public static function get_cityName($cid)
	{
		$city = new City_Model();
		$city = $city ->where('citycode',$cid)->find();
		return $city->loaded() ? $city->cityname : NULL;
	}

	public function is_aboard()
	{
		$city_array = config::item('gamerule.China_region',false,array());
		return in_array($this->parentname, $city_array) ? false : true;
	}
	
	public function is_open()
	{
		return $this->isopen == self::CITY_OPEN;
	}

	public function use_tranditional() {
		$city_array = config::item('gamerule.Tranditional_region',false,array());
		return in_array($this->parentname, $city_array) ? true : false;
	}

	public function find_all_open($limit = NULL, $offset = NULL)
	{
		return $this->where("isopen","1")->orderby("pinyin", "asc")->find_all($limit, $offset);
	}

	public function find_all_unlaunched($limit = NULL, $offset = NULL)
	{
		return $this->where("isopen","0")->orderby("pinyin", "asc")->find_all($limit, $offset);
	}

	public function to_api($brief = true)
	{
		if (!$this->loaded()) {
			return array();
		}
		if ($brief) {
			$data = auxApi::get_output($this, array('@citycode'=>'citycode','cityname','english'));
		}
		else {
			$data = auxApi::get_output($this, array(
				'@citycode'=>'citycode','cityname','english','parentname','isopen','title','@introduction'=>'Introduction',
				'location_num','official_guide_num','user_guide_num','long','lat','updated','created'));
			$data['link'][] = array("@rel"=>"self", "@href"=>apiFormat::getLink_City($this));
		}
		$data['pic'] = storage::get_storage_url($this->pic,'save');
		return $data;
	}*/

	public static function deploy() {

		$db = & Database::instance();
		try {
			$result = $db->query("
				CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Camps` (
				  `camp_id` varchar(15) NOT NULL,
				  `name` varchar(150) NOT NULL COMMENT '活动代号',
				  `title` varchar(150) NOT NULL COMMENT '活动标题',
				  `keywords` varchar(255) NOT NULL COMMENT '微博关键字',
				  `category` varchar(255) NOT NULL COMMENT '类目',
				  `db_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '建表状态',
				  `created` int(11) NOT NULL,
				  `updated` int(11) NOT NULL,
				  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '活动开启状态;0:未开启,1:已开启',
				  PRIMARY KEY (`camp_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			");
			echo  __CLASS__.' deployed.<br />';
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
			return;
		}

        return 1;

		/*$re = $db->query("SELECT * FROM `{$db->table_prefix()}Cities` ");

		if(count($re) < 1)
		{
			$needrun = true;
		}
		else
		{
			$needrun = false;
		}

		if($needrun)
		{
			$filename = Kohana::find_file ( 'vendor', 'cities', FALSE, "sql" );
			$handle = fopen($filename, "r");
			$sqlscript = fread($handle, filesize ($filename));
			$sqlscript = str_ireplace('^##*-^',$db->table_prefix(),$sqlscript);
			fclose($handle);
			$sqlarray = split(";",$sqlscript);
			foreach($sqlarray as $sql)
			{
				$sql = trim($sql);
					if(!empty($sql))
					$db->query($sql);
			}
			echo "excuete cities.sql finished";
		}
		return 1;*/
	}

	/*public static function deploy_1() {
		$db = & Database::instance();
		try {
			$result = $db->query("
					ALTER TABLE `{$db->table_prefix()}Cities` ADD INDEX `default` ( `isopen` , `pinyin` ( 20 ) )
					");
			echo  __CLASS__.' index "default" added.<br />';
			return 2;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__."::".__FUNCTION__.":{$ex->getMessage()}<br />";
			return 2;
		}
	}

	public static function deploy_2() {

		$db = & Database::instance();
		try {
			$result = $db->query("
				ALTER TABLE  `{$db->table_prefix()}Cities` CHANGE  `title`  `title` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '标题',
				CHANGE  `pic`  `pic` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '城市首页图片'
			");
			echo  __CLASS__.' add field success.<br />';
			return 3;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";

		}
	}

	public static function deploy_3() {
		$db = & Database::instance();
		$filename = Kohana::find_file ( 'vendor', 'cities_v1', FALSE, "sql" );
		$handle = fopen($filename, "r");
		$sqlscript = fread($handle, filesize ($filename));
		$sqlscript = str_ireplace('^##*-^',$db->table_prefix(),$sqlscript);
		fclose($handle);
		$sqlarray = split(";",$sqlscript);
		foreach($sqlarray as $sql)
		{
			$sql = trim($sql);
				if(!empty($sql))
				$db->query($sql);
		}
		echo "excuete cities_v1.sql finished<br/>";
		return 4;
	}

	public static function deploy_4() {
		$db = & Database::instance();
		try {
			$re = $db->query("SELECT * FROM `{$db->table_prefix()}Cities` WHERE `cityname` = '台北' ");

			if(count($re) < 1)
			{
				$result = $db->query("
					INSERT INTO `{$db->table_prefix()}Cities`(`citycode`, `cityname`, `title`, `Introduction`,`parentname`,`english`,`pinyin`,`jianpin`, `pic`, `location_num`, `official_guide_num`, `user_guide_num`, `lat`, `long`, `isopen`) 
					VALUES ('taibei','台北','','','台湾','taipei','TaiBei','TB','',0,0,0,25.093061,121.558228,0);
				");
				echo  __CLASS__.' add taibei success.<br />';
			}
			

			$re = $db->query("SELECT * FROM `{$db->table_prefix()}Cities` WHERE `cityname` = '高雄' ");

			if(count($re) < 1)
			{
				$result = $db->query("
					INSERT INTO `{$db->table_prefix()}Cities`(`citycode`, `cityname`, `title`, `Introduction`,`parentname`,`english`,`pinyin`,`jianpin`, `pic`, `location_num`, `official_guide_num`, `user_guide_num`, `lat`, `long`, `isopen`) 
					VALUES ('gaoxiong','高雄','','','台湾','Kaohsiung','GaoXiong','GX','',0,0,0,23.019076,120.662842,0);
				");
				echo  __CLASS__.' add gaoxiong success.<br />';
			}


			$result = $db->query("
				UPDATE `{$db->table_prefix()}Cities` SET `cityname` = '马尔代夫', `citycode` = 'maerdaifu', `english` = 'Maldives', `pinyin` = 'MaErDaiFu', `jianpin` = 'MEDF' WHERE `cityname` = '马累';
			");
			echo  __CLASS__.' change male to maldives success.<br />';

			$result = $db->query("
				UPDATE `{$db->table_prefix()}Cities` SET `citycode` = 'bolin', `pinyin` = 'BoLin' WHERE `cityname` = '柏林';
			");
			echo  __CLASS__.' change bailin to bolin success.<br />';
			return 5;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on add taibei  ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}

	public static function deploy_5() {
		$db = & Database::instance();
		try {
			$re = $db->query("SELECT * FROM `{$db->table_prefix()}Cities` WHERE `cityname` = '北海道' ");

			if(count($re) < 1)
			{
				$result = $db->query("
					INSERT INTO `{$db->table_prefix()}Cities`(`citycode`, `cityname`, `title`, `Introduction`,`parentname`,`english`,`pinyin`,`jianpin`, `pic`, `location_num`, `official_guide_num`, `user_guide_num`, `lat`, `long`, `isopen`) 
					VALUES ('beihaidao','北海道','','','日本','Hokkaido','BeiHaiDao','BHD','',0,0,0,43.165123,141.328125,0);
				");
				echo  __CLASS__.' add taibei success.<br />';
			}
			

			$re = $db->query("SELECT * FROM `{$db->table_prefix()}Cities` WHERE `cityname` = '芭提雅' ");

			if(count($re) < 1)
			{
				$result = $db->query("
					INSERT INTO `{$db->table_prefix()}Cities`(`citycode`, `cityname`, `title`, `Introduction`,`parentname`,`english`,`pinyin`,`jianpin`, `pic`, `location_num`, `official_guide_num`, `user_guide_num`, `lat`, `long`, `isopen`) 
					VALUES ('batiya','芭提雅','','','泰国','Pattaya','BaTiYa','BTY','',0,0,0,12.9275,100.875278,0);
				");
				echo  __CLASS__.' add gaoxiong success.<br />';
			}

			$re = $db->query("SELECT * FROM `{$db->table_prefix()}Cities` WHERE `cityname` = '嘎纳' ");

			if(count($re) < 1)
			{
				$result = $db->query("
					INSERT INTO `{$db->table_prefix()}Cities`(`citycode`, `cityname`, `title`, `Introduction`,`parentname`,`english`,`pinyin`,`jianpin`, `pic`, `location_num`, `official_guide_num`, `user_guide_num`, `lat`, `long`, `isopen`) 
					VALUES ('gana','嘎纳','','','法国','Cannes','GaNa','GN','',0,0,0,43.553333,7.022222,0);
				");
				echo  __CLASS__.' add gaoxiong success.<br />';
			}

			return 6;
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on add cities 5  ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}

    public static function deploy_6() {
        $db = & Database::instance();
        try {
            $result = $db->query("
					ALTER TABLE `{$db->table_prefix()}Cities` CHANGE `pinyin` `pinyin` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'For order convience, case sensitive'
					");
            echo  __CLASS__.' field "pinyin" changed to case sensitive.<br />';
            return 7;
        }
        catch (Kohana_Database_Exception $ex)
        {
            echo "Error occurs on deploy ".__CLASS__."::".__FUNCTION__.":{$ex->getMessage()}<br />";
        }
    }

    public static function deploy_7() {
        $db = & Database::instance();
        try {
            $re = $db->query("SELECT * FROM `{$db->table_prefix()}Cities` WHERE `cityname` = '卡迪夫' ");

            if(count($re) == 1)
            {
                $result = $db->query("
                    UPDATE `{$db->table_prefix()}Cities` SET `pinyin`='KaDiFu' WHERE `cityname` = '卡迪夫'
				");
                echo  __CLASS__.' CaDiFu\'s  pinyin changed to "KaDiFu" success.<br />';
            }
            return 8;
        }
        catch (Kohana_Database_Exception $ex)
        {
            echo "Error occurs on deploy ".__CLASS__."::".__FUNCTION__.":{$ex->getMessage()}<br />";
        }
    }*/
}