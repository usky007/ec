<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['img_transform_service'] = true;
//$config['img_transform_filemapper']['site'] = array (
//	"pattern" => '/$/',
//	"replacement" => "!%dx%d"
//);

$config['static_path'] = DOCROOT.'/statics/';
$config['static_res_url'] = 'http://www.uutuu.com/static/topscontent';

$config['offical_account'] = 'kingbug001@sina';

$config['top_sync']['enabled'] = true;
$config['top_sync']['script'] = '/data/cron/yanzi/dummy_copy.sh %s %s 2>&1';
$config['notshow_nearby_cities'] = 'adelaide,posi,kaiensi,huobate';

$config['dianping']['query_url'] = 'http://api.dianping.com/v1/business/find_businesses';
$config['dianping']['query_url_comment'] = 'http://api.dianping.com/v1/review/get_recent_reviews';
$config['dianping']['query_cities'] = 'http://api.dianping.com/v1/metadata/get_cities_with_businesses';
$config['dianping']['query_categories'] = 'http://api.dianping.com/v1/metadata/get_categories_with_businesses';
$config['dianping']['app_key'] = '4959666240';
$config['dianping']['app_secret'] = '6faf6cb9cf8b4a649e81ebab95c9d3f9';
$config['dianping']['enabled'] = false;

$config['api']['version'] = "1.0.3";