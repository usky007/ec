<?php
class Cache_Nginxhttppush_Driver implements Broadcast_Driver {
	private $config;
 
	
	
	public function __construct($config_array)
	{
		$this->config['server_port'] = isset($config_array['server_port'])?$config_array['server_port']:80;
		$this->_requireConfig($config_array,'server_host');
		$this->_requireConfig($config_array,'send_path');
		$this->_requireConfig($config_array,'listen_path');
		$this->_requireConfig($config_array,'channel_arg');
		$this->_requireConfig($config_array,'channel');
 
	}
	
	private function _requireConfig($cfg,$key)
	{
		if(isset($cfg[$key]))
			$this->config[$key] = $cfg[$key];
		else
			throw new Kohana_Exception('core.driver_implements','missing_argument');
	}
	
	public function send($message)
	{
		$config = $this->config;
		$server_host = $config['server_port']=="80"?$config['server_host']: $config['server_host'].":".$config['server_port'];
		$url =  $server_host.'/'.$config['send_path']."?".$config['channel_arg']."=".$config['channel'];
	 
		$header = "POST $url HTTP/1.1\r\n";
		$header .= "Host:$server_host\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Connection: Close\r\n\r\n";
 
		$fp = fsockopen($config['server_host'], $config['server_port']);
		fputs($fp, $header);
		while (!feof($fp)) {
			$str = fgets($fp);
		}
		fclose($fp);
		
		return $str;
	}
}