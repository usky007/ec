<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Mico url helper.
 *
 *
 * $Id: miurl.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    micommon
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class miurl {
	/**
	 * Subsite url formatter. This function will ensure the validity for both url in main site and subsite.
	 * eg. [surfix]request_path in main site = request_path in subsite.
	 *
	 * @param string surfix
	 * @param string url
	 * @return string auto detected url;
	 */
	public static function subsite($surfix, $uri) {
		$request_uri = URI::instance()->string();
		
		$surfix = preg_replace('/^(.*?)\/?$/', '\1', $surfix);
		if (strpos($uri, $surfix) === 0) {
			$uri = substr($uri, strlen($surfix));
		}
		
		$uri = preg_replace('/^\/?(.*)$/', '\1', $uri);
		if (strpos($request_uri, $surfix) === 0) {
			return url::site("$surfix/$uri");
		}
		else {
			return url::site($uri);
		}
	}
}
?>