<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');
require_once(dirname(__FILE__).'/../inc/session.inc.php');
require_once(dirname(__FILE__)."/../inc/langs/$locale.inc.php");
require_once(dirname(__FILE__)."/../sci/gateways/setup.php");

// check if an old order code was passed to us
if (isset($_GET['ocode'])) {
  $order_code = preg_replace('/[^a-z0-9]/i', '', $_GET['ocode']);
} else {
  $order_code = bin2hex(crypt_random_string(16));
}

// set cookie for recovering transaction
setcookie('ocode', $order_code, time()+172800, '/');

// connect to database
$conn = connect_to_db();

// check if resuming previous order
if (isset($_GET['ocode'])) {

  // get order details from file
  $t_data = file_get_contents(dirname(__FILE__)."/t_data/$order_code");

  // save order details into variables
  if ($t_data !== false) {
    $t_data = bitsci::read_pay_query($t_data);
    list($total_btc, $buyer, $note, $order_id, 
    $exch_rate, $gateway, $order_time) = $t_data;
	// convert total to fiat price
	$total_fiat = bitsci::btc_num_format(bcmul($total_btc, $exch_rate), 2);
	// check if order has expired
    $time_diff = get_time_difference($order_time, time());
    if ($time_diff['hours'] >= $sess_time) {
      die(LANG('ORDER_HAS_EX'));
    }
  } else {
	die(LANG('TRAN_CODE_INVALID').' '.LANG('TRY_AGAIN_BACK'));
  }
  
  // get extra order details from database
  $order = get_order_byid($order_id);
  if (!empty($order) && $order !== 'N/A') {
	$order = mysqli_fetch_assoc($order);
	$cart_str = $order['Cart'];
    $shipping = $order['Shipping'];
    $address = $order['Address'];
    $account_id = $order['AccountID'];
  } else {
	die(LANG('TRAN_CODE_INVALID').' '.LANG('TRY_AGAIN_BACK'));
  }

  $ship_fiat = bitsci::btc_num_format(bcmul($shipping, $exch_rate), 2);
  $subt_fiat = bitsci::btc_num_format(bcsub($total_fiat, $ship_fiat), 2);
}

// check for session errors before proceeding
if (!isset($_GET['ocode']) && (empty($_SESSION['cart']) || 
empty($_SESSION['sum_total']) || !is_numeric($_SESSION['sum_total']) ||
empty($_SESSION['valid_order']) || $_SESSION['valid_order'] == false)) {

  die(LANG('SESSION_RESET').' '.LANG('TRY_AGAIN_BACK'));
  
} else {

  if (!isset($_GET['ocode'])) {
  
    // loop through each item in cart
    foreach ($_SESSION['cart'] as $key => $item) {
	
      // get product details from db	
      $file = get_file(safe_sql_str($item['id']));	
      if (!empty($file) && ($file != 'N/A')) {
        $file = mysqli_fetch_assoc($file);
      } else {
        die(LANG('DATABASE_ERROR').' '.LANG('TRY_REFRESHING'));
      }
	
      // check db data against session data
      if (($file['FilePrice'] != $item['price']) || ($file['FileID'] != $item['id'])) {
        die(LANG('PROB_CALC_PRICE')."\n\n".safe_str($item['name']));
      }
  
      // check stock and update stock number if necessary
      if (($item['type'] !== 'download') && ($item['type'] !== 'keys')) {
	    if ($file['FileStock'] > 0) {
	      $stock_rem = $file['FileStock'] - $item['quant'];
	      if ($stock_rem < 0) {
            die(LANG('APPEARS_WERE_OUT').': '.$item['name'].
		    '<br /><br />'.LANG('STILL_GO_BACK').' '.
		    $file['FileStock'].' '.LANG('ITEMS'));
	      } else {
            edit_file($item['id'], 'FileStock = FileStock-'.$item['quant']);
	      }
	    } else {
          die(LANG('APPEARS_WERE_OUT').': '.$item['name'].
		  '<br /><br />'.LANG('TRY_AGAIN_LATER'));
	    }
      }
    }
	
    // serialize cart (products, quants and vouchers)
    $cart_str = cart_to_str();
  
    // set up variables from session data
	$total_fiat = $_SESSION['sum_fiat'];
    $total_btc = $_SESSION['sum_total'];
	$sub_total = $_SESSION['sub_total'];
    $shipping = $_SESSION['shipping'];
    $address = $_SESSION['address'];
    $account_id = $_SESSION['account'];
    $note = $_SESSION['order_note'];
    $buyer = $_SESSION['buyer_email'];
    $exch_rate = $_SESSION['exch_rate'];
    $gateway = $_SESSION['gateway'];

	$subt_fiat = bitsci::btc_num_format(bcmul($sub_total, $exch_rate), 2);
	$ship_fiat = bitsci::btc_num_format(bcmul($shipping, $exch_rate), 2);
	
    // unset any sensitive session data at this point
    unset($_SESSION['valid_order']);
    unset($_SESSION['exch_rate']);
	unset($_SESSION['sum_fiat']);
    unset($_SESSION['sum_total']);
	unset($_SESSION['sub_total']);
    unset($_SESSION['shipping']);
    unset($_SESSION['vouchers']);
    unset($_SESSION['cart']);
  }
  
  // check if using default gateway
  if ($gateway === 'default') {
    $gateways['default'] = array($use_defgate, LANG('DEFAULT'), 'BTC');
  }
  
  // ensure we're using a valid gateway
  if (array_key_exists($gateway, $gateways)) {
    if ($gateways[$gateway][0]) {
	  // dynamically include custom gateway module
      require_once("./gateways/$gateway/process.php");
	} else {
      die($gateways[$gateway][1].' '.LANG('GATEWAY_DISABLED'));
	}
  } else {
    die(LANG('UNEXPECTED_ERROR').' '.LANG('TRY_AGAIN_BACK'));
  }
}
?>