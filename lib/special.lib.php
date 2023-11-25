<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function LANG($in_txt, $repeat=1, $between='') {
  $lang_str = '';
  for ($i=0;$i<$repeat;$i++) {
    $lang_str .= $GLOBALS['TEXT'][$in_txt].$between;
  }
  return safe_str(trim($lang_str));
}

function RAW_LANG($in_txt) {
  return trim($GLOBALS['TEXT'][$in_txt]);
}

function format_time($time_str) {
  return date('Y-m-d H:i:s T', strtotime($time_str.' UTC'));
}

function session_expired($sess_time) {
  if (empty($_SESSION['timeout'])) {
    $_SESSION['timeout'] = time();
    return false;
  } else {
    $time_diff = get_time_difference($_SESSION['timeout'], time());
    if (($time_diff == false) || ($time_diff['hours'] >= $sess_time)) {
      return true; 
    } else {
      return false;
    }
  }
}

function ip_state() {
  $state = 'valid';
  $remote_ip = get_remote_ip();
  if ($_SESSION['lock_ip']) {
    if (validate_ip($remote_ip)) {
      if ($_SESSION['client_type'] == 'ncon') {
        if ($_SESSION['ip_address'] !== $remote_ip) {
          $state = 'login';
        }
	  }
    } else {
	  $state = 'login';
	}
  }
  return $state;
}

function get_perms($perm_group) {
  global $group_perms;
  if (isset($group_perms[$perm_group])) {
	return $group_perms[$perm_group];
  } else {
    return false;
  }
}

function login_state() {
  if (isset($_SESSION['user_data']) && ip_state() === 'valid') {
    return 'valid';
  } else {
    return 'login';
  }
}

function admin_valid($die=true, $inc=true) {
  if (login_state() === 'valid') {
    if (!$inc || isset($GLOBALS['admin_call'])) {
	  if ($_SESSION['user_perms']['admin_access']) {
        return true;
	  }
    }
  }
  if ($die) {
    die(LANG('INVALID_ACCESS'));
  } else {
    return false;
  }
}

function get_img_ext($pic_url) {
  if (file_exists($pic_url.'.jpg')) {
    return '.jpg';
  } elseif (file_exists($pic_url.'.bmp')) {
    return '.bmp';
  } elseif (file_exists($pic_url.'.png')) {
    return '.png';
  } elseif (file_exists($pic_url.'.gif')) {
    return '.gif';
  } else {
    return '';
  }
}

function get_rating($item) {
  return round($item['FileVoteSum'] / $item['FileVoteNum'], 2);
}

function get_email_address($acc_id) {
  $account = get_account_byid($acc_id);
  if (!empty($account) && $account !== 'N/A') {
    $account = mysqli_fetch_assoc($account);
	return $account['Email'];
  }
}

function list_cart_items($cart_str, $safe=true) {

  $item_list = array();
  $cart_arr = explode('|', $cart_str);
  
  if (!empty($cart_arr[0])) {
    $items_arr = explode(',', $cart_arr[0]);
  } else {
    return $item_list;
  }
  
  foreach ($items_arr as $key => $item_dat) {

    if (empty($item_dat)) { continue; }
    list($item_id, $quantity) = explode(':', $item_dat);
	$item = get_file($item_id);
	
	if (!empty($item) && $item !== 'N/A') {
	  $item = mysqli_fetch_assoc($item);
	  if ($safe === 'links') {
	    $item_name = "<a href='./admin.php?page=items&amp;".
		"action=edit&amp;fid=$item_id'>".safe_str($item['FileName']).'</a>';
	  } elseif ($safe) {
	    $item_name = safe_str($item['FileName']);
	  } else {
	    $item_name = $item['FileName'];
	  }
	  if ($safe) {
	    $item_list[] = "$quantity &times; $item_name";
	  } else {
	    $item_list[] = "$quantity x $item_name";
	  }
	}
  }
  
  return $item_list;
}

