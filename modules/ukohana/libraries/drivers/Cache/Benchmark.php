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
class Cache_Benchmark_Driver extends Cache_Abstract_Driver {

	// Cache backend object and flags
	protected static $log = NULL;
	protected $driver = NULL;

	public static function profile() {
		$profiler = &Profiler::instance();
		if ( ! $table = $profiler->table('cache'))
			return;

		$table->add_column();
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_row(array('Cache Operation', 'Key', 'Get', 'Hit', 'Time'), 'kp-title', 'background-color: #E0FFE0');

		text::alternate();
		$get_count = 0;
		$hit_count = 0;
		$total_time = 0;
		foreach (self::$log as $log)
		{
			$data = array($log['op'], $log['key'], $log['get'], $log['hit'], number_format($log['time'], 3));
			if ($log['op'] == 'get')
			{
				$get_count += $log['get'];
				$hit_count += $log['hit'];
			}
			$total_time += $log['time'];
			$class = text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
		}

		$data = array('Hit rate: ' . (($get_count == 0) ? "-" : (round($hit_count * 100 / $get_count, 2)."%")),
			"", "Gets: ".$get_count, "Hits: ".$hit_count, number_format($total_time, 3));
		$table->add_row($data, 'kp-totalrow');
	}

	public function __construct(Cache_Driver $driver = null)
	{
		if (!is_null($driver) && get_class($driver) != get_class($this))
			$this->driver = $driver;

		//profile
		if (self::$log == NULL)
		{
			self::$log = array();
			Event::add('profiler.run', array("Cache_Benchmark_Driver", 'profile'));
		}
	}

	public function find($tag)
	{
		return $this->benchmark("get tag", "find", $tag);
	}

	public function get($id)
	{
		return $this->benchmark(null, "get", $id);
	}

	public function set($id, $data, array $tags = NULL, $lifetime)
	{
		return $this->benchmark(null, "set", $id, $data, $tags, $lifetime);
	}

	public function delete($id, $tag = FALSE)
	{
		$op = "delete";
		if ($id === TRUE)
			$op .= " all";
		else if ($tag)
			$op .= " tag";
		return $this->benchmark($op, "delete", $id, $tag);
	}

	public function delete_expired()
	{
		return $this->benchmark("delete expired", "delete_expired");
	}

	/**
	 * If specified feature is supported.
	 */
	public function is_supported($feature) {
		return $this->driver->is_supported($feature);
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

		// count hit;
		$hit = 0;
		$get = 0;
		switch ($op)
		{
			case "get":
				$get = 1;
				if (is_array($id))
				{
					$get = count($id);
					$hit = count($result);
				}
				else if (!is_null($result))
				{
					$hit = 1;
				}
				break;
			case "find":
				$hit = count($result);

		}

		// benchmark
		self::$log[] = array("op"=>$op, "key"=>$id, "time"=>$stop - $start,"hit"=>$hit, "get"=>$get);
		return $result;
	}
} // End Cache Script Driver
?>