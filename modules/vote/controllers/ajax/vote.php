<?php
class Vote_Controller extends ServiceController
{
	public function index()
	{
		
	}
	
	// public function getlimit()
	// {
	// 	$data = input::instance()->post('data',null);
	// 	$data = $this->_get_array($data);
	// 	if(!$this->_getlimit($data))
	// 		throw new UKohana_Exception('E_VOTE_QUANTITY_ERROR',"errors.quantity_error");
	// }
	public function setvote()
	{
		$data = input::instance()->post('data',null);
		$votekey = input::instance()->post('votekey',null);
		$sharemsg = input::instance()->post('sharemsg', '');

		if($data == null)
		{
			throw new U_Exception("没收到任何数据");
		}
		if($votekey == null)
		{
			throw new U_Exception("缺少votekey");
		}

		$data = (array)json_decode($data);
		$rst = $this->_dataCheck($votekey, $data);
		if($rst !== true)
		{
			$this->set_output(array(
					'errorCode' => 'error',
					'msg' => $rst,
				));
			return;
		}

		$userkey = user::get_information();

		$optcount = 0;
		foreach ($data as $key => $value) {
			$optcount += count($value);
		}

		$votetmp = new Voterecordtmp_Model();
		$vts = $votetmp->where(array('user' => $userkey, 'votekey' => $votekey))->find_all();

		$optcount += count($votetmp);

		$vmt = new Voterecord_Model();
		ID_Factory::prepare_ids($vmt,$optcount);

		foreach ($vts as $tmp) {
			$vote = new Voterecord_Model();
			$vote->id = ID_Factory::next_id($vote);
			$vote->user = $userkey;
			$vote->votekey = $tmp->votekey;
			$vote->col = $tmp->col;
			$vote->option = $tmp->option;
			$vote->text = $tmp->text;
			$vote->save();
			$tmp->delete();
		}

		foreach ($data as $col => $options) {
			foreach ($options as $option) {
				$vote = new Voterecord_Model();
				$vote->id = ID_Factory::next_id($vote);
				$vote->user = $userkey;
				$vote->votekey = $votekey;
				$vote->col = $col;
				$vote->option = $option->option;
				if(isset($option->others))
				{
					$vote->text = $option->others;
				}
				$vote->save();
			}
		}

		$rst = array(
					'errorCode' => 'success',
					'msg' => 'done',
				);

		if($sharemsg != '')
		{
			$rst['sharemsg'] = urlencode(trim($sharemsg));
		}

		$this->set_output($rst);
	}
	public function settmp()
	{
		$data = input::instance()->post('data',null);
		$votekey = input::instance()->post('votekey',null);
		
		if($data == null)
		{
			throw new U_Exception("没收到任何数据");
		}
		if($votekey == null)
		{
			throw new U_Exception("缺少votekey");
		}

		$data = (array)json_decode($data);
		$rst = $this->_dataCheck($votekey, $data);
		if($rst !== true)
		{
			$this->set_output(array(
					'errorCode' => 'error',
					'msg' => $rst,
				));
			return;
		}

		$userkey = user::get_information();

		$cols = array();
		foreach ($data as $key => $value) {
			$cols[] = "'".$key."'";
		}
		$votetmp = new Voterecordtmp_Model();
		$votetmp->where(array('user' => $userkey, 'votekey' => $votekey))->in('col', implode(',', $cols))->delete_all();

		foreach ($data as $col => $options) {
			foreach ($options as $option) {
				$vote = new Voterecordtmp_Model();
				$vote->id = ID_Factory::next_id($vote);
				$vote->user = $userkey;
				$vote->votekey = $votekey;
				$vote->col = $col;
				$vote->option = $option->option;
				if(isset($option->others))
				{
					$vote->text = $option->others;
				}
				$vote->save();
			}
		}

		$this->set_output(array(
					'errorCode' => 'success',
					'msg' => 'done',
				));
	}

	public function get_setting()
	{
		$votekey = input::instance()->query('votekey', null);
		$cols = input::instance()->query('columns', '');
		$cols = explode(',', $cols);
		$settings = Vote_Model::sgetSetting($votekey, $cols);	
		$userkey = user::get_information();
		$selected = Voterecordtmp_Model::getSelected($votekey, $userkey, $cols);	
		$this->set_output(array("json" => $settings, 'selected' => $selected));
	}

	private function _dataCheck($votekey, $data) {
		$rst =  true;
		$cols = array();
		foreach ($data as $key => $value) {
			$cols[] = $key;
		}
		$settings = Vote_Model::sgetSetting($votekey, $cols);		
		
		$now = time();
		if($now < $settings[Vote_Model::SETTING_TIME][Vote_Model::SETTING_TIME_START])
		{
			return array('type' => 'time', 'msg' => '投票尚未开始');
		}
		else if($settings[Vote_Model::SETTING_TIME][Vote_Model::SETTING_TIME_END] != 0){
				if($now > $settings[Vote_Model::SETTING_TIME][Vote_Model::SETTING_TIME_END])
				{
					return array('type' => 'time', 'msg' => '投票已经结束');
				}
			}

		$errcols = array();
		foreach ($data as $col => $options) {
			$actual = count($options);
			if(isset($settings[Vote_Model::SETTING_COL_SETTING][$col])){
				$col_setting = $settings[Vote_Model::SETTING_COL_SETTING][$col];
				if($col_setting->max == 0 && $col_setting->min == 0){
					continue;
				}
				else if($actual < $col_setting->min)
				{
					$errcols[$col] = '数量不能低于 '.$col_setting->min.' 个';
				}
				else if($actual > $col_setting->max && $col_setting->max != 0)
				{
					$errcols[$col] = '数量不能多于 '.$col_setting->max.' 个';				
				}
			}
		}

		if(count($errcols) > 0)
		{
			return array('type' => 'col', 'msg' => $errcols);
		}
		else
			return true;
	}
}