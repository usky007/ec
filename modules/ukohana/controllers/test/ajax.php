<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Contains tests for service.
 *
 * $Id: ajax.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ukohana
 * @author     UUTUU
 * @copyright  (c) 2008-2009 UUTUU
 */
class Ajax_Controller extends ServiceController {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	function __construct() {
		parent::__construct();

		$this->set_format("json");
	}

	/**
	 * Displays a list of available tests
	 */
	function index()
	{
		$this->output["photo"] = array("@nid"=>5);
	}

	function error()
	{
		throw new UKohana_Exception(E_KOHANA, "core.generic_error");
	}
}
?>