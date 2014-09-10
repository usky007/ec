<?php
class Vote_Controller extends LayoutController {
	public function index($voteid='')
	{
		/**
		 * 检测是否从地图网站转过来
		 * */
// 		if(!isset($_SERVER['HTTP_REFERER']))
// 			throw new UKohana_Exception('E_PAGE_NOT_FOUND',"errors.ui_not_found");
		$vote_user = user::get_information();
// 		$session = Session::instance();
// 		$session->set('vote_user',312312312);
		$data = array();
		$voteid = $voteid==''?'':'_'.$voteid;
		$this->set_view("vote/vote".$voteid);
		
		$this->get_layout()
		->add_js("js/central/vote.js")
		->add_css("central.css")
		->add_css("mobile.css")
		->set_title(kohana::lang('投票'));
		$this->set_js_context('vote_user',$vote_user);
		$this->set_output($data);
	}

	public function test()
	{
		// $t = Vote_Model::sgetSetting('test');
		// var_dump($t);
		$s = array();
		$s['col_setting']['europe'] = array('count' => 2, 'limit_type' => '<=');
		$s['col_setting']['asia'] = array('count' => 3, 'limit_type' => '<=');
		$s['col_setting']['test'] = array('count' => 3, 'limit_type' => '<=');

		//echo json_encode($s);
		$txt = '我刚参与了由旅行者传媒发起的#2013旅行者行业大奖#公众票选活动，为我心目中最佳酒店与度假村投了票，参与活动还有机会赢得丰厚大奖：http://www.tclub.cn/Awards2013/rule.html';
		echo urlencode($txt);

	}
}