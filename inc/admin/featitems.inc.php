<?php admin_valid();

if ($feat_prods == false) {
  echo "<p class='error_tst'>Featured products are not enabled. The selected products will not be displayed until you enable featured products in the main settings.</p>";
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['feat_ids'])) {
  $feat_new = '';
  $feat_str = file_get_contents("inc/feat_ids.inc");
  $feat_ids = explode(',', $feat_str);
  
  foreach ($_POST['feat_ids'] as $option) {
    if (is_numeric($option)) {
	  $id_int = round($option);
	  if (!in_array($id_int, $feat_ids)) {
        $feat_new .= $id_int.',';
	  }
    }
  }
  
  $feat_new = str_replace(',,', ',', $feat_new);
  $feat_new = trim($feat_new, ',');
  
  if (file_put_contents("inc/feat_ids.inc", $feat_str.','.$feat_new)) {
    echo "<p class='happy_txt'>Successfully added to featured items!</p>";
  } else {
    echo "<p class='error_txt'>An unexpected error occurred!</p>";
  }
}

if (isset($_GET['rem'])) {
  $feat_str = file_get_contents("inc/feat_ids.inc");
  $feat_ids = explode(',', $feat_str);
  $found_fid = false;
  
  foreach ($feat_ids as $key => $value) {
    if ($value == $_GET['rem']) {
	  unset($feat_ids[$key]);
	  $found_fid = true;
	}
  }
  
  $feat_new = implode(',', $feat_ids);
  
  if ($found_fid && file_put_contents("inc/feat_ids.inc", $feat_new)) {
    echo "<p class='happy_txt'>Successfully removed from featured items</p>";
  } else {
    echo "<p class='error_txt'>Product has already been removed</p>";
  }
}

$all_prods = active_files();
?>

<p><b>Featured products</b></p>

<div class="row-fluid">
  <div class="span6">
	<form class="form-inline" name="feat_form" method="post" action="">
	  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
	  <label>Select Product(s):</label><br />
	  <select name="feat_ids[]" multiple="multiple" size="10"><?php
		while ($prod = mysqli_fetch_assoc($all_prods)) {
		  echo "<option value='".$prod['FileID']."'>".$prod['FileName']."</option>";
		}
		?>
	  </select><br />
	  <input class="btn" type="submit" value="Add To Featured" style="margin-top:5px" />
	</form>
  </div>
  <div class="span6">
	<p>Currently Featured:</p>

	<?php
	$feat_str = file_get_contents("inc/feat_ids.inc");

	if (!empty($feat_str)) {
	  $feat_ids = explode(',', $feat_str);
	  $feat_items = array();
	  $feat_test = 0;
	  $feat_count = 0;

	  foreach ($feat_ids as $key => $value) {
		$feat_test = get_file(safe_sql_str($value));
		if (!empty($feat_test) && ($feat_test != 'N/A')) {
		  $feat_items[$feat_count] = $feat_test;
		  $feat_count++;  
		}
	  }
	}

	if (!empty($feat_items) && ($feat_items != 'N/A')) {

	  echo '<ul>';  
	  for ($findex=0;$findex<$feat_count;$findex++) {
	  
		$row = mysqli_fetch_assoc($feat_items[$findex]);
		
		if (!empty($row)) {
				
		  if (strlen($row['FileName']) > 18) {
			$item_name = safe_str($row['FileName']);
			$short_name = str_replace(' ', '&nbsp;', safe_str(substr($row['FileName'], 0, 18).'...'));
		  } else {
			$item_name = safe_str($row['FileName']);
			$short_name = $item_name;
		  }
		
		  $item_url = "admin.php?page=items&amp;action=edit&amp;fid=".$row['FileID'];
				
		  echo "<li><a href='$item_url' title='$item_name'>".$short_name."</a>".
		  " <a href='./admin.php?page=items&amp;action=featured&amp;rem=".$row['FileID'].
		  "' title='remove'><i class='icon-remove'></i></a></li>";
		}
	  }
	  echo '</ul>';
	} else {
	  echo "<p>No featured products selected.</p>";
	}
	?>
   </div>
</div>

<p><a class="btn" href="admin.php?page=items">Go Back</a></p>