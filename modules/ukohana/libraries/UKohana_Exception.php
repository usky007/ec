<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Profiler extension.
 *
 * @package    package_name
 * @author		maskxu
 * @copyright  (c) 2010 ukohana
 */

/**
 * Creates a custom exception.
 */
class UKohana_Exception extends Kohana_User_Exception {

	/**
	 * Set exception title and message.
	 *
	 * @param   string  exception title string
	 * @param   string  exception message string
	 * @param   string  custom error template
	 */
	public function __construct($code, $message)
	{
		$args = array_slice(func_get_args(), 2);

		// Fetch the error message
		$message = Kohana::lang($message, $args);

		parent::__construct(self::code($code), $message);
	}
	
	/**
	 * Override this function to customize output fields;
	 *
	 * @return array Output.
	 */
	public function output() {
		// Example implementation:
		// $output = array (
		// 		'message' => $this->getMessage(),
		//		'errcode' => $this->getCode()
		// );
		// return $output;
		return NULL;
	}
	
	public static function code($id) {
		if (!is_numeric($id)) {
			return config::item("errcode.$id", true);
		}
		return $id;
	}

	public static function exception_handler($exception, $message = NULL, $file = NULL, $line = NULL)
	{
		if ($exception instanceof UKohana_Exception &&
			!is_null(Service_View::get_default_format()))
		{
//			if (method_exists($exception, 'sendHeaders') AND ! headers_sent())
//			{
//				// Send the headers if they have not already been sent
//				$exception->sendHeaders();
//			}

			$view = new Service_View($exception);
			$view->render(TRUE);

			if ( ! Event::has_run('system.shutdown'))
			{
				// Run the shutdown even to ensure a clean exit
				Event::run('system.shutdown');
			}

			// Turn off error reporting
			error_reporting(0);
			exit;
		}
		else
		{
			Kohana::exception_handler($exception,$message,$file,$line);
		}
	}

	public static function set_handler(){
		// Set exception handler
		set_exception_handler(array('UKohana_Exception', 'exception_handler'));
	}
} // End Kohana PHP Exception