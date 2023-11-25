<?php
if (isset($gateway) && $gateway === 'default') {
  
  // insert new order into database
  if (is_numeric($total_btc)) {
    if (!isset($_GET['ocode'])) {
      $order_id = save_order($account_id, $total_btc, 
	  $shipping, $cart_str, $address, $note, $order_code);
	}
  } else {
    die(LANG('PROB_CALC_TOTAL').' '.LANG('TRY_REFRESHING'));
  }
  
  // check the order was inserted into database
  if (isset($order_id) && is_numeric($order_id)) {
	
	// save order data to session
	$_SESSION["$order_code-data"] = array(
	$total_btc, $buyer, $note, $order_id, 
    $exch_rate, $gateway, time());

	if (!isset($_GET['ocode'])) {
	  // encrypt order data
	  $t_data = bitsci::save_pay_query($_SESSION["$order_code-data"]);
	
	  // save encrypted data to file
	  if (file_put_contents('t_data/'.$order_code, $t_data) !== false) {
	    chmod('t_data/'.$order_code, 0600);
	  } else {
        die(LANG('ERROR_CREATE_TRAN').' '.LANG('TRY_AGAIN_BACK'));
	  }
	}

    // build the URL for the default payment gateway
    $payment_gateway = $site_url.$bitsci_url.'payment.php?t='.$order_code;

    // go to default payment gateway
    redirect($payment_gateway);
  } else {
    die(LANG('ERROR_CREATE_TRAN').' '.LANG('TRY_AGAIN_BACK'));
  }
} else {
  die(LANG('ERROR_CREATE_TRAN').' '.LANG('TRY_AGAIN_BACK'));
}
?>