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
class ID_Factory {
	const DEFAULT_ID_BASE = 0;

	private static $id_buffer = array();

	public static function next_id($name, $base = self::DEFAULT_ID_BASE) {
		if ($name instanceof ORM) {
			$name = $name->table_name()."_".$name->primary_key();
		}
		else if (!is_string($name) || empty($name))
			throw new Kohana_Exception("core.invalid_parameter", "name", __CLASS__, __FUNCTION__);

		if (isset(self::$id_buffer[$name]) && self::$id_buffer[$name]["id"] <= self::$id_buffer[$name]["max"]) {
			$seq = self::$id_buffer[$name]["id"];
			if (++self::$id_buffer[$name]["id"] > self::$id_buffer[$name]["max"]) {
				unset(self::$id_buffer[$name]);
			}
			return $seq;
		}

		$seq = new Sequence_Model($name, $base == self::DEFAULT_ID_BASE);
		if ($base != self::DEFAULT_ID_BASE) {
			$seq->next($name, 0, 1, $base);
		}
		return $seq->id;
	}

	public static function prepare_ids($name, $num = 1, $base = self::DEFAULT_ID_BASE) {
		if ($num < 1)
			return;

		if ($name instanceof ORM) {
			$name = $name->table_name()."_".$name->primary_key();
		}
		else if (!is_string($name) || empty($name))
			throw new Kohana_Exception("core.invalid_parameter", "name", __CLASS__, __FUNCTION__);

		$seq = new Sequence_Model($name, false);
		$seq->next($name, 0, $num, $base);
		self::$id_buffer[$name] = array("id" => $seq->id, "max" => $seq->id + $num - 1);
	}
}
?>