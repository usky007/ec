<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Authenticate Method driver interface.
 *
 * $Id: AuthMethod.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    AuthMethod
 * @author     UUTUU Tianium
 * @copyright  (c) 2009-2012 UUTUU
 */

define('AUTH_STEP_ONE', 1);
define('AUTH_STEP_TWO', 2);

define('AUTH_VALIDATE_TOKEN', 10);

interface AuthMethod_Driver {
	/**
	 * Authenticate step by step.
	 *
	 * @param int 	$step     Step constant define in driver classes, if only one step needed, this one can be omitted.
	 * @param array $params   Parameter array.
	 */
	public function authenticate($step, $params = NULL);

	/**
	 * Sign request, append authentication info and forward;
	 *
	 * @param CurlRequest 	$request 	Http request without authentication info.
	 * @param Credential	$credential	Credential object with valid token.
	 *
	 * @return response in raw format.
	 */
	public function send(CurlRequest $request, Credential $credential);
	
	/**
	 * Verify request 
	 */
	public function verify($application, $token, CurlRequest $request = NULL);
}

class Auth_Exception extends UKohana_Exception {
	
};
?>