<?php
require_once('./../lib/paypal.lib.php');

//use PaypalIPN;
$paypal = new PayPal();
if ($debug_paypal) $paypal->useSandbox();
	
if (isset($gateway) && $gateway === 'paypal') {

  // generate gateway return and callback URLs
  $return_url = $site_url.'?page=return&result=success&delay=2&code='.$order_code;
  $cancel_url = $site_url.'?page=return&result=cancel&code='.$order_code;
  $callback_url = $site_url.'sci/gateways/paypal/callback.php?s='.$paypal_call_secret;

  // insert new order into database
  if (is_numeric($total_btc)) {
    if (!isset($_GET['ocode'])) {
      $order_id = save_order($account_id, $total_btc, $shipping, $cart_str, 
	  $address, $note, $order_code, "empty:paypal.com account");
	}
  } else {
    die(LANG('PROB_CALC_TOTAL').' '.LANG('TRY_REFRESHING'));
  }

  $order_vars = array(
	'type' => 'payment',
	'cmd' => '_xclick',
	'amount' => $subt_fiat,
	'shipping' => $ship_fiat,
	'currency_code' => $curr_code,
	'business' => $paypal_email,
	'item_name' => RAW_LANG('ORDER')." #$order_id",
	'invoice' => $order_code,
	'quantity' => 1,
	'no_note' => 1,
	'no_shipping' => 1,
	'return' => $return_url,
	'cancel_return' => $cancel_url,
	'notify_url' => $callback_url
  );
  
  // check the order was inserted into database
  if (isset($order_id) && is_numeric($order_id)) {
	
	// save order data to array
	$order_data = array(
	$total_btc, $buyer, $note, $order_id, 
    $exch_rate, $gateway, time());
	
	if (!isset($_GET['ocode'])) {
	  // encrypt order data
	  $t_data = bitsci::save_pay_query($order_data);
	
	  // save encrypted data to file
	  if (file_put_contents('t_data/'.$order_code, $t_data) !== false) {
	    chmod('t_data/'.$order_code, 0600);
	  } else {
        die(LANG('ERROR_CREATE_TRAN').' '.LANG('TRY_AGAIN_BACK'));
	  }
	}

    // go to paypal gateway
	echo '<center>';
	$paypal->printForm($order_vars);
	echo '</center>';

  } else {
    die(LANG('ERROR_CREATE_TRAN').' '.LANG('TRY_AGAIN_BACK'));
  }
} else {
  die(LANG('ERROR_CREATE_TRAN').' '.LANG('TRY_AGAIN_BACK'));
}
?>