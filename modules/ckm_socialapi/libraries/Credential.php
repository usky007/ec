<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Used for 3rd party credential. All Credential_Model fields
 * are accessible from the instance of this class.
 *
 * $Id: Credential.php 2658 2011-06-23 06:53:18Z zhangjyr $
 *
 * @package    social api
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class Social_Exception extends UKohana_Exception {
	
}
 
class Credential {
//	const REQUEST_TOKEN_URL = "/oauth/%s/request_token.do";
//	const AUTHORIZE_URL = "/oauth/%s/authenticate.do";
//	const ACCESS_TOKEN_URL = "/oauth/%s/access_token.do";
	const REQUEST_TOKEN_URL = "request_token";
	const AUTHORIZE_URL = "authenticate";
	const ACCESS_TOKEN_URL = "access_token";
	const TOKEN_INFO_URL = "token_info";

	protected $_credential = null;

	protected $_valid = false;
	protected $_authorized = false;
	protected $_user = null;
	
	protected $_auth_method = null;

	/**
	 * First step authorization.
	 */
	public static function request($provider, $callback, $payload, $return = false, $extra = null) {
		$str_payload = null;
		if ($payload !== NULL) {
			if (is_array($payload)) {
				$str_payload = http_build_query($payload);
				$str_payload = base64_encode($str_payload);
			}else{
				$str_payload = base64_encode($payload);
			}			
		}
			
		$method = self::get_auth_method($provider);
		$params = null;
		$driver_name = get_class($method);
		switch ($driver_name) {
			case "AuthMethod_OAuth_Driver":
				if ($str_payload !== NULL) {
					$callback = preg_replace('/^(.*?)\/*$/', '$1/', $callback).$str_payload;
				}
				$params = array($callback, $return);
				break;
			case "AuthMethod_OAuth2_Driver":
				if (is_array($payload) && isset($payload['scope'])) {
					if ($extra == null) {
						$extra = array();
					}
					$extra['scope'] = $payload['scope'];
				}
				$params = array($callback, 'code', $str_payload, $return, $extra);
				break;
			default:
				throw new Kohana_Exception("core.assert_failure", "Unlikely auth method driver '$driver_name'");
		}
		return $method->authenticate(AUTH_STEP_ONE, $params);
	}

	public static function exchange($provider, $infos, &$payload) {
		$method = self::get_auth_method($provider);
		$params = null;
		$driver_name = get_class($method);
		switch ($driver_name) {
			case "AuthMethod_OAuth_Driver":
				if (!isset($infos["oauth_token"]) || !isset($infos["oauth_verifier"])) {
					throw new UKohana_Exception("E_APP_INVALID_PARAMETER", "errors.invalid_parameter");
				}
				$params = array($infos["oauth_token"], $infos["oauth_verifier"]);
				break;
			case "AuthMethod_OAuth2_Driver":
				if (!isset($infos["code"]) || !isset($infos["redirect_uri"])) {
					if (!isset($infos["code"])) {
						log::error("Missing 'code' in params - ".__CLASS__."::".__FUNCTION__);
					}
					else if (!isset($infos["redirect_uri"])) {
						log::error("Missing 'redirect_uri' in params - ".__CLASS__."::".__FUNCTION__);
					}
					throw new UKohana_Exception("E_APP_INVALID_PARAMETER", "errors.invalid_parameter");
				}
				$payload = isset($infos['state']) ? $infos['state'] : NULL;
				$params = array('authorization_code', $infos);
				break;
			default:
				throw new Kohana_Exception("core.assert_failure", "Unlikely auth method driver '$driver_name'");
		}
		if (!empty($payload)) {
			parse_str(base64_decode($payload), $payload);
		}
		return $method->authenticate(AUTH_STEP_TWO, $params);
	}

	/**
	 * Get all credentials binded with the user.
	 */
	public static function get_user_credentials($user) {
		$result = array();

		$credential = new Credential_Model();
		foreach ($credential->find_user_credentials($user) as $cred) {
			$result[] = new Credential($cred, $user);
		}
		return $result;
	}

	public static function get_credential_flags($sessioned_on_update = false) {
		$flags = Session::instance()->get('social', null);
		if (!is_null($flags)) {
			return $flags;
		}
		else if (!Account::instance()->user->is_guest()) {
			return self::update_credential_flags($sessioned_on_update);
		}
		else {
			return array();
		}
	}

	public static function update_credential_flags($sessioned = true) {
		$act = & Account::instance();
		if($act->user->is_guest()) {
			throw new UKohana_Exception("E_USER_SESSION_EXPIRED", "errors.not_login");
		}

		$creds = self::get_user_credentials($act->loginuser);
		$providers = array_keys(config::item('socialapi.providers', false, array()));
		$social = array_fill_keys($providers, false);
		foreach ($creds as $cred) {
			if (isset($social[$cred->provider])) {
				$social[$cred->provider] = $cred->is_valid();
			}
		}
		if ($sessioned) {
			Session::instance()->set('social', $social);
		}

		return $social;
	}
	
