<?php
	/**
	 * West Australia Controller
	 *
	 * @package camp_west_au_2013
	 * @author cuiyulei
	 **/
	class Wa_Controller extends LayoutController
	{
		const WA_URL = 'wa';
	
		public function __construct()
		{
			parent::__construct();
			$this->add_dialog("overlaywa");
			$this->positions["headers"] = '';
			$this->positions["footers"] = '';
		}

		public function __call($method, $arguments=null)
		{
			// if (empty($arguments)) 
			// {
			// 	return $this->index($method);
			// }
			// else 
			// {
			// 	$arg1 = $arguments[0];
			// 	if (method_exists($this,$arg1)) {
			// 		$arguments[0] = $method;
			// 	}
			// 	else {
			// 		$arg1 = 'type';
			// 		array_unshift($arguments, $method);
			// 	}
			// 	return call_user_func_array(array($this, $arg1), $arguments);
			// }
			url::redirect('http://feature.uutuu.com/WA/live.html', '301');
			
		}
		
		public function index($order='new', $keyword = '')
		{
			url::redirect('http://feature.uutuu.com/WA/live.html', '301');

			// $this->get_layout()
			// 	 ->set_title("有奖活动_西澳美食达人秀_旅行者传媒")
			// 	 ->add_js("js/central/westau.js")
			// 	 ->add_css( "css/central/westau/sub_pubu.css" )
			// 	 ->add_css( "css/central/westau/pubu_ad.css" );
			// $this->_prepare_header();
			// $cat_mod = new Camp_Br_Category_Model();
			// $cates = $cat_mod->find_all();
			// $keywords = array( '全部' => 'all');
			// foreach ($cates as $v){
			// 	$keywords[$v->name] = $v->key;
			// }
			// $data['order'] = $order;
			// $data['keyword'] = $keyword;
			// $data['keywords']= $keywords;
			// $data['britainUrl'] = self::WA_URL;
			// $this->set_output($data);
			// $this->set_js_context('order', $order);
			// $this->set_js_context('keyword', $keyword);
			// // $this->set_js_context('userinfo', 'Camp_Wa_UserInfos_Model');
			// $this->set_js_context('tweet', 'Camp_Wa_Tweet_Model');
			// $this->set_js_context('tweetComments', 'Camp_Wa_TweetComments_Model');
			// $this->set_js_context('waterfall', '/waterfall/waterfall');
			// $this->set_view("wa");
		}
		
		public function type($order, $keyword)
		{
			return $this->index($order, $keyword);
		}

		//TODO::准备header
		private function _prepare_header()
		{
			$this->positions['headers'] = '';
		}

	} // END class 
?>