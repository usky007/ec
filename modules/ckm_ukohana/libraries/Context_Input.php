<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Context library.
 *
 * $Id: Context_Input.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ukohana
 * @author     UUTUU
 * @copyright  (c) 2008-2009 UUTUU
 */

class Context_Input extends Input {
	protected static $patterns;
	protected static $context = "";

	/**
	 * Keys passed from uri in format "key in uri => key"
	 */
	protected $context_keys = array();

	/**
	 * Keys passed from uri that cannot be inherited by calling build_uri;
	 */
	protected $dead_keys = array();

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
			Input::$instance = new Context_Input;
		}
		return Input::$instance;
	}

	/**
	 * Extract context from last segment of uri.
	 */
	public static function parse_uri()
	{
		if (self::$patterns === NULL)
		{
			// Load routes
			self::$patterns = Kohana::config('context');
		}

		if (empty(Router::$current_uri))
			return;

		$pattern = config::item('context.pattern.main', true, null);
		if (is_null($pattern))
			return;
		// Create full pattern regex.
		$pattern = '/^(' .
			str_replace('{unit}', '(?:'.config::item('context.pattern.unit', true, ".+").')', $pattern) .
			')\.('. config::item('context.pattern.postfix', true, "htm") .')$/';

		// Extract last segment of url for validating.
		$segments = explode('/', Router::$current_uri);
		$context = $segments[count($segments) - 1];
		// Validate context according to pattern settings.
        if(preg_match($pattern, $context, $matchs) == 0)
        	return;

        // Remove context from uri
        array_pop($segments);
        Router::$current_uri = join($segments, "/");
        self::$context = $matchs[1];
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

		global $_CONTEXT;
		$_CONTEXT = $this->parse_context();

		Kohana::log('debug', 'Global CONTEXT data sanitized');
	}

	/**
	 * Fetch an item from the $_CONTEXT array.
	 *
	 * @param   mixed   key to find, or key filter array.
	 * @param   mixed    default value, ignore if key passed as filter.
	 * @param   boolean  XSS clean the value
	 * @return  mixed	context value, or an array of filtered values.
	 */
	public function context($key = array(), $default = NULL, $xss_clean = FALSE)
	{
		global $_CONTEXT;
		return $this->search_array($_CONTEXT, $key, $default, $xss_clean);
	}

	/**
	 * Fetch keys originally passed from uri. Keys are in normalized form.
	 *
	 * @return array keys originally passed.
	 */
	public function get_context_keys()
	{
		return array_values($this->context_keys);
	}

	/**
	 * Build uri according to context passed.
	 *
	 * @param string uri to be prepended the context string
	 * @param array key filter, keys not in array will be ignored if not empty
	 * @param string custom file extension
	 * @return string context string
	 */
	public function build_uri($uri = "", $filter = array(), $postfix=null)
	{
		// filter keys by value
		$keys = empty($filter) ? $this->context_keys : array_intersect($this->context_keys, $filter);
		// filter dead keys by value
		$keys = array_diff($keys, $this->dead_keys);

		// mapping values
		return $this->build_custom_uri(
			array_map(array($this, 'context_mapping_callback'), $keys), $uri, $postfix);
	}

	/**
	 * Build uri according to given properties.
	 *
	 * @param array properties to process
	 * @param string uri to be prepended the context string
	 * @param string custom file extension
	 * @return string context string
	 */
	public function build_custom_uri($array, $uri = "", $postfix=null)
	{
		$uri = preg_replace("/(.+?)\/*$/", "\\1/",  $uri);

		return $uri.$this->build_uri_impl($array, $postfix);
	}

	/**
	 * Encode context value;
	 *
	 * @param string context value
	 * @return string encoded context value
	 */
	public function encode_context($context)
	{
		$context = rawurlencode($context);
		$context = str_replace(array('.', '-', '%2F', '%'), array('~2E', '~2D', '-', '~'), $context);
        return $context;
	}

	/**
	 * Decode context value
	 * @param string context value to be decoded.
	 * @return string decoded context value.
	 */
	public function decode_context($context)
	{
		$context = str_replace(array('~', '-'), array('%', '%2F'), $context);
        $context = rawurldecode($context);
        return $context;
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
	 * Implements context building process.
	 *
	 * @param array properties to process
	 * @param string custom file extension
	 * @return string context string
	 */
	protected function build_uri_impl($array, $postfix=null)
	{
		$units = array();
		$unit_template = config::item("context.template.unit", true, "");
		foreach ($array as $key => $value)
		{
			$units[] = str_replace(array('{key}', '{value}'),
				array(strtolower($key), $this->encode_context($value)),
				$unit_template);
		}

		if (empty($units))
			return "";
		else
		{
			$units = join($units, config::item("context.template.delimiter", true, ""));
			$postfix = isset($postfix) ? $postfix : config::item("context.template.postfix", true, "htm");
			return str_replace(array('{units}', '{postfix}'),
				array($units, $postfix), config::item("context.template.main", true, ""));
		}
	}

	/**
	 * Parse context string
	 *
	 * @return array context values.
	 */
	protected function parse_context()
	{
		$properties = array();
		if (!empty(self::$context) && config::is_set("context.pattern.unit"))
		{
			$result = preg_match_all('/'.config::item("context.pattern.unit").'/', self::$context, $matchs);
			for ($i = 0; $i < $result; $i++)
        	{
        		$key = strtolower($this->clean_input_keys($matchs[1][$i]));
        		$key_setting = $this->get_key_setting($key);
        		// save original keys
        		$this->context_keys[$key] = $key_setting['key'];
        		// save dead keys
        		if (!$key_setting['inherit'])
        			$this->dead_keys[$key] = $key_setting['key'];
        		// abbrs are dealed here.
        		$properties[$key_setting['key']] = $this->clean_input_data($this->decode_context($matchs[2][$i]));
        	}
		}
		// apply default values
		$key_settings = config::item('context.key_settings', false, array());
		foreach ($key_settings as $key => $value)
		{
			if (!isset($value['default']))
				continue;
			// use alias as key if defined.
			$key = isset($value['key']) ? $value['key'] : $key;
			if (!isset($properties[$key]))
				$properties[$key] = $value['default'];
		}
        // evaluate others intact.
        return $properties;
	}

	private function get_key_setting($key)
	{
		$setting = array("key" => $key, 'inherit' => true);
		return array_merge($setting,
			config::item("context.key_settings.$key", false, array()));
	}

	private function context_mapping_callback($key)
	{
		return $this->context($key);
	}
} // End Input Class
