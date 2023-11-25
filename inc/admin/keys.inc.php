<?php admin_valid();
  
  if (!empty($_GET['remove'])) {
    if ($_SESSION['csrf_token'] !== $_GET['toke']) {
	  echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
    } elseif ($_GET['remove'] == 'all') {
      if (remove_all_keys()) {
	    echo "<p class='happy_txt'>All keys successfully removed!</p>";
	  } else {
	    echo "<p class='error_txt'>There was a problem removing the keys!</p>";
	  }
	} else {
	  if (remove_key_data($_GET['remove'])) {
	    echo "<p class='happy_txt'>Key successfully removed!</p>";
	  } else {
	    echo "<p class='error_txt'>There was a problem removing the key!</p>";
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
  
  $order_keys = list_conf_keys(round(($curr_page-1) * 20));
  $key_num = count_conf_keys();
  $page_num = (int) ceil($key_num / 20);

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
    $nav_html = "<li$p_active><a href='admin.php?wallet&amp;action=keys&amp;p=1'>First</a></li>";
    for ($i=$start_page;$i<=$end_page;$i++) {
	  $p_active = ($i == $curr_page) ? " class='active'" : '';
      $nav_html .= "<li$p_active><a href='admin.php?page=wallet&amp;action=keys&amp;p=$i'>$i</a></li>";
    }
	$p_active = ($curr_page == $page_num) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='admin.php?page=wallet&amp;action=keys&amp;p=$page_num'>Last</a></li>";
  }

  if (!empty($nav_html)) {
    echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
  }
?>

<h1>Bitcoin Keys</h1>
<p><b>List of addresses and private keys:</b></p>

<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

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

function decrypt_key(pkd_id) {
	var priv_key = prompt('Private Key:', '');
	var key_str = $('#pkd'+pkd_id).html();
	$('#pkd'+pkd_id).html(do_decrypt(priv_key, key_str));
}

function decrypt_keys() {
	var priv_key = prompt('Private Key:', '');
	var key_str = '';
	var pkd_id = 0;
	var pkd = 0;
	while (pkd = $('#pkd'+pkd_id).length) {
	  key_str = $('#pkd'+pkd_id).html();
	  $('#pkd'+pkd_id).html(do_decrypt(priv_key, key_str));
	  pkd_id++;
	}
}

function remove_key(tid) {
	if (confirm('This action will permanently remove the key from your database. Continue?')) {
	  redirect('admin.php?page=wallet&action=keys&remove='+tid+'&toke='+csrf_token);
	}
}

function remove_keys() {
	if (confirm('This action will permanently remove ALL the keys from your database. Continue?')) {
	  redirect('admin.php?page=wallet&action=keys&remove=all&toke='+csrf_token);
	}
}

function export_keys() {
	redirect('admin.php?page=wallet&action=keys&export');
}

function exp_keys() {
	var priv_key = prompt('Private Key:', '');
	var decr_key = '';
	var add_str = '';
	var key_str = '';
	var result0 = '';
	var result1 = '';
	var result2 = "{\n\t\"keys\" : [";
	var pk_id = 0;
	var pkd = 0;
	while (pkd = $('#pk_d'+pk_id).length) {
	  add_str = $('#pk_a'+pk_id).html().trim();
	  key_str = $('#pk_d'+pk_id).html().trim();
	  decr_key = do_decrypt(priv_key, key_str);
	  result0 += decr_key+'\n';
	  result1 += (pk_id+1)+',"'+add_str+'","'+decr_key+'"\n';
	  result2 += "\n\t\t{\"addr\" : \""+add_str+"\",\n\t\t \"priv\" : \""+decr_key+"\"},";
	  pk_id++;
	}
	result2 = result2.slice(0, -1)+"\n\t]\n}";
	$('#exp_con').show();
	$('#exp_box0').html(result0);
	$('#exp_box1').html(result1);
	$('#exp_box2').html(result2);
}
</script>

<p><a href="admin.php?page=wallet" title="Go back">BACK</a> | <a href="#" onClick="decrypt_keys();">DECRYPT KEYS</a> | <a href="#" onClick="export_keys();">EXPORT KEYS</a> | <a href="#" onClick="remove_keys();">REMOVE ALL</a></p>

<?php
  if (!empty($msg)) { echo $msg; }
  
  if (!empty($order_keys) && ($order_keys != 'N/A')) {
	$pkn = 0;
    while ($row = mysqli_fetch_assoc($order_keys)) {
	  $keys = explode(':', $row['KeyData']);
	  if ($keys[0] !== 'empty') {
?>
<table class='table table-striped table-bordered table-condensed'>
<tr>
  <th align='center'>Address</th>
  <th align='center'>Private Key</th>
</tr>
<tr>
  <td>
    <div id="pka<?php echo $pkn; ?>" style="width:300px;overflow:auto;">
      <?php echo $keys[1]; ?>
    </div>
  </td>
  <td>
    <div id="pkd<?php echo $pkn; ?>" style="width:300px;overflow:auto;">
      <?php echo $keys[0]; ?>
    </div>
  </td>
</tr>
<tr>
  <th align='center'>Balance*</th>
  <th align='center'>Actions</th>
</tr>
<tr>
  <td><?php echo $row['Amount']; ?>&nbsp;BTC</td>
  <td>
    <a href="#" onClick="decrypt_key(<?php echo $pkn; ?>);">DECRYPT</a> |
    <a href="#" onClick="remove_key(<?php echo $row['OrderID']; ?>)">REMOVE</a>
  </td>
</tr>
</table>
<div style="height:5px;"></div>
<?php
	    $pkn++;
	  }
    }
  } else {
    echo "<p>There are no keys yet.</p>";
  }
?>

<p><b>NOTE(*)</b>:
The balance displayed for addresses reflects the balance at the time when the transaction was confirmed.
It may not be the correct balance if you have already withdrawn coins from that address or sent coins to it.
</p>

<a name="anchor"></a>

<div id="exp_con" class='no_display'>
  <h3>Private Key List:</h3>
  <textarea id="exp_box0" style="height:300px;width:700px;"></textarea>
  
  <h3>Generic CSV Format:</h3>
  <textarea id="exp_box1" style="height:300px;width:700px;"></textarea>

  <h3>Generic JSON Format:</h3>
  <textarea id="exp_box2" style="height:300px;width:700px;"></textarea>
</div>

<?php
  if (isset($_GET['export'])) {
  
    $keys = export_conf_keys();
	$pkn = 0;
	
    if (!empty($keys) && ($keys != 'N/A')) {
	  
	  while ($row = mysqli_fetch_assoc($keys)) {
		$key_data = explode(':', $row['KeyData']);
		if ($key_data[0] !== 'empty') {
		  echo "<p class='no_display' id='pk_d$pkn'>".$key_data[0]."</p>";
		  echo "<p class='no_display' id='pk_a$pkn'>".$key_data[1]."</p>";
	      $pkn++;
		}
	  }
	  
	  echo "<script language='JavaScript'>exp_keys(); location.href = '#anchor'</script>";  
    } else {
	  echo "<p class='error_txt'>Unexpected problem locating keys.</p>";  
	}
  }
?>
