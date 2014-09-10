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
class Guide_Controller extends Admin_Controller
{
	protected $current_page='/admin/guide';


	public function city()
 	{
 		$where = array('status'=>1,'isOfficial>'=>0);
 		$citycode = isset($_GET['citycode'])?$_GET['citycode']:'';
 		if(!empty($citycode))
 		{
 			$where['citycode'] = $citycode;
 		}


 		$content = new Grid_Controller();
 		//$content->set_addurl('/admin/city/add');
 		$content->set_pk('gid');
 		//$content->set_form('action',"/admin/city/record_del");
 		$content->add_field('label','gid',array('label'=>'攻略id'));
 		$content->add_field('label','citycode',array('label'=>'城市code','order'=>true));
 		$content->add_field('label','name',array('label'=>'攻略名'));
 		$content->add_field('label','isOfficial',array('label'=>'类型'));
 		$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('link','gid',array('label'=>'操作','text'=>'查看地图地点','href'=>'/admin/guide/guidelocs?gid={0}'));
 		$content->add_field('link','gid',array('label'=>'操作','text'=>'修改','href'=>'/admin/guide/edit?gid={0}'));
 		$content->add_field('link','gid',array('label'=>'操作','text'=>'设为城市攻略','href'=>'/admin/guide/upgrade2?gid={0}'));
 		//$content->add_field('link','citycode',array('label'=>'操作','text'=>'更多设置','href'=>'/admin/city/editmore?code={0}'));

		$attrs = array('id'=>'search', 'autocomplete'=>'off');
		$content->add_filter('input','citycode',array('method'=>'where'),
 			array('label'=>'城市citycode', 'defVal'=>'', 'attrs'=>$attrs));

 		$mod = new Guide_Model();
 		$mod->where($where);
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
 		$mod = $mod->orderby('gid', 'desc');

 		$records = $mod->where($where)->find_all()->as_array();

 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize, 'style'=>'admin'));

 		$order = isset($_GET['order'])?$_GET['order']:"rid";
 		$sort = isset($_GET['sort'])?$_GET['sort']:"DESC";

		foreach($records as $row)
 		{
 			$row->created = date('Y-m-d H:i:s',$row->created);
 			$row->isOfficial = $row->isOfficial == Guide_Model::GUIDE_OFFICIAL_CITY?"城市攻略":"官方攻略";


 		}
 		$datas = $records;
 		$content = $content->view($datas,false);
		$content = $this->upfrom()."<br>".$content;

		$this->add_css('admin.css');
		$this->add_js("js/central/admin.location.js");

 		$this->set_output(array('pagenation'=>$pagenation,'content'=>$content));
	}

	public function guidelocs()
	{
		$gid = isset($_GET['gid'])?$_GET['gid']:'';
		$guide = new Guide_Model($gid);
		$guide->find();
		$html = "地图《".$guide->name."》<br/>";


 		$content = new Grid_Controller();
 		$content->set_pk('id');

 		$content->add_field('label','id',array('label'=>'GLID'));
 		$content->add_field('label','lid',array('label'=>'地点id'));
 		$content->add_field('label','locname',array('label'=>'地点名'));
 		$content->add_field('label','isofficial',array('label'=>'是否是官方地点'));

 		$content->add_field('link','lid',array('label'=>'操作','text'=>'修改','href'=>'/admin/location/edit?lid={0}'));
 		$content->add_field('link','id',array('label'=>'操作','text'=>'升级为官方地点','href'=>'/admin/guide/upguideloc?id={0}'));

		$where = array('gid'=>$gid,'class>'=>1);
 		$mod = new GuideLocations_Model();
 		$records = $mod->where($where)->find_all()->as_array();
 		//$records = $mod->location->find_all()->as_array();

		$datas = array();
 		foreach($records as $row)
 		{
			if($row->deleted)
				continue;
			if($row->suggested)
				continue;
			$lid = $row->lid;
			$newrow['lid'] = $row->lid;
			$newrow['id'] = $row->id;
			$location = new Location_Model($lid);
			if(!$location->find()->loaded())
			{
				continue;
			}
			$newrow['locname'] = '<a href="/location/'.$lid.'" target="_blank">'.$location->name.'</a>';
			$newrow['isofficial'] =  $location->isofficial==1?'是':'否';


			$datas[] = $newrow;
			//echo $row->id;
 		}

 		//$datas = $records;
 		$html .= $content->view($datas,false);
 		$html .= '<br><a href="/admin/guide/city">返回</a>';
		$this->add_css('admin.css');
	//	$this->add_js("js/central/admin.location.js");

 		$this->set_output(array('pagenation'=>'','content'=>$html));
	}



	private function _checkSameUpGuideLoc($cacheid,$lid)
	{

		$locmod = new Location_Model();
		$rows = $locmod->where(array('cacheid'=>$cacheid,'isofficial'=>1))->find_all();
		$rst = false;
		if(count($rows)>1)
		{
			$rst = false;
		}
		elseif(count($rows)==1)
		{
			if($rows[0]->lid == $lid)
			{
				$rst=true;
			}
		}
		else
		{
			$rst = true;
		}
		return $rst;
	}
	private function showSameUpGuideLoc()
	{


	}
	public function upguideloc()
	{
		$glid = isset($_GET['id'])?$_GET['id']:'';
		$mod = new GuideLocations_Model($glid);

		if(!$mod->find()->loaded())
		{
			$this->add_error_message('找不到该地图地点');
			url::redirect('/admin/guide/city');
			return;
		}

		$lid = $mod->lid;
		$gid = $mod->gid;
		$location = new Location_Model($lid);
		if(!$location->find()->loaded())
		{
			$this->add_error_message('找不到该地点');
			url::redirect('/admin/guide/guidelocs?gid='.$gid);
			return;
		}

		if($location->isofficial == 1)
		{
			$html=' 已经是官方地点！<br><a href="javascript:window.history.back(-1)" >返回</a>';
			$this->set_output(array('pagenation'=>'','content'=>$html));
			return ;
		}


		$cid =$location->cacheid;
		//
		if(!$this->_checkSameUpGuideLoc($cid,$lid))
		{
			$href = "/admin/location/mergeSameLocation/".$location->lid;
			$html='存在相同的官方地点,是否合并？<br><a href="'.$href.'">是</a>,<a href="javascript:window.history.back(-1)" >返回</a>?';
			$this->set_output(array('pagenation'=>'','content'=>$html));
			return ;
		}
		//var_dump($this->_checkSameUpGuideLoc($cid,$lid));exit;

		if($location->isofficial != 1)
		{
			$location->isofficial = 1;
			$location->save();
		}

		if($mod->isOfficial != 1)
		{
			$mod->isOfficial = 1;
			$mod->save();
		}


		$loccache = new LocationCache_Model($cid);
		if($loccache->find()->loaded())
		{
			$setid=false;
			if($loccache->srcAgent == 'mapABC' || $loccache->srcAgent=='google')
			{
				if($loccache->name == $location->name)
				{
					$setid = true;
				}
			}
			if($setid)
			{
				$loccache->lid = $lid ;
				$loccache->save();
			}

		}

		url::redirect('/admin/guide/guidelocs?gid='.$gid);

	}

	public function all()
 	{

 		$citycode = isset($_GET['citycode'])?$_GET['citycode']:'';
 		if(!empty($citycode))
 		{
 			$where['citycode'] = $citycode;
 		}

 		$status = isset($_GET['status'])?$_GET['status']:1;
		$where['status'] = $status;

		$isOfficial = isset($_GET['isOfficial']) ? $_GET['isOfficial'] : 1;
		$where['isOfficial'] = $isOfficial;

		if(isset($_GET['authorid']) && $_GET['authorid'] == 0){
			$where['authorid'] = 0;
		}else{
			$where['authorid>'] = 0;
		}

		$gid = isset($_GET['gid'])?$_GET['gid']:'';
		if(!empty($gid))
 		{
 			$where['gid'] = $gid;
 		}

 		$content = new Grid_Controller();
 		//$content->set_addurl('/admin/city/add');
 		$content->set_pk('gid');
 		$content->set_form('action',"/admin/guide/record_del");
 		$content->add_field('label','gid',array('label'=>'攻略id'));
 		$content->add_field('label','citycode',array('label'=>'城市code','order'=>true));
 		$content->add_field('label','authorid',array('label'=>'用户'));
 		$content->add_field('label','name',array('label'=>'攻略名'));
 		$content->add_field('label','location_num',array('label'=>'地点数量'));
 		$content->add_field('label','isOfficial',array('label'=>'类型'));
 		$content->add_field('label','status',array('label'=>'状态'));
 		$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('link','gid',array('label'=>'操作','text'=>'更多操作','href'=>'/admin/guide/more?gid={0}'));

 		//$content->add_field('link','citycode',array('label'=>'操作','text'=>'更多设置','href'=>'/admin/city/editmore?code={0}'));

		$content->add_filter('input','gid',array('method'=>'where'),
 			array('label'=>'攻略id','defVal'=>'' ));

		//TODO::rewrite add_filter citycode:
		$attrs = array('id'=>'search', 'autocomplete'=>'off');
		$content->add_filter('input','citycode',array('method'=>'where'),
 			array('label'=>'城市citycode', 'defVal'=>'', 'attrs'=>$attrs));


		$content->add_filter('select','isOfficial',array('method'=>'where'),
			array('label'=>'攻略类型','defVal'=>'1','values'=>$this->getOfficailList()));
		$content->add_filter('select','authorid',array('method'=>'where'),
			array('label'=>'是否游客攻略','defVal'=>'1','values'=>$this->getAuthoridList()));
 		$content->add_filter('select','status',array('method'=>'where'),
 			array('label'=>'是否删除','defVal'=>'1','values'=>$this->getIsDelList()));

 		$mod = new Guide_Model();
 		$mod->where($where);
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
 		$mod = $mod->orderby('gid', 'desc');

 		$records = $mod->where($where)->find_all();

 		//var_dump($records);exit;
 		$pagenation = new Pagination(array('total_items'=>$cnt, 'items_per_page'=>$pagesize, 'style'=>'admin'));


 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
 		$offset *= $pagesize;

 		$order = isset($_GET['order'])?$_GET['order']:"rid";
 		$sort = isset($_GET['sort'])?$_GET['sort']:"DESC";

 		$datas = array();

		foreach($records as $row)
 		{
 			$newrows = $row->as_array();
 			$userObj = new User_Model();
 			$user = $userObj->where('uid', $newrows['authorid'])->find();
 			$newrows['authorid'] = '<a href="/user/'.$newrows['authorid'].'">'.$user->nickname.'</a>';
 			$href = $row->isOfficial ? '/city/guide/'.$newrows['citycode'].'/' : '/user/guide/';
 			$newrows['name'] = '<a href="'.$href.$row->gid.'">'.$row->name.'</a>';
 			$newrows['created'] = date('Y-m-d H:i:s',$row->created);
 			$newrows['isOfficial'] = $row->isOfficial == Guide_Model::GUIDE_OFFICIAL_CITY ? "城市攻略":
 					($row->isOfficial == Guide_Model::GUIDE_OFFICIAL?"官方攻略":"个人攻略");
			$newrows['status'] = $row->status == Guide_Model::GUIDE_STATUS_NORMAL?'正常':'已删除<a href="/admin/guide/restore?gid='.$row->gid.'">恢复</a>';
			$datas[] = $newrows;
 		}

 		$this->add_css('admin.css');

 		$this->add_js("js/central/admin.js");

 		$content = $content->view($datas,false);

 		$this->set_output(array('pagenation'=>$pagenation,'content'=>$content));
	}
	public function record_del()
	{
		$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();

 		foreach($ids as $id)
 		{
 			$guide = new Guide_Model();
 			if($guide->where('gid',$id)->find()->loaded())
 			{
 				if($guide->status)
 				{
 					$guide->status=0;
 					$guide->save();

 				}

 			}
 		}
 		$this->add_success_message('删除成功！');
 		url::redirect('/admin/guide/all');
	}

	public function restore()
	{
		$gid = isset($_GET['gid'])?$_GET['gid']:'';
		if(empty($gid))
		{
			$this->add_success_message('没有找到要恢复的地图');
			url::redirect('/admin/guide/all');
		}
		$guide = new Guide_Model();
		if($guide->where('gid',$gid)->find()->loaded())
		{
			if($guide->status==0)
			{
				$guide->status=1;
				$guide->save();
			}

		}
 		$this->add_success_message('恢复成功！');
 		url::redirect('/admin/guide/all');
	}

	public function edit()
	{
		$gid = input::instance()->get('gid',null);

		$mod = new Guide_Model($gid);
 		$source_record = $mod->find()->as_array();
		$source_record['created'] = date('Y-m-d H:i:s',$source_record['created']);
		$source_record['updated'] = date('Y-m-d H:i:s',$source_record['updated']);
 		$content = new Form_Controller();
 		$content->set_form('action','editsave');
 		$content->add_field('label','gid',array('label'=>'gid','moreHTML'=>'<input type="hidden" name="gid" value="'.$gid.'">'));
		$content->add_field('input','name',array('label'=>'地图名'));
 		$content->add_field('input','title',array('label'=>'地图标题'));
 		$content->add_field('textarea','content',array('label'=>'简介'));

 		$picsrc = $source_record['pic'];
 		if(empty($picsrc))
 			$content->add_field('pic','pic',array('label'=>'首页图片'));
 		else
 		{
 			$picsrc = format::get_local_storage_url($picsrc, 'save');
 			$content->add_field('pic','pic',array('label'=>'首页图片','src'=>$picsrc,'img_style'=>'width:400px'));
 		}

 		$content->add_field('label','created',array('label'=>'创建时间'));
 		$content->add_field('label','updated',array('label'=>'更新时间 '));


 		$content = $content->view($source_record,false);
		$this->set_output(array('content'=>$content));

	}



	public function editsave()
	{
		$gid = isset ( $_POST['gid'] ) ? trim ( $_POST['gid'] ) : null;
 		$name = isset ( $_POST['name'] ) ? trim ( $_POST['name'] ) : null;
 		$title = isset ( $_POST['title'] ) ? trim ( $_POST['title'] ) : null;
 		$content = isset ( $_POST['content'] ) ? trim ( $_POST['content'] ) : null;
 		$content = $this->br2nl($content);

		//var_dump($code);exit;
 		$mod = new Guide_Model($gid);
 		if(!$mod->find()->loaded())
 		{
 			if(empty($gid))
 			{
 				echo "城市地图gid不能为空 ";exit;
 			}
 			//add
 			echo "城市地图gid错误 ";exit;
 		}
 		else
 		{
 			$mod->name = $name;
 			$mod->title = $title;
 			$mod->content = $content;
 			$mod = $mod->save();
 		}


		$guidepic = isset ( $_FILES['pic'] ) ? trim ( $_FILES['pic']['name'] ) : null;
		if(!empty($guidepic))
		{
			$upimg = new UploadImage();
			$filename = $upimg->save('pic', $mod->citycode.'/guide/'.$gid);
			$mod->pic = strtolower($filename);
			$mod->save();
		}



 		$this->add_success_message('保存成功！');

 		url::redirect('/admin/guide/city');

	}

	public function more()
	{
		$gid = input::instance()->get('gid',null);

		$mod = new Guide_Model($gid);
 		$source_record = $mod->find()->as_array();
		$source_record['created'] = date('Y-m-d H:i:s',$source_record['created']);
		$source_record['updated'] = date('Y-m-d H:i:s',$source_record['updated']);
 		$content = new Form_Controller();
 		$content->set_form('action','moresave');
 		$content->add_field('label','gid',array('label'=>'gid','moreHTML'=>'<input type="hidden" name="gid" value="'.$gid.'">'));
		$content->add_field('label','name',array('label'=>'地图名'));
 		$content->add_field('input','newcitycode',array('label'=>'复制到城市code'));
 		$content->add_field('label','more',array('label'=>'更多操作',
 			'moreHTML'=>'<a href="'.url::site("/admin/guide/guidelocs?gid=".$gid).'">查看所有地点</a>
 			<a href="'.url::site("/admin/guide/edit?gid=".$gid).'">修改地图</a>'
 			));


 		$content = $content->view($source_record,false);
 		$content = "复制到新城市code".$content;



		$this->set_output(array('content'=>$content));
	}

	public function moresave()
	{
		$gid = isset ( $_POST['gid'] ) ? trim ( $_POST['gid'] ) : null;
 		$newcitycode = isset ( $_POST['newcitycode'] ) ? trim ( $_POST['newcitycode'] ) : null;

 		if(empty($gid))
		{
			echo "城市地图gid不能为空 ";exit;
		}

 		if(empty($newcitycode))
		{
			echo "复制到新城市不能为空 ";exit;
		}

		//var_dump($code);exit;
		$guide = new Guide_Model($gid);
		if(!$guide->find()->loaded())
		{
			echo "找不到该攻略 ";exit;
		}

		$newcity = new City_Model($newcitycode);
		if(!$newcity->find()->loaded())
		{
			echo "城市code错误 ";exit;

		}
		else
		{
			if(strtolower($newcitycode) == strtolower($guide->citycode))
			{
				echo "新城市不能为原地图城市";exit;

			}
		}

		$user = new YUser_Model($guide->author);
		$guide->copytocity($user,$newcitycode);

 		$this->add_success_message('复制成功！');

 		url::redirect('/admin/guide/all');

	}


	private function upfrom()
	{

 		$content = new Form_Controller();
 		$content->set_form('action','upgrade');
 		$content->set_form('submitlabel','升级');
 		$content->set_form('preview','预览');

 		$content->add_field('input','guideid',array('label'=>'攻略id'));
 		$content = $content->view(array(),false);
 		$content = "升级为官方攻略<br>".$content;
		return $content ;//$this->set_output(array('content'=>$content));
	}

	public function upgrade()
	{
		$gid = isset ( $_POST['guideid'] ) ? trim ( $_POST['guideid'] ) : null;
 		if(empty($gid))
 		{
 			echo '请输入攻略id';exit;
 		}

 		$guide = new Guide_Model($gid);
 		if(!$guide->find()->loaded())
 		{
 			echo '输入攻略id错误';exit;

 		}
		try
		{
			$guide->up2Official();
		}
		catch(UKohana_Exception $ex)
		{
			echo $ex->getMessage();exit;
		}


 		//$mod->add_Category($category);


 		$this->add_success_message('升级成功！');
 		url::redirect('/admin/guide/city');

	}

	public function upgrade2()
	{
		$gid = isset ( $_GET['gid'] ) ? trim ( $_GET['gid'] ) : null;
 		if(empty($gid))
 		{
 			echo '请输入攻略id';exit;
 		}

 		$guide = new Guide_Model($gid);
 		if(!$guide->find()->loaded())
 		{
 			echo '输入攻略id错误';exit;

 		}

 		if($guide->isOfficial <Guide_Model::GUIDE_OFFICIAL)
 		{
 			echo '只有官方攻略才能升级到城市攻略';exit;
 		}

 		if($guide->isOfficial ==Guide_Model::GUIDE_OFFICIAL_CITY)
 		{
 			echo '已经是城市攻略';exit;
 		}

		try
		{
			$guide->up2OfficialCity();
		}
		catch(UKohana_Exception $ex)
		{
			echo $ex->getMessage();exit;
		}


 		//$mod->add_Category($category);


 		$this->add_success_message('升级成功！');
 		url::redirect('/admin/guide/city');

	}

	function br2nl($message){
		$message = str_replace("<br>\n","\n",$message);
		$message = str_replace("\r\n\r\n","\n",$message);
		$message = str_replace("\n\r\n","\n",$message);
		return $message;
	}

