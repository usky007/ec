<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * SmartCronController Library
 *
 * $Id: SmartCronController.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    common
 * @author     Tianium
 * @copyright  (c) 2007-2010 UUTUU
 */
abstract class SmartCronController extends ServiceController
{
	const CONFIG_PREFIX = 'cron.';
	const CONFIG_KEY_DEFAULT_BATCH_LIMIT = 'batch_limit.default';

	const CONFIG_KEY_BATCH_LIMIT = 'batch_limit';
	const CONFIG_KEY_START = 'start';
	
	const EVENT_PREFIX = "smart_cron.";
	const EVENT_ON_NEW_START = "on_new_start";
	const EVENT_ON_BATCH_END = "on_batch_end";

	const START_FROM_AUTO = 'auto';

	const END_TYPE_LOOP = 0;
	const END_TYPE_EXPECT_ET = 1;

	const INVALID_COUNT = -1;

	const STATS_KEY_PROCESSED = 'processed';
	
	protected $batch_limit_min = 10;
	protected $batch_limit_max = 10000;

	protected $operation_name = 'process';
	protected $object_name = 'record(s)';

	public function __construct()
	{
		parent::__construct();

		$this->set_format($this->get_format());
	}

	public function run($expectedTime = 10)
	{
		return $this->execute(false, self::START_FROM_AUTO, $expectedTime, false);
	}

	public function run_from($start = self::START_FROM_AUTO, $expectedTime = 10)
	{
		return $this->execute(false, $start, $expectedTime, false);
	}

	public function simulate($loopOrET = 1, $isLoop = true)
	{
		return $this->execute(true, self::START_FROM_AUTO, $loopOrET, $isLoop);
	}

	public function simulate_from($start = self::START_FROM_AUTO, $loopOrET = 1, $isLoop = true)
	{
		return $this->execute(true, $start, $loopOrET, $isLoop);
	}

	public function execute($simulate = true, $start = self::START_FROM_AUTO, $loopOrET = 1, $isLoop = true) {
		set_time_limit(0);

		$start = is_numeric($start) ? (int)$start : self::START_FROM_AUTO;
		$simulate = ($simulate === "run" || $simulate === false) ? false : true;
		$end_at = intval($loopOrET);
		$isLoop = intval($isLoop);

		$key_batch_limit = $this->config_key(self::CONFIG_KEY_BATCH_LIMIT);
		$key_start = $this->config_key(self::CONFIG_KEY_START);

		$limit = config::ditem($key_batch_limit, false,
			config::item(self::CONFIG_PREFIX.self::CONFIG_KEY_DEFAULT_BATCH_LIMIT, false, 1000));
		$begin_flag = config::item($key_start, false, 0);
		$start = ($start === self::START_FROM_AUTO) ? config::ditem($key_start, false, 0) : $start;
		if ($start === $begin_flag) {
			Event::run(self::EVENT_PREFIX.self::EVENT_ON_NEW_START);
		}
		$result = $this->execute_impl($simulate, $start, $limit, $end_at, $isLoop ? self::END_TYPE_LOOP : self::END_TYPE_EXPECT_ET);
		if ($result['rest'] <= 0) {
			// Reset start position.
			$start = config::item($key_start, false, 0);
		}
		config::ditem_set($key_start, $start);
		config::ditem_set($key_batch_limit, $limit);

		$this->set_output(array('batch' => $result));
	}

	// Customizable functions
	abstract protected function total_for_iteration($simulate, &$constraints, $range_start);

	abstract protected function iterate_once($simulate, $constraints, &$range_start, &$offset, $limit, &$output = array(), &$statistics = null);

	protected function rest_for_next_iteration($simulate, $constraints, &$offset)
	{
		return self::INVALID_COUNT;
		// Default implementation;
	}
	
	protected function & confirm_stats($stats)
	{
		return $stats;
	}

