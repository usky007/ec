<?php
/**
 * Class description.
 *
 * $Id: log.php 627 2011-11-21 10:43:51Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class log {
	const DEBUG = "debug";
	const INFO = "info";
	const WARN = "alert";
	const ERROR = "error";

	public static function debug($msg, &$output = null) {
		Kohana::log(self::DEBUG, $msg);
		self::output($output, self::DEBUG, $msg);
	}

	public static function info($msg, &$output = null) {
		Kohana::log(self::INFO, $msg);
		self::output($output, self::INFO, $msg);
	}
	
	public static function alert($msg, &$output = null) {
		return self::warn($msg, $output);
	}

	public static function warn($msg, &$output = null) {
		Kohana::log(self::WARN, $msg);
		self::output($output, self::WARN, $msg);
	}

	public static function error($msg, &$output = null) {
		Kohana::log(self::ERROR, $msg);
		self::output($output, self::ERROR, $msg);
	}
	
	protected static function output(&$output, $lvl, $msg) {
		if (is_array($output)) {
			$output[] = $lvl . ':' . $msg;
		}
	}
}