<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Format Helper.
 *
 * $Id: format.php 1579 2012-08-13 06:54:36Z xuronghua $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */
class format {
	//public static function get_home_url(){
	//	return url::site("main"); //edit main
	//}
	//
	const SUBSTR_LOCATION_NAME_TOP_LIST = 21;
	const SUBSTR_LOCATION_NAME = 30;
	const SUBSTR_LOCATION_ADDRESS = 45;
	const HTML_TAG_A = "a";
	const HTML_TAG_SPAN = "span";

	const UPLOAD_CLASS_ORIGINAL = '';

	public static function distance($dis)
	{
		if($dis<100)
		{
			return round($dis)." 米";
		}
		else
		{
			return round($dis/1000,2)." 公里";
		}

	}
	public static function get_default_avartar($count=4)
	{
		$rst = array();
		if($count>10)
			return false;
		for($i=0;$i<$count;$i++)
		{
			$rnd = mt_rand(1,10);
			while(in_array($rnd,$rst))
			{
				$rnd = mt_rand(1,10);
			}
			$rst[] = $rnd;
		}
		$avatars = array();
		$layout = new AppLayout_View();
		foreach($rst as $item)
		{
			$avatars[] =  $layout->resource_path("images/default_avatar/".$item.".jpg");
		}
		return $avatars;
	}

 	public static function get_breadcrumbs_location_html($citycode,$type,$name)
 	{
/*
 		$html = '<a href="/">旅行者</a>&raquo;';
 		$cityname = City_Model::get_cityName($citycode);
 		$html .= '<a href="/city/'.$citycode.'">'.$cityname.'</a>&raquo;';
 		$typename = Location::getTypeName($type);
 		$typecode = Location::getTypeCode($type);
 		$html .= '<a href="/city/type/'.$citycode.'/'.$typecode.'">'.$typename.'</a>&raquo;<span>'.$name.'</span>';
 		return $html;
*/

 		return "<br/>";
 	}

