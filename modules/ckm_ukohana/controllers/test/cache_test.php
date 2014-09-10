<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: cache_test.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Cache_Test_Controller extends Controller {
	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	/**
	 * Displays a list of available tests
	 */
	public function index()
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

		foreach ($tests as $method)
		{
			if ($method == __FUNCTION__)
				continue;

			echo '<li>'.html::anchor('test/cache_test/'.$method, $method)."</li>\n";
		}

		echo "</ul>\n";
		echo '<p>'.Kohana::lang('core.stats_footer')."</p>\n";
	}

	public function set() {
		$cache = & Cache::instance("testCategory");
		$len = 50;
		for ($i = 0; $i < $len; $i++){
            $cache->set("testkey$i", "testval$i", "testtag");
            echo $cache->get("testkey$i"),'<Br/>';
        }

		//echo $cache->get("testkey".($len - 1));
		$profiler = new Profiler;
	}

	public function get() {
		$cache = & Cache::instance("testCategory");
		$len = 20;
		Kohana::log("debug", "Controller start get");
		for ($i = 0; $i < $len; $i++) {
			var_dump($cache->get("testkey$i"));
		}
		Kohana::log("debug", "Controller end get");
	    $profiler = new Profiler;
	}

	public function gettag() {
		$cache = & Cache::instance("testCategory");
		var_dump($cache->find("testtag"));
		$profiler = new Profiler;
	}

	public function clear() {
		Cache::category_manager("testCategory")->clear();
		$profiler = new Profiler;
	}

	public function allcat() {
		echo "categories:<br/>";
		foreach(Cache::categories() as $key => $value)
		{
			echo "$key:$value->value<br/>";
		}
		$profiler = new Profiler;
	}

	public function gc() {
		$cache = & Cache::instance("testCategory");
		$cache->get_driver()->delete_expired();
		$profiler = new Profiler;
	}
}
?>