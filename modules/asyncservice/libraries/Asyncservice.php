<?php
/*
 * Created on 2010-6-23
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class AsyncService {
	//var $logconfig;
	var $con = null;

	function __construct() {
		$this->con = new StompConnection("tcp://" . config::item('log.mq_addr') . ":61613");
		$this->con->connect();
	}
	function __destruct() {
		if (isset($this->con)) {
			$this->con->disconnect();
		}
	}

	public function send_log($recall_url) {
 		return  $this->_send($recall_url,'log');
	}

	private function _send($recall_url,$config_item)
	{
		if (!isset($this->con)) {
			$this->con = new StompConnection("tcp://" . config::item('log.mq_addr') . ":61613");
			$this->con->connect();
		}

		if ( $this->con->socket == null ) {

			Kohana::log("error","conn ".config::item('log.mq_addr')." close");
			return false;
		}

		$con = $this->con;

		$properties = array('persistent' => 'true');

		$message = array("action"=>"post","url"=>$recall_url,"info"=>array());
		$content = $this->makeMessage($message);
 		$def_queue = config::item('async.mq_queue.default',false,null);
		$queue =  config::item("async.mq_queue.$config_item",false,$def_queue);
		if(is_null($queue))
			return false;
		$con->send($queue, $content, $properties); // message 即上一节中定义的消息结构

		if(isset($con->error)) {
			return false;
		} else {
			return true;
		}
	}
	/**
	 * Envoke an async call
	 *
	 * example: $message->async_call($url, $func[, $arg1[, $arg2[, ...]]]);
	 *
	 * @param unknown_type $url
	 * @param mixed $func funcname, array(obj, funcname) or array(class, funcname)
	 * @return unknown
	 */
	public function async_call($url=null, $func , $array) {

		$mq_enable = config::item('log.mq_enable');

		if ($mq_enable['async_queue'])
		{
			//echo $url;
			Kohana::log("debug","do asycn $url")  ;
			return $this->_do_async_call($url);

		}
		else
		{
			Kohana::log("debug","do sycn ".json_encode($func))  ;
			return $this->_do_sync_call($func , $array);
		}
	}
	private function _do_async_call($url)
	{
		return  $this->_send($url,'queue');
	}
	private function _do_sync_call($func , $array)
	{
		//$args = func_get_args();
		//$args = array_slice($args, 2);
		$array = array_values($array);
		//Kohana::log("dubg","func:".json_encode($func)."|para:".json_encode($array));
		return call_user_func_array($func, $array);
	}

	function makeMessage($result) {
		$headers=array();
		if(isset($result['info']))
			$result['info'] = json_encode($result['info']);
		$content = $this->_format_xml_result($result,$headers);
		$content = base64_encode($content);
		return $content;
	}
	private function _format_xml_result($result, &$headers) {

		$headers[] = "Content-Type: text/xml";

		$dom = new DOMDocument('1.0', 'utf-8');

		$root = $dom->createElement("message");
		$dom->appendChild($root);

		$this->_format_xml_result_unit($dom, $root, $result);
		return $dom->saveXML();
	}

	private function _format_xml_result_unit(&$dom, &$parent, $arr) {
		foreach ($arr as $key => $val) {
			if ($key === 0) {
				// $val must be a string, or the array should be optimized first.
				$parent->appendChild($dom->createTextNode($val));
			}
			else if ($key == "_CDATA") {
				$parent->appendChild($dom->createCDATASection($val));
			}
			else if (strpos($key, "@") === 0)
				$parent->setAttribute(substr($key, 1), $val);
			else if (!is_array($val)) {
				$elem = $dom->createElement($key);
				$parent->appendChild($elem);
				$elem->appendChild($dom->createTextNode($val));
			}
			else if (isset($val[1])) {
				foreach ($val as $item)
					$this->_format_xml_result_unit($dom, $parent, array($key => $item));
			}
			else {
				// optimize key=>[val1] to key=>val
				if (isset($val[0]) && is_array($val[0])) {
					$temp_val = $val[0];
					unset($val[0]);
					$val = array_merge($val, $temp_val);
				}
				// associate array
				$sub = $dom->createElement($key);
				$parent->appendChild($sub);
				$this->_format_xml_result_unit($dom, $sub, $val);
			}
		}
	}



	function makeLog($msg, $attributes) {
		$content = "<message ";

		// build attributes
		if (is_array($attributes))
		{
			foreach($attributes as $k => $v)
			{
				if ($k == 'vip')
				continue;
				$content .= "{$k}=\"{$v}\" ";
			}
		}

		$content .= ">";

		// build body
		if(is_array($msg))
		{
			foreach($msg as $k => $v)
			{
				if(!is_numeric($v))
				{
					$content .= "<{$k}><![CDATA[{$v}]]></{$k}>";
				}
				else
				{
					$content .= "<{$k}>{$v}</{$k}>";
				}
			}
		}
		else
		{
			$content .= "<![CDATA[$msg]]>";
		}

		$content .= '</message>';

		//var_dump($content);
		return $content;
	}
}