function list_cart_vouchs($cart_str, $safe=true) {

  $vouch_list = array();
  $cart_arr = explode('|', $cart_str);
  
  if (!empty($cart_arr[1])) {
    $vouch_arr = explode(',', $cart_arr[1]);
  } else {
    return $vouch_list;
  }
  
  foreach ($vouch_arr as $key => $vouch_id) {

    if (empty($vouch_id)) { continue; }
	$vouch = get_voucher_byid($vouch_id);
	
	if (!empty($vouch) && $vouch !== 'N/A') {
	  $vouch = mysqli_fetch_assoc($vouch);
	  if ($safe === 'links') {
	    $vouch_code = "<a href='./admin.php?page=vouchers&amp;".
		"id=$vouch_id'>".safe_str($vouch['Name']).'</a>';
	  } elseif ($safe) {
	    $vouch_code = safe_str($vouch['Name']);
	  } else {
	    $vouch_code = $vouch['Name'];
	  }
	  $vouch_list[] = $vouch_code;
	}
  }
  
  return $vouch_list;
}

function rfc1342b($str) {
  if (preg_match('/[^\x20-\x7E]/', $str)) {
    return '=?utf-8?B?'.base64_encode($str).'?=';
  } else {
    return $str;
  }
}

function get_mail_headers($reply_email=false, $from_email=false) {
  $reply_email = ($reply_email===false) ? 'noreply@'.$_SERVER['SERVER_NAME'] : $reply_email;
  $from_email = ($from_email===false) ? 'noreply@'.$_SERVER['SERVER_NAME'] : $from_email;
  return "From: $from_email\r\nReply-To: $reply_email\r\nMIME-Version: 1.0\r\n".
  "Content-type: text/plain; charset=utf-8\r\nX-Mailer: PHP/".phpversion();
}

function send_smtp_email($to_email, $subject, $body, $reply_email=false, $from_email=false, $is_html=false) {
  
  $reply_email = ($reply_email===false) ? 'noreply@'.$_SERVER['SERVER_NAME'] : $reply_email;
  $from_email = ($from_email===false) ? 'noreply@'.$_SERVER['SERVER_NAME'] : $from_email;

  try {
    $mail = new PHPMailer;

    $mail->isSMTP();
    $mail->SMTPDebug = $GLOBALS['smtp_debug'];
    $mail->SMTPAuth = $GLOBALS['smtp_auth'];
    $mail->SMTPSecure = $GLOBALS['smtp_meth'];
    $mail->Host = $GLOBALS['smtp_host'];
    $mail->Port = $GLOBALS['smtp_port'];
    $mail->Username = $GLOBALS['smtp_user'];
    $mail->Password = $GLOBALS['smtp_pass'];

    $mail->setFrom($from_email);
    $mail->addAddress($to_email);
    $mail->addReplyTo($reply_email);

    $mail->isHTML($is_html); 
    $mail->Subject = $subject;
    $mail->Body = $body;

    if(!$mail->send()) {
	  if ($GLOBALS['smtp_debug'] > 0) {
	    echo '<p class="error_txt">ERROR: '.$mail->ErrorInfo.'<p>';
	  }
	  return false;
    } else {
      return true;
    }
  } catch (phpmailerException $e) {
    if ($GLOBALS['smtp_debug'] > 0) {
	  echo '<p class="error_txt">ERROR: '.$e->errorMessage().'<p>';
	}
	return false;
  } catch (Exception $e) {
    if ($GLOBALS['smtp_debug'] > 0) {
	  echo '<p class="error_txt">ERROR: '.$e->getMessage().'<p>';
	}
	return false;
  }
}

function send_confirm_email($order, $seller) {

  // set location of email template file
  $tpl_dir = dirname(__FILE__)."/../inc/email_body.inc";

  // get user defined email template
  $body = file_get_contents($tpl_dir);
  
  // get buyer email address from db
  $to = get_email_address($order['AccountID']);
  
  // get payment destination
  $key_data = explode(':', $order['KeyData']);
  $destination = $key_data[1];
  
  // create item list from cart data
  $item_list = implode("\n", list_cart_items($order['Cart'], false));
  
  // get the real amount paid by buyer
  $amount_paid = $order['Amount'].' '.$order['Currency'];
  
  // get time the transaction was paid
  $date_paid = format_time($order['DatePaid']);
	
  // replace place holders with actual values
  $body = str_replace('SELLER_NAME', $seller, $body);
  $body = str_replace('TRAN_CODE', $order['TranCode'], $body);
  $body = str_replace('TOTAL_PAID', $amount_paid, $body);
  $body = str_replace('DATE_PAID', $date_paid, $body);
  $body = str_replace('DESTINATION', $destination, $body);
  $body = str_replace('ITEM_LIST', $item_list, $body);
  
  // send email to buyer
  if ($GLOBALS['smtp_enable']) {
    $subject = "$seller: ".RAW_LANG('TRAN_CONFIRMED');
    if (send_smtp_email($to, $subject, $body) === true) {
	  return true;
    } else {
	  return false;
    }
  } else {
    $subject = rfc1342b("$seller: ".RAW_LANG('TRAN_CONFIRMED'));
    if (mail($to, $subject, $body, get_mail_headers())) {
	  return true;
    } else {
	  return false;
    }
  }
}

