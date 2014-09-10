<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Http 请求类，用于请求
 *
 * 授权机制说明请大家参考微博开放平台文档：{@link http://open.weibo.com/wiki/Oauth2}
 *
 * @package sae
 * @author Elmer Zhang
 * @version 1.0
 */
class CurlRequest {
	/**
	 * Contains the last HTTP status code returned.
	 *
	 * @ignore
	 */
	public $http_code;
	/**
	 * Contains the last API call.
	 *
	 * @ignore
	 */
	public $url;
	/**
	 * Method
	 */
	public $method;
	/**
	 * Parameters
	 */
	public $parameters;
	/**
	 * Headers
	 */
	public $headers = array();
	/**
	 * Multi
	 */
	public $multi = false;
	/**
	 * Set timeout default.
	 *
	 * @ignore
	 */
	public $timeout = 30;
	/**
	 * Set connect timeout.
	 *
	 * @ignore
	 */
	public $connecttimeout = 30;
	/**
	 * Verify SSL Cert.
	 *
	 * @ignore
	 */
	public $ssl_verifypeer = FALSE;
	/**
	 * Respons format.
	 *
	 * @ignore
	 */
	public $format = 'json';
	/**
	 * Contains the last HTTP headers returned.
	 *
	 * @ignore
	 */
	public $http_info;
	/**
	 * Set the useragnet.
	 *
	 * @ignore
	 */
	public $useragent = 'UUTUU';

	/**
	 * print the debug info
	 *
	 * @ignore
	 */
	public $debug = FALSE;

	/**
	 * boundary of multipart
	 * @ignore
	 */
	public static $boundary = '';

	/**
	 * Generate a CurlRequest object representing current PHP script/request.
	 */
	public static function current() {
		$input = &Input::instance();
		$url = ($input->is_ssl() ? "https://" : "http://") . $input->server("HTTP_HOST");
		if ($input->server("SERVER_PORT") != 80) {
			$url .= ':'. $input->server("SERVER_PORT");
		}
		$url .= $input->server("REQUEST_URI");
		return new CurlRequest($url, $input->server("REQUEST_METHOD"), $input->query());
	}

	/**
	 * construct Request object
	 */
	function __construct($url, $method, $parameters , $multi = false ) {
		$this->url = $url;
		$this->method = $method;
		$this->parameters = $parameters;
		$this->multi = $multi;
	}

	/**
	 * Format and sign an OAuth / API request
	 *
	 * @return string
	 * @ignore
	 */
	function send() {
		$headers = $this->headers;
		switch ($this->method) {
			case 'GET':
				$url = $this->url;
				if (!empty($this->parameters)) {
					$url .= '?' . http_build_query($this->parameters);
				}
				Kohana::log("debug", "Authenticated call: $url,".json_encode($headers));
				return $this->http($url, 'GET', NULL, $headers);
			default:
				if (!$this->multi && (is_array($this->parameters) || is_object($this->parameters)) ) {
					$body = http_build_query($this->parameters);
				} else {
					$body = self::build_http_query_multi($this->parameters);
					$headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
				}
				return $this->http($this->url, $this->method, $body, $headers);
		}
	}

	/**
	 * Make an HTTP request
	 *
	 * @return string API results
	 * @ignore
	 */
	protected function http($url, $method, $postfields = NULL, $headers = array()) {
		$this->http_info = array();
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
 		$ssl_ca_path = config::item("socialapi.ssl_ca_path",null);

		if(!empty($ssl_ca_path))
		{
			curl_setopt($ci, CURLOPT_CAINFO, $ssl_ca_path); //"c:/path/to/ca-bundle.crt"
		}

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
					$this->postdata = $postfields;
				}
				break;
			case 'PUT':
				// Can't use "curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'PUT')", seems postfields not passed.
				$headers[] = 'X-HTTP-Method-Override: PUT';
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
					$this->postdata = $postfields;
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
				break;
		}

		$headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->http_info['post_data'] = $postfields;
		$this->http_info['response'] = $response;
		$this->url = $url;

		if ($this->debug) {
			echo PHP_EOL, "=====post data======", PHP_EOL;
			var_dump($postfields);

			echo PHP_EOL, '=====info=====', PHP_EOL;
			print_r( curl_getinfo($ci) );

			echo PHP_EOL, '=====$response=====', PHP_EOL;
			print_r( $response );
		}
		curl_close ($ci);
		return $response;
	}

	/**
	 * Get the header info to store.
	 *
	 * @return int
	 * @ignore
	 */
	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}

	/**
	 * @ignore
	 */
	public static function build_http_query_multi($params) {
		if (!$params) return '';

		uksort($params, 'strcmp');

		$pairs = array();

		self::$boundary = $boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

		foreach ($params as $parameter => $value) {

			if( in_array($parameter, array('pic', 'image')) && $value{0} == '@' ) {
				$url = ltrim( $value, '@' );
				$content = file_get_contents( $url );
				$array = explode( '?', basename( $url ) );
				$filename = $array[0];

				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
				$multipartbody .= "Content-Type: image/unknown\r\n\r\n";
				$multipartbody .= $content. "\r\n";
			} else {
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
				$multipartbody .= $value."\r\n";
			}

		}

		$multipartbody .= $endMPboundary;
		return $multipartbody;
	}
}
