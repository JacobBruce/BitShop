<?php admin_valid(); 
if (isset($_GET['edit']) && isset($_GET['id'])) {

  $acc_id = (int) $_GET['id'];

  if (!empty($_POST['email'])) {
    if (validate_maxlength($_POST['email'], 50)) {
	  set_account_email($acc_id, $_POST['email']);
	  echo "<p class='happy_txt'>Account email updated!</p>";
	} else {
	  echo "<p class='error_txt'>".LANG('INJECTED_EMAIL')."</p>";
	}
  }
  
  if (!empty($_POST['pass'])) {
	if ($_POST['pass'] === $_POST['pass2']) {
	  $pass_hash = pass_hash($_POST['pass'], $hash_rounds);
	  set_account_pass($acc_id, $pass_hash);
	  echo "<p class='happy_txt'>Account password updated!</p>";
	} else {
	  echo "<p class='error_txt'>".LANG('DO_NOT_MATCH')."</p>";
	}
  }
  
  if (!empty($_POST['group'])) {
    set_account_group($acc_id, $_POST['group']);
	echo "<p class='happy_txt'>Account user group updated!</p>";
  }

  $account = get_account_byid($acc_id);

  if (!empty($account) & $account !== 'N/A') {
    $account = mysqli_fetch_assoc($account);
?>

<h1>Edit Account</h1>

<form name="account_form" method="post" action="">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <label title="Email address of the account (used to login)."><b>New Email/Login:</b></label>
  <input type="text" name="email" value="<?php echo $account['Email']; ?>" maxlength="50" />
  <label title="The account login password."><b>New Password:</b></label>
  <input type="password" name="pass" value="" maxlength="99" />
  <label title="Repeat the account password."><b>Repeat Pass:</b></label>
  <input type="password" name="pass2" value="" maxlength="99" />
  <label title="Give the account different permission levels."><b>New User Group:</b></label> 
  <select name="group" <?php if ($acc_id == $account_id) { echo 'disabled'; } ?>>
	<option value="0" <?php if ($account['PermGroup'] == 0) { 
	echo 'selected="selected"'; } ?>>Basic User</option>
	<option value="1" <?php if ($account['PermGroup'] == 1) { 
	echo 'selected="selected"'; } ?>>Super User</option>
	<option value="2" <?php if ($account['PermGroup'] == 2) { 
	echo 'selected="selected"'; } ?>>Admin User</option>
  </select>
  <br /><br />
  <a class="btn" href="admin.php?page=accounts">Go Back</a> 
  <button class="btn" type="submit">Submit</button>
</form>

<?php
  } else {
    echo "<p class='error_txt'>The specified account does not exist!</p>";
  }
} elseif (isset($_GET['new'])) {

  if (!empty($_POST['email'])) {
	if (empty($_POST['pass'])) {
	  echo "<p class='error_txt'>".LANG('PASS_EMPTY')."</p>";
	} elseif ($_POST['pass'] !== $_POST['pass2']) {
	  echo "<p class='error_txt'>".LANG('DO_NOT_MATCH')."</p>";
	} elseif (!validate_maxlength($_POST['email'], 50)) {
	  echo "<p class='error_txt'>".LANG('INJECTED_EMAIL')."</p>";
	} else {
	  $account = get_account_byemail($_POST['email']);
	  if (empty($account) || ($account === 'N/A')){
		$pass_hash = pass_hash($_POST['pass'], $hash_rounds);
		$account_id = create_account($_POST['email'], $pass_hash, $_POST['group']);
		if ($account_id) {
		  echo "<p class='happy_txt'>Account successfully created!</p>";
		} else {
		  echo "<p class='error_txt'>".LANG('DATABASE_ERROR')."</p>";
		}
	  } else {
		echo "<p class='error_txt'>".LANG('ALREADY_IN_USE')."</p>";
	  }
	}
  }
  
  $user_group = empty($_POST['group']) ? 0 : (int)$_POST['group'];
  $acc_email = empty($_POST['email']) ? '' : $_POST['email'];
?>

<h1>New Account</h1>

<form name="account_form" method="post" action="">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <label title="Email address of the account (used to login)."><b>Email/Login:</b></label>
  <input type="text" name="email" value="<?php echo $acc_email; ?>" maxlength="50" required />
  <label title="The account login password."><b>Password:</b></label>
  <input type="password" name="pass" value="" maxlength="99" required />
  <label title="Repeat the account password."><b>Repeat Pass:</b></label>
  <input type="password" name="pass2" value="" maxlength="99" required />
  <label title="Give the account different permission levels."><b>User Group:</b></label> 
  <select name="group">
	<option value="0" <?php if (empty($user_group)) { 
	echo 'selected="selected"'; } ?>>Basic User</option>
	<option value="1" <?php if ($user_group == 1) { 
	echo 'selected="selected"'; } ?>>Super User</option>
	<option value="2" <?php if ($user_group == 2) { 
	echo 'selected="selected"'; } ?>>Admin User</option>
  </select>
  <br /><br />
  <a class="btn" href="admin.php?page=accounts">Go Back</a> 
  <button class="btn" type="submit">Submit</button>
</form>

<?php
} elseif (isset($_GET['id'])) {

  $acc_id = (int) $_GET['id'];
  $account = get_account_byid($acc_id);

  if (!empty($account) & $account !== 'N/A') {
    $account = mysqli_fetch_assoc($account);
	if ($account['AddressID'] > 0) {
	  $address = get_account_address($acc_id);
	  if (!empty($address) && $address !== 'N/A') {
	    $address = mysqli_fetch_assoc($address);
		$addr_str = address_string($address);
	  } else {
	    $addr_str = 'n/a';
	  }	  
	} else {
	  $addr_str = 'n/a';
	}
?>

<h1>Account <small>#<?php echo $acc_id; ?></small></h1>

<table class='table table-striped table-bordered table-condensed'>
<tr>
  <th>Email</th>
  <th>Enabled</th>
  <th>Type</th>
</tr>
<tr>
  <td><?php safe_echo($account['Email']); ?></td>
  <td><?php safe_echo($account['Enabled'] ? 'yes' : 'no'); ?></td>
  <td><?php safe_echo($group_perms[$account['PermGroup']]['group_name']); ?> user</td>
</tr>
<tr>
  <th>Date Created</th>
  <th>Last Logged</th>
  <th>Last IP</th>
</tr>
<tr>
  <td><?php safe_echo(format_time($account['Created'])); ?></td>
  <td><?php safe_echo(empty($account['LastTime']) ? 'n/a' : format_time($account['LastTime'])); ?></td>
  <td><?php safe_echo(empty($account['LastIP']) ? 'n/a' : $account['LastIP']); ?></td>
</tr>
<tr>
  <th>Address</th>
  <th>Real Name</th>
  <th>Phone</th>
</tr>
<tr>
  <td><?php echo str_replace("\n", ', ', safe_str($addr_str)); ?></td>
  <td><?php safe_echo(empty($account['RealName']) ? 'n/a' : $account['RealName']); ?></td>
  <td><?php safe_echo(empty($account['Phone']) ? 'n/a' : $account['Phone']); ?></td>
</tr>
</table>

<?php
if (empty($_GET['p'])) {
  $curr_page = 1;
} else {
  $curr_page = (int) $_GET['p'];
  if ($curr_page < 1) {
	$curr_page = 1;
  }
}

$orders = list_account_orders($acc_id, round(($curr_page-1) * 20));
$tran_num = count_account_orders($acc_id);
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
  $nav_html = "<li$p_active><a href='./admin.php?page=accounts&amp;id=$acc_id&amp;p=1'>First</a></li>";
  for ($i=$start_page;$i<=$end_page;$i++) {
	$p_active = ($i == $curr_page) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='./admin.php?page=accounts&amp;id=$acc_id&amp;p=$i'>$i</a></li>";
  }
  $p_active = ($curr_page == $page_num) ? " class='active'" : '';
  $nav_html .= "<li$p_active><a href='./admin.php?page=accounts&amp;id=$acc_id&amp;p=$page_num'>Last</a></li>";
}

if (!empty($nav_html)) {
  echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
}

echo '<h3>Order History</h3>';
  
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
	  case 'Callback Error':
	    $row_class = 'error';
	    break;
	  case 'Payment Pending':
	    $row_class = 'info';
	    break;
	  default:
	    $row_class = 'warning';
	  }
		
	  $row['Currency'] = empty($row['Currency']) ? 'BTC' : $row['Currency'];
	  $row['DatePaid'] = empty($row['DatePaid']) ? LANG('UPAID') : format_time($row['DatePaid']);
	  $row['TranCode'] = empty($row['TranCode']) ? LANG('NOT_APPLICABLE') : $row['TranCode'];			
	  $row_link = './admin.php?page=orders&amp;tid='.$row['OrderID'];
	  
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
  echo '<p>No orders have been made by this account yet.</p>';
}
?>

