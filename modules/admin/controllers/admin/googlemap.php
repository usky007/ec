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
class Googlemap_Controller extends Admin_Controller
{


	public function index()
	{
		$content = "";
		if(config::item('googlemap.proxy.enable',false,false)==true)
			$content .= "支持代理访问google地图<br>";
		else
		{
			$content .= "不支持代理访问google地图" ;
			$this->set_output(array('content'=>$content));
			return;

		}
		$cache =  Cache::instance('googleMapSet');
		if($this->_user_google_proxy())
		{
			$content .=  "使用代理中<br>" ;
		}
		else
		{

			$content .=  "未使用代理<br>" ;

			$errtimes = $cache->get('errortimes');
			$errtimes = empty($errtimes)?0:$errtimes;
			$content .=  "已经连续连接失败 $errtimes 次";

		}

		$this->set_output(array('content'=>$content));
	}

	private function _user_google_proxy()
	{
		if(config::item('googlemap.proxy.enable',false,false)!=true)
			return false;

		$cache =  Cache::instance('googleMapSet');
		$switch = $cache->get('switch');
		if(isset($switch))
		{
			return $switch;
		}
		else
		{
			$default = config::item('googlemap.proxy.default',false,false);
			$cache->set('switch',$default);
			return $default;
		}
	}



} // End Index_Controller