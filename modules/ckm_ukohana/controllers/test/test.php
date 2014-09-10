<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Contains tests for advanced router.
 *
 * $Id: test.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ukohana
 * @author     UUTUU
 * @copyright  (c) 2008-2009 UUTUU
 */
class Test_Controller extends Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	/**
	 * Displays a list of available tests
	 */
	function index()
	{
		// Get the methods that are only in this class and not the parent class.
		$tests = array_diff
		(
			get_class_methods(__CLASS__),
			get_class_methods(get_parent_class($this))
		);

		sort($tests);

		echo "<strong>Tests:</strong>\n";
		echo "<ul>\n";
		echo "<li>Root Url Folding, routes to default controller:".html::anchor('index', "index")."->welcome/index</li>\n";
		echo "<li>Folder Url Folding, routes to controller with same name:".html::anchor('test', "test")."->test/test/index</li>\n";
		echo "<li>Normal Url:".html::anchor('test/general/context', "test/general/context")."->test/general/context</li>\n";

		foreach ($tests as $method)
		{
			if ($method == __FUNCTION__)
				continue;

			echo '<li>'.html::anchor('test/general/'.$method, $method)."</li>\n";
		}

		echo "</ul>\n";
		echo '<p>'.Kohana::lang('core.stats_footer')."</p>\n";
		echo url::current();
		echo phpinfo();
	}
}
?>