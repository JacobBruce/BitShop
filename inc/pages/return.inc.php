<script language='JavaScript'>
// remove cookie using js since headers already sent
clearOrderCookie();
$('#order_alert').hide();
</script>
<?php
if (!empty($_GET['result'])) {

  // check if we need to delay the script
  if (!empty($_GET['delay'])) {
	// give the callback script time to complete
	sleep((int)$_GET['delay']);
  }

  if ($_GET['result'] === 'cancel') {
  
	echo '<h1>'.LANG('TRAN_FAILED').'</h1>';
	
	// get order from database
	$order = get_order_bycode($_GET['code']);
	
	if (!empty($order) && ($order !== 'N/A')) {
	  // convert db result into assoc array
	  $order = mysqli_fetch_assoc($order);
	  // check if canceled or expired
  	  if ($order['Status'] === 'Expired/Invalid') {
	    echo '<p>'.LANG('TRAN_EXPIRED').' '.LANG('CONTACT_ADMIN').'</p>';
  	  } elseif ($order['Status'] === 'Callback Error' || strpos($order['Status'], 'Unknown') !== false) {
	    echo '<p>'.LANG('TRAN_CANCELLED').' '.LANG('CONTACT_ADMIN').'</p>';
	  } else {
        echo '<p>'.LANG('TRAN_CANCELLED').'</p>'; 
	    // delete order and update stock
	    if (delete_order($order['OrderID'])) {
	      update_stock($order);
	    }
	  }
	}
	
  } elseif ($_GET['result'] === 'success') {
	
	if (!empty($_GET['code'])) {
	
	  // pull corresponding order from db
	  $order = get_order_bycode($_GET['code']);
	  
	  // make sure the db result is valid
	  if (!empty($order) && ($order !== 'N/A')) {
	  
	    // convert mysql result into assoc array
	    $order = mysqli_fetch_assoc($order);
		
		// verify state of transaction
		if ($order['Status'] === 'Confirmed') {
		
		  $key_data = explode(':', $order['KeyData']);
		  $destination = $key_data[1];
		  
		  // display transaction info
		  echo '<h1>'.LANG('TRAN_CONFIRMED').'</h1><p>'.LANG('PURCHASE_COMPLETE').
		  '</p><p><b>'.LANG('TRAN_CODE').':</b> '.safe_str($order['TranCode']).'<br /><b>'.
		  LANG('TOTAL_PAID').':</b> '.$order['Amount'].' '.safe_str($order['Currency']).
		  '<br /><b>'.LANG('DATE_PAID').':</b> '.safe_str(format_time($order['DatePaid'])).
		  '<br /><b>'.LANG('PAID_TO').':</b> '.$destination.'</p>';
		  
		  // display list of items purchased
		  echo '<h3>'.LANG('ITEMS_PURCHASED').':</h3><p>';
		  $item_list = list_cart_items($order['Cart']);
		  echo implode('<br />', $item_list).'</p>';
		  
		  echo '<p><a href="./?page=account">'.LANG('LOGIN').
		       '</a> '.LANG('TO_ACCESS_ITEMS').'</p>';
		
		// check if transaction underpaid
		} elseif ($order['Status'] === 'Underpaid') {
		
		  echo '<h1>'.LANG('TRAN_FAILED').'</h1><p>'.
		  LANG('TRAN_UNDERPAID').' '.LANG('CONTACT_TO_VERIFY').'</p>';
		  
		// otherwise show payment pending message
		} else {
		
		  echo '<h1>'.LANG('PROCESSING_TRAN').'</h1><p>'.LANG('PAYMENT_PENDING').
		  ' '.LANG('NO_ACCESS_UNTIL').'</p><p>'.LANG('LOGIN_TO_CHECK').'</p>';
		  
		}
	  } else {
	    echo "<p class='error_txt'>".LANG('TRAN_NOT_FOUND')."</p>";
	  }
	} else {
	  echo "<p class='error_txt'>".LANG('NO_TRAN_CODE')."</p>";
	}
  } else {
    echo "<p class='error_txt'>".LANG('INVALID_RESULT')."</p>";
  }
} else {
  echo "<p class='error_txt'>".LANG('NO_RESULT')."</p>";
}
?>