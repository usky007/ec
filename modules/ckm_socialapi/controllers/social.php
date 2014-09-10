<?php
class Social_Controller extends Public_Controller {
	private $provider_arr = array();

	function __construct() {
		parent::__construct();
		Event::add('socialapi.on_bind', array($this, '_on_bind'));
	}

	function _on_bind(){
		$credential = Event::$data;
	
		//ERROR credential instanceof Credential is false
		if (!($credential instanceof Credential))			
			return;

		$user = User_Model::new_guest($credential->openid);
		$guid = $user->guid;		
		if ($guid !== NULL && $guid != Account::instance()->loginuser->guid) {			
			Event::run('socialapi.on_usermerge',  $user);
			//$userPref = new UserPref($user);
			//$userPref->delete("guid");
		}
	}
	
	function redirect($provider) {

		$return = Input::instance()->query("return", "");
		$this->set_js_context("provider", $provider);
		if (Input::instance()->query("widget", false)) {
			AppLayout_View::set_layout("layouts/widget");
			$this->set_js_context("widget", 1);
			$this->set_js_context("cb", Input::instance()->query("cb", ""));
			$this->set_js_context("user", Account::instance()->user->to_api());
		}
		else {
			$this->set_js_context("redirect", url::trusted($return));
		}
		
		$this->set_view("social/redirect");
		$this->get_layout()->set_title(Kohana::lang('titles.social.redirect'))
							->add_css("css.social.redirect")
                            ->add_css("central_spec.css")
							->add_js("js.social.redirect");
		$this->set_output(array("redirect" => url::trusted($return))); 
	}

	/**
	 * Register or direct login in other words with authorized credential in session;
	 */
	function direct_login($provider) {	

		$credential = Session::instance()->get("{$provider}_credential", NULL);	
		if(is_null($credential)) {
			throw new UKohana_Exception("E_SOCIAL_LOGIN_FAILED", "errors.invalid_entry");
		}

		$this->_register($credential, false);
		Session::instance()->delete("{$provider}_credential");
		Session::instance()->delete("{$provider}_credential_base");

		$this->_redirect(__FUNCTION__, $provider);
	}
	
	function cb($provider, $payload = null) {

		$input = Input::instance();			

		if($provider == "sina"){ //新浪授权的错误处理
			$error = $input->get("error", null);
			if ($error) {
				$error_code = $input->get("error_code", null);
				if ($error_code == "21330") {
					$return = url::site("login");
					if ($payload) {
						parse_str(base64_decode($payload), $payload);
						if (isset($payload['return'])) {
							$return = url::trusted($payload['return']);
						}
					}
					url::redirect($return);
					return;
				}
			$message =  $input->get("error_description", null);
			throw new UKohana_Exception("E_SOCIAL_LOGIN_FAILED", $message);
			}
		}else if($provider == "weixin"){ //授权时，微信用户点击取消的处理方式
			$code = $input->get("code", null);

			if ($code == "authdeny") {
				url::redirect("/m/login/back/?override_cred_check=1");
				return;
			}
		}
	 
		if (Account::instance()->checklogin(FALSE)) {
			log::debug(__CLASS__."::".__FUNCTION__." call bind({$provider},...)");
			return $this->bind($provider, $payload);
		}
		else {
			log::debug(__CLASS__."::".__FUNCTION__." call userinfo({$provider},...)");
			return $this->userinfo($provider, $payload);
		}
	}