<p><a class='btn' href='admin.php?page=accounts' title='Go back'>BACK</a></p>

<?php
  } else {
    echo "<p class='error_txt'>The specified account does not exist!</p>";
  }
} else {

  if (!empty($_GET['task'])) {
  
    if ($_SESSION['csrf_token'] !== $_GET['toke']) {
	
	  echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
	  
	} elseif ($_GET['task'] === 'toggle') {
	
	  if ($_GET['newstate'] == 1) {
		if (enable_account($_GET['aid'])) {
		  echo "<p class='happy_txt'>Account successfully enabled!</p>";
		} else {
		  echo "<p class='error_txt'>There was a problem activating the account!</p>";
		}
	  } else {
		if (disable_account($_GET['aid'])) {
		  echo "<p class='happy_txt'>Account successfully disabled!</p>";
		} else {
		  echo "<p class='error_txt'>There was a problem disabling the account!</p>";
		}
	  }
	  
	} elseif ($_GET['task'] === 'remove') {

	  if (remove_account($_GET['aid'])) {
		echo "<p class='happy_txt'>Account successfully removed!</p>";
	  } else {
		echo "<p class='error_txt'>There was a problem removing the account!</p>";
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

  if (isset($_GET['q'])) {
    $accounts = search_accounts($_GET['q'], round(($curr_page-1) * 20));
    $account_num = ($accounts != 'N/A') ? $accounts->num_rows : 0;
  } else {
    $accounts = list_all_accounts(round(($curr_page-1) * 20));
    $account_num = count_accounts();
  }
  $page_num = (int) ceil($account_num / 20);

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
	$nav_html = "<li$p_active><a href='admin.php?page=accounts&amp;p=1'>First</a></li>";
	for ($i=$start_page;$i<=$end_page;$i++) {
	  $p_active = ($i == $curr_page) ? " class='active'" : '';
	  $nav_html .= "<li$p_active><a href='admin.php?page=accounts&amp;p=$i'>$i</a></li>";
	}
	$p_active = ($curr_page == $page_num) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='admin.php?page=accounts&amp;p=$page_num'>Last</a></li>";
  }

  if (!empty($nav_html)) {
	echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
  }
?>

<div id="account_search">
  <form class="navbar-search" name="acc_search_form" method="get" action="admin.php">
    <input type="hidden" name="page" value="accounts">
    <input type="text" value="" name="q" maxlength="50" class="search-query" placeholder="search accounts">
  </form>
</div>

<h1>Accounts</h1>
<?php if (!empty($account_num)) { ?>
<p><b>List of accounts:</b></p>

<table class='table table-striped table-bordered table-hover table-condensed'>
<tr>
  <th>Email Address</th>
  <th>Real Name</th>
  <th>Date Created</th>
  <th>Actions</th>
</tr>
<?php
	if (!empty($accounts) && ($accounts !== 'N/A')) {
	  while ($row = mysqli_fetch_assoc($accounts)) {
	  
		if ($row['Enabled']) {
		  $toggle = 'DISABLE';
		  $row_class = 'success';
		  $action = 0;
		} else {
		  $toggle = 'ENABLE';
		  $row_class = 'error';
		  $action = 1;
		}
			
		$row['RealName'] = empty($row['RealName']) ? 'n/a' : $row['RealName'];
		$row_link = 'admin.php?page=accounts&amp;id='.$row['AccountID'];
		
		echo "<tr class='$row_class'><td><a href='$row_link'>".
		$row['Email']."</a></td><td>".safe_str($row['RealName']).
		"</td><td>".format_time($row['Created'])."</td><td>".
		"<a href='#' onclick='toggle_account(".$row['AccountID'].
		", $action);'>$toggle</a> | <a href='#' onclick='".
		"remove_account(".$row['AccountID'].")'>REMOVE</a> | ".
		"<a href='./admin.php?page=accounts&amp;id=".
		$row['AccountID']."&amp;edit'>EDIT</a></td></tr>";
	  }
	}
?>
</table>

<?php
  } else {
	if (isset($_GET['q'])) {
	  echo "<p>No accounts were found.</p>";
	} else {
	  echo "<p>There are no accounts yet.</p>";
	}
  }
?>
  
<p><a class="btn" href="admin.php?page=home">Go Back</a> 
<a class="btn" href="admin.php?page=accounts&amp;new">New Account</a></p>

<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

function remove_account(acc_id) {
	if (confirm('This action will permanently remove the account from your database. Continue?')) {
	  redirect('admin.php?page=accounts&task=remove&aid='+acc_id+'&toke='+csrf_token);
	}
}

function toggle_account(acc_id, new_state) {
	if (new_state == 1) { var action = 'enable'; } else { var action = 'disable'; }
	if (confirm('Are you sure you want to '+action+' this account?')) {
		redirect('admin.php?page=accounts&task=toggle&newstate='+new_state+'&aid='+acc_id+'&p=<?php echo $curr_page; ?>&toke='+csrf_token);
	}
}
</script>

<?php } ?>