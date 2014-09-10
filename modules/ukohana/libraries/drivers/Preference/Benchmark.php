<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: Benchmark.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Preference_Benchmark_Driver implements Preference_Driver {

	// Cache backend object and flags
	protected static $log = NULL;
	protected $driver = NULL;

	public static function profile() {
		$profiler = &Profiler::instance();
		if ( ! $table = $profiler->table('pref'))
			return;

		$table->add_column();
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_row(array('Preference Operation', 'Key', 'Time'), 'kp-title', 'background-color: #E0FFE0');

		text::alternate();
		$total_time = 0;
		foreach (self::$log as $log)
		{
			$data = array($log['op'], $log['key'], number_format($log['time'], 3));
			$total_time += $log['time'];
			$class = text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
		}

		$data = array("", "", number_format($total_time, 3));
		$table->add_row($data, 'kp-totalrow');
	}

	public function __construct(Preference_Driver $driver = null)
	{
		if (!is_null($driver) && get_class($driver) != get_class($this))
			$this->driver = $driver;

		//profile
		if (self::$log == NULL)
		{
			self::$log = array();
			Event::add('profiler.run', array("Preference_Benchmark_Driver", 'profile'));
		}
	}

	public function get($category, $key, &$lock = null)
	{
		return $this->benchmark(null, "get", $category, $key, $lock);
	}

	public function set($category, $key, $data, $lock = null)
	{
		return $this->benchmark(null, "set", $category, $key, $data, $lock);
	}

	public function delete($category, $key, $lock = null)
	{
		return $this->benchmark(null, "delete", $category, $key, $lock);
	}

	public function & entries($category, $limit = NULL, $offset = NULL)
	{
		$result = $this->benchmark("list", "entries", $category, $limit, $offset);
		return $result;
	}

	/**
	 * If specified feature is supported.
	 */
	public function is_lock_supported() {
		return $this->driver->is_lock_supported();
	}

	protected function benchmark($message, $op, $id = null)
	{
		$message = empty($message) ? $op : $message;
		$id = is_null($id) ? "" : $id;
		$args = func_get_args();

		$start = microtime(TRUE);
		switch(count($args))
		{
			case 2: $result = $this->driver->$op(); break;
			case 3: $result = $this->driver->$op($args[2]); break;
			case 4: $result = $this->driver->$op($args[2], $args[3]); break;
			case 5: $result = $this->driver->$op($args[2], $args[3], $args[4]); break;
			case 6: $result = $this->driver->$op($args[2], $args[3], $args[4], $args[5]); break;
			default:
				$args = array_slice($args, 2);
				$result = call_user_func_array(array($this->driver, $op), $args);
				break;
		}
		$stop = microtime(TRUE);

		// benchmark
		self::$log[] = array("op"=>$op, "key"=>$id, "time"=>$stop - $start);
		return $result;
	}
} // End Cache Script Driver
?>