	public static function check_credential_flags($provider) {
		$flags = self::get_credential_flags();
		return isset($flags[$provider]) && $flags[$provider];
	}

	public static function account_on_initialize_handler() {
		if (!config::item("account.support_guest", false, false)) {
			return;
		}
		$support_providers = array_keys(config::item("socialapi.providers"));
		$openid = null;

		foreach ($support_providers as  $provider) {
			$credential = Session::instance()->get("{$provider}_credential_base", NULL);
			if($credential !== NULL) {
				$openid = $credential->openid;
				break;
			}
		}
		if ($openid !== null) {
			Event::$data[Account::EVENT_DATA_KEY_USER] = User_Model::new_guest($openid);
		}
	}

	/**
	 *
	 * @param string $provider
	 * @param string $identity
	 * @return NULL|Credential
	 */
	public static function get_credential_by_identity($provider, $identity) {
		$model = new Credential_Model();
		$model->with("user")->find(array("provider"=>$provider,
			"identity"=>$identity));
		if (!$model->loaded()) {
			return null;
		}

		return new Credential($model, $model->user);
	}
	
	public static function get_auth_method($provider, $is_gateway = false) {
		$gateway = $is_gateway ? $provider : self::gateway_by_provider($provider);
		$driver_name = social::config($gateway, "protocol", false);
		if (!$driver_name) {
			$driver_name = "OAuth";
		}
		if (strstr($driver_name, "_") === FALSE) {
			$driver_name = 'AuthMethod_'.ucfirst($driver_name).'_Driver';
		}
				
		// Load the driver
		if ( ! Kohana::auto_load($driver_name))
			throw new Kohana_Exception('core.driver_not_found', $driver_name, "AuthMethod");

		// Initialize the driver
		return new $driver_name($gateway, social::config($gateway, "options", false));
	}

	/**
	 * Constuct
	 *
	 * @param string Provider identifier.
	 * @param mixed  Token or User object, serect or possible private_key can be set later.
	 * @param bool   If $token_or_user is a token string, this parameter indicate if it is authorized, ignored otherwise.
	 */
	public function __construct($provider, $token_or_user, $is_authorized_token = false) {
		// Private use only, shortcut if $provider is a Credential_Model object. 
		if ($provider instanceof Credential_Model && isset($token_or_user->uid)) {
			// special routing for internal construction.
			$this->_credential = $provider;
			$this->_user = $token_or_user;
			$this->_authorized = $this->_valid = ($this->_credential->status == 0 && (
				$this->_credential->tokenTimeout == 0 || $this->_credential->tokenTimeout > time()));

			return;
		}
		
		$this->_credential = new Credential_Model();
		if (is_string($token_or_user)) {
			$this->provider = $provider;
			$this->token = $token_or_user;
			$this->_valid = 1;
			$this->_authorized = $is_authorized_token;
		}
		else if (isset($token_or_user->uid)) {
			// assuming User_Model object;
			$this->_credential->find(array(
				"uid" => $token_or_user->uid,
				"provider"=>$provider));
			if ($this->_credential->loaded()) {
				$this->_authorized = $this->_valid = ($this->_credential->status == 0 && (
					$this->_credential->tokenTimeout == 0 || $this->_credential->tokenTimeout > time()));
				$this->_user = $token_or_user; // be aware: not $this->user here.
			}
			else {
				$this->provider = $provider;
				$this->user = $token_or_user;
			}
		}
	}
	
	/*
	 * Get auth method matches credential.
	 */
	public function auth_method($gateway = null) {
		if (!$this->_auth_method) {
			$gateway_specified = is_null($gateway);
			$this->_auth_method = self::get_auth_method($gateway_specified ? $gateway : $this->provider, $gateway_specified);
		}
		return $this->_auth_method;
	}

	/**
	 * Is the credential valid(not expired or be deauthorized) or not. 
	 * Credential generated by program is always valid, but may be unauthorized. 
	 * Credential load from persistent storage (eg. database) maybe invalid, but always authorized.
	 */
	public function is_valid() {
		return $this->_valid;
	}

	public function is_deleted() {
		return !$this->_credential->loaded() || $this->_credential->status == Credential_Model::STATUS_DELETED;
	}

	/**
	 * Is the credential authorized for initiate an authorized call.
	 * Credential generated by program maybe unauthorized, but alway valid.
	 * Credential load from persistent storage (eg. database) always authorized, but maybe invalid.
	 */
	public function is_authorized() {
		return $this->_authorized;
	}

	/**
	 * Invalidate the credential. If the credential is invalid, do nothing.
	 */
	public function invalidate() {
		if ($this->_credential->loaded() && $this->_credential->status != Credential_Model::STATUS_DELETED) {
			$this->_credential->delete();
		}
		$this->_authorized = $this->_valid = false;
	}
	
