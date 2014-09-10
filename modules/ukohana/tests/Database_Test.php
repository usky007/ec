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
class Database_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;
	private $db = null;

	public function setup() {
		$this->db = & Database::instance();
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}

	public function read_timeout_test() {
		$res = false;
		try {
			$res = $this->db->query('select sleep(9)');
		}
		catch (Kohana_Database_Exception $de) {
			$this->assert_false_strict($res, $de->getMessage());
		}
	}
}