<?php
class Social_Controller extends Public_Controller {
	private $provider_arr = array();

	function __construct() {
		parent::__construct();
	 
		Event::add('socialapi.on_register', array($this, '_on_register'));
	}
	
	function redirect($provider) {
		$this->set_view("social/redirect");
		$this->get_layout()->set_title(Kohana::lang('titles.social.redirect'))
							->add_css("css.social.redirect")
							->add_js("js.social.redirect");
							
		$this->set_js_context("provider", $provider);
		$return = Input::instance()->get('return', null);
		if ($return) {
			$this->set_js_context("redirect", url::site($return));
		}
		$this->set_output(array("redirect" => url::site($return ? $return : "")));
 
	}
	
	function cb($provider, $payload = null) {
		$input = Input::instance();
		$error = $input->get("error", null);
		if ($error) {
			if ($provider == "sina") {
				$error_code = $input->get("error_code", null);
				if ($error_code == "21330") {
					$return = url::site("login");
					if ($payload) {
						parse_str(base64_decode($payload), $payload);
						if (isset($payload['return'])) {
							$return = url::site($payload['return']);
						}
					}
					url::redirect($return);
					return;
				}
			}
			$message =  $input->get("error_description", null);
			throw new UKohana_Exception("E_SOCIAL_LOGIN_FAILED", $message);
		}
	 
		if (Account::instance()->checklogin(FALSE)) {
			return $this->bind($provider, $payload);
		}
		else {
			return $this->userinfo($provider, $payload);
		}
	}

	function userinfo($provider, $payload = null) {
		$act = Account::instance();
		if ($act->checklogin(false)) {
			url::redirect(url::site("/"));
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

			$s_account = new SocialAccount($credential);
			$ms = $s_account->get();
			if(isset($ms['error_code']))
				throw new UKohana_Exception("E_SOCIAL_LOGIN_FAILED", "errors.get_userinfo_fail");

			$record = Credential::get_credential_by_identity($provider, $ms['uid']);
			$login = FALSE; $uid = 0;
			if(!is_null($record)) {
				$login = TRUE;
				$uid = $record->uid;

				$record->status = 0;
				$record->token = $credential->token;
				$record->secret = $credential->secret;
				$record->tokenTimeout = $credential->tokenTimeout;
				$record->store();
				
				Session::instance()->set("uid", $uid);
				$act->loginuser = $record->user;
				Event::run("socialapi.on_login", $record);
				Event::run("account.on_login");
			} else {
				$credential->identity = $ms['uid'];
				Session::instance()->set("{$provider}_credential", $credential);
				Event::run("socialapi.on_register", $credential);
				Event::run("account.on_login");
			}
			$this->_redirect(__FUNCTION__, $provider);			
		} catch (UKohana_Exception $e) {
			// TODO: review
			throw $e;
		}
	}

	function bind($provider, $payload = null) {
		
		$act = Account::instance();
		if (!$act->checklogin(false)) {
			url::redirect(url::site("/"));
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
			$ms = $s_account->get();
			if(isset($ms['error_code'])) {
				throw new UKohana_Exception("E_SOCIAL_LOGIN_FAILED", "errors.get_userinfo_fail");
			}


			$identity = $ms['uid'];
			$prov_cred = Credential::get_credential_by_identity($provider, $identity);
			if(!is_null($prov_cred) && $prov_cred->is_valid())
				throw new UKohana_Exception('E_SOCIAL_REGISTER_FAILED', "errors.bind_identity_has_been_binded");

			$user_cred = new Credential($provider, $act->loginuser);
			if($user_cred->is_valid())
				throw new UKohana_Exception('E_SOCIAL_REGISTER_FAILED', "errors.bind_account_has_been_binded");

			$credential = !is_null($prov_cred) ? $prov_cred : $user_cred;
			$credential->uid = $act->loginuser->uid;
			$credential->identity = $identity;
			$credential->provider = $provider;
			$credential->token = $new_cred->token;
			$credential->tokenTimeout = $new_cred->tokenTimeout;
			$credential->secret = $new_cred->secret;
			$credential->status = 0;
			$credential->store();

			Session::instance()->delete("{$provider}_credential");
			Credential::update_credential_flags();
			Event::run("socialapi.on_bind", $credential);
			
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
		Credential::request($provider, $callback, $query);
	}
	
	function deauthorize($provider) {
		$support_providers = array_keys(config::item("socialapi.providers"));
		if (!in_array($provider, $support_providers)) {
			throw new UKohana_Exception('E_APP_INVALID_PARAMETER', "errors.unsupported");
		}
		
		$source = $input->get("source", null, true);
		if ($source != social::config($provider, "app_key")) {
			throw new UKohana_Exception('E_APP_INVALID_PARAMETER', "errors.unsupported");
		}
		
		$time = $input->get("auth_end", null, true);
		$identity = $input->get("uid", null, true);
		
		$credential = Credential::get_credential_by_identity($provider, $identity);
		if (!is_null($credential) || $credential->is_valid()) {
			$credential->invalidate();
		}
	}
	
	function _on_register() {
 
		$credential = Event::$data;
		assertion::is_false(is_null($credential), "Credential unavailable.");
		
		Session::instance()->delete("{$credential->provider}_credential");
		
		$profile = new SocialUser($credential);
		$user = $profile->get($credential->identity)->create_user();
		$credential->user = $user;
		$credential->store();
		Session::instance()->set("uid", $user->uid);
		
		Account::instance()->loginuser = $user;
	}
	
	function _redirect($op, $provider) {
		$url = config::cascade("socialapi.redirect.$op", false);
		if ($url) {
			unset($_GET['oauth_token']);
			unset($_GET['oauth_verifier']);
			unset($_GET['code']);
			unset($_GET['state']);
			url::redirect(preg_replace('/\/*$/', '', $url)."/$provider?".http_build_query($_GET));
		}
	}
}