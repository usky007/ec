<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Base controller for public pages
 *
 * $Id: public.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    front
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
abstract class Public_Controller extends Layout_Controller {
	// protected $allcities = array();

	/**
	 * Template loading and setup routine.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->positions["footers"]["footer"] = "public/footer";
	}

	/**
	 * Prepare for rightbar with guide infos
	 */
	// public function _prepare_footer()
	// {
	// 	$view = Event::$data["view"];

	// 	$city_mod = new City_Model();
	// 	if (!isset($this->allcities['open'])) {
	// 		$this->allcities['open'] = $city_mod->find_all_open();
	// 	}
	// 	if (!isset($this->allcities['unlaunched']) && !isset($this->allcities['unlaunchedAboard'])) {
	// 		$unlaunched = $city_mod->find_all_unlaunched();
	// 		$this->allcities['unlaunched'] = array();
	// 		$this->allcities['unlaunchedAboard'] = array();
	// 		foreach ($unlaunched as $city) {
	// 			$this->allcities[$city->is_aboard() ? 'unlaunchedAboard' : 'unlaunched'][] = $city;
	// 		}
	// 	}
	// 	$view->allcities = $this->allcities;


	// }
} // End ServiceController
?>