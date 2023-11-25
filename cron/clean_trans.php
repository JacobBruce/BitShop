<?php
require_once(dirname(__FILE__).'/../inc/config.inc.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');

$conn = connect_to_db();				  
$unc_trans = list_unconfirmed_orders();
$now = mysqli_now();

if (!empty($unc_trans) && ($unc_trans !== 'N/A')) {
  while ($tran = mysqli_fetch_assoc($unc_trans)) {
	$diff = get_time_difference($tran['Created'], $now);
	if ($diff['hours'] > $tran_clean_time) {
	  if ($tran['Status'] == 'Unconfirmed' || $tran['Status'] == 'Expired/Invalid') {
	    delete_order($tran['OrderID']);
	    $items = cart_items($tran['Cart']);
	    foreach ($items as $key => $quant) {
		  $item = get_file($key);
		  if (!empty($item) && ($item !== 'N/A')) {
			$item = mysqli_fetch_assoc($item);
			if (($item['FileMethod'] !== 'download') && ($item['FileMethod'] !== 'keys')) {
			  edit_file($key, "FileStock = FileStock+$quant");
			}
		  }
		}
      }
    }
  }
}
?>