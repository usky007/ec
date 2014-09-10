<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
* Object Relational Mapping (ORM) result iterator.
*
* $Id: ORM_Page_Iterator.php 329 2011-06-21 03:08:23Z zhangjyr $
*
* @package    ORM
* @author     Kohana Team
* @copyright  (c) 2007-2008 Kohana Team
* @license    http://kohanaphp.com/license.html
*/
class ORM_Page_Iterator implements Page_Iterator {
	protected $offset = 0;
	protected $callback = null;
	protected $args = null;
	public $inner_iter = null;
	protected $model = null;

	public function __construct(ORM $model, $callback, $args = null)
	{
		// Class attributes
		$this->model  = $model;
		$this->primary_key = $model->primary_key;
		$this->primary_val = $model->primary_val;

		// Database result
		if (!is_array($callback))
			$callback = array($model, $callback);
		if (is_callable($callback))
		{
			$this->callback = $callback;
			$this->args = $args;
		}
	}

	/**
	 * Countable: count
	 */
	public function count()
	{
		if (is_null($this->inner_iter))
			return 0;
		return $this->inner_iter->count();
	}

	/**
	 * Iterator: current
	 */
	public function current()
	{
		if (is_null($this->inner_iter)) {
			return FALSE;
		}

		return $this->inner_iter->current();
	}

	/**
	 * Iterator: key
	 */
	public function key()
	{
		if (is_null($this->inner_iter)) {
			return null;
		}
		return $this->inner_iter->key();
	}

	/**
	 * Iterator: next
	 */
	public function next()
	{
		if (is_null($this->inner_iter)) {
			return null;
		}
		return $this->inner_iter->next();
	}

	/**
	 * Iterator: rewind
	 */
	public function rewind()
	{
		if (!is_null($this->inner_iter)) {
			$this->inner_iter->rewind();
		}
	}

	/**
	 * Iterator: valid
	 */
	public function valid()
	{
		if (is_null($this->inner_iter)) {
			return FALSE;
		}
		return $this->inner_iter->valid();
	}


		/**
	 * Load data to iterator
	 */
	public function load_page($limit, $offset = 0) {
		if (is_null($this->callback))
			return;

		call_user_func_array($this->callback, $this->args);
		$this->inner_iter = $this->model->find_all($limit, $offset);
		$this->offset = $offset + $this->inner_iter->count();
		return TRUE;
	}

	/**
	 * Load next page of data.
	 */
	public function next_page($limit) {
		$this->load_page($limit, $this->offset);
		return $this->offset;
	}

	/**
	 * Total num of data.
	 */
	public function total() {
		if (is_null($this->callback))
			return 0;

		call_user_func_array($this->callback, $this->args);
		return $this->model->count_all();
	}
} // End ORM Iterator