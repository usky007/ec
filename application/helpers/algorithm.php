<?php
class algorithm
{
	const TOKEN_SEQUENCE_KEY = "mailtoken";
	const INVIATION_TOKEN_SEQUENCE_KEY = "invitetoken";

	private static $map = array (
	    'b','f','3','d','a','1','w','F','k','6','j','5','H','x','y','2',
	    'W','X','7','g','P','B','T','_','Y','c','A','J','l','9','R','G',
	    'v','n','z','-','h','Q','p','E','I','s','o','L','M','4','U','r',
	    'C','O','m','t','i','0','K','D','e','S','V','q','N','8','u','Z');



	public static function intersection($sort,$limit,$cmpfield,$cmpfield_1)
	{
		$intSort = $sort=="ASC"?1:-1;
		log::debug("cmpsort $intSort");
		log::debug("cmp0 $cmpfield");
		log::debug("cmp1 $cmpfield_1");

		$arys = array_slice(func_get_args(), 4);
		$arrays = array();
		$idxes = array();
		$total = 0;
		$result = array();
		foreach ($arys as $a)
		{
			if(!empty($a))
			{
				$arrays[] = $a;
			}
		}
		for ($i = 0; $i < count($arrays); $i++) {
			$idxes[] = 0;//N个为0的数组成员
			$total += count($arrays[$i]);//得到所有数组值的总数；
		}
		if(count($idxes)==0)
			return $result;
		while ($idxes[0] < count($arrays[0]) && count($result) < $limit)
		{
//			print_r($idxes);
//			echo "<br/>";
			$minVal = $arrays[0][$idxes[0]];
			$eqCnt = 1;

			for ($j = 1; $j < count($arrays); $j++) {//3

				//log::debug($arrays[$j][$idxes[$j]]."#");
				if ($idxes[$j] >= count($arrays[$j])) {
					return $result;
				}

				//if ($arrays[$j][$idxes[$j]] < $minVal) {
				if(self::cmp($arrays[$j][$idxes[$j]],$minVal,$cmpfield,$cmpfield_1)*$intSort<0)
				{
					$idxes[$j]++;
				}
				else if(self::cmp($arrays[$j][$idxes[$j]],$minVal,$cmpfield,$cmpfield_1)*$intSort>0)
				{
				//else if ($arrays[$j][$idxes[$j]] > $minVal) {
					$idxes[0]++;
					break;
				}
				else {
					$eqCnt++;
				}
			}

			if($eqCnt == count($arrays))
			{
				$result[] = $minVal;
				for ($j = 0; $j < count($arrays); $j++) {
					$idxes[$j]++;
				}
			}
		}

		return $result;
	}

	public static function union($order)
	{

		$pram_sample = array_slice(func_get_args(),1);
		$sample = array();
		foreach($pram_sample as $smp)
		{
			if(count($smp)>0)
			$sample[] = $smp;
		}




//		foreach($sample as $list)
//		{
//			foreach($list as $row)
//				echo $row->locName."[".$row->lid."]" ."|";
//			echo "<br>";
//		}


 		$sampleCount = count($sample);
		$idx = array();
		for ($i = 0; $i < $sampleCount; $i++) {
			$idx[] = 0;//N个为0的数组成员
		}


		$heap = new Heap();
		$heap->setLength($sampleCount);
		$heap->compare_driver = 'algorithm';
		switch ($order)
		{
			case Location::ORDER_RATE_ASC :
			  $heap->compare_function = 'cmp_union_rate_asc';
			  break;
			case Location::ORDER_RATE_DESC :
			  $heap->compare_function = 'cmp_union_rate_desc';
			  break;
			case Location::ORDER_NAME_ASC:
			  $heap->compare_function = 'cmp_union_name_asc';
			  break;
			case Location::ORDER_NAME_DESC:
			  $heap->compare_function = 'cmp_union_name_desc';
			  break;

		}


//		$test = "";
//		foreach($sample[0] as $row)
//		{
//			$test .= $row->lid.",";
//		}
//		log::debug("sample1:".$test);
//		$test = "";
//		foreach($sample[1] as $row)
//		{
//			$test .= $row->lid.",";
//		}
//		log::debug("sample2:".$test);

		$minitem = null;
		for($i=0;$i<$sampleCount;$i++)
		{
			$hpitem = self::buildHeapItem($sample[$i][0],$i);

			$heap->swapMin($hpitem);

//			$aa = $sample[$i][0];
//			log::debug("in:".$aa->lid);
//
//			$heap->chk();


			$minitem = is_null($minitem)? $hpitem :self::getMin($hpitem,$minitem,$heap->compare_function);
		}

		$mingroup = $minitem['group'];

		$lastout = null;
		$topheap = $heap->getTop();
		$group =$topheap['group'];

		$rst = array();
		while($sampleCount>0)
		{

			if($idx[$group]<0)
			{

				$topheap = $heap->getTop();
				$group =$topheap['group'];
			}

			$idx[$group] ++;
			//log::debug("sample curt:$group idx".$idx[$group]);
			if($idx[$group] >= count($sample[$group]))
			{
				//某个sample 用完
				//log::debug("sample $group 用完");
				$sampleCount -- ;
				$out = $heap->outTop();
				$heap->setLength($sampleCount);
	 			$idx[$group] = -1;
			}
			else
			{
				$hpitem = self::buildHeapItem($sample[$group][$idx[$group]],$group);
				//log::debug("in:".$hpitem['value']->lid);
				$out = $heap->swapMin($hpitem);
			}
			$group =$out['group'];

			if(count($rst)>0)
			{
				if(self::cmp( $rst[count($rst)-1] ,$out['value'],'locName','id') != 0)
					$rst[] =$out['value'];
			}
			else
			{
				$rst[] =$out['value'];
			}
			//$heap->chk();
			//log::debug("out:".$out['value']->lid);
			//var_dump($out['value']->id);
		}
		//var_dump(count($rst));
		return array($rst);

	}

