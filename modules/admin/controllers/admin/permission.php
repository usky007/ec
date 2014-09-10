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
class Permission_Controller extends Admin_Controller
{
	protected $current_page='/admin/permission';
	protected $benkend_privilege="BACKEND-Permission";
	
 	
	
	public function index()
	{
		return $this->record();
	}
	
	public function record()
 	{
 		$content = new Grid_Controller();
 		$content->set_addurl('permission/add');		
 		$content->set_pk('PermissionCode'); 		
 		//$content->set_form('action',"/admin/permission/record_del");
 		$content->add_field('label','PermissionCode',array('label'=>'CODE','order'=>true));
 		$content->add_field('label','PermissionName',array('label'=>'权限名'));
 		$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('link','PermissionCode',array('label'=>'操作','text'=>'修改','href'=>'/admin/permission/edit?code={0}'));
 		
 		
 		$mod = new Permission_Model();
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
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
 
 		$mod = new Permission_Model();
 		$source_record = $mod->where('PermissionCode',$code)->find()->as_array();
 		
  
 	 	
 		$content = new Form_Controller();
 		$content->set_form('action','editsave');
 		$act = "add";
 		if(is_null($code))
 		{
 			$content->add_field('input','PermissionCode',array('label'=>'CODE'));
 			
 		}
 		else
 		{
 			$act = "edit";
 			$content->add_field('label','PermissionCode',array('label'=>'CODE','moreHTML'=>'<input type="hidden" name="PermissionCode" value="'.$code.'">'));	
 			
 		}
 		
 		$content->add_field('input','PermissionName',array('label'=>'权限名','moreHTML'=>'<input type="hidden" name="act" value="'.$act.'">'));
 
 		$content = $content->view($source_record,false);
		$this->set_output(array('content'=>$content));
		
	}
	
 	
 	public function editsave()
 	{
 		$PermissionCode = isset ( $_POST['PermissionCode'] ) ? trim ( $_POST['PermissionCode'] ) : null;
 		$Permissionname = isset ( $_POST['PermissionName'] ) ? trim ( $_POST['PermissionName'] ) : null;
 		
 		$mod = new Permission_Model();
 		if($_POST['act']=='add')
 		{
 			$mod->PermissionCode = $PermissionCode;
 			$mod->PermissionName = $Permissionname;
 			$mod->save();
 		}
 		else
 		{
 			$mod->where('PermissionCode',$PermissionCode)->find();
 			if($mod->loaded())
 			{
 				$mod->PermissionName = $Permissionname;
 				$mod->save();	
 			}
 			else
 			{
 				echo "该记录不存在！";exit;
 			}
 			
 		}
 		$this->add_success_message('保存成功！');
 		header("Location: /admin/permission");
 	}
  	
 	
 	public function record_del()
 	{
 		$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();
 		if(count($ids)>0)
 		{
 			$db = new Database();
 			$db->from('permission')->in('PermissionCode',$ids)->delete();
 		}
 		$this->add_success_message('删除成功！');
 		header("Location: /admin/permission");
 	}
 	
 	
 	
 	
	function br2nl($message){
		$message = str_replace("<br>\n","\n",$message);
		$message = str_replace("\r\n\r\n","\n",$message);
		$message = str_replace("\n\r\n","\n",$message);
		return $message;
	}
 	
} // End Index_Controller