	/** 
	 * Validate the credential. Only a few of provider (including sina) support this feature.
	 */
	public function validate() {
		$method = self::get_auth_method($this->provider);
		try {
			if (!$method->authenticate(AUTH_VALIDATE_TOKEN, array($this))) {
				$this->_authorized = $this->_valid = false;
				return false;
			}
				
			$this->_authorized = $this->_valid = true;
			return true;
		}
		catch (Social_Exception $ex) {
			log::error($ex->getMessage());
			if ($ex->getCode() != UKohana_Exception::code('E_SOCIAL_UNSUPPORTED')) {
				throw $ex;
			}
		}
		
		// try get social account
		try {
			$account = new SocialAccount($this);
			$account->get();
			
			$this->identity = $account[SocialAccount::FILED_IDENTITY];
			$this->_authorized = $this->_valid = true;
			return true;
		}
		catch (AuthorizedCall_Exception $ex) {
			$this->_authorized = $this->_valid = false;
			return false;
		}
	}

	/**
	 * Store the credential, ensure all field are set before call this function.
	 */
	public function store() {
		if (!isset($this->_credential->credid))
			$this->_credential->credid = ID_Factory::next_id($this->_credential);
		$this->_credential->save();
		$this->_authorized = $this->_valid = ($this->_credential->status == 0);
	}

	/**
	 * For unstored authorized credential, try bind to a user.
	 * @param  object $user User to bind.
	 * @return bool   Always true, or throws exception.
	 */
	public function bind($user, $throw_on_binded = TRUE, &$binded = null) {		

		if (!$this->is_authorized()) {			
			throw new UKohana_Exception('E_SOCIAL_AUTHORIZATION_REQUIRED','errors.authorization_required');
		}		
		
		$new_cred = $this->_credential;
		$binded = false;
		
		// Check user already binded to credential to be binded.
		$prov_cred = Credential::get_credential_by_identity($new_cred->provider, $new_cred->identity);			
		if(!is_null($prov_cred) && $prov_cred->is_valid()) {
			$binded = true;
			if ($prov_cred->uid == $user->uid) {
				// do nothing	
			}
			else if ($throw_on_binded) {
				throw new UKohana_Exception('E_SOCIAL_IDENTITY_BINDED', "errors.bind_identity_has_been_binded");
			}
			else {
				return false;
			}
		}
		
		// Check existed credential with different identity of user to be binded.
		$user_cred = null;
		// Binded only if user is the user of $prov_cred, and vise versa, which is not neccesary to check again.
		if (!$binded) {
			$user_cred = new Credential($new_cred->provider, $user);
			if(!$user_cred->is_valid()) {
				// do nothing
			}
			else if (!$throw_on_binded) {
				throw new UKohana_Exception('E_SOCIAL_ACCOUNT_BINDED', "errors.bind_account_has_been_binded");
			}
			else {
				return false;
			}
		}

		// Update existed credential($prov_cred) or initialized empty credential($user_cred).
		$credential = !is_null($prov_cred) ? $prov_cred : $user_cred;
		$credential->uid = $user->uid;
		$credential->identity = $new_cred->identity;
		$credential->provider = $new_cred->provider;
		$credential->token = $new_cred->token;
		$credential->tokenTimeout = $new_cred->tokenTimeout;
		$credential->secret = $new_cred->secret;
		$credential->status = 0;
		$credential->store();
		
		$this->_credential = $credential->_credential;
		$this->_authorized = $credential->is_authorized();
		$this->_valid = $credential->is_valid();
		
		return true;
	}

	// Magic Functions

	public function __get($key) {
		if ($key == "user") {
			return $this->_user;
		}
		else if ($key == 'openid') {
			return $this->is_authorized() ? ($this->provider.':'.$this->identity) : null;
		}
		return $this->_credential->__get($key);
	}

	public function __set($key, $value) {
		if ($key == "user") {
			if (!isset($value->uid))
				throw new Kohana_Exception("core.invalid_parameter", "value", __CLASS__, __FUNCTION__);
			$this->_user = $value;
			$this->_credential->__set("uid", $value->uid);
			return;
		}

		$this->_credential->__set($key, $value);

		// Update local variables only for authorized (but may be expired or deleted) credential.
		if ($this->_credential->loaded() && ($key == "tokenTimeout" || $key == "status")) {
			$this->_authorized = $this->_valid = ($this->_credential->status == 0 && (
					$this->_credential->tokenTimeout == 0 || $this->_credential->tokenTimeout > time()));
		}
	}

	public function __isset($key) {
		if ($key == "user") {
			return isset($this->_user);
		}
		return $this->_credential->__isset($key);
	}

	public function __sleep()
	{
		// Store only information about the object
		return array('_credential', '_valid', '_authorized', '_user');
	}
	
	private static function gateway_by_provider($provider) {
		return config::item("socialapi.providers.$provider.gateway", false, $provider);
	}
}
?>