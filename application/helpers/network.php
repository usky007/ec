<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Network Helper.
 *
 * $Id: network.php 1579 2012-08-13 06:54:36Z Liaodd $
 *
 * @package    package_name
 * @author     UUTUU Liaodd
 * @copyright  (c) 2008-2013 UUTUU
 */
class network {
	
	const PREFERENCE_KEY = 'Network';	

	public static function getImg($url = "", $savepath = "" ) 
	{
		if(file_exists($savepath))
		{
			log::debug('Image file has already exists: '.$savepath);
			return $savepath;
		}

		$cachekey = md5($url);
		$cache =  Preference::instance(self::PREFERENCE_KEY);	
		if($cache->get($cachekey) == NULL)
		{
			$cache->set($cachekey, time());
			log::debug('Download image lock has been enabled: '.$url);
		}
		else
		{
			if((time() - $cache->get($cachekey)) < 60)
			{
				log::debug('Image is downloading: '.$url);
				header('HTTP/1.1 503 Service Temporarily Unavailable');
				return;
			}
			else
			{
				$cache->set($cachekey, time());
				log::debug('Download image lock has been refreshed: '.$url);
			}
		}

	    //去除URL连接上面可能的引号
	    $url = preg_replace( '/(?:^[\'"]+|[\'"\/]+$)/', '', $url );
	    if (!extension_loaded('sockets')) return false;
	    //获取url各相关信息
	    preg_match( '/http:\/\/([^\/\:]+(\:\d{1,5})?)(.*)/i', $url, $matches );
	    //var_export($matches); exit;
	    if (!$matches) return false;
	    $sock = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
	    if ( !@socket_connect( $sock, $matches[1], $matches[2] ? substr($matches[2], 1 ) : 80 ) ) {
	        return false;
	    }
	    //图片的相对地址
	    $msg = 'GET ' . $matches[3] . " HTTP/1.1\r\n";
	    //主机名称
	    $msg .= 'Host: ' . $matches[1] . "\r\n";
	    $msg .= 'Referer: http://ditu.uutuu.com' . "\r\n";
	    $msg .= 'Accept-Language:zh-CN,zh;q=0.8' . "\r\n";
	    $msg .= 'Connection: Close' . "\r\n\r\n";
	    socket_write( $sock, $msg );
	    $bin = '';
	    while ( $tmp = socket_read( $sock, 10 ) ) {
	        $bin .= $tmp;
	        $tmp = '';
	    }
	    $httpp = explode("\r\n", $bin);
	    //var_export($httpp[0]);exit;
	    if(stripos($httpp[0], '200 OK') != false)
	    {
		    $bin = explode("\r\n\r\n", $bin);
		    
		    $img = $bin[1];
		    $h = fopen( $savepath, 'wb' );
		    $res = fwrite( $h, $img ) === false ? false : true;
		    @socket_close( $sock );
		    $cache->delete($cachekey);
			log::debug('Download image lock has been disabled: '.$url);
		    return $savepath;
		}
	   	else
	   	{
		    $cache->delete($cachekey);
			log::debug('Download image lock has been disabled. But remote server response 403: '.$url);
	   		header('HTTP/1.1 503 Service Temporarily Unavailable');
			return;
	   	}
	}

	// public static function getpage($url, $header = array())
	// {
	// 	$header = self::buildheader($header);

	// 	preg_match( '/http:\/\/([^\/\:]+(\:\d{1,5})?)(.*)/i', $url, $matches );
	//     //var_export($matches); exit;
	//     if (!$matches) return false;
	//     $sock = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
	//     if ( !@socket_connect( $sock, $matches[1], $matches[2] ? substr($matches[2], 1 ) : 80 ) ) {
	//         return false;
	//     }
	//     //图片的相对地址
	//     $msg = 'GET ' . $matches[3] . " HTTP/1.1\r\n";
	//     //主机名称
	//     $msg .= 'Host: ' . $matches[1] . "\r\n";
	//     foreach ($header as $key => $value) {
	//     	$msg .= $key.':'.$value.'\r\n';
	//     }
	//     $msg .= 'Connection: Close' . "\r\n\r\n";
	//     socket_write( $sock, $msg );
	//     // $bin = '';
	//     // while ( $tmp = socket_read( $sock, 10 ) ) {
	//     //     $bin .= $tmp;
	//     //     $tmp = '';
	//     // }
	//     // $httpp = explode("\r\n", $bin);

	//     // return $httpp;
	// }

	// private static function buildheader($header = array())
	// {
	// 	if(!isset($header['Accept-Language']))
	// 		$header['Accept-Language'] = 'zh-CN,zh;q=0.8';
	// 	if(!isset($header['Referer']))
	// 		$header['Referer'] = 'http://ditu.uutuu.com';
	// 	return $header;
	// }
}

?>