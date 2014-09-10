<?php
/**
 * Test set for helpers/config.
 *
 * $Id: Config_Test.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ukohana
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class Config_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;

	public function setup() {
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}
	
	public function ditem_test() {
		$key = "gamerule.test";
	
		$preference = Preference::instance("application");
		$pref_key = str_replace('.', '-', $key);
		
		$preference->delete($pref_key);
		
		$this->assert_same(config::item($key, false), config::ditem($key, false), "Test configuration in config files.");
		
		$preference->set($pref_key, '5.01');
		$this->assert_same(5.01, config::ditem($key, true), "Test config as float.");
		
		$preference->set($pref_key, '9.0');
		$this->assert_same(9, config::ditem($key, true), "Test config as float like integer.");
		
		$preference->set($pref_key, '10.0');
		$this->assert_same(10, config::ditem($key, true), "Test config as float like integer.");
		
		$preference->set($pref_key, '1.0.3');
		$this->assert_equal('1.0.3', config::ditem($key, true), "Test config as string(short, float like).");
		
		$preference->set($pref_key, "I'm a string.");
		$this->assert_equal("I'm a string.", config::ditem($key, true), "Test config as string(longer).");

		$preference->set($pref_key, 'false');
		$this->assert_false_strict(config::ditem($key, true), "Test config as boolean(all lower).");
		
		$preference->set($pref_key, 'trUe');
		$this->assert_true_strict(config::ditem($key, true), "Test config as boolean(some random upper).");
	}
}