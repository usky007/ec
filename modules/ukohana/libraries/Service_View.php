<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * View extension for certain format output. API and internal services
 * could use this implementation for output normalization.
 *
 * Notes for xml output.
 * Keys like "@key" will be interpreted as attribute.
 * Keys like "_CDATA" will be interpreted as CDATA text.
 *
 * @package    package_name
 * @author	   maskxu
 * @copyright  (c) 2010 ukohana
 */
class Service_View extends View {
	const KEY_CDATA = "_CDATA";

	protected static $default_format = null;

	protected $format;

	public static function set_default_format($format)
	{
		self::$default_format = $format;
	}

	public static function get_default_format()
	{
		return self::$default_format;
	}

	public function __construct($data = null, $format=null)
	{
		$this->format = $format;

		if ($data instanceof Exception) {
			$this->kohana_local_data = $data;
			return;
		}
		elseif (is_object($data)) {
			$data = get_object_vars($data);
		}
		elseif (!is_null($data) && !is_array($data)) {
			$data = array("data"=>$data);
		}
		parent::__construct(NULL, $data, NULL);
	}

	public function render($print = FALSE, $renderer = FALSE)
	{
		$format = is_null($this->format) ? self::$default_format : $this->format;
		if ($renderer === FALSE)
			$renderer = array($this, "_format_{$format}_result");
		// support format shortcut like "json" and "xml"
		if (is_string($renderer) && !is_callable($renderer))
			$renderer = array($this, "_format_{$renderer}_result");

		$headers = array();
		if (is_callable($renderer)) {
			// can't use call_user_func, reference not supported.
			if (is_array($renderer)) {
				$func = $renderer[1];
				$result = $renderer[0]->$func($this->kohana_local_data, $headers);
			}
			else {
				$result = $renderer($this->kohana_local_data, $headers);
			}
		}
		else
		{
			header("HTTP/1.1 400 Bad Request");
			$result = "Unsupported format \"$format\"";
		}

		if($print)
		{
			foreach ($headers as $header)
				header($header);
			echo $result;
			return;
		}

		return $result;
	}


	public function render_xml($print = FALSE)
	{
 		return $this->render($print, "xml");
	}

	public function render_json($print = FALSE)
	{
		return $this->render($print, "json");
	}

	public function render_xslcsv($print = FALSE)
	{
		return $this->render($print, "xslcsv");
	}

	/////////////////////////////////////private/////////////////////////////////////////
	private function _get_exception_infos($ex) {
		$infos = NULL;
		if ($ex instanceof UKohana_Exception) {
			$infos = $ex->output();
		}
		if ($infos == NULL) {
			$infos = array (
				'message' => $ex->getMessage(),
				'errcode' => $ex->getCode()
			);
		}
		return $infos;
	}
	
	private function _format_json_result($result) {
		//print_r($result);exit;

		if ($result instanceof Exception) {
			$output = array ("success" => false);
			$output = array_merge($output, $this->_get_exception_infos($result));
			return json_encode($output);
		}

		//exit;
		$result['success'] = !isset($result['errcode']);
		$result = preg_replace("/\"@([a-zA-Z_][a-zA-Z0-9_]*)\"/", "\"\\1\"",  json_encode($result));
		return $result;
	}

	private function _format_xml_result($result, &$headers) {

		$headers[] = "Content-Type: text/xml";

		$dom = new DOMDocument('1.0', 'utf-8');

		$root = $dom->createElement("rsp");
		$dom->appendChild($root);
		
		if ($result instanceof Exception) {
			$infos = $this->_get_exception_infos($result);
			$root->setAttribute("stat", "fail");
			foreach ($infos as $field => $info) {
				$root->appendChild($dom->createElement($field, $info));
			}
			return $dom->saveXML();
		}
		
		$root->setAttribute("stat", isset($result['errcode']) ? "fail" : "ok");
		if (count($result) > 1) {
			// remove root attributes
			foreach (array_keys($result) as $key) { 
				if (strpos($key, "@") === 0) {
					$root->setAttribute(substr($key, 1), $result[$key]);
					unset($result[$key]);
				}
			}
		}
		if (count($result) > 1) {
			$result = array("data"=>$result);
		}
		$this->_format_xml_result_unit($dom, $root, $result);
		return $dom->saveXML();
	}

	private function _format_xml_result_unit(&$dom, &$parent, $arr) {
		foreach ($arr as $key => $val) {
			if ($key === 0) {
				// $val must be a string, or the array should be optimized first.
				$parent->appendChild($dom->createTextNode($val));
			}
			else if ($key == self::KEY_CDATA) {
				$parent->appendChild($dom->createCDATASection($val));
			}
			else if (strpos($key, "@") === 0) {
				$parent->setAttribute(substr($key, 1), $val);
			}
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

	private function _format_xslcsv_result($result, &$headers) {
		$headers[] = 'Content-Disposition: attachment; filename="data.csv"';
		$headers[] = 'Accept-Ranges: bytes';
		$headers[] = 'Content-Type: application/x-unknown;charset=gb2312';

		if (!is_array($result) || count($result) == 0)
			return "";

		$row = $result[0];
		$title_arr = array();
		foreach ($row as $k=>$v)
		{
			$title_arr[] = $k;
		}
		$title = implode(",", $title_arr);
		$str = iconv("utf-8", "GBK//IGNORE", $title);
		foreach ($result as $row)
		{
			$line = array();
			foreach ($row as $k=>$v)
			{
				$line[] = '"' . str_replace('"', '""', $v) . '"';
			}
			$str .= base64_decode("DQo=") .
				mb_convert_encoding(implode(",", $line), "GBK", "utf-8");
//			$str .= base64_decode("DQo=") .
//				iconv("utf-8","GBK",implode(",", $line));
		}
		return $str;
	}
}