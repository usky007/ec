<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * URL helper class.
 *
 * $Id: U_url.php 1579 2012-08-13 06:54:36Z xuronghua $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class user
{
	public static function avatar($user, $default = "", $width=null,$height=null)
	{
		if (is_null($user) || empty($user->avatar)) {
			return $default;
		}
		return format::get_local_storage_url($user->avatar,'avatar',$width,$height);
	}
	
	public static function get_information()
	{
		return md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
	}
}