<?php
class Account_Controller extends ServiceController
{
	public function login()
	{
		//throw new UKohana_Exception(Exception::code('E_USER_NOT_FOUND'), "errors.not_login");
//		  $msg = array('stuts'=>'ok');
//		$this->set_output($msg);
	 
		$user = new Account();
		$rst = false;
		$email = Input::instance()->query('email');
		$password = Input::instance()->query('password');
		try{
			$rst = $user->login($email,md5($password),1);
		}
		catch(UKohana_Exception $ex)
		{
			throw new UKohana_Exception('E_USER_NOT_FOUND', "errors.login_invalid_input");
		}
		return $rst;
	}	
	
	public function verify_user()
	{
		$ary = array('email' => $_POST['email']);
	
		$user = new User_Model();
		if($user->find($ary)->loaded()) //找到此用户
		{
			throw new UKohana_Exception(Exception::code('E_USER_NOT_FOUND'), "errors.login_invalid_input");
		}
		
	}
	
	public function checkuser()
	{
		$email = Input::instance()->post('email');
		if(isset($email))
		{
			$user = new User_Model();
			$user = $user->find(array('email'=>$email));
			if($user->loaded())
			{
				throw new UKohana_Exception('E_USER_REGISTER_FAILED', "errors.user_name_is_not_legitimate");
			}
		}
		else 
		{
			throw new UKohana_Exception('E_USER_REGISTER_FAILED',"api.user_name_already_exists");
		}
	}
	
	public function signup()
	{
		$password = Input::instance()->post('password');
		$email = Input::instance()->post('email');
		$nickname = Input::instance()->post('nickname');

		if(empty($password)||empty($email)||empty($nickname))
		{
			throw new UKohana_Exception( 'E_APP_INVALID_PARAMETER ', "errors.register_error");
		}
		else 
		{
			if (!preg_match('/^.+@.+$/',$email))
			{
				throw new UKohana_Exception( 'E_APP_INVALID_PARAMETER' , "errors.email_format_is_wrong");
			} 
			$user = new User_Model();
			if($user->where('email',$email)->find()->loaded())
			{
				
				throw new UKohana_Exception( 'E_USER_ACCOUNT_UNAVAILABLE' , "errors.email_has_been_registered");
			}
			
			
	
			$user = User_Model::new_user(array(
				'email' => $email,
				'password' => md5($password),
				'nickname' => trim($nickname)
			));
			
			$uid = $user->uid;
			
			$session = Session::instance();
			$session->set('uid',$uid);
			$data = array("registered"=>true);
			Event::run("account.on_login", $data);
			$msg = array('uid'=>$uid);
			$this->set_output($msg);
		}
	}
}