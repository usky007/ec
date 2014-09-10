<?php
/**
 * Test cases for helper::date
 *
 * $Id: Date_Test.php 2030 2011-02-15 13:51:57Z xuronghua $
 *
 * @package    common
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class Date_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;

	public function setup() {
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}

	public function today_test() {
		$now = new DateTime('now');
		$today = new DateTime($now->format('Y-m-d'));
		//$this->assert_equal($today->getTimestamp(), date::today(), "Test today for default timezone");
		
		$timezone = new DateTimeZone('America/New_York');
		$now = new DateTime('now', $timezone);
		$today = new DateTime($now->format('Y-m-d'), $timezone);
		//$this->assert_equal($today->getTimestamp(), date::today('America/New_York'), "Test today for new york");
	}
}