<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['upload_path'] = "";       //The path to the folder where the upload should be placed.
$config['allowed_types'] = 'gif|jpg|png|jpeg|flv'; //The mime types corresponding to the types of files you allow to be uploaded.
$config['max_size'] = 102400;                   //The maximum size (in kilobytes) that the file can be.
$config['max_width'] = 1024000;               //The maximum width (in pixels) that the file can be.
$config['max_height'] = 768000;               //The maximum height (in pixels) that the file can be.
$config['overwrite'] = FALSE;               //If set to true, if a file with the same name as the one you are uploading exists, it will be overwritten.
$config['encrypt_name'] = TRUE;             //If set to TRUE the file name will be converted to a random encrypted string.
$config['remove_spaces'] = TRUE;            //If set to TRUE, any spaces in the file name will be converted to underscores.
 
$config['allowed_image_types'] = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG,IMAGETYPE_BMP);

$config['advance_storage'] = "on";
$config['local_storage_keyword'] = "local";
$config['upload_date_pattern'] = 'ymd';
$config['old_upload_directory'] = '%s/';
 



$config['storage_path']['default'] =  '/data/vhosts/yanzi/res/upimg/save/';
$config['storage_path']['save'] =  '/data/vhosts/yanzi/res/upimg/save/';
$config['storage_path']['revision'] =  '/data/vhosts/yanzi/res/upimg/revision/';
$config['local_storage_base']['default'] ="http://test.stay.com/res/upimg/save/";
$config['local_storage_base']['save'] =   'http://test.stay.com/res/upimg/save/';
$config['local_storage_base']['revision'] =   'http://test.stay.com/res/upimg/revision/';

 
$config['max_image_size'] = 40000000;
 