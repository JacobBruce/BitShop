<?php admin_valid();

$rid = (empty($_GET['rid'])) ? 0 : $_GET['rid'];
echo '<h1>Product Reviews</h1>';

if (!empty($_GET['task'])) {
  if ($_SESSION['csrf_token'] !== $_GET['toke']) {
    echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
  } elseif (($_GET['task'] === 'toggle') && isset($_GET['newstate'])) {
    if (toggle_review($rid, $_GET['newstate'])) {
	  if ($_GET['newstate'] == 1) {
        $msg = "<p class='happy_txt'>Review successfully enabled!</p>";
	  } else {
		$msg = "<p class='happy_txt'>Review successfully disabled!</p>";
	  }
    }
  } elseif ($_GET['task'] === 'delete') {
    if (delete_review($rid)) {
      $msg = "<p class='happy_txt'>Review successfully deleted!</p>";
	}
  }
}

if (isset($_GET['action'])) {
  if ($_GET['action'] === 'edit') {
    if (!empty($_POST['new_rev'])) {
	  if (update_review($_GET['rid'], $_POST['new_rev'])) {
	    echo "<p class='happy_txt'>Review successfully updated!</p>";
	  } else {
	    echo "<p class='error_txt'>Error updating review!</p>";
	  }
	}
	$review = get_review($_GET['rid']);	  
	if (empty($review)) {
	  echo "<p class='error_txt'>Specified review does not exist!</p>";
	} else {
	  $review = mysqli_fetch_assoc($review);
?>

<p><b>Edit Review</b></p>
<form name="rev_form" method="post" action="">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <label for="new_rev">Review text:</label>
  <textarea name="new_rev" maxlength="980"><?php echo $review['Review']; ?></textarea>
  <br />
  <a class='btn' href="admin.php?page=reviews">Go Back</a> 
  <button type="submit" class="btn">Update</button>
</form>

<?php
	}
  }
} else {

  if (empty($_GET['p'])) {
    $curr_page = 1;
  } else {
    $curr_page = (int) $_GET['p'];
	if ($curr_page < 1) {
	  $curr_page = 1;
	}
  }
  
  $result = list_reviews(($curr_page-1) * 20);
  $rev_num = count_reviews();
  $page_num = (int) ceil($rev_num / 20);

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
    $nav_html = "<li$p_active><a href='admin.php?page=reviews&amp;p=1'>First</a></li>";
    for ($i=$start_page;$i<=$end_page;$i++) {
	  $p_active = ($i == $curr_page) ? " class='active'" : '';
      $nav_html .= "<li$p_active><a href='admin.php?page=reviews&amp;p=$i'>$i</a></li>";
    }
	$p_active = ($curr_page == $page_num) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='admin.php?page=reviews&amp;p=$page_num'>Last</a></li>";
  }
	
  if (!empty($nav_html)) {
    echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
  }
  
  if (!empty($rev_num)) {
    if (!empty($msg)) { echo $msg; }
?>

<table class='table table-striped table-bordered table-hover table-condensed'>
<tr>
  <th width="10%">Review&nbsp;ID</th>
  <th width="10%">Item&nbsp;ID</th>
  <th width="20%">Author</th>
  <th width="10%">Rating</th>
  <th width="25%">Date</th>
  <th width="25%">Actions</th>
  <?php
  if (!empty($result) && ($result !== 'N/A')) {
    while ($row = mysqli_fetch_assoc($result)) {
	
	  if ($row['Confirmed']) {
	    $toggle = 'DISABLE';
		$row_class = 'success';
		$action = 0;
	  } else {
	    $toggle = 'ENABLE';
		$row_class = 'error';
		$action = 1;
	  }
	  
      echo "<tr class='$row_class'><td><a title='' data-content='".
	  safe_str(str_replace("\n", '<br />', $row['Review']))."' data-placement='bottom' ".
	  "data-html='true' data-trigger='focus' data-toggle='popover' href='#'>".$row['RevID'].
	  "</a></td><td><a href='admin.php?page=items&action=edit&fid=".$row['ItemID']."'>".
	  $row['ItemID']."</a></td><td>".safe_str($row['Author'])."</td><td>".$row['Rating'].
	  "</td><td>".str_replace(' ', '&nbsp;', format_time($row['Created']))."</td><td><a href='".
	  "admin.php?page=reviews&amp;action=edit&amp;rid=".$row['RevID']."'>EDIT</a>&nbsp;|&nbsp;".
	  "<a href='#' onClick=\"toggle_review(".$row['RevID'].", $action);\">$toggle</a>&nbsp;".
	  "|&nbsp;<a href='#' onClick=\"delete_review(".$row['RevID'].");\">REMOVE</a></td></tr>";
    }
  }
  ?>
</tr>
</table>

<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

function delete_review(rev_id) {
	if (confirm('Are you really sure you want to delete this review?')) {
		redirect('admin.php?page=reviews&task=delete&rid='+rev_id+'&p=<?php echo $curr_page; ?>&toke='+csrf_token);
	}
}

function toggle_review(rev_id, new_state) {
	if (new_state == 1) { var action = 'enable'; } else { var action = 'disable'; }
	if (confirm('Are you sure you want to '+action+' this review?')) {
		redirect('admin.php?page=reviews&task=toggle&newstate='+new_state+'&rid='+rev_id+'&p=<?php echo $curr_page; ?>&toke='+csrf_token);
	}
}

$("a[data-toggle=popover]")
.popover({
  template: '<div class="popover rev-pop"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"><p></p></div></div></div>'
})
.click(function(e) {
	e.preventDefault();
	$(this).focus();
});
</script>

<?php
  } else {
    echo "<p>There are no product reviews yet.</p>";
  }
  echo '<p><a class="btn" href="admin.php">Go Back</a></p>';
}
?>