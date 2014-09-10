<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: assertion.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class assertion_Core {
	public static function is_true($value, $msg)
	{
		if ($value !== TRUE)
			throw new Kohana_Exception('core.assert_failure', $msg);
	}

	public static function is_false($value, $msg)
	{
		if ($value !== FALSE)
			throw new Kohana_Exception('core.assert_failure', $msg);
	}

	public static function is_equal($expected, $actual, $msg)
	{
		if ($expected != $actual)
			throw new Kohana_Exception('core.assert_failure', $msg);
	}

	public static function is_equal_strict($expected, $actual, $msg)
	{
		if ($expected !== $actual)
			throw new Kohana_Exception('core.assert_failure', $msg);
	}
}
?>