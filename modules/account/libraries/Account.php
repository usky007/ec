<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Account Class
 *
 * This class provide basic utility for account checking.
 * Usage:
 * Start by get $account = Account::instance();
 * Chack login or not by check $account->checklogin($dologin = true);
 * $account->loginuser will always be available if login check passed, null if failed.
 * $account->user will be a GUEST object if "account.support_guest" set to on and login check failed.
 * (A GUEST object is a User_Model where $user->is_guest() return true);
 */
class Account{
	const EVENT_ON_INITIALIZE = 'account.on_intialize';
	const EVENT_ON_REGISTER = 'account.on_register';
	const EVENT_ON_LOGIN = 'account.on_login';
	const EVENT_ON_LOGOUT = 'account.on_logout';

	const EVENT_DATA_KEY_OBJECT = 'object';
	const EVENT_DATA_KEY_USER = 'user';

	const ACCESS_BY_URL = 0;
	const ACCESS_BY_ROLE = 1;
	const ACCESS_BY_OPERATION = 2;

	const ROLE_SITE_ADMIN = 100;
	const ROLE_INFO_ADMIN = 101;

	protected static $account = NULL;

	// user logged in;
	protected $loginuser = NULL;
	// user currently visit, NULL if "account.support_guest" set to "off"
	protected $user = NULL;
	protected $unverified = 0;
	protected $login_success_handler = NULL;
	protected $login_checked = false;

	public static function login_url() {
		return self::loopback_url(url::site("login"));
	}

	public static function signup_url() {
		return self::loopback_url(url::site("signup"));
	}

	public static function logout_url() {
		return self::loopback_url(url::site("logout"));
	}

	public static function loopback_url($url) {
		return preg_replace('/^(.*?)\/*(?:\?(.*))?$/', '\1?return='.url::current(true).'&\2', $url);
	}

	public function __construct() {
		if (self::$account != NULL) {
			$this->loginuser = &self::$account->loginuser;
			$this->user =   &self::$account->user;
		}
		else {
			self::$account = &$this;
			// Guest support, always suppose a guest user.
			$data = array (
				self::EVENT_DATA_KEY_OBJECT => self::$account,
				self::EVENT_DATA_KEY_USER => &$this->user
			);
			Event::run(self::EVENT_ON_INITIALIZE, $data);
			if ($this->user === NULL) {
				$this->user = User_Model::new_guest();
			}
		}
	}

	public static function &instance()
	{
		if (self::$account == NULL) {
			self::$account = new Account();
		}

		return self::$account;
	}

    /**
     * 登出
     * */
	public function logout()
	{
		$this->loginuser = null;
		$this->user = config::item("account.support_guest", false, false) ? User_Model::new_guest() : null;
		$session = Session::instance ();
		$session->destroy();
		cookie::delete("auth_code");
		$this->login_checked = true;
	}

	/**
     * 登出，微信授权绑定账户冲突时，用户登出，但保留session
     * */
	public function logout_incomplete()
	{
		$this->loginuser = null;
		$this->user = config::item("account.support_guest", false, false) ? User_Model::new_guest() : null;
		$session = Session::instance();
		$session->set('uid',0);
		cookie::delete("auth_code");
		$this->login_checked = true;
	}
	
	public function autoLogin()
	{
		$input = new Input_Core();
		$authstr = $input->cookie("auth_code", NULL, true);
		if ($authstr != NULL) {
			$encrypt = new Encrypt("auth");
			$data = json_decode($encrypt->decode($authstr), true);
			if (is_array($data) && count($data) >= 6) {
				$data = array_combine(array("ip", "agent", "email", "pwd", "timeout", "id"), $data);
				if ($data["ip"] == $input->ip_address() &&
					$data["agent"] == Kohana::$user_agent &&
					time() > (int)$data["agent"]) {
					$email = $data["email"];
					$pwdmd5 = $data["pwd"];
					try
					{
						$this->login($email, $pwdmd5, TRUE);
						return TRUE;
					}catch (Exception $e)
					{
						cookie::delete("auth_code");
						return false;
					}

				}
			}
		}

		return FALSE;
	}

	public function checklogin($dologin = true)
	{
		if ($this->login_checked) {
			$islogin = ($this->loginuser != null);
			if($islogin)
				return true;
			else if(!$dologin)
				return false;
		}

		$session = Session::instance();
		$uid = $session->get('uid');

	 	$user = $this->create_user_model();
	 	if(!empty($uid) && $user->find($uid)->loaded())//登录成功
	 	{
	 		$this->user = $this->loginuser = $user;
	 		$this->unverified = $session->get('unverified', 0);

	 		if($user->lastLoggedIn < date::today()) {
		 		$user->lastAccessed = $user->lastLoggedIn = ORM::get_time();
		 		$user->lastIp = Input::instance()->ip_address();
		 		$user->save();
	 		}
	 		else if(rand(1,100) <= intval(config::item('gamerule.set_accessed_rate', false, 1)))
	 		{
	 			$user->lastAccessed = ORM::get_time();
	 			$user->save();
	 		}
	 		$this->login_checked = true;
	 		return true;
	 	}

	 	if($this->autoLogin())
	 	{
	 		$this->login_checked = true;
	 		return TRUE;
	 	}

		if($dologin)
		{
			$login_url = url::site("login");

			$return_url = url::site(url::current(TRUE), "http");
			$login_url .= "?return=".urlencode($return_url);
			Kohana::log("debug", "redirect to ".$login_url);
			url::redirect($login_url);
			return false;
		}

		$this->login_checked = true;
		return false;
	}

