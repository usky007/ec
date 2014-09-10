<?php defined('SYSPATH') OR die('No direct access allowed.');
/*
 * APP level error
 * General error for every application
 */
// 200 User errors
$config['E_USER_NOT_FOUND']= 				200;
$config['E_USER_ACCOUNT_UNAVAILABLE']= 		201;
$config['E_USER_REGISTER_FAILED']= 			202;
$config['E_USER_ACTIVATE_REQUIRED']= 		203;
$config['E_USER_BLOCKED']= 					204;
$config['E_USER_PRIVACY_DENY']= 			205;
$config['E_USER_SESSION_EXPIRED']=			206;
$config['E_USER_NOT_LOGIN']= 				207;
$config['E_INVALID_PARAMETER']=             208;
$config['E_INVALID_PERMISSION']=            209;