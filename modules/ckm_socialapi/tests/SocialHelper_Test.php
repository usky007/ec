<?php
/**
 * Test set for helpers/social.
 *
 * $Id: Config_Test.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ukohana
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class SocialHelper_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;

	public function setup() {
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}
	
	public function config_test() {	
		$this->assert_same(
			config::item("socialapi.Credential.access_token"),
			social::config('sina', 'access_token', false, 'Credential'),
			'Test access_token url of sina');
	}

	public function auth_api_test() {
		$this->assert_same(
			config::item('socialapi.gateway.sina.base').config::item("socialapi.Credential.access_token"),
			social::auth_url('sina', 'Credential', 'access_token'),
			'Test access_token url of sina');
	}
}