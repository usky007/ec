<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * URL helper class.
 *
 * $Id: U_date.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class date extends date_Core {
	
	/**
	 * Returns time difference between two timestamps, in the format:
	 * N year, N months, N weeks, N days, N hours, N minutes, and N seconds ago
	 *
	 * @param   integer       timestamp
	 * @param   integer       timestamp, defaults to the current time
	 * @param   string        formatting string
	 * @return  string
	 */
	public static function timespan_string($time1, $time2 = NULL, $output = 'years,months,weeks,days,hours,minutes,seconds')
	{
		if (is_null($time2)) {
			$time2 = time();
		}

		$differ = $time2 - $time1;
	
		$year = date('Y', $time2);
	
		if (($year % 4) == 0 && ($year % 100) > 0) {
			//闰年
			$days = 366;
		} elseif (($year % 100) == 0 && ($year % 400) == 0) {
			//闰年
			$days = 366;
		} else {
			$days = 365;
		}
	
		if ($differ <= 60) {
			//小于1分钟
			if ($differ <= 0) {
				$differ = 1;
			}
			$format_time = sprintf('%d秒前', $differ);
		} elseif ($differ > 60 && $differ <= 60 * 60) {
			//大于1分钟小于1小时
			$min = floor($differ / 60);
			$format_time = sprintf('%d分钟前', $min);
		} elseif ($differ > 60 * 60 && $differ <= 60 * 60 * 24) {
			if (date('Y-m-d', $time2) == date('Y-m-d', $time1)) {
				//大于1小时小于当天
				$format_time = sprintf('今天 %s', date('H:i', $time1));
			} else {
				//大于1小时小于24小时
				$format_time = sprintf('%s月%s日 %s', date('n', $time1), date('j', $time1), date('H:i', $time1));
			}
		} elseif ($differ > 60 * 60 * 24 && $differ <= 60 * 60 * 24 * $days) {
			if (date('Y', $time2) == date('Y', $time1)) {
				//大于当天小于当年
				$format_time = sprintf('%s月%s日 %s', date('n', $time1), date('j', $time1), date('H:i', $time1));
			} else {
				//大于当天不是当年
				$format_time = sprintf('%s年%s月%s日 %s', date('Y', $time1), date('n', $time1), date('j', $time1), date('H:i', $time1));
			}
		} else {
			//大于今年
			$format_time = sprintf('%s年%s月%s日 %s', date('Y', $time1), date('n', $time1), date('j', $time1), date('H:i', $time1));
		}
		return $format_time;
	}
	
	public static function today($timezone = null) {
		$timezone = self::get_timezone($timezone);
		$date = new DateTime('now', $timezone);
		$ts = (int)(ORM::get_time() / 86400) * 86400 - $timezone->getOffset($date);
		return $ts;
	}
	
	private static function get_timezone($timezone = null) {
		if ($timezone instanceof DateTimeZone) {
			return $timezone;
		}
		
		$str = ($timezone === null) ? date_default_timezone_get() : (string) $timezone;
		return new DateTimeZone($str);
	}
}