function lock_ip_check($code_string, $ip_hash, $hit_limit=5, $lock_time=7) {
  $file_dir = "uploads/down_logs/$code_string.log";
  $time_now = time();
  if (file_exists($file_dir)) {
    $log_data = json_decode(file_get_contents($file_dir), true);
	if ($log_data['lock_state'] <> 0) {
	  $time_diff = get_time_difference($log_data['last_reset'], $time_now);
	  $lock_time = round($lock_time * $log_data['lock_count']);
	  if ($time_diff['days'] >= $lock_time) {
	    $log_data['last_reset'] = $time_now;
		$log_data['last_ip'] = $ip_hash;
		$log_data['hit_count'] = 1;
		$log_data['lock_state'] = 0;
		file_put_contents($file_dir, json_encode($log_data));
		return 0;
	  } else {
	    return $lock_time - $time_diff['days'];
	  }
	} else {
	  if ($log_data['last_ip'] != $ip_hash) {
	    $log_data['last_ip'] = $ip_hash;
	    $log_data['hit_count']++;
		if ($log_data['hit_count'] >= $hit_limit) {
		  $time_diff = get_time_difference($log_data['last_reset'], $time_now);
		  if ($time_diff['days'] < 5) {
		    $log_data['lock_state'] = 1;
			$log_data['lock_count']++;
			$result = round($lock_time * $log_data['lock_count']);
		  } else {
			$result = 0;
		  }
		  $log_data['hit_count'] = 0;
		  $log_data['last_reset'] = $time_now;
		} else {
		  $result = 0;
		}
		file_put_contents($file_dir, json_encode($log_data));
		return $result;
	  } else {
	    return 0;
	  }
	}
  } else {
    $log_data = array(
	  'last_reset' => $time_now,
	  'last_ip' => $ip_hash,
	  'hit_count' => 1,
	  'lock_count' => 0,
	  'lock_state' => 0
	);
    file_put_contents($file_dir, json_encode($log_data));
	return 0;
  }
}

function update_config($config_targ, $new_config, $dir='') {
  
  try {
    if ($config_targ == 'main') {
      $file_targ = dirname(__FILE__).'/../inc/config.inc.php';
    } elseif ($config_targ == 'sci') {
      $file_targ = dirname(__FILE__).'/../sci/config.php';
    } elseif ($config_targ == 'gate') {
      $file_targ = dirname(__FILE__)."/../sci/gateways/$dir/config.php";
    }
  
    $config_file = file_get_contents($file_targ, true);
	$config_file = str_replace("\r\n", "\n", $config_file);
    $config_array = explode("\n", $config_file);
  
    foreach ($new_config as $key1 => $value1) {
      foreach ($config_array as $key2 => $value2) {
	    if (strpos($value2, '$'.$key1.' = ') !== false) {
	      $new_val = str_replace("'", "\'", $value1);
	      if (is_string($GLOBALS[$key1])) {
	        $new_val = "'$new_val'";
	      }
	      $config_array[$key2] = '$'.$key1.' = '.$new_val.';';
	    }
	  }
    }
  
    $config_file = implode("\r\n", $config_array);
    if (file_put_contents($file_targ, $config_file)) {
	  return true;
	} else {
	  return false;
	}

  } catch (Exception $e) {
    return false;
  }
}

function update_stock($order) {	  
  if ($order['Status'] !== 'Confirmed') {
	  
	list($items_str, $vouch_str) = explode('|', $order['Cart']);
	$items_arr = explode(',', $items_str);
	
	foreach ($items_arr as $key => $item_dat) {

	  if (empty($item_dat)) { continue; }
	  list($item_id, $quantity) = explode(':', $item_dat);  
	  $item = get_file($item_id);
	
	  if (!empty($item) && $item !== 'N/A') {
		$item = mysqli_fetch_assoc($item);
		if (($item['FileMethod'] !== 'download') && ($item['FileMethod'] !== 'keys')) {	  
		  edit_file($item_id, "FileStock = FileStock + $quantity");
		}
	  }
	}
  }
}

