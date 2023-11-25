<h5 class="side_head"><?php echo LANG('POP_PRODS'); ?>:</h5>
<?php
$best_items = best_files(5);
if (!empty($best_items) && ($best_items != 'N/A')) {
  echo '<ul class="nav nav-tabs nav-stacked">';
  
  foreach ($best_items as $key => $row) {
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
  echo "<p>".LANG('NO_POP_PRODS')."</p>";
}
?>
  