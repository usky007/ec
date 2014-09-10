<?php
/**
 * Class description.
 *
 * $Id: SocialAccount.php 2658 2011-06-23 06:53:18Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class SocialAccount extends AuthorizedObject {
//	const GET_URL = "/oauth/%s/account/profile.json";
	const GET_URL = "profile";

	public function get() {
		$this->_object = $this->http_get(self::GET_URL);
		return $this;
	}
}
?>