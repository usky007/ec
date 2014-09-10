<?php
/**
 * 
 * @author cuiyulei
 *
 */
class User_Controller extends ServiceController
{
	public function is_login()
	{
		$act = Account::instance();
		$is_login = $act->checklogin(false);
		$this->set_output(array('is_login'=>$is_login));
	}
	
	public function is_join()
	{
		$act = Account::instance();
		$loginUser = $act->loginuser;
		if($loginUser->uid){
			$ui_mod = new UserInfos_Model();
			$join = $ui_mod->where('uid', $loginUser->uid)->find();
			if($join->loaded){
				$this->set_output(array('msg' => '您已经提交了用户信息'));
			}else{
				$this->set_output(array('msg' => '您还未提交用户信息'));
			}
		}else{
			throw  new Kohana_Exception('E_INVALID_PERMISSION', 'errors.no_permission');
		}
	}
	
	//我要参加
	public function join()
	{
		$act = Account::instance();
		$is_login = $act->checklogin(false);
		if($is_login)
		{
			$name = Input::instance()->query('name');
			$email = Input::instance()->query('email');
			$weibo = Input::instance()->query('weibo');
			$mobile = Input::instance()->query('mobile');
			if(empty($name)||empty($email))
			{
				throw new UKohana_Exception( 'E_INVALID_PARAMETER', "errors.request_failure");
			}else{
				if (!preg_match('/^.+@.+$/', $email))
				{
					throw new UKohana_Exception( 'E_INVALID_PARAMETER', "errors.email_format_is_wrong");
				}
				$loginUser = $act->loginuser;
				$userInfo_mod = new UserInfos_Model();
				$userInfo_mod->uid = $loginUser->uid;
				$userInfo_mod->name = trim($name);
				$userInfo_mod->email = trim($email);
				$userInfo_mod->weibo = $weibo;
				$userInfo_mod->mobile = $mobile;
				$userInfo_mod->save();
				$msg = array('uid'=>$loginUser->uid);
				$this->set_output($msg);
			}
		}else{
			throw  new Kohana_Exception('E_INVALID_PERMISSION', 'errors.no_permission');
		}
	}
	
	public function s_join()
	{
		$session = Session::instance();
		$name = trim(Input::instance()->query('name'));
		$email = trim(Input::instance()->query('email'));
		$weibo = trim(Input::instance()->query('weibo'));
		$mobile = trim(Input::instance()->query('mobile'));
		$userInfoModel = trim(Input::instance()->query('userinfo'));
		$submited = $session->get('submited');
		log::debug('submited='.$submited);
		if($submited){
			$msg = array('msg'=>'您已经提交了联系方式，请勿重复提交', 'submited'=>false);
		}else{
			if(empty($name) || empty($email)){
				$msg = array('msg'=>'非法操作，名字或者邮箱不能为空', 'submited'=>false);
			}else{
				if (!preg_match('/^.+@.+$/', $email))
				{
					$msg = array('msg'=>'非法操作，邮箱格式错误', 'submited'=>false);
				}else{
					$userInfo_mod = new $userInfoModel();
					$e_exist = $userInfo_mod->where('email', $email)->find();
					if($e_exist->loaded){
						$msg = array('msg'=>'您提交的邮箱已经存在，请更换一个邮箱', 'submited'=>false);
					}else{
						$userInfo_mod->uid = ID_Factory::next_id($userInfo_mod);
						$userInfo_mod->name = trim($name);
						$userInfo_mod->email = trim($email);
						$userInfo_mod->weibo = $weibo;
						$userInfo_mod->mobile = $mobile;
						$userInfo_mod->save();
						$session->set('submited', true);
						$msg = array('msg'=>'成功提交联系方式', 'submited'=>true);
					}	
				}
			}
		}
		$this->set_output($msg);
	}
}