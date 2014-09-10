<?php
/**
 * This class reponsible for generating IDs for other database
 *
 * $Id: ID_Factory.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class UObject {
	public static function factory($cls, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null) {
		switch (func_num_args()) {
			case 1: return new $cls();
			case 2: return new $cls($arg1);
			case 3: return new $cls($arg1, $arg2);
			case 4: return new $cls($arg1, $arg2, $arg3);
			case 5: return new $cls($arg1, $arg2, $arg3, $arg4);
			default:
				$cls = new ReflectionClass($cls);
				$args = func_get_args();
				array_shift($args);
				return $cls->newInstanceArgs($args);
		}
	}

	public function __get($prop) {
		$func = array($this, "get_$prop");
		if (is_callable($func)) {
			return call_user_func($func);
		}
		else
		{
			throw new Kohana_Exception('core.invalid_property', $prop, get_class($this));
		}
	}
	
	public function __set($prop, $val) {
		$func = array($this, "set_$prop");
		if (is_callable($func)) {
			return call_user_func_array($func, array($val));
		}
		else
		{
			throw new Kohana_Exception('core.invalid_property', $prop, get_class($this));
		}
	}
	
	/**
	 * Checks if object data is set.
	 *
	 * @param   string  column name
	 * @return  boolean
	 */
	public function __isset($prop)
	{
		$func = array($this, "get_$prop");
		return is_callable($func) && call_user_func($func) != null;
	}
}