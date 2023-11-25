<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/gateways/setup.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');
require_once(dirname(__FILE__)."/../inc/langs/$locale.inc.php");

session_start();

if (isset($_GET['t'])) {

  $ted = preg_replace('/[^a-z0-9]/i', '', $_GET['t']);
  $t_data = get_order_data($ted);
  if ($t_data === false) { die(LANG('TRAN_NOT_FOUND')); }

  list($total, $buyer, $note, $order_id, 
  $exch_rate, $gateway, $order_time) = $t_data;

  if ($use_altrpc) {
    // get altcoin price data
    if (isset($_SESSION["$ted-alt_total"])) {
      $alt_total = $_SESSION["$ted-alt_total"];
    } elseif (file_exists("backup/alt-$ted")) {
	  $key_data = get_key_data('backup/alt-', $ted);
	  $key_arr = explode(':', $key_data);
	  $alt_total = $key_arr[0];
    } else {
	  $alt_total = alt_btc_pair($total, $alt_btc_api);
	  if ($alt_total !== false && bccomp($alt_total, '0') == 1) {
        $alt_total = trim_decimal($alt_total, $p_precision);
        $_SESSION["$ted-alt_total"] = $alt_total;
	  } else {
	    echo '0';
	  }
    }
	echo $alt_total;
  } else {
    echo '0';
  }
} else {
  echo '0';
}
?>