	function userinfo($provider, $payload = null) {
		$act = Account::instance();
		if ($act->checklogin(false)) {
			url::redirect("");
			return;
		}

		$this->set_view("social/userinfo");
		$this->get_layout()->set_title(Kohana::lang('titles.social.userinfo'))
							->add_css("css.social.userinfo")
							->add_js("js.social.userinfo");
		$msg = '';
		try {
			$support_providers = array_keys(config::item("socialapi.providers"));
			if (!in_array($provider, $support_providers)) {
				throw new UKohana_Exception("E_APP_INVALID_PARAMETER", "errors.unsupported");
			}

			$input = Input::instance();
			$infos = array();
			$infos['oauth_token'] = $input->get("oauth_token", null, true);
			$infos['oauth_verifier'] = $input->get("oauth_verifier", null, true);
			$infos['code'] = $input->get("code", null, true);
			$infos['state'] = $input->get("state", null, true);
			$infos['redirect_uri'] = url::site("social/cb/$provider").($payload ? "/".$payload : "");
			
			$credential = Credential::exchange($provider, $infos, $payload);
			if (!empty($payload)) {
				$_GET = array_merge($_GET, $payload);
			}
			
			// Get identity by credential first.
			$s_account = new SocialAccount($credential);
			$s_account->get();
			// Is credential record already exists?
			$record = Credential::get_credential_by_identity($provider, $s_account[SocialAccount::FILED_IDENTITY]);
			// Login if credential valid or unbindable which means no chance to bind another account.
			if(!is_null($record) && ($record->is_valid() || !config::item("socialapi.providers.$provider.bindable",false,false))) {
				// Update information
				$record->status = 0;
				$record->token = $credential->token;
				$record->secret = $credential->secret;
				$record->tokenTimeout = $credential->tokenTimeout;
				$record->store();
				
				// Do login
				Event::run("socialapi.on_login", $record);
				Account::instance()->login_as($record->user);
				$this->_redirect('login', $provider);
			} else {
				// Set identity to credential
				$credential->identity = $s_account[SocialAccount::FILED_IDENTITY];
				
				// Is credential in some kind of scope?
				$scope_key = "";
				if(isset($payload['scope']) && !empty($payload['scope'])) {
					$scope_key .= '.'.$payload['scope'];
				}			
				// Bindable provider, redirect to login page and then bind.
				if( config::cascade("socialapi.providers.$provider.bindable$scope_key", false, false) ){
					Session::instance()->set("{$provider}_credential", $credential);
					log::debug("Authorized by bindable provider: $provider!");
					$this->_redirect("login_and_bind", $provider);
					return;	
				}
				// Unregisterable provider, simply pass.
				else if ( !config::cascade("socialapi.providers.$provider.registerable$scope_key", false, true) ) {
					Session::instance()->set("{$provider}_credential_base", $credential);
					log::debug("Authorize by unregisterable provider: $provider!");
					$this->_redirect("pass", $provider);
					return;
				}
				// Do register
				else{
					$this->_register($credential);
					$this->_redirect('direct_login', $provider);
				}
			}
		} catch (UKohana_Exception $e) {
			// TODO: review
			throw $e;
		}
	}

	function bind($provider, $payload = null) {
		
		$act = Account::instance();
		if (!$act->checklogin(false)) {
			url::redirect("");
			return;
		}
		
		$this->set_view("social/bind");
		$this->get_layout()->set_title(Kohana::lang('titles.social.bind'))
						->add_css("css.social.bind")
						->add_js("js.social.bind");
	
		try {
			$support_providers = array_keys(config::item("socialapi.providers"));
			if (!in_array($provider, $support_providers)) {
				throw new UKohana_Exception("E_APP_INVALID_PARAMETER", "errors.unsupported");
			}

			$input = Input::instance();
			$new_cred = Session::instance()->get("{$provider}_credential", NULL);
			if (is_null($new_cred)) {


				$input = Input::instance();
				$infos = array();
				$infos['oauth_token'] = $input->get("oauth_token", null, true);
				$infos['oauth_verifier'] = $input->get("oauth_verifier", null, true);
				$infos['code'] = $input->get("code", null, true);
				$infos['state'] = $input->get("state", null, true);
				$infos['redirect_uri'] = url::site("social/cb/$provider");

				$new_cred = Credential::exchange($provider, $infos, $payload);
				Session::instance()->set("{$provider}_credential", $new_cred);
				if (!empty($payload)) {
					$_GET = array_merge($_GET, $payload);
				}
			}
			
			$s_account = new SocialAccount($new_cred);
			$s_account->get();
			$new_cred->identity = $s_account[SocialAccount::FILED_IDENTITY];
			$bind_result = $new_cred->bind($act->loginuser);
			if( !is_null($bind_result) && !$bind_result) {
				if(strpos(Kohana::user_agent(),'MicroMessenger')) {
					Account::instance()->logout_incomplete();
					$this->_redirect("relogin", $provider);
					return;
				}else{
					throw new UKohana_Exception('E_SOCIAL_ACCOUNT_BINDED', "errors.bind_account_has_been_binded");
				}
				//throw new UKohana_Exception('E_SOCIAL_ACCOUNT_BINDED', "errors.bind_account_has_been_binded");
			}
			Credential::update_credential_flags();
			Event::run("socialapi.on_bind", $credential);
			Session::instance()->delete("{$provider}_credential");
			Session::instance()->delete("{$provider}_credential_base");
			$this->sendMsgToWechat($provider);
			$this->pushDataToMq($provider);
			
			$this->_redirect(__FUNCTION__, $provider);
		} 
		catch (UKohana_Exception $e) {
			// TODO:: review
			throw $e;
		}
	}
	
