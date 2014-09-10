<?php
class weibobritain_Controller extends ServiceController {

	public function category()
	{
		$wb = new BritainCategory();
		$wb->category();
		$wb->removecategory();
	}

	public function output()
	{
		$uim = new Camp_Br_UserInfos_Model();
		$uis = $uim->find_all()->as_array();
		$op = array();
		foreach ($uis as $value) {
			$op[] = array('uid' => $value->uid,
						'name' => $value->name,
						'email' => $value->email,
						'weibo' => $value->weibo,
						'mobile' => $value->mobile
						);
		}
		$this->set_format('xslcsv');
		$this->set_output($op);
	}
}