<?php
/**
 * Class description.
 *
 * $Id: SocialUser.php 2654 2011-06-21 02:34:29Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class SocialUser extends AuthorizedObject {
	const GET_URL = "/users/show.json";

	const TYPE_ID = "uid";
	const TYPE_NAME = "screen_name";

	public function __construct(Credential $cred) {
		parent::__construct($cred, "sina");
	}

	public function get($id, $id_type = null) {
		if (is_null($id_type)) {
			if (is_numeric($id)) {
				$id_type = self::TYPE_ID;
			}
			else {
				// Compatible with @username form.
				$id = preg_replace('/^@?(.+)$/', '\1', $id);
				$id_type = self::TYPE_NAME;
			}
		}
		$this->_object = $this->http_get(self::GET_URL, array($id_type => $id));
		return $this;
	}
	
	public function create_user($import_avatar = false) {
		if (empty($this->_object)) {
			$this->get($this->credential->identity);
		}
		
		$info = array(
			'email' => $this->credential->identity.'@'.$this->credential->provider,
			'password' => md5(uniqid(rand(), true)),
			'nickname' => $this['screen_name']
		);
		$this->credential->user = User_Model::new_user($info);
		if ($import_avatar) {
			$info['avatar'] = $this->import_avatar();
		}
		return $this->credential->user;
	}
	
	public function import_avatar() {
		if (empty($this->_object)) {
			$this->get($this->credential->identity);
		}
		
		if (!is_callable(array("format", "get_local_storage_path"))) {
			log::warn("No storage fetch function specified.");
			return "";
		}
		$filename = "profile.jpg";
		$path = preg_replace('/\d{2}/', '/$0', (string)$this->credential->uid);
		$path = preg_replace('/(\/?)(\d+)$/', '$1A$2/', $path);
		$path = $this->credential->provider.'/avatar'.preg_replace('/^\/*/', '/', $path);
		$fullpath = format::get_local_storage_path($path, 'save');
		if (!@is_dir($fullpath) && !@mkdir($fullpath, 0744, true))
		{
			log::error("create ".$fullpath." failed.");
			return "";
		}
		
		$avatar_url = str_replace('/50/', '/180/', $this['profile_image_url']);
		$image = @file_get_contents($avatar_url);
		if (!$image) {
			log::error("can't load avatar at url ".$avatar_url);
			return "";
		}
		
		if (@file_put_contents($fullpath.$filename, $image) === false) {
			log::error("can't save avatar to path ".$fullpath.$filename);
			return "";
		}
		return $path.$filename;
	}
}