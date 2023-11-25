<h5 class="nobot_margin"><?php echo LANG('NEW_PRODS'); ?>:</h5>
<?php
$new_items = new_files(5);
if (!empty($new_items) && ($new_items != 'N/A')) {
  echo '<div class="snap_box">';
  while ($row = mysqli_fetch_assoc($new_items)) {
    if (!empty($row) && $row['FileActive']) {
	  echo item_box_html($row);
    }
  }
  echo '<br clear="all" /></div>';
} else {
  echo "<p>".LANG('NO_NEW_PRODS')."</p>";
}
?>