<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = array
(
	'Social_Exception'        		=> array( 5, '请求错误', '第三方 API 请求错误'),

	"request_failure"				=> '请求失败',
	"credential_not_found"          => '没有找到授权信息:%s',
	"authorization_required"		=> '无效的授权信息',
	"repeat_post"                   => '不能重复发布相同信息',
	"parameter_required"			=> '缺少参数:%s',
	"sign_algorithm_unsupported"	=> '签名算法不支持:%s',
	"missing_algorithm_definition"	=> '缺少算法定义',
	"token_type_unsupported"		=> '令牌类型不支持:%s',
	"request_unsupported"			=> '请求方法不支持:%s(%s)'
);
