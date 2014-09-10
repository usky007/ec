<?php
/**
 * Class description.
 *
 * $Id: Dictionary.php 626 2011-11-21 10:43:35Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Preference_Dictionary_Driver implements Preference_Driver {

	private $backend = NULL;
	private $manual_update = false;
	
	public function __construct($params = null)
	{
		if (is_array($params) && isset($params['manual_update'])) {
			$this->manual_update = $params['manual_update'];
		}
	}

	public function set($category, $key, $data, $lock = null)
	{
		$entry = $this->get_backend();

		$key = "$category:$key";
		$entry->find($key, null, true);
		if (!is_null($lock) && $entry->loaded() && $entry->timestamp != $lock)
			return FALSE;

		if (!$entry->loaded()) {
			try {
				$entry->dic_id = $key;
				$entry->category = $category;
				$entry->auto = $this->manual_update ? 0 : 1;
				$entry->val = $data;
				$entry->timestamp = ORM::get_time();
				$entry->save();
				return true;
			}
			catch (Kohana_Database_Exception $kde) {
				// be saved already? out of sync! simply ignore this update.
				return false;
			}
		}
		else {
			return $entry->safe_update($data, $lock);
		}
	}

	public function delete($category, $key, $lock = null)
	{
		$entry = $this->get_backend();
		if ($key !== TRUE)
		{
			// validate lock
			$entry->find("$category:$key", null, true);
			if (!isset($entry->dic_id))
			{
				return TRUE;
			}
			else if (!is_null($lock) && isset($entry->timestamp) && $entry->timestamp > $lock)
			{
				return FALSE;
			}
			else
			{
				$entry->delete();
			}
		}
		else
		{
			$entry->select_category($category);
			$entry->delete_all();
		}

		return TRUE;
	}

	public function get($category, $key, &$lock = null)
	{
		$entry = $this->get_backend();
		$entry->find("$category:$key");
		if (!$entry->loaded())
			return NULL;

		$lock = $entry->timestamp;
		return $entry->val;
	}

	public function & entries($category, $limit = NULL, $offset = NULL)
	{
		$model = $this->get_backend();
		$iterator = new Preference_Dictionary_Page_Iterator($model, "select_category", array($category));
		$iterator->load_page($limit, $offset);
		return $iterator;
	}

	public function is_lock_supported()
	{
		return true;
	}

	public function set_backend(Dicentry_Model &$backend)
	{
		$this->backend = $backend;
	}

	public function get_backend()
	{
		if (is_null($this->backend))
			$this->backend = new Dicentry_Model();
		$this->backend->clear();
		return $this->backend;
	}
}

class Preference_Dictionary_Page_Iterator extends ORM_Page_Iterator {
	public function __construct(ORM $model, $callback, $args)
	{
		parent::__construct($model, $callback, $args);
	}

	public function current()
	{
		$cur = parent::current();
		return (object)array("key"=>substr($cur->dic_id, strlen($cur->category) + 1),
			"value"=>$cur->val, "lock"=>$cur->timestamp);
	}

	public function key()
	{
		$cur = parent::current();
		return substr($cur->dic_id, strlen($cur->category) + 1);
	}
}
?>