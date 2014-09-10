<?php
/**
 * Test cases for helper::url
 *
 * $Id: Url_Test.php 2030 2011-02-15 13:51:57Z xuronghua $
 *
 * @package    application
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class IDFactory_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;

	public function setup() {
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}
	
	public function GUID_test() {
		$template = '====================';
		
		$guid = MyIDFactory::GUID_impl(20, 1, $template);
		$this->assert_equal(20, strlen($guid), "Test guid with sequence 1:$guid");
		$this->assert_equal('3', preg_replace('/f+/', '', $guid), "Test guid with sequence 1:$guid");
		
		$guid = MyIDFactory::GUID_impl(20, 19455947, $template);
		$this->assert_equal(20, strlen($guid), "Test guid with random sequence(int32):$guid");
		$this->assert_equal('hzUNT', preg_replace('/f+/', '', $guid), "Test guid with random sequence:$guid");
		
		$guid = MyIDFactory::GUID_impl(20, 1944564468455947, $template);
		$this->assert_equal(20, strlen($guid), "Test guid with random sequence(int64):$guid");
		$this->assert_equal('3UWhWmHjHvT', preg_replace('/f+/', '', $guid), "Test guid with random sequence:$guid");
	}
}

class MyIDFactory extends ID_Factory {
	public static function GUID_impl($length, $id, $template) {
		return ID_Factory::_GUID_impl($length, $id, $template, true);
	}
}