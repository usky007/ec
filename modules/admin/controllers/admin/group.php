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
class Group_Controller extends Admin_Controller
{
	protected $current_page='/admin/group';
 	protected $benkend_privilege="BACKEND-GROUP";
	
	public function index()
	{
		return $this->record();
	}
	
	public function record()
 	{
 		$content = new Grid_Controller();
 		$content->set_addurl('group/add');		
 		$content->set_pk('ID'); 		
 		$content->set_form('action',"/admin/group/record_del");
 		$content->add_field('label','ID',array('label'=>'ID','order'=>true));
 		$content->add_field('label','GroupName',array('label'=>'组名'));
 		$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('link','ID',array('label'=>'操作','text'=>'修改','href'=>'/admin/group/edit?id={0}'));
 		$content->add_field('link','ID',array('label'=>'操作','text'=>'设置组长','href'=>'/admin/group/setadmin?gid={0}'));
 		
 		$mod = new Group_Model();
 		$cnt =  $mod->count_all();
 		$order = isset($_GET['order'])?$_GET['order']:'';
 		$sort = isset($_GET['sort'])?$_GET['sort']:'asc';
 		if(!empty($order))
 		{
 			$mod = $mod->orderby($order,$sort);
 		}
 		
 		$pagenation = new Pagination(array('total_items'=>$cnt));
 		$pagesize = 10 ;
 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
 		$offset *= $pagesize;
 		$mod = $mod->limit($pagesize,$offset);
 		
 		
 		$records = $mod->find_all()->as_array();
 		
 		$pagesize = 10 ;
 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize));
 		
 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
 		$offset *= $pagesize;
 		
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
 
 		$mod = new Group_Model();
 		$source_record = $mod->where('ID',$id)->find()->as_array();
 		
  
 	 	
 		$content = new Form_Controller();
 		$content->set_form('action','editsave');
 		$content->add_field('hidden','ID',array('label'=>'ID'));
 		$content->add_field('input','GroupName',array('label'=>'组名','moreHTML'=>'<input type="hidden" name="id" value="'.$id.'">'));
 
 		$content = $content->view($source_record,false);
		$this->set_output(array('content'=>$content));
		
	}
	
 	
 	public function editsave()
 	{
 		$id = isset ( $_POST['id'] ) ? trim ( $_POST['id'] ) : null;
 		$groupname = isset ( $_POST['GroupName'] ) ? trim ( $_POST['GroupName'] ) : null;
 		
 		$mod = new Group_Model();
 		if(empty($id))
 		{
 			$mod->ID = ID_Factory::next_id($mod);
 			$mod->GroupName = $groupname;
 			$mod->save();
 		}
 		else
 		{
 			$mod->where('ID',$id)->find();
 			if($mod->loaded())
 			{
 				$mod->GroupName = $groupname;
 				$mod->save();	
 			}
 			else
 			{
 				echo "该记录不存在！";exit;
 			}
 			
 		}
 		$this->add_success_message('保存成功！');
 		header("Location: /admin/group");
 	}
  	
 	
 	public function record_del()
 	{
 		$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();
 
 		foreach($ids as $id)
 		{
 			$gp = new Group_Model();
 			if($gp->where('ID',$id)->find()->loaded())
 			{
 			$gp->delete();}
 		}
 		$this->add_success_message('删除成功！');
 		header("Location: /admin/group");
 	}
 	
 	
 	
	public function setadmin()
 	{
 		$gid = isset ( $_GET['gid'] ) ? trim ( $_GET['gid'] ) : null;
 		
 		$uridx = new UserResIndex_Model();
 		$hasResource =array();
 		$rows= $uridx->where(array('ResType'=>Group_Model::RES_TYPE,'ResID'=>$gid))->find_all();
 		foreach($rows as $row)
 		{
 			$hasResource[] = $row->UserID;
 		}
 
 		$username = isset ( $_GET['Username'] ) ? trim ( $_GET['Username'] ) : '';
 		if(!empty($username))
 		{
 			$usermod = new User_Model();
 			$usermod = $usermod->like('Username',$username);
 			$records = $usermod->find_all()->as_array();
 			$srchIds = '';
 			foreach($records as $rec)
 			{
 				$srchIds .= $rec->uid.',';
 			}
 			if(strlen($srchIds)>0)
 			{
 				$srchIds = substr($srchIds,0,-1);
 			}
 			
 			
 			$content = new Grid_Controller();
	 		$content->set_pk('uid'); 		
	 		$content->set_form('action',"/admin/group/setadminsave/$gid?Username=$username&srchIds=$srchIds");
	 		$content->add_field('label','uid',array('label'=>'ID','order'=>true));
	 		$content->add_field('label','Username',array('label'=>'用户名'));
	 		$content->set_delbtnval('确定');
	 		$content->set_checkrows($hasResource);
			
	 		//$tagname_values = $this->tag_select();
	 		$content->add_filter('input','Username',array('method'=>"like"),array('label'=>'用户名',
	 		'values'=>'','defVal'=>'','moreHTML'=>'<input type="hidden" name="gid" value="'.$gid.'">'));
	 
 			
 		}
 		else
 		{
 			$content = new Grid_Controller();
	 		$content->set_pk('uid'); 		
	 		$content->set_form('action',"/admin/group/setadminsave/$gid?Username=$username");
	 		$content->add_field('label','uid',array('label'=>'ID','order'=>true));
	 		$content->add_field('label','Username',array('label'=>'用户名'));
	 		$content->set_delbtnval('确定');
	 		$content->set_checkrows($hasResource);
			
	 		//$tagname_values = $this->tag_select();
	 		$content->add_filter('input','Username',array('method'=>"like"),array('label'=>'用户名','values'=>'','defVal'=>'','moreHTML'=>'<input type="hidden" name="gid" value="'.$gid.'">'));
	 
 			
 			$usermod = new User_Model();
 			$records = $usermod->find_all()->as_array();
 		}
 		
 		
 
 		$datas = $records;
 		$content = $content->view($datas,false);
 		$this->set_output(array('pagenation'=>null,'content'=>$content));
 	}
 	
 	public function setadminsave($gid)
 	{
 		$username = isset ( $_GET['Username'] ) ? trim ( $_GET['Username'] ) : '';
		
 		$db = new Database();
 		if(empty($username))
 		{
 			
 			$db->where(array('ResType'=>Group_Model::RES_TYPE,'ResID'=>$gid))->from('UserResIndex')->delete();	
 		}
 		else
 		{
 			$srchIds=isset ( $_GET['srchIds'] ) ? trim ( $_GET['srchIds'] ) : '';
 			$srchIds = split(',',$srchIds);
 		 
 			$db->where(array('ResType'=>Group_Model::RES_TYPE,'ResID'=>$gid))->in('UserID',$srchIds)->from('UserResIndex')->delete();//
 			//var_dump($uridx->last_query());exit;
 		}
 		
 		
 
 		$userids = isset($_POST['checkrow'])?$_POST['checkrow']:array();
 
  		foreach($userids as $uid)
  		{
  			
  			$uridx =new UserResIndex_Model();
  			$uridx->ID = ID_Factory::next_id($uridx);
  			$uridx->UserID=$uid;
  			$uridx->ResID=$gid;
  			$uridx->ResType=Group_Model::RES_TYPE;
  			$uridx->save();
  		}
 		//header("Location: /admin/group/setadmin?gid=$gid");
 		$this->add_success_message('设置成功！');
 		header("Location: /admin/group");
 		
 	}
	function br2nl($message){
		$message = str_replace("<br>\n","\n",$message);
		$message = str_replace("\r\n\r\n","\n",$message);
		$message = str_replace("\n\r\n","\n",$message);
		return $message;
	}
 	
} // End Index_Controller