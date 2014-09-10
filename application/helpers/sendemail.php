<?php
class sendemail
{
	public static function custom($type,$to,$msg=null,$sybject=null)
	{
		if(!in_array($type, Config::item('email.modules')))
		{
			throw new UKohana_Exception('E_API_INVALID_PARAMETER',"errors.invalid_parameter");
		}
		$mail = new sendemail();
		$mail->send(
		$to,
		Config::item('email.'.$type.'.frommail'), 
		Config::item('email.'.$type.'.from'),
		$sybject==null?Config::item('email.'.$type.'.subject'):$sybject, 
		$msg);
	}
	private function send($to,$frommail,$from,$subject,$message)
	{
		header("Content-type: text/html; charset=UTF-8");
		  // 地址也可以使用数组形式：array('to@example.com', 'Name')

		
		$from = '=?UTF-8?B?'.base64_encode($from).'?='; 
		$from = array($frommail,$from);
		
		$subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
		$mes = $message;
		 
		email::send($to, $from, $subject, $mes, TRUE);
	}
}