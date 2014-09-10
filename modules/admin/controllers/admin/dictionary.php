<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * data dictionary operate belong to preference
 * 
 * @author cuiyulei
 *
 */
class Dictionary_Controller extends Admin_Controller{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$content = "<h1>操作此数据字典时，务必请小心小心再小心!<br/>一旦出错，我们将追究相关操作人员的责任</h1>";
		$this->add_css('admin.dictionary.css');
		$this->add_js('js/central/dictionary.js');
		$this->set_output(array('content'=>$content));
	}
	
	public function record()
	{
		$content = new Grid_Controller();
		$content->set_pk('category');
		//$content->set_form('action',"/admin/dictionary/category_del");
		$content->add_field('label','category',array('label'=>'分类'));
		$content->add_field('label','number',array('label'=>'数量'));
		$content->add_field('link','category',array('label'=>'操作','text'=>'查看','href'=>'/admin/dictionary/categorylist?code={0}'));
		
		$db = & Database::instance();
		
		$pagesize = 20 ;
		$offset = isset($_GET['page'])?$_GET['page']-1:0;
		$offset *= $pagesize;
		
		//$records = $db->query("SELECT category,count(*) as number FROM {$db->table_prefix()}Dictionary group by category");
// 		$records = $db->from("{$db->table_prefix()}Dictionary")
// 					  ->select('category', 'count(*) as number')
// 					  ->groupby('category')
// 					  ->limit($pagesize, $offset)
// 					  ->get();
		
		$prefs = config::item("preference", false, array());
		$datas = array();
		foreach ($prefs as $category => $config) {
			$pref = Preference::instance($category);
			$manual_update = config::item("preference.$category.params.manual_update", false, false);
			if ($pref->get_driver() instanceof Preference_Dictionary_Driver && $manual_update) {
				$datas[] = array(
					'category' => $category,
					'number'   => count($pref->entries())
				);
			}
		}
		
		
// 		foreach ($records as $k => $r){
// 			$datas[$k]['category'] = $r->category;
// 			$datas[$k]['number']	 = $r->number;
// 		}
		
// 		$cnt = $db->query("SELECT DISTINCT category FROM {$db->table_prefix()}Dictionary");
		//$pagenation = new Pagination(array('total_items'=>count($cnt),'items_per_page'=>$pagesize, 'style'=>'admin'));
		//var_dump($datas);exit;
		$content = $content->view($datas,false);
		$this->set_output(array('pagenation'=>'', 'content'=>$content));
	}
	
	
	public function categorylist()
	{
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
		
// 		$dicObj = new Dicentry_Model();
		
// 		$pagesize = 20 ;
// 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
// 		$offset *= $pagesize;
// 		$dicObj = $dicObj->limit($pagesize, $offset);
// 		$source_records = $dicObj->where('category', $code)->find_all();
		
// 		$cnt =  $dicObj->where('category', $code)->count_all();
// 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize, 'style'=>'admin'));
		
		$datas = array();
		
		$preference = Preference::instance($code);
		$manual_update = config::item("preference.$code.params.manual_update", false, false);
		if(!$preference->get_driver() instanceof Preference_Dictionary_Driver || !$manual_update)
		{
			echo 'code为'.$code.'的数据字典不允许手动编辑';
			exit;
		}
		
		foreach ($preference->entries() as $key => $entry){
			$new_record = array();
			$new_record['category'] = $code;
			$new_record['timestamp'] = date('Y-m-d H:i:s',$entry->lock);
			$new_record['auto'] = "<a href='/admin/dictionary/edit?category={$code}&code={$key}'>编辑</a> | <a href='/admin/dictionary/record_del?category={$code}&code={$key}'>删除</a>";	
			$new_record['dic_id'] = $key;
			$new_record['val'] = $entry->value;
			$datas[] = $new_record;
		}

		$content = new Grid_Controller();
		$content->set_addurl('/admin/dictionary/add/'.$code);
		$content->set_pk('dic_id');
		$content->set_form('action',"/admin/dictionary/record_del?category=".$code);
		$content->add_field('label','category',array('label'=>'分类'));
		$content->add_field('label','dic_id',array('label'=>'键名'));
		$content->add_field('label','val',array('label'=>'键值'));
		$content->add_field('label','timestamp',array('label'=>'更新时间 '));
		$content->add_field('label','auto',array('label'=>'操作'));
	
		$content = $content->view($datas, false);
		
		$this->set_output(array('pagenation'=>"", 'content'=>$content));
	}
	
	public function add($category)
	{
		$this->edit($category);
	}
	
	public function edit($category=null)
	{
		if(!$category){
			$category = isset($_GET['category']) ? trim($_GET['category']) : null;
		}
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
		/* $mod = new Dicentry_Model();
		$source_record = $mod->where('dic_id', $code)->find()->as_array();
		$source_record['timestamp'] = date('Y-m-d H:i:s',$source_record['timestamp']); */
		$new_record = array();
		if(!empty($category)){
			$preference = Preference::instance($category);
			$manual_update = config::item("preference.$category.params.manual_update", false, false);
			if(!$preference->get_driver() instanceof Preference_Dictionary_Driver || !$manual_update)
			{
				echo 'code为'.$category.'的数据字典不允许手动编辑';
				exit;
			}
			$new_record['category'] = $category;
			$new_record['key'] = $code;
			$new_record['val'] = $preference->get($code, $lock);
			$new_record['timestamp'] = date('Y-m-d H:i:s',$lock);
		}
		
		$content = new Form_Controller();
		$content->set_form('action','/admin/dictionary/editsave');
		
		$content->add_field('label', 'category', array('label'=>'分类','moreHTML'=>'<input type="hidden" name="category" value="'.$category.'">'));
		if(empty($code)){
			$content->add_field('input', 'key', array('label'=>'键名'));
		}else{
			$content->add_field('label', 'key', array('label'=>'键名','moreHTML'=>'<input type="hidden" name="key" value="'.$code.'">'));
		}
		$content->add_field('input','val', array('label'=>'值'));
		$content->add_field('label','timestamp', array('label'=>'创建时间'));
		$content = $content->view($new_record, false);
		$this->set_output(array('content'=>$content));
	}
	
	public function editsave()
	{
		$code = isset ( $_POST['key'] ) ? trim ( $_POST['key'] ) : null;
		$category = isset ( $_POST['category'] ) ? trim ( $_POST['category'] ) : null;
		$val = isset ( $_POST['val'] ) ? trim ( $_POST['val'] ) : null;

		if(empty($code))
		{
			echo "数据字典键名  code 不能为空 ";exit;
		}
		if(empty($category))
		{
			echo "数据字典分类 不能为空 ";exit;
		}
		if(empty($val))
		{
			echo "数据字典值 不能为空 ";exit;
		}
		
		//TODO:: Preference operate dictionary:
		$preference = Preference::instance($category);
		$manual_update = config::item("preference.$category.params.manual_update", false, false);
		if(!$preference->get_driver() instanceof Preference_Dictionary_Driver || !$manual_update)
		{
			echo 'code为'.$category.'的数据字典不允许手动编辑';
			exit;
		}
		$preference->set($code, $val);
		
		
		$this->add_success_message('保存成功！');
		url::redirect('/admin/dictionary/categorylist?code='.$category);
	}
	
	//Preference operate delete_all: delete from Dictionary where category = {category}
	/* public function category_del()
	{
		$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();
		foreach($ids as $category)
		{
			$Preference = Preference::instance($category);
			$Preference->delete_all();
		}
		$this->add_success_message('删除成功！');
		
		url::redirect('/admin/dictionary/record');
	} */
	
	//Preference operate delete($id): delete Dictionary where dic_id = {dic_id}
	public function record_del()
	{
		$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : (isset($_GET['code']) ? array($_GET['code']) : array());
		$category = isset($_GET['category']) ? $_GET['category'] : null;
	
		if(is_null($category)){
			echo '分类不能为空';
			exit;
		}
		foreach($ids as $k => $id)
		{
			$preference = Preference::instance($category);
			$manual_update = config::item("preference.{$category}.params.manual_update", false, false);
			if(!$preference->get_driver() instanceof Preference_Dictionary_Driver || !$manual_update)
			{
				echo 'code为'.$category.'的数据字典不允许删除';
				exit;
			}
			$preference->delete($id);
		}
		
		$this->add_success_message('删除成功！');
	
		url::redirect('/admin/dictionary/categorylist?code='.$category);
	}
	
	
}