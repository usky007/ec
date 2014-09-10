<?php
/**
 * Test cases for helper::miurl
 *
 * $Id: Miurl_Test.php 2030 2011-02-15 13:51:57Z xuronghua $
 *
 * @package    common
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

	public function build_test() {
		$expected = '/m/login/redirect/sina?override_cred_check=1&return=m';
		$this->assert_equal($expected, url::build(
			'/m/login/redirect?override_cred_check=1',
			array(
				"path" => 'sina',
				"query" => array("return" => "m")
			)), "Test case 1");

		$expected = '/m/login/redirect/sina?return=m';
		$this->assert_equal($expected, url::build(
			'/m/login/redirect',
			array(
				"path" => 'sina',
				"query" => array("return" => "m")
			)), "Test case 1");
	}
}
