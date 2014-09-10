<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: UserTimeline.php 3 2011-06-07 03:00:48Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2009-2011 UUTUU
 */
class UserTimeline extends Timeline implements Page_Iterator, ArrayAccess {
	
	const GET_URL = "/2/statuses/user_timeline.json";
	
	protected $id;
	
	public function __construct(Credential $cred, $id, $is_name = false) {
		parent::__construct($cred);
		$this->id = array(($is_name ? "screen_name" : "user_id") => $id);
	}
	
	protected function get_api_url($uri) {
		if ($uri === Timeline::GET_URL)
			return parent::get_api_url(self::GET_URL);
			
		return parent::get_api_url($uri);
	}
	
	protected function & build_parameters() {
		$params = & parent::build_parameters();
		$params = array_merge($params, $this->id);
		return $params;
	}

}
?>