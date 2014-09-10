<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: MentionTimeline.php 18 2011-06-17 10:03:10Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Liaodd
 * @copyright  (c) 2009-2013 UUTUU
 */
class UserTimeline extends Timeline {
    #获取用户发布的微博
	const GET_URL = '/2/statuses/user_timeline.json';

	protected $params;

	public function __construct($user = null) {
		parent::__construct($user);
		$this->params = array();
	}

	public function get_api_url() {
		return self::GET_URL;
	}

	public function set_params($params = array())
	{
		$this->params = $params;
	}

	public function get_params()
	{
		return $this->params;
	}


}