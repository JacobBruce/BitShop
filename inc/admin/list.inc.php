<?php admin_valid();

if (isset($_GET['new'])) {
  if (!empty($_POST['addresses'])) {
    $addresses = explode("\n", $_POST['addresses']);
    foreach ($addresses as $key => $value) {
	  $address = trim($value);
	  $db_addr = get_add_byadd($address);
	  if (empty($db_addr) || $db_addr === 'N/A') {
	    if (bitcoin::checkAddress($address)) {
          if (!create_btc_add($address)) {
	        $error = "<p class='error_txt'>Failed to create new addresses!</p>";
	        break;
	      }
		} else {
	      $error = "<p class='error_txt'>$address is not a valid address!</p>";
		}
	  } else {
	    $error = "<p class='error_txt'>$address is already in list!</p>";
	  }
    }
    if (isset($error)) { 
      echo $error; 
    } else {
      echo "<p class='happy_txt'>Addresses successfully added to list</p>";
    }
  }
?>

<h1>New Addresses</h1>
<form name="add_form" method="post" action="">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <label><b>Bitcoin addresses (1 per line):</b></label>
  <textarea name="addresses"></textarea><br />
  <a class="btn" href="admin.php?page=wallet&amp;action=list">Go Back</a> 
  <button class="btn" type="submit">Submit</button>
</form>

<?php
} else {

  if (!empty($_GET['task'])) {
  
    if ($_SESSION['csrf_token'] === $_GET['toke']) {
	
	  if ($_GET['task'] === 'enable') {
		if (enable_address($_GET['aid'])) {
		  echo "<p class='happy_txt'>Address successfully enabled!</p>";
		} else {
		  echo "<p class='error_txt'>There was a problem activating the address!</p>";
		}
	  } elseif ($_GET['task'] === 'disable') {
		if (disable_address($_GET['aid'])) {
		  echo "<p class='happy_txt'>Address successfully disabled!</p>";
		} else {
		  echo "<p class='error_txt'>There was a problem deactivating the address!</p>";
		}
	  }
	  
	  if ($_GET['task'] == 'remall') {
		if (remove_all_adds()) {
		  echo "<p class='happy_txt'>All addresses successfully removed!</p>";
		} else {
		  echo "<p class='error_txt'>There was a problem removing the addresses!</p>";
		}
	  } elseif ($_GET['task'] == 'remove') {
		if (remove_btc_add($_GET['aid'])) {
		  echo "<p class='happy_txt'>Address successfully removed!</p>";
		} else {
		  echo "<p class='error_txt'>There was a problem removing the address!</p>";
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
  
  $addresses = list_btc_adds(round(($curr_page-1) * 20));
  $address_num = count_btc_adds();
  $page_num = (int) ceil($address_num / 20);

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
    $nav_html = "<li$p_active><a href='admin.php?wallet&amp;action=list&amp;p=1'>First</a></li>";
    for ($i=$start_page;$i<=$end_page;$i++) {
	  $p_active = ($i == $curr_page) ? " class='active'" : '';
      $nav_html .= "<li$p_active><a href='admin.php?page=wallet&amp;action=list&amp;p=$i'>$i</a></li>";
    }
	$p_active = ($curr_page == $page_num) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='admin.php?page=wallet&amp;action=list&amp;p=$page_num'>Last</a></li>";
  }

  if (!empty($nav_html)) {
    echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
  }
?>

<h1>Address List</h1>
<p><b>Custom list of Bitcoin addresses:</b></p>

<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

function remove_address(aid) {
	if (confirm('This action will permanently remove the address from your database. Continue?')) {
	  redirect('admin.php?page=wallet&action=list&task=remove&aid='+aid+'&toke='+csrf_token);
	}
}

function remove_addresses() {
	if (confirm('This action will permanently remove ALL the addresses from your database. Continue?')) {
	  redirect('admin.php?page=wallet&action=list&task=remall&toke='+csrf_token);
	}
}

function toggle_address(aid, action) {
	if (confirm('Are you sure you want to '+action+' this address?')) {
		redirect('./admin.php?page=wallet&action=list&task='+action+'&aid='+aid+'&toke='+csrf_token);
	}
}
</script>

<?php
  if (!empty($msg)) { echo $msg; }
  if (!empty($addresses) && ($addresses != 'N/A')) {
?>

<table class='table table-striped table-bordered table-condensed'>
<tr>
  <th>Address</th>
  <th>Actions</th>
</tr>

<?php
    while ($row = mysqli_fetch_assoc($addresses)) {
	
	  if ($row['Enabled']) {
	    $toggle = 'DISABLE';
		$row_class = 'success';
		$action = 'disable';
	  } else {
	    $toggle = 'ENABLE';
		$row_class = 'error';
		$action = 'enable';
	  }
	  
	  echo "<tr class='$row_class'>
	  <td>".$row['Address']."</td>
	  <td><a href='#' onclick='toggle_address(".
	  $row['AddID'].", \"$action\")'>$toggle</a> | 
	  <a href='#' onclick='remove_address(".
	  $row['AddID'].")'>REMOVE</a>
	  </td>
	</tr>";
	}
?>
</table>

<p><a class="btn" href="admin.php?page=wallet">Go Back</a> 
<a class="btn" href="admin.php?page=wallet&amp;action=list&amp;new">Add Addresses</a> 
<a class="btn" href="#" onClick="remove_addresses();">Remove All</a></p>

<?php
  } else {
    echo '<p>There are no addresses yet.</p><p><a class="btn" href="admin.php?page=wallet">Go Back</a> ';
	echo '<a class="btn" href="admin.php?page=wallet&amp;action=list&amp;new">Add Addresses</a></p>';
  }
  
  echo "<p><b>IMPORTANT:</b> to use this list of addresses instead of auto-generated addresses you must edit the gateway settings and set 'Use Address List' to true. Each time one of these addresses is used to receive a payment it will be disabled. Once all of the addresses in the list have been disabled you will no longer be able to receive payments so you must add new addresses or re-enable used addresses periodically. If you do re-use an address you must first make sure to withdraw any coins from that address because it must have a 0 balance to receive payments.</p>";
}
?>