/**
 *
 * Copyright 2005-2006 The Apache Software Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/* vim: set expandtab tabstop=3 shiftwidth=3: */

//require_once('JSON.php');

/**
 * StompFrames are messages that are sent and received on a StompConnection.
 *
 * @package Stomp
 * @author Hiram Chirino <hiram@hiramchirino.com>
 * @author Dejan Bosanac <dejan@nighttale.net>
 * @version $Revision$
 */
class StompFrame {
	var $command;
	var $headers = array();
	var $body;

	function StompFrame($command = null, $headers=null, $body=null) {
		$this->init($command, $headers, $body);
	}

	function init($command = null, $headers=null, $body=null) {
		$this->command = $command;
		if ($headers != null)
		$this->headers = $headers;
		$this->body = $body;
	}
}

/**
 * Basic text stomp message
 *
 * @package Stomp
 * @author Dejan Bosanac <dejan@nighttale.net>
 * @version $Revision$
 */
class StompMessage extends StompFrame {

	function StompMessage($body, $headers = null) {
		$this->init("SEND", $headers, $body);
	}

}

/**
 * Message that contains a stream of uninterpreted bytes
 *
 * @package Stomp
 * @author Dejan Bosanac <dejan@nighttale.net>
 * @version $Revision$
 */
class BytesMessage extends StompMessage {
	function ByteMessage($body, $headers = null) {
		$this->init("SEND", $headers, $body);
		if ($this->headers == null) {
			$this->headers = array();
		}
		$this->headers['content-length'] = count($body);
	}
}

/**
 * Message that contains a set of name-value pairs
 *
 * @package Stomp
 * @author Dejan Bosanac <dejan@nighttale.net>
 * @version $Revision$
 */
class MapMessage extends StompMessage {

	var $map;

	function MapMessage($msg, $headers = null) {
		if (is_a($msg, "StompFrame")) {
			$this->init($msg->command, $msg->headers, $msg->body);
			$json = new Services_JSON();
			$this->map = $json->decode($msg->body);
		} else {
			$this->init("SEND", $headers, $msg);
			if ($this->headers == null) {
				$this->headers = array();
			}
			$this->headers['amq-msg-type'] = 'MapMessage';
			$json = new Services_JSON();
			$this->body = $json->encode($msg);
		}
	}

}

/**
 * A Stomp Connection
 *
 *
 * @package Stomp
 * @author Hiram Chirino <hiram@hiramchirino.com>
 * @author Dejan Bosanac <dejan@nighttale.net>
 * @version $Revision$
 */
class StompConnection {

	var $socket = null;
	var $hosts = array();
	var $params = array();
	var $subscriptions = array();
	var $defaultPort = 61613;
	var $currentHost = -1;
	var $attempts = 10;
	var $username = '';
	var $password = '';
	// Add at 2009-11-24, for reconnect control
	// If reconnect for a number, then give up reconnecting again
	var $reconNumber = 0;
	var $reconLimit = 3;

