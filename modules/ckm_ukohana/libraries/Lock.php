<?php
/**
 * This class reponsible for generating IDs for other database
 *
 * $Id: ID_Factory.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Lock {
	const PREF_LOCK_CATEGORY = "Lock";

	const LOCK_RELEASE_FLAG = "0";

	protected $backend;
	protected $identifier;
	// Flag for lock acquisition.
	protected $lock = 0;

	public function __construct($identifier){
		$this->identifier = $identifier;
		$this->backend = Preference::instance(self::PREF_LOCK_CATEGORY);
	}	

	public function acquire($timeout = 60)
	{
		assertion::is_false($this->is_acquired(), "Can not acquire twice.");
		// To allow, uncomment code below.
		//if ($this->is_acquired()) {
		//	return true;
		//}

		$val = $this->backend->get($this->identifier, $backend_lock);
		if($val == NULL || $val == self::LOCK_RELEASE_FLAG) {
			if ($this->do_lock($timeout, $backend_lock)) {
				log::debug('lock acquired:'.$this->identifier);
				return true;
			}
		}
		else if ($val != null && $this->is_lock_expired($val)) {
			// Lock expired and try acquire lock by force;
			if ($this->do_lock($timeout, $backend_lock)) {
				log::debug('lock timeout, acquired by force:'.$this->identifier);
				return true;
			}
		}
		return false;
	}

	public function is_acquired()
	{
		return $this->lock > 0;
	}

	public function release()
	{
		if (!$this->is_acquired())
			return true;

		$result = $this->backend->set($this->identifier, self::LOCK_RELEASE_FLAG, $this->lock);
		assertion::is_true($result, "lock release operation should always be successful.");
		$this->lock = 0;
	}

	protected function do_lock($timeout, $backend_lock)
	{
		if (!$this->backend->set($this->identifier, $this->get_lock_acquire_flag($timeout), $backend_lock)) {
			return false;
		}
		// Refresh lock for later release.
		$this->backend->get($this->identifier, $this->lock);
		return true;
	}

	protected function get_lock_acquire_flag($timeout) {
		if ($timeout <= 0) {
			$timeout = 60;
		}
		return ORM::get_time() + $timeout;
	}

	protected function is_lock_expired($flag) {
		return $flag < ORM::get_time();
	}
}