/////////////////////////////////////////////////////////////////

	function getCityList()
 	{
 		$citymod = new City_Model();
 		$citys = $citymod->where('isopen',1)->find_all();
 		$rst=array();
 		foreach($citys as $cty)
 		{
 			$newitem['label'] = $cty->cityname;
 			$newitem['value'] = $cty->citycode;
 			$rst[] = $newitem;
 		}
 		return $rst;
 	}

 	function getOfficailList()
 	{
 		$newitem['label'] = '城市攻略';
 		$newitem['value'] = 2;
 		$rst[] = $newitem;
 		$newitem['label'] = '官方攻略';
 		$newitem['value'] = 1;
 		$rst[] = $newitem;
 		$newitem['label'] = '个人攻略';
 		$newitem['value'] = 0;
 		$rst[] = $newitem;
 		return $rst;
 	}

 	function getAuthoridList()
 	{
 		$newitem['label'] = '用户攻略';
 		$newitem['value'] = 1;
 		$rst[] = $newitem;
 		$newitem['label'] = '游客攻略';
 		$newitem['value'] = 0;
 		$rst[] = $newitem;
 		return $rst;
 	}


 	function getIsDelList()
 	{
 		$newitem['label'] = '未删除';
 		$newitem['value'] = 1;
 		$rst[] = $newitem;
 		$newitem['label'] = '已删除';
 		$newitem['value'] = 0;
 		$rst[] = $newitem;
 		return $rst;
 	}




} // End Index_Controller