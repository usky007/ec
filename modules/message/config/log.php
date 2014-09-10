<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Preference
 *
 * Preference settings, defined as arrays, or "groups". If no group name is
 * used when loading the cache library, the group named "default" will be used.
 *
 * Each group can be used independently, and multiple groups can be used at once.
 *
 * Group Options:
 *  driver   - Cache backend driver. Kohana comes with file, database, and memcache drivers.
 *              > File cache is fast and reliable, but requires many filesystem lookups.
 *              > Database cache can be used to cache items remotely, but is slower.
 *              > Memcache is very high performance, but prevents cache tags from being used.
 */
$config['uc_action'] = array
(
	'CREATE_BLOG'				=> 'SNSBLOG_CREATE',
	'SHARE_SHARE_BLOG'	=> 'SNS_SHARE',
	'FEEL_FEEL_BLOG'		=> 'SNS_OTHER_FEEL',
	'COMMENT_CRITIQUE_BLOG'			=> 'SNS_OTHER_CRITIQUE',
	'COMMENT_DISCUSSION_BLOG'	=> 'SNS_DISCUSSION',
	'COMMENT_QUESTION_BLOG'			=> 'SNS_QUESTION',

	'CREATE_ALBUM'			=> 'SNS_OTHER',
	'SHARE_SHARE_ALBUM'	=> 'SNS_SHARE',

	'CREATE_PIC'		=> 'SNSPIC_UPLOAD',
	'CREATE_PIC_EVENT'	=> 'SNSPIC_UPLOAD',
	'CREATE_PIC_ALBUM'	=> 'SNSPIC_UPLOAD',
	'CREATE_PIC_MTAG'	=> 'SNSPIC_UPLOAD',
	'CREATE_PIC_CITY'	=> 'SNSPIC_UPLOAD',

	'SHARE_SHARE_PIC'	=> 'SNS_SHARE',
	'FEEL_FEEL_PIC'		=> 'SNS_OTHER_FEEL',
	'COMMENT_CRITIQUE_PIC'			=> 'SNS_OTHER_CRITIQUE',
	'COMMENT_DISCUSSION_PIC'	=> 'SNS_DISCUSSION',
	'COMMENT_QUESTION_PIC'			=> 'SNS_QUESTION',

	'CREATE_DOING'			=> 'SNS_OTHER',
	'SHARE_SHARE_DOING'	=> 'SNS_SHARE',

	'CREATE_POLL'				=> 'SNSPOLL_CREATE',
	'JOIN_JOIN_POLL'		=> 'SNSPOLL_JOIN',
	'SHARE_SHARE_POLL'	=> 'SNS_SHARE',
	'COMMENT_CRITIQUE_POLL'			=> 'SNS_OTHER_CRITIQUE',
	'COMMENT_DISCUSSION_POLL'	=> 'SNS_DISCUSSION',
	'COMMENT_QUESTION_POLL'			=> 'SNS_QUESTION',

	'CREATE_EVENT'			=> 'SNSEVENT_CREATE',
	'JOIN_JOIN_EVENT'		=> 'SNSEVENT_JOIN',

	'SHAREALBUM_ALBUM_EVENT'	=> 'SNSALBUM_SHARE',
	'FOCUS_FOCUS_EVENT'	=> 'SNS_OTHER',
	'CREATE_POLL_EVENT'	=> 'SNSPOLL_CREATE',
	'SHARE_SHARE_EVENT'	=> 'SNS_SHARE',
	'COMMENT_CRITIQUE_EVENT'			=> 'SNS_OTHER_CRITIQUE',
	'COMMENT_DISCUSSION_EVENT'	=> 'SNS_DISCUSSION',
	'COMMENT_QUESTION_EVENT'			=> 'SNS_QUESTION',

	'CREATE_MTAG'				=> 'SNS_OTHER',
	'CREATE_THREAD_MTAG'=> 'SNS_OTHER',
	'SHARE_SHARE_MTAG'	=> 'SNS_SHARE',
	'TOP_TOP_MTAG'			=> 'SNS_OTHER',
	'ELITE_ELITE_MTAG'	=> 'SNS_OTHER',
	'JOIN_JOIN_MTAG'		=> 'SNS_OTHER',
	'CREATE_EVENT_MTAG'	=> 'SNSEVENT_CREATE',
	'CREATE_POLL_MTAG'	=> 'SNSPOLL_CREATE',
	'UPLOAD_PIC_MTAG'		=> 'SNSPIC_UPLOAD',
	'SHAREALBUM_ALBUM_MTAG'	=> 'SNSALBUM_SHARE',

	'REGISTER_REGISTER'			=> 'SNS_REGISTER',
	'FRIEND_FRIEND_FRIEND'	=> 'SNS_FRIEND',



	'FEEL_FEEL_SPACE'				=> 'SNS_OTHER_FEEL',
	'COMMENT_CRITIQUE_SPACE'			=> 'SNS_OTHER_CRITIQUE',
	'COMMENT_DISCUSSION_SPACE'	=> 'SNS_DISCUSSION',
	'COMMENT_QUESTION_SPACE'			=> 'SNS_QUESTION',

	'FEEL_FEEL_CITY'				=> 'SNS_FEEL',
	'COMMENT_CRITIQUE_CITY'			=> 'SNS_CRITIQUE_CITY',
	'COMMENT_DISCUSSION_CITY'	=> 'SNS_DISCUSSION',
	'COMMENT_QUESTION_CITY'			=> 'SNS_QUESTION',
	'SHAREALBUM_ALBUM_CITY'		=> 'SNSALBUM_SHARE',

	'FEEL_FEEL_SPOT'				=> 'SNS_FEEL',
	'COMMENT_CRITIQUE_SPOT'			=> 'SNS_CRITIQUE_LOCATION',
	'COMMENT_DISCUSSION_SPOT'	=> 'SNS_DISCUSSION',
	'COMMENT_QUESTION_SPOT'			=> 'SNS_QUESTION',
	'SHAREALBUM_ALBUM_SPOT'		=> 'SNSALBUM_SHARE',

	'CLICK_CLICK_COMMENT'		=> 'SNSCOMMENT_CLICK',

	'TOBE_FRIEND_FRIEND'	=> 'SNS_INVITE',
	'DEL_FRIEND_FRIEND'	=> 'SNS_REMOVE',

	'UPDATE_NAME'		=> 'SNS_UPDATE_NAME',
	'UPDATE_AVATAR'		=> 'SNS_UPDATE_AVATAR',
	'UPDATE_RESIDE' 	=> 'SNS_UPDATE_RESIDE',

	'DELETE_BLOG_BLOG'		=> 'SNSBLOG_DELETE',
	'DELETE_PIC_PIC'		=> 'SNSPIC_DELETE',
	'DELETE_POLL_POLL'		=> 'SNSPOLL_DELETE',
	'DELETE_EVENT_EVENT'	=> 'SNSEVENT_DELETE',
	'DELETE_SHARE_SHARE'	=> 'SNS_DELETE_SHARE',
	'DELETE_CRITIQUE_COMMENT'	=> 'SNS_OTHER',
	'DELETE_CRITIQUE_PIC'		=>	'SNS_DELETE_CRITIQUEPIC',
	'DELETE_DISCUSSION_COMMENT'	=> 'SNS_DELETE_DISCUSSION',
	'DELETE_DOING_DOING'		=> 'SNS_OTHER',
	'DELETE_THREAD_THREAD'		=> 'SNS_OTHER',
	'DELETE_THREADREPLY_THREADREPLY'	=> 'SNS_OTHER',

	'DELETE_DISCUSSION_CITY'	=>'SNS_NOTHING', //删除 地点城市讨论
	'DELETE_DISCUSSION_PIC'	=>'SNS_NOTHING', //删除 地点城市讨论

	'DELETE_CRITIQUE_CITY'	=> 'SNS_DELETE_OTHER',
	'REPLY_REPLY_CRITIQUE'		=> 'SNS_REPLY',
	'REPLY_REPLY_DISCUSSION'	=> 'SNS_REPLY',
	'REPLY_REPLY_QUESTION'		=> 'SNS_REPLY',

);






