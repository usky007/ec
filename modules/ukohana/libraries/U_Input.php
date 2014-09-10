<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Input library.
 *
 * $Id: Input.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ukohana
 * @author     UUTUU
 * @copyright  (c) 2008-2009 UUTUU
 */

class Input extends Input_Core {
	protected $authorization_type = NULL;
	protected $authorization = NULL;
	protected $query = array();
	
	public $method = NULL;
	
	/**
	 * Retrieve a singleton instance of Input. This will always be the first
	 * created instance of this class.
	 *
	 * @return  object
	 */
	public static function instance()
	{
		if (Input::$instance === NULL)
		{
			// Create a new instance
			Input::$instance = new Input;
		}
		return Input::$instance;
	}

	/**
	 * Sanitizes global GET, POST and COOKIE data. Also takes care of
	 * magic_quotes and register_globals, if they have been enabled.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		parent::__construct();
		
		if ($this->authorization_type == NULL && isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$this->authorization_type = $this->parse_authorization_header($_SERVER['HTTP_AUTHORIZATION'], $this->authorization);
		}
		$this->query = array_merge($_GET, $_POST);
		if (is_array($this->authorization)) {
			$this->query = array_merge($this->query, $this->authorization);
		}
		
		$method_override = $this->server("HTTP_X_HTTP_METHOD_OVERRIDE", NULL);
		if (!is_null($method_override)) {
			$this->method = strtolower($method_override);
		}
		else {
			$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		}
		
		Kohana::log('debug', 'Authorization data sanitized');
	}
	
	/**
	 * Get authorization type.
	 */
	public function authorization_type()
	{
		return $this->authorization_type;
	}

	/**
	 * Fetch an item from the Authorization Header.
	 *
	 * @param   mixed   key to find, or key filter array.
	 * @param   mixed    default value, ignore if key passed as filter.
	 * @param   boolean  XSS clean the value
	 * @return  mixed	context value, or an array of filtered values.
	 */
	public function authorization($key = array(), $default = NULL, $xss_clean = FALSE)
	{
		return $this->search_array($this->authorization, $key, $default, $xss_clean);
	}
	
	/**
	 * Fetch an item from all input params, which includes AUTHORIZATION, POST and GET params.
	 *
	 * @param   mixed   key to find, or key filter array.
	 * @param   mixed    default value, ignore if key passed as filter.
	 * @param   boolean  XSS clean the value
	 * @return  mixed	context value, or an array of filtered values.
	 */
	public function query($key = array(), $default = NULL, $xss_clean = FALSE)
	{
		return $this->search_array($this->query, $key, $default, $xss_clean);
	}
	
	/**
	 * Is ssl request.
	 */
	public function is_ssl()
	{
		if (!isset($_SERVER['HTTPS'])) {
			return false;
		}
		else if (in_array($_SERVER['HTTPS'], array(1, 'on'))) {
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Fetch an item from a global array.
	 *
	 * @param   array    array to search
	 * @param   string   key to find
	 * @param   mixed    default value
	 * @param   boolean  XSS clean the value
	 * @return  mixed
	 */
	protected function search_array($array, $key, $default = NULL, $xss_clean = FALSE)
	{
		if (is_array($key) && empty($key))
		{
			return $array;
		}
		else if (is_array($key))
		{
			// filter by key
			$properties = array();
			foreach ($key as $k)
			{
				if (isset($array[$key]))
				{
					$properties[$key] = ($this->use_xss_clean === FALSE AND $xss_clean === TRUE) ?
						$this->xss_clean($array[$key]) : $array[$key];
				}
			}
			return $properties;
		}

		if ( ! isset($array[$key]))
			return $default;

		// Get the value
		$value = $array[$key];

		if ($this->use_xss_clean === FALSE AND $xss_clean === TRUE)
		{
			// XSS clean the value
			$value = $this->xss_clean($value);
		}

		return $value;
	}

	/**
	 * Parse authorization header
	 *
	 * @return array context values.
	 */
	protected function parse_authorization_header($authorization_header, &$val)
	{
		$matches = NULL;
		// Fetch type and val;
		if (!preg_match('/^(\S+)\s+(.+)$/', $authorization_header, $matches)) {
			return NULL;
		}
		
		$auth_type = $matches[1];
		$val = $matches[2];
		
		// Try to parse parameters.
		$chars = PCRE_UNICODE_PROPERTIES ? '\pL' : 'a-zA-Z';
		if (!preg_match_all('/(['.$chars.'0-9:_.-]+)=("?)([^",]*)\2/', $val, $matches, PREG_SET_ORDER)) {
			// value not in parameter format, just return;
			return $auth_type;
		}
		
		$val = array();
		foreach ($matches as $match) {
			$val[$match[1]] = $this->clean_input_data($match[3]);
		}
		return $auth_type;
	}
} // End Input Class