<?php if (login_state() === 'valid') {

if (!empty($_GET['id'])) {
    $oid = safe_sql_str($_GET['id']);
    $order = get_order_byid($oid);
    if (!empty($order) && ($order !== 'N/A')) {
      $order = mysqli_fetch_assoc($order);
	  if ($order['AccountID'] == $account_id) {
	    if ($order['Status'] !== 'Unconfirmed') {
	      $key_data = explode(':', $order['KeyData']);
	      $destination = $key_data[1];
	      $order['DatePaid'] = empty($order['DatePaid']) ? LANG('UPAID') : format_time($order['DatePaid']);
	      $order['TranCode'] = empty($order['TranCode']) ? LANG('NOT_APPLICABLE') : $order['TranCode'];	
	      $order['Currency'] = empty($order['Currency']) ? 'BTC' : $order['Currency'];
	      $amount_paid = $order['Amount'].' '.$order['Currency']; 
		}
		$item_list = list_cart_items($order['Cart']);
	  } else {
        $error_str = "<div class='alert alert-error'><button type='button' class='close' ".
	    "data-dismiss='alert'>&times;</button>".LANG('TRAN_NOT_FOUND')."</div>";
	  }
    } else {
      $error_str = "<div class='alert alert-error'><button type='button' class='close' ".
	  "data-dismiss='alert'>&times;</button>".LANG('TRAN_NOT_FOUND')."</div>";
    }
	
	if (!isset($error_str)) {
?>

<h1><?php echo LANG('STATUS_TITLE'); ?> <small><?php echo LANG('ORDER')." #$oid"; ?></small></h1>

<?php if ($order['Status'] !== 'Unconfirmed') { ?>
<p><b><?php echo LANG('TRAN_CODE'); ?></b>: <?php safe_echo($order['TranCode']); ?>
<br /><b><?php echo LANG('PAY_STATUS'); ?></b>: <?php safe_echo($order['Status']); 
if (bccomp($order['Shipping'], '-1') != 0) { ?><br />
<b><?php echo LANG('SHIP_STATUS'); ?></b>: <?php safe_echo($order['ShipStatus']); } ?>
<br /><b><?php echo LANG('DATE_PAID'); ?></b>: <?php safe_echo($order['DatePaid']); 
if ($order['DatePaid'] !== LANG('UPAID')) { ?><br />
<b><?php echo LANG('TOTAL_PAID'); ?></b>: <?php safe_echo($amount_paid); ?><br />
<b><?php echo LANG('PAID_TO'); ?></b>: <?php safe_echo($destination); } ?></p>
<?php } else { echo '<p>'.LANG('TRAN_NOT_CONFIRMED').'</p>'; } ?>

<h3><?php echo LANG('ITEMS_PURCHASED'); ?></h3>

<?php
	  echo '<p>'.implode('<br />', $item_list).'</p>';
	  echo '<p><a class="btn" href="./?page=account&amp;action=myorders">'.LANG('GO_BACK').'</a></p>';
	} else {
	  echo $error_str;
	}
  }
  
  if (empty($_GET['id']) || !empty($error_str)) {

    if (empty($_GET['p'])) {
      $curr_page = 1;
    } else {
      $curr_page = (int) $_GET['p'];
	  if ($curr_page < 1) {
	    $curr_page = 1;
	  }
    }
  
    $orders = list_account_orders($account_id, round(($curr_page-1) * 20));
    $tran_num = count_account_orders($account_id);
    $page_num = (int) ceil($tran_num / 20);

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
      $nav_html = "<li$p_active><a href='./?page=account&amp;action=myorders&amp;p=1'>First</a></li>";
      for ($i=$start_page;$i<=$end_page;$i++) {
	    $p_active = ($i == $curr_page) ? " class='active'" : '';
        $nav_html .= "<li$p_active><a href='./?page=account&amp;action=myorders&amp;p=$i'>$i</a></li>";
      }
	  $p_active = ($curr_page == $page_num) ? " class='active'" : '';
	  $nav_html .= "<li$p_active><a href='./?page=account&amp;action=myorders&amp;p=$page_num'>Last</a></li>";
    }
	
    if (!empty($nav_html)) {
      echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
    }
  
    echo '<h1>'.LANG('MY_ORDERS').'</h1>';
	  
    if (!empty($tran_num)) {
?>

<table class='table table-striped table-bordered table-hover table-condensed'>
<tr>
  <th><?php echo LANG('ID'); ?></th>
  <th><?php echo LANG('STATUS'); ?></th>
  <th><?php echo LANG('DATE_PAID'); ?></th>
  <th><?php echo LANG('TOTAL_PAID'); ?></th>
  <th><?php echo LANG('TRAN_CODE'); ?></th>
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
		  default:
			$row_class = 'warning';
		  }
			
		  $row['Currency'] = empty($row['Currency']) ? 'BTC' : $row['Currency'];
	      $row['DatePaid'] = empty($row['DatePaid']) ? LANG('UPAID') : format_time($row['DatePaid']);
	      $row['TranCode'] = empty($row['TranCode']) ? LANG('NOT_APPLICABLE') : $row['TranCode'];			
		  $row_link = './?page=account&amp;action=myorders&amp;id='.$row['OrderID'];
		  
		  if (strlen($row['TranCode']) > 16) {
		    $row['TranCode'] = substr($row['TranCode'], 0, 13).'...';
		  }
			
          echo "<tr class='$row_class tr_link' onclick=\"document.location='$row_link';\"><td>".
		  "<a href='$row_link'>".$row['OrderID']."</a></td><td>".$row['Status']."</td><td>".
	      str_replace(' ', '&nbsp;', $row['DatePaid'])."</td><td>".$row['Amount']."&nbsp;".
		  $row['Currency']."<small>&nbsp;</small></td><td>".$row['TranCode']."</td></tr>";
        }
      }
		
      echo '</table>';
	  
    } else {
      echo '<p>'.LANG('NO_ORDERS_YET').'</p>';
    }
	
    echo '<p><a class="btn" href="./?page=account">'.LANG('GO_BACK').'</a></p>';	
  }

} else {
  require_once('./inc/pages/login.inc.php');
}
?>