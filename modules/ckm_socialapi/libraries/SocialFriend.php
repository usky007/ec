<?php
/**
 * Class description.
 *
 * $Id: SocialAccount.php 2658 2011-06-23 06:53:18Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU mask
 * @copyright  (c) 2008-2010 UUTUU
 */
class SocialFriend extends AuthorizedObject {

	public function __construct(Credential $cred, $gateway = NULL)
	{
		parent::__construct($cred, $gateway);
	}

	public function get($parameters = array()) {
		$url = '/2/friendships/friends/bilateral.json';
		return $this->http_get($url,$parameters);
	}

	public function get_city_friend($citycode,$limit=3,$cachesize=10)
	{

		$city = new City_Model();
		$city->where('citycode',$citycode)->find();

		$cachepool = $this->_get_cache_friend($citycode);
		$cachepoolValid = $this->_checkCacheValid($citycode);

//		$cache = Cache::instance("Friend");
//		$provider = $this->credential->provider;
//		$uid =  $this->credential->uid;
//		$cacheKey = $provider.'_'.$citycode.'_'.$uid;
//		$cachepool = $cache->get($cacheKey);


		$return_users = array();

		if(!$cachepoolValid)
		{
			//renew cache

			$rst = array();
			try{
				$this->_cache_city_friend($rst,$city,10);
			}
			catch(Exception $ex)
			{

				return array();
			}
			$cachepool = $rst;
		}

		if(count($cachepool)<$limit)
		{

			$return_users = $cachepool;
			$needcount =  $limit-count($cachepool);
			$allusers = $this->get_all_friend($needcount,$cachepool);

			foreach($allusers as $user)
			{
				$return_users[] = $user;
			}
			return $return_users;

		}
		elseif(count($cachepool) == $limit)
		{
			return $cachepool;
		}

		$idxs = array();

		$i=0;
		while(count($return_users)<$limit)
		{
			$idx = $this->getRand($cachepool);

			if(!in_array($idx ,$idxs))
			{
				$idxs[] = $idx;
				$return_users[] = $cachepool[$idx];
			}

			if($i>50)
				return $return_users;
			$i += 1;
		}
		return $return_users;
	}

	private function _cache_city_friend(&$result,$city,$limit,$page =1)
	{
		 $cityname = $city->cityname;
		 $friends = $this->get(array('uid'=>$this->credential->identity,'page'=>$page));


		if($friends['total_number']==0 || $page>9)
		{
			$this->set_cache_city_friend($result,$city);
			return true;
		}
		foreach($friends['users'] as $friend)
		{
			if(count($result) >= $limit)
				continue;
			$usercity = $this->_get_cityname($friend['location']);

			if($usercity != $cityname || empty($usercity))
				continue;

			$result[] = $friend;
		}

		if(count($result)<$limit)
		{
			return $this->_cache_city_friend($result,$city,$limit,$page+1);
		}
		else
		{
			$this->set_cache_city_friend($result,$city);
			return true;
		}
	}
	function set_cache_city_friend(&$result,$city)
	{

		$cache = Cache::instance("Friend");
		$provider = $this->credential->provider;
		$cacheKey = $provider.'_'.$city->citycode.'_'.$this->credential->uid;

		uasort($result, array($this,"cb_sortfriend"));
		$level = 1;
		for($i=0;$i<count($result);$i++)
		{
			$result[$i]['level'] = (1+$level)*$level/2;
			$level = $level+1;
		}
		$cache->set($cacheKey.'_ts',time());
		$cache->set($cacheKey,$result);
	}
	private function _get_cache_friend($citycode='all')
	{
		$cache = Cache::instance("Friend");
		$provider = $this->credential->provider;
		$uid =  $this->credential->uid;
		$cacheKey = $provider.'_'.$citycode.'_'.$uid;
		$rst = is_null($cache->get($cacheKey))?array():$cache->get($cacheKey);
		return $rst;

	}
	public function get_all_friend($limit,&$return_users=array())
	{
		$cachepool = $this->_get_cache_friend();
		$cachepoolValid = $this->_checkCacheValid();
		if(!$cachepoolValid)
		{
			//renew cache

			$rst = array();
			try{
				$this->_cache_all_friend($rst);

			}
			catch(Exception $ex)
			{
				return false;
			}
			$cachepool = $rst;
		}




//		$return_users = array();
//		if(count($cachepool)<=$limit)
//		{
//			$return_users = $cachepool;
//
//		}
//		else
//		{


		$idxs = array();

		if(!is_null($return_users))
		{
			foreach($return_users as $usr)
			{
				$idxs[] = $usr['id'];
			}

		}


		$i=0;

		$result = array();

		while(count($result)<$limit)
		{
			$idx = $this->getRand($cachepool);
			if($idx<0)
			{
				return $result;
			}
			$uid = $cachepool[$idx]['id'];

			if(!in_array($uid ,$idxs))
			{

				$idxs[] = $uid;
				$result[] = $cachepool[$idx];
			}

			if($i>20)
			{
				return $result;

			}

			$i += 1;
		}
		//}

		return $result;
	}

