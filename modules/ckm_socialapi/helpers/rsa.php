<?php
/**
 * RSA helper
 *
 * $Id: rsa.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    socialapi
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2012 UUTUU
 */
 class rsa {
	
	public static function generate_rsa_prvkey($bits)
	{
		$prvkey = function_exists("openssl_pkey_new") ? @openssl_pkey_new(array("private_key_bits"=>$bits)) : null;
		return $prvkey ? $prvkey : null;
	}

	public static function get_rsa_cert($prvkey, $notext = true)
	{
		$config = config::item('rsa', false, array());
		$dn = isset($config['dn']) ? $config['dn'] : array();
		$csr = @openssl_csr_new ($dn, $prvkey);
		if ($csr == false)
			return null;

		$sslcsrt = @openssl_csr_sign ($csr, null, $prvkey, 365);
		if ($sslcsrt == false)
			return null;

		$result = array ("csr"=>"", "cert" => "");
		if (@openssl_csr_export($csr, $result["csr"], $notext) and @openssl_x509_export($sslcsrt, $result["cert"], $notext))
			return $result;
		else
			return null;
	}

	public static function get_rsa_prvkeyout($prvkey, $password = null)
	{
		$prvkeyout = null;
		if (!function_exists("openssl_pkey_export") || !@openssl_pkey_export ($prvkey, $prvkeyout, $password))
			return null;
		return $prvkeyout;
	}

	/**
	 * Cert passed in may be regenerated if necessary.
	 */
	public static function get_rsa_pubkey(&$cert, $prvkeyout)
	{
		if (!function_exists("openssl_pkey_get_public"))
			return false;

		$pubkey = false;
		if (!is_null($cert)) {
			$pubkey = @openssl_pkey_get_public($cert);
		}
		if (is_null($cert) || !$pubkey) {
			$prvkey = @openssl_pkey_get_private($prvkeyout);
			if ($prvkey === false)
				return false;
			$cert = self::get_rsa_cert($prvkey);
			if (is_null($cert))
				return false;
		}
		if (!$pubkey)
			$pubkey = @openssl_pkey_get_public($cert);
		return $pubkey;
	}
}
?>