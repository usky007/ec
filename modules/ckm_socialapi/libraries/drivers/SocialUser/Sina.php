<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: SocialUser.php 2654 2011-06-21 02:34:29Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class SocialUser_Sina_Driver extends SocialUser
{
	const PARAM_NAME = "screen_name";

	public function get($id, $id_type = null) 
	{
		if (is_null($id_type)) {
			if (is_numeric($id)) {
				$id_type = SocialUser::PARAM_ID;
			}
			else {
				// Compatible with @username form.
				$id = preg_replace('/^@?(.+)$/', '\1', $id);
				$id_type = self::PARAM_NAME;
			}
		}
		$this->_object = $this->http_get(self::GET_URL, array($id_type => $id));
		return $this;
	}
	
	public function offsetGet($offset) {
		if ($offset == SocialUser::FIELD_AVATAR) {
			return str_replace('/50/', '/180/', parent::offsetGet($offset));
		}
		return parent::offsetGet($offset);
	}
}