	public static function get_breadcrumbs_city_html($city,$typecode='')
 	{
 		/*
 		$html = '<a href="/">旅行者</a>&raquo;';
 		$cityname = City_Model::get_cityName($citycode);
 		if(empty($type))
 		{
 			$html .= $cityname;
 		}
 		elseif($type == 'guide'){
 			$html .= '<a href="/city/'.$citycode.'">'.$cityname.'</a>&raquo;';
 			$typename = '<a href="'.url::site("/city/guides/".$citycode).'">地图</a>'.'»';
 			$html .= $typename;
 		}
 		elseif($type == 'guides')
 		{
 			$html .= '<a href="/city/'.$citycode.'">'.$cityname.'</a>&raquo;';
 			$typename = '地图';
 			$html .= $typename;
 		}
 		else
 		{
 			$html .= '<a href="/city/'.$citycode.'">'.$cityname.'</a>&raquo;';
 			$typename = Location::getTypeName($type);
 			$html .= $typename;
 		}

 		return $html;
		*/
 		return "<br/>";
 	}
	public static function get_bread_nav($city,$typecode=null,$guidename=null)
	{
		switch ($typecode)
		{
			case 'allguides':
				$typename = '地图集';
				break;
			case 'recommend':
				$typename = '推荐地图';
				break;
			case 'guidename':
				$typename = $guidename;
				break;
			default:
				$typename = Location::getTypeName($typecode);
				break;
		}
		$name = $city->parentname == $city->cityname?$city->cityname:$city->parentname.' - '.$city->cityname;
		$homepage = url::site('/');
		$citypage = url::site('/city/'.$city->citycode);
		$del_but_1 = '<a href="'.$homepage.'"><img class="new_nav_close" align="absmiddle" src="'.url::site('/res/images/nav_close.png').'"></a>';
		$del_but_2 = '<a href="'.$citypage.'"><img class="new_nav_close" align="absmiddle" src="'.url::site('/res/images/nav_close.png').'"></a>';
		$html = '<div class="new_nav"><div class="new_nav_box1"><div class="new_nav_l1"><a href="'.$homepage.'">回到首页</a></div><div class="new_nav_l1r"></div></div>';

   	 	$html .= '<div class="new_nav2"><div class="new_nav_box2"><div class="new_nav_l2l"></div>
    	<div class="new_nav_l2"><a href="'.$citypage.'">'.$name.'</a>'.$del_but_1.'</div><div class="new_nav_l2r"></div><div class="clear"></div></div></div>';
		if(!empty($typecode))
	   		$html .= '<div class="new_nav3"><div class="new_nav_box3"><div class="new_nav_l3l"></div>
	    	<div class="new_nav_l3"><span>'.$typename.'</span>'.$del_but_2.'</div><div class="new_nav_l3r"></div><div class="clear"></div></div></div>';
		$html .= '</div>';
		return $html;
	}
 	public static function get_location_image_fragment($location, $width, $height)
	{
		$full_path = $location->full_pic_path($width, $height);
		$map = self::get_static_map_url($location->lat, $location->long, $width, $height, 14, $location->city->is_aboard());
		if (empty($full_path)) {
			return " src=\"$map\"";
		}
		else {
			$layout = new AppLayout_View();
			$default = $layout->resource_path('images/tan_nano.png');
			return " error=\"$map\" onerror=\"javascript:this.src=this.error;this.error='$default'\" src=\"$full_path\"";
		}
	}
	/*
	 *
	 */
	public static function get_static_map_url($lat, $long, $width, $height, $zoom = 14,$abroad=false,$markers=array()) {
		if($abroad)
		{
			//return  "http://maps.google.com/maps/api/staticmap?center={$lat},{$long}&zoom={$zoom}&size={$width}x{$height}&maptype=roadmap&markers=size:small|color:blue|{$lat},{$long}&sensor=false";
			//$domain = config::item('googlemap.domain.staticmap',true);
			//return "http://{$domain}/maps/api/staticmap?center={$lat},{$long}&zoom={$zoom}&size={$width}x{$height}&sensor=false";

			return self::_get_abord_static_map_url($lat, $long, $width, $height, $zoom, $markers);
		}
		else
		{
			return "http://st.map.soso.com/api?size={$width}*{$height}&center={$long},{$lat}&zoom={$zoom}";
		}
	}

	public static function get_static_map_url_bylocs($center,$locs,$abroad,$size=array(188, 88)) {
		if($abroad)
		{

			return self::_get_abord_static_map_url_bylocs($center,$locs,$size);
		}
		else
		{
			return self::_get_internal_static_map_url_bylocs($center,$locs,$size);
		}
	}


	private static function _get_abord_static_map_url($lat, $long, $width, $height, $zoom = 14, $markers=array())
	{
		$driver = $domain = config::ditem('gamerule.staticmap.driver',false,'mapquest');
		if($driver == 'mapquest')
		{
			$key = 'Fmjtd%7Cluub290a2q%2Cb0%3Do5-96zlhr';
			$zoom = $zoom ? round($zoom/21*16):false;
			$needZoom   = $zoom ? "&zoom={$zoom}" : '';
			$needCenter = $long ? "&center={$lat},{$long}" : '';
			$pic = "http://www.mapquestapi.com/staticmap/v3/getmap?key={$key}&size={$width},{$height}{$needZoom}{$needCenter}&scalebar=false";
			if(!empty($markers)){
				$pic .= '&pois=';
				foreach($markers as $marker)
				{
					$num = is_int($marker['num'])?'purple-'.$marker['num']:$marker['num'];
					$pic.= $num.','.$marker['lat'].','.$marker['long'].'|';
				}
				$pic = substr($pic,0,strlen($pic)-1);
			}
			return $pic;
		}elseif($driver == 'google')
		{
			$allowcache = config::ditem('gamerule.staticmap.cache', false, 'false');

			if($allowcache == 'true' && empty($markers))
			{
				$pic = 'map/'.'map_'.$lat.'_'.$long.'_'.$zoom.'_'.$width.'x'.$height.'.png';
				$pic = self::get_local_storage_url($pic, 'save');
				return $pic;
			}
			else
			{
				$key = config::item("googlemap.apikey", false, "");
				if (!empty($key)) {
					$key = "key=$key&";
				}
				$needZoom   = $zoom ? "&zoom={$zoom}" : '';
				$needCenter = $long ? "&center={$lat},{$long}" : '';
				$pic = 'http://'.config::item('googlemap.domain.staticmap',false,'www.google.com').
							"/maps/api/staticmap?{$key}size={$width}x{$height}{$needCenter}{$needZoom}";
				if(!empty($markers)){
					foreach($markers as $marker)
					{
						$pic.= "&markers=color:blue|label:{$marker['num']}".'|'.$marker['lat'].','.$marker['long'];
					}
				}
				$pic .= "&sensor=false";
				return $pic;
			}
		}
	}

