<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: MentionTimeline.php 18 2011-06-17 10:03:10Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2009-2011 UUTUU
 */
class MentionTimeline extends Timeline implements Page_Iterator, ArrayAccess {
	
	const GET_URL = "/2/statuses/mentions.json";
	const RESET_URL = "/2/statuses/reset_count.json";
	
	const RESET_TYPE = 2;
	
	protected $id;
	
	public function __construct(Credential $cred) {
		parent::__construct($cred);
	}
	
	public function reset() {
		$this->http_post(self::RESET_URL, array("type"=>self::RESET_TYPE));
		return true;
	}
	
	protected function get_api_url($uri) {
		if ($uri === Timeline::GET_URL)
			return parent::get_api_url(self::GET_URL);
			
		return parent::get_api_url($uri);
	}
}
?>