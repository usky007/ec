<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Token drivers.
 *
 * $Id: Token.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    socialapi
 * @author     UUTUU Tianium
 * @copyright  (c) 2009-2012 UUTUU
 */
 
/**
 * @ignore
 */
abstract class Token_Driver {
	const OAUTH_TOKEN_KEY = 'OAuth';
	const OAUTH2_TOKEN_KEY = 'OAuth2';
	const BEARER_TOKEN_KEY = 'Bearer';
	const MAC_TOKEN_KEY = 'MAC';
}