$config["valid_type"] = array(
		'user', 'Location', 'Contribution', 'CommentID', 'Tools',
		'Post' );


/////////////////////////////////////////////////////////////////
//////SNS.Login
////////////统计服务
$config["ActionProcessCheck"]["101"]["statistic"][0] =
array(array('Userstat','SetContinous'),array(array('vid'),'SNS_Login_CTN','SNS_Login_CM'));
$config["ActionProcessCheck"]["101"]["statistic"][1] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'SNS_Login_CM',1));
//$config["ActionProcessCheck"]["101"]["statistic"][2] =
//array(array('Userstat_login','setlogin'),array(array('vid'),array('created')));
////////////成就
$config["ActionProcessCheck"]["101"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'SNS_Login'));
$config["ActionProcessCheck"]["101"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'SNS_Login_1-5'));
//////////////////////////////////////////////////////////////////
//////SNS.LVUP
////////////统计服务
$config["ActionProcessCheck"]["102"]["statistic"][0] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'SNS_LVUP',1));


$config["ActionProcessCheck"]["105"]["statistic"][0] =
array(array('Batch','addfriend'),array(array('ouid'),array('rouid')));

$config["ActionProcessCheck"]["106"]["statistic"][0] =
array(array('Batch','removefriend'),array(array('ouid'),array('rouid')));

$config["ActionProcessCheck"]["107"]["statistic"][0] =
array(array('Batch','update_nickname'),array(array('vid'),array('remark'=>'price')));

$config["ActionProcessCheck"]["108"]["statistic"][0] =
array(array('Batch','update_avatar'),array(array('vid'),1));


/*$config["ActionProcessCheck"]["103"]["statistic"][0] =
array(array('Userstat','SetMaxCurrentToday'),
array(array('vid'),'SNS_GUAGUA_CMT',1));*/

$config["ActionProcessCheck"]["103"]["statistic"][0] =
array(array('Userstat','setUserCash'),
array(array('vid') ));

$config["ActionProcessCheck"]["103"]["statistic"][1] =
array(array('Userstat','setUserAssets'),
array(array('vid') ) );

$config["ActionProcessCheck"]["103"]["statistic"][2] =
array(array('Userstat','setProperty'),
array(array('vid')));

$config["ActionProcessCheck"]["103"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["103"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));


/////////////////////////////////////////statistic//////////////////////////////////////////////////////////////////

//Location.buy 201
$config["ActionProcessCheck"]["201"]["statistic"][0] =
array(array('Userstat_landlord','setBuySell'),
array(array('vid'),'buy',array('remark'=>'ltype'),array('remark'=>'price')));

$config["ActionProcessCheck"]["201"]["statistic"][1] =
array(array('Userstat','setUserCash'),
array(array('vid') ));

$config["ActionProcessCheck"]["201"]["statistic"][2] =
array(array('Userstat','setUserAssets'),
array(array('vid') ) );

$config["ActionProcessCheck"]["201"]["statistic"][3] =
array(array('Userstat','setUserCash'),
array(array('ouid') ) );
//array(array('vid'),array('remark'=>'buyDiffAssets')));



$config["ActionProcessCheck"]["201"]["statistic"][4] =
array(array('Userstat_landlord','setBuySell'),
array(array('ouid'),'sell',array('remark'=>'ltype'),array('remark'=>'price')));

$config["ActionProcessCheck"]["201"]["statistic"][5] =
array(array('Userstat','setUserCash'),
array(array('ouid'),'0',array('remark'=>'sellRstCash')));

///买家地主
$config["ActionProcessCheck"]["201"]["statistic"][6] =
array(array('Userstat','setOwn'),
array(array('vid')));
///卖家地主
$config["ActionProcessCheck"]["201"]["statistic"][7] =
array(array('Userstat','setOwn'),
array(array('ouid')));

