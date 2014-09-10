<?php
/**
 * Social API helper
 *
 * $Id: config.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    socialapi
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2012 UUTUU
 */
 class social {
	/**
	 * Fetches config item for social api.
	 * socialapi.provider.key > socialapi.key(If provider matches). 
	 * key could be a single word or dot separated string(ex:A.B).
	 *
	 * @return  string
	 */
	public static function config($provider, $key, $required = true, $category = 'gateway')
	{
		$config = config::item("socialapi.$category.$provider.$key", false);
		if ($config !== NULL) {
			return $config;
		}
		
		if ($category == 'gateway') {
			if (config::item("socialapi.provider",false, NULL) == $provider) {
				return config::item("socialapi.$key", $required);
			}
			else if ($required) {
				throw new Kohana_Exception("core.misconfiguration", "$provider.$key(for socialapi)");
			}
			else {
				return NULL;		
			}
		}
		else {
			return config::item("socialapi.$category.$key", $required);
		}
	}

	public static function api_url($provider, $cls, $uri_key)
	{
		$url = social::config($provider, $uri_key, false, $cls);
		if ($url == NULL) {
			$url = $uri_key;
		}
		$url = sprintf($url, $provider);
		return (( preg_match("/^https?:\/\//", $url) > 0) ? "" : social::config($provider, 'base')).$url;
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
}
?>