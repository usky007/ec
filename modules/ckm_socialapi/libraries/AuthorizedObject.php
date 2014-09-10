<?php
/**
 * Class description.
 *
 * $Id: AuthorizedObject.php 2658 2011-06-23 06:53:18Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
abstract class AuthorizedObject implements ArrayAccess {
	protected $credential = null;
	protected $gateway = null;
	protected $last_call = array();
	protected $last_status_code = 0;

	public $timeout = 10;
	public $connecttimeout = 10;
	
	protected static $log = NULL;
	public static function profile() {
		$profiler = &Profiler::instance();
		if ( ! $table = $profiler->table('AuthorizedObject Remote API Call'))
			return;

		$table->add_column();
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_row(array('AuthorizedObject API Call URL', 'Method', 'Status Code', 'Time'), 'kp-title', 'background-color: #E0FFE0');

		text::alternate();
		$total_time = 0;
		foreach (self::$log as $log)
		{
			$data = array($log['url'], $log['method'], $log['code'], number_format($log['time'], 3));
			$total_time += $log['time'];
			$class = text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
		}

		$data = array("Total:".count(self::$log), "", "", number_format($total_time, 3));
		$table->add_row($data, 'kp-totalrow');
	}
	
	public static function factory(Credential $cred, $gateway, $base)
	{
		if ($base == NULL) {
			throw new Kohana_Exception('core.method_unimplement', __CLASS__, "factory(with base null)");
		}
		$class_name = self::class_by_credential($cred, $base);
		return new $class_name($cred, $gateway);
	}
	
	private static $class_mapper = array();
	protected static function class_by_credential(Credential $cred, $base)
	{
		if (!isset(self::$class_mapper[$base][$cred->provider])) {
			$driver_name = ucfirst($cred->provider);
			$class_name = "{$base}_{$driver_name}_Driver";
			if ( ! Kohana::auto_load($class_name)) {
				$class_name = $base;
			}
			self::$class_mapper[$base][$cred->provider] = $class_name;
		}
		return self::$class_mapper[$base][$cred->provider];
	}

	protected function __construct(Credential $cred, $gateway = NULL) {
		if (is_null($cred) || !$cred->is_authorized()) {
			throw new Kohana_Exception("core.invalid_parameter", "cred", __CLASS__, __FUNCTION__);
		}

		$this->credential = $cred;
		$this->gateway = !is_null($gateway) ? $gateway : 
			config::item("socialapi.providers.{$cred->provider}.gateway", false, $cred->provider);
		
		//profile
		if (!IN_PRODUCTION && self::$log == NULL)
		{
			self::$log = array();
			Event::add('profiler.run', array("AuthorizedObject", 'profile'));
		}
	}
	
	/**
	 * ArrayAccess Methods
	 */
	protected $_object = array();
	
	protected function load($data) {
		$this->_object = $data;
		return $this;
	}
	
	public function loaded() {
		return !empty($this->_object);
	}
	
	public function offsetExists($offset) {
		$front = &$this->getOffsetRoot($offset, $lastIdx);
		return !is_null($front) && isset($front[$lastIdx]);
	}
	
    public function offsetGet($offset) {
	    $front = &$this->getOffsetRoot($offset, $lastIdx);
		if (is_null($front) || !isset($front[$lastIdx])) {
			return null;
		}
		return $front[$lastIdx];
    }
    
    public function offsetSet($offset, $value) {
	    $front = &$this->_object;
	    $segments = &$this->parseOffset($offset);
	    $lastIdx = array_shift($segments);
		foreach ($segments as $idx) {
			if (!isset($front[$lastIdx])) {
				$front[$lastIdx] = array();
			}
			$front = &$front[$lastIdx];
			$lastIdx = $idx;
		}
		$front[$lastIdx] = $value;
    }
    
    public function offsetUnset($offset) {
	    $front = &$this->getOffsetRoot($offset, $lastIdx);
		if (!is_null($front) && isset($front[$lastIdx])) {
			unset($front[$lastIdx]);
		}
    }
    
    protected function & getOffsetRoot($offset, &$lastIdx) {
	    $front = &$this->_object;
	    $segments = &$this->parseOffset($offset);
	    $lastIdx = array_pop($segments);
		foreach ($segments as $idx) {
			if (!isset($front[$idx])) {
				$front = null;
				break;
			}
			else {
				$front = &$front[$idx];
			}
		}
		return $front;
    }
    
    protected function & parseOffset($offset) {
	    if (is_int($offset)) {
		    return array($offset);
	    }
	 
	    preg_match_all('/(?:^|\.)([0-9a-zA-Z_]+)|\[([0-9]+)\]/', (string)$offset, $matches, PREG_SET_ORDER);
	    $segments = array();
	    foreach ($matches as $match) {
	    	if (empty($match[1])) {
			    $segments[] = intval($match[2]);
		    }
		    else {
			    $segments[] = $match[1];
		    }
	    }
	    assertion::is_false(empty($segments), "Failed to parse offset in ". __CLASS__);
	    return $segments;
    }
    
    // End of ArrayAccess Methods

	/**
	 * Get http return code, defined in RFC2616, of last http call.
	 */
	public function last_status_code() {
		return $this->last_status_code;
	}

	/**
	 * Get settings of last http call.
	 *
	 * @return array Array with key "method"(string), "url"(string), "parameters"(array) and "multipart"(bool);
	 */
	public function last_http_call() {
		return $this->last_call;
	}

	protected function http_get($uri, $parameters = array()) {
		return $this->invoke("GET", $uri, $parameters);
	}

	protected function http_post($uri, $parameters = array() , $multi = false) {
		return $this->invoke("POST", $uri, $parameters, $multi);
	}

	protected function http_put($uri, $parameters = array() , $multi = false) {
		return $this->invoke("PUT", $uri, $parameters, $multi);
	}

	protected function http_delete($uri, $parameters = array()) {
		return $this->invoke("DELETE", $uri, $parameters);
	}

	protected function invoke($method, $uri, $parameters = array(), $multi = false) {
		$url = $this->get_api_url($uri);
		$this->denormalized_params($uri, $parameters);
		$this->last_call['method'] = $method;
		$this->last_call['url'] = $url;
		$this->last_call['parameters'] = $parameters;
		$this->last_call['multipart'] = $multi;
		
		$request = new CurlRequest($url, $method, $parameters , $multi);
		$request->connecttimeout = $this->connecttimeout;
		$request->timeout = $this->timeout;
		
		$start = microtime(TRUE);
		$response = $this->credential->auth_method($this->gateway)->send($request, $this->credential);
		$stop = microtime(TRUE);
		if (!IN_PRODUCTION) {
			self::$log[] = array(
				"method"=>$method,
				"url"=>$url.($multi ? "(multipart)" : ""),
				"code"=>$request->http_code,
				"time"=>$stop - $start);
		}
		
		$this->last_status_code = $request->http_code;
		if ($response === false) {
			Kohana::log("error", "Authenticated call failed: $method $url [$multi], returned:({$this->last_status_code})".var_export($request->http_info, true));
			throw new UKohana_Exception('E_SOCIAL_REQUEST', "errors.request_failure");
		}
        else if (!IN_PRODUCTION) {
			Kohana::log("debug", "Authenticated call: $method $url [$multi] ".
				var_export($parameters, true).var_export($response, true));
		}
		try {
			return $this->on_invoke_return($uri, $response, $request->format);
		}
		catch (Exception $e) {
			Kohana::log("error", "Error response on calling: $method $url [$multi] ".
					var_export($parameters, true).var_export($response, true));
			throw $e;
		}
	}

	protected function on_invoke_return($uri, $response, $format='json') {
		// sina compatibe error
		if ($format === 'json') {
        	$return = json_decode($response, true);
        	$this->normalized_result($uri, $return);
        	if (is_null($return) || isset($return['error_code'])) {
				$error_code = isset($return['error_code']) ? $return['error_code'] : 0;
				$error = isset($return['error']) ? $return['error'] : "0:unknown error";
				throw new AuthorizedCall_Exception($error, $error_code);
	       	}
        	return $return;
        }
        return $response;
	}

	protected function get_api_url($uri) 
	{
		return social::api_url($this->gateway, $this->get_api_category(), $uri);
	}
	
	protected function get_api_category()
	{
		return get_class($this);
	}
	
	protected function denormalized_params($uri, &$params) 
	{
		if (empty($params)) {
			return $params;
		}
		$params_map = social::config($this->gateway, $this->get_api_category().'.'.$uri, false, "params_mapper");
		$this->apply_global_mapper($params_map, true);
		$params = social::ex_mapping($params_map, $params);
		return $params;
	}
	
	protected function normalized_result($uri, &$result)
	{
		if ($result === NULL || empty($result)) {
			return $result;
		}
		
		// Map error fields first.
		$error_map = social::config($this->gateway, 'error', false, "result_mapper");
		$result = social::ex_mapping($error_map, $result);
		// Shortcut error result.
		if (isset($result['error']) || isset($result['error_code'])) {
			return $result;
		}
		
		// Do field mapping.
		$result_map = social::config($this->gateway, $this->get_api_category().'.'.$uri, false, "result_mapper");
		$this->apply_global_mapper($result_map, false);
		$result = social::ex_mapping($result_map, $result);
		return $result;
	}
	
	private function apply_global_mapper(&$params, $flip = false)
	{
		if (!isset($this->global_mapper)) {
			$this->global_mapper = config::item('socialapi.global_mapper.'.$this->gateway, false, array());
		}
		if (!empty($this->global_mapper)) {
			if (is_null($params)) {
				$params = $flip ? array_flip($this->global_mapper) : $this->global_mapper;
			}
			else {
				$params += $flip ? array_flip($this->global_mapper) : $this->global_mapper;
			}
		}
	}
}

class AuthorizedCall_Exception extends UKohana_Exception {
	protected $api_error_code;
	protected $api_message;
	protected $api_error_detail;

	public function __construct($msg, $code) {
		parent::__construct('E_SOCIAL_REQUEST', "errors.request_failure");
		$this->api_error_code = $code;
		$this->api_message = $msg;
		$detail = explode(":", $msg);
		$this->api_error_detail = $detail[0];
	}

	public function getOriginalCode() {
		return $this->api_error_code;
	}

	public function getOriginalMessage() {
		return $this->api_message;
	}

	public function getDetail() {
		return $this->api_error_detail;
	}
}
?>