	function StompConnection($brokerUri) {
		$ereg = "^(([a-zA-Z]+)://)+\(*([a-zA-Z0-9\.:/i,-]+)\)*\??([a-zA-Z0-9=]*)$";
		if (eregi($ereg, $brokerUri, $regs)) {
			$scheme = $regs[2];
			$hosts = $regs[3];
			$params = $regs[4];
			if ($scheme != "failover") {
				$this->processUrl($brokerUri);
			} else {
				$urls = explode(",", $hosts);
				foreach($urls as $url) {
					$this->processUrl($url);
				}
			}

			if ($params != null) {
				parse_str($params, $this->params);
			}

			$this->makeConnection();

		} else {
			//echo "error";exit;
			trigger_error("Bad Broker URL $brokerUri", E_USER_ERROR);
		}
	}

	function processUrl($url) {
		$parsed = parse_url($url);
		if ($parsed) {
			$scheme = $parsed['scheme'];
			$host = $parsed['host'];
			$port = $parsed['port'];
			array_push($this->hosts, array($parsed['host'], $parsed['port'], $parsed['scheme']));
		} else {
			trigger_error("Bad Broker URL $url", E_USER_ERROR);
		}
	}

	function makeConnection() {
		if (count($this->hosts) == 0) {
			trigger_error("No broker defined", E_USER_ERROR);
		}

		$i = $this->currentHost;
		$att = 0;
		$connected = false;

		while (!$connected && $att++ < $this->attempts) {
			if (isset($this->params['randomize']) && $this->params['randomize'] != null
			&& $this->params['randomize'] == 'true') {
				$i = rand(0, count($this->hosts) - 1);
			} else {
				$i = ($i + 1) % count($this->hosts);
			}

			$broker = $this->hosts[$i];

			$host = $broker[0];
			$port = $broker[1];
			$scheme = $broker[2];
			//trigger_error("connecting to: $scheme://$host:$port");
			if ($port == null) {
				$port = $this->defaultPort;
			}

			if ($this->socket != null) {
				//trigger_error("Closing existing socket");
				fclose($this->socket);
				$this->socket = null;
			}


			while ( $this->socket == null ) {

				$this->socket = @fsockopen($scheme.'://'.$host, $port, $errno, $errstr, 1);


				if ( $this->socket != null ) {
					$this->reconNumber = 0;
					break;
				} else {
					$this->reconNumber++;
				}
				if ( $this->reconNumber == $this->reconLimit ) {
					// Reach the reconnect number
					$this->reconNumber = 0;
					return;
				}

			}

			if ($this->socket == null) {
				return;
				break;
				//trigger_error("Could not connect to $host:$port ({$att}/{$this->attempts})", E_USER_WARNING);
			} else {
				//trigger_error("Connected");
				$connected = true;
				$this->currentHost = $i;
				break;
			}

		}

		if (!$connected) {
			//trigger_error("Could not connect to a broker", E_USER_ERROR);
		}

	}

	function connect($username="", $password="") {
		if ($username != "")
		$this->username = $username;

		if ($password != "")
		$this->password = $password;


		$this->writeFrame( new StompFrame("CONNECT", array("login"=>$this->username, "passcode"=> $this->password ) ) );


		return $this->readFrame();
	}

	function send($destination, $msg, $properties=null) {
		if (is_a($msg, 'StompFrame')) {
			$msg->headers["destination"] = $destination;
			$this->writeFrame($msg);
		} else {
			//Kohana::log("sending '$msg' message to '$destination'");
			//print_r($destination) ;
			$headers = array();
			if( isset($properties) ) {
				foreach ($properties as $name => $value) {
					$headers[$name] = $value;
				}
			}
			$headers["destination"] = $destination ;
			$this->writeFrame( new StompFrame("SEND", $headers, $msg) );
			//@@@trigger_error("'$msg' message sent to '$destination'");
		}
	}

	function subscribe($destination, $properties=null) {
		$headers = array("ack"=>"client");
		if( isset($properties) ) {
			foreach ($properties as $name => $value) {
				$headers[$name] = $value;
			}
		}
		$headers["destination"] = $destination ;
		$this->writeFrame( new StompFrame("SUBSCRIBE", $headers) );
		$this->subscriptions[$destination] = $properties;
	}

	function unsubscribe($destination, $properties=null) {
		$headers = array();
		if( isset($properties) ) {
			foreach ($properties as $name => $value) {
				$headers[$name] = $value;
			}
		}
		$headers["destination"] = $destination ;
		$this->writeFrame( new StompFrame("UNSUBSCRIBE", $headers) );
		unset($this->subscriptions[$destination]);
	}

