<?php
/**
 * This class reponsible for generating IDs for other database
 * Set 'guid_mapper' item in configuration file id.php to reset mapper for guid generation. 
 *
 * $Id: ID_Factory.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class ID_Factory {
	const DEFAULT_ID_BASE = 0;
	const GUID_SEQUENCE_KEY = "GUID";
	
	private static $map = array (
	    'b','f','3','d','a','1','w','F','k','6','j','5','H','x','y','2',
	    'W','X','7','g','P','B','T','_','Y','c','A','J','l','9','R','G',
	    'v','n','z','-','h','Q','p','E','I','s','o','L','M','4','U','r',
	    'C','O','m','t','i','0','K','D','e','S','V','q','N','8','u','Z');

	private static $id_buffer = array();

	public static function next_id($name, $base = self::DEFAULT_ID_BASE) 
	{
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

	public static function prepare_ids($name, $num = 1, $base = self::DEFAULT_ID_BASE) 
	{
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
	
	public static function GUID($length = 0)
	{
		return self::_GUID_impl($length);
	}
	
	/**
	 * For test.
	 */
	protected static function _GUID_impl($length = 0, $id = 0, $template = null, $ignore_config = false)
	{
		// avoid numeric template
		if (isset($template) && is_numeric($template)) {
			return null;
		}
		
		$mapper = $ignore_config ? self::$map : config::item('id.guid_mapper', false, self::$map);
		// reserve one bit for collapsing avoiding.
		$step = (int)log(is_string($mapper) ? strlen($mapper) : count($mapper), 2) - 1;
		$min_length = ceil(64.0 / $step);
		$length = $length < $min_length ? $min_length : $length;
	
		// generate sequence id (64bit)
		if ($id == 0) {
			$id = self::next_id(self::GUID_SEQUENCE_KEY);
			if (!is_int($id)) {
				$id = intval($id);
			}
		}
		$id_len = $id === 1 ? 1 : ceil(log($id, 2) / 5);

		while (true) {
			// validate $length and prepare guid template
			$guid = isset($template) ? $template : md5(uniqid(rand(), true));
			if ($length < strlen($guid)) {
				$guid = substr($guid, 0, $length);
			}
			else {
				$length = strlen($guid);
			}
			
			// choose mode of padding.
			// align: id segment will align to padding, which means it always shows on padding position.
			// skip: id segment will skip padding position.
			if ($length - $id_len > $id_len) {
				$align_or_skip = $id_len; // positive for align
			}
			else {
				$align_or_skip = $id_len - $length; // negative for skip
			}
			$padding = array();
			for ($i = 0; $i < abs($align_or_skip); $i++) {
				// use nonstop loops for unique check;
				while (true) {
					$idx = rand(0, $length - 1);
					if (!isset($padding[$idx])) {
						$padding[$idx] = true;
						break;
					}
				}
			}

			// every 5 bit, map to a character
			$code = $id & 0x1F;
			$id = $id >> $step;
			$skiped = 0;
			for ($i = strlen($guid) - 1; $i >= 0; $i--) {
				// skip + padding set or align + padding unset
				if ($align_or_skip < 0 === isset($padding[$i])) {
					$guid[$i] = $mapper[(intval($guid[$i], 16) << 1) + 1];
					continue;
				}
				if ($code > 0) {
					$guid[$i] = $mapper[$code << 1];
				} else {
					// token character splited here(lowest bit), no need for other measure to avoid collapsing.
					$guid[$i] = $mapper[(intval($guid[$i], 16) << 1) + 1];
				}
				$code = $id & 0x1F;
				$id = $id >> $step;
			}
			

			// ensure no numeric token returns;
			if (!is_numeric($guid)) {
				break;
			}
		}
		return $guid;
	}
}
?>