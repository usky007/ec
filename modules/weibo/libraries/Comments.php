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
class Comments extends Timeline {

	const GET_URL = '/2/comments/show.json';

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

	public function load_page($limit, $offset = 0)
	{
		if($limit == 0)
			$limit = 20;

		$limit += 1;

		$this->lastOffset = $offset;
		$page = (int)($offset / $limit) + 1;

		$params['page'] = 1;
		$params['count'] = $limit;
		$params['max_id'] = $this->max_id;
		$params = array_merge($params, $this->get_params());

		//var_export($params);echo '<br/>';

		$this->timeline = $this->get($this->get_api_url(self::GET_URL), $params);

		if (isset($this->timeline['error_code'])) {
			throw new UKohana_Exception(E_MICO_GENERAL, "errors.request_failure");
		}
		//$this->total = isset($this->timeline['total_number']) ? $this->timeline['total_number'] : 0;
		$this->timeline = isset($this->timeline['comments']) ? $this->timeline['comments'] : array();

		if(count($this->timeline) == 0)
		{
			$this->total = 0;
			return $this;
		}

		if($this->timeline[0]['id'] == $this->max_id && count($this->timeline) == 1)
		{
			unset($this->timeline[0]);
			$this->total = 0;
			return $this;
		}

		$this->max_id = $this->timeline[count($this->timeline) - 1]['id'];
		if(count($this->timeline) == 21)
			unset($this->timeline[20]);

		$this->total = count($this->timeline);

		return $this;
	}
}