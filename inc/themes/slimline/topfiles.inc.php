<h5 class="side_head"><?php echo LANG('TOP_PRODS'); ?>:</h5>
<?php
$top_items = top_files(5);
if (!empty($top_items) && ($top_items != 'N/A')) {
  echo '<ul class="nav nav-tabs nav-stacked side_box">';
  
  foreach ($top_items as $key => $row) {
    if (!empty($row)) {
			
      if (strlen($row['FileName']) > 29) {
        $item_name = safe_str($row['FileName']);
        $short_name = safe_str(substr($row['FileName'], 0, 29).'...');
      } else {
        $item_name = safe_str($row['FileName']);
        $short_name = $item_name;
      }
			
	  $btc_price = get_btc_price($row['FilePrice'], $exch_orig);
	  $btc_price = bitsci::btc_num_format($btc_price, 8, $dec_shift);
      $item_url = "index.php?page=item&amp;id=".$row['FileID'];
			
      echo "<li><a class='side_link' href='$item_url' title='$item_name'>$short_name".
      " <span class='side_price'>".$btc_price.' '.$dec_unit."BTC</span></a></li>\n";
    }
  }
  echo '</ul>';
} else {
  echo "<p>".LANG('NO_TOP_PRODS')."</p>";
}
?>
  