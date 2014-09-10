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
 * @author     maskxu
 */
class User_Controller extends Admin_Controller
{
	protected $current_page='/admin/user';
	protected $benkend_privilege="BACKEND-USER";
	
	/*public function __construct()
	{
		 
		$page = explode('/',$_SERVER['REQUEST_URI']);
		$page = $page[count($page)-1];
		$page = explode('?',$page);
		$page = $page[0];
		$page = explode('_',$page);
		$page = strtolower($page[0]);
		$this->_ini($page);
		parent::__construct();
	}*/
	
	private function _ini($function)
	{
 
	}
	
	
	
	public function index()
	{
		
		//echo "a";exit;
		return $this->record();
	}
	
	public function record()
 	{
 		$content = new Grid_Controller();
 		
 		$content->set_pk('uid'); 		
 		//$content->set_form('action',"/admin/user/record_del");
 		$content->add_field('label','uid',array('label'=>'rid','order'=>true));
 		$content->add_field('label','Username',array('label'=>'用户名'));
 		$content->add_field('label','Email',array('label'=>'电子邮件'));
 		//$content->add_field('label','Role',array('label'=>'角色'));
 		$content->add_field('label','GroupID',array('label'=>'小组'));
 		$content->add_field('label','enable',array('label'=>'是否有效'));
 		$content->add_field('label','updated',array('label'=>'更改日期'));
 		$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('link','uid',array('label'=>'操作','text'=>'修改','href'=>'/admin/user/edit?uid={0}'));
 		$content->add_field('link','uid',array('label'=>'操作','text'=>'设置角色','href'=>'/admin/user/setrole?uid={0}'));
 		
 		
 		//$tagname_values = $this->tag_select();
 		//$content->add_filter('select','tag',array('method'=>"where"),array('label'=>'标签','values'=>$tagname_values,'defVal'=>''));

 		//$filterdata = $content->get_filter_data();
 		$order = isset($_GET['order'])?$_GET['order']:'';
 		$sort = isset($_GET['sort'])?$_GET['sort']:'asc';
 		
 		$usermod = new User_Model();
 		$cnt =  $usermod->count_all();
 		
 		$pagesize = 10 ;
 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize));
 
 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
 		$offset *= $pagesize;
 		
 		if(!empty($order))
 		{
 			$usermod = $usermod->orderby($order,$sort);
 		}
 		
 		
 		$usermod = $usermod->limit($pagesize,$offset);
 		
 		$records = $usermod->find_all()->as_array();
 		
 		//var_dump($usermod->last_query());exit;
 		$groupAry = $this->_get_group_array();
 	 
		foreach($records as $record)
		{

			$record->GroupID = isset($groupAry[$record->GroupID])?$groupAry[$record->GroupID]:"";
			$record->enable = $record->enable>0?'有效':'<font  color="#F00">无效</font>';
		}
 		
 		
 		
 		
 	 	 
 		
 		
 		$order = isset($_GET['order'])?$_GET['order']:"rid";
 		$sort = isset($_GET['sort'])?$_GET['sort']:"DESC";
 		
 		
 		$datas = $records;
 		
 		
 
 		$content = $content->view($datas,false);
