<?php

/**
 * Class description.
 *
 * $Id: Broadcast.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */
//.//log_uh

class Broadcast
{
	public $driver;
	public $config;
 
	
	public function __construct()
	{
		$config = Kohana::config('broadcast.default');

		$this->config = $config;

		// Set driver name
		$driver = 'Broadcast_'.ucfirst($this->config['driver']).'_Driver';

		// Load the driver
		if ( ! Kohana::auto_load($driver))
			throw new Kohana_Exception('core.driver_not_found', $this->config['driver'], get_class($this));

		// Initialize the driver
		$this->driver = new $driver($this->config['params']);

		// Validate the driver
		if ( ! ($this->driver instanceof Broadcast_Driver))
			throw new Kohana_Exception('core.driver_implements', $this->config['driver'], get_class($this), 'Cache_Driver');

		Kohana::log('debug', 'BroadCast Library initialized');

 
	}
	
 
	
	function send($sender,$receiver,$type,$message ) {
		 $data['sender'] = $sender;
		 $data['receiver'] = $receiver;
		 $data['type'] = $type;
		 $data['message'] = $message;
		 $this->_broadcast(json_encode($data));
		 Kohana::log('debug', "send message from $sender to $receiver [$type]$message success");
	}


	function _broadcast($message) {
 
		 $this->driver->send($message);
 
	}
	

}
?>