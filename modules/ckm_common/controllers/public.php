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
abstract class Public_Controller extends LayoutController {
	protected $allcities = array();

	/**
	 * Template loading and setup routine.
	 */
	public function __construct()
	{
		$this->bodypic= '' ;
		$this->bgpic ='' ;


		parent::__construct();

		$this->positions["footers"]["footer"] = "public/footer";
	}

} // End ServiceController
?>