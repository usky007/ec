<?php
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
$config['ssl_ca_path'] = "c:\\path\\to\\ca-bundle.crt";

$config['gateway']['yanzi'] = array (
	'base' => "http://dev.feature.com",
	'protocol' => 'OAuth2'
);

// Supported Provider Settings
$config['providers'] = array(
	'sina' => array('unbind' => true, "name" => "新浪微博", "account" => 3507968164 ,"bindable"=>false)
);

$config['gateway']['sina']['app_key'] = '3507968164';
$config['gateway']['sina']['app_secret'] = '49d94b8eacb34c19b11ad638868f20aa';
