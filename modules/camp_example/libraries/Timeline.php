<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: Timeline.php 55 2011-07-27 11:34:50Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
abstract class Timeline extends AuthorizedObject implements Page_Iterator {
	const GET_URL = 0;
	const COUNT_URL = "/statuses/counts.json";
	
	const TYPE_AUTO = 0;
	const TYPE_TOPIC = 1;
	const TYPE_USERID = 2;
	const TYPE_USERNAME = 3;
	const TYPE_FEATURE = 4;
	const TYPE_MENTION = 5;
	const ID_FIRST = 0;
	const ID_RECENT = 0;
	
	private static $property_get_list = array("uptodate", "nomore");
	
	protected $timeline = null;
	protected $since_id = 0;
	protected $upto_id = 0;
	protected $position;
	protected $lastOffset = 0;
	
	protected $uptodate = true;
	protected $nomore = false;

	public static function get_timeline(Credential $cred, $key, $type = self::TYPE_AUTO) {
		if ($type == self::TYPE_AUTO) {
			$type = self::detect_timeline_type($key);
		}
		
		$is_name = false;
		switch ($type) {
			case self::TYPE_USERNAME:
				$is_name = true;
			case self::TYPE_USERID:
				return new UserTimeline($cred, $key, $is_name);
			case self::TYPE_TOPIC:
				return new TopicTimeline($cred, $key);
			case self::TYPE_FEATURE:
				return new FeatureTimeline($cred);
			case self::TYPE_MENTION:
				return new MentionTimeline($cred);
		}
		return NULL;
	}
	
	/**
	 * Detect timeline type by key. Key in certain format will be changed to normal form if neccessary
	 * ie: "@user" => "user", "#keyword#" => "keyword"
	 */
	public static function detect_timeline_type(&$key) {
		if (empty($key)) {
			return self::TYPE_FEATURE;
		}
	
		if (is_numeric($key)) {
			return self::TYPE_USERID;
		}

		$count = 0;

		$key = preg_replace('/^@(.+)$/', '\1', $key, -1, $count);
		if ($count > 0) {
			return self::TYPE_USERNAME;
		}

		$key = preg_replace('/^#(.+?)#?$/', '\1', $key, -1, $count);
		// default type;
		return self::TYPE_TOPIC;
	}
	
	public function __construct(Credential $cred) {
		parent::__construct($cred, "sina");
	}
	
	/**
	 * since date.
	 *
	 * @param int64 Id search starts, pass Timeline::ID_FIRST to reset.
	 * @return Timeline
	 */
	public function since($id){
		$this->since_id = $id;
		return $this;
	}
	
	/**
	 * upto date.
	 *
	 * @param int64 Id search ends, pass Timeline::ID_RECENT to reset.
	 * @return Timeline
	 */
	public function upto($id){
		$this->upto_id = $id;
		return $this;
	}
	