	public function buildHeapItem($item,$group)
	{
		return array('value'=>$item,'group'=>$group);

	}

	public function getMin($obj1,$obj2,$cmpfun)
	{
		if(self::$cmpfun($obj1,$obj2)>0)
			return $obj2;
		else
			return $obj1;
	}

	function cmp_union_name_asc($obj1,$obj2)
	{

		$obj1 = isset($obj1['value'])?$obj1['value']:$obj1;
		$obj2 = isset($obj2['value'])?$obj2['value']:$obj2;
		return self::cmp($obj1,$obj2,'locName','id');
	}

	function cmp_union_name_desc($obj1,$obj2)
	{

		$obj1 = isset($obj1['value'])?$obj1['value']:$obj1;
		$obj2 = isset($obj2['value'])?$obj2['value']:$obj2;
		return (-1)*self::cmp($obj1,$obj2,'locName','id');
	}

	function cmp_union_rate_desc($obj1,$obj2)
	{

		$obj1 = isset($obj1['value'])?$obj1['value']:$obj1;
		$obj2 = isset($obj2['value'])?$obj2['value']:$obj2;
		return (-1)*self::cmp($obj1,$obj2,'rating','id');
	}

	function cmp_union_rate_asc($obj1,$obj2)
	{

		$obj1 = isset($obj1['value'])?$obj1['value']:$obj1;
		$obj2 = isset($obj2['value'])?$obj2['value']:$obj2;
		return self::cmp($obj1,$obj2,'rating','id');
	}



	function cmp_old($obj1,$obj2,$cmpfield,$cmpfield_1)
	{
		$rst = 0;
		if($obj1->$cmpfield > $obj2->$cmpfield)
			$rst = 1;

		if($obj1->$cmpfield < $obj2->$cmpfield)
			$rst = -1;

		if($obj1->$cmpfield == $obj2->$cmpfield)
		{
			 if($obj1->$cmpfield_1 > $obj2->$cmpfield_1)
			 	$rst = 1;
			 if ($obj1->$cmpfield_1 == $obj2->$cmpfield_1)
			 	$rst = 0;
			 if ($obj1->$cmpfield_1 < $obj2->$cmpfield_1)
			 	$rst = -1;
		}
		log::debug("[cmp]".$obj1->lid."<>".$obj2->lid."|".$obj1->$cmpfield."<>".$obj2->$cmpfield);
		return $rst;

	}

	function cmp($obj1,$obj2,$cmpfield,$cmpfield_1)
	{
		$rst = 0;
		$obj1Value = strtoupper($obj1->$cmpfield);
		$obj2Value = strtoupper($obj2->$cmpfield);
		$obj1Value1 = strtoupper($obj1->$cmpfield_1);
		$obj2Value1 = strtoupper($obj2->$cmpfield_1);


		if($obj1Value > $obj2Value)
			$rst = 1;

		if($obj1Value < $obj2Value)
			$rst = -1;

		if($obj1Value == $obj2Value)
		{
			 if($obj1Value1 > $obj2Value1)
			 	$rst = 1;
			 if ($obj1Value1 == $obj2Value1)
			 	$rst = 0;
			 if ($obj1Value1 < $obj2Value1)
			 	$rst = -1;
		}
		//log::debug("[cmp]".$obj1->lid."<>".$obj2->lid."|".$obj1Value."<>".$obj2Value);
		return $rst;

	}
//	function f($limit, $a, $b)
//	{
//		$arrays = array_slice(func_get_args(), 1);
//		$idxes = array();
//		$total = 0;
//		for ($i = 0; $i < count($arrays); $i++) {
//			$idxes[] = 0;
//			$total += count($arrays[$i]);
//		}
//		$result = array();
//
//
//		for ($i = 0; count($result) < $limit && $i < $total; $i++) {
//			$minArray = -1;
//			$minVal = 99999999; //confirm?
//			for ($j = 0; $j < count($arrays); $j++) {
//				if ($idxes[$j] >= count($arrays[$j])) {
//					continue;
//				}
//				if ($arrays[$j][$idxes[$j]] < $minVal) {
//					$minArray = $j;
//					$minVal = $arrays[$j][$idxes[$j]];
//				}
//			}
//			$idxes[$minArray]++;
//			if ($minVal != $result[count($result) - 1]) {
//				$result[] = $minVal;
//			}
//		}
//		return $result;
//	}

