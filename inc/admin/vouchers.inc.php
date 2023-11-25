<?php admin_valid();

if (isset($_GET['new']) || isset($_GET['edit'])) {

  if (!empty($_POST['code'])) {
	$_POST['discount'] = ($_POST['disc_curr'] == 'per') ? $_POST['discount'] : '-'.$_POST['discount'];
	$_POST['credits'] = isset($_POST['credits']) ? $_POST['credits'] : '0';
    if (isset($_POST['edit_vouch'])) {
      if (!edit_voucher($_GET['id'], $_POST['name'], $_POST['code'], $_POST['discount'], 
	  $_POST['item_id'], $_POST['target'], $_POST['type'], $_POST['credits'])) {
	    echo "<p class='error_txt'>Failed to update voucher/coupon!</p>";
      } else {
        echo "<p class='happy_txt'>Voucher/coupon successfully updated</p>";
      }
	} else {
	  $voucher = get_voucher_bycode($_POST['code']);
	  if (empty($voucher) || $voucher === 'N/A') {
        if (!create_voucher($_POST['name'], $_POST['code'], $_POST['discount'], 
	    $_POST['item_id'], $_POST['target'], $_POST['type'], $_POST['credits'])) {
	      $error = "<p class='error_txt'>Failed to create new voucher/coupon!</p>";
	    }
	  } else {
	    $error = "<p class='error_txt'>Voucher/coupon already exists!</p>";
	  }
      if (isset($error)) { 
        echo $error; 
      } else {
        echo "<p class='happy_txt'>Voucher/coupon successfully created</p>";
      }
	}
  }
  
  if (isset($_GET['edit'])) { 
    echo '<h1>Edit Voucher</h1>';
	$vouch = get_voucher_byid($_GET['id']);
	if (!empty($vouch) && $vouch !== 'N/A') {
	  $vouch = mysqli_fetch_assoc($vouch);
	  $vname = $vouch['Name'];
	  $vcode = $vouch['CodeData'];
	  $vtarg = $vouch['Target'];
	  $vtype = $vouch['UseType'];
	  $vdisc = $vouch['Discount'];
	  $vitem = $vouch['ItemID'];
	  $vcred = $vouch['Credits'];
	} else {
	   echo "<p class='error_txt'>Voucher/coupon already exists!</p>";
	}
  } else {
    echo '<h1>New Voucher</h1>';
	  $vname = '';
	  $vcode = '';
	  $vtarg = '';
	  $vtype = '';
	  $vdisc = '';
	  $vitem = '';
	  $vcred = '';
  }
?>

<form name="add_form" method="post" action="">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <?php if (isset($_GET['edit'])) { echo '<input type="hidden" name="edit_vouch" value="true" />'; } ?>
  <label title="The name of the voucher/coupon."><b>Name:</b></label>
  <input type="text" name="name" value="<?php echo $vname; ?>" maxlength="50" required />
  <label title="The voucher/coupon code."><b>Code:</b></label>
  <input type="text" name="code" value="<?php echo $vcode; ?>" maxlength="64" required />
  <label title="ID of the item this voucher/coupon targets (0 to discount totals)."><b>Item ID:</b></label>
  <input type="text" name="item_id" value="<?php echo $vitem; ?>" maxlength="20" required />
  <label title="Voucher/coupon discount value (percentage or fiat value)."><b>Discount:</b></label>
  <div class="form-inline bot_gap">
    <input type="text" name="discount" class="input-medium" value="<?php echo trim($vdisc, '-'); ?>" maxlength="30" style="width:142px" required /> 
    <select name="disc_curr" style="width:60px">
	  <option value="per" <?php if ($vdisc === '' || $vdisc[0] !== '-') { echo 'selected="selected"'; } ?>>%</option>
	  <option value="cur" <?php if ($vdisc !== '' && $vdisc[0] === '-') { echo 'selected="selected"'; } ?>><?php echo $curr_code; ?></option>
    </select>
  </div>
  <label title="Discount the item price or shipping price."><b>Target:</b></label> 
  <select name="target">
	<option value="0" <?php if (empty($vtarg) || !$vtarg) { echo 'selected="selected"'; } ?>>Item Price</option>
	<option value="1" <?php if ($vtarg) { echo 'selected="selected"'; } ?>>Shipping Price</option>
  </select>
  <label title="Choose the number of allowed uses."><b>Type:</b></label> 
  <select name="type" id="type_list">
	<option value="1" <?php if ($vtype === '' || $vtype == 1) { echo 'selected="selected"'; } ?>>One-Time use (voucher)</option>
	<option value="2" <?php if ($vtype == 2) { echo 'selected="selected"'; } ?>>Limited uses (coupon)</option>
	<option value="0" <?php if ($vtype == 0) { echo 'selected="selected"'; } ?>>Unlimited uses (coupon)</option>
  </select>
  <label title="The number of allowed uses before being disabled."><b>Credits:</b></label>
  <input type="text" name="credits" id="credits" value="<?php echo $vcred; ?>" maxlength="20" required <?php if ($vtype != 2) { echo 'disabled'; } ?> />
  <br /><br />
  <a class="btn" href="admin.php?page=vouchers">Go Back</a> 
  <button class="btn" type="submit">Submit</button>
</form>

<script language="JavaScript">
$('#type_list').on('change', function() {
  if ($(this).val() == '2') {
    $('#credits').removeAttr('disabled');
  } else {
    $('#credits').attr('disabled', true);
  }
});
</script>

<?php
} elseif (isset($_GET['id'])) {

  $vid = (int) $_GET['id'];
  $vouch = get_voucher_byid($vid);
  if (!empty($vouch) && $vouch !== 'N/A') {
    $vouch  = mysqli_fetch_assoc($vouch);
	
	if ($vouch['Discount'][0] === '-') {
	  $discount = trim($vouch['Discount'], '-')." $curr_code";
	} else {
	  $discount = trim(trim($vouch['Discount'], '0'), '.').'%';
	}
	
	if ($vouch['ItemID'] > 0) {
	  if ($vouch['Target']) {
	    $description = "$discount discount on item shipping cost";
	  } else {
	    $description = "$discount discount on item price";
	  }
	} else {
	  if ($vouch['Target']) {
	    $description = "$discount discount on total shipping cost";
	  } else {
	    $description = "$discount discount on total price";
	  }
	}
	
	if ($vouch['ItemID'] > 0) {
	  $item = get_file($vouch['ItemID']);
	  if (!empty($item) && $item !== 'N/A') {
	    $item = mysqli_fetch_assoc($item);	
	    $item_link = "<a href='./admin.php?page=items&amp;action=edit&amp;fid=".
	                 $vouch['ItemID']."'>".$item['FileName']."</a>";
	  } else {
	    $item_link = 'Item Not Found';
	  }
	} else {
	  $item_link = 'Not Required';
	}
	
	switch($vouch['UseType']) {
	  case 0: $vouch_type = 'Unlimited uses (coupon)'; break;
	  case 1: $vouch_type = 'One-Time use (voucher)'; break;
	  case 2: $vouch_type = 'Limited uses (coupon)'; break;
	  default: $vouch_type = 'Unknown'; break;
	}
?>

<h1>Voucher <small>#<?php echo $vid; ?></small></h1>
<table class='table table-striped table-bordered table-condensed'>
<tr>
  <th>Name</th>
  <th>Description</th>
</tr>
<tr>
  <td><?php safe_echo($vouch['Name']); ?></td>
  <td><?php safe_echo($description); ?></td>
</tr>
<tr>
  <th>Code</th>
  <th>Item</th>
</tr>
<tr>
  <td><?php safe_echo($vouch['CodeData']); ?></td>
  <td><?php echo $item_link; ?></td>
</tr>
<tr>
  <th>Type</th>
  <th>Credits</th>
</tr>
<tr>
  <td><?php echo $vouch_type; ?></td>
  <td><?php safe_echo($vouch['Credits']); ?></td>
</tr>
</table>

<a class="btn" href="admin.php?page=vouchers">Go Back</a> 
<a class="btn" href="admin.php?page=vouchers&amp;id=<?php echo $vid; ?>&amp;edit">Edit Voucher</a>

<?php
  } else {
    echo "<p class='error_txt'>Voucher/coupon does not exist.</p>";
  }
} else {

  if (!empty($_GET['task'])) {
  
    if ($_SESSION['csrf_token'] !== $_GET['toke']) {

	  echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";

    } elseif ($_GET['task'] === 'toggle') {

	  if ($_GET['newstate'] == 1) {
		if (enable_voucher($_GET['vid'])) {
		  echo "<p class='happy_txt'>Voucher successfully enabled!</p>";
		} else {
		  echo "<p class='error_txt'>There was a problem activating the voucher!</p>";
		}
	  } else {
		if (disable_voucher($_GET['vid'])) {
		  echo "<p class='happy_txt'>Voucher successfully disabled!</p>";
		} else {
		  echo "<p class='error_txt'>There was a problem deactivating the voucher!</p>";
		}
	  }

	} elseif ($_GET['task'] === 'remall') {

	  if (remove_vouchers()) {
		echo "<p class='happy_txt'>All vouchers successfully removed!</p>";
	  } else {
		echo "<p class='error_txt'>There was a problem removing the vouchers!</p>";
	  }

	} elseif ($_GET['task'] === 'remove') {

	  if (remove_voucher($_GET['vid'])) {
		echo "<p class='happy_txt'>Voucher successfully removed!</p>";
	  } else {
		echo "<p class='error_txt'>There was a problem removing the voucher!</p>";
	  }
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
  
  $vouchers = list_vouchers(round(($curr_page-1) * 20));
  $voucher_num = count_vouchers();
  $page_num = (int) ceil($voucher_num / 20);

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
    $nav_html = "<li$p_active><a href='admin.php?vouchers&amp;p=1'>First</a></li>";
    for ($i=$start_page;$i<=$end_page;$i++) {
	  $p_active = ($i == $curr_page) ? " class='active'" : '';
      $nav_html .= "<li$p_active><a href='admin.php?page=vouchers&amp;p=$i'>$i</a></li>";
    }
	$p_active = ($curr_page == $page_num) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='admin.php?page=vouchers&amp;p=$page_num'>Last</a></li>";
  }

  if (!empty($nav_html)) {
    echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
  }

  if (!empty($msg)) { echo $msg; }
  if (!empty($vouchers) && ($vouchers != 'N/A')) {
?>

<h1>Vouchers</h1>
<p><b>List of vouchers/coupons:</b></p>

<table class='table table-striped table-bordered table-condensed'>
<tr>
  <th>Name</th>
  <th>Code</th>
  <th>Actions</th>
</tr>

<?php
    while ($row = mysqli_fetch_assoc($vouchers)) {
	
	  if ($row['Enabled']) {
	    $toggle = 'DISABLE';
		$row_class = 'success';
		$action = 0;
	  } else {
	    $toggle = 'ENABLE';
		$row_class = 'error';
		$action = 1;
	  }
	  
	  $row_link = './admin.php?page=vouchers&amp;id='.$row['VouchID']; 
	  echo "<tr class='$row_class'><td><a href='$row_link'>".$row['Name']."</a></td>".
	  "<td>".safe_str($row['CodeData'])."</td><td><a href='./admin.php?page=vouchers".
	  "&amp;id=".$row['VouchID']."&amp;edit'>EDIT</a> | <a href='#' onclick=\"toggle_".
	  "voucher(".$row['VouchID'].", $action);\">$toggle</a> | <a href='#' onClick='".
	  "remove_voucher(".$row['VouchID'].")'>REMOVE</a></td></tr>";
	}
?>
</table>

<p><a class="btn" href="admin.php?page=vouchers">Go Back</a> 
<a class="btn" href="admin.php?page=vouchers&amp;new">New Voucher</a> 
<a class="btn" href="#" onClick="remove_vouchers();">Remove All</a></p>

<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

function remove_voucher(vouch_id) {
	if (confirm('This action will permanently remove the voucher from your database. Continue?')) {
	  redirect('admin.php?page=vouchers&task=remove&vid='+vouch_id+'&toke='+csrf_token);
	}
}

function remove_vouchers() {
	if (confirm('This action will permanently remove ALL the vouchers from your database. Continue?')) {
	  redirect('admin.php?page=vouchers&task=remall&toke='+csrf_token);
	}
}

function toggle_voucher(vouch_id, new_state) {
	if (new_state == 1) { var action = 'enable'; } else { var action = 'disable'; }
	if (confirm('Are you sure you want to '+action+' this voucher/coupon?')) {
		redirect('admin.php?page=vouchers&task=toggle&newstate='+new_state+'&vid='+vouch_id+'&p=<?php echo $curr_page; ?>&toke='+csrf_token);
	}
}
</script>

<?php
  } else {
    echo '<h1>Vouchers</h1><p>There are no vouchers yet.</p>';
    echo '<p><a class="btn" href="admin.php?page=vouchers">Go Back</a> ';
	echo '<a class="btn" href="admin.php?page=vouchers&amp;new">New Voucher</a></p>';
  }
}
?>