	/**
	 * Countable: count
	 */
	public function count() {
		if (!isset ( $this->timeline )) {
			$this->load_page(0);
		}
		return count( $this->timeline );
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
		return $val['id'];
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
	
	/**
	 * ArrayAccess: offsetExists
	 */
	public function offsetExists($offset) {
		if (!isset ( $this->timeline )) {
			$this->load_page(0);
		}
		return isset ( $this->timeline[$offset] );
	}
	
	/**
	 * ArrayAccess: offsetGet
	 */
	public function offsetGet($offset) {
		if (!isset ( $this->timeline )) {
			$this->load_page(0);
		}
		return $this->timeline[$offset];
	}
	
	/**
	 * ArrayAccess: offsetSet
	 *
	 * @throws  Kohana_Database_Exception
	 */
	public function offsetSet($offset, $value) {
		$this->offsetExists ( $offset );
		return $this->timeline[$offset] = $value;
	}
	
	/**
	 * ArrayAccess: offsetUnset
	 *
	 * @throws  Kohana_Database_Exception
	 */
	public function offsetUnset($offset) {
		if (isset ( $this->timeline [$offset] )) {
			unset ( $this->timeline [$offset] );
		}
	}
	
	/**
	 * Load data to iterator
	 */
	public function load_page($limit, $offset = 0) {
		if ($limit == 0)
			$limit = 20;
		// Get one more as guard. (no more data detection)
		$limit += 1;
			
		$this->lastOffset = $offset;
		$page = (int)($offset / $limit) + 1;
		
		$params = & $this->build_parameters();
		$parmas['page'] = $page;
		$params['count'] = $limit;
		
		$this->timeline = $this->http_get($this->get_api_url(self::GET_URL), $params);
		if (isset($this->timeline['error_code'])) {
			throw new UKohana_Exception(E_MICO_GENERAL, "errors.request_failure");
		}
		
		$this->timeline = isset($this->timeline['statuses']) ? $this->timeline['statuses'] : array();
		// no more data detection, check since_id first to contain no boundary case(upto_id == 0 and since_id == 0).
		if ($this->since_id == self::ID_FIRST) {
			$this->uptodate = $this->upto_id == self::ID_RECENT;
			if  (count($this->timeline) < $limit) {
				$this->nomore = true;
			}
			else {
				array_pop($this->timeline);
			}
		}
		else if ($this->upto_id == self::ID_RECENT) {
			if  (count($this->timeline) == $limit) {
				$this->uptodate = false;
				array_shift($this->timeline);
			}
		}
		
		// no result? process ends.
		if (count($this->timeline) == 0)
			return $this;
		
		// load counts
		$ids = array();
		foreach ($this->timeline as $status) {
			$ids[] = $status['id'];
		}
		$counts = $this->http_get($this->get_api_url(self::COUNT_URL), array("ids"=>join(",", $ids)));
		// sort
//		$ids = array();
//		foreach ($counts as $count) {
//			$ids[] = $count['id'];
//		}
		$this->quicksort($counts, false);
		
		// set count and load media info
		$buffer = array();
		$i = 0;
		foreach ($this->timeline as &$status) {
			$count = array_shift($counts);
			if ($status['id'] == $count['id']) {
				$status = array_merge($status, $count);
			}
			
			$cache_obj = Timelinecaches_Model::from_status($this->credential->provider, $status);
			
			if (!empty($cache_obj->mediaInfo)) {
				$status['media'] = $cache_obj->mediaInfo;
				$status['media']['type'] = $cache_obj->mediaType;
				$status['media']['width'] = $cache_obj->mediaWidth;
				$status['media']['height'] = $cache_obj->mediaHeight;
			}
			if (!$cache_obj->saved()) {
				$status['obj'] = $cache_obj;
				$buffer[$i++] = &$status;
			}
			else {
				$status['tcid'] = $cache_obj->tcid;
			}
		}
		// save caches
		if ($i > 0) {
			ID_Factory::prepare_ids($buffer[0]['obj'], $i);
			foreach ($buffer as &$status) {
				$status['tcid'] = $status['obj']->save()->tcid;
				unset($status['obj']);
			}
		}
		
		return $this;
	}

	/**
	 * Load next page of data.
	 */
	public function next_page($limit) {
		$this->load_page($limit, $this->lastOffset + count($this->timeline));
		return $this;
	}

	/**
	 * Total num of data.
	 */
	public function total() {
		return 0;
	}
	
	public function __get($name) {
		if (in_array($name, self::$property_get_list)) {
			if (!isset ( $this->timeline )) {
				$this->load_page(0);
			}
			return $this->$name;
		}
		return null;
	}
	
	public function __isset($name) {
		if (in_array($name, self::$property_get_list)) {
			if (!isset ( $this->timeline )) {
				$this->load_page(0);
			}
			return isset($this->$name);
		}
		return false;
	}
	
	/**
	 * 
	 */
	protected function & build_parameters() {
		$params = array();
		if ($this->since_id != self::ID_FIRST) {
			$params['since_id'] = $this->since_id;
		}
		if ($this->upto_id != self::ID_RECENT) {
			$params['max_id'] = $this->upto_id;
		}
		return $params;
	}
	
	private function quicksort(&$array, $asc, $start = 0, $end = -10) {
		if ($end == -10)
			$end = count($array) - 1;
		if ($start >= $end)
			return;
		
		$func = $asc ? "max" : "min";
		$guard = $start;
		$first = $start;
		$last = $end;
		while ($last > $first) {
			if ($guard != $last) {
				if ($func($array[$guard]['id'], $array[$last]['id']) == $array[$guard]['id']) {
					$temp = $array[$last];
					$array[$last] = $array[$guard];
					$array[$guard] = $temp;
					$guard = $last;
					$first++;
				}
				else {
					$last--;
				}
			}
			else {
				if ($func($array[$guard]['id'], $array[$first]['id']) == $array[$first]['id']) {
					$temp = $array[$first];
					$array[$first] = $array[$guard];
					$array[$guard] = $temp;
					$guard = $first;
					$last--;
				}
				else {
					$first++;
				}
			}
		}

		$this->quicksort($array, $asc, $start, $guard - 1);
		$this->quicksort($array, $asc, $guard + 1, $end);
	}
}
?>
