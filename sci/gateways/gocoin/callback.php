<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/../../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../../../lib/gocoin.lib.php');

// set up a few directory strings
$dat_file = dirname(__FILE__).'/../../t_data/';
$ipn_file = dirname(__FILE__).'/../../ipn-control.php';

// get data being passed to us
$post_data = json_decode(file_get_contents('php://input'), true);

// verify authenticity of callback
if (empty($_GET['s']) || $_GET['s'] !== $gocoin_call_secret) {
  header('HTTP/1.1 400 Bad request', true, 400);
  exit;
}

// get transaction details from GoCoin
try {
  $event       = $post_data['event'];
  $invoice     = $post_data['payload'];
  $tran_id     = $invoice['id'];
  $invoice = GoCoin::getInvoice($gocoin_api_secret, $tran_id);
  $pay_status  = $invoice->status;
  $cust_code   = $invoice->order_id;
  $currency    = $invoice->price_currency;
  $amount_paid = $invoice->price;
}
catch (Exception $e) {
  header('HTTP/1.1 500 Internal Error', true, 500);
  exit;
}

// get order data from file
$t_code = preg_replace("/[^a-z0-9]/i", '', $cust_code);
$t_data = get_key_data($dat_file, $t_code);

// make sure the order was found
if ($t_data !== false) {

  $t_data = bitsci::read_pay_query($t_data); 
  list($btc_total, $buyer, $note, $order_id, 
  $exch_rate, $gateway, $order_time) = $t_data;
  $fiat_total = bitsci::btc_num_format(bcmul($exch_rate,$btc_total), 2);

  switch($event) {
	case 'invoice_created':
	  $status = 'Payment Pending';
	  break;
	case 'invoice_payment_received':
	  if ($pay_status == 'underpaid') {
		$status = 'Underpaid';
	  } else {
		  $status = 'Payment Pending';
	  } 
	  break;
	case 'invoice_merchant_review':
	  $status = 'Under Review';
	  break;
	case 'invoice_ready_to_ship':
	  if ($pay_status == 'paid' || $pay_status == 'ready_to_ship') {
	    require($ipn_file);
		if ($error !== false) {
		  $status = 'Callback Error';
		}
	  } elseif ($pay_status == 'unpaid') {
		$status = 'Test Confirmed';
	  } else {
		$status = $pay_status;
	  }
	  break;
	case 'invoice_invalid':
	  $status = 'Expired/Invalid';
	  break;
	default:
	  if ($debug_gocoin) {
	    $status = "Unknown: $event ($pay_status)";
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
