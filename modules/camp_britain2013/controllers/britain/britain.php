<?php
/**
 * Class description.
 *
 * $Id: britain.php 55 2013-06-27 11:34:50Z cyl $
 *
 * @package    package_name
 * @author     UUTUU cuiyulei
 * @copyright  (c) 2008-2010 UUTUU
 */
class Britain_Controller extends LayoutController 
{
	const BRITAIN_URL = 'GreatBritain';
	
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
			 ->set_title("有奖活动_晒Great英国照片，免费游Great英国！")
			 ->add_js("js/central/britain.js")
			 ->add_css( "css/central/britain/sub_pubu.css" );
		$cat_mod = new Camp_Br_Category_Model();
		$cates = $cat_mod->find_all();
		$keywords = array( '全部' => 'all');
		foreach ($cates as $v){
			$keywords[$v->name] = $v->key;
		}
		$data['order'] = $order;
		$data['keyword'] = $keyword;
		$data['keywords']= $keywords;
		$data['britainUrl'] = self::BRITAIN_URL;
		$this->set_output($data);
		$this->set_js_context('order', $order);
		$this->set_js_context('keyword', $keyword);
		$this->set_js_context('userinfo', 'Camp_Br_UserInfos_Model');
		$this->set_js_context('tweet', 'Camp_Br_Tweet_Model');
		$this->set_js_context('tweetComments', 'Camp_Br_TweetComments_Model');
		$this->set_js_context('waterfall', '/waterfall/waterfall');
		$this->set_view("britain/index");
	}
	
	public function type($order, $keyword)
	{
		return $this->index($order, $keyword);
	}
}
?>