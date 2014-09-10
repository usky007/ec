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

	public function cascade_from_test() {
		$config_set = array(
			"sina" => array('unbind' => true, "name" => "新浪微博", "account" => 1623165616, 'bindable' => false, 'registerable' => array(
					'default' => true
				)
			),
			"weixin" => array(
				'default' => array ('unbind' => true, "name" => "微信", "account" => "wx732f14cdb13c5c1a", 'bindable' => true),
				'snsapi_base' => array ('unbind' => true, "name" => "微信", "account" => "wx732f14cdb13c5c1a", 'bindable' => false, 'registerable' => false)
			)
		);

		// Normal Cases
		$this->assert_false_strict(config::cascade_from($config_set, 'weixin.snsapi_base.bindable', false, true, 'default', $result_key), "Test config with specified sub key.");
		$this->assert_equal('weixin.snsapi_base.bindable',$result_key, "Test result key of config with specified sub key.");
		unset($result_key);
		
		$result = config::cascade_from($config_set, 'weixin.snsapi_base.undefine_key', false, null, 'default', $result_key);
		$this->assert_null($result_key, "Test result key of config with unspecified sub key.");
		$this->assert_null($result, "Test config with unspecified sub key.");
		unset($result_key);
		unset($result);

		// 'Default' Cases
		$this->assert_true_strict(config::cascade_from($config_set, 'weixin.default.bindable', false, true, 'default', $result_key), "Test 'default' config with specified sub key.");
		$this->assert_equal('weixin.default.bindable',$result_key, "Test result key of 'default' config with specified sub key.");
		unset($result_key);

		// Simplified Default Cases
		$this->assert_false_strict(config::cascade_from($config_set, 'sina.premium.bindable', false, true, 'default', $result_key), "Test simplified default config with specified sub key.");
		$this->assert_equal('sina.bindable', $result_key, "Test result key of simplified default config with specified sub key.");
		unset($result_key);

		$this->assert_true_strict(config::cascade_from($config_set, 'sina.premium.undefine_key', false, true, 'default', $result_key), "Test simplified default config with unspecified sub key.");
		$this->assert_null($result_key, "Test result key of simplified default config with unspecified sub key.");
		unset($result_key);

		// 2 Lvls Simplified Default Cases
		$this->assert_false_strict(config::cascade_from($config_set, 'sina.premium.bindable.guest', false, true, 'default', $result_key), "Test 2 lvls simplified default config with specified sub key.");
		$this->assert_equal('sina.bindable', $result_key, "Test result key of 2 lvls simplified default config with specified sub key.");
		unset($result_key);

		// Simplified + Standard Default Cases
		$this->assert_true_strict(config::cascade_from($config_set, 'sina.premium.registerable.guest', false, true, 'default', $result_key), "Test simplified + standard default config with specified sub key.");
		$this->assert_equal('sina.registerable.default', $result_key, "Test result key of simplified + standard default config with specified sub key.");
		unset($result_key);

		// Standard Default Cases
		$this->assert_true_strict(config::cascade_from($config_set, 'weixin.snsapi_userinfo.bindable', false, true, 'default', $result_key), "Test standard default config with specified sub key.");
		$this->assert_equal('weixin.default.bindable',$result_key, "Test result key of standard default config with specified sub key.");
		unset($result_key);

		$this->assert_true_strict(config::cascade_from($config_set, 'weixin.snsapi_userinfo.registerable', false, true, 'default', $result_key), "Test standard default config with unspecified sub key.");
		$this->assert_null($result_key, "Test result key of standard default config with unspecified sub key.");
		unset($result_key);

		// Partial Match Cases
		unset($config_set);
		$config_set = array(
			'sina' => array('test' => null)
		);
		$this->assert_false_strict(config::cascade_from($config_set, 'sina.test.key', false, false, 'default', $result_key), "Test unfulfiled config(NULL).");
		$this->assert_null($result_key, "Test result key of unfulfiled config(NULL).");
		unset($result_key);

		// Application cases: UserPref
		unset($config_set);
		$config_set = array();
		$config_set['userPref']['default'] = array (
			'driver'   => 'Preference_Dictionary_Driver',
			'params'   => array('model' => 'UserPref_Dicentry_Model'),
			'default_settings' => array(	// write default user preferences here.
		        'test_value' => true,
				'recevoir_sug_msg_Wechat' => true
			)
		);
		$this->assert_same($config_set['userPref']['default'], config::cascade_from($config_set, 'userPref.uid:183', true, NO_DEFAULT, 'default', $result_key), "Test userPref configuration.");
		$this->assert_equal('userPref.default', $result_key, "Test result key of userPref configuration.");
		unset($result_key);

		// Application cases: Socialapi after authorize redirection
		unset($config_set);
		$config_set = array();
		$config_set['redirect']['default'] = "/social/redirect";
		$config_set['redirect']['login_and_bind']['default'] = '/login';
		$config_set['redirect']['userinfo']['mobile'] = "/m/login/redirect?override_cred_check=1";
		$config_set['redirect']['login_and_bind']['mobile'] = '/m/login?override_cred_check=1';

		$this->assert_same($config_set['redirect']['default'], config::cascade_from($config_set, 'redirect.userinfo', true, NO_DEFAULT, 'default', $result_key), "Test intermediate node as scalar configuration.");
		$this->assert_equal('redirect.default', $result_key, "Test result key of intermediate node as scalar configuration.");
		unset($result_key);

		$this->assert_same($config_set['redirect']['login_and_bind']['default'], config::cascade_from($config_set, 'redirect.login_and_bind.iphone', true, NO_DEFAULT, 'default', $result_key), "Test authorize redirection configuration.");
		$this->assert_equal('redirect.login_and_bind.default', $result_key, "Test result key of authorize redirection configuration.");
		unset($result_key);

		$this->assert_same($config_set['redirect']['default'], config::cascade_from($config_set, 'redirect.userinfo.iphone', true, NO_DEFAULT, 'default', $result_key), "Test authorize redirection configuration.");
		$this->assert_equal('redirect.default', $result_key, "Test result key of authorize redirection configuration.");
		unset($result_key);
		
		unset($config_set);
		$config_set['redirect']['default']['mobile1']['do'] = '/m/login?override_cred_check=12';
		$config_set['redirect']['default']['mobile1']['do1'] = '/m/login?override_cred_check=13';
		//$config_set['redirect']['login_and_bind']['mobile1']['do'] = '/m/login?override_cred_check=1';
		$config_set['redirect']['login_and_bind']['mobile1']['do1'] = '/m/login?override_cred_check=do1';
		$config_set['redirect']['login_and_bind']['mobile2']['do2'] = '/m/login?override_cred_check=do2';
		$config_set['redirect']['login_and_bind']['default']['do'] = '/m/login?override_cred_check=do';

		$this->assert_null(config::cascade_from($config_set, 'redirect.login_and_bind.mobile1.do', false, NO_DEFAULT, 'default', $result_key), "Test none support for cascade mergence of default configsets.");
		$this->assert_null($result_key, "Test result key of none support for cascade mergence of default configsets.");
		unset($result_key);
		$this->assert_same(
			$config_set['redirect']['login_and_bind']['default']['do'], 
			config::cascade_from($config_set, 'redirect.login_and_bind.mobile1.do', false, NO_DEFAULT, 'default', $result_key, true), 
			"Test support for cascade mergence of default configsets.");
		$this->assert_equal('redirect.login_and_bind.default.do', $result_key, "Test result key of support for cascade mergence of default configsets.");
		unset($result_key);
		
		// Application case: social::config
		unset($config_set);
		$config_set = array();
		$config_set['Credential']['access_token'] = "/oauth2/access_token";
		$config_set['Credential']['sina']['other_key'] = 'other_val';
		
		$this->assert_same($config_set['Credential']['access_token'], config::cascade_from($config_set, 'Credential.sina.access_token', false, NO_DEFAULT, 'default', $result_key, true), "Test Credential.sina.access_token");
		$this->assert_equal('Credential.access_token', $result_key, "Test result key of Credential.sina.access_token");
		unset($result_key);
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