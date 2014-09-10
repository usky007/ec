<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Context
 *
 * Enable context
 */
$config['enable'] = true;

/**
 * Context recognition pattern settings
 */
$config['pattern']['main'] = '{unit}(\.{unit})*';
$config['pattern']['unit'] = '([^.]+)\.([^.]*)'; // pattern for key-value pair extraction.
$config['pattern']['postfix'] = 'htm|xml|jpg|js|css'; // supported file extension

/**
 * Context build template settings
 */
$config['template']['main'] = '{units}.{postfix}';    // pattern for building of context uri string
$config['template']['unit'] = '{key}.{value}';        // pattern for key-value unit
$config['template']['delimiter'] = '.';				  // Delimiter to separate key-value unit
$config['template']['postfix'] = 'htm';				  // Default extension

/**
 * Settings for special keys, supported properties are as following.
 * key: Alias of key.
 * default: Default value if key absent.
 * inherit: Inheritable or not, default true. A Inheritable key will be passed on when call build_uri.
 */
$config['key_settings']['g'] = array ('key' => 'geouri', 'default' => '/');
$config['key_settings']['q'] = array ('key' => 'query');
$config['key_settings']['trace'] = array ('inherit' => false); // ignore in function build_url