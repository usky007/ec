<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * OAuth 2 authentication method, sina driver.
 *
 * $Id: Abstract.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    socialapi
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2012 UUTUU
 */
define('OAUTH2_STEP_REQUEST', 1);
define('OAUTH2_STEP_EXCHANGE', 2);

class AuthMethod_OAuth2_Driver implements AuthMethod_Driver {

	const AUTHORIZE_URL = "authenticate";
	const ACCESS_TOKEN_URL = "access_token";
	const TOKEN_INFO_URL = "token_info";
	
	const GRANT_TYPE_CODE = 'authorization_code';
	const GRANT_TYPE_PASSWORD = 'password';
	const GRANT_TYPE_CREDENTIALS = 'client_credentials';
	const GRANT_TYPE_REFRESH = 'refresh_token';
	
	const MACKEY_KEY = "key";
	const MACSIGNATURE_KEY = 'mac';

	protected $provider = NULL;

	public function __construct($provider)
	{
		$this->provider = $provider;

		Kohana::log('debug', 'OAuth2 Authentication Method Driver Initialized');
	}
	
	public function send(CurlRequest $request, Credential $credential)
	{
		if (!$request) {
			throw new Kohana_Exception("core.invalid_parameter", "request", __CLASS__, __FUNCTION__);
		}
		if (!$credential || !$credential->is_valid() || !$credential->is_authorized()) {
			throw new Kohana_Exception("core.invalid_parameter", "credential", __CLASS__, __FUNCTION__);
		}
	
		$request->headers[] = "Authorization: OAuth2 ".$credential->token;
		if (IN_PRODUCTION) {
			$request->ssl_verifypeer = TRUE;
		}
		
		return $request->send();
	}
	
	/**
	 * authorize接口
	 *
	 * 对应API：{@link http://open.weibo.com/wiki/Oauth2/authorize Oauth2/authorize}
	 *
	 * @param string $response_type 支持的值包括 code 和token 默认值为code
	 * @param string $url 授权后的回调地址,站外应用需与回调地址一致,站内应用需要填写canvas page的地址
	 * @param string $state 用于保持请求和回调的状态。在回调时,会在Query Parameter中回传该参数
	 * @param string $return 是否直接跳转，默认值为直接跳转
	 * @param array  $extra 额外参数，可供选择的有：display（sina专用）
	 *
	 * @return array
	 */
	public function request($callback = NULL,  $response_type = 'code', $state = NULL, $return = false, $extra = NULL) {
		$params = array();
		$params['client_id'] = social::config($this->provider, 'app_key');
		$params['response_type'] = $response_type;
		if ($callback) {
			$params['redirect_uri'] = $callback;
		}
		if ($state) {
			$params['redirect_uri'] = preg_replace('/^(.*)\/*$/', '$1/', $callback).urlencode($state);
			$params['state'] = $state;
		}
		if ($extra && is_array($extra)) {
			$params = array_merge($params, $extra);
		}
		
		$url = social::api_url($this->provider, "Credential", self::AUTHORIZE_URL) . "?" . http_build_query($params);
		if ($return) {
			return $url;
		}
		else {
			url::redirect($url);
		}
	}
	
	/**
	 * access_token接口
	 * Doc: {@link http://tools.ietf.org/html/draft-ietf-oauth-v2-31 OAuth2 Draft}
	 * Sina API：{@link http://open.weibo.com/wiki/OAuth2/access_token OAuth2/access_token}
	 *
	 * @param string $grant_type 请求的类型,可以为:authorization_code(code), password, 
	 *  client_credentials(credentials), refresh_token(token)。默认为 authorization_code
	 * @param array $params 请求参数：
	 *  - 当$grant_type为authorization_code时： array('code'=>..., 'redirect_uri'=>...) Doc(4.1.3)
	 *  - 当$grant_type为password时： array('username'=>..., 'password'=>..., 'scope'(optional)=>...) Doc(4.3.2)
	 *  - 当$grant_type为client_credentials时： array('scope'(optional)=>...) Doc(4.4.2)
	 *  - 当$grant_type为refresh_token时： array('refresh_token'=>..., 'scope'(optional)=>...) Doc(6)
	 * @return array
	 */
	public function exchange($grant_type = 'authorization_code', $keys) {
		$params = array();
		// client_id and client secret are not required parameters for all grant type, according to 
		// OAuth2 draft 31, specified here for compatibility concern.(mainly because of sina implementation).
		$params['client_id'] = social::config($this->provider, 'app_key', false);
		$params['client_secret'] = social::config($this->provider, 'app_secret', false);
		switch ($grant_type) {
			case 'code':
				$grant_type = 'authorization_code';
				// intentionly pass down
			case 'authorization_code':
				$params['client_id'] = social::config($this->provider, 'app_key');
				$params['code'] = $keys['code'];
				$params['redirect_uri'] = $keys['redirect_uri'];
				break;
			case 'password':
				$params['username'] = $keys['username'];
				$params['password'] = $keys['password'];
				if (isset($keys['scope'])) {
					$params['scope'] = $keys['scope'];
				}
				break;
			case 'credentials':
				$grant_type = 'client_credentials';
			case 'client_credentials':
				if (isset($keys['scope'])) {
					$params['scope'] = $keys['scope'];
				}
				break;
			case 'token':
				$grant_type = 'refresh_token';
				// intentionly pass down
			case 'refresh_token':
				$params['refresh_token'] = $keys['refresh_token'];
				if (isset($keys['scope'])) {
					$params['scope'] = $keys['scope'];
				}
				break;
			default:
				throw new Kohana_Exception("core.invalid_parameter", "grant_type", __CLASS__, __FUNCTION__);
				break;
		}
		$params['grant_type'] = $grant_type;

		$request = new CurlRequest(social::api_url($this->provider, "Credential", self::ACCESS_TOKEN_URL), 'POST', $params);
		if (IN_PRODUCTION) {
			$request->ssl_verifypeer = TRUE;
		}
		$response = $request->send();
		if ($response === false || $request->http_code != 200) {
			Kohana::log("error", "Fail to get access token of {$this->provider}:{$request->url}, returned:({$request->http_code})".var_export($request->http_info, true));
			throw new UKohana_Exception('E_SOCIAL_REQUEST', "errors.request_failure");
		}
		$token = json_decode($response, true);
		if ( !is_array($token) ) {
			Kohana::log("error", "Invalid response from {$this->provider}:{$request->url}, returned $response");
			throw new UKohana_Exception('E_SOCIAL_REQUEST', "errors.request_failure");
		}
		if ( isset($token['error']) ) {
			Kohana::log("error", "Receive error from {$this->provider}:{$request->url}:".var_export($token, true));
			throw new UKohana_Exception('E_SOCIAL_REQUEST', "errors.request_failure");
		}
		
		$credential = new Credential($this->provider, $token['access_token'], TRUE);
		if (isset($token['expires_in'])) {
			$credential->tokenTimeout = time() + intval($token['expires_in'], 10);
		}
		if (isset($token['refresh_token'])) {
			$credential->secret = $token['refresh_token'];
		}
		return $credential;
	}
	