function get_order_data($code) {
  if (empty($_SESSION["$code-data"])) {
    if (file_exists('t_data/'.$code)) {
      $t_data = file_get_contents('t_data/'.$code);
      if ($t_data !== false) {
		return bitsci::read_pay_query($t_data);
      } else {
        return false;
      }	  
    } else {
      return false;
    }
  } else {
    return $_SESSION["$code-data"];
  }
}

function get_key_data($dir, $code) {
  $result = file_get_contents($dir.$code);
  if (empty($result)) {
    return false;
  } else {
    return $result;
  }
}

function shipping_req() {
  foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['type'] === 'ship') {
	  return true;
	}
  }
  return false;
}

function get_ship_info($item_arr, $curr) {
  global $global_shipping;
  global $weight_mult;
  $result = array('cost' => '0.0', 'curr' => $curr);
  $ship_arr = explode(':', $item_arr['FileType']);
  switch ($ship_arr[0]) {
    case 'fiat': $result['cost'] = $ship_arr[1]; break;
    case 'global': $result['cost'] = $global_shipping; break;
    case 'weight': $result['cost'] = bcmul($weight_mult, $ship_arr[1]); break;
    default: $result['cost'] = $ship_arr[1]; $result['curr'] = 'BTC'; break;
  }
  return $result;
}

function get_btc_price($price, $erate) {
  if ($price < 0) {
    return abs($price);
  } else {
    return bcdiv($price, $erate);
  }
}

function get_fiat_price($price, $erate, $orate) {
  if ($price > 0) {
    if (bccomp($erate, $orate) === 0) {
	  return $price;
	} else {
      $btc_val = bcdiv($price, $orate);
	  return bcmul($btc_val, $erate);
	}
  } else {
	return bcmul(abs($price), $erate);
  }
}

function cart_to_str() {
  $result = '';
  foreach ($_SESSION['cart'] as $key => $item) {
   $result .= $item['id'].':'.$item['quant'].',';
  }
  $result = rtrim($result, ',').'|';
  foreach ($_SESSION['vouchers'] as $key => $vouch) {
   $result .= $vouch['id'].',';
  }
  return rtrim($result, ',');
}

function cart_items($cart_str) {

  $item_list = array();
  $cart_arr = explode('|', $cart_str);
  
  if (!empty($cart_arr[0])) {
    $items_arr = explode(',', $cart_arr[0]);
  } else {
    return $item_list;
  }
  
  foreach ($items_arr as $key => $item_dat) {
    if (empty($item_dat)) { continue; }
    list($item_id, $quantity) = explode(':', $item_dat);
	$item_list[$item_id] = $quantity;
  }
  
  return $item_list;
}

function item_from_cart($item_id) {
  foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] == $item_id) {
	  return $item;
	}
  }
  return false;
}

function physical_order() {
  foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['type'] === 'ship') {
	  return true;
	}
  }
  return false;
}

function manual_delivery_req() {
  if (physical_order()) { return true; }
  foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['type'] === 'email') {
	  return true;
	}
  }
  return false;
}

function unset_voucher($vouch_id) {
  foreach ($_SESSION['vouchers'] as $key => $vouch) {
    if ($vouch_id == $vouch['id']) {
	  unset($_SESSION['vouchers'][$key]);
	  return true;
	}
  }
  return false;
}

function dupe_voucher($voucher) {
  foreach ($_SESSION['vouchers'] as $key => $vouch) {
    if ($voucher['VouchID'] == $vouch['id']) {
	  return true;
	}
  }
  return false;
}

function check_voucher($voucher) {
  if ($voucher['Enabled'] == true) {
	if ($voucher['ItemID'] > 0) {
	  if (item_from_cart($voucher['ItemID'])) {
		return true;
	  }
	} else {
	  return true;
	}
  }
  return false;
}

function validate_vouchers() {
  $result = true;
  foreach ($_SESSION['vouchers'] as $key => $vouch) {
    $voucher = get_voucher_byid($vouch['id']);
	if (!empty($voucher) && $voucher !== 'N/A') {
	  $voucher = mysqli_fetch_assoc($voucher);
	  if (!check_voucher($voucher)) {
		unset($_SESSION['vouchers'][$key]);
		$result = false;
	  }
	} else {
	  unset($_SESSION['vouchers'][$key]);
	  $result = false;
	}
  }
  return $result;
}

