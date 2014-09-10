<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Session library.
 *
 * $Id: U_Session.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Session extends Session_Core {
	/**
	 * Singleton instance of Session.
	 */
	public static function instance($lifetime = NULL)
	{
		if (Session::$instance == NULL)
		{
			// Create a new instance
			new Session($lifetime);
		}

		return Session::$instance;
	}

	/**
	 * On first session instance creation, sets up the driver and creates session.
	 */
	public function __construct($lifetime = NULL)
	{
		$this->input = Input::instance();

		// This part only needs to be run once
		if (Session::$instance === NULL)
		{
			// Load config
			Session::$config = Kohana::config('session');

			// Makes a mirrored array, eg: foo=foo
			Session::$protect = array_combine(Session::$protect, Session::$protect);

			// Configure garbage collection
			ini_set('session.gc_probability', (int) Session::$config['gc_probability']);
			ini_set('session.gc_divisor', 100);
			ini_set('session.gc_maxlifetime', (Session::$config['expiration'] == 0) ? 86400 : Session::$config['expiration']);

			// Create a new session
			$this->create();

			if ($lifetime !== NULL) {
				$_SESSION["expires"] = $lifetime;
			}

			if (isset($_SESSION['expires'])) {
				Session::$config['expiration'] = $_SESSION["expires"];
			}

			if (Session::$config['regenerate'] > 0 AND ($_SESSION['total_hits'] % Session::$config['regenerate']) === 0)
			{
				// Regenerate session id and update session cookie
				$this->regenerate();
			}
			else
			{
				// Always update session cookie to keep the session alive
				cookie::set(Session::$config['name'], $_SESSION['session_id'], Session::$config['expiration']);
			}

			// Close the session just before sending the headers, so that
			// the session cookie(s) can be written.
			Event::add('system.send_headers', array($this, 'write_close'));

			// Make sure that sessions are closed before exiting
			register_shutdown_function(array($this, 'write_close'));

			// Singleton instance
			Session::$instance = $this;
		}

		Kohana::log('debug', 'Session Library initialized');
	}
} // End Session Class
