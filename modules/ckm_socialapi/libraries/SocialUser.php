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
	const GET_URL = 'info';
	
	const PARAM_ID = 'identity';
	
	const FIELD_AVATAR = 'avatar';
	const FILED_IDENTITY = 'identity';
	const FIELD_NICKNAME = 'nickname';
	
	public static function factory(Credential $cred, $gateway = NULL)
	{
		return AuthorizedObject::factory($cred, $gateway, __CLASS__);
	}

	public function get($id) {
		return $this->load($this->http_get(self::GET_URL, array(self::PARAM_ID => $id)));
	}

	public function create_user($import_avatar = false) {
		if (!$this->loaded()) {
			$this->get($this->credential->identity);
		}

		$email = $this->credential->identity.'@'.$this->credential->provider;
		$user = new User_Model(array("email" => $email));
		if (!$user->find()->loaded()) {
			$info = array(
				'email' => $email,
				'password' => md5(uniqid(rand(), true)),
				'nickname' => $this[self::FIELD_NICKNAME]
			);
			$guest = Account::instance()->user;
			if ($guest !== NULL && $guest->is_guest() && !is_null($guest->guid)) {
				$info['guid'] = $guest->guid;
			}
			$user = User_Model::new_user($info);
		}
		$this->credential->user = $user;
				
		if($import_avatar && empty($user->avatar)){
			$user->avatar = $this->import_avatar();
			$user->save();
		}

		return $this->credential->user;
	}
	
	public function import_avatar() {
		if (!$this->loaded()) {
			$this->get($this->credential->identity);
		}
		
		if ( ! Kohana::auto_load('StorageFile')) {
			log::warn("No storage library found.");
			return "";
		}
		
		$avatar_url = $this[self::FIELD_AVATAR];
		$image = @file_get_contents($avatar_url);
		if (!$image) {
			log::error("can't load avatar at url ".$avatar_url);
			return "";
		}
		
		$path = $this->credential->user->get_avatar_upload_path($this->credential->provider);
		$storage_file = new StorageFile('avatar', $path, true);
		if ($storage_file->write($image) === false) {
			return "";
		}
		return $storage_file->path;
	}
	
	protected function get_api_category() {
		return __CLASS__;
	}
}