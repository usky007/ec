<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: Video.php 51 2011-07-21 04:31:30Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2010-2011 UUTUU
 */
class Video {
	private $url;
	private $video_type;
	private $video_id;
	
	private static $type = array('youku','sina','tudou','ku6','56');
	
	public static function get_info($url) {
		$site = self::is_supported($url);
		if ($site === false)
			return null;
			
		$obj = new Video($url, $site);
		return $obj->get_thumbnail();
	}

	public static function is_supported($url) {
		$url_arr = explode('.',$url);
		if(!isset($url_arr[1]) || !in_array($url_arr[1], self::$type)){
			return false;
		}
		
		return $url_arr[1];
	}
		
	public function __construct($url, $site = null){
		if (is_null($site)) {
			$site = self::is_supported($url);
		}
			
		$this->url = $url;
		$this->video_type = $site;
		
		if($this->video_type != 'sina' && isset($video_id[1][0]) ){
			$this->video_id=$video_id[1][0];
		}
		
	}
	
	public function get_thumbnail(){
		switch ($this->video_type){
			case 'youku':
				return $this->get_youku_thumbnail();
				break;
			case 'tudou':
				return $this->get_tudou_thumbnail();
				break;
			case 'sina':
				return $this->get_sina_thumbnail();
				break;
			case 'ku6':
				return $this->get_ku6_thumbnail();
				break;
			case '56':
				return $this->get_56_thumbnail();
				break;
			default:
				return null;
				break;
		}
	}
	
	private function get_youku_thumbnail(){
		if (!preg_match('/id_([a-zA-Z0-9]+)\=*\.html/', $this->url, $video_id)) {
			return null;
		}
		$video_id = $video_id[1];
		
		$contents = file_get_contents($this->url);
		$videourl = "http://player.youku.com/player.php/sid/{$video_id}/v.swf";
		$result = array('video'=>$videourl);
		
		preg_match_all("/id=\"download\" href=\"(.*)\|\">/U",$contents,$m);
		if(isset($m)&&!empty($m[1])){
			$m_arr = explode("|",$m[1][0]);
			if (isset($m_arr[8]))
				$result['pic'] = $m_arr[8];
		}	
		return $result;
	}
	
	private function get_tudou_thumbnail(){
		$type = 0;
		if (preg_match('/programs\/view\/([a-zA-Z0-9]+)/', $this->url, $video_id)) {
			$videourl = "http://www.tudou.com/v/{$video_id['1']}/v.swf";
			$type = 1;
		}
/*
		else if (preg_match('/playlist\/p\//', $this->url)) {
			$type = 2;
		}
*/
		else {
			return null;
		}
		
/*
		$contents = file_get_contents($this->url);
		if ($type == 2) {
			echo (preg_match('/lcode\s*=\s*\'([^\']+)\'/', $contents, $lcode));
			
			if (!preg_match('/lcode\s*=\s*\'([^\']+)\'/', $contents, $lcode)) {
				return null;
			}
			$lcode = $lcode[1];
			
		echo $lcode;exit;
			if (!preg_match('/defaultIid\s*=\s*([0-9]+)/', $contents, $iid)) {
				return null;
			}
			$iid = $iid[1];
			$videourl = "http://www.tudou.com/l/{$lcode}/&iid={$iid}/v.swf";
		}
*/
		$result = array('video'=>$videourl);
		
/*
		preg_match_all("/<span class=\"s_pic\">(.*)<\/span>/U",$contents,$m);
		if(isset($m)&&!empty($m[1])){
			$m_arr = explode("|",$m[1][0]);
			if (isset($m_arr[0]))
				$result['pic'] = $m_arr[0];
		}
*/
		return $result;
	}
	
	private function get_sina_thumbnail(){
		$contents = file_get_contents($this->url);
		preg_match_all("/swfOutsideUrl:'(.*?)'/i", $contents, $videourl);
		if(empty($videourl[1])) {
			return null;
		}
		else {
			$result = array('video'=>$videourl[1][0]);
		}
		
		preg_match_all("/pic: '(.*?)'/i", $contents, $m);
		if(!empty($m[1])){
			$result['pic'] = $m[1][0];
		}
		return $result;
	}
	
	private function get_ku6_thumbnail(){		
		$contents = file_get_contents($this->url);
		preg_match_all("/<span class=\"s_pic\">(.*)<\/span>/", $contents, $videourl);
		if(empty($videourl[1])) {
			return null;
		}
		else {
			$result = array('video'=>$videourl[1][0]);
		}
		
		preg_match_all("/pic: '(.*?)'/i", $contents, $m);
		if(!empty($m[1])){
			$result['pic'] = $m[1][0];
		}
		return $result;
	}
	
	private function get_56_thumbnail(){
		$contents = file_get_contents($this->url);
		preg_match_all("/var _oFlv_o = '([\S|\s]+)'/isU", $contents, $videourl);
		if(empty($videourl[1])) {
			return null;
		}
		else {
			$result = array('video'=>$videourl[1][0]);
		}
		return $result;
	}
}
?>
