<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: FeatureTimeline.php 3 2011-06-07 03:00:48Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2009-2011 UUTUU
 */
class FeatureTimeline extends Timeline implements Page_Iterator, ArrayAccess {
	
	const GET_URL = "/2/statuses/friends_timeline.json";
	
	protected $id;
	
	public function __construct(Credential $cred) {
		parent::__construct($cred);
	}
	
	protected function get_api_url($uri) {
		if ($uri === Timeline::GET_URL)
			return parent::get_api_url(self::GET_URL);
			
		return parent::get_api_url($uri);
	}
}
?>