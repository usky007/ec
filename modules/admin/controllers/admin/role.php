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
class Role_Controller extends Admin_Controller
{
	protected $current_page='/admin/role';
	protected $benkend_privilege="BACKEND-ROLE";
	
 	
	
	public function index()
	{
		return $this->record();
	}
	
	public function record()
 	{
 		$content = new Grid_Controller();
 		$content->set_addurl('role/add');		
 		$content->set_pk('ID'); 		
 		$content->set_form('action',"/admin/role/record_del");
 		$content->add_field('label','ID',array('label'=>'ID','order'=>true));
 		$content->add_field('label','RoleName',array('label'=>'角色名'));
 		$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('link','ID',array('label'=>'操作','text'=>'修改','href'=>'/admin/role/edit?id={0}'));
 		$content->add_field('link','ID',array('label'=>'操作','text'=>'设置权限','href'=>'/admin/role/set_permission?id={0}'));
 		
 		
 		
 		
 		$mod = new Role_Model();
 		$cnt =  $mod->count_all();
 		$order = isset($_GET['order'])?$_GET['order']:'';
 		$sort = isset($_GET['sort'])?$_GET['sort']:'asc';
 		if(!empty($order))
 		{
 			$mod = $mod->orderby($order,$sort);
 		}
 		
 		
 		$pagesize = 10 ;
 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize));
 
 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
 		$offset *= $pagesize;
 		$mod = $mod->limit($pagesize,$offset);
 		
 		
 		$records = $mod->find_all()->as_array();
 		
 		
 		$order = isset($_GET['order'])?$_GET['order']:"rid";
 		$sort = isset($_GET['sort'])?$_GET['sort']:"DESC";
 		
 		
 		$datas = $records;
 		
 		$content = $content->view($datas,false);
 		$this->set_output(array('pagenation'=>$pagenation,'content'=>$content));
	}
	
	public function add()
	{
		$this->edit();
		
	}
	public function edit()
	{
		$id = isset ( $_GET['id'] ) ? trim ( $_GET['id'] ) : null;
 
 		$mod = new Role_Model();
 		$source_record = $mod->where('ID',$id)->find()->as_array();
 		
  
 	 	
 		$content = new Form_Controller();
 		$content->set_form('action','editsave');
 		$content->add_field('hidden','ID',array('label'=>'ID'));
 		$content->add_field('input','RoleName',array('label'=>'角色名','moreHTML'=>'<input type="hidden" name="id" value="'.$id.'">'));
 
 		$content = $content->view($source_record,false);
		$this->set_output(array('content'=>$content));
		
	}
	
 	
 	public function editsave()
 	{
 		$id = isset ( $_POST['id'] ) ? trim ( $_POST['id'] ) : null;
 		$rolename = isset ( $_POST['RoleName'] ) ? trim ( $_POST['RoleName'] ) : null;
 		
 		$mod = new Role_Model();
 		if(empty($id))
 		{
 			$mod->ID = ID_Factory::next_id($mod);
 			$mod->RoleName = $rolename;
 			$mod->save();
 		}
 		else
 		{
 			$mod->where('ID',$id)->find();
 			if($mod->loaded())
 			{
 				$mod->RoleName = $rolename;
 				$mod->save();	
 			}
 			else
 			{
 				echo "该记录不存在！";exit;
 			}
 			
 		}
 		$this->add_success_message('保存成功！');
 		header("Location: /admin/role");
 	}
  	
 	
 	public function record_del()
 	{
 		$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();
 
 		foreach($ids as $id)
		{
			$role = new Role_Model();
			$role->where('ID',$id)->find()->delete();
		}
		$this->add_success_message('删除成功！');
 		header("Location: /admin/role");
 	}
 	
 	public function set_permission()
 	{
 		$roleid=isset ( $_GET['id'] ) ? trim ( $_GET['id'] ) : null;
 		$content = new Grid_Controller();	
 		$content->set_pk('PermissionCode');
 		//$content->set_form('action',"/admin/permission/record_del");
 		$content->add_field('label','PermissionCode',array('label'=>'CODE','order'=>true));
 		$content->add_field('label','PermissionName',array('label'=>'权限名'));
 		$content->add_field('label','hasPermission',array('label'=>'是否有该权限'));
 		$content->add_field('link','PermissionCode',array('label'=>'操作','text'=>'打开','href'=>'/admin/role/addp?roleid='.$roleid.'&code={0}'));
 		$content->add_field('link','PermissionCode',array('label'=>'操作','text'=>'关闭','href'=>'/admin/role/delp?roleid='.$roleid.'&code={0}'));
 		
 		$pmod = new Permission_Model();
 		$p_rows = $pmod->find_all();
 		
 		$rpmod = new RolePermissionIndex_Model();
 		$rp_rows = $rpmod->where(array('RoleID'=>$roleid))->find_all();
 		
 		
 		$rp_rcd = array();
 		foreach($rp_rows as $row)
 		{
 			$pcode = $row->PermissionCode;
 			$rp_rcd[] = $pcode;
 		}
 
 		
 		$records = array();
 		foreach($p_rows as $row)
 		{
 			$item =array();
 			$item['PermissionCode'] = $row->PermissionCode;
 			$item['PermissionName'] = $row->PermissionName;
 			$item['hasPermission'] = in_array($row->PermissionCode,$rp_rcd)?"<font style=\"color:#FF0000\">是</b>":"否";
 			$records[] = $item;
 		}
 		
 		$cnt = count($records);
 		
 	 	 
 		$pagenation = new Pagination(array('total_items'=>$cnt));
 		$pagesize = $cnt ;
 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
 		$offset *= $pagesize;
 		
 		$order = isset($_GET['order'])?$_GET['order']:"rid";
 		$sort = isset($_GET['sort'])?$_GET['sort']:"DESC";
 		
 		
 		$datas = $records;
 		
 		$content = $content->view($datas,false);
 		$this->set_output(array('pagenation'=>$pagenation,'content'=>$content));
 	}
 	
 	function addp()
 	{
 		$roleid=isset ( $_GET['roleid'] ) ? trim ( $_GET['roleid'] ) : null;
 		$pcode =isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
 		if(is_null($roleid) || is_null($pcode))
 			header("Location: /admin/role");
 		
 		$rpmod = new RolePermissionIndex_Model();
 		$rpmod->where(array('RoleID'=>$roleid,'PermissionCode'=>$pcode))->find();
 		if(!$rpmod->loaded())
 		{
 			$rpmod->ID = ID_Factory::next_id($rpmod);
 			$rpmod->RoleID = $roleid;
 			$rpmod->PermissionCode = $pcode;
 			$rpmod->save();
 		}
 		$this->add_success_message('打开权限'.$pcode);
 		header("Location: /admin/role/set_permission?id=$roleid");
 			
 	}
 	function delp()
	{
 		$roleid=isset ( $_GET['roleid'] ) ? trim ( $_GET['roleid'] ) : null;
 		$pcode =isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
 		if(is_null($roleid) || is_null($pcode))
 			header("Location: /admin/role");
 		$rpmod = new RolePermissionIndex_Model();
 		$rpmod->where(array('RoleID'=>$roleid,'PermissionCode'=>$pcode))->find();
 		if($rpmod->loaded())
 		{
 			$rpmod->where(array('RoleID'=>$roleid,'PermissionCode'=>$pcode))->delete();
 			//$rpmod->delete();
 		}
 		$this->add_success_message('关闭权限'.$pcode);
 		header("Location: /admin/role/set_permission?id=$roleid");
 	}
 	
	function br2nl($message){
		$message = str_replace("<br>\n","\n",$message);
		$message = str_replace("\r\n\r\n","\n",$message);
		$message = str_replace("\n\r\n","\n",$message);
		return $message;
	}
 	
} // End Index_Controller
