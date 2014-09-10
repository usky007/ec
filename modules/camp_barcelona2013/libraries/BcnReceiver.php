<?php defined('SYSPATH') OR die('No direct access allowed.');
class BcnReceiver extends Receiver {
	protected $prfKey = 'bcn.keywords';
	protected $TweetModel = 'Camp_Ba_Tweet_Model';
	protected $TweetCommentsModel = 'Camp_Ba_TweetComments_Model';

	public function getPrekey()
	{
		return $this->prfKey;
	}

	public function getTweetModel()
	{
		return $this->TweetModel;
	}

	public function getTweetCommentsModel()
	{
		return $this->TweetCommentsModel;
	}

	public function setupCategory($tweet)
	{
		//do nothing
	}

	public function receive($value)
	{
		$rst = parent::receive($value);

		if($rst)
		{		
			$annotations = isset($value['annotations']) ? $value['annotations'] : '';

			//到达 离开 计划顺延 计划提前 不去了
			$act = $this->_actParser($value['text']);

//++++++ 测试代码
			// if(!isset($annotations[0]['ts']))
			// {
			// 	$annotations[0]['ts'] = strtotime($value['created_at']);
			// }

			// $annotations[0]['glid'] = 81708;
			// $annotations[0]['ts'] = 1379395800 + 3600;

			// var_dump($annotations);
			// $this->_submit(array('1st' => 'arrive', '2nd' => 'plan_later'), $annotations);
//++++++

			if(count($act) > 0 && $annotations != '')
			{
				if(!isset($annotations[0]['ts']))
				{
					$annotations[0]['ts'] = strtotime($value['created_at']);
				}
				$this->_submit($act, $annotations);
			}
		}
	}

	private function _actParser($text)
	{
		$act_arr = array();
		if(preg_match('/#到达#/', $text))
		{
			$act_arr['1st'] = 'arrive';
		}
		if(preg_match('/#离开#/', $text))
		{
			if(isset($act_arr['1st']))
			{
				return array();
			}
			$act_arr['1st'] = 'leave';
		}
		if(preg_match('/#计划顺延#/', $text))
		{
			$act_arr['2nd'] = 'plan_later';
		}
		if(preg_match('/#计划提前#/', $text))
		{
			if(isset($act_arr['2nd']))
			{
				return array();
			}
			$act_arr['2nd'] = 'plan_forward';
		}
		if(preg_match('/#不去了#/', $text))
		{
			if(isset($act_arr['1st']))
			{
				return array();
			}
			$act_arr['1st'] = 'abort';
		}

		if(!isset($act_arr['1st']))
		{
			return array();
		}
		return $act_arr;
	}	

	private function _submit($act_arr, $annotations)
	{
		$glid = isset($annotations[0]['glid']) ? $annotations[0]['glid'] : 0;
		if($glid == 0)
			return;

		$ts = isset($annotations[0]['ts']) ? $annotations[0]['ts'] : 0;
		if($ts == 0)
			return;

		$params['action'] = $act_arr['1st'];
		if(isset($act_arr['2nd']))
			$params['addition'] = $act_arr['2nd'];
		$params['glid'] = $glid;
		$params['time'] = $ts;

		$url = '/api/bcschedule';
		$username = 'uutuu@uutuu.com';
		$pwdmd5 = 'b4cbeafb156c7ca4f5c7763bf801a9eb';

		$oauth2 = new AuthMethod_OAuth2_Driver('yanzi');
	
		$credential = $oauth2->exchange(AuthMethod_OAuth2_Driver::GRANT_TYPE_PASSWORD, array(
					'username' => $username,
					'password' => $pwdmd5
				));

		$oauth = new AuthorizedRestObject($credential, $url);
		$rst = $oauth->update($params);

		log::info('response form yanzi: '.json_encode($rst));
		//var_dump($rst);
	}
}