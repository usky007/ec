<?php defined ( 'SYSPATH' ) or die ( 'No direct access allowed.' );
/**
 * Class description.
 *
 * $Id: login.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */
class Login_Controller extends LayoutController{


	public function __construct() 
	{
		parent::__construct();
		
		AppLayout_View::set_layout("layouts/login");
		$this->positions = array();
	}
	public function index()
	{
		url::redirect("http://ditu.uutuu.com/login");
	}
	
	public function logout()
	{
		$return = Input::instance()->query("return");
		if ($return == null) {
			$return = "";
		}
	
		Account::instance()->logout();
		url::redirect($return);
	}
	
	public function signup()
	{
		$return = Input::instance()->query("return");
		
		if ($return == null) {
			$return = "";
		}
		
		if(Account::instance()->checklogin(false))
		{
			url::redirect($return);
		}
	
		$this->set_view("login/signup");
		$this->get_layout()
			 ->add_css("central.css")
			 ->set_title(kohana::lang('titles.signup'))
			 ->add_js("js/central/signup.js");
		$this->set_output(array(
			"sina_authorize_url" => url::site("/social/authorize/sina?return=".urlencode($return)),
			"login_url" => url::site("/signup?return=".urlencode($return))
			));
			
		$this->set_js_context('gotourl',$return);
		$this->set_output(array('allowregister'=> config_Core::item('account.allow_register')));
	}
	
	public function __call($method, $args)
	{
		return $this->index();
	}
	
}
