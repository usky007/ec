<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: Page_Iterator.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
interface Page_Iterator extends Iterator, Countable {
	/**
	 * Load data to iterator
	 */
	public function load_page($limit, $offset = 0);

	/**
	 * Load next page of data.
	 */
	public function next_page($limit);

	/**
	 * Total num of data.
	 */
	public function total();
}
?>