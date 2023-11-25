<?php admin_valid();

$tid = (int) (empty($_GET['tid']) ? 0 : $_GET['tid']);

if (!empty($_GET['action']) && $_GET['action'] == 'codes') {

  require_once('./inc/admin/ordercodes.inc.php');

} elseif (!empty($_GET['tid']) && is_numeric($_GET['tid'])) {
?>

<h1>Order <small>#<?php echo $tid; ?></small></h1>

<?php
  $order = get_order_byid($tid);
  $go_back = false;
  
  if (!empty($_GET['task'])) {
  
    if ($_SESSION['csrf_token'] === $_GET['toke']) {
	
	  if ($_GET['task'] === 'newstat') {
	  
		if (edit_order($tid, 'ShipStatus', "'".safe_sql_str($_GET['newstat'])."'")) {
		  echo "<p class='happy_txt'>Order shipping status was successfully updated!</p>";
		} else {
		  echo "<p class='error_txt'>Order shipping status could not be updated!</p>";
		}
		
	  } elseif ($_GET['task'] === 'confirm') {
	  
		if (!empty($order) && ($order !== 'N/A')) {
		
		  $order = mysqli_fetch_assoc($order);  
		  $t_data = get_key_data('./sci/t_data/', $order['Code']);

		  if ($t_data !== false) {

			$t_data = bitsci::read_pay_query($t_data);
			list($btc_total, $buyer, $note, $order_id, 
			$exch_rate, $gateway, $order_time) = $t_data;
			$fiat_total = bitsci::btc_num_format(bcmul($exch_rate,$btc_total));
			$tran_id = strtoupper(substr(rand_str(), 0, 16));
			
			if (empty($_GET['amount'])) {
			  if (empty($order['Currency'])) {
				$amount_paid = $order['Total'];
				$currency = 'BTC';
			  } else {
				$amount_paid = $order['Amount'];
				$currency = $order['Currency'];
			  }
			} else {
			  $amount_paid = $_GET['amount'];
			  $currency = 'BTC';
			}
			
			require_once('./sci/ipn-control.php');
			
			if ($error === false) {
			  echo "<p class='happy_txt'>The order was successfully confirmed!</p>";	  
			} else {
			  echo $error;
			}
		  } else {
			echo "<p class='error_txt'>Order information could not be found!</p>";
		  }
		} else {
		  echo "<p class='error_txt'>Could not locate referenced order!</p>";
		}
		
	  } elseif ($_GET['task'] === 'delete') {
	  
		if (!empty($order) && ($order !== 'N/A')) {
		  $order = mysqli_fetch_assoc($order);  
		  if (delete_order($order['OrderID'])) {
			update_stock($order);
			$go_back = true;
			echo "<p class='happy_txt'>The order was successfully deleted!</p>";
		  } else {
			echo "<p class='error_txt'>There was an error deleting the order!</p>";
		  }
		} else {
		  echo "<p class='error_txt'>Could not locate referenced order!</p>";
		}
	  }
	  
	  $order = get_order_byid($tid);
	  
	} else {
	  echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
	}
  }
  
  if ($go_back == true) {
    echo "<p>Please wait ...</p>";
    redirect('admin.php?page=orders');
	
  } elseif (!empty($order) && ($order !== 'N/A')) {
  
    $order = mysqli_fetch_assoc($order);
	$key_data = explode(':', $order['KeyData']);
	
	if ($order['Shipping'][0] === '-') {
	  $ship_curr = '';
	  $shipping = 'not required';
	} else {
	  $ship_curr = $curr_code;
	  $shipping = $order['Shipping'];
	}
	
	$t_code = preg_replace("/[^a-z0-9]/i", '', $order['Code']);
	$t_data = get_key_data('./sci/t_data/', $t_code);

	// make sure the order was found
	if ($t_data !== false) {

	  $t_data = bitsci::read_pay_query($t_data);
	  list($btc_total, $buyer, $note, $order_id, 
	  $exch_rate, $gateway, $order_time) = $t_data;
	  $fiat_total = bitsci::btc_num_format(bcmul($exch_rate,$btc_total), 2);
	  
	  $items = list_cart_items($order['Cart'], 'links');
	  if (!empty($items)) {
	    $item_list = implode('<br />', $items);
	  } else {
	    $item_list = 'n/a';
	  }
	  
	  $vouchs = list_cart_vouchs($order['Cart'], 'links');
	  if (!empty($vouchs)) {
	    $vouch_list = implode('<br />', $vouchs);
	  } else {
	    $vouch_list = 'none';
	  }
?>

<table class='table table-striped table-bordered table-condensed' style='width:600px'>
<tr>
  <th colspan='2'>Buyer Email</th>
  <th colspan='2'>Tx Code</th>
</tr>
<tr>
  <td colspan='2'><a href="./admin.php?page=accounts&amp;id=<?php echo $order['AccountID']; ?>"><?php safe_echo($buyer); ?></a></td>
  <td colspan='2'><?php safe_echo(empty($order['TranCode']) ? 'n/a' : $order['TranCode']); ?></td>
</tr><tr>
  <th>Shipping</th>
  <th>Total</th>
  <th>Amount Paid</th>
  <th>Exch Rate</th>
</tr><tr >
  <td><?php echo "$shipping&nbsp;$ship_curr"; ?></td>
  <td><?php echo $order['Total']."&nbsp;BTC ($curr_symbol$fiat_total&nbsp;$curr_code)"; ?></td>
  <td><?php echo ($order['Amount']==0) ? 'n/a' : $order['Amount'].'&nbsp;'.$order['Currency']; ?></td>
  <td><?php echo "$exch_rate&nbsp;$curr_code"; ?></td>
</tr><tr>
  <th>Ship Status</th>
  <th>Payment Status</th>
  <th>Date Paid</th>
  <th>Date Created</th>
</tr><tr >
  <td><?php safe_echo($order['ShipStatus']); ?></td>
  <td><?php safe_echo($order['Status']); ?></td>
  <td><?php safe_echo(empty($order['DatePaid']) ? 'n/a' : format_time($order['DatePaid'])); ?></td>
  <td><?php safe_echo(empty($order['Created']) ? 'n/a' : format_time($order['Created'])); ?></td>
</tr><tr>
  <th colspan='2'>Shipping Address</th>
  <th colspan='2'>Buyer Note</th>
</tr><tr>
  <td colspan='2'><?php echo str_replace("\n", '<br />', safe_str($order['Address'])); ?></td>
  <td colspan='2'><?php safe_echo(empty($order['Note']) ? 'n/a' : $order['Note']); ?></td>
</tr><tr>
  <th colspan='2'>Items Purchased</th>
  <th colspan='2'>Vouchers/Coupons</th>
</tr><tr >
  <td colspan='2'><?php echo $item_list; ?></td>
  <td colspan='2'><?php echo $vouch_list; ?></td>
</tr><tr>
  <th colspan='4'>Payment (<?php safe_echo($gateway); ?> gateway)</th>
</tr><tr>
  <?php
  // check what payment gateway was used
  if ($key_data[0] === 'empty') {
    if (strpos($key_data[1], 'coinbase') !== false) {
	  if (!empty($order['TranCode'])) {
	    $tc = safe_str($order['TranCode']);
	    echo "<td colspan='4'><a target='_blank' href='https://commerce.coinbase.com/receipts/$tc'>View Coinbase payment</a></td>";
	  } else {
	    echo "<td colspan='4'><a target='_blank' href='https://commerce.coinbase.com/dashboard/payments'>View Coinbase payments</a></td>";
	  }
    } elseif (strpos($key_data[1], 'gocoin') !== false) {
	  if (!empty($order['TranCode'])) {
	    $tc = safe_str($order['TranCode']);
	    echo "<td colspan='4'><a target='_blank' href='https://dashboard.gocoin.com/invoices/$tc'>View GoCoin invoice</a></td>";
	  } else {
	    echo "<td colspan='4'><a target='_blank' href='https://dashboard.gocoin.com/merchants/$gocoin_merch_id/invoices/'>View GoCoin invoices</a></td>";
	  }
    } elseif (strpos($key_data[1], 'paypal') !== false) {
	  if (!empty($order['TranCode'])) {
	    $tc = safe_str($order['TranCode']);
	    echo "<td colspan='4'><a target='_blank' href='https://www.paypal.com/myaccount/transactions/details/$tc'>View PayPal transaction</a></td>";
	  } else {
	    echo "<td colspan='4'><a target='_blank' href='https://www.paypal.com/myaccount/transactions'>View PayPal transactions</a></td>";
	  }
	} else {
	  echo '<td colspan="4">Destination: '.safe_str($key_data[1]).'</td>';
	}
  } else {
  ?>
  <td colspan='4'>
    <?php if (!empty($key_data[0])) { ?>
    <b>Private Key</b>
    <a href="#" onClick="decrypt_key('<?php echo $key_data[0]; ?>')">(decrypt)</a>
    <br /><input type="text" id="priv_key" class="key_box" value="<?php echo $key_data[0]; ?>" />
    <br /><b>Bitcoin Address</b>
    <a id="pal" href="#" onClick="get_balance('<?php echo $key_data[1]; ?>');">(get balance)</a>
    <br /><input type="text" id="pub_key" class="key_box" value="<?php echo $key_data[1]; ?>" />
    <?php } else { ?>
	Payment stage not yet initiated, order may be abandoned.
	<?php } ?>
  </td>
  <?php } ?>
</tr>
</table>

<p><a href="admin.php?page=orders" title="Go Back">BACK</a> | <a href="#" title="Delete Order" onClick="delete_tran();">DELETE ORDER</a> <?php if ($order['Status'] != 'Confirmed') { echo '| <a href="#" title="Confirm Order" onClick="confirm_tran();">CONFIRM ORDER</a>'; } ?> | <a href='#' title="Update Order Status" onClick="update_status();">CHANGE SHIP STATUS</a> | <a href='./admin.php?page=orders&amp;tid=<?php echo $tid; ?>&amp;action=codes' title="Manage Digital Codes">MANAGE CODES</a></p>

<p><b>Information:</b> When you delete this order the stock number of the corresponding product (if it's not an instant download) will go up by the quantity being purchased in this order, but only if this order has not been confirmed. In order to avoid false stock numbers on your products you should try to clean out unconfirmed orders which are several days old. In other words, when an order is placed, stock is reserved for the buyer under the assumption they are about to pay, if the buyer cancels the transaction with the cancel button the order will automatically be deleted from the database and the stock will be returned but if they don't pay and don't cancel the order you will get orders which remain unconfirmed (red) and their reserved stock cannot be sold again until you delete the order.</p>

<p><b class="error_txt">WARNING:</b> Make sure to check the balance of the address before you delete this order.</p>

<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';
var total = <?php echo $order['Total']; ?>;
var status = '<?php echo $order['Status']; ?>';

function do_decrypt(pk, ciphertext) {

	var rsa = new RSAKey();
	var pub_dat = '<?php echo $pub_rsa_key; ?>';
	var pri_dat = pk.split(':');

	var n = pub_dat;
	var d = pri_dat[0];
	var p = pri_dat[1];
	var q = pri_dat[2];
	var dp = pri_dat[3];
	var dq = pri_dat[4];
	var c = pri_dat[5];

	rsa.setPrivateEx(n, '10001', d, p, q, dp, dq, c);

	var res = rsa.decrypt(ciphertext);

	if (res == null) {
		return "*** Invalid Ciphertext ***";
	} else {
		return res;
	}
}

function decrypt_key(key_str) {
	var priv_key = prompt('Private Key:', '');
	$('#priv_key').val(do_decrypt(priv_key, key_str));
}

function updateBalance(response) {
	$('#pal').html('('+response+' BTC)');
	if ((response >= total) && (status != 'Confirmed')) {
		if (confirm('The balance of this address is equal to the total price of this order. Do you wish to confirm this order?')) {
			redirect('admin.php?page=orders&task=confirm&tid=<?php echo $tid; ?>&amount='+response+'&toke='+csrf_token);
		}
	}
}

function handle_error(response) {
	$('#pal').html('(error getting balance)');
}

function get_balance(address) {
	$('#pal').html('(Checking...)');
	ajax_get('sci/get_balance.php', {'address': address}, updateBalance, handle_error);
}

function delete_tran() {
	if (confirm('Are you really sure you want to delete this order?')) {
		redirect('admin.php?page=orders&task=delete&tid=<?php echo $tid; ?>&toke='+csrf_token);
	}
}

function confirm_tran() {
	if (confirm('Are you completely sure you want to confirm this order?')) {
		redirect('admin.php?page=orders&task=confirm&tid=<?php echo $tid; ?>&toke='+csrf_token);
	}
}

function update_status() {
	var new_status = prompt('Enter new status:', '');
	if (new_status != null && new_status != '') {
		redirect('admin.php?page=orders&tid=<?php echo $tid; ?>&task=newstat&newstat='+encodeURIComponent(new_status)+'&toke='+csrf_token);
	}
}

$(document).ready(function() {
	$(".key_box").focus(function(){
		$(this).select();
	});
});
</script>

<?php
    } else {
      echo "<p class='error_txt'>Order does not exist!</p>";
    }
  } else {
    echo "<p class='error_txt'>Order does not exist!</p>";
  }
} else {
  
  if (!empty($_GET['task'])) {
    if ($_SESSION['csrf_token'] === $_GET['toke']) {
      if ($_GET['task'] == 'delunc') {
	    $unc_trans = list_unconfirmed_orders();
	    if (!empty($unc_trans) && ($unc_trans !== 'N/A')) {
	      while ($tran = mysqli_fetch_assoc($unc_trans)) {
		    if (delete_order($tran['OrderID'])) {
		      update_stock($tran);
		    }
	      }
	      echo "<p class='happy_txt'>All unconfirmed orders successfully deleted.</p>";
	    } else {
	      echo "<p class='error_txt'>Could not find any unconfirmed orders!</p>";
	    }
      } elseif ($_GET['task'] == 'delall') {
	    if (delete_all_orders()) {
	      file_put_contents('inc/rss.inc', '');
	      echo "<p class='happy_txt'>All orders successfully deleted.</p>";
	    } else {
	      echo "<p class='error_txt'>There was a problem deleting the orders!</p>";
	    }
      }
	} else {
	  echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
	}
  }

  if (empty($_GET['p'])) {
	$curr_page = 1;
  } else {
	$curr_page = (int) $_GET['p'];
	if ($curr_page < 1) {
	  $curr_page = 1;
	}
  }

  $orders = list_all_orders(round(($curr_page-1) * 20));
  $order_num = count_orders();
  $page_num = (int) ceil($order_num / 20);

  $start_page = $curr_page - 2;
  if ($start_page < 1) {
	$start_page = 1;
  }

  $end_page = $start_page + 4;
  if ($end_page > $page_num) {
	$end_page = $page_num;
	$start_page = $end_page - 4;
	if ($start_page < 1) {
	  $start_page = 1;
	}
  }

  if ($page_num > 1) {
	$p_active = ($curr_page == 1) ? " class='active'" : '';
	$nav_html = "<li$p_active><a href='admin.php?page=orders&amp;p=1'>First</a></li>";
	for ($i=$start_page;$i<=$end_page;$i++) {
	  $p_active = ($i == $curr_page) ? " class='active'" : '';
	  $nav_html .= "<li$p_active><a href='admin.php?page=orders&amp;p=$i'>$i</a></li>";
	}
	$p_active = ($curr_page == $page_num) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='admin.php?page=orders&amp;p=$page_num'>Last</a></li>";
  }

  if (!empty($nav_html)) {
	echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
  }

  if (!empty($order_num)) {
?>

<h1>Customer Orders</h1>
<p><b>List of orders:</b></p>

<table class='table table-striped table-bordered table-hover table-condensed'>
<tr>
  <th>Order ID</th>
  <th>Pay Status</th>
  <th>Date Paid</th>
  <th>Amount Paid</th>
  <th>Tx Code</th>
</tr>
<?php
	if (!empty($orders) && ($orders !== 'N/A')) {
	  while ($row = mysqli_fetch_assoc($orders)) {
	  
		switch ($row['Status']) {
		case 'Confirmed':
		  $row_class = 'success';
		  break;
		case 'Unconfirmed':
		  $row_class = 'error';
		  break;
		case 'Callback Error':
		  $row_class = 'error';
		  break;
		case 'Payment Pending':
		  $row_class = 'info';
		  break;
		default:
		  $row_class = 'warning';
		}
		
		$row['Amount'] = ($row['Amount'] == 0) ? 'n/a' : $row['Amount'].'&nbsp;'.$row['Currency'];
		$row['DatePaid'] = empty($row['DatePaid']) ? 'n/a' : format_time($row['DatePaid']);
		$row['TranCode'] = empty($row['TranCode']) ? 'n/a' : $row['TranCode'];			
		$row_link = 'admin.php?page=orders&amp;tid='.$row['OrderID'];
		
		echo "<tr class='$row_class tr_link' onclick=\"document.location='$row_link';\">".
		"<td><a href='$row_link'>".$row['OrderID']."</a></td><td>".$row['Status'].
		"</td><td>".str_replace(' ', '&nbsp;', $row['DatePaid'])."</td><td>".
		$row['Amount']."</td><td>".$row['TranCode']."</td></tr>";
	  }
	}
?>
</table>
  
<p><a href="admin.php?page=home" title="Main Menu">BACK</a> | <a href="#" onclick="del_unc_trans();" title="Clean out unconfirmed orders">DELETE UNCONFIRMED ORDERS</a> | <a href="#" onclick="del_all_trans();" title="Delete ALL orders">DELETE ALL ORDERS</a></p>

<?php
  } else {
    echo '<h1>Orders</h1>';
	echo "<p>There are no orders yet.</p>";
	echo '<p><a class="btn" href="admin.php?page=home">Go Back</a></p>';
  }
?>

<script type="text/javascript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

function del_all_trans() {
	if (confirm('This action will delete ALL the orders in your database and empty the RSS feed. Are you completely sure you want to continue?')) {
		redirect('admin.php?page=orders&task=delall&toke='+csrf_token);
	}
}

function del_unc_trans() {
	if (confirm('This action will delete all the unconfirmed orders in your database. Are you sure you want to continue?')) {
		redirect('admin.php?page=orders&task=delunc&toke='+csrf_token);
	}
}
</script>

<?php } ?>
