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
class Category_Controller extends Admin_Controller
{
	protected $current_page='/admin/category';


	public function index()
	{
		return $this->record();
	}

	public function record()
 	{
 		$content = new Grid_Controller();
 		$content->set_addurl('category/add');
 		$content->set_pk('id');
 		$content->set_form('action',"/admin/category/record_del");
 		$content->add_field('label','id',array('label'=>'id','order'=>true));
 		$content->add_field('label','type_id',array('label'=>'分类'));
 		$content->add_field('label','name',array('label'=>'条目名'));
 		$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('link','id',array('label'=>'操作','text'=>'修改','href'=>'/admin/category/edit?id={0}'));


 		$mod = new Category_Model();
 		$cnt =  $mod->count_all();
 		$order = isset($_GET['order'])?$_GET['order']:'';
 		$sort = isset($_GET['sort'])?$_GET['sort']:'asc';
 		if(!empty($order))
 		{
 			$mod = $mod->orderby($order,$sort);
 		}

 		$pagesize = 20 ;
 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
 		$offset *= $pagesize;
 		$mod = $mod->limit($pagesize,$offset);


 		$records = $mod->find_all()->as_array();
 		foreach($records as $rcd)
 		{
 			$rcd->type_id = Location::getTypeName($rcd->type_id );
 		}

 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize,'style'=>'admin'));

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

 		$mod = new category_Model();
 		$source_record = $mod->where('id',$id)->find()->as_array();

 		$content = new Form_Controller();
 		$content->set_form('action','editsave');
 		$content->add_field('label','id',array('label'=>'ID','moreHTML'=>'<input type="hidden" name="id" value="'.$id.'">'));
 		$typevalues = $this->getLocationTypeList();
 		$content->add_field('select','type_id',array('label'=>'type_id','values'=>$typevalues));
 		$content->add_field('input','name',array('label'=>'组名'));
 		$content = $content->view($source_record,false);
		$this->set_output(array('content'=>$content));

	}


 	public function editsave()
 	{
 		$id = isset ( $_POST['id'] ) ? trim ( $_POST['id'] ) : null;
 		$type_id = isset ( $_POST['type_id'] ) ? trim ( $_POST['type_id'] ) : null;
 		$name = isset ( $_POST['name'] ) ? trim ( $_POST['name'] ) : null;

 		$mod = new category_Model();
 		if(empty($id))
 		{
 			$mod->id = ID_Factory::next_id($mod);
 			$mod->type_id = $type_id;
 			$mod->name = $name;
 			$mod->save();
 		}
 		else
 		{
 			$mod->where('id',$id)->find();
 			if($mod->loaded())
 			{
	 			$mod->type_id = $type_id;
	 			$mod->name = $name;
	 			$mod->save();
 			}
 			else
 			{
 				echo "该记录不存在！";exit;
 			}

 		}
 		$this->add_success_message('保存成功！');

 		url::redirect('/admin/category/index');
 	}


 	public function record_del()
 	{
 		$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();

 		foreach($ids as $id)
 		{
 			$gp = new category_Model();
 			if($gp->where('ID',$id)->find()->loaded())
 			{
 			$gp->delete();}
 		}
 		$this->add_success_message('删除成功！');
 		url::redirect('/admin/category/index');
 	}





	function getLocationTypeList()
 	{
 		$ary = Location::getTypeList();
 		$rst=array();
 		foreach($ary as $id=>$name)
 		{

 			$rst[]=array('label'=>$name,'value'=>$id);
 		}
 		return $rst;
 	}

	function br2nl($message){
		$message = str_replace("<br>\n","\n",$message);
		$message = str_replace("\r\n\r\n","\n",$message);
		$message = str_replace("\n\r\n","\n",$message);
		return $message;
	}
 	public function test()
 	{
 		$this->set_contentview('admin/test');
 		$this->set_output(array('a'=>'test'));
 	}

	public function updateType()
	{
		$this->set_contentview('admin/updatetype');
		$data = array();
		$cateObj = new Category_Model();
		if(isset($_REQUEST['tid']))
		{
			$data['cates'] = $cateObj->where('isdel',1)->where('type_id',$_REQUEST['tid'])->find_all();
		}
		else
		{
			$data['cates'] = $cateObj->where('isdel',1)->find_all();
		}
		$this->set_output($data);
	}

	public function execType()
	{
		$cateId = $_REQUEST['cid'];

		if($_REQUEST['type']=='typeId')
		{
			$typeId = $_REQUEST['tid'];
			$mfObj = new CityMultifilter_Model();
			$filterObj = new CityFilter_Model();
			$cateObj = new Category_Model();
			$locObj = new Location_Model();

			$cate = $cateObj->where('id',$cateId)->find();
			$cate ->type_id = $typeId;
			$cate ->save();

			$mfs = $mfObj->where('filterkey',$cateId)->find_all();

			foreach ($mfs as $m)
			{

				$m->type_id = $typeId;
				$m->save();

				$locObj->where('name',$m->locName)->find();
				$locObj->type_id = $typeId;
				$locObj->save();

				$files = $filterObj->where('locName',$m->locName)->find_all();
				foreach ($files as $f)
				{
					$f->type_id = $typeId;
					$f->save();
				}
			}
		}
		else if($_REQUEST['type']=='name')
		{
			$name = $_REQUEST['name'];
			$cateObj = new Category_Model();
			$cateObj->where('id',$cateId)->find();
			$cateObj->name = $name;
			$cateObj->save();
		}
		else if($_REQUEST['type']=='del')
		{
			$cateObj = new Category_Model();
			$cateObj->where('id',$cateId)->find();
			$cateObj->isdel = 0;
			$cateObj->save();
		}
		exit;
	}















} // End Index_Controller



?>