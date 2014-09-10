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
		$val = Kohana::config($key);

		if (!is_null($val))
			return $val;

		if (!$required)
			return $default !== NO_DEFAULT ? $default : NULL;

		if ($default !== NO_DEFAULT)
		{
			Kohana::log('alert', "Detect misconfiguration: no '$key' specified.");
			return $default;
		}
		else
		{
			throw new Kohana_Exception("core.misconfiguration", $key);
		}
	}
	
	public static function cascade($key, $required = false, $defaultKey = 'default')
	{
		$keys = explode(".", $key);
		
		$lastDefault = null;
		$group = array_shift($keys);
		
		$configs = Kohana::config($group);
		if (empty($keys) && !is_null($configs)) {
			return $configs;
		}
		
		while (!is_null($configs)) {
			if (!is_array($configs)) {
				// scalar value, regard as implicit default.
				$lastDefault = $configs;
				break;
			}
			else if (isset($configs[$defaultKey])) {
				$lastDefault = $configs[$defaultKey];
			}
			
			$segment = array_shift($keys);
			if (!isset($configs[$segment])) {
				break;
			}
			else if (empty($keys)) {
				// last segment, exact match
				return $configs[$segment];
			}
			else {
				$configs = &$configs[$segment];
			}
		}
		
		// no match
		if ($required && $lastDefault === NULL) {
			throw new Kohana_Exception("core.misconfiguration", $key);
		}
		else {
			return $lastDefault;
		}
	}

	public static function is_set($key)
	{
		$val = Kohana::config($key, false, false);
		return !is_null($val);
	}
	
	public static function ditem($key, $required = false, $default = NO_DEFAULT)
	{
		$preference = Preference::instance("application");
		$pref_key = str_replace('.', '-', $key);
		
		$result = $preference->get($pref_key);
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
}
?>