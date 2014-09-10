<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Profiler extension.
 *
 * $Id: U_Profiler.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Profiler extends Profiler_Core {
	private static $instance = NULL;

	public static function &instance()
	{
		if (self::$instance == NULL)
			self::$instance = new Profiler();
		return self::$instance;
	}

	public function __construct()
	{
		// compatible with old new expression
		self::$instance = $this;
		parent::__construct();
	}

	/**
	 * Database query benchmarks.
	 *
	 * @return  void
	 */
	public function database()
	{
		if ( ! $table = $this->table('database'))
			return;

		$table->add_column();
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		// By Tianium: Add Column Ticks
		$table->add_column('kp-column kp-data');
		$table->add_row(array('Queries', 'Time', 'Rows', 'Tick'), 'kp-title', 'background-color: #E0FFE0');

		$queries = Database::$benchmarks;

		text::alternate();
		$total_time = $total_rows = 0;
		foreach ($queries as $query)
		{
			$data = array($query['query'], number_format($query['time'], 3), $query['rows'], $query['tick']);
			$class = text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
			$total_time += $query['time'];
			$total_rows += $query['rows'];
		}

		$data = array('Total: ' . count($queries), number_format($total_time, 3), $total_rows, "");
		$table->add_row($data, 'kp-totalrow');
	}
}
?>