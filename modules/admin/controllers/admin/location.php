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
class Location_Controller extends Admin_Controller
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
 		$content->set_addurl('location/add');
 		$content->set_pk('lid');
 		$content->set_form('action',"/admin/location/record_del");
 		$content->add_field('label','lid',array('label'=>'lid','order'=>true));
 		$content->add_field('label','cacheid',array('label'=>'cacheid'));
 		$content->add_field('label','citycode',array('label'=>'城市'));
 		$content->add_field('label','type_id',array('label'=>'分类'));
 		$content->add_field('label','name',array('label'=>'地点名'));
 		$content->add_field('label','long',array('label'=>'经度'));
 		$content->add_field('label','lat',array('label'=>'纬度'));
 		$content->add_field('label','isofficial',array('label'=>'是否官方'));
 		$content->add_field('label','updated',array('label'=>'修改日期'));
 		$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('link','lid',array('label'=>'操作','text'=>'修改','href'=>'/admin/location/edit?lid={0}'));
 		//后台增加关联cache地点的操作
//  		$content->add_field('label', 'linkcacheid', array('label'=>'操作'));

 		$attrs = array('id'=>'search', 'autocomplete'=>'off');
		$content->add_filter('input','citycode',array('method'=>'where'),
 			array('label'=>'城市citycode', 'defVal'=>'', 'attrs'=>$attrs));

 		$content->add_filter('select','typeid',array('method'=>'where'),
 		array('label'=>'分类','values'=>$this->getLocationTypeList()));
 		$content->add_filter('select','isofficial',array('method'=>'where'),
 			array('label'=>'是否官方','defVal'=>'1','values'=>$this->getBoolList()));
		$content->add_filter('input','name',array('method'=>'like'),array('label'=>'地点名'));

 		$mod = new Location_Model();
 		$order = isset($_GET['order'])?$_GET['order']:'';
 		$sort = isset($_GET['sort'])?$_GET['sort']:'asc';
 		if(!empty($order))
 		{
 			$mod = $mod->orderby($order,$sort);
 		}
 		$mod = $mod->orderby('lid', 'DESC');
 		$where = array();
 		$filterCitycode = Input::instance()->get('citycode',null);
 		$filterTypeid = Input::instance()->get('typeid',null);
 		$filterIsofficial = Input::instance()->get('isofficial',null);
 		$filterName = Input::instance()->get('name',null);
 		if(!empty($filterCitycode))
 		{
 			$where['citycode'] = $filterCitycode;
 		}

		if(!empty($filterTypeid))
 		{
 			$where['type_id'] = $filterTypeid;
 		}

 		if(!is_null($filterIsofficial))
 		{
 			$where['isofficial'] = $filterIsofficial;
 		}

		if(!is_null($filterName))
 		{
 			$mod->like('name',"$filterName") ;
 		}

 		$pagesize = 20 ;
 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
 		$offset *= $pagesize;
 		$mod = $mod->limit($pagesize,$offset);


 		$records = $mod->where($where)->find_all()->as_array();

 		$cnt =  $mod->where($where)->count_all();
 		foreach($records as $rcd)
 		{
 			$rcd->type_id = Location::getTypeName($rcd->type_id);
 			$rcd->name = '<a href="/location/'.$rcd->lid.'" target="_blank">'.$rcd->name.'</a>';//		http://dev.yanzi.com/87
//  			$row = $rcd->as_array();
//  			$row['linkcacheid']='<a href="/admin/location/linkcache?lid='.$row['lid'].'">关联到cache地点 </a>';	
//  			$datas[] =$row;
 		}

 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize, 'style'=>'admin'));
 		$order = isset($_GET['order'])?$_GET['order']:"rid";
 		$sort = isset($_GET['sort'])?$_GET['sort']:"DESC";

 		//$datas = $records;

 		$this->add_css('admin.css');
 		$this->add_js('js/central/admin.location.js');

 		$content = $content->view($records, false);
 		$this->set_output(array('pagenation'=>$pagenation,'content'=>$content));
	}

	public function add()
	{
		$this->edit();

	}
	public function edit()
	{
		$lid = isset ( $_GET['lid'] ) ? trim ( $_GET['lid'] ) : null;

 		$mod = new Location_Model();

 		$source_record = $mod->where('lid',$lid)->find()->as_array();

 	 	$source_record['categorys'] ="";
 		$categorys = $mod->get_Categorys();
 		foreach($categorys as $ct)
 		{
 			$source_record['categorys'] .= $ct['name'] ."&nbsp;";
 		}

 		$LocationCacheName = "";
 		$LocationCacheLid = 0;
		if(!empty($source_record['cacheid']))
		{
			$locCache = new LocationCache_Model($source_record['cacheid']);
			if($locCache->find()->loaded())
			{
				$LocationCacheName = $locCache->name;
				$LocationCacheLid = $locCache->lid;
			}
		}

 		$content = new Form_Controller();
 		$content->set_form('action','editsave');

 		$content->add_field('label','lid',array('label'=>'lid','moreHTML'=>'<input type="hidden" name="lid" value="'
 			.$lid.'"><input type="hidden" name="reurnURL" value="'.$_SERVER['HTTP_REFERER'].'">'));
 		$content->add_field('label','cacheid',array('label'=>'cacheid',));
 		$content->add_field('select','citycode',array('label'=>'城市',
 			'values'=>$this->getCityList()));
 		$content->add_field('select','type_id',array('label'=>'分类',
 			'values'=>$this->getLocationTypeList(),'emptyValue'=>0,'defVal'=>''));
 		$js_typeid = "document.getElementById('sel_type_id').options[document.getElementById('sel_type_id').selectedIndex].value";
 		$jslink = "window.open('/admin/location/setcategory?id=$lid&typeid='+$js_typeid)";

 		$content->add_field('label','categorys',array('label'=>'子类',
 			 'moreHTML'=>'<a href="#" onclick="'.$jslink.'" >设置</a>'));

 		$content->add_field('input','name',array('label'=>'地名'));
 		$content->add_field('input','title',array('label'=>'标题'));
 		$content->add_field('textarea','content',array('label'=>'内容','style'=>'width: 806px; height: 235px;'));


		$picsrc = $source_record['mainpic'];
 		if(empty($picsrc))
 			$content->add_field('pic','mainpic',array('label'=>'首页图片'));
 		else
 		{
 			$picsrc = format::get_local_storage_url($picsrc, 'save');
 			$content->add_field('pic','mainpic',array('label'=>'首页图片','src'=>$picsrc,'img_style'=>'width:400px'));
 		}

 		$content->add_field('input','address',array('label'=>'地址'));
 		$content->add_field('input','long',array('label'=>'经度'));
 		$content->add_field('input','lat',array('label'=>'纬度'));
 		$content->add_field('input','rating',array('label'=>'打分'));
 		$content->add_field('input','price',array('label'=>'价格'));
 		//$content->add_field('input','rating',array('label'=>''));

 		$content->add_field('select','isofficial',array('label'=>'是否官方','values'=>$this->getBoolList(),'defVal'=>$source_record['isofficial']));
 		
 		//关联cache
 		log::debug('locationCacheName='.$LocationCacheName);
 		$cacheVal = $LocationCacheName ? $LocationCacheName : '暂无';
 		$cacheVal = "<span id='linkedCache'>$cacheVal</span>";
 		$moreHtml = " <a onclick='window.open(\"/admin/location/linkcache?lid={$lid}\")' href='#'>设置关联cache地点</a>";
 		$content->add_field('label','linkCache',array('label'=>'已关联cache地点','value'=>$cacheVal, 'moreHTML' => $moreHtml));
 		
 		$detailAttrs = array('id'=>'detail');
 		$editDetail = "window.open('/admin/location/editDetail?lid=$lid')";
 		$details = $this->_getDetailInfo($lid);
 		if($details){
 			$details = '<span id="detail">'.$details."</span><a href='javascript:;' onclick=\"".$editDetail."\">编辑Detail信息</a>";
 		}else{
 			$details = "<span id='detail'>暂无 </span>  <a href='javascript:;' onclick=\"".$editDetail."\">设置Detail信息</a>";
 		}
 		$content->add_field('label', 'detail', array('label'=>'Detail信息', 'moreHTML' => $details));
 		$content->add_field('label','updated',array('label'=>'更改时间','readonly'=>true));
 		$content->add_field('label','created',array('label'=>'创建时间','readonly'=>true));
 		$content = $content->view($source_record,false);
		$this->set_output(array('content'=>$content));
	}

	public function editDetail()
	{
		$lid = isset($_GET['lid']) ? $_GET['lid'] : null;
		if(empty($lid)){
			echo 'lid 不能为空';exit;
		}
		
		$locMod = new Location_Model();
		$loc = $locMod->where('lid', $lid)->find();
		if(!$loc->loaded){
			echo "lid为$lid的地点不存在";exit;	
		}
		
		$locDMod = new LocationDetail_Model();
		$source_record = $locDMod->where('lid', $lid)->find_all();
		$content = new Form_Controller();
		$content->set_form('action','detailsave');
		$datas = array();
		foreach($source_record as $sr){
			$datas[] = $sr; 	
		}
		$moreHtml = "<input type='hidden' name='lid' value='".$lid."' />";
		$locName = "<a target='_blank' href='/location/".$lid."'>{$loc->name}</a>";
		$content->add_field('label', 'location', array('label' => '地点信息', 'value' => $locName, 'moreHTML' => $moreHtml));

		$addInfo = "<a href='#' onclick='addInfo()'>添加Detail信息</a>";
		$jsLink = <<<JS
			<script>
				function addInfo(){
					var info = jQuery("<tr><td>项目名称：<input class='detail' autocomplete='off' type='text' name='infoitem[]' /></td><td>项 目 值：<input type='text' name='infovalue[]' /> <a href='#' onclick='$(this).parent(\"td\").parent(\"tr\").remove()'>删除</a></td></tr>");
					$('#table1').find('tbody').append(info);
					info.find("input.detail").trigger("generated");
				}
			</script>
JS;
		if(!empty($datas)){
			$jsDel = <<<DEL
			<script>
				function delDetail(id, lid){
					if(confirm('您确定要删除么?')){
						location.href='/admin/location/detailDel?id='+id+'&lid='+lid;
					}
				}
			</script>
DEL;
			$notEdit = array('团购', '优惠券');
			foreach($datas as $da){
				if(in_array($da->infoitem, $notEdit))
					continue;
				$delDetail = "<a href='#' onclick='delDetail(".$da->id.", ".$lid.")' >删除</a>";
				$content->add_field('input', 'infovalue[]', array('label' => $da->infoitem, 'value' => $da->infovalue, 'moreHTML' => '<input type="hidden" name="infoitem[]" value="'.$da->infoitem.'">'.$delDetail));
			}
			$content->add_field('label', 'noinfo', array('label' => 'Detail信息', 'value' => '', 'moreHTML' => $addInfo.$jsLink.$jsDel));
		}else{
			$content->add_field('label', 'noinfo', array('label' => 'Detail信息', 'value' => '暂无 ', 'moreHTML' => $addInfo.$jsLink));
		}
		
		$content = $content->view($datas, false);
		$content = '编辑(设置)Detail信息：'.$content;
		$this->add_css("admin.css");
		$this->add_js('js/central/admin.location.detail.js');
		$this->set_output(array('content' => $content));
	}
	
	//delete LocationDetail info
	public function detailDel()
	{
		$id = isset($_GET['id']) ? $_GET['id'] : '';
		$lid = isset($_GET['lid']) ? $_GET['lid'] : '';
		if(!$id || !$lid){
			echo 'id 或者 lid 不能为空';exit;
		}
		$locMod = new LocationDetail_Model();
		$locMod->where('id', $id)->find();
		if($locMod->loaded){
			$locMod->delete();
			$this->add_success_message('删除成功');
		}else{
			$this->add_error_message('id为'.$id.'的Detail信息不存在');
		}
		url::redirect('/admin/location/editDetail?lid='.$lid);
	}
	
	//save detail info
	public function detailsave()
	{
		$lid = isset($_POST['lid']) ? $_POST['lid'] : null;
		if(empty($lid)){
			echo 'lid不能为空';exit;
		}
		$infoitem = isset($_POST['infoitem']) ? $_POST['infoitem'] : '';
		$infovalue = isset($_POST['infovalue']) ? $_POST['infovalue'] : '';
		$locMod = new Location_Model();
		$locDMod = new LocationDetail_Model();
		$details = array();
		if(!empty($infoitem)){
			$details = array_combine($infoitem, $infovalue);
		}
		log::debug('details='.json_encode($details));
		$detail_str = '';
		foreach ($details as $item => $value){
			if(!$item)
				continue;
			$detail_str .= $item.' : '.$value.'<br/>';
			$locD = new LocationDetail_Model();
			$locD->where(array('lid'=>$lid, 'infoitem'=>$item))->find();
			if($locD->loaded){
				if($locD->infovalue != $value){
					$locD->infovalue = $value;
					$locD->save();
				}
			}else{
				$locD->add_new_records($lid, array($item => $value));
			}
		}
		echo "<script>alert('编辑成功');opener.document.getElementById('detail').innerHTML = '".$detail_str."';window.close();</script>";
	}
	
	//关联cache操作
	public function linkcache()
	{
		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : null;
		$mod = new Location_Model();
		if(empty($lid)){
			echo "lid不能为空";exit;
		}else{
			$mod->where('lid', $lid)->find();
			if(!$mod->loaded()){
				echo '记录不存在';exit;
			}
		}
		$lcaMod = new LocationCache_Model();
		//TODO linkcache search operate:
		$content = new Grid_Controller();
		$content->set_pk('cacheid');
		$content->add_field('label', 'cacheid', array('label'=>'cacheid'));
		$content->add_field('label', 'lid', array('label'=>'lid'));
		$content->add_field('label', 'city', array('label' => '城市'));
		$content->add_field('label', 'name', array('label'=>'cache地点名'));
		$content->add_field('label', 'longitude', array('label' => '经度'));
		$content->add_field('label', 'latitude', array('label' => '纬度'));
		$content->add_field('label', 'address', array('label'=>'地址'));
		$content->add_field('label', 'srcAgent', array('label' => '来源'));
		$content->add_field('label', 'linkOperate', array('label'=>'操作'));
		$filterCitycode = Input::instance()->get('citycode', null);
		$filterKeywords = Input::instance()->get('keywords', null);
		if(empty($filterCitycode) && empty($filterKeywords)){
			$records = array();
		}else{
			$records = $this->_search($filterCitycode, $filterKeywords);
		}
		
		$datas = array();
		foreach($records as $rcd){
			if($rcd->lid == $mod->lid){
				$linked_href = "(已关联  [ <a target='_blank' href='/location/".$rcd->lid."'>{$mod->name}</a>] )";
				$rcd->name = $rcd->name . $linked_href;
			}elseif($rcd->lid){
				$loc = new Location_Model();
				$loc->where('lid', $rcd->lid)->find();
				if($loc->loaded()){
					$linked_href = "(已关联 [<a target='_blank' href='/location/".$rcd->lid."'>{$loc->name}</a>])";
					$rcd->name = $rcd->name . $linked_href;
				}
			}
			$row = $rcd->as_array();
			$raw = json_decode($row['raw']);
			$cityname = City_Model::get_cityName($mod->citycode);
			$rawCityname = isset($raw->cityname) ? $raw->cityname : ( isset($raw->citycode) ? City_Model::get_cityName($raw->citycode) : '');
			$row['city'] = $rawCityname;
			if($cityname != $rawCityname || $row['lid'] == $lid){
				$row['linkOperate'] = '<a href="#" onclick="cancelCache('.$row['cacheid'].', '.$lid.')">取消关联cache</a>';
			}else{
				$row['linkOperate'] = "<a href='#' onclick=\"linkCache(".$row['cacheid'].', '.$lid.")\">关联到[ {$mod->name}({$lid}) ]</a>";
			}
			if($row['lid']){
				$row['lid'] = "<a target='_blank' href='/admin/location/edit?lid=".$row['lid']."'>{$row['lid']}</a>";
			}
			$datas[] = $row;
		}
		
		log::debug('datas='.json_encode($datas));		
		$linkOpVerify = "\n<script>\nfunction linkCache(cacheid, lid)\n{\n if(confirm('确认进行关联cache操作么？')){\n  location.href = '/admin/location/linkcachesave?op=link&cacheid='+cacheid+'&lid='+lid;\n }\n}\n";
		$cancelOpVerify = "\nfunction cancelCache(cacheid, lid)\n{\n if(confirm('确认取消关联cache操作么？')){\n  location.href= '/admin/location/linkcachesave?op=cancel&cacheid='+cacheid+'&lid='+lid;\n }\n}\n</script>\n";
		$hidden_citycode = '<input type="hidden" name="citycode" value="'.$mod->citycode.'" />';
		$hidden_lid      = '<input type="hidden" name="lid" value="'.$lid.'" />';
		$content->add_filter('label', 'citycode', array(), array('label'=>'citycode','defVal'=>$mod->citycode, 'moreHTML'=> $hidden_citycode.$hidden_lid.$linkOpVerify.$cancelOpVerify));
		$attr = array('placeholder' => '输入地址、名字关键字搜索');
		$content->add_filter('input', 'keywords', array(), array('label'=>'关键字', 'attrs'=> $attr, 'style' => 'width:300px;'));
		$content = $content->view($datas, false);
		$locInfo = $this->_getLocationInfo($mod);
		$lcaInfo = '';
		if($mod->cacheid){
			$linked = $lcaMod->where('cacheid', $mod->cacheid)->find();
			$lcaInfo = '<br/><br/>'.$this->_getLinkedInfo($linked, $mod);
		}
		$content = $locInfo.$lcaInfo.$content;
		$this->set_output(array('content'=>$content));
	}
	
	
	private function _getLocationInfo($location)
	{
		$locInfo = <<<STR
		当前地点信息:
		<table>
			<tr>
				<th>LID</th>
				<th>CACHEID</th>
				<th>citycode</th>
				<th>名称</th>
				<th>经度</th>
				<th>纬度</th>
				<th>地点</th>
			</tr>
			<tr>
				<td>{$location->lid}</td>
				<td>{$location->cacheid}</td>
				<td>{$location->citycode}</td>
				<td>{$location->name}</td>
				<td>{$location->long}</td>
				<td>{$location->lat}</td>
				<td>{$location->address}</td>
			</tr>
		</table>
STR;
		return $locInfo;
	}
	
	private function _getLinkedInfo($linked, $mod)
	{
		if($linked->lid == $mod->lid){
			$op = '<a href="#" onclick="cancelCache('.$linked->cacheid.', '.$mod->lid.')">取消关联cache</a>';
		}else{
			$op = "<a href='#' onclick=\"linkCache(".$linked->cacheid.', '.$mod->lid.")\">关联到[ {$mod->name}({$mod->lid}) ]</a>";
		}
		if($linked->lid){
			$linkHref = "<a href='/admin/location/edit?lid={$linked->lid}' target='_blank'>{$linked->lid}</a>";
		}else{
			$linkHref = '';
		}
		$lcaInfo = <<<STR
		对应cache信息:
		<table>
			<tr>
				<th>CACHEID</th>
				<th>LID</th>
				<th>名称</th>
				<th>经度</th>
				<th>纬度</th>
				<th>地点</th>
				<th>来源</th>
				<th>操作</th>
			</tr>
			<tr>
				<td>{$linked->cacheid}</td>
				<td>{$linkHref}</td>
				<td>{$linked->name}</td>
				<td>{$linked->longitude}</td>
				<td>{$linked->latitude}</td>
				<td>{$linked->address}</td>
				<td>{$linked->srcAgent}</td>
				<td>{$op}</td>
			</tr>
		</table>
STR;
		return $lcaInfo;
	}
	
	//search from engine
	private function _search($citycode, $keyword)
	{
		log::debug('citycode='.$citycode .' | keyword='.$keyword);
		if ($citycode === NULL){
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.missing_argument", "city");
		}
		if ( $keyword === NULL){
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.missing_argument", "keyword");
		}
		$city_object = new City_Model();
		$city_object->find(array('citycode' => $citycode));
		if (!$city_object->loaded()) {
			throw new UKohana_Exception("E_MICO_CITY_NOT_FOUND", "errors.city_not_open");
		}
		$sea_en = new Search();
		return  $sea_en->backSearch($city_object, $keyword);
	}
	
	//关联cahce保存
	public function linkcachesave()
	{
		$op = isset($_GET['op']) ? $_GET['op'] : null;
		$cacheid = isset($_GET['cacheid']) ? $_GET['cacheid'] : null;
		$lid = isset($_GET['lid']) ? $_GET['lid'] : null;	
		if(empty($cacheid) || empty($lid) || empty($op)){
			echo 'Invalid argument!';exit;
		}else{
			log::debug('op='.$op);
			log::debug('cacheid='.$cacheid);
			log::debug('lid='.$lid);
			$lcaMod = new LocationCache_Model();
			$locMod = new Location_Model();
			$loc = $locMod->where('lid', $lid)->find();
			$lca = $lcaMod->where('cacheid', $cacheid)->find();
			if($lca->srcAgent == 'daodao' || $lca->srcAgent == 'top10'){
				echo 'cache的srcAgent为daodao、top10，非法操作';exit;
			}
			if($op == 'link'){
				$raw = json_decode($lca->raw);
				$cityname = City_Model::get_cityName($loc->citycode);
				if((isset($raw->cityname) && $cityname != $raw->cityname)|| (isset($raw->citycode) && $raw->citycode != $loc->citycode)){
					$error_msg = "关联cache地点的citycode与cache地点的citycode不一致，关联失败";
					$this->add_error_message($error_msg);
					url::redirect("/admin/location/linkcache?lid=".$lid);
				}
				
				$lcaMods = new LocationCache_Model();
				$lca_linked = $lcaMods->where('cacheid', $loc->cacheid)->find();
				if($lca_linked->loaded()){
					$lca_linked->lid = 0 ;
					$lca_linked->save();
				}
				
				$loc->cacheid = $cacheid;
				$loc->status = 1;
				$loc->save();
				$lca->lid = $lid;
				$lca->save();
				
				//TODO:: opener operate
				$linkedCache = $lca->name;
				echo "<script>alert('关联成功');opener.document.getElementById('linkedCache').innerHTML = '".$linkedCache."';location.href='/admin/location/linkcache?lid={$lid}';</script>";
			}elseif($op == 'cancel'){
				$loc->cacheid = 0;
				$loc->status = 0;
				$loc->save();
				$lca->lid = 0;
				$lca->save();
				
				//TODO:: opener operate
				echo "<script>alert('取消关联成功');opener.document.getElementById('linkedCache').innerHTML = '暂无 ';location.href='/admin/location/linkcache?lid={$lid}';</script>";
			}
		}
	}
	
 	public function editsave()
 	{

 		$id = isset ( $_POST['lid'] ) ? trim ( $_POST['lid'] ) : null;
 		$type_id = isset ( $_POST['type_id'] ) ? trim ( $_POST['type_id'] ) : null;
 		if(empty($type_id))
 		{
 			echo '请选择分类';exit;
 		}

 		$mod = new location_Model();
 		if(empty($id))
 		{
 			$mod->lid = ID_Factory::next_id($mod);
 		}
  		else
  		{
  			$mod->where('lid',$id)->find();
  			if(!$mod->loaded())
  			{
  				echo "该记录不存在！";exit;
  			}
  		}
  		$hasfile = empty($_FILES['mainpic']['name'])?false:true;
  		if($hasfile)
  		{
  			$upimg = new UploadImage();
			$filename = $upimg->save('mainpic', $mod->citycode.'/loc/'.$id);
  		}



		$isofficial = isset ( $_POST['isofficial'] ) ? trim ( $_POST['isofficial'] ) : 0 ;
		$upgrade = false;
		if($isofficial > $mod->isofficial)
			$upgrade = true;


 		$mod->citycode = isset ( $_POST['citycode'] ) ? trim ( $_POST['citycode'] ) : null;
 		$mod->type_id = $type_id;

 		$mod->name = isset ( $_POST['name'] ) ? trim ( $_POST['name'] ) : null;
 		$mod->title = isset ( $_POST['title'] ) ? trim ( $_POST['title'] ) : null;
 		$mod->content = isset ( $_POST['content'] ) ? trim ( $_POST['content'] ) : null;
 		$mod->address = isset ( $_POST['address'] ) ? trim ( $_POST['address'] ) : null;
 		$mod->long = isset ( $_POST['long'] ) ? trim ( $_POST['long'] ) : null;
 		$mod->lat = isset ( $_POST['lat'] ) ? trim ( $_POST['lat'] ) : null;
 		$mod->rating = isset ( $_POST['rating'] ) ? trim ( $_POST['rating'] ) : null;
 		$mod->price = isset ( $_POST['price'] ) ? trim ( $_POST['price'] ) : null;
 		$mod->neighborhood = isset ( $_POST['neighborhood'] ) ? trim ( $_POST['neighborhood'] ) : null;
 		//$mod->isofficial =$isofficial;
 		$orgIsofficial = $mod->isofficial;
		if($isofficial <= $orgIsofficial)//不升级
		{
			$mod->isofficial =$isofficial;
		}
 		if($hasfile)
 			$mod->mainpic = $filename;
 		$mod->save();

 		/*********************************\
 		 * 关联cache的操作：
 		\*********************************/
// 		if(input::instance()->query('isLinkCache',0)==1) //1、关联cache
// 		{
// 			if(!empty($mod->cacheid))
// 			{
// 				$locCache = new LocationCache_Model($mod->cacheid);
// 				if($locCache->find()->loaded())
// 				{
// 					$locCache->lid = $mod->lid;
// 					$locCache->save();
// 					//$LocationCacheName = $locCache->name;
// 				}
// 			}
// 		}
// 		else
// 		{                                          //0、取消关联cache操作
// 			if(!empty($mod->cacheid))
// 			{
// 				$locCache = new LocationCache_Model($mod->cacheid);
// 				if($locCache->find()->loaded())
// 				{
// 					$locCache->lid = 0;
// 					$locCache->save();
// 				}
// 			}
// 		}



		$returnUrl = empty($_POST['reurnURL'])?'/admin/location/index':$_POST['reurnURL'];
		if($isofficial > $orgIsofficial)//升级
		{
			if(!$this->_checkSameUpGuideLoc($mod->cacheid,$mod->lid))//是否需要合并
			{
				$href = "/admin/location/mergeSameLocation/".$mod->lid."?reurnURL=$returnUrl";
				$html='地点已经修改,升级该地点时发现存在相同的官方地点。是否合并？<br><a href="'.$href.'">是</a>,<a href="'.$returnUrl.'" >放弃升级</a>?';
				$this->set_output(array('pagenation'=>'','content'=>$html));
				return ;

			}
		}


 		//$mod->add_Category($category);


 		$this->add_success_message('保存成功！');
 		url::redirect($returnUrl);
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


 	public function setcategory()
 	{
 		$lid = isset ( $_GET['id'] ) ? trim ( $_GET['id'] ) : null;
 		$typeid = isset ( $_GET['typeid'] ) ? trim ( $_GET['typeid'] ) : null;

 		$loc_mod = new Location_Model();
 		$loc_mod->where('lid',$lid)->find();
 		$categorys = $loc_mod->get_Categorys();
 		$hasCategory = array();
 		foreach($categorys as $ct)
 		{
 			$hasCategory[]= $ct['id']  ;
 		}


 		$mod = new Category_Model();
 		$source_record = $mod->where('type_id',$typeid)->find_all();

 		$content = new Grid_Controller();
 		$content->set_pk('id');
 		$content->set_form('action',"/admin/location/savecategory?lid=$lid");
 		$content->add_field('label','name',array('label'=>'name','order'=>true));
 		$content->set_delbtnval('确定');
 		$content->set_checkrows($hasCategory);

 		$content = $content->view($source_record,false);

		$this->set_output(array('content'=>$content));

 	}
 	public function savecategory()
 	{
 		$lid = isset ( $_GET['lid'] ) ? trim ( $_GET['lid'] ) : null;

 	 	$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();

 		$mod = new location_Model();
  		$mod->where('lid',$lid)->find();
  		if(!$mod->loaded())
  		{
  			echo "该记录不存在！";exit;
  		}
  		$mod->set_Category($ids);

  		echo "<script>alert('保存成功');opener.location.reload();window.close();</script>";exit;


 	}
 	public function record_del()
 	{
 		$ids = isset ( $_POST['checkrow'] ) ?   $_POST['checkrow']   : array();

 		foreach($ids as $id)
 		{
 			$gp = new location_Model();
 			if($gp->where('lid',$id)->find()->loaded())
 			{
 			$gp->delete();}
 		}
 		$this->add_success_message('删除成功！');
 		//header("Location: /admin/location");
 		url::redirect('/admin/location/index');
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
 		//header("Location: /admin/group");
 		url::redirect('/admin/location/index');

 	}
	public function sameLocation()
	{
		$content = new Grid_Controller();
 		$content->set_addurl('location/add');
 		$content->set_pk('cacheid');
 		$content->set_form('action',"/admin/location/record_del");
 		$content->add_field('label','cacheid',array('label'=>'cacheid' ));
 		$content->add_field('label','cnt',array('label'=>'出现次数'));
 		$content->add_field('label','citycode',array('label'=>'城市'));
 		$content->add_field('label','name',array('label'=>'地点名'));
 		$content->add_field('link','cacheid',array('label'=>'操作','text'=>'处理','href'=>'/admin/location/doSameLocation?cid={0}'));
 		/*$content->add_field('label','long',array('label'=>'经度'));
 		$content->add_field('label','lat',array('label'=>'纬度'));
 		$content->add_field('label','isofficial',array('label'=>'是否官方'));
 		$content->add_field('label','updated',array('label'=>'修改日期'));
 		$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('link','lid',array('label'=>'操作','text'=>'修改','href'=>'/admin/location/edit?lid={0}'));*/

 		//

		$pagesize = 20 ;
 		$offset = isset($_GET['page'])?$_GET['page']-1:0;
 		$offset *= $pagesize;
 		$limit = "limit $offset ,$pagesize";


 		$db = & Database::instance();
 		$sqlcnt = "SELECT cacheid
				FROM `{$db->table_prefix()}Locations`
				WHERE cacheid >0 and isofficial=1
				GROUP BY cacheid
				HAVING count(cacheid)  >1 ";
 		$cnt = count($db->query($sqlcnt)) ; //总数

 		$sql = "SELECT cacheid, count( cacheid ) as cnt,lid,name,citycode
				FROM `{$db->table_prefix()}Locations`
				WHERE cacheid >0 and isofficial=1
				GROUP BY cacheid
				HAVING cnt  >1
				ORDER BY `cnt` DESC $limit";

		$result = $db->query($sql) ;
		$records = array();

 		foreach($result as $rcd)
 		{
			$newrow = array();
			foreach($rcd as $key=>$val)
			{
				$newrow[$key]=$val;

			}
			$records[] = $newrow;
 			//$rcd->type_id = Location::getTypeName($rcd->type_id);
 			//$rcd->name = '<a href="/location/'.$rcd->lid.'" target="_blank">'.$rcd->name.'</a>';//		http://dev.yanzi.com/87
 		}

 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize, 'style'=>'admin'));

 		//$order = isset($_GET['order'])?$_GET['order']:"rid";
 		//$sort = isset($_GET['sort'])?$_GET['sort']:"DESC";

 		$datas = $records;

 		$this->add_css('admin.css');
 		$this->add_js('js/central/admin.location.js');

 		$content = $content->view($datas,false);
 		$this->set_output(array('pagenation'=>$pagenation,'content'=>$content));

	}

	public function doSameLocation()
	{
		$content = new Grid_Controller();
 		$content->set_pk('lid');
 		$content->add_field('label','lid',array('label'=>'cacheid' ));
 		$content->add_field('label','citycode',array('label'=>'城市'));
 		$content->add_field('label','name',array('label'=>'地点名'));
 		$content->add_field('label','relatedGuide',array('label'=>'相关攻略'));
 		$content->add_field('link','lid',array('label'=>'操作','text'=>'合并到其他地点','href'=>'/admin/location/mergeSameLocation/{0}'));

		$cid = Input::instance()->get('cid',null);
		$mod = new Location_Model();
		$mod->where(array('cacheid'=>$cid,'isofficial'=>1));
		$results = $mod->find_all();

		foreach($results as $row)
		{
			$newrow['lid'] = $row->lid;
			$newrow['citycode'] = $row->citycode;
			$newrow['name'] = $row->name;

			$newrow['relatedGuide'] =$this->_getRelatedGuideByLocation($row->lid);
			$datas[] = $newrow;
		}
 		//$datas = $results;

 		//$this->add_css('admin.css');
 		//$this->add_js('js/central/admin.location.js');

 		$content = $content->view($datas,false);
 		$this->set_output(array('pagenation'=>'','content'=>$content));

	}

	public function mergeSameLocation($locid)
	{
		$baseLoc = new Location_Model($locid);
		$baseLoc->find()->loaded();

		$data['baseLoc'] = $baseLoc;
		$data['relativeGuideStr'][$locid] = $this->_getRelatedGuideByLocation($locid);


		$cacheid = $baseLoc->cacheid;
		$locmod = new Location_Model();
		$sameLocs = $locmod->where(array('lid<>'=>$locid,'cacheid'=>$cacheid,'isofficial'=>1))->find_all();
		foreach($sameLocs as $sameloc)
		{
			$data['relativeGuideStr'][$sameloc->lid] = $this->_getRelatedGuideByLocation($sameloc->lid);
		}
		$data['sameLocs'] = $sameLocs;
		$data['sameLocsCount'] = count($sameLocs);

		$reurnURL = empty($_GET['reurnURL'])?'':$_GET['reurnURL'];
		$data['reurnURL'] = $reurnURL;
		$this->set_contentview('admin/mergeLocations');
		$this->set_output($data);
	}

	public function mergeSameLocationSave($lid,$mergeToLid)
	{
		//替换 guideLocation
		//var_dump($_GET['reurnURL']);exit;
		$glmod = new GuideLocations_Model();
		$where['lid']=$lid;
		$gls = $glmod->where($where)->find_all();
		foreach($gls as $gl)
		{
			$gl->lid= $mergeToLid;
			$gl->save();
		}
		//替换 guidelocationComment
		$glcmod = new GuideLocationComment_Model();
		$where['lid']=$lid;
		$glcs = $glcmod->where($where)->find_all();
		foreach($glcs as $glc)
		{
			$glc->lid= $mergeToLid;
			$glc->save();
		}

		//删除location
		$cid=0;
		$location = new Location_Model($lid);
		if($location->find()->loaded())
		{
			$cid = $location->cacheid;
			$location->delete();
		}
		$returnUrl = empty($_GET['reurnURL'])?"/admin/location/doSamelocation?cid=$cid":$_GET['reurnURL'];

		echo "合并完成 <a href=\"$returnUrl\">返回</a>";

	}


	private function _getRelatedGuideByLocation($lid)
	{
		$glmod = new GuideLocations_Model();
		$where['lid']=$lid;
		$gls = $glmod->where($where)->find_all();
		$returnStr = "";
		foreach($gls as $gl)
		{

			$returnStr .= '<a target="_blank" href="/city/guide/'.$gl->guide->citycode.'/'.$gl->guide->gid.'">'.
					$gl->guide->name.'</a>';
			if($gl->guide->isOfficial==0)
			{
				$returnStr .= "[非]，";

			}
			else
				$returnStr .= "，";
		}
		return $returnStr;
	}
 	////////////////////////////////////////////////////////
 	function getCityList()
 	{
 		$citymod = new City_Model();
 		$citys = $citymod->where('isopen',1)->find_all();
 		//$rst=array();
 		$rst=array(array('label'=>'全部','value'=>''));
 		foreach($citys as $cty)
 		{
 			$newitem['label'] = $cty->cityname;
 			$newitem['value'] = $cty->citycode;
 			$rst['开放'][] = $newitem;
 		}
 		$citys = $citymod->where('isopen',0)->find_all();
 		foreach($citys as $cty)
 		{
 			$newitem['label'] = $cty->cityname;
 			$newitem['value'] = $cty->citycode;
 			$rst['即将开放'][] = $newitem;
 		}
 		return $rst;
 	}
	function getLocationTypeList()
 	{
 		$ary = Location::getTypeList();

 		$rst=array(array('label'=>'全部','value'=>''));
 		foreach($ary as $key=>$val)
 		{
 			$rst[]=array('label'=>Location::getTypeName($key),'value'=>$key);
 		}
 		return $rst;
 	}
	function getBoolList()
 	{
 		$rst[] = array('label'=>'是','value'=>'1');
 		$rst[] = array('label'=>'否','value'=>'0');
 		return $rst;
 	}
	function br2nl($message){
		$message = str_replace("<br>\n","\n",$message);
		$message = str_replace("\r\n\r\n","\n",$message);
		$message = str_replace("\n\r\n","\n",$message);
		return $message;
	}
	private function _getDetailInfo($lid)
	{
		$details = '';
		$notEdit = array('团购', '优惠券');
		$locDetailMod = new LocationDetail_Model();
		$locDes = $locDetailMod->where('lid', $lid)->find_all();
		foreach($locDes as $ld){
			if(in_array($ld->infoitem, $notEdit))
				continue;
			$details .= $ld->infoitem.':'.$ld->infovalue.'<br/>';
		}
		return $details;
	}

} // End Index_Controller
