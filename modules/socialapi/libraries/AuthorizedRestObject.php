<?php
/**
 * Class description.
 *
 * $Id: AuthorizedRestObject.php 2654 2011-06-21 02:34:29Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class AuthorizedRestObject extends AuthorizedObject {
	protected $res_url;

	public function __construct(Credential $cred, $res_url, $gateway = NULL) {
		parent::__construct($cred, $gateway);
		$this->res_url = $res_url;
	}

	public function get($parameter = array()) {
		return $this->http_get($this->res_url, $parameter);
	}

	public function add($parameter, $multipart = false) {
		return $this->http_post($this->res_url, $parameter, $multipart);
	}

	public function update($parameter, $multipart = false) {
		return $this->http_put($this->res_url, $parameter, $multipart);
	}

	public function delete($parameter = array()) {
		return $this->http_delete($this->res_url, $parameter);
	}
}
?>