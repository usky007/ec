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
class Url_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;

	public function setup() {
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}

	public function is_trusted_test() {
		$expected_origin = "http://example.uutuu.com";
		$config = array($expected_origin);
		
		$result_origin = null;
		$this->assert_true(url::is_trusted("", $result_origin, $config), "Test site url('').");
		$this->assert_null($result_origin, "Test origin of site url('').");
		
		$this->assert_true(url::is_trusted("m", $result_origin, $config), "Test site url(path).");
		$this->assert_null($result_origin, "Test origin of site url(path).");
		
		$this->assert_true(url::is_trusted("/m", $result_origin, $config), "Test site url(/path).");
		$this->assert_null($result_origin, "Test origin of site url(/path).");
		
		$result_origin = null;
		$this->assert_true(url::is_trusted("http://example.uutuu.com", $result_origin, $config), "Test trusted url(scheme://host).");
		$this->assert_equal($expected_origin, $result_origin, "Test origin of trusted url(scheme://host).");
		
		$expected_origin = "http://example.virus.com";
		$result_origin = null;
		$this->assert_false(url::is_trusted("http://example.virus.com", $result_origin, $config), "Test untrusted url(scheme://host).");
		$this->assert_equal($expected_origin, $result_origin, "Test origin of untrusted url(scheme://host).");
	}
	
	public function trusted_test() {
		$expected_origin = "http://example.uutuu.com";
		$config = array($expected_origin);
		
		$this->assert_equal($expected_origin, url::trusted("http://example.uutuu.com", FALSE, $config), "Test trusted url(scheme://host).");
	}
}