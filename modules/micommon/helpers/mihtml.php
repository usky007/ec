<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Mico html helper.
 *
 *
 * $Id: mihtml.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class mihtml {
	/**
	 * API format compatible hyperlink formater
	 *
	 * support argument format:
	 * text
	 * array(text)
	 * array(text, "@link"=>link)
	 */
	public static function hyperlink($block, $mark = NULL) {
		if (!is_array($block))
			return $block;

		if (!isset($block["@link"]))
			return $block[0];

		$mark = ($mark == NULL) ? "" : "mark=\"$mark\"";
		return "<a href=\"{$block['@link']}\" $mark>{$block[0]}</a>";
	}
}
?>