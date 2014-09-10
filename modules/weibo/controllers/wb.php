<?php defined('SYSPATH') OR die('No direct access allowed.');
class Wb_Controller extends Controller 
{
	public function get($lng = 180, $lat = 90, $range = 1000)
	{
		if($lng == 180 && $lat == 90)
		{
			echo '需要正确的坐标';
			return;
		}

		$nbu = new NearbyUsers();

		$params['long'] = $lng;
		$params['lat'] = $lat;
		$params['range'] = $range;

		$nbu->set_params($params);

		$now = time();
		$result = array();

		if(count($nbu->next_page(50)) > 0)
		{
			//var_dump($nbu);exit;
			foreach ($nbu as $item) {
				//var_dump($item);exit;
				$r = array('user' => $item['name'], 'weibo' => $item['status']['text'], 'profile_url' => $item['profile_url']);
			//	var_dump($r);exit;
				$wbtime = strtotime($item['last_at']);
				if(($now - $wbtime) <= 600)
				{
					$result['tenm'][] = $r;
				}
				else if(($now - $wbtime) <= 1800)
				{
					$result['half'][] = $r;
				}
				else if(($now - $wbtime) <= 3600)
				{
					$result['one'][] = $r;
				}
				else if(($now - $wbtime) <= 7200)
				{
					$result['two'][] = $r;
				}
				else
				{
					$result['others'][] = $r;

				}
			}
		}

		foreach ($result as $key => $value) {
			switch ($key) {
				case 'tenm':
					echo '10分钟内:<br/>';
					foreach ($value as $item) {
						echo $this->_arr2str($item);
					}
					echo '<br/><br/>';
					break;
				case 'half':
					echo '半小时内:<br/>';
					foreach ($value as $item) {
						echo $this->_arr2str($item);
					}
					echo '<br/><br/>';
					break;
				case 'one':
					echo '一小时内:<br/>';
					foreach ($value as $item) {
						echo $this->_arr2str($item);
					}
					echo '<br/><br/>';
					break;
				case 'two':
					echo '两小时内:<br/>';
					foreach ($value as $item) {
						echo $this->_arr2str($item);
					}
					echo '<br/><br/>';
					break;
				case 'others':
					echo '两小时以上:<br/>';
					foreach ($value as $item) {
						echo $this->_arr2str($item);
					}
					break;
			}
		}
	
	}

	private function _arr2str($arr)
	{
		return '<a href="http://weibo.com/'.$arr['profile_url'].'">@'.$arr['user'].'</a>&nbsp;';
	}

	public function show()
	{
		// $keywords = array('live' => array('#旅行者在西澳#'),
		// 				  'food' => array('#西澳美食达人秀#')
		// 				);
		$keywords = array('#缤纷菲律宾#');

		echo json_encode($keywords);

		echo '<br/>';

		$category = array('island' => array('长滩', '海豚湾', '宿雾', '薄荷', '苏比克湾', '巴拉望', '佬沃', '棉兰老岛', '八打雁'),
				  'sports' => array('潜水', '高尔夫', '冲浪', '探险', '户外', '骑行', '香蕉船', '拖曳伞'),
				  'foods' => array('海鲜', '烧烤', 'bbq', '刨冰', '鸡尾酒', '水果', '龙虾', '下午茶', '春卷', '烤乳猪', 'halo')
				);

		echo json_encode($category);
	}

	public function rebuildCategory($model = null)
	{
		if($model === null)
			$model = Input::instance()->query('model', '');

		if($model !== '' && class_exists($model))
		{
			$m = new $model();
			if(method_exists($m, 'reCate'))
			{
				$m->reCate();
			}
			else
			{
				echo $model.' 未定义 reCate 方法';
			}
		}
		else
		{
				echo $model.' 不存在';

		}
	}
}
?>