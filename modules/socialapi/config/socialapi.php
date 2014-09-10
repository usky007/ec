<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Social API Settings
 *
 * base, app_key, app_secret
 * Base address, key, secret of social api gateway. All providers share these infomation.
 *
 * providers Providers' custom setttings, unbind:can be unbind. account:official account.
 */
/*
$config['base'] = "http://betasns.mico.cn:8080/sns";
$config['app_key'] = '1234567890';
$config['app_secret'] = '21ab571038bc8238cd65e60cf6466857';
$config['provider'] = 'traveller';

$config['Credential']['request_token'] = "/oauth/%s/request_token.do";
$config['Credential']['authenticate'] = "/oauth/%s/authenticate.do";
$config['Credential']['access_token'] = "/oauth/%s/access_token.do";
$config['SocialAccount']['profile'] = "/oauth/%s/account/profile.json";
*/

$config['redirect'] = "/social/redirect";

$config['providers'] = array(
	'sina' => array('unbind' => true, "name" => "新浪微博", "account" => 1058633245)
//	'qq' => array('unbind' => true, "name" => "腾讯微博")
);

// hybrid configuration settings
$config['gateway']['test'] = array (
	'base' => "http://".$_SERVER['HTTP_HOST'],
	'protocol' => 'OAuth2'
);

$config['gateway']['sina'] = array (
	'base' => "https://api.weibo.com",
	'app_key' => '1058633245',
	'app_secret' => 'a102598375f2c4c7eebea562e338bbc0',
	'protocol' => 'OAuth2'
);
$config['TestAPI']['get_test'] = '/ajax/social/test';
$config['TestAPI']['put_test'] = '/ajax/social/put_test';
$config['Credential']['authenticate'] = "/oauth2/authorize";
$config['Credential']['access_token'] = "/oauth2/access_token";
$config['Credential']['sina']['token_info'] = "/oauth2/get_token_info";
$config['SocialAccount']['sina']['profile'] = "/2/account/get_uid.json";


$config['gateway']['sinaOld'] = array (
	'base' => "http://api.t.sina.com.cn",
	'app_key' => '1058633245',
	'app_secret' => 'a102598375f2c4c7eebea562e338bbc0',
	'protocol' => 'OAuth'
);
$config['Credential']['sinaOld']['request_token'] = "/oauth/request_token";
$config['Credential']['sinaOld']['authenticate'] = "/oauth/authorize";
$config['Credential']['sinaOld']['access_token'] = "/oauth/access_token";
$config['SocialAccount']['sinaOld']['profile'] = "/account/verify_credential.json";