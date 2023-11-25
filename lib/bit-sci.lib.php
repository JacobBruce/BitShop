<?php
/**
* Bitcoin SCI class
*
* @author Jacob Bruce
* www.bitfreak.info
*/

// requires AES.php, RSA.php & config.php

if(!function_exists('curl_init')) {
    die('ERROR: cURL is not installed!');
}

class bitsci {

  public static function curl_simple_post($url_str, $ver_ssl=true) {
	
    // Initializing cURL
    $ch = curl_init();
  
    // Setting curl options
    curl_setopt($ch, CURLOPT_URL, $url_str);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ver_ssl);
    curl_setopt($ch, CURLOPT_USERAGENT, "PHP/".phpversion());

    // Getting jSON result string
    $result = curl_exec($ch); 
  
    // close cURL and json file
    curl_close($ch);

    // return cURL result
    return $result;
  
  }
  
  public static function explorer_request($api, $address, $confs, $testnet=false) {
    switch ($api) {
	case 1:
	  if ($testnet) {
	    $url = 'https://sochain.com/api/v2/get_address_balance/BTCTEST/';
	  } else {
	    $url = 'https://sochain.com/api/v2/get_address_balance/BTC/';
	  }
	  $url .= $address.'?confirmations='.$confs;
      $result = self::curl_simple_post($url);
	  $result = json_decode($result, true);
	  if (isset($result['status']) && $result['status'] == 'success') {
	   if (isset($result['data']['confirmed_balance']) && 
	   is_numeric($result['data']['confirmed_balance'])) {
	     return $result['data']['confirmed_balance'];
	   } else {
	     return false;
	   }
	  } else {
	    return false;
	  }
	default:
	  if ($testnet) {
	    $url = 'https://testnet.blockchain.info/q/addressbalance/';
	  } else {
	    $url = 'https://blockchain.info/q/addressbalance/';
	  }
	  $url .= $address.'?confirmations='.$confs;
	  $result = self::curl_simple_post($url);
      if (!empty($result) && is_numeric($result)) {
        return bcdiv($result, '100000000');
      } else {
        return $result;
      }
	}
  }
  
  public static function send_btc_request($addr_str, $confirmations, $main_api=EXP_API) {

    if (defined('USE_TESTNET') && (USE_TESTNET == true)) {

	  $result = self::explorer_request($main_api, $addr_str, $confirmations, true);
	  if ($result === false) {
	    return self::explorer_request($main_api==0?1:0, $addr_str, $confirmations, true);
	  } else {
	    return $result;
	  }
	  
    } else {
	
	  $result = self::explorer_request($main_api, $addr_str, $confirmations);
      if ($result === false) {
        return self::explorer_request($main_api==0?1:0, $addr_str, $confirmations);
      } else {
        return $result;
      }
    }
  }
  
  public static function send_rpc_request($address, $confs, $rpc_client=RPC_CLIENT) {
    switch ($rpc_client) {  
	case 'cryptonited':
	  $rpc_result = $_SESSION[$rpc_client]->listbalances($confs, array($address));
	  $rpc_result = str_replace('ep', '', $rpc_result[0]['balance']);
	  break;
	default:
	  $rpc_result = $_SESSION[$rpc_client]->getreceivedbyaddress($address, $confs);
	  break;
	}
	if ($_SESSION[$rpc_client]->status === 0) {
	  return false;
	} else {
	  if (empty($_SESSION[$rpc_client]->error)) {
	    return $rpc_result;
	  } else {
	    return $_SESSION[$rpc_client]->error;
	  }
	}
  }
 
  public static function get_balance($addr_str, $confirmations, $currency='btc') {
	if ($currency == 'btc') {
	  return self::send_btc_request($addr_str, $confirmations);
	} elseif ($currency == 'alt') {
	  return self::send_rpc_request($addr_str, $confirmations);
	} else{
	  return 'ERROR: unknown currency';
	}
  }
  
  public static function check_payment($price, $addr_str, $confirmations=CONF_NUM, $p_variance=0, $currency='btc') {
  
	$balance = self::get_balance($addr_str, $confirmations, $currency);
	$str_start = explode(' ', $balance);

	if ($balance === false) {
	  return 'e1';
	} elseif ((strpos($balance, 'address') !== false) || (strpos($balance, 'valid') !== false)) {
	  return 'e2';
	} elseif (($str_start[0] === 'ERROR:') || !is_numeric($balance)) {
	  return 'e3';
	} elseif (bccomp($balance, '0') == 1) {
	  if (!valid_balance($balance, $price, $p_variance)) {
	    return 'e4';
	  } else {
	    return $balance;
	  }
	} else {
	  return false;
	}
  }
  
  public static function rsa_encrypt($input_str, $key) {
  
    $rsa = new Crypt_RSA();
 
    $rsa->setPrivateKeyFormat(CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
    $rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_PKCS1);
    $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);

	$public_key = array(
		'n' => new Math_BigInteger($key, 16),
		'e' => new Math_BigInteger('65537', 10)
	);
	
	$rsa->loadKey($public_key, CRYPT_RSA_PUBLIC_FORMAT_RAW);

    return $rsa->encrypt($input_str);	
  }
  
  public static function encrypt_data($input_str, $key=SEC_STR) {
  
    $aes = new Crypt_AES();
    $aes->setKey($key);	

    return $aes->encrypt($input_str);	
  }
  
  public static function decrypt_data($input_str, $key=SEC_STR) {
  
    $aes = new Crypt_AES();
    $aes->setKey($key);	

    return $aes->decrypt($input_str);	
  }
  
  public static function encode_data($inp_str) {
    return base64_encode(self::encrypt_data($inp_str));
  }
  
  public static function decode_data($inp_str) {
    return self::decrypt_data(base64_decode($inp_str));
  }

  public static function read_pay_query($t_data) {

	$td = explode('|', self::decode_data($t_data));

	foreach ($td as $key => $value) {
	  $td[$key] = urldecode($value);
	}

	return $td;
  }

  public static function save_pay_query($td) {

	foreach ($td as $key => $value) {
	  $td[$key] = urlencode($value);
	}

	return self::encode_data(implode('|', $td));
  }
  
  public static function btc_num_format($num, $dec=8, $pow=0, $tsep=SEP_STR, $dsep=DEC_STR) {

    return number_format(bcmul($num,pow(10, $pow)), $dec-$pow, $dsep, $tsep);	
  }
 
  public static function JSONtoAmount($value) {
  
    return round(value * 1e8);
  }
}
?>
