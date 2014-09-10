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
class Camp_Controller extends Admin_Controller
{
    const RIGHT_DB_STATUS = 3;

	protected $current_page='/admin/camp';


	public function index()
	{
		return $this->record();
	}

	public function record()
 	{
 		$content = new Grid_Controller();
 		$content->set_addurl('/admin/camp/add');
 		$content->set_pk('camp_id');
 		//$content->set_form('action',"/admin/category/record_del");
 		$content->add_field('label','camp_id',array('label'=>'camp_id','order'=>true));
 		$content->add_field('label','name',array('label'=>'活动代号'));
 		$content->add_field('label','title',array('label'=>'活动标题'));
 		$content->add_field('label','keywords',array('label'=>'微博关键字'));
        $content->add_field('label','category',array('label'=>'类目'));
        //$content->add_field('label','created',array('label'=>'创建日期'));
 		$content->add_field('link','camp_id',array('label'=>'微博列表','text'=>'查看','href'=>'/admin/camp/tweets?id={0}'));
 		$content->add_field('link','camp_id',array('label'=>'预览','text'=>'查看','href'=>'/admin/camp/waterfall?id={0}'));
 		$content->add_field('label','status',array('label'=>'抓取状态'));
 		$content->add_field('link','camp_id',array('label'=>'操作','text'=>'修改','href'=>'/admin/camp/edit?id={0}'));


 		$mod = new Camp_Model();
 		$cnt =  $mod->count_all();
 		$order = isset($_GET['order'])?$_GET['order']:'status';
 		$sort = isset($_GET['sort'])?$_GET['sort']:'desc';
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
            if($rcd->db_status <>self::RIGHT_DB_STATUS){
                $rcd->status = "<a class='status_init_op ' href='/admin/camp/init?id=".$rcd->camp_id."' >未初始化</a>";
            }elseif($rcd->status==0){
                $rcd->status = "<a class='status_open_op' href='/admin/camp/open?id=".$rcd->camp_id."' >已关闭</a>";
            }elseif($rcd->status==1){
                $rcd->status = "<a class='status_close_op' href='/admin/camp/close?id=".$rcd->camp_id."' >开启中</a>";
            }
 			$rcd->keywords = str_replace(',',' | ',$this->_getJson($rcd->keywords));
 			$rcd->category = str_replace(';',' | ',$this->_getJsonCategory($rcd->category));
 		}
 		$pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize,'style'=>'admin'));

 		//$order = isset($_GET['order'])?$_GET['order']:"camp_id";
 		//$sort = isset($_GET['sort'])?$_GET['sort']:"DESC";

        $this->add_js('js/central/admin.camp.js');
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

 		$mod = new Camp_Model();
 		$source_record = $mod->where('camp_id',$id)->find()->as_array();
        $source_record['keywords'] = $this->_getJson($source_record['keywords']);
        $source_record['category'] = $this->_getJsonCategory($source_record['category']);

 		$content = new Form_Controller();
 		$content->set_form('action','editsave');
 		$content->add_field('label','camp_id',array('label'=>'ID','moreHTML'=>'<input type="hidden" name="id" value="'.$id.'">'));
        if($mod->db_status<>0)
            $content->add_field('label','name',array('label'=>'活动代号','style'=>'font-size:22px;','moreHTML'=>' 活动已经初始化过，不能修改代号<input type="hidden" name="name_not_edit" value="1">'));
        else
            $content->add_field('input','name',array('label'=>'活动代号','style'=>'width:206px;','moreHTML'=>' 使用简短的英文单词组成，只允许4至20位小写英文字母和数字'));
        $content->add_field('input','title',array('label'=>'活动标题','style'=>'width:306px;'));
 		$content->add_field('input','keywords',array('label'=>'微博关键字','style'=>'width:406px;','moreHTML'=>' 按英文逗号","分割多个关键字'));
 		$content->add_field('input','category',array('label'=>'类目','style'=>'width:406px;','moreHTML'=>' 格式：{分类名1}:{关键字1},{关键字2};{分类名2}:{关键字3},{关键字4};按封号";"分割多个类目'));
 		$content = $content->view($source_record,false);
		$this->set_output(array('content'=>$content));
	}


 	public function editsave()
 	{
        $id = isset ( $_POST['id'] ) ? trim ( $_POST['id'] ) : null;
 		$name = isset ( $_POST['name'] ) ? trim ( $_POST['name'] ) : null;
 		$title = isset ( $_POST['title'] ) ? trim ( $_POST['title'] ) : null;
 		$keywords = isset ( $_POST['keywords'] ) ? trim ( $_POST['keywords'] ) : null;
 		$category = isset ( $_POST['category'] ) ? trim ( $_POST['category'] ) : null;
        $nameNotEdit = isset ( $_POST['name_not_edit']) && $_POST['name_not_edit']==1 ? true : false;

        if(($name==null && $nameNotEdit == false) || $title==null || $keywords==null){
            if($name==null && $nameNotEdit == false){
                $this->add_error_message('代号不能为空');
            }elseif($title==null){
                $this->add_error_message('标题不能为空');
            }elseif($keywords==null){
                $this->add_error_message('微博关键字不能为空');
            }
            if(empty($id)){
                url::redirect('/admin/camp/add');
            }else{
                url::redirect('/admin/camp/edit?id='.$id);
            }
        }
        if($nameNotEdit == false && (preg_match("/^[0-9a-z]+$/",$name,$match)==0 || strlen($name)>20 || strlen($name)<4)){
            $this->add_error_message('代号格式不正确,只允许4至20位小写英文字母和数字');
            if(empty($id)){
                url::redirect('/admin/camp/add');
            }else{
                url::redirect('/admin/camp/edit?id='.$id);
            }
        }

        if($nameNotEdit==false){
            //判断 name 重复
            $modUnique = new Camp_Model();
            $modUnique->where('name',$name)->find();
            if($modUnique->loaded() && ($id==null || $id!=$modUnique->camp_id)){
                $this->add_error_message('代号已存在，不能重复');
                if(empty($id)){
                    url::redirect('/admin/camp/add');
                }else{
                    url::redirect('/admin/camp/edit?id='.$id);
                }
            }
        }

 		$mod = new Camp_Model();
 		if(empty($id))
 		{
 			$mod->camp_id = ID_Factory::next_id($mod);
            $mod->name = $name;
 			$mod->title = $title;
 			$mod->keywords = $this->_jsonEncode($keywords);
 			$mod->category = $this->_jsonEncodeCategory($category);
 			$mod->save();
 		}
 		else
 		{
 			$mod->where('camp_id',$id)->find();
 			if($mod->loaded())
 			{
                if($nameNotEdit==false)
                    $mod->name = $name;
	 			$mod->title = $title;
                $mod->keywords = $this->_jsonEncode($keywords);
                $mod->category = $this->_jsonEncodeCategory($category);
                $mod->save();
 			}
 			else
 			{
 				echo "该记录不存在！";exit;
 			}

 		}
 		$this->add_success_message('保存成功！');

 		url::redirect('/admin/camp');
 	}

    public function open()
    {
        $id = isset ( $_GET['id'] ) ? trim ( $_GET['id'] ) : null;

        $mod = new Camp_Model();
        if(empty($id))
        {
            echo "id为空！";exit;
        }
        else
        {
            $mod->where('camp_id',$id)->find();
            if($mod->loaded())
            {
                $mod->status = 1;
                $mod->save();
                $this->add_success_message('活动['.$mod->name.']开启成功！');
            }
            else
            {
                echo "该记录不存在！";exit;
            }
        }
        url::redirect('/admin/camp');
    }

    public function close()
    {
        $id = isset ( $_GET['id'] ) ? trim ( $_GET['id'] ) : null;

        $mod = new Camp_Model();
        if(empty($id))
        {
            echo "id为空！";exit;
        }
        else
        {
            $mod->where('camp_id',$id)->find();
            if($mod->loaded())
            {
                $mod->status = 0;
                $mod->save();
                $this->add_success_message('活动['.$mod->name.']关闭成功！');
            }
            else
            {
                echo "该记录不存在！";exit;
            }
        }
        url::redirect('/admin/camp');
    }

    public function init()
    {
        $id = isset ( $_GET['id'] ) ? trim ( $_GET['id'] ) : null;

        $camp = new Camp_Model();
        if(empty($id))
        {
            echo "id为空！";exit;
        }
        else
        {
            $camp->where('camp_id',$id)->find();
            if($camp->loaded())
            {
                if($camp->db_status==0){
                    $this->createTable($camp);
                }
                $this->add_success_message('活动['.$camp->name.']初始化成功！');
            }
            else
            {
                echo "该记录不存在！";exit;
            }
        }
        url::redirect('/admin/camp');
    }

    protected function createTable($camp){
        $db = & Database::instance();
        $name = $camp->name;
        if($camp->db_status==0){
            try {
                $result = $db->query("
                        CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Camp_{$name}_Tweet` (
                            `tweet_id` BIGINT(20) NOT NULL COMMENT  '微博id',
                            `pic` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '微博图片',
                            `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '微博内容',
                            `uid` BIGINT NOT NULL COMMENT  '微博作者新浪id',
                            `name` VARCHAR( 200 ) NOT NULL COMMENT  '作者名字',
                            `avatar` VARCHAR( 200 ) NOT NULL COMMENT  '作者头像',
                            `link` VARCHAR( 200 ) NOT NULL COMMENT  '作者链接',
                            `heat` INT( 11 ) NOT NULL COMMENT  '热度',
                            `source` VARCHAR( 50 ) NOT NULL COMMENT  '来源',
                            `created` INT(11) NOT NULL COMMENT  '抓取时间',
                            `updated` INT NOT NULL COMMENT  '最后更新时间',
                            PRIMARY KEY (  `tweet_id` )
                        ) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT =  '微博表';
                        ");
                echo  'Camp_'.$name.'_Tweet created.<br />';
                $camp->db_status = 1;
                $camp->save();
            }
            catch (Kohana_Database_Exception $ex)
            {
                echo "Error occurs on craeted Table Camp_".$name."_Tweet:{$ex->getMessage()}<br />";
            }
        }

        if($camp->db_status==1){
            try {
                $result = $db->query("
                                CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Camp_{$name}_TweetComments` (
                                 `tweetComments_id` BIGINT(20) NOT NULL COMMENT  '微博评论id',
                                 `tweet_id` BIGINT(20) NOT NULL COMMENT  '微博id',
                                 `content` TEXT NOT NULL COMMENT  '评论内容',
                                 `name` VARCHAR( 200 ) NOT NULL COMMENT  '评论者姓名',
                                 `avatar` VARCHAR( 200 ) NOT NULL COMMENT  '评论者头像',
                                 `link` VARCHAR( 200 ) NOT NULL COMMENT  '评论者链接',
                                 `created` INT NOT NULL ,
                                 PRIMARY KEY (  `tweetComments_id` ),
                                 KEY `tweet_id` (`tweet_id`)
                                ) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT =  '微博评论表';
                            ");
                echo  'Camp_'.$name.'_TweetComments created.<br />';
                $camp->db_status = 2;
                $camp->save();
            }
            catch (Kohana_Database_Exception $ex)
            {
                echo "Error occurs on craeted Table Camp_".$name."_TweetComments:{$ex->getMessage()}<br />";
            }
        }

        if($camp->db_status==2){
            try {
                $result = $db->query("
                            CREATE TABLE  IF NOT EXISTS  `{$db->table_prefix()}Camp_{$name}_Category` (
                              `id` int(11) NOT NULL COMMENT 'id',
                              `tweet_id` bigint(20) NOT NULL,
                              `keyword` varchar(20) NOT NULL,
                              PRIMARY KEY (`id`),
                              KEY `tweet_id_keyword` (`keyword`, `tweet_id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                            ");
                echo  'Camp_'.$name.'_Category created.<br />';
                $camp->db_status = 3;
                $camp->save();
            }
            catch (Kohana_Database_Exception $ex)
            {
                echo "Error occurs on craeted Table Camp_".$name."_Category:{$ex->getMessage()}<br />";
            }
        }
    }


    private function _jsonEncode($val){
        $val_arr = explode(',', $val);
        if(is_array($val_arr) && count($val_arr)>1){
            return json_encode($val_arr);
        }else{
            return json_encode(array($val));
        }
    }

    private function _jsonEncodeCategory($val){
        //$val = '分类名1:"#3213213#","ss";分类名2:"大电视","分享图片"';
        /*$val = '分类名1:#3213213#,ss';*/
        $val_arr = explode(';',$val);
        //var_dump($val_arr);
        $result = array();
        foreach($val_arr as $v){
            $v_arr = explode(':',$v);
            if(is_array($v_arr) && count($v_arr)==2){
                $result[$v_arr[0]] = explode(',',$v_arr[1]);
            }
        }
        return json_encode($result);
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

    private function _getJsonCategory($json)
    {
        //$json = '{"{\u5206\u7c7b\u540d1}":["{\u5173\u952e\u5b571}","{\u5173\u952e\u5b572}"],"{\u5206\u7c7b\u540d2}":["{\u5173\u952e\u5b573}","{\u5173\u952e\u5b574}"]}';
        $arr = json_decode($json,true);
        $arrTmp = array();
        if($arr){
            if(is_array($arr)){
                foreach($arr as $k => $v){
                    if(!empty($v)){
                        $arrTmp[]= $k.':'.implode(',',$v);
                    }
                }
                return implode(';',$arrTmp);
            }else{
                return $json;
            }
        }
        return $json;
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


    public function tweets()
    {
        $id = isset ( $_GET['id'] ) ? trim ( $_GET['id'] ) : null;
        $camp = new Camp_Model();
        $camp->where('camp_id',$id)->find();
        if(!$camp->loaded())
        {
            $this->add_success_message('活动id不存在！');
            url::redirect('/admin/camp');
        }
        else
        {
            $categories = array();
            $cateModel = new Camp_Standard_Category_Model(null,$camp->name);
            foreach($cateModel->groupby('keyword')->find_all()->as_array() as $c){
                $categories[] = $c->keyword;
            }

            $content = new Grid_Controller();
            //$content->set_addurl('/admin/lottery/addAward?actid='.$actid);
            $content->set_pk('tweet_id');
            //$content->set_form('action','/admin/lottery/awardDel?actid='.$actid);
            $content->add_field('label','tweet_id',array('label'=>'微博id','order'=>true));
            $content->add_field('label','content',array('label'=>'内容'));
            if(!empty($categories))
                $content->add_field('label','link',array('label'=>'分类'));  //借用link 字段 表示分类
            $content->add_field('label','name',array('label'=>'作者名字','order'=>true));
            $content->add_field('label','avatar',array('label'=>'作者头像'));

            $content->add_filter('input','content',array('method'=>'where'),
                array('label'=>'微博内容', 'defVal'=>''));
            if(!empty($categories)){
                $cateSearchOptions = array(array('label'=>'-----','value'=>''));
                foreach($categories as $cate){
                    $cateSearchOptions[] = array('label'=>$cate,'value'=>$cate);
                }
                $content->add_filter('select','category',array('method'=>'where'),
                    array('label'=>'分类', 'defVal'=>'', 'values'=>$cateSearchOptions));
            }

            $tweets = new Camp_Standard_Tweet_Model(null,$camp->name);
            $order = isset($_GET['order'])?$_GET['order']:'';
            $sort = isset($_GET['sort'])?$_GET['sort']:'asc';
            if(!empty($order))
            {
                $tweets = $tweets->orderby($order,$sort);
            }
            $pagesize = 100;
            $offset = isset($_GET['page'])?$_GET['page']-1:0;
            $offset *= $pagesize;
            $tweets = $tweets->limit($pagesize,$offset);

            //$awdid = isset($_GET['awdid'])?$_GET['awdid']:'';
            //$isopen = isset($_GET['isopen']) ? $_GET['isopen'] : 1;
            $where = array();
            $like = array();
            /*if(!empty($actid))
            {
                $where['actid'] = $actid;
            }*/



            $contentSearch = isset($_GET['content'])?$_GET['content']:'';
            if($contentSearch!='')
                $like['content'] = $contentSearch;
            $records = $tweets->where($where)->like($like);
            $cateSearch = isset($_GET['category'])?$_GET['category']:'';
            if($cateSearch!=''){
                $tweetCateIdArr = array();
                $tweetCateModel = new Camp_Standard_Category_Model(null,$camp->name);
                foreach($tweetCateModel->where('keyword',$cateSearch)->find_all()->as_array() as $tCate){
                    $tweetCateIdArr[]=$tCate->tweet_id;
                }
                if(!empty($tweetCateIdArr))
                    $records = $records->in('tweet_id',$tweetCateIdArr);
            }
            $records = $records->find_all()->as_array();
            $cnt =  count($records);
            //$cnt =  $tweets->where($where)->like($like)->in($in)->count_all();
            $pagenation = new Pagination(array('total_items'=>$cnt,'items_per_page'=>$pagesize, 'style'=>'admin'));
            /*$order = isset($_GET['order'])?$_GET['order']:'';
            $sort = isset($_GET['sort'])?$_GET['sort']:'asc';
            if(!empty($order))
            {
                $tweets = $tweets->orderby($order,$sort);
            }*/
            $tweetIds = array();
            if(!empty($records)){
                foreach($records as $row)
                {
                    $tweetIds[] = $row->tweet_id;
                    $row->avatar = "<img src='".$row->avatar."' height=40 />";
                    /*$award = $row->award->where('awdid',$row->awdid)->find();
                    $row->awdid = $award->awdName;
                    $row->created = date('Y-m-d H:i',$row->created);
                    $um = new User_Model($row->uid);
                    $um->find();
                    $row->uid = $row->uid.' - '.$um->nickname;*/
                    //$row->status = $row->status==1?"<a href='/admin/lottery/close?actid=".$row->actid."'>进行中</a>":"<a href='/admin/lottery/open?actid=".$row->actid."'>已关闭</a>";
                    //$row->updated = date('y-m-d H:i:s',$row->updated);
                }
                if(!empty($categories)){
                    $tweetCateArr = array();
                    $tweetCateModel = new Camp_Standard_Category_Model(null,$camp->name);
                    foreach($tweetCateModel->in('tweet_id',$tweetIds)->find_all()->as_array() as $tCate){
                        $tweetCateArr[$tCate->tweet_id][]=$tCate->keyword;
                    }
                    foreach($records as $row){
                        $row->link = isset($tweetCateArr[$row->tweet_id])?implode(', ',$tweetCateArr[$row->tweet_id]):' ';
                    }
                }
                $tweetCommArr = array();
                $tweetCommModel = new Camp_Standard_TweetComments_Model(null,$camp->name);
                foreach($tweetCommModel->in('tweet_id',$tweetIds)->find_all()->as_array() as $tComm){
                    $tweetCommArr[$tComm->tweet_id][]=$tComm->tweetComments_id;
                }
                foreach($records as $row){
                    $row->content .= isset($tweetCommArr[$row->tweet_id])?'<br/>'.count($tweetCommArr[$row->tweet_id]).'条评论':' ';
                }
            }

            $datas = $records;

            $this->add_css('admin.css');
            $this->add_js('js/central/admin.camp.js');
            $this->set_js_context('camp_id',$id);
            $content = $content->view($datas,false);
            $content = "<h3> 活动【".$camp->title."】 的微博记录</h3>".$content;
            $this->set_output(array('pagenation'=>$pagenation,'content'=>$content));
        }
    }


    public function waterfall()
    {
        $id = isset ( $_GET['id'] ) ? trim ( $_GET['id'] ) : null;
        $camp = new Camp_Model();
        $camp->where('camp_id',$id)->find();

        if(!$camp->loaded())
        {
            $this->add_success_message('活动id不存在！');
            url::redirect('/admin/camp');
        }
        else
        {
           $http = $_SERVER['HTTP_HOST'];

            $content = '<link rel="stylesheet" href="/res/css/waterfall/layout.css">
<link rel="stylesheet" href="/res/css/waterfall/main.css">
<link rel="stylesheet" href="/res/css/waterfall/photobook.css"/>
<link rel="stylesheet" href="/res/css/waterfall/waterfall.css">';
            $content .= '<div class="phtobookMain">
    <ul id="waterfall">
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <div class="clear"></div>
    </ul>
    <div class="loading"  style="display: none;"> <img  id="loading_start" style="display:none;" src="/res/images/loading.gif">
    <span id="loading_more" style="display:none;cursor:pointer;">点击加载更多...</span>
    <img  id="loading_end" style="display:none;" src="/res/images/end.png"> </div>
  </div>';
            $content.= '<script type="text/javascript">
var js_context={
  "base_url"      : "http:\/\/'.$http.'\/",
  "res_url"       : "http:\/\/'.$http.'\/feature\/res\/",
  "js_url"        : "http:\/\/'.$http.'\/res\/",
  "waterfall"     : "http:\/\/'.$http.'\/waterfall\/waterfall",
  //"keyword"       : "数字",
   "tweet"         : "Camp_'.$camp->name.'_Tweet_Model",
  "tweetComments" : "Camp_'.$camp->name.'_TweetComments_Model",
  //"cateModel"    : "Camp_test123_Cate_Model",
  //"order"         : "new"
  "camp_name"      : "'.$camp->name.'"
};
</script>
<script type="text/javascript" src="/res/js/central/waterfall.js" ></script><style>.main {overflow: visible;}</style>';

            $this->set_output(array('content'=>$content));
        }
    }
} // End Index_Controller



?>