	function begin($transactionId=null) {
		$headers = array();
		if( isset($transactionId) ) {
			$headers["transaction"] = $transactionId;
		}
		$this->writeFrame( new StompFrame("BEGIN", $headers) );
	}

	function commit($transactionId=null) {
		$headers = array();
		if( isset($transactionId) ) {
			$headers["transaction"] = $transactionId;
		}
		$this->writeFrame( new StompFrame("COMMIT", $headers) );
	}

	function abort($transactionId=null) {
		$headers = array();
		if( isset($transactionId) ) {
			$headers["transaction"] = $transactionId;
		}
		$this->writeFrame( new StompFrame("ABORT", $headers) );
	}

	function ack($message, $transactionId=null) {
		if (is_a($message, 'StompFrame')) {
			$this->writeFrame( new StompFrame("ACK", $message->headers) );
		} else {
			$headers = array();
			if( isset($transactionId) ) {
				$headers["transaction"] = $transactionId;
			}
			$headers["message-id"] = $message ;
			$this->writeFrame( new StompFrame("ACK", $headers) );
		}
	}

	function disconnect() {
		if ( $this->socket != null ) {
			$this->writeFrame( new StompFrame("DISCONNECT") );
			fclose($this->socket);
		}
	}

	private $_fwrite_error_times = 0;


	function writeFrame($stompFrame) {
		if ( $this->socket == null ) {
			return;
		}

		//trigger_error($stompFrame->command);
		$data = $stompFrame->command . "\n";
		if( isset($stompFrame->headers) ) {
			foreach ($stompFrame->headers as $name => $value) {
				$data .= $name . ": " . $value . "\n";
			}
		}
		$data .= "\n";
		if( isset($stompFrame->body) ) {
			$data .= $stompFrame->body;
		}
		$l1 = strlen($data);
		$data .= "\x00\n";
		$l2 = strlen($data);

		$noop = "\x00\n";
		//set_time_limit(5);

		fwrite($this->socket, $noop, strlen($noop));

		$r = fwrite($this->socket, $data, strlen($data));
		if ($r === false || $r == 0) {
			//trigger_error("Could not send stomp frame to server");
			if($this->_fwrite_error_times<=3)
			{
				$this->_fwrite_error_times += 1;
				$this->reconnect();
				$this->writeFrame($stompFrame);
			}
			else
			{
				Kohana::log('error', 'connect to ActiveMQ error 3 times!!!');
				return;
			}
		}
		else
		{
			$_fwrite_error_times = 0;
		}

	}

	function readFrame() {
		if ( $this->socket == null ) {
			return;
		}

		$rc = fread($this->socket, 1);

		if($rc === false) {
			$this->reconnect();
			return $this->readFrame();
		}

		$data = $rc;
		$prev = '';
		// Read until end of frame.
		while (!feof($this->socket)) {
			$rc = fread($this->socket, 1);

			if ($rc === false) {
				$this->reconnect();
				return $this->readFrame();
			}

			$data .= $rc;

			if(ord($rc) == 10 && ord($prev) == 0) {
				break;
			}
			$prev = $rc;
		}
 		//print_r(explode("\n\n", $data)) ;exit;
		list($header, $body) = explode("\n\n", $data, 2);
		$header = explode("\n", $header);
		$headers = array();

		$command = null;

		foreach ($header as $v) {
			if( isset($command) ) {
				list($name, $value) = explode(':', $v, 2);
				$headers[$name]=$value;
			} else {
				$command = $v;
			}
		}

		$frame = new StompFrame($command, $headers, trim($body));
		if (isset($frame->headers['amq-msg-type'])
		&& $frame->headers['amq-msg-type'] == 'MapMessage') {
			return new MapMessage($frame);
		} else {
			return $frame;
		}
	}


	/**
	 * Reconnects and renews subscriptions (if there were any)
	 * Call this method when you detect connection problems
	 */
	function reconnect() {
		$this->makeConnection();
		$this->connect();
		foreach($this->subscriptions as $dest=>$properties) {
			$this->subscribe($dest, $properties);
		}
	}

}

?>