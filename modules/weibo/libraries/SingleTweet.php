<?php defined('SYSPATH') OR die('No direct access allowed.');

class SingleTweet
{
	const GET_URL = '/2/statuses/show.json';

	protected $credential = null;

	public function __construct($user = null)
	{
		if($user == null)
		{
			$cfg = config::ditem('activity.offical_account');
			$user = new User_Model();
			$user->where('uid', $cfg)->find();
			if(!$user->loaded)
			{
				throw new Kohana_Exception("找不到该用户", __CLASS__, __FUNCTION__);
			}
		}

		$cm = new Credential_Model();
		$c = $cm->find_user_credentials($user);

		if(!isset($c[0]))
		{
			throw new Kohana_Exception("该用户没有授权信息", __CLASS__, __FUNCTION__);
		}

		$this->credential = new Credential('sina', $c[0]->token, true);
	}

	public function get($id)
	{
		try
		{
			$aro = new AuthorizedRestObject($this->credential, self::GET_URL);
			$d = $aro->get(array('id' => $id));
			return $d;
		}
		catch(Exception $e)
		{
			if($e->getOriginalCode() == 20101)
			{
				return null;				
			}
			
			throw $e;
		}

	}
}