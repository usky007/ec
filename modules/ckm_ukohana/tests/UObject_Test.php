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
class UObject_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;

	public function setup() {
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}
	
	public function factory_effeciency_test() {
		$iteration = 1000000;
		$test_msg = array();
		
		$start = microtime(true);
		for ($i = 0; $i < $iteration; $i++) {
			$inst = new MyUObject();
		}
		$excutetime = microtime(true) - $start;
		$test_msg[] = "$excutetime(SC)"; // static creation.
		
		$cls = 'MyUObject';
		$start = microtime(true);
		for ($i = 0; $i < $iteration; $i++) {
			$inst = UObject::factory($cls);
		}
		$excutetime = microtime(true) - $start;
		$test_msg[] = "$excutetime(UObject factory)";
				
		$start = microtime(true);
		for ($i = 0; $i < $iteration; $i++) {
			$inst = new $cls();
		}
		$excutetime = microtime(true) - $start;
		$test_msg[] = "$excutetime(CV)"; // creation with variable
		
		$start = microtime(true);
		for ($i = 0; $i < $iteration; $i++) {
			$ref = new ReflectionClass($cls);
			$inst = $ref->newInstance();
		}
		$excutetime = microtime(true) - $start;
		$test_msg[] = "$excutetime(CR)"; // creation with reflection class
		
		$start = microtime(true);
		for ($i = 0; $i < $iteration; $i++) {
			$ref = new ReflectionClass($cls);
			$inst = $ref->newInstance(0);
		}
		$excutetime = microtime(true) - $start;
		$test_msg[] = "$excutetime(CRAL)"; // creation with reflection class use argument list.
		
		$args = array(0);
		$start = microtime(true);
		for ($i = 0; $i < $iteration; $i++) {
			$ref = new ReflectionClass($cls);
			$inst = $ref->newInstanceArgs($args);
		}
		$excutetime = microtime(true) - $start;
		$test_msg[] = "$excutetime(CRAA)"; // creation with reflection class use argument array.
		
		$this->assert_true_strict(false, implode(",", $test_msg));
	}
}

class MyUObject extends UObject {
	public function __construct($id = 0) {
		
	}
}