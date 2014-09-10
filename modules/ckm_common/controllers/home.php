<?php defined('SYSPATH') OR die('No direct access allowed.');
/*
 * Created on 2013-12-24
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
abstract class Home_Controller extends Public_Controller {

	public function __construct()
	{
		parent::__construct();

		//$background = json_decode(config::ditem('homead.background',false,json_encode(array())) ,1);
		$background = config::ditem('homead.background',false,array());
		$background = is_string($background)?json_decode($background,1):$background;
		$layout = new AppLayout_View();
		$background['pic'] = isset($background['pic']) ? $background['pic']  :"";
		if(!empty($background['pic']))
		{
			$background = $background['pic'];
			$this->bodypic = 'style="background:no-repeat top center; background-image : url('. $background.')"';
		}
		else
		{
			$background = $layout->resource_path('images/bg_home.png');
			$this->bodypic = 'style="background:repeat-x top center; background-image : url('. $background.')"';
		}

		AppLayout_View::set_layout("layouts/home");
		$this->positions["headers"]["header"] = "home/header";
		$this->positions["footers"]["footer"] = "home/footer";
	}
}
?>
