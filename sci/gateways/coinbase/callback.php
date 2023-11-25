<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/../../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../../../lib/coinbase.lib.php');

use CoinbaseCommerce\Webhook;

// set up a few directory strings
$dat_file = dirname(__FILE__).'/../../t_data/';
$ipn_file = dirname(__FILE__).'/../../ipn-control.php';

// ensure callback secret matches
if (empty($_GET['s']) || $_GET['s'] !== $coinbase_call_secret) {
  header('HTTP/1.1 400 Bad request', true, 400);
  exit;
}

$headerName = 'X-Cc-Webhook-Signature';
$headers = getallheaders();
$sigHeader = isset($headers[$headerName]) ? $headers[$headerName] : null;
$raw_body = trim(file_get_contents('php://input'));
$post_data = json_decode($raw_body, true);

try {// verify authenticity of callback
    $event = Webhook::buildEvent($raw_body, $sigHeader, $coinbase_api_secret);
} catch (Exception $exception) {
    header('HTTP/1.1 400 Bad request', true, 400);
    if ($debug_coinbase) die(safe_str($exception->getMessage()));
    exit;
}
	
// get transaction details
$tran_id = $post_data['event']['data']['code'];
$cust_code = $post_data['event']['data']['metadata']['order_code'];
$pay_status = $post_data['event']['type'];

// get order data from file
$t_code = preg_replace("/[^a-z0-9]/i", '', $cust_code);
$t_data = get_key_data($dat_file, $t_code);

// make sure the order was found
if ($t_data !== false) {

  $t_data = bitsci::read_pay_query($t_data);
  list($btc_total, $buyer, $note, $order_id, 
  $exch_rate, $gateway, $order_time) = $t_data;
  $fiat_total = bitsci::btc_num_format(bcmul($exch_rate,$btc_total), 2);

  switch ($pay_status) {
  case 'charge:created':
	$status = 'Payment Pending';
	break;
  case 'charge:pending':
	$status = 'Payment Pending';
	break;
  case 'charge:delayed':
	$status = 'Under Review';
	break;
  case 'charge:confirmed':
    $pay_count = count($post_data['event']['data']['payments']);
	if (!is_numeric($pay_count) || $pay_count < 1) {
	  $status = 'Under Review';
	  break;
	}
	if (isset($post_data['event']['data']['payments'][0]['value']['crypto'])) {
	  $currency = $post_data['event']['data']['payments'][0]['value']['crypto']['currency'];
	  $ptarg = 'crypto';
	} else {
	  $currency = $post_data['event']['data']['payments'][0]['value']['local']['currency'];
	  $ptarg = 'local';
	}
    if ($pay_count === 1) {
      $amount_paid = $post_data['event']['data']['payments'][0]['value'][$ptarg]['amount'];
	} else {
	  $amount_paid = '0.0';
	  foreach ($post_data['event']['data']['payments'] as $key => $val) {
	    if ($val['value'][$ptarg]['currency'] == $currency) {
		  $amount_paid = bcadd($amount_paid, $val['value'][$ptarg]['amount']);
		}
	  }
	}
	require($ipn_file);
	if ($error !== false) {
	  $status = 'Callback Error';
	}
	break;
  case 'charge:resolved':
	break;
  case 'charge:failed':
	$status = 'Expired/Invalid';
	break;
  default:
    if ($debug_coinbase) {
	  $status = 'Unknown: '.$pay_status;
	} else {
	  $status = 'Unknown';
	}
  }

} else {
  header('HTTP/1.1 500 Internal Error', true, 500);
  exit;
}

if (isset($status)) {      
  // connect to database
  $hide_crash = true;
  $conn = connect_to_db();

  // update payment status in db
  set_order_status($order_id, $status);
}

// return error code if unable to confirm order
if (isset($error) && $error !== false) {
  header('HTTP/1.1 500 Internal Error', true, 500);
} else {
  header('HTTP/1.1 200 OK', true, 200);
}
?>
