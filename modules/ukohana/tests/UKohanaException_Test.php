<?php
/**
 * Class description.
 *
 * $Id: Database_Test.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class UKohanaException_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;

	public function setup() {
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}

	public function message_test() {
		try {
			throw new UKohana_Exception('E_APP_GENERAL', 'core.generic_error');
		}
		catch (UKohana_Exception $ue) {
			$expect = '无法完成请求';
			$this->assert_equal($expect, $ue->getMessage(), 'Test message without parameter');
		}
	
		$reason = "Test Reason";
		try {
			throw new UKohana_Exception('E_APP_GENERAL', 'core.assert_failure', $reason);
		}
		catch (UKohana_Exception $ue) {
			$expect = '断言错误: ' . $reason;
			$this->assert_equal($expect, $ue->getMessage(), 'Test message with parameter');
		}
	}
}