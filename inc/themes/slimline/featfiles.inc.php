<h5 class="nobot_margin"><?php echo LANG('FEAT_PRODS'); ?>:</h5>
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
  echo '<div class="snap_box">';
  for ($findex=0;$findex<$feat_count;$findex++) { 
    $row = mysqli_fetch_assoc($feat_items[$findex]);
    if (!empty($row) && $row['FileActive']) {
	  echo item_box_html($row);
    }
  }
  echo '<br clear="all" /></div>';
} else {
  echo "<p>".LANG('NO_FEAT_PRODS')."</p>";
}
?>