<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Unit_Test controller.
 *
 * $Id: unit_test.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Unit_test_Controller extends Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function index()
	{
		// Run tests and show results!
		$class = Input::instance()->get("class", array(), true);
		echo new Unit_Test($class);
		
		$profiler = new Profiler();
	}

}