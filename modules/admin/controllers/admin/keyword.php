<?php
	/**
	 * 配置微博抓取的keyword
	 *
	 * @package admin
	 * @author cuiyulei
	 **/
	class Keyword_Controller extends Admin_Controller
	{

		public function __construct()
		{
			parent::__construct();
		}

		public function index()
		{
			return $this->record();
		}

		//application级别的关键字
		public function record()
		{
			$datas = array();
		
			$datas = array();
		
			$preference = Preference::instance('application');
			$manual_update = config::item("preference.application.params.manual_update", false, false);
			if(!$preference->get_driver() instanceof Preference_Dictionary_Driver || !$manual_update)
			{
				echo 'code为application的数据字典不允许手动编辑';
				exit;
			}
			
			foreach ($preference->entries() as $key => $entry){
				$new_record = array();
				$new_record['category'] = 'application';
				$new_record['timestamp'] = date('Y-m-d H:i:s',$entry->lock);
				$new_record['auto'] = "<a href='/admin/keyword/edit?category=application&code={$key}'>编辑</a>";	
				$new_record['key'] = $key;
				$new_record['val'] = $entry->value;
				$datas[] = $new_record;
			}

			$content = new Grid_Controller();
			$content->set_pk('key');
			$content->set_addurl('/admin/keyword/add/application');
			// $content->set_form('action',"/admin/keyword/record_del?category=application");
			$content->add_field('label','category',array('label'=>'分类'));
			$content->add_field('label','key',array('label'=>'键名'));
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
			$new_record = array();
			if(!empty($category)){
				$preference = Preference::instance($category);
				$new_record['category'] = $category;
				$new_record['key'] = $code;
				$new_record['val'] = $this->_getJson($preference->get($code, $lock));
				$new_record['timestamp'] = date('Y-m-d H:i:s',$lock);
			}
			
			$content = new Form_Controller();
			$content->set_form('action','/admin/keyword/editsave');
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
		

		private function _getJson($json)
		{
			$arr = json_decode($json);
			if($arr){
				if(is_array($arr))
					return implode($arr, ',');
				return $arr;
			}
			return $json;
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
			
			$preference = Preference::instance($category);
			$manual_update = config::item("preference.$category.params.manual_update", false, false);
			if(!$preference->get_driver() instanceof Preference_Dictionary_Driver || !$manual_update)
			{
				echo 'code为'.$category.'的数据字典不允许手动编辑';
				exit;
			}

			$val_arr = explode(',', $val);
			if(is_array($val_arr) && count($val_arr)>1){
				$preference->set($code, json_encode($val_arr));
			}else{
				$preference->set($code, json_encode(array($val)));
			}
			
			$this->add_success_message('保存成功！');
			url::redirect('/admin/keyword');
		}

	} // END class 
?>