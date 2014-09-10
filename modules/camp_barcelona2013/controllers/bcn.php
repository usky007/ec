<?php
	/**
	 * barcelona Controller
	 *
	 * @package act_barcelona2013
	 * @author cuiyulei
	 **/
	class Bcn_Controller extends LayoutController
	{
		const WA_URL = 'GreatBritain';
	
		public function __construct()
		{
			parent::__construct();
			$this->add_dialog("overlay");
			$this->positions["headers"] = '';
			$this->positions["footers"] = '';
		}

		public function __call($method, $arguments=null)
		{
			if (empty($arguments)) 
			{
				return $this->index($method);
			}
			else 
			{
				$arg1 = $arguments[0];
				if (method_exists($this,$arg1)) {
					$arguments[0] = $method;
				}
				else {
					$arg1 = 'type';
					array_unshift($arguments, $method);
				}
				return call_user_func_array(array($this, $arg1), $arguments);
			}
		}
		
		public function index($order='new', $keyword = '')
		{
			$this->get_layout()
				 ->set_title("跟旅行者一起玩转巴塞罗那")
				 ->add_js("js/central/britain.js")
				 ->add_css( "css/central/bcn/sub_pubu.css" );
			$this->_prepare_header();
			$cat_mod = new Camp_Br_Category_Model();
			$cates = $cat_mod->find_all();
			$keywords = array( '全部' => 'all');
			foreach ($cates as $v){
				$keywords[$v->name] = $v->key;
			}
			$data['order'] = $order;
			$data['keyword'] = $keyword;
			$data['keywords']= $keywords;
			$data['britainUrl'] = self::WA_URL;
			$this->set_output($data);
			$this->set_js_context('order', $order);
			$this->set_js_context('keyword', $keyword);
			// $this->set_js_context('userinfo', 'Camp_Ba_UserInfos_Model');
			$this->set_js_context('tweet', 'Camp_Ba_Tweet_Model');
			$this->set_js_context('tweetComments', 'Camp_Ba_TweetComments_Model');
			$this->set_js_context('waterfall', '/waterfall/waterfall');
			$this->set_view("ba");
		}
		
		public function type($order, $keyword)
		{
			return $this->index($order, $keyword);
		}

		//TODO::准备header
		private function _prepare_header()
		{
			$this->positions['header'] = '';
		}

		//TODO::准备dialog
		private function _prepare_dialog()
		{
			$this->positions['dialogs'] = '';
		}

	} // END class 
?>