	// Overwritable functions
	protected function execute_impl($simulate, &$range_start, &$limit, $expect_end_at, $end_type)
	{
		// Initialize output variables.
		$output = array();
		$stats = array();

		// Calculate total
		$total = $rest = $this->total_for_iteration($simulate, $cons, $range_start);
		if ($total > 0) {
			log::debug("Count of {$this->object_name} confirmed: $rest {$this->object_name} to {$this->operation_name}.", $output);
		}
		else {
			log::debug("Count of {$this->object_name} confirmed: no {$this->object_name} to {$this->operation_name}.", $output);
			return array (
				'next' => $range_start,
				'@simulation' => $simulate ? 1 : 0,
				'rest' => 0,
				'statistics' => $stats,
				'logs' => array ('log' => $output)
			);
		}

		// Initialize end condition variables.
		$pos_for_end = 0;
		$start_time = microtime(true);
		$elapsed = 0;
		$target = $last_target = 1000;
		$deviant = 1;

		// Start iteration.
		$offset = 0;
		while ($rest > 0) {
			if ($expect_end_at) {
				$end = false;
				$elapsed = microtime(true) - $start_time;
				switch ($end_type) {
					case self::END_TYPE_LOOP:
						if ($pos_for_end >= $expect_end_at) {
							log::debug(ucfirst($this->operation_name)." stopped at loop $pos_for_end, as setting $expect_end_at loops", $output);
							$end = true;
						}
						break;
					case self::END_TYPE_EXPECT_ET:
						if ($elapsed > $expect_end_at) {
							log::debug(ucfirst($this->operation_name)." stopped at $elapsed sec, as setting $expect_end_at sec", $output);
							$end = true;
						}
						break;
					default:
						log::debug("Unexpected end type($end_type), ignore!", $output);
						break;
				}
				if ($end) {
					break;
				}
				$pos_for_end++;
			}

			if ($limit < $this->batch_limit_min) {
				$limit = $this->batch_limit_min;
			}
			if ($limit > $this->batch_limit_max) {
				$limit = $this->batch_limit_max;
			}

			$processed = 0;
			$statistcis = null;
			try {
				log::debug("Scanning $limit {$this->object_name}(start from $offset). status(last_run:$last_target, amplifier:$deviant, rest:$rest)", $output);
				$loop_start_time = microtime(true);
				$processed = $this->iterate_once($simulate, $cons, $range_start, $offset, $limit, $output, $statistics);
				$last_target = (microtime(true) - $loop_start_time) * 1000;
			}
			catch (Exception $e) {
				log::debug($e->getMessage(), $output);
				if (!empty($statistics) && isset($statistcis[self::STATS_KEY_PROCESSED])) {
					$processed = $statistcis[self::STATS_KEY_PROCESSED];
					// Partial complete, execution time counted.
					$last_target = (microtime(true) - $loop_start_time) * 1000;
				}
				else {
					$last_target = 2 * $target;
					log::debug("Error on scanning $limit of $rest {$this->object_name}, set next limit to half.", $output);
				}
			}
			if (!empty($statistics)) {
				foreach ($statistics as $key => $stat) {
					$stats[$key] = isset($stats[$key]) ? ($stats[$key] + $stat) : $stat;
				}
			}
			log::debug("$processed {$this->object_name} scanned.", $output);

			$rest -= $processed;
			// Try update # of left objects
			$updated_rest = $this->rest_for_next_iteration($simulate, $cons, $offset);
			if ($updated_rest !== self::INVALID_COUNT && $updated_rest != $rest) {
				$rest = $updated_rest;
				$total = $rest + $processed;
				log::debug("Count of {$this->object_name} updated: $rest (of $total) {$this->object_name} to {$this->operation_name}.", $output);
			}

			// Try Calibrate deviant according to actual # of processed.
			$calibrated = $last_target;
			if ($processed > 0 && $processed != $limit) {
				$calibrated = $last_target / $processed * $limit;
			}
			$deviant = $target / $calibrated;
			if ($deviant > 2.0) {
				// limit max amplifier.
				$deviant = 2.0;
			}
			if (abs($deviant - 1) > 0.1) {
				$limit = intval($limit * $deviant);
			}
			log::debug("Will Scanning $limit {$this->object_name} next(start from $offset). status(last_run:$last_target($calibrated), amplifier:$deviant, rest:$rest)", $output);
		}

		log::debug("Final status(last_run:$last_target, elapsed:$elapsed)", $output);
		$stats = & $this->confirm_stats($stats);
		$summary = array (
			'last' => $range_start,
			'@simulation' => $simulate ? 1 : 0,
			'total' => (int)$total,
			'rest' => $rest,
			'statistics' => $stats,
			'logs' => array ('log' => $output)
		);
		
		Event::run(self::EVENT_PREFIX.self::EVENT_ON_BATCH_END);

		return $summary;
	}

	protected function config_key($key) {
		return self::CONFIG_PREFIX.strtolower(preg_replace('/_Controller$/', '', get_class($this))).'.'.$key;
	}

	/**
	 * 根据request值决定输出XML或JSON
	 * */
	protected function get_format()
	{
		return Input::instance()->query('format', 'json');
	}
} // End SmartCronController