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

$config['redirect']['default'] = "/social/redirect";
$config['redirect']['pass'] = false;
$config['redirect']['login_and_bind']['default'] = '/login';

// Supported Provider Settings
$config['providers'] = array(
	// unbind: Is the provider unbindable, which means user can unbind the provider from UI.
	// name: For website display purpose.
	// account: Account id provided by provider.
	// bindable: Default false. Is the provider credential can be used to bind a existed account, or for user creation only.
	// registerable: Default true. Is the provider credential can be used to register, or for bind only.
	// gateway: Default provider key. Actural API gateway to call.
	'sina' => array('unbind' => true, "name" => "新浪微博", "account" => 1058633245 ,"bindable"=>false)
//	'qq' => array('unbind' => true, "name" => "腾讯微博")
);

// Gateway Settings
$config['gateway']['test'] = array (
	'base' => "http://".$_SERVER['HTTP_HOST'],
	'protocol' => 'OAuth2'
);
$config['gateway']['sina'] = array (
	'base' => "https://api.weibo.com",
	'app_key' => '1058633245',
	'app_secret' => 'a102598375f2c4c7eebea562e338bbc0',
	'protocol' => 'OAuth2',
	'options' => array(AuthMethod_OAuth2_Driver::OPTIONS_KEY_BEARER_TOKEN_NAME => Token_Driver::OAUTH2_TOKEN_KEY)
);

// Test API Settings
$config['TestAPI']['get_test'] = '/ajax/social/test';
$config['TestAPI']['put_test'] = '/ajax/social/put_test';
// OAuth Default Settings
//$config['Credential']['request_token'] = "/oauth/request_token";
//$config['Credential']['authenticate'] = "/oauth/authorize";
//$config['Credential']['access_token'] = "/oauth/access_token";
// OAuth2 Default Settings
$config['Credential']['authenticate'] = "/oauth2/authorize";
$config['Credential']['access_token'] = "/oauth2/access_token";
// OAuth2 Sina Settings
$config['Credential']['sina']['authenticate'] = "/oauth2/authorize?scope=follow_app_official_microblog";
$config['Credential']['sina']['token_info'] = "/oauth2/get_token_info";
// Sina API Settings
$config['SocialAccount']['sina']['profile'] = "/2/account/get_uid.json";
$config['SocialUser']['sina']['info'] = '/users/show.json';

// Sina API Mappers
$config['global_mapper']['sina'] = array('uid' => 'identity', 'screen_name' => 'nickname');
$config['result_mapper']['sina']['SocialUser']['info'] = array('profile_image_url' => 'avatar');