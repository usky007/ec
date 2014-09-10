<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Base controller for public pages
 *
 * $Id: blank.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    mico
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
abstract class Blank_Controller extends LayoutController {

	protected $home_active=false;
	/**
	 * Template loading and setup routine.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->positions["headers"] = "";
		$this->positions["footers"] = "";

		AppLayout_View::set_layout("layouts/blank");
 
	}
	
	public function _prepare_footer() {
		// do nothing
	}
} // End ServiceController
?>