	private function _cache_all_friend(&$result,$page =1)
	{

		$friends = $this->get(array('uid'=>$this->credential->identity,'page'=>$page));

		if($friends['total_number']==0)
		{
			$this->set_cache_all_friend($result);
			return true;
		}

		foreach($friends['users'] as $friend)
		{

			$result[] = $friend;
		}

		$this->set_cache_all_friend($result);
		return true;

	}

	private function set_cache_all_friend(&$result)
	{

		$cache = Cache::instance("Friend");
		$provider = $this->credential->provider;
		$cacheKey = $provider.'_all_'.$this->credential->uid;

		uasort($result, array($this,"cb_sortfriend"));
		$level = 1;
		for($i=0;$i<count($result);$i++)
		{
			$result[$i]['level'] = (1+$level)*$level/2;
			$level = $level+1;
		}
		$cache->set($cacheKey.'_ts',time());
		$cache->set($cacheKey,$result);
	}

	private function _checkCacheValid($citycode='all')
	{
		$cache = Cache::instance("Friend");
		$provider = $this->credential->provider;
		$cacheKey = $provider.'_'.$citycode.'_'.$this->credential->uid;
		$lasttime = $cache->get($cacheKey.'_ts');
		return time()-$lasttime<= 86400 ;//day
	}




	private function _get_cityname($strLocation)
	{
		$aryLocs = explode(" ", $strLocation);

		if(count($aryLocs)==1)
			return $aryLocs[0];
		if(count($aryLocs)==0)
			return "";
		if($aryLocs[0]=="海外")
			return $aryLocs[1];
		else
			return $aryLocs[0];
	}

	private function cb_sortfriend($a,$b)
	{
		$astrtime = isset($a['status']['created_at'])?$a['status']['created_at']:$a['created_at'];
		$bstrtime = isset($b['status']['created_at'])?$b['status']['created_at']:$b['created_at'];

		$a_dt = date_parse( $astrtime); //'D M d H:i:s O Y',
		$b_dt = date_parse($bstrtime);
		$a_dt = mktime($a_dt['hour'],$a_dt['minute'],$a_dt['second'],$a_dt['month'],$a_dt['day'],$a_dt['year'])+($a_dt['zone']*60);
		$b_dt = mktime($b_dt['hour'],$b_dt['minute'],$b_dt['second'],$b_dt['month'],$b_dt['day'],$b_dt['year'])+($b_dt['zone']*60);
		//$b_dt = $b_dt->getTimestamp();

		if ($a_dt == $b_dt) {
			return 0;
		}

		return ($a_dt < $b_dt) ? -1 : 1;
	}
	private function getRand($array)
	{
		$max = count($array)+1;
		$max =  (1+$max)*$max/2;
		$randval = mt_rand(1,$max);

		for($i=count($array)-1;$i>=0;$i--)
		{

			 if($array[$i]['level']<=$randval)
			 	return $i;
		}
		return -1;
	}
}
?>