	private static function _get_abord_static_map_url_bylocs($center,$locs,$size)
	{
		$key = config::item("googlemap.apikey", false, "");
		if (!empty($key)) {
			$key = "key=$key&";
		}
		$url = 'http://'.config::item('googlemap.domain.staticmap',false,'maps.google.com').
					"/maps/api/staticmap?{$key}" ;
 		$url .= "size=".$size[0]."x".$size[1];
		$url .= "&center=".$center[1].",".$center[0];


		if(count($locs)>0)
		{
			$mks = "";
			$i = 1;
			foreach($locs as $loc)
			{

				$mks .= "&markers=color:blue|".$loc[1].",".$loc[0];
				$i += 1;
			}
			$url .= $mks;
		}
		$url .= "&sensor=false";
		return $url;


	}

	private static function _get_internal_static_map_url_bylocs($center,$locs,$size)
	{

		$url = "http://st.map.soso.com/api?";
		$url .= "size=".$size[0]."*".$size[1];
		$url .= "&center=".$center[0].",".$center[1];

		if(count($locs)>0)
		{

			$mks = "&markers=";
			foreach($locs as $loc)
			{
				$mks .= $loc[0].",".$loc[1]."|";
			}
			$mks = substr($mks,0,strlen($mks)-1);
			$url .= $mks;
		}
		return $url;
	}

	public function create_local_storage_path($filename, $type = 'default', $res = 'map')
	{
		$path = format::get_local_storage_path($res.'/', $type);

		if(!is_dir($path))
		{
			@mkdir($path, 0777);
		}

		$args = explode('/', $filename);

		for ($i = 0; $i < count($args) - 1 ; $i++) {
			$path .= $args[$i].'/';
			if(!is_dir($path))
			{
				@mkdir($path, 0777);
			}
		}

		return $path;
	}

	public function get_local_storage_path($filename,$type='default')
	{
		if(preg_match('/^dianping\/dp_([0-9]{2})([0-9]{2})([0-9]{2})([0-9]*).(.*)/', $filename, $matches))
		{
			$filename = 'dianping/';
			$name = '';
			for ($i = 1; $i < 5 ; $i++) {
				if($matches[$i] != '')
				{
					$filename .= $matches[$i].'/';
					$name .=$matches[$i];
				}
			}
			$filename .= $name.'.'.$matches[5];
		}
		$storage_path = config::item('upload.storage_path',false,array());
		$site = isset($storage_path[$type])?$storage_path[$type]:$storage_path['default'];
		$path = preg_replace('/\/*$/', "", $site).preg_replace('/^\/*/', '/', $filename);
		//$path =  str_replace("/","\\",$path);
		return $path;
	}

