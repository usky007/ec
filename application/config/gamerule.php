<?php defined('SYSPATH') OR die('No direct access allowed.');

$c2t['历史博物馆']='1002';
$c2t['商店']='1004';
$c2t['特色博物馆']='1002';
$c2t['活动： 夜生活']='4002';
$c2t['水疗中心']='1005';
$c2t['赛车场']='1006';
$c2t['科学博物馆']='1002';
$c2t['自然历史博物馆']='1002';
$c2t['博物馆']='1002';
$c2t['爵士乐酒吧']='4001';
$c2t['自行车道']='1005';
$c2t['艺术博物馆']='1002';
$c2t['文娱中心']='1006';
$c2t['高尔夫课程']='1005';
$c2t['活动： 购物']='1004';
$c2t['综合体育中心']='1005';
$c2t['健身俱乐部']='1005';
$c2t['运动场']='1005';
$c2t['运动营/训练']='1005';
$c2t['儿童博物馆']='1002';
$c2t['购物中心']='1004';
$c2t['滑雪/滑雪区']='1005';
$c2t['表演']='1006';
$c2t['健行步道']='1005';
$c2t['赛狗场']='1005';
$c2t['酒吧/俱乐部']='4001';
$c2t['美术馆/画廊']='1003';
$c2t['游戏/娱乐中心']='1006';
$c2t['音乐会']='1006';
$c2t['歌剧']='1006';
$c2t['学区']='1006';
$c2t['观光']='1001';
$c2t['娱乐']='1006';
$c2t['美食']='2001';
$c2t['购物']='1004';
$c2t['住宿']='3001';

$config['daodao']['category2type'] = $c2t;

$config['daodao']['category2price']['收费: 是'] = 100;
$config['daodao']['category2price']['收费: 否'] = 0;
$config['daodao']['ignor']=array('收费: 是','收费: 否','"类别/部门"');

$config['China_region']=array('北京','天津','上海','重庆','内蒙古','新疆','西藏',
		'宁夏','广西','香港','澳门','黑龙江','吉林','辽宁',
		'河北','山西','青海','山东','河南','江苏','安徽','浙江','福建','江西',
		'湖南','湖北','广东','海南','甘肃','陕西','四川','贵州','云南');
$config['Tranditional_region']=array('香港','澳门','台湾');

$config['search_location_per_page']	= 10;

$config['maptag2type']['餐饮服务'] = '2001';
$config['maptag2type']['风景名胜'] = '1001';
$config['maptag2type']['购物服务'] = '1004';
$config['maptag2type']['科教文化'] = '1002';
$config['maptag2type']['政府机构'] = '1001';
$config['maptag2type']['商务住宅'] = '1001';
$config['maptag2type']['公共设施'] = '1001';
$config['maptag2type']['交通设施'] = '1001';
$config['maptag2type']['体育休闲'] = '1005';
$config['maptag2type']['医疗保健'] = '1005';
$config['maptag2type']['生活服务'] = '1005';
$config['maptag2type']['住宿服务'] = '3001';


$config['maptag2type']['金融保险'] = '9001';
$config['maptag2type']['地名地址'] = '9001';
$config['maptag2type']['公司企业'] = '9001';
$config['maptag2type']['汽车销售'] = '9001';
$config['maptag2type']['汽车维修'] = '9001';
$config['maptag2type']['汽车服务'] = '9001';
$config['maptag2type']['摩托车服'] = '9001';
$config['maptag2type']['大众点评'] = '9001';

$config['dptag2type']['美食'] = '2001';
$config['dptag2type']['休闲娱乐'] = '1005';
$config['dptag2type']['购物'] = '1004';
$config['dptag2type']['丽人'] = '1005';
$config['dptag2type']['结婚'] = '1005';
$config['dptag2type']['亲子'] = '1005';
$config['dptag2type']['运动健身'] = '1005';
$config['dptag2type']['酒店'] = '3001';
$config['dptag2type']['爱车'] = '1005';
$config['dptag2type']['生活服务'] = '1005';

//cache只支持google地图
$config['staticmap']['driver'] = 'google';
$config['staticmap']['cache'] = 'false';

$config['providerhome']['providers'] = array('sina');
$config['providerhome']['sina']['link'] = 'http://weibo.com/';
$config['providerhome']['sina']['thumbnail'] = 'images/sina.jpg';

//dazhongdianping city map table
$config['dazhongdianping']['map'] = array(
			'本地城市名' => '大众点评城市名',
			'大理' => '大理州'
		);

//数据字典可以配置print mode
$config['print']['mode'] = 'img';

////特殊分享文字
$config['owner_share_text']['default']='我在@旅行者自制地图 上制作了一个【$guidename】，不仅有【$cityname】好吃好玩的地点介绍，还在地图上标出来了，比较适合自由行，可打印携带，有去过的朋友帮我出出主意吧！点击链接查看完整内容： $url';
$config['visitor_share_text']['default']='我在@旅行者自制地图 上看到这个【$guidename】不错，不仅有【$cityname】好吃好玩的地点介绍，还在地图上标出来了，比较适合自由行，可打印携带，近期要去的朋友可以看看。点击链接查看完整内容： $url';

$config['owner_share_text']['xini']='【赢免费澳洲10日采访机会和考拉公仔】我正在@旅行者自制地图 上参与由@旅行者杂志电视网站 @澳大利亚旅游局 强强联手的#澳大利亚旅行地图有奖#活动，制作了一个【$guidename】点击链接查看完整内容：$url';
$config['owner_share_text']['bulisiban']= 	$config['owner_share_text']['xini'];
$config['owner_share_text']['daerwen']= 	$config['owner_share_text']['xini'];
$config['owner_share_text']['huobate']= 	$config['owner_share_text']['xini'];
$config['owner_share_text']['moerben']= 	$config['owner_share_text']['xini'];
$config['owner_share_text']['posi']= 		$config['owner_share_text']['xini'];
$config['owner_share_text']['adelaide']= 	$config['owner_share_text']['xini'];

$config['owner_share_text']['changtandao'] = '【免费游长滩】我正在@旅行者自制地图 上参与由@旅行者杂志电视网站 @菲律宾旅游局上海办事处 举办的#寻找长滩旅行达人#活动，大奖是【1张长滩往返机票+3晚酒店+机场接送】~！我制作了一个【$guidename】点击链接查看完整内容：$url';