function alt_btc_pair($btc_val, $api_url) {
  $alt_parr = json_decode(bitsci::curl_simple_post($api_url));
  if (!empty($alt_parr) && bccomp($alt_parr->BTC, '0') == 1) {
	return bcdiv($btc_val, $alt_parr->BTC);
  } else {
	return false;
  }
}

function valid_balance($amount, $total, $pvar) {
  if (bccomp(bcadd($amount, $pvar), $total) == -1) {
    return false;
  } else {
    return true;
  }
}

function item_box_html($item, $admin=false, $cat_id=0) {
  global $exch_orig;
  global $exch_rate;
  global $dec_shift;
  global $curr_symbol;
  global $curr_code;
  global $dec_unit;
  $sold_out = '';
  
  if ($item['FileMethod'] !== 'download' && $item['FileMethod'] !== 'keys') {
	if ($item['FileStock'] < 1) {
	  $sold_out = '<span class="badge badge-important">'.LANG('SOLD_OUT').'</span>';
	}
  }
	
  if (strlen($item['FileName']) > 18) {
	$item_name = safe_str($item['FileName']);
	$short_name = str_replace(' ', '&nbsp;', safe_str(substr($item['FileName'], 0, 18).'...'));
  } else {
	$item_name = safe_str($item['FileName']);
	$short_name = $item_name;
  }

  $btc_price = get_btc_price($item['FilePrice'], $exch_orig);
  $fiat_price = get_fiat_price($item['FilePrice'], $exch_rate, $exch_orig);

  $btc_price = bitsci::btc_num_format($btc_price, 8, $dec_shift);
  $fiat_price = bitsci::btc_num_format($fiat_price, 2);
  
  if ($admin) {
    $item_url = "admin.php?page=items&amp;action=edit&amp;fid=".$item['FileID'];
  } else {
    if ($cat_id > 0) {
	  $cat_ids = explode(',', $item['FileCat']);
	  $cat_arg = ($cat_ids[0] == $cat_id) ? '' : "&amp;cat=$cat_id";
	  $item_url = "index.php?page=item$cat_arg&amp;id=".$item['FileID'];
	} else {
      $item_url = "index.php?page=item&amp;id=".$item['FileID'];
	}
  }
  $pic_url = 'pics/'.$item['FileID'].'/preview';
  $img_ext = get_img_ext($pic_url);
  $img_src = $pic_url.$img_ext;
	
  if (!file_exists($img_src)) {
	$img_src = 'img/no-image.png';
  }
	
  return "<div class='thumbnail item_box'><div class='img_box'><a class='ibox_link' ".
  "href='$item_url' title='$item_name'><img src='$img_src' alt='".LANG('LOADING_IMG').
  "' class='ibox_image' /></a></div><a class='iname_link' href='$item_url' title=".
  "'$item_name'>".$short_name."</a><p>$btc_price&nbsp;".$dec_unit."BTC<br />".
  "<span class='fiat_price'>".safe_str($curr_symbol)."$fiat_price ".
  safe_str($curr_code)."</span> $sold_out</p></div>\n";
}

function generate_codes($item, $quant, $order) {

  $item_id = $item['FileID']; 
  $acc_id = $order['AccountID'];
  $ord_id = $order['OrderID'];

  if ($item['FileMethod'] === 'keys' || $item['FileMethod'] === 'download') {
  
	for ($i=0;$i<$quant;$i++) {
	  $key_dat = strtoupper(md5($i.$order['Code'].$item_id));
	  if (!insert_code($key_dat, $item_id, $acc_id, $ord_id)) {
	    return false;
	  }
	}

  } elseif ($item['FileMethod'] === 'codes') {
  
	for ($i=0;$i<$quant;$i++) {
	  if (!claim_code($item_id, $acc_id, $ord_id)) {
	    return false;
	  }
	}
	
  }
}

function address_string($address) {
  if (!empty($address['Address'])) {
    return $address['Address']."\n".
	$address['Suburb'].', '.$address['State'].', '.
	$address['Zipcode']."\n".$address['Country'];
  } else {
    return 'n/a';
  }
}
?>
