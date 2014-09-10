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
	
	const FILED_IDENTITY = 'identity';
	
	public function __construct(Credential $cred, $gateway = NULL)
	{
		parent::__construct($cred, $gateway);
	}

	public function get() {
		if (isset($this->credential->identity)) {
			return $this->load(array(self::FILED_IDENTITY => $this->credential->identity));
		}

		return $this->load($this->http_get(self::GET_URL));
	}
}
?>