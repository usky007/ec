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
class Lock_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;

	private $lock;

	public function setup() {
		$this->lock = new MyLock("test_lock");

		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);

		$this->assert_true($this->lock->backend instanceof Preference, "Test class of member 'backend'");
		$this->assert_equal("test_lock", $this->lock->identifier, "Test value of member 'identifier'");
		$this->assert_equal(0, $this->lock->lock, "Test value of member 'lock'");
	}

	public function acquire_test() {
		$this->lock->backend->delete($this->lock->identifier);
		$this->assert_null($this->lock->backend->get($this->lock->identifier), "Test lock record has been deleted.");

		$success = $this->lock->acquire();
		$this->assert_not_equal(0, $this->lock->lock, "Test value of member 'lock'.(lock acquired)");
		$this->assert_true_strict($success, "Test lock acquired(no lock before)");
		$this->lock->backend->get($this->lock->identifier, $actual_lock);
		$this->assert_equal($this->lock->lock, $actual_lock, "Test value of member 'lock'");
		$this->lock->lock = 0;

		$this->lock->backend->set($this->lock->identifier, Lock::LOCK_RELEASE_FLAG);
		$success = $this->lock->acquire();
		$this->assert_not_equal(0, $this->lock->lock, "Test value of member 'lock'.(lock acquired)");
		$this->assert_true_strict($success, "Test lock acquired(locked once)");
		$this->lock->lock = 0;

		$timeout = 100;
		$last_lock_timeout = ORM::get_time() - 10;
		$this->assert_true($this->lock->is_lock_expired_exposure($last_lock_timeout), "Test is_lock_expired.");

		$this->lock->backend->set($this->lock->identifier, $last_lock_timeout);
		$success = $this->lock->acquire($timeout);
		$this->assert_not_equal(0, $this->lock->lock, "Test value of member 'lock'.(lock acquired)");
		$this->assert_true_strict($success, "Test lock acquired(force lock)");
		$new_lock_timeout = $this->lock->backend->get($this->lock->identifier);
		$this->assert_not_null($new_lock_timeout, "Test timeout set(force lock)");
		$this->assert_equal(ORM::get_time() + $timeout, $new_lock_timeout, "Test timeout value(force lock)");

		$this->lock->backend->delete($this->lock->identifier);
	}

	public function release_test() {
		$this->lock->backend->delete($this->lock->identifier);
		$this->lock->acquire();

		$this->lock->release();
		$this->assert_equal(0, $this->lock->lock, "Test value of member 'lock'");
		$this->assert_equal(Lock::LOCK_RELEASE_FLAG, $this->lock->backend->get($this->lock->identifier), "Test value of db record");
	}
}

class MyLock extends Lock {

	public function __get($key) {
		return $this->$key;
	}

	public function __set($key, $val) {
		$this->$key = $val;
	}

	public function __isset($key) {
		return isset($this->$key);
	}

	public function is_lock_expired_exposure($val) {
		return $this->is_lock_expired($val);
	}
}