	public static function date($unix)
	{
		$day = 86400;
		$hour = 3600;
		$minute = 60;
		if($unix >= $day)
		{
			return floor($unix/$day).'天';
		}
		elseif($unix >= $hour)
		{
			return floor($unix/$hour).'小时';
		}
		elseif($unix >= $minute)
		{
			return floor($unix/$minute).'分钟';
		}else
		{
			return $unix.'秒';
		}
	}

	public static function rating($rating)
	{
		$rating = round($rating/10*2);
		return $rating/2*10;
	}




	public static function generate_short()
	{
		// generate sequence id (64bit) only support 32bit
		$id = sprintf("%016s", ID_Factory::next_id(self::INVIATION_TOKEN_SEQUENCE_KEY));

		while (true) {
			// generate app bundled unique string (128bit)
			$token = substr(md5(uniqid(rand(), true)), 0, 4);

			// from last bit, mangle 4bit of token with 2bit of id. Token created
			// by this process keeps unique as well as ensures security.
			$code = $id & 0x1F;
			$id = $id >> 5;
			for ($i = strlen($token) - 1; $i >= 0 && $code > 0; $i--) {
				if ($code > 0) {
					$token[$i] = self::$map[$code << 1];
				} else {
					$token[$i] = self::$map[(intval($token[$i], 16) << 1) + 1];
				}
				$code = $id & 0x1F;
				$id = $id >> 5;
			}
			while ($code > 0) {
				$token = self::$map[$code << 1] . $token;
				$code = $id & 0x1F;
				$id = $id >> 5;
			}

			// ensure no numeric token returns;
			if (!is_numeric($token)) {
				break;
			}
		}
		return $token;
	}

	public static function digital_cn($int)
	{
		$int = (int)$int;
		switch ($int) {
			case 1:
			return '一';
			break;
			case 2:
			return '两';
			break;
			case 3:
			return '三';
			break;
			case 4:
			return '四';
			break;
			case 5:
			return '五';
			break;
			case 6:
			return '六';
			break;
			case 7:
			return '七';
			break;
			case 8:
			return '八';
			break;
			case 9:
			return '九';
			break;
			case 10:
			return '十';
			break;

			default:
			return $int;
			break;
		}
	}
	public static function cut($str,$long)
	{
		if(mb_strlen($str,'utf-8') > $long)
		{
			return mb_substr($str, 0, $long, 'utf-8').'...';
		}
		else
		{
			return mb_substr($str, 0, $long, 'utf-8');
		}
	}
//	public static function get_token(){
//		$encrypt_key = md5(((float) date("YmdHis") + rand(10000000000000000,99999999999999999)).rand(100000,999999));
//		$ctr=0;
//		$tmp = "";
//		for ($i=0;$i<10;$i++)
//		{
//			if ($ctr==strlen($encrypt_key)) $ctr=0;
//			$tmp.= substr($encrypt_key,$ctr,1) . (substr(10,$i,1) ^ substr($encrypt_key,$ctr,1));
//			$ctr++;
//		}
//		return $tmp;
//	}
	public static function generate_token()
	{
		// generate sequence id (64bit)
		$id = sprintf("%016s", ID_Factory::next_id(self::TOKEN_SEQUENCE_KEY));

		// generate app bundled unique string (128bit)
		$token = md5(uniqid(rand(), true));

		// from last bit, mangle 4bit of token with 2bit of id. Token created
		// by this process keeps unique as well as ensures security.
		$code = $id & 0x03;
		$id = $id >> 2;
		for ($i = strlen($token) - 1; $i >= 0 && $code > 0; $i--) {
			$token[$i] = self::$map[(intval($token[$i], 16) << 2) + $code];
			$code = $id & 0x03;
			$id = $id >> 2;
		}
		return $token;
	}
}