	public function get_local_storage_url($filename,$type='default',$width=0, $height=0)
	{
		if(empty($filename))
			return "";

		$storage_path = config::item('upload.local_storage_base',false,array());

		$site = isset($storage_path[$type])?$storage_path[$type]:$storage_path['default'];


		if (config::item('gamerule.img_transform_service',false, true) &&  $width > 0 && $height > 0  ) {
			$mapper = config::item("gamerule.img_transform_filemapper.$type", false, null);
			if (!is_array($mapper)) {
				$mapper = array (
					"pattern" => '/\.(jpg)$/',
					"replacement" => "_%d_%d.\\1"
				);
			}
			else if (!isset($mapper['pattern']) || !isset($mapper['replacement'])) {
				throw new Kohana_Exception("core.misconfiguration", 'gamerule.img_transform_filemapper');
			}
			$mapper["replacement"] = sprintf($mapper["replacement"], $width, $height);
			$filename = preg_replace($mapper["pattern"], $mapper["replacement"], $filename);
			//return url::site("/service/image/transform/$filename?nosave=1")  ;
		}

		//$site = isset($storage_path[$type])?$storage_path[$type]:$storage_path['default'];
		return $site.$filename;
	}


	public function get_upload_address_pattern($filename, $create_ts, $type = "photo")
	{
		if (config::item('upload.advance_storage') != "on")
			return $filename;

 		$storage = new Storage();
		$uri = $storage->get_storage_uri($type);

		if (!$uri)
			return $type."/".$filename;
		else
			return preg_replace("/(.+?)\/*$/", "\\1/",  $uri).'%s'.date(config::item('upload.upload_date_pattern'), $create_ts).'/'.$filename;
	}

	public function get_upload_address($link, $class = null, $type = "photo", $fullpath=true)
	{
		$link = preg_replace("/(\?[^\?]*?)$/is", "", $link);
		$address = $link;


		if (preg_match('/^[0-9a-zA-Z]+\.[0-9a-zA-Z]+$/', $link))
		{

			// old simple link in filename format
			if (isset($class) and !empty($class))
				$address = "{$class}/{$address}";
			$old_dir = config::item('upload.old_upload_directory',false,'');

			if ($old_dir !== false and !empty($old_dir)) {
				$old_dir = sprintf($old_dir, $type);
				$address = preg_replace("/(.+?)\/*$/", "\\1/",  $old_dir).$address;
			}
		}
		else
		{

			if (!isset($class) ||  empty($class))
				$class = "";
			else
				$class .= "/";
			$address = sprintf($address, $class);
		}

		if (!$fullpath)
			return $address;

		if (isset($type))
		{
			$storage_path = config::item('upload.storage_path');


			if ($storage_path === false) {
				Kohana::log('error', "[format::get_upload_address]Missing config item:storage_path");
				$storage_path = "";
			}
			if (is_array($storage_path)) {
				$storage_path = isset($storage_path[$type]) ? $storage_path[$type] :
					(isset($storage_path['default']) ? $storage_path['default'] : false);
				if ($storage_path === false) {
					Kohana::log('error', "[format::get_upload_address]No matching or default storage_path settings:{$type}");
					$storage_path = "";
				}
			}

			return $storage_path.$address;
		}
		return $address;
	}


	function get_sinalogin_button()
	{
		return '<a href="'.url::site("/signup").'" class="button sina show-signup-button">使用新浪微博登录</a>';
	}

	function get_linklogin_button()
	{
		$html = '<form method="post" action="'.url::site("/social/authorize/sina?return=".urlencode('settings/sina')).'">';
		$html .='<input type="submit" value="接入新浪微博账号" class="button sina show-signup-button" name="connect"></form>';
		return $html;
	}


	//////////////////////////link/////////////////////////////////////

	function getLink_UserGuides($uid = "")
	{
		return url::site("user/$uid");
	}

	function getLink_Location($location)
	{
		if ($location->isofficial) {
			return url::site("location/{$location->lid}");
		}
		else {
			return "#".$location->lid;
		}
	}

	function getLink_UserGuide($guide, $more = "")
	{
		return url::site("user/guide/{$guide->gid}/$more");
	}

	function getLink_OfficialGuide($guide)
	{
		return url::site("user/guide/{$guide->gid}");
	}


