<?php defined('SYSPATH') OR die('No direct access allowed.');
/*
 * APP level error
 * General error for every application
 */
// 100 Code errors
$config['E_API_INVALID_PARAMETER']  =		2100;
$config['E_API_INSUFFICIENT_PRIVILEGE']  =	2101;
$config['E_API_FAILED_REQUEST']  =			2102;
// city related errors 2200
$config['E_API_CITY_NOTFOUND']  =			2200;
// location related errors 2250
$config['E_API_LOCATION_NOTFOUND']  =		2250;
// guide related errors 2300
$config['E_API_GUIDE_NOT_FOUND']  =			2300;
// guide location related errors 2400
$config['E_API_MYLOCATION_NOT_FOUND']  =	2400;
// other 2900
$config['E_API_COMMENT_REGISTER_FAILED']  =	2900;
$config['E_API_INVALID_INSERT']  =			2901;
$config['E_API_INVALID_DATE']  =			2901;
$config['E_API_INVALID_TRAVELLER']  =		2903;

$config['E_MAP']=							5001;
$config['E_MAP_GOOLGE']=					5011;
$config['E_MAP_MAPABC']=					5012;
$config['E_MICO_UPLOAD_FAILED']=            5013;
$config['E_GUIDE_NOT_FIND']=            	6001;
$config['E_GUIDECOMMENT_NOT_FIND']=      	6002;
$config['E_GUIDEINVITATIONS_NOT_FIND']=     6003;

$config['E_GUIDE_UPGRADE']=     6004;
$config['E_EMPTY_EMAIL']=     6005;
$config['E_EMAIL_NOT_SEND_TIME']=     6007;

$config['E_CITY_NOTFOUND']  =			2200;
$config['E_PAGE_NOT_FOUND'] = 3001;