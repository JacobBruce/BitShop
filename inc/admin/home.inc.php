<?php admin_valid(); ?>

<center>
  <h1>Admin Control Panel</h1>

  <p>Here you can review your orders, manage your products, etc.<br />
  Use the navigation menu to the  left to get started.</p>
</center>

<hr />

<div class="row-fluid">
  <div class="span4">
	<center>
	  <h3>Lifetime Sales</h3>
	  <h4><?php
      $ti_arr = total_income();
	  if ($ti_arr !== 0) {
	    $sln = '';
	    foreach ($ti_arr as $key => $val) {
	      if ($key == $curr_code) {
	        $sln .= $curr_symbol.bitsci::btc_num_format($val,2).'&nbsp;'.$key.' + ';
	      } else {
	        $sln .= $val.'&nbsp;'.$key.' + ';
	      }
	    }
	    echo substr($sln, 0, strlen($sln)-3);
	  } else {
	    echo $curr_symbol."0.0 $curr_code";
	  }
	  ?></h4>
	</center>
  </div>
  <div class="span4">
	<center>
	  <h3>Orders Placed</h3>
	  <h4><?php echo order_num(); ?></h4>
	</center>
  </div>
  <div class="span4">
	<center>
	  <h3>Unique Customers</h3>
	  <h4><?php echo total_customers(); ?></h4>
	</center>
  </div>
</div>

<hr />

<h3>Sales Summary:</h3>
<table class="table table-striped table-bordered table-condensed">
  <tr>
    <th>Confirmed orders in last 30 days:</th>
    <th>Unconfirmed orders in last 30 days:</th>
    <th width="30%">Total sales in last 30 days:</th>
    <th width="30%">Total sales in current month:</th>
  </tr>
  <tr>
    <td><?php echo order_num(30); ?></td>
    <td><?php echo order_num(30, false); ?></td>
    <td><?php
    $tm_arr = total_income(30);
	if ($tm_arr !== 0) {
	  $sln = '';
	  foreach ($tm_arr as $key => $val) {
	    if ($key == $curr_code) {
	      $sln .= $curr_symbol.bitsci::btc_num_format($val,2).'&nbsp;'.$key.' + ';
	    } else {
	      $sln .= $val.'&nbsp;'.$key.' + ';
	    }
	  }
	  echo substr($sln, 0, strlen($sln)-3);
	} else {
	  echo $curr_symbol."0.0 $curr_code";
	}
	?></td>
	<td><?php
    $ai_arr = monthly_income();
	if ($ai_arr !== 0) {
	  $sln = '';
	  foreach ($ai_arr as $key => $val) {
	    if ($key == $curr_code) {
	      $sln .= $curr_symbol.bitsci::btc_num_format($val,2).'&nbsp;'.$key.' + ';
	    } else {
	      $sln .= $val.'&nbsp;'.$key.' + ';
	    }
	  }
	  echo substr($sln, 0, strlen($sln)-3);
	} else {
	  echo $curr_symbol."0.0 $curr_code";
	}
	?></td>
  </tr>
</table>

<h3>Product summary:</h3>
<table class="table table-striped table-bordered table-condensed">
  <tr>
    <th>Number of active products:</th>
    <th>Number of inactive products:</th>
    <th width="30%">Best selling product:</th>
    <th width="30%">Highest rated product:</th>
  </tr>
  <tr>
    <td><?php echo count_active_files(); ?></td>
	<td><?php echo count_inactive_files(); ?></td>
    <td>
	  <?php
	  $best_item = best_file();
	  if (!empty($best_item) && $best_item != 'N/A') {
		echo "<a href='admin.php?page=items&amp;action=edit&amp;fid=".
		$best_item['FileID']."'>".$best_item['FileName']."</a>";
	  } else {
	    echo 'none';
	  }
	  ?>
	</td>
	<td>
	  <?php 
	  $top_item = top_file();
	  if (!empty($top_item) && $top_item != 'N/A') {
		echo "<a href='admin.php?page=items&amp;action=edit&amp;fid=".
		$top_item['FileID']."'>".$top_item['FileName']."</a>";
	  } else {
	    echo 'none';
	  }
	  ?>
	</td>
  </tr>
</table>

<h3>Recent Orders:</h3>
<table class="table table-striped table-bordered table-condensed">
  <tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Status</th>
    <th>Items</th>
    <th>Total</th>
  </tr>
  <?php
  $orders = list_new_orders(2);
  if (!empty($orders) && ($orders !== 'N/A')) {
	while ($row = mysqli_fetch_assoc($orders)) {
	  $cart_count = 0;
	  $customer_name = 'Unknown';
	  $customer = get_account_byid($row['AccountID']);
	  if (!empty($customer) && ($customer !== 'N/A')) {
		  $customer = mysqli_fetch_assoc($customer);
		  if (empty($customer['RealName'])) {
		    $customer_name = $customer['Email'];
		  } else {
			$customer_name = $customer['RealName'];
		  }
	  }
	  if (!empty($row['Cart'])) {
		$order_items = cart_items($row['Cart']);
		foreach ($order_items as $key => $val) {
		  $cart_count += $val;
		}
	  }
	  $row_link = 'admin.php?page=orders&amp;tid='.$row['OrderID'];
      echo "<tr><td><a href='$row_link'>".$row['OrderID']."</a></td>".
	  "<td>".safe_str($customer_name)."</td>".
	  "<td>".safe_str($row['Status'])."</td>".
	  "<td>$cart_count</td>".
	  "<td>".$row['Total']."&nbsp;BTC</td></tr>";
	}
  }
  ?>
</table>