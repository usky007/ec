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

	public static function debug($msg) {
		Kohana::log(self::DEBUG, $msg);
	}

	public static function info($msg) {
		Kohana::log(self::INFO, $msg);
	}

	public static function warn($msg) {
		Kohana::log(self::WARN, $msg);
	}

	public static function error($msg) {
		Kohana::log(self::ERROR, $msg);
	}
}