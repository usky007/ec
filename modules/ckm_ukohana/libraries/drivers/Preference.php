<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Preference driver interface, offers lock interface for security reason.
 *
 * $Id: Preference.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Preference
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
interface Preference_Driver {
	/**
	 * Set a preference item.
	 */
	public function set($category, $key, $data, $lock = null);

	/**
	 * Delete a preference item.
	 */
	public function delete($category, $key, $lock = null);

	/**
	 * Get a preference item.
	 *
	 * @return string NULL if the item is not found.
	 */
	public function get($category, $key, &$lock = null);

	/**
	 * List all entries of given category. Return an obj implements Iterator
	 * and Countable interface, which could be used to fetch entries. Each entry
	 * contains the value or an array with "key", "value" and "lock" field if lock is supported.
	 *
	 * @return Page_Iterator.
	 */
	public function & entries($category, $limit = NULL, $offset = NULL);

	/**
	 * Is current implementation support lock?
	 */
	public function is_lock_supported();
}
?>