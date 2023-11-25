<?php
  /**
  * Bitcoin Payment Gateway
  *
  * @author Jacob Bruce
  * www.bitfreak.info
  */

  // $order_id : the database index of the order
  // $tran_id : the callback transaction id
  // $fiat_total : total fiat cost of order
  // $btc_total : total cost of the order in BTC
  // $exch_rate : exchange rate at time of purchase
  // $amount_paid : the real amount paid in $currency
  // $currency : form of currency used to pay
  // $buyer : email address of the customer
  // $note : customer note (possibly empty)

  require_once(dirname(__FILE__).'/config.php');
  require_once(dirname(__FILE__).'/../lib/common.lib.php');
  require_once(dirname(__FILE__)."/../inc/langs/$locale.inc.php");
  
  // ensure we have $order_id and $tran_id
  if (isset($order_id) && isset($tran_id)) {

    // ensure the transaction has been paid
    if (isset($amount_paid) && isset($currency)) {
	
	  // get confirmation time
	  $confirm_date = date('Y-m-d H:i:s T');
	  
      // connect to database
	  $hide_crash = true;
	  $conn = connect_to_db();
	
	  // get order details from database
	  $order = get_order_byid($order_id);
	
	  // make sure the order exists in db
	  if (!empty($order) && ($order !== 'N/A')) {
	  
	    // get assoc array of order details
	    $order = mysqli_fetch_assoc($order);
		
		// get payment destination info
		$key_data = explode(':', $order['KeyData']);
		$destination = $key_data[1];
	  
	    // make sure the order has not already been confirmed
	    if ($order['Status'] !== 'Confirmed') {
	  
          // confirm order by updating status in database
          if (confirm_order($order_id, $tran_id, 'Confirmed', $amount_paid, $currency)) {
		  
			// send confirmation email to buyer
		    send_confirm_email($order, $seller);
			
		    // create an email to alert admin of order
		    if ($send_email) {
		
		      // form body of email message
		      $body = "A new order has been confirmed: \n\n".
		      "Buyer: $buyer \n".
		      "Order ID: $order_id \n".
		      "Tran ID: $tran_id \n".
			  "Total: $btc_total BTC ($fiat_total $curr_code) \n".
			  "Paid: $amount_paid $currency \n".
			  "Date: $confirm_date \n".
			  "Sent to: $destination \n".
			  "Note: $note \n\n".
		      "Log into the admin panel for more information.";

		      // send email to admin
			  if ($smtp_enable) {
			    $subject = RAW_LANG('NEW_ORDER');
				send_smtp_email($contact_email, $subject, $body);
			  } else {
			    $subject = rfc1342b(RAW_LANG('NEW_ORDER'));
		        mail($contact_email, $subject, $body, get_mail_headers());
			  }
			}

			// unserialize the cart string
			list($items_str, $vouch_str) = explode('|', $order['Cart']);
			$items_arr = explode(',', $items_str);
			$vouch_ids = explode(',', $vouch_str);
			
			// loop through each item in cart
			foreach ($items_arr as $key => $item_dat) {
			
			  // make sure item data is not empty
			  if (empty($item_dat)) { continue; }
			  
			  // get item id and quantity purchased
			  list($item_id, $quant) = explode(':', $item_dat);
			  
			  // pull item data from database
			  $item = mysqli_fetch_assoc(get_file($item_id));
			  
		      // update number of sales for product
			  edit_file($item_id, "FileSales = FileSales + $quant");
			  
			  // generate any necessary codes/keys
			  generate_codes($item, $quant, $order);

			  // add purchase details to feed if enabled
			  if ($rss_feed === true) {

			    $now = date("D, d M Y H:i:s T");
			    $rss_src = dirname(__FILE__)."/../inc/rss.inc";
			    $item_name = spec_str($item['FileName']);
			    $rss_item = "<item>\n\t<title>".spec_str(RAW_LANG('SOLD')).
				" $quant x $item_name </title>\n\t<link>$site_url?page=item".
				"&amp;id=$item_id</link>\n\t<description>".spec_str(RAW_LANG('PAID_TO')).
				": $destination</description>\n\t<pubDate>$now</pubDate>\n</item>\n";
			    $rss_items = file_get_contents($rss_src);
			    file_put_contents($rss_src, $rss_item.$rss_items);
			  }
			}
			
			// loop through any vouchers/coupons
			foreach ($vouch_ids as $key => $vouch_id) {
			  if (empty($vouch_id)) { continue; }		  
			  $voucher = get_voucher_byid($vouch_id);
			  if (!empty($voucher) && $voucher !== 'N/A') {
			    $voucher = mysqli_fetch_assoc($voucher);
				if ($voucher['UseType'] == 1) {
				  // disable single-use voucher
				  disable_voucher($vouch_id);
				} elseif ($voucher['UseType'] == 2) {
				  if ($voucher['Credits'] > 1) {
				    // decrement coupon credits
				    update_voucher($vouch_id);
				  } else {
				    // coupon credits are used up
				    disable_voucher($vouch_id);
				  }
				}
			  }
			}
			
			// log the transaction data
			$ts = "Sent to: ".$destination."\nBuyer: ".$buyer.
				  "\nTotal: ".$btc_total." BTC\nPaid: ".$amount_paid.
				  " ".$currency."\nExch Rate: ".$exch_rate.' '.
				  $curr_code."\nOrder ID: ".$order_id."\nTran ID: ".
				  $tran_id."\nDate: ".$confirm_date."\nNote: ".$note;
			$fp=fopen(dirname(__FILE__)."/$ipn_log_file","a");
			if ($fp) {
			  if (flock($fp,LOCK_EX)) {
				@fwrite($fp,$ts."\n\n");
				flock($fp,LOCK_UN);
			  }
			  fclose($fp);
			}
			
			// order successfully confirmed
			$error = false;

	      } else {
            $error = LANG('DATABASE_ERROR');
	      }
	    } else {
		  $error = false;
	    }
	  } else {
        $error = LANG('TRAN_NOT_FOUND');
	  }
    } else {
      $error = LANG('TRAN_NOT_CONFIRMED');
    }
  } else {
    $error = LANG('UNEXPECTED_ERROR');
  }
?>