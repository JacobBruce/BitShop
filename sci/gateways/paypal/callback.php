<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/../../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../../../lib/paypal.lib.php');

$paypal = new PayPal();
if ($debug_paypal) $paypal->useSandbox();

// set up a few directory strings
$dat_file = dirname(__FILE__).'/../../t_data/';
$ipn_file = dirname(__FILE__).'/../../ipn-control.php';

try {
  // verify authenticity of callback
  if (!$paypal->verifyIPN()) {
    header('HTTP/1.1 400 Bad request', true, 400);
    exit;
  }
} catch (Exception $e) {
  header('HTTP/1.1 400 Bad request', true, 400);
  exit;
}

// verify callback secret
if (empty($_GET['s']) || $_GET['s'] !== $paypal_call_secret ||
empty($_POST['invoice']) || empty($_POST['payment_status'])) {
  header('HTTP/1.1 400 Bad request', true, 400);
  exit;
}

// get transaction details from PayPal
$order_code = $_POST['invoice'];
$pay_status = $_POST['payment_status'];
$seller_email = $_POST['receiver_email'];
$tran_id = $_POST['txn_id'];
$currency = $_POST['mc_currency'];
$amount_paid = $_POST['mc_gross'];

// get order data from file
$t_code = preg_replace("/[^a-z0-9]/i", '', $order_code);
$t_data = get_key_data($dat_file, $t_code);

// make sure the order was found
if ($t_data !== false) {

  $t_data = bitsci::read_pay_query($t_data);
  list($btc_total, $buyer, $note, $order_id, 
  $exch_rate, $gateway, $order_time) = $t_data;
  $fiat_total = bitsci::btc_num_format(bcmul($exch_rate,$btc_total), 2);

  switch($pay_status) {
	case 'Completed':
	  if ($seller_email !== $paypal_email || $currency != $curr_code) {
	    $status = 'Under Review';
	  } elseif (bccomp($amount_paid, $fiat_total) == -1) {
		$status = 'Underpaid';
	  } else {
	    require($ipn_file);
	    if ($error !== false) {
		  $status = 'Callback Error';
	    }
	  }
	  break;
	case 'Pending':
	  $status = empty($_POST['pending_reason']) ? 'Payment Pending' : 'Payment Pending: '.$_POST['pending_reason'];
	  break;
	default:
	  if ($pay_status == 'Created' || $pay_status == 'Processed') {
	    $status = 'Payment Pending';
	  } elseif ($pay_status == 'Expired' || $pay_status == 'Voided' || $pay_status == 'Failed') {
	    $status = 'Expired/Invalid';
	  } elseif ($pay_status == 'Reversed' || $pay_status == 'Refunded' || $pay_status == 'Canceled_Reversal' || $pay_status == 'Denied') {
	    $status = empty($_POST['reason_code']) ? $pay_status : $pay_status.': '.$_POST['reason_code'];
	  } else {
	    if ($debug_paypal) {
	      $status = 'Unknown: '.$pay_status;
		} else {
		  $status = 'Unknown';
		}
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

// check if we got an error
if (isset($error) && $error !== false) {
  header('HTTP/1.1 500 Internal Error', true, 500);
} else {
  header('HTTP/1.1 200 OK', true, 200);
}
?>
