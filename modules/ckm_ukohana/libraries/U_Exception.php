<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Profiler extension.
 *
 * @package    package_name
 * @author		maskxu
 * @copyright  (c) 2010 ukohana
 */
class U_Exception extends Exception {
	private static $instance = NULL;

	public static function &instance()
	{
		if (self::$instance == NULL)
			self::$instance = new U_Exception();
		return self::$instance;
	}

	/**
	 * Set exception message.
	 *
	 * @param  string  i18n language key for the message
	 * @param  array   addition line parameters
	 */
	public function __construct($error,$code=0)
	{
		$error_msg = is_array($error)? $error[0]:$error;
		$args =  is_array($error)? $error[1] :null;
		$message = Kohana::lang($error_msg,$args);

		if ($message === $error OR empty($message))
		{
			// Unable to locate the message for the error
			$message = $error_msg;
		}
		//exit;
		// Sets $this->message the proper way
		parent::__construct($message,$code);
	}
}
?>