// 				$this->set_view('allguides')->set_menu(self::MENU_ALLGUIDE)
//					->set_output(array( 'guides' => $guides,
//										'show' => $showall,
//					                    'orderby' => $orderby));
 		$this->set_output(array('pagenation'=>$pagenation,'content'=>$content));
	}
 	
 	private function _get_group_array()
 	{
 		$group=new Group_Model();
 		$rows = $group->find_all();
 		$rst = array();
 		foreach($rows as $row)
 		{
 			$rst[$row->ID] = $row->GroupName;
 		}
 		return $rst;
 	}
 
 	
	public function record_del()
 	{
 
 		$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();
 		foreach($ids as $id)
 		{
 			$user = new User_Model();
 			if($user->where("uid",$id)->find()->loaded())
 				$user->delete();
 		}
 		header("Location: /admin/user/record");
 		exit;
 	}
 	
 
	private function _get_group_select()
 	{
 		$gpmod = new Group_Model();
 		$datas = $gpmod->find_all();
 		$tagname_values = array();
 		foreach($datas as $dt)
 		{
 			$tagname_values[] = array('label'=>$dt->GroupName,'value'=>$dt->ID);
 		}
 		return $tagname_values;
 	}
	private function _get_role_select()
 	{
 		$rolemod = new Role_Model();
 		$datas = $rolemod->find_all();
 		$tagname_values = array();
 		foreach($datas as $dt)
 		{
 			$tagname_values[] = array('label'=>$dt->RoleName,'value'=>$dt->ID);
 		}
 		return $tagname_values;
 	}
	private function _get_enable_select()
 	{
 		$rst[] = array('label'=>"有效",'value'=>1);
 		$rst[] = array('label'=>"无效",'value'=>0);
 		return $rst;
 	}
 	
 	
	public function edit()
 	{
 		$uid = isset ( $_GET['uid'] ) ? trim ( $_GET['uid'] ) : null;
 
 		$usermod = new User_Model();
 		$source_record = $usermod->where('uid',$uid)->find()->as_array();
 		
  
 	 	
 		$content = new Form_Controller();
 		$content->set_form('action','editsave');
 		
 		$content->add_field('input','Username',array('label'=>'用户名','moreHTML'=>'<input type="hidden" name="id" value="'.$uid.'">'));
 		$content->add_field('input','Email',array('label'=>'电子邮件'));
 		$content->add_field('select','GroupID',array('label'=>'小组','values'=>$this->_get_group_select(),'emptyValue'=>0,'defVal'=>''));
 		$content->add_field('select','enable',array('label'=>'是否有效','values'=>$this->_get_enable_select(),'defVal'=>''));
 		$content->add_field('input','updated',array('label'=>'更改时间','readonly'=>true));
 		$content->add_field('input','created',array('label'=>'创建时间','readonly'=>true));
 
 		$content = $content->view($source_record,false);
		$this->set_output(array('content'=>$content));
 
 	}
 	
 	public function editsave()
 	{
 		$id = isset ( $_POST['id'] ) ? trim ( $_POST['id'] ) : null;
 		$username = isset ( $_POST['Username'] ) ? trim ( $_POST['Username'] ) : null;
 		$email =  isset ( $_POST['Email'] ) ? trim ( $_POST['Email'] ) : null;
 		$groupID =  isset ( $_POST['GroupID'] ) ? trim ( $_POST['GroupID'] ) : null;
 		$enable = isset ( $_POST['enable'] ) ? trim ( $_POST['enable'] ) : 1;
 		$mod = new User_Model();
 		if($mod->where('Username',$username)->find()->loaded())
 		{
 			if($mod->uid != $id)
 			{
 			$this->add_error_message('存在相同用户名！');
 			echo "<script>history.back();</script>";exit;}
 			//header("Location: /admin/user/edit?uid=".$id);
 		}
 		$mod = new User_Model();
 		if(!empty($id))
 		{
 			$mod->where('uid',$id)->find();
 			if($mod->loaded())
 			{
 				$mod->Username = $username;
 				$mod->Email = $email;
 				$mod->GroupID = $groupID;
 				$mod->enable = $enable;
 				$mod->save();	
 			}
 			else
 			{
 				echo "该记录不存在！";exit;
 			}
 			
 		}
 		$this->add_success_message('保存成功！');
 		header("Location: /admin/user");
 	}
 	
 	public function setrole()
 	{
 		$uid = isset ( $_GET['uid'] ) ? trim ( $_GET['uid'] ) : null;
 		
 		$uridx =new UserRoleIndex_Model();
 		$rows = $uridx->where('UserID',$uid)->find_all();
 		$hasRoles =array();
 		foreach($rows as $row)
 		{
 			$hasRoles[] = $row->RoleID;
 		}
 		
 		$content = new Grid_Controller();
 		$content->set_pk('ID'); 		
 		$content->set_form('action',"/admin/user/setrolesave/$uid");
 		$content->add_field('label','ID',array('label'=>'ID','order'=>true));
 		$content->add_field('label','RoleName',array('label'=>'角色名'));
 		$content->set_delbtnval('确定');
 		$content->set_checkrows($hasRoles);
 		
 		$rolemod = new Role_Model();
 		$records = $rolemod->find_all()->as_array();
 		 
 
 		$datas = $records;
 		
 		
 
 		$content = $content->view($datas,false);
 		$this->set_output(array('pagenation'=>null,'content'=>$content));
 	}
 	
 	public function setrolesave($uid)
 	{
 		$db = new Database();
 		$db->where('UserID',$uid)->from('UserRoleIndex')->delete();
 		
 		
 		$roleids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();
  		foreach($roleids as $roleid)
  		{
  			$uridx =new UserRoleIndex_Model();
  			$uridx->ID = ID_Factory::next_id($uridx);
  			$uridx->UserID=$uid;
  			$uridx->RoleID=$roleid;
  			$uridx->save();
  		}
 		$this->add_success_message('设置成功！');
 		header("Location: /admin/user/record");
 		
 	}
 	
	function br2nl($message){
		$message = str_replace("<br>\n","\n",$message);
		$message = str_replace("\r\n\r\n","\n",$message);
		$message = str_replace("\n\r\n","\n",$message);
		return $message;
	}
 	
} // End Index_Controller
