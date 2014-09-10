<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Provides a driver-based interface for creating, updating, deleting and finding
 * system preferences. Preference items are organized by categories.
 *
 * $Id: Preference.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Preference
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Slog {
	protected static $slog = NULL;
	protected $path = array();
	protected $data = array();
	public function __construct()
	{
		if(config::item('slog.enable',false,false)==false)
		{
			$this->enable = false;
			$this->config = array();
		}
		else
		{
			$this->enable = true;
			$this->config = config::item('slog',false,array());
		}
	}

	public static function & instance()
	{
		if ( ! isset(self::$slog))
		{
			// Create a new instance
			self::$slog = new Slog();
		}
		return self::$slog;
	}


	public function add($domain=null,$url=null,$ip=null)
	{
		if(!$this->enable)
			return $this;

		$message = $this->_format($ip,$domain,$url);
		array_push($this->data,$message);
		return $this;
	}

	public function render()
	{
		if(!$this->enable)
			return ;
		foreach($this->data as $msg)
		{
			$filename = $this->_getfilename();
			@file_put_contents($filename, $msg.PHP_EOL, FILE_APPEND);
		}
		return $this;
	}


	private function _getfilename($type="default")
	{
		$cfg = $this->_getTypeConfig($type);

		$filename = $this->_getlogdir($type)."access_".date('Y-m-d').'.log';

		if ( ! is_file($filename))
		{
			// Write the SYSPATH checking header
			file_put_contents($filename,"");
 			/*file_put_contents($filename,
 			'<?php defined(\'SYSPATH\') or die(\'No direct script access.\'); ?>'.PHP_EOL.PHP_EOL);*/

			// Prevent external writes
			chmod($filename, 0644);
		}
 		return $filename;
	}
	private function _getlogdir($type="default")
	{
		$cfg = $this->_getTypeConfig($type);
		$path = $this->path;
		if (empty($path[$type]))
		{
			$dir = $cfg['path'];
			//$dir = realpath($dir);

			if(!file_exists($dir))
			{
				$dir =  str_replace('\\', '/', $dir).'/';
				mkdir($dir,0777);
			}


			if (is_dir($dir) and is_writable($dir))
			{
				$path[$type] = str_replace('\\', '/', $dir).'/';
			}
			else
			{
				// Log directory is invalid
				throw new UKohana_Exception(E_KOHANA  , "errors.slog_dir_unwritable");
			}
		}
		return $path[$type];

	}
	private function _format($ip=null,$domain=null,$url=null)
	{
		$ip = !is_null($ip)? $ip : (isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'0.0.0.0');

		$userobj = Account::instance()->get_user();
		$userstr = $userobj->is_guest()?'GUEST':$userobj->email;
		$domain = !is_null($domain)? $domain.".".$_SERVER['HTTP_HOST'] : $_SERVER['HTTP_HOST'];

		$url = !is_null($url)? $url : (isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'index.php');

		$REDIRECT_STATUS = isset($_SERVER["REDIRECT_STATUS"])?$_SERVER["REDIRECT_STATUS"]:'200';
		$str = $domain." ". $ip;
		$str .= ' - - ['. date('d/M/Y:H:i:s O',$_SERVER['REQUEST_TIME']) .']';
		$str .= ' "'.$_SERVER["REQUEST_METHOD"].' '.$url.' '.$_SERVER["SERVER_PROTOCOL"].'"';
		$str .= ' '.$REDIRECT_STATUS.' 10000';//.$_SERVER["REMOTE_PORT"];
		$str .= ' "-" ';
		$str .= '"'.$userstr.'"';
		return $str;
	}
	private function _getTypeConfig($type)
	{
		$config = $this->config;
		if(!isset($config[$type]))
		{
			return $config['default'];
		}
		return $config[$type];
	}
} // End Cache
?>