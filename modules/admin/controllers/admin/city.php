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
class City_Controller extends Admin_Controller
{
	protected $current_page='/admin/city';


	public function index()
	{
		return $this->record();
	}

	public function record()
 	{
 		$content = new Grid_Controller();
 		$content->set_addurl('/admin/city/add');
 		$content->set_pk('citycode');
 		$content->set_form('action',"/admin/city/record_del");
 		$content->add_field('label','citycode',array('label'=>'城市code','order'=>true));
 		$content->add_field('label','cityname',array('label'=>'城市名'));
 		$content->add_field('label','title',array('label'=>'城市标题'));
 		$content->add_field('label','isopen',array('label'=>'是否开放'));
 		$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('label','updated',array('label'=>'修改日期'));
 		$content->add_field('link','citycode',array('label'=>'操作','text'=>'修改','href'=>'/admin/city/edit?code={0}'));
 		$content->add_field('link','citycode',array('label'=>'操作','text'=>'皮肤修改','href'=>'/admin/city/editbg?code={0}'));
 		$content->add_field('link','citycode',array('label'=>'操作','text'=>'更多设置','href'=>'/admin/city/editmore?code={0}'));
		$content->add_field('link','citycode',array('label'=>'操作','text'=>'重建索引','href'=>'/admin/city/rebuildFilter?code={0}'));
		$content->add_field('link','citycode',array('label'=>'操作','text'=>'开放','href'=>'/admin/city/open?code={0}'));
		$content->add_field('link','citycode',array('label'=>'操作','text'=>'关闭','href'=>'/admin/city/close?code={0}'));
		$content->add_field('link','citycode',array('label'=>'操作','text'=>'待定','href'=>'/admin/city/hold?code={0}'));

		$attrs = array('id'=>'search', 'autocomplete'=>'off');
		$content->add_filter('input','citycode',array('method'=>'where'),
 			array('label'=>'城市citycode', 'defVal'=>'', 'attrs'=>$attrs));
		$content->add_filter('select','isopen',array('method'=>'where'),
				array('label'=>'是否开放', 'defVal'=>'1', 'values'=>$this->getOpenList()));


 		$mod = new City_Model();
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

		$citycode = isset($_GET['citycode'])?$_GET['citycode']:'';
		$isopen = isset($_GET['isopen']) ? $_GET['isopen'] : 1;
		if(!empty($citycode))
		{
			$where['citycode'] = $citycode;

		}
		$where['isopen'] = $isopen;
 		$records = $mod->where($where)->orderby('isopen','desc')->find_all()->as_array();
 		$cnt =  $mod->where($where)->count_all();
 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize, 'style'=>'admin'));


 		$order = isset($_GET['order'])?$_GET['order']:"rid";
 		$sort = isset($_GET['sort'])?$_GET['sort']:"DESC";


		foreach($records as $row)
		{
			$row->isopen = $row->isopen==1? "开放" : ($row->isopen==2 ? '待定' : "即将开放");
			$row->created = date('y-m-d H:i:s',$row->created);
			$row->updated = date('y-m-d H:i:s',$row->updated);
		}
 		$datas = $records;

 		$this->add_css('admin.css');
 		$this->add_js('js/central/admin.city.js');

 		$content = $content->view($datas,false);
 		$this->set_output(array('pagenation'=>$pagenation,'content'=>$content));
	}

	public function open()
	{
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
		$this->_cityOperate($code, 1);
	}

	public function close()
	{
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
		$this->_cityOperate($code, 0);
	}

	public function hold()
	{
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
		$this->_cityOperate($code, 2);
	}
	/**
	 * 开关城市操作
	 * @param string $citycode
	 * @param int $isopen
	 */
	private function _cityOperate($code, $isopen)
	{
		if(empty($code))
		{
			echo "城市 code不能为空 ";exit;
		}
		$mod = new City_Model($code);
		$city = $mod->find();
		if(!$city->loaded()){
			echo "该城市不存在";exit;
		}
		$city->isopen = $isopen;
		$city->save();
		$this->add_success_message('保存成功！');
		url::redirect('/admin/city/index');
	}

	public function add()
	{
		$this->edit();

	}

	public function edit()
	{
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;

 		$mod = new City_Model();
 		$source_record = $mod->where('citycode',$code)->find()->as_array();
		$source_record['created'] = date('Y-m-d H:i:s',$source_record['created']);
		$source_record['updated'] = date('Y-m-d H:i:s',$source_record['updated']);
 		$content = new Form_Controller();
 		$content->set_form('action','editsave');
 		if(empty($code))
 			$content->add_field('input','citycode',array('label'=>'城市code'));
 		else
 			$content->add_field('label','citycode',array('label'=>'code','moreHTML'=>'<input type="hidden" name="citycode" value="'.$code.'">'));
 		$content->add_field('input','cityname',array('label'=>'城市名'));
 		$content->add_field('input','title',array('label'=>'城市标题'));
 		$content->add_field('textarea','Introduction',array('label'=>'简介'));

 		$picsrc = $source_record['pic'];
 		if(empty($picsrc))
 			$content->add_field('pic','pic',array('label'=>'首页图片(450*250)'));
 		else
 		{
 			$picsrc = format::get_local_storage_url($picsrc, 'save');
 			$content->add_field('pic','pic',array('label'=>'首页图片(450*250)','src'=>$picsrc,'img_style'=>'width:400px'));
 		}


 		$content->add_field('label','location_num',array('label'=>'地点数'));
 		$content->add_field('label','official_guide_num',array('label'=>'官方地图数'));
 		$content->add_field('label','user_guide_num',array('label'=>'用户地图数'));
 		$content->add_field('label','created',array('label'=>'创建时间'));
 		$content->add_field('label','updated',array('label'=>'更新时间 '));


 		$content = $content->view($source_record,false);
		$this->set_output(array('content'=>$content));

	}


 	public function editsave()
 	{
 		$code = isset ( $_POST['citycode'] ) ? trim ( $_POST['citycode'] ) : null;
 		$cityname = isset ( $_POST['cityname'] ) ? trim ( $_POST['cityname'] ) : null;
 		$title = isset ( $_POST['title'] ) ? trim ( $_POST['title'] ) : null;
 		$Introduction = isset ( $_POST['Introduction'] ) ? trim ( $_POST['Introduction'] ) : null;
 		$Introduction = $this->br2nl($Introduction);

		//var_dump($code);exit;
 		$mod = new City_Model();
 		$mod->where('citycode',$code)->find();
 		if(!$mod->loaded())
 		{
 			if(empty($code))
 			{
 				echo "城市 code不能为空 ";exit;
 			}
 			$mod = new City_Model();
 			$mod->citycode = $code;
 			$mod->cityname = $cityname;
 			$mod->title = $title;
 			$mod->Introduction = $Introduction;
 			$mod = $mod->save();
 		}
 		else
 		{
 			$mod->cityname = $cityname;
 			$mod->title = $title;
 			$mod->Introduction = $Introduction;
 			$mod = $mod->save();

 		}

		$picname = isset( $_FILES['pic'] ) ? trim ($_FILES['pic']['name'] ) : null;

		if(!empty($picname))
		{
			$upimg = new UploadImage();
			$filename = $upimg->save('pic', $code);

			$mod->pic = $filename;
		}


		$mod->save();
 		$this->add_success_message('保存成功！');

		url::redirect('/admin/city/index');
 		//$this->index();
 	}
	public function rebuildFilter()
	{
		set_time_limit(86400000);
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
		$mod = new City_Model();
 		$mod->where('citycode',$code)->find();
 		if(!$mod->loaded())
 		{
 			if(empty($code))
 			{
 				echo "该城市不存在";exit;
 			}
 		}
 		else
 		{
			$cf = new CityFilter_Model();
			$cf->rebuildByCity($code);
 		}
 		$this->add_success_message('重建成功！');
 		url::redirect('/admin/city/index');

	}
	public function editmore()
	{
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
		$citymod = new City_Model($code);
		if(!$citymod->find()->loaded())
		{
			echo "该城市不存在";exit;
		}


 		$content = new Form_Controller();
		$content->set_form('action','editmoresave?code='.$code);

		$types = Location::getTypeList();

		$data=array();

		//$mod = new CityType_Model();
		foreach($types as $key=>$value)
		{

			$typeid = $key;
			$typecode = $value;

			if($typecode == 'others')
			{
				continue;
			}

			$typename = Location::getTypeName($typeid);

			$mod = new CityType_Model();
			$source_record = $mod->where(array('citycode'=>$code,'type_id'=>$typeid))->find()->as_array();


			$content->add_field('textarea','content_'.$typecode,array('label'=>$typename."说明",
			'style'=>'width:400px;height:200px'));

			$picsrc = $source_record['pic'];

	 		if(empty($picsrc))
	 			$content->add_field('pic',"pic_$typecode",array('label'=>$typename."图片(300*175)"));
	 		else
	 		{
	 			$picsrc = format::get_local_storage_url($picsrc, 'save');
	 			$content->add_field('pic',"pic_$typecode",array('label'=>$typename."图片(300*175)",'src'=>$picsrc,'img_style'=>'width:400px'));
	 		}

	 		$data['content_'.$typecode] = $source_record['Introduction'];
	 		$data['pic_'.$typecode] = $source_record['pic'];


		}



		//攻略地图

		$locdetail = $citymod->details->where('infoitem',CityDetail_Model::ITEM_GUIDEPIC)->find();
		$picguidesrc = $locdetail->loaded()?$locdetail->infovalue:"";
		$locdetail = $citymod->details->where('infoitem',CityDetail_Model::ITEM_GUIDECOMMENT)->find();
		$guidecomment = $locdetail->loaded()?$locdetail->infovalue:"";
		$data['content_guide'] = $guidecomment;
	 	$data['pic_guide'] = $picguidesrc;

		$content->add_field('textarea','content_guide',array('label'=>'攻略栏目说明',
			'style'=>'width:400px;height:200px'));

		if(empty($picguidesrc))
 			$content->add_field('pic',"pic_guide",array('label'=>"攻略栏目图片"));
 		else
 		{
 			$picguidesrc = format::get_local_storage_url($picguidesrc, 'save');
 			$content->add_field('pic',"pic_guide",array('label'=>"攻略栏目图片",
			'src'=>$picguidesrc,'img_style'=>'width:400px'));
 		}


		//首页flash用图
		$locdetail = $citymod->details->where('infoitem',CityDetail_Model::ITEM_FLASHPIC)->find();
		$flashpic = $locdetail->loaded()?$locdetail->infovalue:"";
		$data['pic_flash'] = $picguidesrc;
		if(empty($flashpic))
 			$content->add_field('pic',"pic_flash",array('label'=>"首页flash图片"));
 		else
 		{
 			$flashpic = format::get_local_storage_url($flashpic, 'save');
 			$content->add_field('pic',"pic_flash",array('label'=>"首页flash图片",
			'src'=>$flashpic,'img_style'=>'width:400px'));
 		}

 		//用户攻略默认用图
		$locdetail = $citymod->details->where('infoitem',CityDetail_Model::ITEM_USERGUIDEPIC)->find();
		$ugpic = $locdetail->loaded()?$locdetail->infovalue:"";
		$data['pic_userguide'] = $ugpic;
		if(empty($ugpic))
 			$content->add_field('pic',"pic_userguide",array('label'=>"用户攻略默认图片"));
 		else
 		{
 			$ugpic = format::get_local_storage_url($ugpic, 'save');
 			$content->add_field('pic',"pic_userguide",array('label'=>"用户攻略默认图片",
			'src'=>$ugpic,'img_style'=>'width:400px'));
 		}



 		$content = $content->view($data,false);
		$this->set_output(array('content'=>$content));

	}
	public function editbg()
	{
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
		$citymod = new City_Model($code);
		if(!$citymod->find()->loaded())
		{
			echo "该城市不存在";exit;
		}
		$content = new Form_Controller();
		$content->set_form('action','editbgsave?code='.$code);
		$pf = Preference::instance('customizeCity');
		$json = $pf->get($code);
		$enable = array(
	 			0=>array(
	 				'label' => '开',
	 				'value' => true

	 			),
				1=>array(
	 				'label' => '关',
	 				'value' => false
	 			)
	 		);
		if(!empty($json)){
			$json = json_decode($json);
	 		//城市换肤

			$start = $json->start==0?null:date('Y-m-d',$json->start);
			$end = $json->end==0?null:date('Y-m-d',$json->end);
	 		$content->add_field('select','enable',array('label'=>'皮肤开关','values'=>$enable,'defVal'=> $json->enable));
			$content->add_field('input',"start",array('label'=>"皮肤起始时间(格式:xxxx-xx-xx)",'value'=>$start));
			$content->add_field('input',"end",array('label'=>"皮肤结束时间(格式:xxxx-xx-xx)",'value'=>$end));
			$content->add_field('input',"height",array('label'=>"图片预留高度",'value'=>$json->height));
			$content->add_field('input',"pic_citybg",array('label'=>"皮肤图片",'value'=>$json->pic));
			$content->add_field('input',"pic_cityrightbar",array('label'=>"右边栏背景",'value'=>$json->rightbar));
			if(!empty($json->ad_header))
			{
				$content->add_field('label',"ad_header",array('label'=>"顶部自定义代码",'moreHTML'=>'<textarea id="ad_header" name="ad_header">'.htmlentities($json->ad_header,ENT_COMPAT,'UTF-8').'</textarea>'));
			}
			else
				$content->add_field('textarea',"ad_header",array('label'=>"顶部自定义代码"));
		}
		else
		{
			$content->add_field('select','enable',array('label'=>'皮肤开关','values'=>$enable,));
			$content->add_field('input',"start",array('label'=>"皮肤起始时间(格式:xxxx-xx-xx)"));
			$content->add_field('input',"end",array('label'=>"皮肤结束时间(格式:xxxx-xx-xx)"));
			$content->add_field('input',"height",array('label'=>"图片预留高度"));
			$content->add_field('input',"pic_citybg",array('label'=>"皮肤图片"));
			$content->add_field('input',"pic_cityrightbar",array('label'=>"右边栏背景"));
			$content->add_field('textarea',"ad_header",array('label'=>"顶部自定义代码"));
		}


		$content = $content->view('',false);
		$this->set_output(array('content'=>$content));
	}

	public function editmoresave()
	{
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
		$citymod = new City_Model($code);
		if(!$citymod->find()->loaded())
		{
			echo "该城市不存在";exit;
		}
		$upimg = new UploadImage();
		$types = Location::getTypeList();
		foreach($types as $key=>$value)
		{
			$typeid = $key;
			$typecode = $value;
			$typename = Location::getTypeName($typeid);

			$content = isset( $_POST['content_'.$typecode] ) ? trim ( $_POST['content_'.$typecode] ) : null;

			$pic = isset( $_FILES['pic_'.$typecode] ) ? trim ( $_FILES['pic_'.$typecode]['name'] ) : null;

			if(!empty($pic))
			{

				$dir =  "$code/$typecode";
				$pic = $upimg->save('pic_'.$typecode, $dir);
			}


			$mod = new CityType_Model();
			$mod->where(array('citycode'=>$code,'type_id'=>$typeid))->find();
			if($mod->loaded())
			{
				$mod->Introduction = $this->br2nl($content);
				if(!empty($pic))
					$mod->pic = $pic;
				$mod->save();
			}
			else
			{
				$mod = new CityType_Model();
				$mod->id=ID_Factory::next_id($mod);
				$mod->citycode=$code;
				$mod->type_id=$typeid;
				$mod->Introduction = $this->br2nl($content);
				if(!empty($pic))
					$mod->pic = $pic;
				$mod->save();
			}

		}

		//攻略图片，攻略 内容
		$guidecontent = input::instance()->post('content_guide',null);
		$citydetail_mod = new CityDetail_Model();
		$citydetail_mod->where(array('citycode'=>$code,'infoitem'=>CityDetail_Model::ITEM_GUIDECOMMENT))->find();
		if($citydetail_mod->loaded())
		{
			$citydetail_mod->infovalue = $guidecontent;
			$citydetail_mod->save();
		}
		else
		{
			$citydetail_mod->id = ID_Factory::next_id($citydetail_mod);
			$citydetail_mod->citycode = $code;
			$citydetail_mod->infoitem = CityDetail_Model::ITEM_GUIDECOMMENT;
			$citydetail_mod->infovalue = $guidecontent;
			$citydetail_mod->save();
		}


		$guidepic = isset ( $_FILES['pic_guide'] ) ? trim ( $_FILES['pic_guide']['name'] ) : null;
		if(!empty($guidepic))
		{

			$dir =  "$code/guide";
			$guidepic = $upimg->save('pic_guide', $dir);


			$citydetail_mod = new CityDetail_Model();
			$citydetail_mod->where(array('citycode'=>$code,'infoitem'=>CityDetail_Model::ITEM_GUIDEPIC))->find();
			if($citydetail_mod->loaded())
			{
				$citydetail_mod->infovalue = $guidepic;
				$citydetail_mod->save();
			}
			else
			{
				$citydetail_mod->id = ID_Factory::next_id($citydetail_mod);
				$citydetail_mod->citycode = $code;
				$citydetail_mod->infoitem = CityDetail_Model::ITEM_GUIDEPIC;
				$citydetail_mod->infovalue = $guidepic;
				$citydetail_mod->save();
			}
		}

		//flash

		$flashpic = isset ( $_FILES['pic_flash'] ) ? trim ( $_FILES['pic_flash']['name'] ) : null;

		if(!empty($flashpic))
		{
			$dir =  "$code/flash";
			$flashpic = $upimg->save('pic_flash', $dir);


			$citydetail_mod = new CityDetail_Model();
			$citydetail_mod->where(array('citycode'=>$code,'infoitem'=>CityDetail_Model::ITEM_FLASHPIC))->find();
			if($citydetail_mod->loaded())
			{
				$citydetail_mod->infovalue = $flashpic;
				$citydetail_mod->save();
			}
			else
			{
				$citydetail_mod->id = ID_Factory::next_id($citydetail_mod);
				$citydetail_mod->citycode = $code;
				$citydetail_mod->infoitem = CityDetail_Model::ITEM_FLASHPIC;
				$citydetail_mod->infovalue = $flashpic;
				$citydetail_mod->save();
			}

		}

		//用户攻略默认图片

		$ugpic = isset ( $_FILES['pic_userguide'] ) ? trim ( $_FILES['pic_userguide']['name'] ) : null;

		if(!empty($ugpic))
		{
			$dir =  "$code/userguide";
			$ugpic = $upimg->save('pic_userguide', $dir);


			$citydetail_mod = new CityDetail_Model();
			$citydetail_mod->where(array('citycode'=>$code,'infoitem'=>CityDetail_Model::ITEM_USERGUIDEPIC))->find();
			if($citydetail_mod->loaded())
			{
				$citydetail_mod->infovalue = $ugpic;
				$citydetail_mod->save();
			}
			else
			{
				$citydetail_mod->id = ID_Factory::next_id($citydetail_mod);
				$citydetail_mod->citycode = $code;
				$citydetail_mod->infoitem = CityDetail_Model::ITEM_USERGUIDEPIC;
				$citydetail_mod->infovalue = $ugpic;
				$citydetail_mod->save();
			}

			$guide_mod = new Guide_Model();
			$cityguides = $guide_mod->where('citycode',$code)->find_all();
			foreach($cityguides as $guide)
			{
				if(empty($guide->pic))
				{
					$guide->pic = $ugpic;
					$guide->save();
				}
			}

		}



		$this->add_success_message('保存成功！');
		url::redirect('/admin/city/index');
 		//$this->index();
	}
	public function editbgsave()
	{
		$code = isset ( $_GET['code'] ) ? trim ( $_GET['code'] ) : null;
		$citymod = new City_Model($code);
		if(!$citymod->find()->loaded())
		{
			echo "该城市不存在";exit;
		}
		$start = $_POST['start']==0?0:strtotime($_POST['start']);
		$end = $_POST['end']==0?0:strtotime($_POST['end']);
		$pf = Preference::instance('customizeCity');
		$data = array(
			'start' => $start,
			'end' => $end,
			'enable' => $_POST['enable'],
			'height' => $_POST['height'],
			'pic' => $_POST['pic_citybg'],
			'rightbar' => $_POST['pic_cityrightbar'],
			'ad_header'=>$_POST['ad_header']
		);
		$data = json_encode($data);
		$pf->set($code,$data);
		$this->add_success_message('保存成功！');
		url::redirect('/admin/city/index');
	}

	function getOpenList()
	{
		$newitem['label'] = '待定';
		$newitem['value'] = 2;
		$rst[] = $newitem;
		$newitem['label'] = '已开放';
		$newitem['value'] = 1;
		$rst[] = $newitem;
		$newitem['label'] = '即将开放';
		$newitem['value'] = 0;
		$rst[] = $newitem;
		return $rst;
	}

 	public function record_del()
 	{
 		$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();

 		foreach($ids as $id)
 		{
 			$gp = new City_Model();
 			if($gp->where('citycode',$id)->find()->loaded())
 			{
 			$gp->delete();}
 		}
 		$this->add_success_message('删除成功！');

 		url::redirect('/admin/city/index');
 	}






	function br2nl($message){
		$message = str_replace("<br>\n","\n",$message);
		$message = str_replace("\r\n\r\n","\n",$message);
		$message = str_replace("\n\r\n","\n",$message);
		return $message;
	}


} // End Index_Controller