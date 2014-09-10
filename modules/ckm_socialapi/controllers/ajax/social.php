<?php
class Social_Controller extends ServiceController {

	function put_test() {
		// Don't Delete, for unit test routings.
		$id = Input::instance()->post("id", null);
		if (is_null($id)) {
			throw new UKohana_Exception('E_APP_INVALID_PARAMETER',"core.missing_parameter", "id");
		}
		
		$this->set_output(array(
			"id" => $id,
			"method" => Input::instance()->method
		));
	}

	function test() {
		// Don't Delete, for unit test routings.
		$this->set_output(array(
			"id"=>123,
			"status"=>array("id"=>456),
			"records"=>array(
				array("id"=>789)
			)
		));
	}

	function test2()
	{
		$account = &Account::instance();
		$credential = new Credential('sina', $account->loginuser);
		$sfriend = new SocialFriend($credential);

		$users = $sfriend->get_city_friend('shanghai');
		var_dump($users);exit;
	 	$this->set_output(array('user' => $account->loginuser->uid));
	}

	function test_api() {
		if (!IN_PRODUCTION) {
			echo phpinfo();
		}
	}

	function test_call() {
		if (!IN_PRODUCTION) {
			$model = new Credential_Model();
			$model->token = "ABCD_HTTP-NAVI";
			$model->secret = "EFGH";
			$model->provider = "test";
			$model->status = 0;

			$user = new User_Model();
			$user->uid = 1;
			$user->email = "zhangjyr@qq.com";

			$credential = new Credential($model, $user);

			$testapi = new AuthorizedRestObject($credential, url::site("ajax/social/test_api"));
			$result = $testapi->get();
			echo $result;
		}
	}

	function unbind($provider) {
		$account = &Account::instance();
		if (!$account->checklogin(false))
			throw new UKohana_Exception('E_USER_SESSION_EXPIRED', "errors.not_login");

		$credential = new Credential($provider, $account->loginuser);
		if ($credential->is_valid()) {
			$credential->invalidate();
		}

		Credential::update_credential_flags();
		$this->set_output(array('uid' => $account->loginuser->uid));
	}

	function post_process($provider) {
		$account = &Account::instance();
		if (!$account->checklogin(false))
			throw new UKohana_Exception('E_USER_SESSION_EXPIRED', "errors.not_login");

		if($provider == 'sina')
		{
			$user = $account->get_loginuser();
			log::debug('post_process: ready to call async function '.$provider);
			$async = new AsyncService();
			$url = url::site('/wb/get/'.$user->uid);
			$t =  config::item('async.mq_enable.async_queue')?"Async enabled":"Async disabled";
			log::debug('post_process: '.$t);
			$t = gettype($this);
			log::debug($t);
			$async->async_call($url, array($this, '_getBilateral'), array('user' => $user));
			log::debug('post_process: rasync/sync finished');
		}
		
		if (Kohana::auto_load('StorageFile')) {
			$path = $account->loginuser->avatar;
			
			//if (empty($path) || !StorageFile::handler('avatar', $path)->exists()) {
				log::debug("User({$account->loginuser->uid}) avatar not found, try fetch avatar.");
				$credential = new Credential($provider, $account->loginuser);
				if ($credential->is_valid()) {
					$suser = SocialUser::factory($credential);
					$filename = $suser->import_avatar();
					if (!empty($filename)) {
						$account->loginuser->avatar = $filename;
						$account->loginuser->save();
					}
				}
			//}
		}
	}

	public function _getBilateral($user)
	{
		log::debug('post_process: calling async function failed, try to call sync function');
		$wb = Weibo::instance();

		$friends = $wb->getBilateral($user);
	}
}