	/**
	 * Providers that require 'scope' parameter, pass 'scope' as a GET parameter.
	 */
	function authorize($provider) {
		$support_providers = array_keys(config::item("socialapi.providers"));
		if (!in_array($provider, $support_providers)) {
			throw new UKohana_Exception("E_APP_INVALID_PARAMETER", "errors.unsupported");
		}

		$callback = url::site("social/cb/$provider");
		$query = null;	
		if (!empty($_SERVER['QUERY_STRING'])) {
			parse_str($_SERVER['QUERY_STRING'], $query);
		}
		$extra = array();
		if (isset($query['forcelogin'])) {
			$extra['forcelogin'] = $query['forcelogin'];
			unset($query['forcelogin']);
		}
		if (isset($query['display'])) {
			$extra['display'] = $query['display'];
			unset($query['display']);
		}
		Credential::request($provider, $callback, $query, false, empty($extra) ? null : $extra);
	}
	
	function deauthorize($provider) {
		$support_providers = array_keys(config::item("socialapi.providers"));
		if (!in_array($provider, $support_providers)) {
			throw new UKohana_Exception('E_APP_INVALID_PARAMETER', "errors.unsupported");
		}
		
		$args = $input->get();
		$gateway = config::item("socialapi.providers.$provider.gateway", false, $provider);
		$mapper = config::item("socialapi.global_mapper.$gateway", false, array());
		$args = social::ex_mapping($mapper, $args);
		
		$source = isset($args['source']) ? $args['source'] : null;
		if ($source != config::ditem("socialapi.gateway.{$this->provider}.app_key")) {
			throw new UKohana_Exception('E_APP_INVALID_PARAMETER', "errors.unsupported");
		}
		
		//$time = $input->get("auth_end", null, true);
		$identity = isset($args['identity']) ? $args['identity'] : null;
		$credential = Credential::get_credential_by_identity($provider, $identity);
		if (!is_null($credential) || $credential->is_valid()) {
			$credential->invalidate();
		}
	}
	
	private function _register($credential, $skip_credential_check = true)
	{ 
		$profile = SocialUser::factory($credential);
		$user = $profile->get($credential->identity)->create_user(true);
		
		if (!$skip_credential_check) {
			$record = Credential::get_credential_by_identity($credential->provider, $credential->identity);
			if (!is_null($record)) {
				$record->status = 0;
				$record->token = $credential->token;
				$record->secret = $credential->secret;
				$record->tokenTimeout = $credential->tokenTimeout;
				$credential = $record;
			}
		}
		$credential->user = $user;
		$credential->store();
		Event::run("socialapi.on_register", $credential);

		Account::instance()->login_as($user);
		$this->sendMsgToWechat($credential->provider);
		$this->pushDataToMq($credential->provider);
	}

	/**
	 * 发送客服消息给Wechater
	 * 
	 */
	private function sendMsgToWechat($provider){
		
		if ($provider == "weixin") {
			$user = Account::instance()->loginuser;
			$cred1 = new Credential($provider, $user);
			$cred2 = new Credential("weixin_server", $user);

			//微信已绑定 而且 激活的weixin_server不存在的情况下发送客服消息:提醒开启推送
			if($cred1->is_valid() && !$cred2->is_valid()){
				try {
					$weixinapi = new WeixinApi();
					$weixinapi->api_call(array($weixinapi, "sendCustomerServiceMsg"),$cred1->identity,kohana::lang('weixin.servicemsg'));
				}
				catch (Exception $e) {
					//ignore;
				}
			}
		}
	}

	/**
	 * push openid to Mq
	 */
	private function pushDataToMq($provider){

		if ($provider == "weixin") {
			$user = Account::instance()->loginuser;
			$cred1 = new Credential($provider, $user);
			$cred2 = new Credential("weixin_server", $user);

			//微信已绑定 而且 激活的weixin_server不存在的情况下将Openid放到MQ里
			if($cred1->is_valid() && !$cred2->is_valid()){
				try {
					WeixinApi::pushOpenidToMq($cred1->identity);
				}
				catch (Exception $e) {
					//ignore;
				}
			}
		}
		
	}
	
	private function _redirect($op, $provider) {
		$redirect_op = $op.(Kohana::user_agent('is_mobile') ? ".mobile." : ".default.").$provider;
		$url = config::cascade("socialapi.redirect.$redirect_op", false, false);
		if ($url !== false) {
			unset($_GET['oauth_token']);
			unset($_GET['oauth_verifier']);
			unset($_GET['code']);
			unset($_GET['state']);
			if (isset($_GET['return']) && preg_replace('/^\/*(.+?)\/*$/','$1', $url) == preg_replace('/^\/*(.+?)\/*$/','$1', $_GET['return'])) {
				// 'return' can't be same with url.
				unset($_GET['return']);
			}			
			url::redirect(url::build($url, array(
				"path" => $provider,
				"query" => $_GET)));
			return;
		}
		
		$return = isset($_GET['return']) ? $_GET['return'] : "";
		url::redirect($return);
	}
}

