<?php
/**
 * Class description.
 *
 * $Id: Credential_Test.php 2030 2011-02-15 13:51:57Z xuronghua $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class Credential_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;
	private $user = null;
	private $provider = "3PTEST";

	public function setup() {
		$this->user = new User_Model();
		$this->user->email = "zhangjyr@qq.com";
		$this->user->uid = 1;
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}

	public function attribute_access_test() {
		$model = new Credential_Model();
		$model->provider = "sina";
		$secret = "123456";
		$credential = new Credential($model, $this->user);

		$this->assert_equal($model->provider, $credential->provider, "attribute get test");
		$this->assert_false(isset($credential->token), "attribute isset test");

		$credential->secret = $secret;
		$this->assert_equal($secret, $model->secret, "attribute set test");

		$credential->user = $this->user;
		$this->assert_equal($this->user->uid, $model->uid, "attribute set(user) test");
	}

	public function create_test() {
		$model = new Credential_Model();
		$model->where("uid", $this->user->uid)
			->where("provider", $this->provider)
			->delete_all();
		$this->assert_equal(0, $model->where("uid", $this->user->uid)
								->where("provider", $this->provider)
								->count_all());

		$token = "ABCDED";
		$identity = "123456";
		$credential = new Credential($this->provider, $token);
		$credential->user = $this->user;
		$credential->identity = $identity;
		$credential->store();

		$model = new Credential_Model();
		$model->find(array("uid"=>$this->user->uid, "provider"=>$this->provider));
		$this->assert_true_strict($model->loaded());
		$this->assert_equal($token, $model->token);
		$this->assert_equal($identity, $model->identity);
	}

	public function load_test() {
		$identity = "123456";
		$credential = new Credential($this->provider, $this->user);
		$this->assert_true_strict($credential->is_valid());
		$this->assert_equal($identity, $credential->identity);
	}

	public function serialize_set_test() {
		$identity = "123456";
		$newtoken = "ABCDEFG";
		$credential = new Credential($this->provider, $this->user);
		$this->assert_true_strict($credential->is_valid());
		$this->assert_equal($identity, $credential->identity);

		$serialized = serialize($credential);
		$credential = unserialize($serialized);
		$this->assert_true_strict($credential->is_valid());
		$this->assert_equal($identity, $credential->identity);
		$this->assert_equal($this->user->uid, $credential->user->uid);

		$cache = Cache::instance("CredentialTest");
		$credential->token = $newtoken;
		$cache->set($this->provider.$identity, $credential);
	}

	public function serialize_get_test() {
		$identity = "123456";
		$newtoken = "ABCDEFG";
		$cache = Cache::instance("CredentialTest");

		$credential = $cache->get($this->provider.$identity);
		$this->assert_object($credential);
		$this->assert_true_strict($credential->is_valid());
		$this->assert_equal($identity, $credential->identity);
		$this->assert_equal($this->user->uid, $credential->user->uid);

		$credential->store();

		$model = new Credential_Model();
		$model->find(array("uid"=>$this->user->uid, "provider"=>$this->provider));
		$this->assert_true_strict($model->loaded());
		$this->assert_equal($newtoken, $model->token);
		$this->assert_equal($identity, $model->identity);
	}
	
	public function validate_test() {
		$identity = 1923346074;
		
		$record = Credential::get_credential_by_identity("sina", $identity);
		$this->assert_not_null($record, "Authenticate use zhangjyr@qq.com(1923346074) first");
		
		// construct temporary credential
		$credential = new Credential("sina", $record->token, false);
		$credential->invalidate();
		
		$result = $credential->validate();
		$this->assert_true_strict($result, "Test validate a known valid token");
	}
}
?>