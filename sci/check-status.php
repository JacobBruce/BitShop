<?php
/**
* Bitcoin Payment Gateway
*
* @author Jacob Bruce
* www.bitfreak.info
*/
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/gateways/setup.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');
require_once(dirname(__FILE__).'/../lib/rpcclient.lib.php');
require_once(dirname(__FILE__).'/../inc/session.inc.php');
require_once(dirname(__FILE__)."/../inc/langs/$locale.inc.php");

if (!empty($_GET['t'])) {

  // connect to database
  $hide_crash = true;
  $conn = connect_to_db();

  // clean GET['t'] and GET['cur']
  $ted = preg_replace('/[^a-z0-9]/i', '', $_GET['t']);
  $cur = preg_replace('/[^a-z0-9]/i', '', $_GET['c']);

  // update some vars based on currency type
  switch ($cur) {
    case 'btc': 
      $pinc = $prog_inc;
	  $currency = 'BTC';
	  $coin_name1 = LANG('BITCOIN');
	  $coin_name2 = strtolower($coin_name1);
	  $confs_req = $confirm_num;
	  break;
    case 'alt': 
      $pinc = $alt_pinc;
	  $currency = $altcoin_code;
	  $coin_name1 = $altcoin_name;
	  $coin_name2 = strtolower($coin_name1);
	  $confs_req = $alt_confirms;
	  break;
    default: 
      die(LANG('INVALID_ACTION'));
  }

  // get t_data from file if session expired
  $t_data = get_order_data($ted);
  if ($t_data === false) { die('0:'.LANG('TRAN_NOT_FOUND')); }

  // save the transaction data to individual variables
  list($btc_total, $buyer, $note, $order_id, 
  $exch_rate, $gateway, $order_time) = $t_data;
  $fiat_total = bitsci::btc_num_format(bcmul($exch_rate,$btc_total), 2);
  
  // get key data from file
  $key_data = file_get_contents("backup/$cur-$ted");
  if ($key_data !== false) {
    $key_data = explode(':', $key_data);
    $pub_add = $key_data[1];
  } else {
    die('0:'.LANG('TRAN_CODE_INVALID'));
  }
  
  // check if order has expired
  $time_diff = get_time_difference($order_time, time());
  if ($time_diff['minutes'] >= $order_expire_time) {
    if ($_SESSION[$pub_add.'-status'] !== 'Expired') {
      set_order_status($order_id, 'Expired/Invalid');
	  $_SESSION[$pub_add.'-status'] = 'Expired';
	}
    die('0:'.LANG('ORDER_EXPIRED'));
  }
  
  // check if IP has changed
  if (empty($_SESSION['ip_hash'])) {
    $_SESSION['ip_hash'] = get_ip_hash();
  } elseif ($_SESSION['ip_hash'] !== get_ip_hash()) {
	if ($_SESSION['client_type'] != 'tcon') {
	  die('0:'.LANG('IP_CHANGED'));
	}
  }

  // reset or increase the progress
  if (!isset($_SESSION[$pub_add.'-confirms'])) {
    $first = ($cur=='btc') ? 0 : 1;
    $_SESSION[$pub_add.'-confirms'] = $first;
	$_SESSION[$pub_add.'-progress'] = 0;
  }
  
  // set initial order status
  if (!isset($_SESSION[$pub_add.'-status'])) {
    $_SESSION[$pub_add.'-status'] = 'Scanning';
  }
  
  // get real amount required
  if ($cur === 'btc') {
	$amount = trim_decimal($btc_total, $p_precision);
  } else {
    if ($use_altrpc) {
      $amount = $key_data[0];
	} else {
	  die('0:'.LANG('INVALID_ACTION'));
	}
  }
  
  // make sure we have a valid price amount
  if (!is_numeric($amount) || bccomp($amount, '0') != 1) {
    die('0:'.LANG('PROB_CALC_TOTAL'));
  }
  
  // check if the payment has been received
  $amount_paid = bitsci::check_payment($amount, $pub_add,
  $_SESSION[$pub_add.'-confirms'], $p_variance, $cur);

  if ($amount_paid === false) {
  
	// the payment isn't confirmed yet
    $_SESSION[$pub_add.'-confirms']--;
    $payment_status = LANG('AWAITING_PAYMENT');
	
  } elseif ($amount_paid === 'e1') {
	
	// we have no working API's...
    $_SESSION[$pub_add.'-confirms']--;
    $payment_status = LANG('APIS_UNAVAILABLE');
	
  } elseif ($amount_paid === 'e2') {
	
	// this really shouldn't happen...
    $_SESSION[$pub_add.'-confirms']--;
    $payment_status = LANG('CORRUPT_ADDRESS');
	
  } elseif ($amount_paid === 'e3') {
	
	// something weird happened...
    $_SESSION[$pub_add.'-confirms']--;
    $payment_status = LANG('UNKNOWN_ERROR');
	
  } elseif ($amount_paid === 'e4') {
	
	// not enough funds sent yet...
    $_SESSION[$pub_add.'-confirms']--;
    $payment_status = LANG('PARTIAL_PAYMENT');
	
  } else {
  
    // if tx is confirmed run the ipn script
    if ($_SESSION[$pub_add.'-confirms'] >= $confs_req)  {
	  $tran_id = strtoupper(substr(rand_str(), 0, 16));
	  require('ipn-control.php');
	  ($error === false) ? die('100:confirmed') : die("0:$error");
    } else {
	  $payment_status = LANG('CONFIRMING_PAYMENT');
    }
  }
  
  // update order status in db if necessary
  if ($payment_status === LANG('PARTIAL_PAYMENT') &&
  ($_SESSION[$pub_add.'-status'] !== 'Underpaid')) {
    set_order_status($order_id, 'Underpaid');
	$_SESSION[$pub_add.'-status'] = 'Underpaid';
  } elseif ($payment_status === LANG('CONFIRMING_PAYMENT') &&
  ($_SESSION[$pub_add.'-status'] !== 'Payment Pending')) {
    set_order_status($order_id, 'Payment Pending');
	$_SESSION[$pub_add.'-status'] = 'Payment Pending';
  }
  
  // stuff below is to make the percentage bar behave nicely
  if ($_SESSION[$pub_add.'-confirms'] < 0) {
    $real_confs = 0;
  } else {
    $real_confs = $_SESSION[$pub_add.'-confirms'];
  }
  
  if ($confs_req > 0) {
    $perc_prog = ($real_confs / $confs_req) * 100;
    $half_prog = (($real_confs + 0.5) / $confs_req) * 100;
    $next_prog = (($real_confs + 1) / $confs_req) * 100; 
  } else {
    $perc_prog = $real_confs * 100;
    $half_prog = ($real_confs + 0.5) * 100;
    $next_prog = ($real_confs + 1) * 100; 
  }
  
  if (($_SESSION[$pub_add.'-progress'] < $perc_prog) && ($perc_prog > 0)) {
    $_SESSION[$pub_add.'-progress'] = round($perc_prog);
  } elseif ($_SESSION[$pub_add.'-progress'] >= 95) {
    if ($perc_prog < 100) {
	  $_SESSION[$pub_add.'-progress'] = round($half_prog);
	}
  } elseif ($_SESSION[$pub_add.'-progress'] > $next_prog) {
    $_SESSION[$pub_add.'-progress'] -= $pinc;
  }
  
  if ($_SESSION[$pub_add.'-progress'] >= 100) {
    if ($perc_prog >= 100) {
      $_SESSION[$pub_add.'-progress'] = 100;
	} else {
      $_SESSION[$pub_add.'-progress'] = 99;
	}
  }

  // output percentage integer and status string 
  echo $_SESSION[$pub_add.'-progress'].':'.$payment_status;
  
  // increment progress and confirms
  $_SESSION[$pub_add.'-progress'] += $pinc;
  $_SESSION[$pub_add.'-confirms']++;
  
  // should prevent weird stuff from happening
  if ($_SESSION[$pub_add.'-confirms'] < 0) {
    $_SESSION[$pub_add.'-confirms'] = 0;
  }
}
?>
