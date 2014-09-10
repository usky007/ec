<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: Timeline.php 55 2011-07-27 11:34:50Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Liaodd
 * @copyright  (c) 2008-2010 UUTUU
 */
abstract class Timeline implements Page_Iterator {

	const GET_URL = '';

	protected $credential = null;

	protected $timeline = array();
	protected $lastOffset = 0;
	protected $postion = 0;
	protected $total = 0;
	protected $max_id = 0;

	protected $ppage = 0;

	public function __construct($user = null)
	{
		ini_set('memory_limit', '-1');
		if($user == null)
		{

			$cfg = config::ditem('activity.offical_account');
			$user = new User_Model();
			$user->where('uid', $cfg)->find();
			if(!$user->loaded)
			{
				throw new Kohana_Exception("找不到该用户", __CLASS__, __FUNCTION__);
			}
		}
        //var_dump($user);exit;
		$cm = new Credential_Model();
		$c = $cm->find_user_credentials($user);

		if(!isset($c[0]))
		{
			throw new Kohana_Exception("该用户没有授权信息", __CLASS__, __FUNCTION__);
		}

		$this->credential = new Credential('sina', $c[0]->token, true);
	}

	abstract function get_api_url();
	abstract function set_params($params = array());
	abstract function get_params();

	protected function get($url, $params = array(), $filter = null)
	{
		$aro = new AuthorizedRestObject($this->credential, $url);
		$d = $aro->get($params);
		return $d;
	}

	public function load_one_page($limit, $offset = 0, $max_id)
	{
		if($limit == 0)
			$limit = 20;

		$params['page'] = 1;
		$params['count'] = $limit;
		$params['max_id'] = $max_id;
		$params = array_merge($params, $this->get_params());

		$this->timeline = $this->get($this->get_api_url(self::GET_URL), $params); //通过api获取数据后存入$this->timeline		

		if (isset($this->timeline['error_code'])) {
			throw new UKohana_Exception(E_MICO_GENERAL, "errors.request_failure");
		}
		
		$this->timeline = isset($this->timeline['statuses']) ? $this->timeline['statuses'] : array();
		
		if(count($this->timeline) == 0)
		{
			$this->total = 0;
			return $this;
		}

		$this->total = count($this->timeline);
		return $this;
	}

	public function load_limit($limit, $max_id)
	{
		$this->ppage++;
		$this->load_one_page($limit, $this->lastOffset + count($this->timeline), $max_id);
		return $this;
	}


	public function load_page($limit, $offset = 0)
	{
		if($limit == 0)
			$limit = 20;

		//$limit += 1;

		$this->lastOffset = $offset;
		$page = (int)($offset / $limit) + 1;

		$params['page'] = $this->ppage;
		$params['count'] = $limit;
		$params['max_id'] = 0;//$this->max_id;
		$params = array_merge($params, $this->get_params());

		$this->timeline = $this->get($this->get_api_url(self::GET_URL), $params); //通过api获取数据后存入$this->timeline

		if (isset($this->timeline['error_code'])) {
			throw new UKohana_Exception(E_MICO_GENERAL, "errors.request_failure");
		}
		//$this->total = isset($this->timeline['total_number']) ? $this->timeline['total_number'] : 0;
		$this->timeline = isset($this->timeline['statuses']) ? $this->timeline['statuses'] : array();
		// echo 'tl: '.count($this->timeline).'<br/>';

		if(count($this->timeline) == 0)
		{
			$this->total = 0;
			return $this;
		}

		// if($this->timeline[0]['id'] == $this->max_id && count($this->timeline) == 1)
		// {
		// 	unset($this->timeline[0]);
		// 	$this->total = 0;
		// 	return $this;
		// }

		// $this->max_id = $this->timeline[count($this->timeline) - 1]['id'];
		// if(count($this->timeline) == $limit)
		// 	unset($this->timeline[$limit - 1]);

		$this->total = count($this->timeline);
		return $this;
	}

	public function next_page($limit)
	{
		$this->ppage++;
		$this->load_page($limit, $this->lastOffset + count($this->timeline));
		return $this;
	}

	public function total()
	{
		return $this->total;
	}

	/**
	 * Countable: count
	 */
	public function count() {
		if (!isset ( $this->timeline )) {
			$this->load_page(0);
		}
		return count( $this->timeline );
	//	return $this->total();
	}

	/**
	 * Iterator: current
	 */
	public function current() {
		if (!isset ( $this->timeline )) {
			$this->load_page(0);
		}
		return $this->timeline [$this->position];
	}
	
	/**
	 * Iterator: key
	 */
	public function key() {
		$val = $this->current();
		return $val['idstr'];
	}
	
	/**
	 * Iterator: next
	 */
	public function next() {
		++$this->position;
	}
	
	/**
	 * Iterator: rewind
	 */
	public function rewind() {
		$this->position = 0;
	}
	
	/**
	 * Iterator: valid
	 */
	public function valid() {
		if (!isset ( $this->timeline )) {
			$this->load_page(0);
		}
		return isset($this->timeline[$this->position]);
	}
}