	public function __get($var) {
		switch($var) {
			case "loginuser":
				return $this->get_loginuser();
			case "user":
				return $this->get_user();
		}
		throw new UKohana_Exception('E_APP_INVALID_PARAMETER', "core.invalid_parameter", __CLASS__, __FUNCTION__, $var);
	}

	public function __set($var, $val) {
		switch($var) {
			case "loginuser":
				$customModel = $this->create_user_model();
				$cls = get_class($customModel);
				if (!is_null($val) && !($val instanceof $cls)) {
					$val = new $cls($val);
				}
				$this->loginuser = $val;
				$this->user = is_null($val) ? User_Model::new_guest() : $val;
				$this->login_checked = true;
				return;
			case "user":
				$customModel = $this->create_user_model();
				$cls = get_class($customModel);
				if (!is_null($val) && !($val instanceof $cls)) {
					$val = new $cls($val);
				}
				$this->user = $val;
				$this->loginuser = (!is_null($val) && !$val->is_guest()) ? $val : null;
				$this->login_checked = true;
				return;
		}
		throw new UKohana_Exception('E_APP_INVALID_PARAMETER', "core.invalid_parameter", __CLASS__, __FUNCTION__, $var);
	}

	public function __isset($var) {
		switch($var) {
			case "loginuser":
				return isset($this->loginuser);
			case "user":
				return isset($this->user);
		}
		throw new UKohana_Exception('E_APP_INVALID_PARAMETER', "core.invalid_parameter", __CLASS__, __FUNCTION__, $var);
	}

	public function get_user() {
		$this->checklogin(false);
		return $this->user;
	}

	public function get_loginuser($throw_on_err = false) {
		if (!$this->checklogin(false) && $throw_on_err) {
			throw new UKohana_Exception('E_USER_SESSION_EXPIRED', "errors.not_login");
		}
		return $this->loginuser;
	}

	public function login($email, $pwdMd5, $remember = FALSE)
	{
		$ary = array('email'=>$email);
		$user = $this->create_user_model();
		if(!$user->where($ary)->find()->loaded()) //找到此用户
		{
			throw new UKohana_Exception('E_USER_NOT_FOUND', "errors.login_invalid_input");
		}
		elseif(!$user->status) //此用户被封禁
		{
			throw new UKohana_Exception('E_USER_BLOCKED', "errors.not_login");
		}
		elseif(md5(strtolower($pwdMd5).'@'.$user->salt) != $user->password) //密码匹配
		{

			throw new UKohana_Exception('E_USER_NOT_FOUND', "errors.login_invalid_input");
		}

		if ($remember)
		{
			$encrypt = new Encrypt("auth");
			$input = Input::instance();
			$authstr = json_encode(array($input->ip_address(), Kohana::$user_agent, $user->email, $pwdMd5, time() + (int)$remember, session_id()));
			cookie::set("auth_code", $encrypt->encode($authstr), $remember);
		}
		else
		{
			cookie::delete("auth_code");
		}

		return $this->login_as($user);
	}

	public function login_as($user)
	{
		if ($user->is_guest()) {
			return false;
		}

		$this->user = $this->loginuser = $user;
		$this->login_checked = true;
		if (is_null($this->login_success_handler)) {
			$this->login_success($user);
		}
		else {
			call_user_func_array($this->login_success_handler, array($user));
		}
		Event::run(self::EVENT_ON_LOGIN);
		return true;
	}

	/*
	 * function : renturn current user avatar
	 * parm: size: small(default) ,big , middle
	 */
//	public function get_avatar($size='small')
//	{
//		if ($this->loginuser === NULL)
//			return "";
//
//		return User::get_user_avatar($this->loginuser->uid, $size);
//	}

	public function is_info_admin() {
		return $this->is_admin('editor');
	}

	public function is_site_admin() {
		return $this->is_admin('site');
	}

	public function accessible($access_type, $id) {
		switch ($access_type) {
			case self::ACCESS_BY_ROLE:
				switch ($id) {
					case self::ROLE_INFO_ADMIN:
						if ($this->is_info_admin()) {

							return true;
						}
						// no break here;
					case self::ROLE_SITE_ADMIN:
						if ($this->is_site_admin()) {
							return true;
						}
						// no break here;
					default:
						return false;
				}
			default:
				throw new UKohana_Exception('E_APP_UNSUPPORTED', "errors.unsupported");
		}
	}

	public function set_login_success_handler($handler) {
		$this->login_success_handler = $handler;
	}

	protected function is_admin($key) {
		if (is_null($this->loginuser)) {
			return false;
		}

		$pref = & Preference::instance("administrators");
		$admins = $pref->get($key);

		if ($admins !== null) {
			$admins = json_decode($admins, true);
		}
		if (empty($admins)) {
			return false;
		}
		else if (in_array($this->loginuser->uid, $admins)) {
			return true;
		}
		return false;
	}


	private function login_success($user)
	{
		// Skip just registered.
		if ($user->lastAccessed != $user->created) {
			$user->lastAccessed = $user->lastLoggedIn = ORM::get_time();
			$user->lastIp = Input::instance()->ip_address();
			$user->save();
		}
		
		// save session
		$session = Session::instance();
		$session->set('uid',$user->uid);

		// social api related initialization.
		if (Kohana::auto_load("Credential")) {
			Credential::update_credential_flags();
		}
	}

	private function create_user_model()
	{
		$model = config::item("account.user_model", false, false);
		if (!$model) {
			return new User_Model();
		}
		else {
			return new $model();
		}
	}
	public static function auxiliary_login()
	{
		$referrer = Kohana::user_agent('referrer');
		return (stristr($referrer,'weibo.com'));
	}
}
