<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Authenticate Signature Method drivers.
 *
 * $Id: AuthMethod.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    AuthMethod
 * @author     UUTUU Tianium
 * @copyright  (c) 2009-2012 UUTUU
 */
 
/**
 * @ignore
 */
abstract class SignatureMethod_Driver {
	/**
	 * Algorithm instance factory method.
	 */
	public static function algorithm($name) {
		$driver_name = 'SignatureMethod_'.strtoupper(str_replace('-', '', $name)).'_Driver';
		if (!class_exists($driver_name, false)) {
			return NULL;
		}
		return new $driver_name();
	}
	
	
	abstract public function get_name();
	
	abstract public function sign($base_string, $key);

    public function verify($base_string, $key, $signature) {
        $built = $this->sign($base_string, $$key);
        return $built == $signature;
    }
    
    /**
     * Generate key for signature;
     *
     * @param string $token_type Type of token used for "Authorization" header, support "OAuth" and "MAC".
     * @param array $extra Extra data for key generation, varies according to token type and signature algorithm.
     */
    public function generate_key($token_type, $extra = NULL) {
	    return NULL;
    }
}

/**
 * @ignore
 */
class SignatureMethod_HMACSHA1_Driver extends SignatureMethod_Driver {

    public function get_name() {
        return "HMAC-SHA1";
    }

    public function sign($base_string, $key) {
        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }
    
    public function generate_key($token_type, $request = NULL, $extra = NULL) {
	 	switch ($token_type) {
		 	case "OAuth":
		 		if (!isset($extra[AuthMethod_OAuth_Driver::SHARED_SECRET_KEY])) {
			 		throw new UKohanaException('E_APP_INVALID_PARAMETER', 'core.invalid_parameter','extra',__CLASS__,__FUNCTION__);
		 		}
		 		$key_parts = array ( 
		 			$extra[AuthMethod_OAuth_Driver::SHARED_SECRET_KEY],
		 			isset($extra[AuthMethod_OAuth_Driver::TOKEN_SECRET_KEY]) ? $extra[AuthMethod_OAuth_Driver::TOKEN_SECRET_KEY] : ""
		 		);
		 		$key_parts = social::urlencode($key_parts);
		 		return implode('&', $key_parts);
		 	case "MAC":
		 		if (!isset($extra[AuthMethod_OAuth2_Driver::MACKEY_KEY])) {
			 		throw new UKohanaException('E_APP_INVALID_PARAMETER', 'core.invalid_parameter','extra',__CLASS__,__FUNCTION__);
		 		}
		 		return $extra[AuthMethod_OAuth2_Driver::MACKEY_KEY];
		 	default:
		 		throw new UKohanaException('E_APP_INVALID_PARAMETER', 'core.invalid_parameter','token_type',__CLASS__,__FUNCTION__);
	 	}
    }
}

/**
 * @ignore
 */
class SignatureMethod_PLAINTEXT_Driver extends SignatureMethod_Driver {
    public function get_name() {
        return "PLAINTEXT";
    }

    public function build_signature($base_string, $key) {
        return social::urlencode($key);
    }
    
    public function generate_key($token_type, $request = NULL, $extra = NULL) {
	 	switch ($token_type) {
		 	case "OAuth":
		 		if (!isset($extra[AuthMethod_OAuth_Driver::SHARED_SECRET_KEY])) {
			 		throw new UKohanaException('E_APP_INVALID_PARAMETER', 'core.invalid_parameter','extra',__CLASS__,__FUNCTION__);
		 		}
		 		$key_parts = array ( 
		 			$extra[AuthMethod_OAuth_Driver::SHARED_SECRET_KEY],
		 			isset($extra[AuthMethod_OAuth_Driver::TOKEN_SECRET_KEY]) ? $extra[AuthMethod_OAuth_Driver::TOKEN_SECRET_KEY] : ""
		 		);
		 		$key_parts = social::urlencode($key_parts);
		 		return implode('&', $key_parts);
		 	default:
		 		throw new UKohanaException('E_APP_INVALID_PARAMETER', 'core.invalid_parameter','token_type',__CLASS__,__FUNCTION__);
		 		  
	 	}
    }
}

/**
 * @ignore
 */
class SignatureMethod_RSASHA1_Driver extends SignatureMethod_Driver {
    public function get_name() {
        return "RSA-SHA1";
    }
    
    /**
     * @param string key Certification of server
     */
    public function  sign($base_string, $key) {
        // Pull the private key ID from the certificate
        $privatekeyid = @openssl_get_privatekey($cert);

        // Sign using the key
        $signature = NULL;
        $ok = @openssl_sign($base_string, $signature, $privatekeyid);

        // Release the key resource
        @openssl_free_key($privatekeyid);

        return base64_encode($signature);
    }
    
    /**
     * @param string $key Public key of client.
     */
    public function verify($base_string, $key, $signature) {
    	$decoded_sig = base64_decode($signature);
        
        return @openssl_verify($base_string, $decoded_sig, $$key);
    }
}