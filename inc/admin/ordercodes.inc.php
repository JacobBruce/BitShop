<?php admin_valid(); ?>

<h1>Codes for Order <small>#<?php echo $tid; ?></small></h1>
<p><b>List of codes:</b></p>

<?php
if (!empty($_GET['task'])) {

  if ($_SESSION['csrf_token'] === $_GET['toke']) {

    if ($_GET['task'] === 'discode') {
	  if (update_code("AccountID = 0", $_GET['cid'])) {
	    echo "<p class='happy_txt'>Successfully removed code from account!</p>";
	  } else {
	    echo "<p class='error_txt'>Failed to remove code from account!</p>";
	  }
    } elseif ($_GET['task'] === 'editcode') {
	  $new_code = base64_decode($_GET['newcode']);
	  if (update_code("CodeData = '".$new_code."'", $_GET['cid'])) {
	    echo "<p class='happy_txt'>Successfully edited code!</p>";
	  } else {
	    echo "<p class='error_txt'>Failed to edit code!</p>";
	  }
    }
  
  } else {
    echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."!</p>";
  }
}
  
$codes = get_order_codes($tid);

if (!empty($codes) && ($codes !== 'N/A')) {
?>
<table class='table table-striped table-bordered table-hover table-condensed'>
<tr>
  <th>Code ID</th>
  <th>Code</th>
  <th>Account</th>
  <th>Created</th>
  <th>Actions</th>
</tr>
<?php
  while ($row = mysqli_fetch_assoc($codes)) {
    $code_acc_id = (empty($row['AccountID']) ? 'n/a' : $row['AccountID']);
    echo "<tr><td>".$row['CodeID']."</td><td>".$row['CodeData'].
	"</td><td>$code_acc_id</td><td>".$row['Created'].
	"</td><td><a href='#' onClick=\"update_code(".$row['CodeID'].
	');">EDIT</a>';

	if ($code_acc_id > 0 && $code_acc_id != 'n/a') {
	  echo " | <a href='#' onClick=\"unlink_code(".
	  $row['CodeID'].');">REMOVE FROM ACCOUNT</a></td></tr>';
	}
  }
?>
</table>
<?php
} else {
  echo "<p>There are no digital codes associated with this order.</p>";
}
?>

<p><a class="btn" href="admin.php?page=orders&amp;tid=<?php echo $tid; ?>">Go Back</a></p>

<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

function unlink_code(code_id) {
	if (confirm('Remove this code from the attached account?')) {
		redirect('admin.php?page=orders&action=codes&tid=<?php echo $tid; ?>&task=discode&cid='+code_id+'&toke='+csrf_token);
	}
}

function update_code(code_id) {
	var new_code = prompt('Enter new value for this code:', '');
	if (new_code != null && new_code != '') {
		redirect('admin.php?page=orders&action=codes&tid=<?php echo $tid; ?>&task=editcode&newcode='+encodeURIComponent(Base64.encode(new_code))+'&cid='+code_id+'&toke='+csrf_token);
	}
}
</script>