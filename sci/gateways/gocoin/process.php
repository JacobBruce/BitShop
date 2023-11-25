<?php
require_once('./../lib/gocoin.lib.php');

if (isset($gateway) && $gateway === 'gocoin') {

  // generate gateway return and callback URLs
  $return_url = $site_url.'?page=return&result=success&code='.$order_code;
  $callback_url = $site_url.'sci/gateways/gocoin/callback.php?s='.$gocoin_call_secret;

  // insert new order into database
  if (is_numeric($total_btc)) {
    if (!isset($_GET['ocode'])) {
      $order_id = save_order($account_id, $total_btc, $shipping, $cart_str, 
	  $address, $note, $order_code, "empty:gocoin.com wallet");
	}
  } else {
    die(LANG('PROB_CALC_TOTAL').' '.LANG('TRY_REFRESHING'));
  }
  
  // generate GoCoin gateway link
  try {
    //$t_total = trim_decimal($total_btc, 4);
    $new_invoice = array(
	  'type' => 'bill',
      'base_price' => $total_fiat,
      'base_price_currency' => $curr_code,
	  'item_name' => RAW_LANG('ORDER')." #$order_id",
	  'order_id' => $order_code,
	  'callback_url' => $callback_url,
	  'redirect_url' => $return_url
    );
	if (!$debug_gocoin) {
	  GoCoin::setApiMode('production');
	}
    $new_invoice = GoCoin::createInvoice($gocoin_api_secret, $gocoin_merch_id, $new_invoice);
	$gateway_url = $new_invoice->gateway_url;
	$gocoin_online = true;
  } catch (Exception $e) {
	if ($debug_gocoin) { die($e->getMessage()); }
	$gocoin_online = false;
  }
  
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

    // go to gocoin gateway if online
	if ($gocoin_online) {
      redirect($gateway_url);
	} else {
      die('GoCoin '.LANG('GATEWAY_OFFLINE').' '.LANG('TRY_AGAIN_BACK'));
	}
  } else {
    die(LANG('ERROR_CREATE_TRAN').' '.LANG('TRY_AGAIN_BACK'));
  }
} else {
  die(LANG('ERROR_CREATE_TRAN').' '.LANG('TRY_AGAIN_BACK'));
}
?>