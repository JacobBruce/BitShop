<?php admin_valid();

  $p = (!empty($_GET['p'])) ? $_GET['p'] : '1';
  $fid = (empty($_GET['fid'])) ? 0 : (int) $_GET['fid'];
  $cid = (empty($_GET['cid'])) ? 0 : (int) $_GET['cid'];
  
  if (!empty($_POST['codes'])) {
	$codes = explode("\n", $_POST['codes']); 
	foreach ($codes as $key => $value) {
	  if (!insert_code(trim($value), $fid, 0, 0)) {
	    $msg = "<p class='error_txt'>Failed to insert codes!</p>";
		break;
	  }
	}
	if ($file['FileMethod'] != 'keys') {
	  edit_file($fid, "FileStock = ".count_active_codes($fid));
	}
  }
  
  if (!empty($_GET['task'])) {
  
    if ($_SESSION['csrf_token'] === $_GET['toke']) {
	
	  if ($_GET['task'] === 'delcode') {
		if (delete_code($_GET['cid'])) {
		  $msg = "<p class='happy_txt'>Successfully deleted code!</p>";
		} else {
		  $msg = "<p class='error_txt'>Failed to delete code!</p>";
		}
		if ($file['FileMethod'] != 'keys') {
		  edit_file($fid, "FileStock = ".count_active_codes($fid));
		}
	  } elseif ($_GET['task'] === 'delall') {
		if (delete_codes($_GET['fid'])) {
		  $msg = "<p class='happy_txt'>Successfully deleted all codes!</p>";
		} else {
		  $msg = "<p class='error_txt'>Failed to delete all codes!</p>";
		}
		if ($file['FileMethod'] != 'keys') {
		  edit_file($fid, "FileStock = 0");
		}
	  } elseif ($_GET['task'] === 'toglock') {
        $down_key = preg_replace('/[^A-Za-z0-9]/i', '', $_GET['key']);	
        $lock_file = "uploads/down_logs/$down_key.log";
		$new_state = (int)$_GET['newstate'];
        if (file_exists($lock_file)) {
          $log_data = json_decode(file_get_contents($lock_file), true);
		  $log_data['last_reset'] = time();
		  $log_data['lock_count'] = $new_state;
		  $log_data['lock_state'] = $new_state;
		} else {
          $log_data = array(
	        'last_reset' => time(),
	        'last_ip' => '',
	        'hit_count' => 0,
	        'lock_count' => $new_state,
	        'lock_state' => $new_state
	      );
		}
		file_put_contents($lock_file, json_encode($log_data));
	  } elseif ($_GET['task'] === 'toggle') {
		update_code('Available = '.$_GET['newstate'], $cid);
		if ($file['FileMethod'] != 'keys') {
		  edit_file($fid, "FileStock = ".count_active_codes($fid));
		}
	  } elseif ($_GET['task'] === 'editcode') {
		$new_code = base64_decode($_GET['newcode']);
		if (update_code("CodeData = '".$new_code."'", $cid)) {
		  $msg = "<p class='happy_txt'>Successfully edited code!</p>";
		} else {
		  $msg = "<p class='error_txt'>Failed to edit code!</p>";
		}
	  } elseif ($_GET['task'] === 'newcode') {
		$new_code = base64_decode($_GET['newcode']);
		if (insert_code($new_code, $fid, 0, 0)) {
		  $msg = "<p class='happy_txt'>Successfully inserted new code!</p>";
		} else {
		  $msg = "<p class='error_txt'>Failed to insert code!</p>";
		}
		if ($file['FileMethod'] != 'keys') {
		  edit_file($fid, "FileStock = ".count_active_codes($fid));
		}
	  }
	} else {
	  $msg = "<p class='error_txt'>".LANG('INVALID_ACCESS')."!</p>";
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
  
  $codes = get_codes($fid, round(($curr_page-1) * 20));
  $page_num = (int) ceil(count_codes($fid) / 20);

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
    $nav_html = "<li$p_active><a href='admin.php?page=items&amp;action=edit&amp;fid=$fid&amp;suba=ecodes&amp;p=1'>First</a></li>";
    for ($i=$start_page;$i<=$end_page;$i++) {
	  $p_active = ($i == $curr_page) ? " class='active'" : '';
      $nav_html .= "<li$p_active><a href='admin.php?page=items&amp;action=edit&amp;fid=$fid&amp;suba=ecodes&amp;p=$i'>$i</a></li>";
    }
	$p_active = ($curr_page == $page_num) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='admin.php?page=items&amp;action=edit&amp;fid=$fid&amp;suba=ecodes&amp;p=$page_num'>Last</a></li>";
  }

  if (!empty($nav_html)) {
    echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
  }
?>

<p><b>List of codes:</b></p>
<?php
  if (!empty($msg)) { echo $msg; } 

  if (empty($codes) || $codes === 'N/A') {
    echo '<p>There are no codes for this product yet.</p>';
  } else {
?>

<table class='table table-striped table-bordered table-hover table-condensed'>
<tr>
  <th>Code ID</th>
  <th>Code</th>
  <th>Order</th>
  <th>Account</th>
  <th>Created</th>
  <th>Actions</th>
</tr>
<?php
    while ($row = mysqli_fetch_assoc($codes)) {
		
      $down_key = 'key'.preg_replace('/[^A-Za-z0-9]/i', '', $row['CodeData']);	
      $lock_file = "uploads/down_logs/$down_key.log";
	  $file_lock = 1;
	  
      if (file_exists($lock_file)) {
        $log_data = json_decode(file_get_contents($lock_file), true);
		if (isset($log_data['lock_state']) && $log_data['lock_state'] > 0)
		  $file_lock = 0;
	  }

	  $row_class = ($row['Available'] == 1) ? 'success' : 'error';

      echo "<tr class='$row_class'><td>".$row['CodeID'].
	  "</td><td>".$row['CodeData']."</td><td>".
	  (empty($row['OrderID']) ? 'n/a' : $row['OrderID']).
	  "</td><td>".(empty($row['AccountID']) ? 'n/a' : $row['AccountID']).
	  "</td><td>".$row['Created'].'</td><td><a href="#" onClick="update_code('.
	  $row['CodeID'].');">EDIT</a> | <a href="#" onClick="toggle_code_lock('.
	  "'$down_key',$file_lock);\">".($file_lock ? 'LOCK' : 'UNLOCK').
	  '</a> | <a href="#" onClick="toggle_code('.$row['CodeID'].", ".
	  ($row['Available'] ? '0' : '1').");\">".($row['Available'] ? 'DISABLE' : 'ENABLE').
	  '</a> | <a href="#" onClick="delete_code('.$row['CodeID'].');">REMOVE</a></td></tr>';
    }
?>
</table>
<?php } ?>
  
<p><a href="admin.php?page=items&action=edit&fid=<?php echo $_GET['fid']; ?>" title="Go back">BACK</a> | <a href="#" onClick="new_code();">ADD NEW CODE</a> | <a href="#" onClick="$('#codes_form').show();">ADD NEW CODES</a> | <a href="#" onClick="delete_all_codes();">DELETE ALL</a></p>

<form action="" method="post" name="codes_form" id="codes_form" target="_self">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <textarea id="codes" name="codes" maxlength="999999" style="width:390px;height:250px;"></textarea>
  <br />
  <button class="btn" type="submit" name="submit">submit</button>
</form>

<script language="JavaScript">
$('#codes_form').hide();

function delete_code(code_id) {
	if (confirm('Are you really sure you want to delete this code?')) {
		redirect('admin.php?page=items&action=edit&fid=<?php echo $fid; ?>&suba=ecodes&p=<?php echo $p; ?>&task=delcode&cid='+code_id+'&toke='+csrf_token);
	}
}

function delete_all_codes() {
	if (confirm('Are you really sure you want to delete all these codes?')) {
		redirect('admin.php?page=items&action=edit&fid=<?php echo $fid; ?>&suba=ecodes&task=delall&toke='+csrf_token);
	}
}

function toggle_code_lock(code_key, new_state) {
    if (new_state == 1) {
	  var conf_msg = 'This will lock the code for <?php echo $file_lock_time.' '.($file_lock_time > 1 ? 'days' : 'day'); ?>. Proceed?';
	} else {
	  var conf_msg = 'Are you sure you want to unlock this code?';
	}
	if (confirm(conf_msg)) {
		redirect('admin.php?page=items&action=edit&fid=<?php echo $fid; ?>&suba=ecodes&task=toglock&newstate='+new_state+'&key='+code_key+'&toke='+csrf_token);
	}
}

function toggle_code(code_id, new_state) {
    if (new_state == 1) {
	  var action = 'activate';
	} else {
	  var action = 'deactivate';
	}
	if (confirm('Are you sure you want to '+action+' this code?')) {
		redirect('admin.php?page=items&action=edit&fid=<?php echo $fid; ?>&suba=ecodes&p=<?php echo $p; ?>&task=toggle&newstate='+new_state+'&cid='+code_id+'&toke='+csrf_token);
	}
}

function update_code(code_id) {
	var new_code = prompt('Enter new value for this code:', '');
	if (new_code != null && new_code != '') {
		redirect('admin.php?page=items&action=edit&fid=<?php echo $fid; ?>&suba=ecodes&p=<?php echo $p; ?>&task=editcode&newcode='+encodeURIComponent(Base64.encode(new_code))+'&cid='+code_id+'&toke='+csrf_token);
	}
}

function new_code() {
	var new_code = prompt('Enter value for this code:', '');
	if (new_code != null && new_code != '') {
		redirect('admin.php?page=items&action=edit&fid=<?php echo $fid; ?>&suba=ecodes&p=<?php echo $p; ?>&task=newcode&newcode='+encodeURIComponent(Base64.encode(new_code))+'&toke='+csrf_token);
	}
}
</script>