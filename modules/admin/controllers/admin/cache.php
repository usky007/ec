<?php
/*
 * Created on 2013-2-22
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class Cache_Controller extends Admin_Controller
{
	protected $current_page='/admin/cache';


	public function index()
	{
		return $this->record();
	}

	public function record()
	{
		$content = new Grid_Controller();
 		//$content->set_addurl('/admin/city/add');
 		$content->set_pk('key');
 		//$content->set_form('action',"/admin/city/record_del");
 		$content->add_field('label','key',array('label'=>'缓存名'));
 		$content->add_field('link','key',array('label'=>'操作','text'=>'清除','href'=>'/admin/cache/del?key={0}'));



		$attrs = array('id'=>'search', 'autocomplete'=>'off');

		$filtekey = array('label'=>'cache key <br>(格式category:key) ','style'=>'width:300px' , 'defVal'=>'', 'attrs'=>$attrs);

		$content->add_filter('input','key',array('method'=>'where'),
 			$filtekey);

		//dev-cache_category:dbobj_Cities
		$datas = array();
		$keystr =  isset($_GET['key'])?$_GET['key']:'';
		$val = "";
		if(!empty($keystr))
		{
			$key = split(':',$keystr);
			if(count($key)==2)
			{
				$cache = Cache::instance($key[0]);
				$val = $cache->get($key[1]);
				if(!empty($val))
				{
					$datas[] = array('key'=>$keystr);

				}
				$val = json_encode($val);
			}

		}






// 		$pagesize = 20 ;
// 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
// 		$offset *= $pagesize;
// 		$mod = $mod->limit($pagesize,$offset);
//
//		$citycode = isset($_GET['citycode'])?$_GET['citycode']:'';
//		if(!empty($citycode))
//		{
//			$where['citycode'] = $citycode;
//
//		}
//		$where['isopen<'] = 2;
// 		$records = $mod->where($where)->orderby('isopen','desc')->find_all()->as_array();
// 		$cnt =  $mod->where($where)->count_all();
// 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize, 'style'=>'admin'));




 		$this->add_css('admin.css');
 		$this->add_js('js/central/admin.city.js');

 		$content = $content->view($datas,false);
 		$this->set_output(array('pagenation'=>$val,'content'=>$content));

	}


	public function del()
	{
		$key =  isset($_GET['key'])?$_GET['key']:'';

		if(!empty($key))
		{
			$key = split(':',$key);
			if(count($key)==2)
			{
				$cache = Cache::instance($key[0]);
				 $cache->set($key[1],null);

			}

		}
		$this->add_success_message('保存成功！');
		url::redirect('/admin/city/index');
	}
}
?>
