<?php

/**
 * Class description.
 *
 * $Id: Storage.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */

class Storage {

	private $_obj = null;
	private $_storage_uri = array();

	/**
	 * constructor
	 */

	function get_storage_uri($app)
	{
		if (isset($this->_storage_uri[$app]))
			return $this->_storage_uri[$app];

		$storage_mod = new Storage_Model();
		$where["application"]=$app;
		$where["enable"]=1;
		$storage_table = $storage_mod->where($where)->find_all();

		$total_weight = 0;
		$weight_array = array();
		$uri = array();
		foreach( $storage_table as $storage)
		{
			$total_weight += $storage->weight;
			$weight_array[] = $total_weight;
			$uri[] = $storage->uri;
		}

		$choice = mt_rand(1, $total_weight);
		for ($i = 0; $i < count($weight_array); $i++)
		{
			if ($choice <= $weight_array[$i])
			{
				$this->_storage_uri[$app] =$uri[$i];
				return $this->_storage_uri[$app];
			}
		}
	}
}
?>