	public static function get_substr($str,$len,$href = NULL, $html_tag = format::HTML_TAG_A,array $attr = NULL){
		$name = format::substring($str,$len);

		if (in_array($html_tag,array(format::HTML_TAG_A,format::HTML_TAG_SPAN))){
			$attr_str = '';
			if (!empty($attr) && is_array($attr)){
				foreach ($attr as $k=>$v){
					$attr_str = $k.'="'.$v.'" ';
				}
			}
			if (!empty($href)){
				$href = ' href="'.$href.'" ';
			}else {
				$href = ' ';
			}
			$return = '<'.$html_tag.' '.$href.' '.$attr_str.'title="'.$str.'">'.$name.'</'.$html_tag.'>';
		}else{
			//more..$html_tag
		}
		return $return;
	}


	private static function normalize_username($name,$len = 12,$end='...'){
		return self::substring($name,$len,$end);
	}

	/**
	 * formatting EN.CN string
	 *
	 * @param $str  string
	 * @param $len  length
	 *
	 * @return string
	 */
	public static function substring($str,$len, $end='...'){
		self::step_substring($str, $len, $found, $outlen, 0, $end);
		return $found;
	}

	public static function step_substring($str, $len, &$found, &$outlen, $offset=0, $end='...'){
		static $sps = array(10, 13); // '\n','enter' case
		$str = strip_tags($str);
		$ll = strlen($str); 	//total string length
		$i = $offset; 			//bit length
		$l = 0; 				//return length
		$s = $offset > 0 ? substr($str, $offset) : $str;
		$char_l = array();
		$char_s = array();
		$end_len = strlen($end);
		switch($end) {
			case "..." : $end_len = 2;
		}
		while ($i < $ll)
		{
			$byte = ord($str{$i});
			if (in_array($byte, $sps)) {
				$i++;
				$char_l[] = 0;
				$char_s[] = 1;
			}
			else if ($byte < 0x80)  	//asscii 1 char
			{
				$l++;
				$i++;
				$char_l[] = 1;
				$char_s[] = 1;
			}
			elseif ($byte < 0xe0) //2 char
			{
				$l += 2;
				$i += 2;
				$char_l[] = 2;
				$char_s[] = 2;
			}
			elseif ($byte < 0xf0) //3 char
			{
				$l += 2;
				$i += 3;
				$char_l[] = 2;
				$char_s[] = 3;
			}
			else				  //4 char
			{
				$l += 2;
				$i += 4;
				$char_l[] = 2;
				$char_s[] = 4;
			}

			if ($l >= $len)
			{
				if ($l > $len) {
					$l -= array_pop($char_l);
					$i -= array_pop($char_s);
				}
				if ($i < $ll) {
					while ($l + $end_len > $len) {
						$l -= array_pop($char_l);
						$i -= array_pop($char_s);
					}
					$s = substr($str, $offset, $i - $offset).$end;
				}
				break;
			}
			if ($byte == 13 && $i < $ll - 1 && ord($str{$i+1}) == 10) {
				// \r\n case
				continue;
			}
			if ($byte==10 || $byte== 13) {
				$s = substr($str, $offset, $i - $offset).$end;
				break;
			}
		}
		$found = $s;
		$outlen = $l;
		return $i;
	}

	public static function format_date($date)
	{
		if($date == 0 || empty($date))
		{
			return ORM_Core::get_time();
		}
		else
		{
			return $date;
		}
	}

	public static function to_jpg($filename)
	{
		$suffix = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '$2', $filename);
		$output = str_ireplace('.'.$suffix,'.jpg',$filename);

		$storage_path = config::item('upload.storage_path');

		$size = getimagesize($storage_path['save'].$filename);
		$width = $size[0];
		$height = $size[1];
		if($width > 500)
		{
			$height = $height/($width/500);
			$width = 500;
		}
		elseif ($height > 500)
		{
			$width = $width/($height/500);
			$height = 500;
		}
		$gd = new Image($storage_path['save'].$filename);
		$gd->resize($width,$height);
		$gd->save($storage_path['save'].$output);
		if($suffix != 'jpg')
			@unlink($storage_path['save'].$filename);
		//error_log($output,3,'/Users/leon/Documents/project/error/aa.txt');
		
		return $output;
	}

}
?>