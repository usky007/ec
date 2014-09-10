<?php
/**
 * re001 Project
 *
 * LICENSE
 *
 * http://www.re001.com/license.html
 *
 * @category   re001
 * @package    ChangeMe
 * @copyright  Copyright (c) 2010 re001 Team.
 * @author     jacky ( GTalk: wanderer.yinxb@gmail.com )
 * @since      Jul 31, 2011
 * @version    SVN: $Id: index.php 237 2011-09-18 14:52:40Z weiyin $
 */

 class Admin_Controller extends LayoutController
{
 	protected $current_page;
	protected $auto_render = TRUE;
 	protected $content_view = null;
 	protected $content_output =array();
 	protected $benkend_privilege="";
	protected $js = array();
	
	public function __construct()
	{
		parent::__construct();
		
		AppLayout_View::set_layout("layouts/admin");
		$this->positions = array(
				"leftbar" => ""
		);
		$this->set_contentview('admin/content');
		$this->get_layout()->add_component('leftbar', $this->leftbar(), 0);
	}
	
	public function add_css($css_filename)
	{
		$this->get_layout()->add_css($css_filename);	
	}
	
	public function add_js($js_filename)
	{
		$this->get_layout()->add_js($js_filename);
	}
	
	public function set_contentview($filename)
	{
		$this->set_view($filename);
	}


// 	public function __construct()
// 	{
// 		//$act = new Account();
// 		//$act->checklogin();

// //		$privelege = new Privilege();
// //		$hasp = $privelege->hasPermission($act->loginuser->uid,'MENU_BACKEND');
// //		if(!$hasp)
// //			throw new UKohana_Exception(E_TOP100_NO_PRIVILEGE, "errors.have not benkend privelege");

// //		if(!empty($this->benkend_privilege))
// //		{
// //			$hasp = $privelege->hasPermission($act->loginuser->uid,$this->benkend_privilege);
// //			if(!$hasp)
// //				throw new UKohana_Exception(E_TOP100_NO_PRIVILEGE, "errors.have not the benkend privelege of this moudule");
// //		}


// 		//
// 		parent::__construct();


// 		Context_Input::instance();
// 		if ($this->auto_render == TRUE)
// 		{
// 			// Render the template immediately after the controller method
// 			Event::add('system.post_controller_constructor', array($this, '_buffer'));
// 			Event::add('system.post_controller', array($this, '_render'));
// 		}
// 		////$this->session = Session::instance();
// 		$this->view = new View("layouts/admin");
// 		$this->view->leftbar = $this->leftbar();
// 		$this->set_contentview('admin/content');
// 	}

// 	public function add_js($js_filename)
// 	{
// 		$layout = new AppLayout_View();
// 		$js_src = $layout->resource_path($js_filename);
// 		$this->js[] = "<script type='javascript' src='".$js_src."'></script>\r\n";
// 	}

 	public function add_success_message($message)
 	{
 		$session = Session::instance();
 		$success_msg = $session->set('backend_success_msg',$message);
 	}

 	public function add_error_message($message)
 	{
 		$session = Session::instance();
 		$success_msg = $session->set('backend_error_msg',$message);
 	}
  	public function index()
  	{
  		$content = new Grid_Controller();
 		$content = '';
 		$pagenation = null;
  		$this->set_output(array('pagenation'=>$pagenation,'content'=>$content));
  	}
// 	public function set_contentview($filename)
// 	{
// 		$this->content_view = $filename;
// 		return $this;
// 	}

// 	protected function set_output($data, $reset = false) {
// 		if (is_null($data)) {
// 			$data = array();
// 		}
// 		elseif (is_object($data)) {
// 			$data = get_object_vars($data);
// 		}
// 		elseif (!is_array($data)) {
// 			$data = array("data"=>$data);
// 		}

// 		if ($reset) {
// 			$this->content_output = $data;
// 		}
// 		else {
// 			$this->content_output = array_merge($this->content_output, $data);
// 		}
// 		return $this;
// 	}
// 	public function _buffer()
// 	{
// 		ob_start();
// 	}

// 	/**
// 	 * Render the loaded template.
// 	 */
// 	public function _render($return = FALSE)
// 	{
// 		$buffer = ob_get_clean();
// //		$buffer = "";

// 		$cv = new View($this->content_view);


// 		foreach($this->content_output as $key=>$val)
// 		{
// 			$cv->$key = $val;

// 		}


// 		//$content = new Admin_Controller();
// 		//var_dump(Kohana::$instance);exit;

// 		$this->view->content = $cv->render(false);
// 		$this->view->js = '';
// 		foreach ($this->js as $js){
// 			$this->view->js .= $js;
// 		}
// 		$this->view->render(true);

// 	}


	protected function leftbar()
	{
		$menus = config::item('admin.acl',false,array());
 		//$privilege = new Privilege();
		$html ="";

//		$act = new Account();
// 		$login_uid = $act->loginuser->uid;

		foreach($menus as $item)
		{
//			$permissionCode = isset($item['permissionCode'])?$item['permissionCode']:'';
//			if(!empty($permissionCode))
//			{
//				if(!$privilege->hasPermission($login_uid,$permissionCode,null,false))
//				{
//					continue;
//				}
//			}


			$html .= $item['link']==$this->current_page?'<li class="active">':'<li>';
			$html .= '<a class="link" href="'.$item['link'].'">'.$item['label'].'</a>';
			$html .= '</li>';
		}
		return $html;
	}




} // End Index_Controller



