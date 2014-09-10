<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Format output in normalized format. support json, xml, xslcsv so far.
 *
 * To use it, declare your controller to extend this class:
 * 1. `class Your_Controller extends ServiceController`
 * 2. Call `$this->set_format($format)` or default as json
 * 3. Call `$this->set_output($array)` or `$this->output->$key = $value`
 *
 * $Id: service.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ukohana
 * @author     Tianium
 * @copyright  (c) 2007-2010 UUTUU
 */
abstract class ServiceController extends Controller {

	// Default format
	private $format = "json";

	// Default to do auto-rendering
	protected $auto_render = TRUE;

	// Output container
	protected $output = array();

	/**
	 * Template loading and setup routine.
	 */
	public function __construct()
	{
		parent::__construct();

		// Set format, without overriding user settings
		$this->set_format();

		if ($this->auto_render == TRUE)
		{
			// Render the template immediately after the controller method
			Event::add('system.post_controller', array($this, '_render'));
		}
	}

	/**
	 * Render the loaded template.
	 */
	public function _render($return = FALSE)
	{
		// benchmark
		if (!IN_PRODUCTION) {
			$input = Input::instance();
			$benchmark = $input->get("benchmark", false);
			if ($benchmark === false) {
				$benchmark = $input->cookie("benchmark", false);
			}
			if ($benchmark == "1") {
				cookie::set("benchmark", "1");
				$profiler = new Profiler();
				$this->output["profiler"] = array("_CDATA" => $profiler->render(TRUE));
				$profiler->disable();
			}
			else if ($benchmark !== false) {
				cookie::delete("benchmark");
			}
		}

		$view = new Service_View($this->output);

		// Return rendered view if $return is TRUE
		if ($return === TRUE)
			return $view->render();

		$view->render(TRUE);
	}

	/**
	 * Set output format
	 */
	protected function set_format($format = null) {
		if (!is_null($format))
			$this->format = $format;
		Service_View::set_default_format($this->format);
	}

	/**
	 *
	 */
	protected function set_output($data, $reset = false) {
		if (is_null($data)) {
			$data = array();
		}
		elseif (is_object($data)) {
			$data = get_object_vars($data);
		}
		elseif (!is_array($data)) {
			$data = array("data"=>$data);
		}

		if ($reset) {
			$this->output = $data;
		}
		else {
			$this->output = array_merge($this->output, $data);
		}
	}
} // End ServiceController