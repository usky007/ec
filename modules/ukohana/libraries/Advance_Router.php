<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Advance Router
 *
 * Implements following router rules:
 * Url folding
 * 	On failure to search controller file, regard file as default controller by rules as
 *  site root: default controller.
 *  other: file the same filename with folder. eg. "test.php" in folder "test".
 *  eg.
 * 	index -> welcome/index, test -> test/test/index, test/func1 -> test/test/func1
 *  Assuming "welcome.php" is site's default controller. File "index.php" is not in project,
 *  Folder "test" and file "test/test.php" exists.
 *
 * $Id: Advance_Router.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ukohana
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */

class Advance_Router extends Router {
	/**
	 * Router setup routine. Automatically called during Kohana setup process.
	 *
	 * @return  void
	 */
	public static function setup()
	{
		if (Router::$controller !== NULL)
			return;

		// Prepare to find the controller
		$controller_path = '';
		$method_segment  = NULL;

		// Paths to search
		$paths = Kohana::include_paths();

		$last_segment = isset(Router::$routes['_default']) ? Router::$routes['_default'] : "";
		foreach (Router::$rsegments as $key => $segment)
		{
			// Add the segment to the search path
			$base_path = $controller_path;
			$controller_path .= $segment;

			if (self::validate_segment($controller_path, $segment) === FALSE)
			{
				// check url folding.
				if (self::validate_segment($base_path.$last_segment, $last_segment) === FALSE)
				{
					// Maximum depth has been reached, stop searching
					break;
				}
				else
				{
					// Url folding detected, controller found. Set the method segment.
					$method_segment = $key;
					break;
				}
			}

			if (Router::$controller !== NULL)
			{
				// Controller found. Set the method segment.
				$method_segment = $key + 1;
				break;
			}

			// Add another slash
			$controller_path .= '/';
			$last_segment = $segment;
		}

		// no controller found. last chance to check url folding.
		if (Router::$controller === NULL && $controller_path == Router::$routed_uri."/")
		{
			self::validate_segment($controller_path.$segment, $segment);
			// no need to set the method segment anymore if detected.
		}

		if ($method_segment !== NULL AND isset(Router::$rsegments[$method_segment]))
		{
			// Set method
			Router::$method = Router::$rsegments[$method_segment];

			if (isset(Router::$rsegments[$method_segment + 1]))
			{
				// Set arguments
				Router::$arguments = array_slice(Router::$rsegments, $method_segment + 1);
			}
		}
	}

	private static function validate_segment($controller_path, $segment) {
		// Paths to search
		$paths = Kohana::include_paths();

		$found = FALSE;
		foreach ($paths as $dir)
		{
			// Search within controllers only
			$dir .= 'controllers/';
			if (is_dir($dir.$controller_path) OR is_file($dir.$controller_path.EXT))
			{
				// Valid path
				$found = TRUE;

				// The controller must be a file that exists with the search path
				if ($c = str_replace('\\', '/', realpath($dir.$controller_path.EXT))
				    AND is_file($c) AND strpos($c, $dir) === 0)
				{
					// Set controller name
					Router::$controller = $segment;

					// Change controller path
					Router::$controller_path = $c;

					// Stop searching
					break;
				}
			}
		}

		return $found;
	}
} // End Router
?>