	public function validate($credential) {
		if (is_null($credential) || $this->provider != $credential->provider || !isset($credential->token)) {
			throw new Kohana_Exception("core.invalid_parameter", "credential", __CLASS__, __FUNCTION__);
		}
		
		$url = social::api_url($this->provider, "Credential", self::TOKEN_INFO_URL);
		if ($url == self::TOKEN_INFO_URL) {
			throw new Social_Exception('E_SOCIAL_UNSUPPORTED', "errors.request_unsupported", self::TOKEN_INFO_URL, $this->provider);
		}
		// TODO: Separated if, rewrite this part to support more provider.
		if ($this->provider != "sina") {
			throw new Social_Exception('E_SOCIAL_UNSUPPORTED', "errors.request_unsupported", self::TOKEN_INFO_URL, $this->provider);
		}
	
		$params = array();
		$params['access_token'] = $credential->token;
		$request = new CurlRequest($url, 'POST', $params);
		if (IN_PRODUCTION) {
			$request->ssl_verifypeer = TRUE;
		}
		$response = $request->send();
		if ($response === false || $request->http_code != 200) {
			Kohana::log("error", "Fail to get token info of {$this->provider}:{$request->url}, returned:({$request->http_code})".var_export($request->http_info, true));
			return false;
		}
		$token = json_decode($response, true);
		if ( !is_array($token) ) {
			Kohana::log("error", "Invalid response from {$this->provider}:{$request->url}, returned $response");
			return false;
		}
		if ( isset($token['error']) ) {
			Kohana::log("error", "Receive error from {$this->provider}:{$request->url}:".var_export($token, true));
			return false;
		}
		
		$credential->identity = $token['uid'];
		if (isset($token['expire_in'])) {
			// sina api fix
			$token['expires_in'] = $token['expire_in'];
		}
		$credential->tokenTimeout = time() + intval($token['expires_in'], 10);
		
		return true;
	}
	
	public function authenticate($step, $params = NULL) {
		$method = NULL;
		switch ($step) {
			case OAUTH2_STEP_REQUEST:
				$method = array($this, 'request');
				break;
			case OAUTH2_STEP_EXCHANGE:
				$method = array($this, 'exchange');
				break;
			case AUTH_VALIDATE_TOKEN:
				$method = array($this, 'validate');
				break;
			default:
				throw new Social_Exception('E_SOCIAL_UNSUPPORTED', "errors.request_unsupported", $step, $this->provider);
				break;
		}
		if ($method) {
			return call_user_func_array($method, $params);
		}
		return NULL;			
	}
	
	public function verify($application, $token, CurlRequest $request = NULL) {
		if (is_null($token)) {
			return false;
		}
		
		switch ($token->type) {
			case Token_Driver::BEARER_TOKEN_KEY:
				return true;
			case Token_Driver::MAC_TOKEN_KEY:
				if (is_null($request)) {
					$request = CurlRequest::current();
				}
				
				if (!isset($request->parameters[self::MACSIGNATURE_KEY])) {
					throw new Auth_Exception('E_SOCIAL_PARAMETER_REQUIRED', 'errors.parameter_required', self::MACSIGNATURE_KEY);
				}
				if (!isset($token->algorithm)) {
					throw new Auth_Exception('E_APP_UNSUPPORTED', 'errors.missing_algorithm_definition');
				}
				$algorithm = SignatureMethod_Driver::algorithm($token->algorithm);
				if (is_null($algorithm)) {
					throw new Auth_Exception('E_APP_UNSUPPORTED', 'errors.sign_algorithm_unsupported', $token->algorithm);
				}
				
				$key_components = array (self::MACKEY_KEY => $token->secret);
				return $algorithm->verify(
					$this->get_base_string($request), 
					$algorithm->generate_key($token->type, $key_components), 
					$request->parameters[self::MACSIGNATURE_KEY]);
			default:
				throw new Auth_Exception('E_APP_UNSUPPORTED', 'errors.token_type_unsupported', $token->type);
		}
	}
	
	protected function get_base_string($request) {
		// TODO: implement
		throw new UKohana_Excpetion('E_APP_UNIMPLEMENT', 'core.method_unimplement', __CLASS__, __FUNCTION__);
	}
}
?>