///买家领地
$config["ActionProcessCheck"]["201"]["statistic"][8] =
array(array('Userstat','setTerritory'),
array(array('vid')));
///卖家领地
$config["ActionProcessCheck"]["201"]["statistic"][9] =
array(array('Userstat','setTerritory'),
array(array('ouid')));

$config["ActionProcessCheck"]["201"]["statistic"][10] =
array(array('Locationstat','setPrice'),
array(array('lid')));

$config["ActionProcessCheck"]["201"]["statistic"][11] =
array(array('Locationstat','cityPriceChangeByBuy'),
array(array('lid')));

$config["ActionProcessCheck"]["201"]["statistic"][12] =
array(array('Locationstat','setIdx'),
array(array('lid')));

$config["ActionProcessCheck"]["201"]["statistic"][13] =
array(array('Locationstat','addBuyTimes'),
array(array('lid')));


$config["ActionProcessCheck"]["201"]["statistic"][14] =
array(array('Userstat','setProperty'),
array(array('vid')));
////////////成就

$config["ActionProcessCheck"]["201"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["201"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["201"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('ouid'),'CASH'));

$config["ActionProcessCheck"]["201"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_CM'));
$config["ActionProcessCheck"]["201"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Culture_CM'));
$config["ActionProcessCheck"]["201"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Entert_CM'));
$config["ActionProcessCheck"]["201"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Food_CM'));
$config["ActionProcessCheck"]["201"]["achievement"][7] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Hotel_CM'));
$config["ActionProcessCheck"]["201"]["achievement"][8] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Shopping_CM'));
$config["ActionProcessCheck"]["201"]["achievement"][9] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Trip_CM'));



//Location.visit

//find
$config["ActionProcessCheck"]["300"]["statistic"][0] =
array(array('Userstat','SetContinous'),array(array('vid'),'Discover_CTN','Discover_CM'));
$config["ActionProcessCheck"]["300"]["statistic"][1] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'Discover_CM',1));
$config["ActionProcessCheck"]["300"]["statistic"][2] =
array(array('Userstat_find','setFind'),array(array('vid'),array('remark'=>'ltype'),array('created')));

$config["ActionProcessCheck"]["300"]["statistic"][3] =
array(array('Locationstat','cityPriceChangeByDiscover'),
array(array('lid')));

///我的领地
$config["ActionProcessCheck"]["300"]["statistic"][4] =
array(array('Userstat','setTerritory'),
array(array('vid')));

///我的发现
$config["ActionProcessCheck"]["300"]["statistic"][5] =
array(array('Userstat','setDiscover'),
array(array('vid')));


$config["ActionProcessCheck"]["300"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_CM'));
$config["ActionProcessCheck"]["300"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Food_CM'));
$config["ActionProcessCheck"]["300"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Trip_CM'));
$config["ActionProcessCheck"]["300"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Shopping_CM'));
$config["ActionProcessCheck"]["300"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Culture_CM'));
$config["ActionProcessCheck"]["300"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Entert_CM'));
$config["ActionProcessCheck"]["300"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Hotel_CM'));
$config["ActionProcessCheck"]["300"]["achievement"][7] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Time_1_5_CM'));
$config["ActionProcessCheck"]["300"]["achievement"][8] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_CTN'));
$config["ActionProcessCheck"]["300"]["achievement"][9] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Food_CTN'));
$config["ActionProcessCheck"]["300"]["achievement"][10] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Trip_CTN'));
$config["ActionProcessCheck"]["300"]["achievement"][11] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Shopping_CTN'));
$config["ActionProcessCheck"]["300"]["achievement"][12] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Culture_CTN'));
$config["ActionProcessCheck"]["300"]["achievement"][13] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Entert_CTN'));
$config["ActionProcessCheck"]["300"]["achievement"][14] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Hotel_CTN'));
$config["ActionProcessCheck"]["300"]["achievement"][15] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Discover_Time_1_5_CTN'));


//Comment
$config["ActionProcessCheck"]["500"]["statistic"][0] =
array(array('Userstat','SetContinous'),array(array('vid'),'Comment_CTN','Comment_CM'));
$config["ActionProcessCheck"]["500"]["statistic"][1] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'Comment_CM',1));
$config["ActionProcessCheck"]["500"]["statistic"][2] =
array(array('Userstat_comment','setComment'),array(array('vid'),array('remark'=>'ltype'),array('created')));

//Tools

//Officer
//setTerritory



//Favorite
$config["ActionProcessCheck"]["601"]["statistic"][0] =
array(array('Userstat','setFavorite'),
array(array('vid')));
$config["ActionProcessCheck"]["601"]["statistic"][1] =
array(array('Userstat','setVisit'),
array(array('vid')));
$config["ActionProcessCheck"]["601"]["statistic"][2] =
array(array('Locationstat','setLocFavorite'),
array(array('lid')));
$config["ActionProcessCheck"]["601"]["statistic"][3] =
array(array('Locationstat','setLocVisit'),
array(array('lid')));

//$config["ActionProcessCheck"]["601"]["statistic"][3] =
//array(array('Userlocationstat','setFavorite'),
//array(array('vid'),array('lid')));


$config["ActionProcessCheck"]["602"]["statistic"][0] =
array(array('Userstat','setFavorite'),
array(array('vid')));
$config["ActionProcessCheck"]["602"]["statistic"][1] =
array(array('Userstat','setLove'),
array(array('vid')));
$config["ActionProcessCheck"]["602"]["statistic"][2] =
array(array('Locationstat','setLocFavorite'),
array(array('lid')));
$config["ActionProcessCheck"]["602"]["statistic"][3] =
array(array('Locationstat','setLocLove'),
array(array('lid')));
//$config["ActionProcessCheck"]["602"]["statistic"][3] =
//array(array('Userlocationstat','setFavorite'),
//array(array('vid'),array('lid')));

$config["ActionProcessCheck"]["603"]["statistic"][0] =
array(array('Userstat','setFavorite'),
array(array('vid')));
$config["ActionProcessCheck"]["603"]["statistic"][1] =
array(array('Userstat','setVisit'),
array(array('vid')));
$config["ActionProcessCheck"]["603"]["statistic"][2] =
array(array('Locationstat','setLocFavorite'),
array(array('lid')));
$config["ActionProcessCheck"]["603"]["statistic"][3] =
array(array('Locationstat','setLocVisit'),
array(array('lid')));
//$config["ActionProcessCheck"]["603"]["statistic"][3] =
//array(array('Userlocationstat','removeFavorite'),
//array(array('vid'),array('lid')));


$config["ActionProcessCheck"]["604"]["statistic"][0] =
array(array('Userstat','setFavorite'),
array(array('vid')));
$config["ActionProcessCheck"]["604"]["statistic"][1] =
array(array('Userstat','setLove'),
array(array('vid')));
$config["ActionProcessCheck"]["604"]["statistic"][2] =
array(array('Locationstat','setLocFavorite'),
array(array('lid')));
$config["ActionProcessCheck"]["604"]["statistic"][3] =
array(array('Locationstat','setLocLove'),
array(array('lid')));


$config["ActionProcessCheck"]["605"]["statistic"][0] =
array(array('Userstat','setFavorite'),
array(array('vid')));
$config["ActionProcessCheck"]["605"]["statistic"][1] =
array(array('Userstat','setVisit'),
array(array('vid')));
$config["ActionProcessCheck"]["605"]["statistic"][2] =
array(array('Locationstat','setCityFavorite'),
array(array('lid')));
$config["ActionProcessCheck"]["605"]["statistic"][3] =
array(array('Locationstat','setCityVisit'),
array(array('lid')));

//$config["ActionProcessCheck"]["601"]["statistic"][3] =
//array(array('Userlocationstat','setFavorite'),
//array(array('vid'),array('lid')));


$config["ActionProcessCheck"]["606"]["statistic"][0] =
array(array('Userstat','setFavorite'),
array(array('vid')));
$config["ActionProcessCheck"]["606"]["statistic"][1] =
array(array('Userstat','setLove'),
array(array('vid')));

$config["ActionProcessCheck"]["606"]["statistic"][2] =
array(array('Locationstat','setCityFavorite'),
array(array('lid')));

$config["ActionProcessCheck"]["606"]["statistic"][3] =
array(array('Locationstat','setCityLove'),
array(array('lid')));
//$config["ActionProcessCheck"]["602"]["statistic"][3] =
//array(array('Userlocationstat','setFavorite'),
//array(array('vid'),array('lid')));

$config["ActionProcessCheck"]["607"]["statistic"][0] =
array(array('Userstat','setFavorite'),
array(array('vid')));
$config["ActionProcessCheck"]["607"]["statistic"][1] =
array(array('Userstat','setVisit'),
array(array('vid')));
$config["ActionProcessCheck"]["607"]["statistic"][2] =
array(array('Locationstat','setCityFavorite'),
array(array('lid')));
$config["ActionProcessCheck"]["607"]["statistic"][3] =
array(array('Locationstat','setCityVisit'),
array(array('lid')));
//$config["ActionProcessCheck"]["603"]["statistic"][3] =
//array(array('Userlocationstat','removeFavorite'),
//array(array('vid'),array('lid')));


$config["ActionProcessCheck"]["608"]["statistic"][0] =
array(array('Userstat','setFavorite'),
array(array('vid')));
$config["ActionProcessCheck"]["608"]["statistic"][1] =
array(array('Userstat','setLove'),
array(array('vid')));
$config["ActionProcessCheck"]["608"]["statistic"][2] =
array(array('Locationstat','setCityFavorite'),
array(array('lid')));
$config["ActionProcessCheck"]["608"]["statistic"][3] =
array(array('Locationstat','setCityLove'),
array(array('lid')));


//$config["ActionProcessCheck"]["604"]["statistic"][3] =
//array(array('Userlocationstat','removeFavorite'),
//array(array('vid'),array('lid')));


//$config["ActionProcessCheck"]["605"]["statistic"][0] =
//array(array('Userlocationstat','setFavorite'),
//array(array('vid'),array('lid')));

//$config["ActionProcessCheck"]["606"]["statistic"][0] =
//array(array('Userlocationstat','setFavorite'),
//array(array('vid'),array('lid')));

//$config["ActionProcessCheck"]["607"]["statistic"][0] =
//array(array('Userlocationstat','removeFavorite'),
//array(array('vid'),array('lid')));

//$config["ActionProcessCheck"]["608"]["statistic"][0] =
//array(array('Userlocationstat','removeFavorite'),
//array(array('vid'),array('lid')));
/*
$config["ActionProcessCheck"]["601"]["statistic"][0] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'Favorite_Visited_Location_CM',1));
$config["ActionProcessCheck"]["602"]["statistic"][0] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'Favorite_Love_Location_CM',1));
$config["ActionProcessCheck"]["603"]["statistic"][0] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'Favorite_Visited_Location_CM',-1));
$config["ActionProcessCheck"]["604"]["statistic"][0] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'Favorite_Love_Location_CM',-1));
$config["ActionProcessCheck"]["605"]["statistic"][0] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'Favorite_Visited_City_CM',1));
$config["ActionProcessCheck"]["606"]["statistic"][0] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'Favorite_Love_City_CM',1));
$config["ActionProcessCheck"]["607"]["statistic"][0] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'Favorite_Visited_City_CM',-1));
$config["ActionProcessCheck"]["608"]["statistic"][0] =
array(array('Userstat','SetMaxCurrent'),array(array('vid'),'Favorite_Love_City_CM',-1));*/

//Event
/////////////////////////////////////////////////////

$config["ActionProcessCheck"]["701"]["statistic"][0] =
array(array('Userstat','setProperty'),
array(array('vid')));

$config["ActionProcessCheck"]["702"]["statistic"][0] =
array(array('Userstat','setProperty'),
array(array('vid')));

$config["ActionProcessCheck"]["702"]["statistic"][1] =
array(array('Property','usePropsAsycn'),
array(array('vid'),array('oid'),array('roid')));

///////////////////////////////frozen////////////////////////////////////
$config["ActionProcessCheck"]["3204"]["statistic"][0] = array(array('Location','SyncAfterFrozen'),
array(array('lid'),array("remark"=>"cityLid"),array("remark"=>"price"),array("remark"=>"explorerUid"),array("remark"=>"ownerUid"),
array("remark"=>"managerUid"),array("remark"=>"tags"),array("remark"=>"created")  ));

$config["ActionProcessCheck"]["3204"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array("remark"=>"ownerUid"),'CASH'));
$config["ActionProcessCheck"]["3204"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array("remark"=>"ownerUid"),'ASSETS_CM'));
$config["ActionProcessCheck"]["3204"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array("remark"=>"managerUid"),'CASH'));
$config["ActionProcessCheck"]["3204"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array("remark"=>"managerUid"),'ASSETS_CM'));




////////////////////////////////elect//////////////////////////////////////////
$config["ActionProcessCheck"]["3203"]["statistic"][0] = array(array('Location','elect'),
array(array('lid'),array("remark"=>"period")));
$config["ActionProcessCheck"]["3302"]["statistic"][0] = array(array('City','elect'),
array(array('lid'),array("remark"=>"period")));


////////////////////////////////elected//////////////////////////////////////////
$config["ActionProcessCheck"]["3101"]["statistic"][0] = array(array('Userstat_ceo','setElect'),
array(array('vid'),'get',array('remark'=>'ltype'),array('remark'=>'price')));

$config["ActionProcessCheck"]["3101"]["statistic"][1] = array(array('Userstat_ceo','setElect'),
array(array('remark'=>'exceo'),'lost',array('remark'=>'ltype'),array('remark'=>'price')));

$config["ActionProcessCheck"]["3101"]["statistic"][2] = array(array('Userstat','setUserCash'),
array(array('vid') ));
$config["ActionProcessCheck"]["3101"]["statistic"][3] = array(array('Userstat','setUserAssets'),
array(array('vid') ) );
$config["ActionProcessCheck"]["3101"]["statistic"][4] = array(array('Userstat','setUserCash'),
array(array('remark'=>'exceo')));
$config["ActionProcessCheck"]["3101"]["statistic"][5] = array(array('Userstat','setUserAssets'),
array(array('remark'=>'exceo')) );




$config["ActionProcessCheck"]["3102"]["statistic"][0] = array(array('Userstat','setOfficer'),
array(array('vid'),array('remark'=>'exofficer'),array('remark'=>'retake')));
$config["ActionProcessCheck"]["3102"]["statistic"][1] = array(array('Userstat','setUserCash'),
array(array('vid') ));
$config["ActionProcessCheck"]["3102"]["statistic"][2] = array(array('Userstat','setUserAssets'),
array(array('vid') ) );
$config["ActionProcessCheck"]["3102"]["statistic"][3] = array(array('Userstat','setUserCash'),
array(array('remark'=>'exofficer')));
$config["ActionProcessCheck"]["3102"]["statistic"][4] = array(array('Userstat','setUserAssets'),
array(array('remark'=>'exofficer')) );

$config["ActionProcessCheck"]["3103"]["statistic"][0] = array(array('Userstat','setOfficer'),
array(array('vid'),array('remark'=>'exofficer'),array('remark'=>'retake')));
$config["ActionProcessCheck"]["3103"]["statistic"][1] = array(array('Userstat','setUserCash'),
array(array('vid') ));
$config["ActionProcessCheck"]["3103"]["statistic"][2] = array(array('Userstat','setUserAssets'),
array(array('vid') ) );
$config["ActionProcessCheck"]["3103"]["statistic"][3] = array(array('Userstat','setUserCash'),
array(array('remark'=>'exofficer')));
$config["ActionProcessCheck"]["3103"]["statistic"][4] = array(array('Userstat','setUserAssets'),
array(array('remark'=>'exofficer')) );


$config["ActionProcessCheck"]["3104"]["statistic"][0] = array(array('Userstat','setOfficer'),
array(array('vid'),array('remark'=>'exofficer'),array('remark'=>'retake')));
$config["ActionProcessCheck"]["3104"]["statistic"][1] = array(array('Userstat','setUserCash'),
array(array('vid') ));
$config["ActionProcessCheck"]["3104"]["statistic"][2] = array(array('Userstat','setUserAssets'),
array(array('vid') ) );
$config["ActionProcessCheck"]["3104"]["statistic"][3] = array(array('Userstat','setUserCash'),
array(array('remark'=>'exofficer')));
$config["ActionProcessCheck"]["3104"]["statistic"][4] = array(array('Userstat','setUserAssets'),
array(array('remark'=>'exofficer')) );

$config["ActionProcessCheck"]["3105"]["statistic"][0] = array(array('Userstat','setOfficer'),
array(array('vid'),array('remark'=>'exofficer'),array('remark'=>'retake')));
$config["ActionProcessCheck"]["3105"]["statistic"][1] = array(array('Userstat','setUserCash'),
array(array('vid') ));
$config["ActionProcessCheck"]["3105"]["statistic"][2] = array(array('Userstat','setUserAssets'),
array(array('vid') ) );
$config["ActionProcessCheck"]["3105"]["statistic"][3] = array(array('Userstat','setUserCash'),
array(array('remark'=>'exofficer')));
$config["ActionProcessCheck"]["3105"]["statistic"][4] = array(array('Userstat','setUserAssets'),
array(array('remark'=>'exofficer')) );

$config["ActionProcessCheck"]["3106"]["statistic"][0] = array(array('Userstat','setOfficer'),
array(array('vid'),array('remark'=>'exofficer'),array('remark'=>'retake')));
$config["ActionProcessCheck"]["3106"]["statistic"][1] = array(array('Userstat','setUserCash'),
array(array('vid') ));
$config["ActionProcessCheck"]["3106"]["statistic"][2] = array(array('Userstat','setUserAssets'),
array(array('vid') ) );
$config["ActionProcessCheck"]["3106"]["statistic"][3] = array(array('Userstat','setUserCash'),
array(array('remark'=>'exofficer')));
$config["ActionProcessCheck"]["3106"]["statistic"][4] = array(array('Userstat','setUserAssets'),
array(array('remark'=>'exofficer')) );

$config["ActionProcessCheck"]["3107"]["statistic"][0] = array(array('Userstat','setOfficer'),
array(array('vid'),array('remark'=>'exofficer'),array('remark'=>'retake')));
$config["ActionProcessCheck"]["3107"]["statistic"][1] = array(array('Userstat','setUserCash'),
array(array('vid') ));
$config["ActionProcessCheck"]["3107"]["statistic"][2] = array(array('Userstat','setUserAssets'),
array(array('vid') ) );
$config["ActionProcessCheck"]["3107"]["statistic"][3] = array(array('Userstat','setUserCash'),
array(array('remark'=>'exofficer')));
$config["ActionProcessCheck"]["3107"]["statistic"][4] = array(array('Userstat','setUserAssets'),
array(array('remark'=>'exofficer')) );

$config["ActionProcessCheck"]["3108"]["statistic"][0] = array(array('Userstat','setOfficer'),
array(array('vid'),array('remark'=>'exofficer'),array('remark'=>'retake')));
$config["ActionProcessCheck"]["3108"]["statistic"][1] = array(array('Userstat','setUserCash'),
array(array('vid') ));
$config["ActionProcessCheck"]["3108"]["statistic"][2] = array(array('Userstat','setUserAssets'),
array(array('vid') ) );
$config["ActionProcessCheck"]["3108"]["statistic"][3] = array(array('Userstat','setUserCash'),
array(array('remark'=>'exofficer')));
$config["ActionProcessCheck"]["3108"]["statistic"][4] = array(array('Userstat','setUserAssets'),
array(array('remark'=>'exofficer')) );


$config["ActionProcessCheck"]["3101"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CEO_P2000'));
$config["ActionProcessCheck"]["3101"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CEO_P2000_Culture'));
$config["ActionProcessCheck"]["3101"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CEO_P2000_Entert'));
$config["ActionProcessCheck"]["3101"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CEO_P2000_Food'));
$config["ActionProcessCheck"]["3101"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CEO_P2000_Hotel'));
$config["ActionProcessCheck"]["3101"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CEO_P2000_Shopping'));
$config["ActionProcessCheck"]["3101"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CEO_P2000_Trip'));
$config["ActionProcessCheck"]["3101"]["achievement"][7] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["3101"]["achievement"][8] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["3101"]["achievement"][9] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exceo'),'CASH'));
$config["ActionProcessCheck"]["3101"]["achievement"][10] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exceo'),'ASSETS_CM'));


$config["ActionProcessCheck"]["3102"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_CM'));
$config["ActionProcessCheck"]["3102"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Total_CM'));
$config["ActionProcessCheck"]["3102"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Retake_CM'));
$config["ActionProcessCheck"]["3102"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["3102"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["3102"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'CASH'));
$config["ActionProcessCheck"]["3102"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'ASSETS_CM'));


$config["ActionProcessCheck"]["3103"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_CM'));
$config["ActionProcessCheck"]["3103"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Total_CM'));
$config["ActionProcessCheck"]["3103"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Retake_CM'));
$config["ActionProcessCheck"]["3103"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["3103"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["3103"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'CASH'));
$config["ActionProcessCheck"]["3103"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'ASSETS_CM'));


$config["ActionProcessCheck"]["3104"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_CM'));
$config["ActionProcessCheck"]["3104"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Total_CM'));
$config["ActionProcessCheck"]["3104"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Retake_CM'));
$config["ActionProcessCheck"]["3104"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["3104"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["3104"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'CASH'));
$config["ActionProcessCheck"]["3104"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'ASSETS_CM'));

$config["ActionProcessCheck"]["3105"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_CM'));
$config["ActionProcessCheck"]["3105"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Total_CM'));
$config["ActionProcessCheck"]["3105"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Retake_CM'));
$config["ActionProcessCheck"]["3105"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["3105"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["3105"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'CASH'));
$config["ActionProcessCheck"]["3105"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'ASSETS_CM'));

$config["ActionProcessCheck"]["3106"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_CM'));
$config["ActionProcessCheck"]["3106"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Total_CM'));
$config["ActionProcessCheck"]["3106"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Retake_CM'));
$config["ActionProcessCheck"]["3106"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["3106"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["3106"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'CASH'));
$config["ActionProcessCheck"]["3106"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'ASSETS_CM'));

$config["ActionProcessCheck"]["3107"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_CM'));
$config["ActionProcessCheck"]["3107"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Total_CM'));
$config["ActionProcessCheck"]["3107"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Retake_CM'));
$config["ActionProcessCheck"]["3107"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["3107"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["3107"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'CASH'));
$config["ActionProcessCheck"]["3107"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'ASSETS_CM'));

$config["ActionProcessCheck"]["3108"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_CM'));
$config["ActionProcessCheck"]["3108"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Total_CM'));
$config["ActionProcessCheck"]["3108"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Officer_Retake_CM'));
$config["ActionProcessCheck"]["3108"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["3108"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["3108"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'CASH'));
$config["ActionProcessCheck"]["3108"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('remark'=>'exofficer'),'ASSETS_CM'));

//$uid,$action,$locationtype,$locationprice



/////////////////////////////////////////contribution//////////////////////////////////////////////////////////////////


$config["ActionProcessCheck"]["5101"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5101,1));

$config["ActionProcessCheck"]["5102"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5102,1));

$config["ActionProcessCheck"]["5103"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5103,1));

$config["ActionProcessCheck"]["5104"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5104,1));

$config["ActionProcessCheck"]["5105"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5105,1));


$config["ActionProcessCheck"]["5106"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5106,1));


$config["ActionProcessCheck"]["5107"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5107,1));

$config["ActionProcessCheck"]["5108"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5108,1));

$config["ActionProcessCheck"]["5109"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5109,1));




$config["ActionProcessCheck"]["5110"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5110,1));
$config["ActionProcessCheck"]["5110"]["statistic"][1] =
array(array('Userlocationstat_contribution','setCritique'),
array(array('vid'),array("remark"=>"referId")));

$config["ActionProcessCheck"]["5110"]["statistic"][2] =
array(array('Userstat_comment','setComment'),
array(array('vid'),array("remark"=>"referId"),array('created')));


$config["ActionProcessCheck"]["5114"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),20,array('logid'),5114,1));
$config["ActionProcessCheck"]["5114"]["statistic"][1] =
array(array('Userlocationstat_contribution','setCritique'),
array(array('vid'),array("remark"=>"referId")));

$config["ActionProcessCheck"]["5114"]["statistic"][2] =
array(array('Userstat_comment','setComment'),
array(array('vid'),array("remark"=>"referId"),array('created')));




$config["ActionProcessCheck"]["5113"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5113,1));

$config["ActionProcessCheck"]["5115"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5115,0.25)); ////other印象

$config["ActionProcessCheck"]["5116"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5116,0.5)); ////other评价






$config["ActionProcessCheck"]["5121"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContributionBylid'),
array(array('vid'),array('lid'),array('remark'=>'contribution'),array('logid'),5121)); ////tips

$config["ActionProcessCheck"]["5121"]["statistic"][1] =
array(array('User','add_money'),
array(array('vid'),array('remark'=>'gold'), "add tips logid:".array('logid')));


$config["ActionProcessCheck"]["5150"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('vid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5150,1));



$config["ActionProcessCheck"]["5201"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('ouid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5201,1));

$config["ActionProcessCheck"]["5202"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('ouid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5202,1));


$config["ActionProcessCheck"]["5203"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('ouid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5203,1));

$config["ActionProcessCheck"]["5204"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('ouid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5204,1));

$config["ActionProcessCheck"]["5205"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('ouid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5205,1));

$config["ActionProcessCheck"]["5206"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('ouid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5206,1));

$config["ActionProcessCheck"]["5207"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('ouid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5207,1));


$config["ActionProcessCheck"]["5211"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('ouid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5211,0.5)); ////删除图片评价


$config["ActionProcessCheck"]["5215"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContribution'),
array(array('ouid'),array("remark"=>"referId"),array("remark"=>"cash"),array('logid'),5215,1));


$config["ActionProcessCheck"]["5221"]["statistic"][0] =
array(array('Userlocationstat_contribution','setContributionBylid'),
array(array('ouid'),array('lid'),array('remark'=>'contribution'),array('logid'),5221)); ////tips

$config["ActionProcessCheck"]["5221"]["statistic"][1] =
array(array('User','add_money'),
array(array('ouid'),array('remark'=>'gold'), "delete tips logid:".array('logid')));
//点评类成就

$config["ActionProcessCheck"]["5110"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_CM'));
$config["ActionProcessCheck"]["5110"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Food_CM'));
$config["ActionProcessCheck"]["5110"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Trip_CM'));
$config["ActionProcessCheck"]["5110"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Shopping_CM'));
$config["ActionProcessCheck"]["5110"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Culture_CM'));
$config["ActionProcessCheck"]["5110"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Entert_CM'));
$config["ActionProcessCheck"]["5110"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Hotel_CM'));
$config["ActionProcessCheck"]["5110"]["achievement"][7] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Time_1_5_CM'));
$config["ActionProcessCheck"]["5110"]["achievement"][8] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_CTN'));
$config["ActionProcessCheck"]["5110"]["achievement"][9] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Food_CTN'));
$config["ActionProcessCheck"]["5110"]["achievement"][10] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Trip_CTN'));
$config["ActionProcessCheck"]["5110"]["achievement"][11] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Shopping_CTN'));
$config["ActionProcessCheck"]["5110"]["achievement"][12] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Culture_CTN'));
$config["ActionProcessCheck"]["5110"]["achievement"][13] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Entert_CTN'));
$config["ActionProcessCheck"]["5110"]["achievement"][14] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Hotel_CTN'));
$config["ActionProcessCheck"]["5110"]["achievement"][15] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Time_1_5_CTN'));


$config["ActionProcessCheck"]["5114"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_CM'));
$config["ActionProcessCheck"]["5114"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Food_CM'));
$config["ActionProcessCheck"]["5114"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Trip_CM'));
$config["ActionProcessCheck"]["5114"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Shopping_CM'));
$config["ActionProcessCheck"]["5114"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Culture_CM'));
$config["ActionProcessCheck"]["5114"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Entert_CM'));
$config["ActionProcessCheck"]["5114"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Hotel_CM'));
$config["ActionProcessCheck"]["5114"]["achievement"][7] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Time_1_5_CM'));
$config["ActionProcessCheck"]["5114"]["achievement"][8] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_CTN'));
$config["ActionProcessCheck"]["5114"]["achievement"][9] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Food_CTN'));
$config["ActionProcessCheck"]["5114"]["achievement"][10] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Trip_CTN'));
$config["ActionProcessCheck"]["5114"]["achievement"][11] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Shopping_CTN'));
$config["ActionProcessCheck"]["5114"]["achievement"][12] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Culture_CTN'));
$config["ActionProcessCheck"]["5114"]["achievement"][13] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Entert_CTN'));
$config["ActionProcessCheck"]["5114"]["achievement"][14] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Hotel_CTN'));
$config["ActionProcessCheck"]["5114"]["achievement"][15] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Comment_Time_1_5_CTN'));



/////////////////////////exchange Location////////////////////////////////
$config["ActionProcessCheck"]["211"]["statistic"][0] =
array(array('Userstat_landlord','setExchange'),
array(array('vid'),array('rouid'),array('remark'=>'locPrice'),array('remark'=>'targetLocPrice')
,array('remark'=>'locType'),array('remark'=>'targetLocType')));


$config["ActionProcessCheck"]["211"]["statistic"][1] =
array(array('Userstat','setUserAssets'),
array(array('vid') ) );

$config["ActionProcessCheck"]["211"]["statistic"][2] =
array(array('Userstat','setUserAssets'),
array(array('rouid') ) );

//////////////////////////////

$config["ActionProcessCheck"]["211"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('rouid'),'ASSETS_CM'));

$config["ActionProcessCheck"]["211"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Culture_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Entert_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Food_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Hotel_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][7] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Shopping_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][8] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'Location_P2000_Trip_CM'));

$config["ActionProcessCheck"]["211"]["achievement"][9] =
array(array('Achievement','checkAchievementGroup'),array(array('rouid'),'Location_P2000_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][10] =
array(array('Achievement','checkAchievementGroup'),array(array('rouid'),'Location_P2000_Culture_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][11] =
array(array('Achievement','checkAchievementGroup'),array(array('rouid'),'Location_P2000_Entert_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][12] =
array(array('Achievement','checkAchievementGroup'),array(array('rouid'),'Location_P2000_Food_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][13] =
array(array('Achievement','checkAchievementGroup'),array(array('rouid'),'Location_P2000_Hotel_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][14] =
array(array('Achievement','checkAchievementGroup'),array(array('rouid'),'Location_P2000_Shopping_CM'));
$config["ActionProcessCheck"]["211"]["achievement"][15] =
array(array('Achievement','checkAchievementGroup'),array(array('rouid'),'Location_P2000_Trip_CM'));










/////////////////////////send Location////////////////////////////////

$config["ActionProcessCheck"]["221"]["statistic"][0] =
array(array('Userstat_landlord','setSend'),
array(array('vid'),array('roid'),array('remark'=>'locPrice'),array('remark'=>'locType')));


$config["ActionProcessCheck"]["221"]["statistic"][1] =
array(array('Userstat','setUserCash'),
array(array('vid') ));

$config["ActionProcessCheck"]["221"]["statistic"][2] =
array(array('Userstat','setUserAssets'),
array(array('vid') ) );

$config["ActionProcessCheck"]["221"]["statistic"][3] =
array(array('Userstat','setUserCash'),
array(array('roid') ));

$config["ActionProcessCheck"]["221"]["statistic"][4] =
array(array('Userstat','setUserAssets'),
array(array('roid') ) );


///买家地主
$config["ActionProcessCheck"]["221"]["statistic"][5] =
array(array('Userstat','setOwn'),
array(array('vid')));
///卖家地主
$config["ActionProcessCheck"]["221"]["statistic"][6] =
array(array('Userstat','setOwn'),
array(array('roid')));

 ///买家领地
$config["ActionProcessCheck"]["221"]["statistic"][7] =
array(array('Userstat','setTerritory'),
array(array('vid')));
///卖家领地
$config["ActionProcessCheck"]["221"]["statistic"][8] =
array(array('Userstat','setTerritory'),
array(array('roid')));



////////////成就

$config["ActionProcessCheck"]["221"]["achievement"][0] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'CASH'));
$config["ActionProcessCheck"]["221"]["achievement"][1] =
array(array('Achievement','checkAchievementGroup'),array(array('vid'),'ASSETS_CM'));
$config["ActionProcessCheck"]["221"]["achievement"][2] =
array(array('Achievement','checkAchievementGroup'),array(array('roid'),'CASH'));
$config["ActionProcessCheck"]["221"]["achievement"][3] =
array(array('Achievement','checkAchievementGroup'),array(array('roid'),'ASSETS_CM'));

$config["ActionProcessCheck"]["221"]["achievement"][4] =
array(array('Achievement','checkAchievementGroup'),array(array('roid'),'Location_P2000_CM'));
$config["ActionProcessCheck"]["221"]["achievement"][5] =
array(array('Achievement','checkAchievementGroup'),array(array('roid'),'Location_P2000_Culture_CM'));
$config["ActionProcessCheck"]["221"]["achievement"][6] =
array(array('Achievement','checkAchievementGroup'),array(array('roid'),'Location_P2000_Entert_CM'));
$config["ActionProcessCheck"]["221"]["achievement"][7] =
array(array('Achievement','checkAchievementGroup'),array(array('roid'),'Location_P2000_Food_CM'));
$config["ActionProcessCheck"]["221"]["achievement"][8] =
array(array('Achievement','checkAchievementGroup'),array(array('roid'),'Location_P2000_Hotel_CM'));
$config["ActionProcessCheck"]["221"]["achievement"][9] =
array(array('Achievement','checkAchievementGroup'),array(array('roid'),'Location_P2000_Shopping_CM'));
$config["ActionProcessCheck"]["221"]["achievement"][10] =
array(array('Achievement','checkAchievementGroup'),array(array('roid'),'Location_P2000_Trip_CM'));


$config["ActionProcessCheck"]["6201"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6202"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6221"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6222"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6211"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6212"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));

$config["ActionProcessCheck"]["6350"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6301"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6302"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6303"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6304"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6305"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6306"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6307"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6308"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));

$config["ActionProcessCheck"]["6321"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6322"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6323"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6324"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6325"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6326"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6327"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6328"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6401"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));
$config["ActionProcessCheck"]["6402"]["statistic"][0] =
array(array('User','add_money'),array(array('vid'),5, "syncmsg to sina".array('logid')));


/////////////////////////////////////////statistic//////////////////////////////////////////////////////////////////








//用户升级
$config["ActionProcessCheck"]["102"]["randomevent"] = 'E0002';



?>