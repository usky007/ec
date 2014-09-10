<?php
/**
 * Configuration helper
 *
 * $Id: config.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
 define('NO_DEFAULT', "65057e55-cdac-4fa0-a284-64320eca6060");
 class config_Core {
	/**
	 * Fetches config item.
	 *
	 * @param   boolean  include the query string
	 * @return  string
	 */
	public static function item($key, $required = false, $default = NO_DEFAULT)
	{
		$val = Kohana::config($key, false, $required && $default === NO_DEFAULT);

		if (!is_null($val))
			return $val;

		if (!$required)
			return $default !== NO_DEFAULT ? $default : NULL;

		if ($default !== NO_DEFAULT) {
			Kohana::log('alert', "Detect misconfiguration: no '$key' specified.");
			return $default;
		}
		else {
			throw new Kohana_Exception("core.misconfiguration", $key);
		}
	}

	public static function subitem($root, $key, $required = false, $default = NO_DEFAULT)
	{
		assertion::is_true(is_array($root), 'Parameter $root should be an array');

		// Get the value of the key string
		$val = Kohana::key_string($root, $key);

		if (!is_null($val))
			return $val;

		if (!$required)
			return $default !== NO_DEFAULT ? $default : NULL;

		if ($default !== NO_DEFAULT) {
			Kohana::log('alert', "Detect misconfiguration: no '$key' specified.");
			return $default;
		}
		else {
			throw new Kohana_Exception("core.misconfiguration", $key);
		}
	}
	
	public static function full_cascade($key, $required = false, $default = NO_DEFAULT, $defaultKey = 'default', &$resultKey = null)
	{
		return self::cascade($key, $required, $default, $defaultKey, $resultKey, true);
	}

	public static function cascade($key, $required = false, $default = NO_DEFAULT, $defaultKey = 'default', &$resultKey = null, $merge_default = false)
	{
		$keys = explode(".", $key);
		
		$group = array_shift($keys);
		$resultKey = $group;
		
		$configs = Kohana::config($group, false, $required && $default === NO_DEFAULT);
		if (empty($keys)) {
			return $default !== NO_DEFAULT ? $default : NULL;
		}

		return self::cascade_from($configs, $keys, $required, $default, $defaultKey, $resultKey, $merge_default);
	}

	public static function cascade_from($root, $key, $required = false, $default = NO_DEFAULT, $defaultKey = 'default', &$resultKey = null, $merge_default = false)
	{
		static $NO_MATCH = "19807e55-cdac-4fa0-a284-64320eca0929";
		assertion::is_false(empty($key), 'Parameter $key not given or should not be empty.');

		$configs = &$root;
		if (is_array($key)) {
			$keys = $key;
			$key = implode(".", $key);
		}
		else {
			$keys = explode(".", $key);
		}

		while (!is_null($configs)) {
			if (!is_array($configs)) {
				return self::cascade_scalar_from($configs, $keys, $required, $configs, $defaultKey, $resultKey);
			}

//			log::debug("configs:".var_export($configs, true));
			$abbr_default_set = &$configs;
			if (isset($configs[$defaultKey])) {
				$default_set = &$configs[$defaultKey];
				if (!is_array($default_set)) {
					// scalar value, regard as implicit default.
					return self::cascade_scalar_from($configs, $keys, $required, $default_set, $defaultKey, $resultKey);
				}
			}
			
			// If segment exists? or use default segment.
			$originalKey = $resultKey;
			$segment = array_shift($keys);
			if (isset($configs[$segment])) {
				$configs =  &$configs[$segment];
				$resultKey = empty($originalKey) ? $segment : ($originalKey.'.'.$segment);
				
				// Are we done?
				if (empty($keys)) {
					// last segment, exact match
					log::debug("Return config set by $resultKey of $key.");
					return $configs;
				}
				if ($merge_default) {
					$result = self::cascade_from($configs, $keys, false, $NO_MATCH, $defaultKey, $resultKey, $merge_default);
				}
				else {
					$result = $configs;
				}
			}
			else {
				$result = $NO_MATCH;
			}
			
			if ($result !== $NO_MATCH) {
				if ($merge_default) {
					log::debug("Return config set by $resultKey of $key.");
					return $result;
				}
			}
			// Try default sets
			else if (isset($default_set)) {
				$configs = $default_set;
				$resultKey = empty($originalKey) ? $defaultKey : ($originalKey.'.'.$defaultKey);
			}
			else if (!empty($keys) && isset($abbr_default_set[$keys[0]])) {
				// dig in
				$configs = &$abbr_default_set[$keys[0]];
				$segment = array_shift($keys);
				$resultKey = empty($originalKey) ? $segment : ($originalKey.'.'.$segment);
			}
			else if (!$required) {
				$resultKey = null;
				log::debug("Return default($default) value for config key: $key.");
				return $default !== NO_DEFAULT ? $default : NULL;
			}
			else if ($default !== NO_DEFAULT) {
				$resultKey = null;
				Kohana::log('alert', "Detect misconfiguration: no '$key' specified.");
				return $default;
			}
			else {
				$resultKey = null;
				throw new Kohana_Exception("core.misconfiguration", $key);
			}
			
			// Are we done?
			if (empty($keys)) {
				// last segment, exact match
				log::debug("Return config set by $resultKey of $key.");
				return $configs;
			}
			
			if ($merge_default) {
				return self::cascade_from($configs, $keys, $required, $default, $defaultKey, $resultKey, $merge_default);
			}
			
			unset($default_set);
			// no need to unset $default_abbr
		}
		
		log::debug("Return null from configuration.($resultKey of $key).");
		return $configs;
	}

	protected static function cascade_scalar_from($root, $key, $required = false, $default = NO_DEFAULT, $defaultKey = 'default', &$resultKey = null)
	{
		assertion::is_false(empty($key), 'Parameter $key not given or should not be empty.');

		$configs = &$root;
		if (is_array($key)) {
			$keys = $key;
			$key = implode(",", $key);
		}
		else {
			$keys = explode(".", $key);
		}
		
		while (is_array($configs) && !empty($keys)) {
			if (isset($configs[$defaultKey])) {
				$default = $configs[$defaultKey];
				$lastDefaultKey = empty($resultKey) ? $defaultKey : ($resultKey.'.'.$defaultKey);
			}
			
			$segment = array_shift($keys);
			if (!isset($configs[$segment])) {
				break;
			}

			// segment matches
			$resultKey = empty($resultKey) ? $segment : ($resultKey.'.'.$segment);
			$configs = &$configs[$segment];
		}
		
		// come to scalar
		if (!is_array($configs)) {
			return $configs;
		}
		else if ($default !== NO_DEFAULT) {
			$resultKey = $lastDefaultKey;
			return $default;
		}
		else if ($required) {
			throw new Kohana_Exception("core.misconfiguration", $key);
		}
		else {
			return NULL;
		}
	}

	public static function is_set($key)
	{
		$val = Kohana::config($key, false, false);
		return !is_null($val);
	}
	
	public static function set($key, $value)
	{
		return Kohana::config_set($key, $value);
	}
	
	//Data Dictionary : application
	public static function ditem($key, $required = false, $default = NO_DEFAULT)
	{
		$result = self::ditem_pref_instance()->get(self::ditem_key($key));
		if (is_null($result)) {
			return self::item($key, $required, $default);
		}
		else if (is_numeric($result)) {
			$fval = floatval($result);
			$ival = intval($result);
			return (abs($fval - $ival) < 0.000000001) ? $ival : $fval;
		}
		else if (strlen($result) > 5) {
			// shortcut the overlong string.
			return $result;
		}
		else if (strtolower($result) == "false") {
			return false;
		}
		else if (strtolower($result) == "true") {
			return true;
		}
		else {
			return $result;
		}
	}
	
	public static function ditem_set($key, $val) {
		self::ditem_pref_instance()->set(self::ditem_key($key), $val);
	}
	
	protected static function ditem_key($key) {
		return str_replace('.', '-', $key);
	}
	
	protected static function ditem_pref_instance() {
		return Preference::instance("application");
	}
}
?>