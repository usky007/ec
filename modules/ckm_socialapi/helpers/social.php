<?php
/**
 * Social API helper
 *
 * $Id: config.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package	   socialapi
 * @author	   UUTUU Tianium
 * @copyright  (c) 2008-2012 UUTUU
 */
 class social {
 
 	const PROVIDER_INFO_KEY_NAME = 'name';
 
 	public static function provider_info($provider, $key = null)
 	{
		$info = config::item("socialapi.providers.$provider", false, NULL);
		if ($info == NULL) {
			return NULL;
		}
		
		return isset($key) ? config::subitem($info, $key, false, NULL) : NULL;
 	}
 	
	/**
	 * Fetches config item for social api.
	 * socialapi.provider.key > socialapi.key(If provider matches). 
	 * key could be a single word or dot separated string(ex:A.B).
	 *
	 * @return	 string
	 */
	public static function config($gateway, $key, $required = true, $category = 'gateway')
	{
		$single_gateway = config::item("socialapi.provider", false, NULL);

		if (is_null($single_gateway)) {
			// Multiple provider mode
			return config::full_cascade("socialapi.$category.$gateway.$key", $required);
		}
		else if ($category == 'gateway') {
			// Single provider mode, gateway configuration
			if ($single_gateway == $gateway) {
				return config::item("socialapi.$key", $required);
			}
			else if ($required) {
				throw new Kohana_Exception("core.misconfiguration", "$gateway.$key(for socialapi)");
			}
			else {
				return NULL;		
			}
		}
		else {
			return config::item("socialapi.$category.$key", $required);
		}
	}

	public static function api_url($gateway, $cls, $uri_key)
	{
		return self::api_url_impl($gateway, $cls, $uri_key, "base");
	}

	public static function auth_url($gateway, $cls, $uri_key)
	{
		return self::api_url_impl($gateway, $cls, $uri_key, "base_auth", "base");
	}

	private static function api_url_impl($gateway, $cls, $uri_key , $base, $alt_base = null)
	{
		$url = social::config($gateway, $uri_key, false, $cls);
		if ($url == NULL) {
			$url = $uri_key;
		}
		$url = sprintf($url, $gateway);

		if( preg_match("/^https?:\/\//", $url) > 0){
			return $url;

		}

		$base_url =  social::config($gateway, $base, !isset($alt_base));
		if (is_null($base_url)) {
			// If null, assert_true(isset($alt_base))
			$base_url = social::config($gateway, $alt_base, true);
		}

		return preg_replace('/^(.*?)\/*$/', '$1', $base_url).$url;
	}
	
	/**
	 * Encode comply to RFC 3986 {@link http://www.ietf.org/rfc/rfc3986.txt} 
	 */
	public static function urlencode($input) {
		if (is_array($input)) {
			return array_map(array('social', 'urlencode'), $input);
		} else if (is_scalar($input)) {
			return str_replace('+',' ',str_replace('%7E', '~', rawurlencode($input)));
		} else {
			return '';
		}
	}
	
	/**
	 * Decode comply to RFC 3986 {@link http://www.ietf.org/rfc/rfc3986.txt} 
	 */
	public static function urldecode($string) {
		return urldecode(str_replace('~', '%7E', $string));
	}

	/**
	 * Map data by specified mapper.
	 * ajouter ::usky
	 */	
	public static function ex_mapping($mapper, $data){
		if (is_null($mapper) || empty($mapper)) {
			return $data;
		}

		$new_data = array();
		foreach ($mapper as $key => $mapping_key) {
			if (isset($data[$key])) {
				if (!is_null($mapping_key)) {
					$new_data[$mapping_key] = $data[$key];
				}
				unset($data[$key]);
			}
		}
		return array_merge($new_data, $data);
	}
	//end ::usky
}
?>