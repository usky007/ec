<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = array
(
	E_KOHANA             => array( 1, '框架错误',   '请根据下面的相关错误查阅 Kohana 文档。'),
	E_PAGE_NOT_FOUND     => array( 1, '页面不存在',    '请求页面不存在。或许它被转移，删除或存档。'),
	E_DATABASE_ERROR     => array( 1, '数据库错误',    '数据库在执行程序时出现错误。请从下面的错误信息检查数据库错误。'),
	E_RECOVERABLE_ERROR  => array( 1, '可回收错误', '发生错误在加载此页面时。如果这个问题仍然存在，请联系网站管理员。'),
	E_ERROR              => array( 1, '致命错误',       ''),
	E_USER_ERROR         => array( 1, '致命错误',       ''),
	E_PARSE              => array( 1, '语法错误',      ''),
	E_WARNING            => array( 1, '警告消息',   ''),
	E_USER_WARNING       => array( 1, '警告消息',   ''),
	E_STRICT             => array( 2, '严格（标准）模式错误', ''),
	E_NOTICE             => array( 2, '运行信息',   ''),
//	E_MAP             	 => array( 1, '地图搜索错误',   ''),


	"slog_invalid_type"	=>'不存在的统计日志类型',
	"slog_dir_unwritable" => '统计日志目录不可写',

	"upgrade_guide_valid_fail_no_name"	=> '地图缺少地图名称，无法升级为官方地图',
	"upgrade_guide_valid_fail_personal_location"	=> '地图包含非官方地点，无法升级为官方地图',

	"request_failure"			=> "请求失败",
	"missing_argument"			=> "缺少参数%s",
	"invalid_parameter"			=> "您没有提交正确的参数",
	"validation_failed"			=> "参数验证错误:%s",
	"unsupported"				=> "不支持此项功能",
	"invalid_entry"				=> "关键数据缺失，请重新尝试。",
	"ui_not_found"				=> "未找到请求的界面",
	"login_invalid_input"		=> "用户名或密码错误",
	"login_failed"				=> "登录失败",
	"not_login"                 => "尚未登录,请登录",
	"no_permission"             => "没有权限访问该页面",
	"user_not_found"			=> "无法找到用户信息",
	"do_not_have_title"			=> "您没有改称号",

	"upload_userfile_not_set"	=> "您没有选择文件",
	"upload_file_exceeds_limit" => "文件太大了",
	"upload_file_partial"	=> "文件被部分上传",
	"upload_no_file_selected"	=> "您没有选择文件",
	"upload_invalid_filetype"	=> "文件格式错误",
	"upload_invalid_filesize" => "文件大小不正确",
	"upload_invalid_dimensions"	=> "系统错误，存储路径出错",
	"upload_destination_error"	=> "系统错误，存储路径出错",
	"upload_no_filepath"	=> "上传路径不合法",
	"upload_no_file_types"	=> "该类型不允许上传",
	"upload_bad_filename"	=> "文件已存在",
	"upload_not_writable"	=> "上传目录不可写",
	"upload_err_unknown"	=> "未知错误",



	"register_nickname_not_complete"	=> "请输入昵称",
	'register_nickname_error' => '昵称只能包含中文英文空格和小括号，请修改',
	'register_email_error' => 'email地址格式错误',
	'register_not_accept' => '你必须接受服务条款',
	'user_name_is_not_legitimate' => '用户名不合法',
	'include_not_registered_words' => '用户名包含不允许注册的词语',
	'user_name_already_exists' => '用户名已经存在',
	'email_format_is_wrong' => '填写的 Email 格式有误',
	'email_not_registered' => '填写的 Email 不允许注册',
	'email_has_been_registered' => '填写的 Email 已经被注册',

	'register_error' => '注册失败',
	'unknown_error' => '未知错误，请重试',
	'bind_identity_has_been_binded' => '你已经绑定过了',
	'bind_account_has_been_binded' => '该帐号已被绑定了',
	'synclogin_fail' => '登录失败',
	'wrong_source' => '请从正确路径进入', // deprecated.
	'get_userinfo_fail' => '无法获取用户信息',

	"syncinfo_set_provider_error"	=> "设置开启同步失败",
	"syncinfo_setting_error"	=> "设置同步信息失败",
	"syncinfo_synctimes_limit"	=> "<br>您今天已经超过自动同步次数。",

	"does_not_have_this_city_information"  => "无此城市信息",
	"does_not_have_this_location_information"  => "无此地点信息",
	"city_will_open_soon"  => "此城市即将开放",
	"does_not_have_this_guide_information"  => "无此地图信息",
	"mapabc_data_not_found"     => "地点数据请求失败",
	"mapabc_data_not_found_log" => "搜索关键字  %s  第%s页",
	"mapabc_data_is_empty_log"  => "搜索关键字  %s  第%s页 数据返回为空",
	"mapabc_data_not_have"      => "没有找到你搜索的地点",
    "does_not_have_this_guidecomment_information" => "无此城市评论信息",
	"does_not_have_this_guideinvitations_information" => "无此地图邀请函",
	"does_not_have_this_guidelocations_information" => "无此地图地点",
	"does_not_have_this_traveller_information" => "无此同游者",
	"email_cannot_be_blank" => "邮件不能为空",
	"email_not_to_send_time" => "请稍后再发",
	"author_invalid" => "作者不能删除",
	"official_location_cant_copy" => "不能复制官方地点"
);