<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * OAuth 1.0 authentication method driver.
 *
 * $Id: Abstract.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    socialapi
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2012 UUTUU
 */
 
define('OAUTH_STEP_REQUEST', 1);
define('OAUTH_STEP_EXCHANGE', 2);

class AuthMethod_OAuth_Driver implements AuthMethod_Driver {
	const SHARED_SECRET_KEY = "shared_secret";
	const TOKEN_SECRET_KEY = "oauth_token_secret";
	const SIGNATURE_METHOD_KEY = "oauth_signature_method";
	const SIGNATURE_KEY = "oauth_signature";

	const REQUEST_TOKEN_URL = "request_token";
	const AUTHORIZE_URL = "authenticate";
	const ACCESS_TOKEN_URL = "access_token";

	protected $provider = NULL;
	
	private $last_credential = NULL;
	private $backend = NULL;

	public function __construct($provider)
	{
		$this->provider = $provider;

		Kohana::log('debug', 'OAuth Authentication Method Driver Initialized');
	}
	
	public function request($callback, $return = false) {
		$lib = $this->get_client_lib();
		$response = $lib->oAuthRequest(social::api_url($this->provider, "Credential", self::REQUEST_TOKEN_URL), "GET", array("oauth_callback" => $callback));
		if ($lib->http_code != 200) {
			Kohana::log("error", "Fail to request token of {$this->provider}:{$lib->url}, returned:({$lib->http_code})".var_export($lib->http_info, true));
			throw new UKohana_Exception(E_SOCIAL_REQUEST, "errors.request_failure");
		}

		$token = OAuthUtil::parse_parameters($response);
		if (!isset($token['oauth_token']) || !isset($token['oauth_token_secret'])) {
			Kohana::log("error", "Invalid response from {$this->provider}:{$lib->url}, returned $response");
			throw new UKohana_Exception(E_SOCIAL_REQUEST, "errors.request_failure");
		}

		$cache = &Cache::instance("OAuth");
		$cache->set($token['oauth_token'], $token['oauth_token_secret']);
		$url = social::api_url($this->provider, "Credential", self::AUTHORIZE_URL)."?oauth_token={$token['oauth_token']}";
		if ($return) {
			return $url;
		}
		else {
			url::redirect($url);
		}
	}
	
	public function exchange($request_token, $verifier) {
		$cache = &Cache::instance("OAuth");
		$secret = $cache->get($request_token);
		if (is_null($secret)) {
			Kohana::log("error", "Invalid request token from $request_token");
			throw new UKohana_Exception(E_SOCIAL_REQUEST, "errors.request_failure");
		}
		$cache->delete($request_token);

		$lib = $this->get_client_lib();
		$lib->token = new OAuthConsumer($request_token, $secret);
		$response = $lib->oAuthRequest(social::api_url($this->provider, "Credential", self::ACCESS_TOKEN_URL), "GET", array("oauth_verifier" => $verifier));
		if ($lib->http_code != 200) {
			Kohana::log("error", "Fail to exchange access token of {$this->provider}:{$lib->url}, returned:({$lib->http_code})".var_export($lib->http_info, true));
			throw new UKohana_Exception(E_SOCIAL_REQUEST, "errors.request_failure");
		}

		$token = OAuthUtil::parse_parameters($response);
		if (!isset($token['oauth_token']) || !isset($token['oauth_token_secret'])) {
			Kohana::log("error", "Invalid response from {$this->provider}:{$lib->url}, returned $response");
			throw new UKohana_Exception(E_SOCIAL_REQUEST, "errors.request_failure");
		}

		$credential = new Credential($this->provider, $token['oauth_token'], TRUE);
		$credential->secret = $token['oauth_token_secret'];
		return $credential;
	}
	
	public function authenticate($step, $params = NULL) {
		$method = NULL;
		switch ($step) {
			case OAUTH_STEP_REQUEST:
				$method = array($this, 'request');
				break;
			case OAUTH_STEP_EXCHANGE:
				$method = array($this, 'exchange');
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
	
	public function send(CurlRequest $request, Credential $credential) {
		if (!$request) {
			throw new Kohana_Exception("core.invalid_parameter", "request", __CLASS__, __FUNCTION__);
		}
		if (!$credential || !$credential->is_valid() || !$credential->is_authorized()) {
			throw new Kohana_Exception("core.invalid_parameter", "credential", __CLASS__, __FUNCTION__);
		}
	
		$lib = $this->get_client_lib($credential);
		$lib->connecttimeout = $request->connecttimeout;
		$lib->timeout = $request->timeout;

		$response = $this->backend->oAuthRequest($request->url, $request->method, $request->parameters , $request->multi);
        $request->http_code = $lib->lastStatusCode();
        $request->format = $lib->format;
        $request->http_info = $lib->http_info;
        return $response;
	}
	
	public function verify($application, $token, CurlRequest $request = NULL) {
		if (is_null($application) || is_null($token)) {
			return false;
		}
		
		if (is_null($request)) {
			$request = CurlRequest::current();
		}
		
		if (!isset($request->parameters[self::SIGNATURE_METHOD_KEY])) {
			throw new Auth_Exception('E_SOCIAL_PARAMETER_REQUIRED', 'errors.parameter_required', self::SIGNATURE_METHOD_KEY);
		}
		if (!isset($request->parameters[self::SIGNATURE_KEY])) {
			throw new Auth_Exception('E_SOCIAL_PARAMETER_REQUIRED', 'errors.parameter_required', self::SIGNATURE_KEY);
		}
		
		$sign_method = $request->parameters['oauth_signature_method'];
		$algorithm = SignatureMethod_Driver::algorithm($sign_method);
		if (is_null($algorithm)) {
			throw new Auth_Exception('E_APP_UNSUPPORTED', 'errors.sign_algorithm_unsupported', $sign_method);
		}
		
		$key_components = array (self::SHARED_SECRET_KEY => $application->secret);
		if (!is_null($token)) {
			$key_components[self::TOKEN_SECRET_KEY] = $token->secret;
		}
		return $algorithm->verify(
			$this->get_base_string($request), 
			$algorithm->generate_key(Token_Driver::OAUTH_TOKEN_KEY, $key_components), 
			$request->parameters[self::SIGNATURE_KEY]);
	}
	
	protected function get_base_string($request) {
		// TODO: implement
		throw new UKohana_Excpetion('E_APP_UNIMPLEMENT', 'core.method_unimplement', __CLASS__, __FUNCTION__);
	}
	
	protected function get_client_lib($credential = NULL) {
		
		if (!$this->backend) {	
			$path = Kohana::find_file ( 'vendor', 'sina/Weibooauth' );
			ini_set ( 'include_path', ini_get ( 'include_path' ) . PATH_SEPARATOR . dirname ( dirname ( $path ) ) );
	
			require_once('sina/Weibooauth.php');
		}
		
		if (!$this->backend || $credential !== $this->last_credential) {
			if ($credential) {
				$this->backend = new WeiboOAuth(
					social::config($this->provider, 'app_key'), 
					social::config($this->provider, 'app_secret'), 
					$credential->token, $credential->secret);
			}
			else {
				$this->backend = new WeiboOAuth(
					social::config($this->provider, 'app_key'), 
					social::config($this->provider, 'app_secret'));
			}
			$this->last_credential = $credential;
		}
		return $this->backend;
	}
}
?>