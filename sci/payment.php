<?php
/**
* Bitcoin Payment Gateway
*
* @author Jacob Bruce
* www.bitfreak.info
*/
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/gateways/setup.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');
require_once(dirname(__FILE__).'/../lib/rpcclient.lib.php');
require_once(dirname(__FILE__).'/../inc/session.inc.php');
require_once(dirname(__FILE__)."/../inc/langs/$locale.inc.php");

// connect to database
$conn = connect_to_db();

// get and clean transaction code
$ted = preg_replace('/[^a-z0-9]/i', '', $_GET['t']);

// recreate success/cancel url's since they aren't passed to us
$cancel_url = $site_url.'?page=return&result=cancel&code='.$ted;
$success_url = $site_url.'?page=return&result=success&code='.$ted;

// get t_data from file if session expired
$t_data = get_order_data($ted);
if ($t_data === false) { die(LANG('TRAN_NOT_FOUND')); }

// save the transaction data to individual variables
list($total, $buyer, $note, $order_id, 
$exch_rate, $gateway, $order_time) = $t_data;

// get time left before order expires
$time_diff = get_time_difference($order_time, time());
$time_left = $order_expire_time - $time_diff['minutes'];
$secs_left = ($order_expire_time*60) - $time_diff['seconds'];
$order_expired = $secs_left <= 0;

// check if order expired
if ($order_expired) {
  redirect($cancel_url);
  exit;
}

// check if IP has changed
if (empty($_SESSION['ip_hash'])) {
  $_SESSION['ip_hash'] = get_ip_hash();
} elseif ($_SESSION['ip_hash'] !== get_ip_hash()) {
  if ($_SESSION['client_type'] != 'tcon') {
    die(LANG('IP_CHANGED').' '.LANG('TRY_AGAIN_BACK'));
  }
}

// handle generation of addresses
if (isset($_GET['scan'])) {
	
  // get and clean currency type
  $cur = preg_replace('/[^a-z]/i', '', $_GET['c']);

  // update some vars based on currency type
  switch ($cur) {
    case 'btc': 
      $pinc = $prog_inc;
	  $coin_code = 'BTC';
	  $coin_name1 = LANG('BITCOIN');
	  $coin_name2 = strtolower($coin_name1);
	  $ms_rt = $refresh_time * 1000;
	  break;
    case 'alt': 
      $pinc = $alt_pinc;
	  $coin_code = $altcoin_code;
	  $coin_name1 = $altcoin_name;
	  $coin_name2 = strtolower($coin_name1);
	  $ms_rt = $alt_refresh * 1000;
	  break;
    default: 
      die(LANG('INVALID_ACTION'));
  }
  
  // check what type of address we need
  if ($cur == 'btc') {
  
    // check if using custom address list
    if ($use_address_list) {
  
      // check if address already generated
      if (file_exists("backup/btc-$ted")) {
	
	    // get data we previously saved
	    $key_data = get_key_data("backup/btc-", $ted);
	    $key_arr = explode(':', $key_data);
	    $pub_add = $key_arr[1];
		
		// check if we need to overwrite old db data
		if (!isset($_SESSION['cur']) || $_SESSION['cur'] !== $cur) {
	      $save_key_data = true;
	    } else {
		  $save_key_data = false;
		}

	  } else {
	
	    // get new address from db
        $pub_add = enabled_address();

        if (empty($pub_add) || $pub_add === 'N/A') {
          die(LANG('ERROR_GEN_ADDRESS').' '.LANG('TRY_AGAIN_LATER'));
        } else {
		  $add_arr = mysqli_fetch_assoc($pub_add);
		  disable_address($add_arr['AddID']);
		  $pub_add = $add_arr['Address'];
		}

	    // create some necessary vars
	    $key_data = "empty:$pub_add";
	    $save_key_data = true;
	  }
	
    } else {

      // check if address already generated
      if (file_exists("backup/btc-$ted")) {
	
	    // get data we previously saved
	    $key_data = get_key_data("backup/btc-", $ted);
	    $key_arr = explode(':', $key_data);
	    $pub_add = $key_arr[1];
		
		// check if we need to overwrite old db data
		if (!isset($_SESSION['cur']) || $_SESSION['cur'] !== $cur) {
	      $save_key_data = true;
	    } else {
		  $save_key_data = false;
		}

	  } else {
	
	    // generate a new key pair
        $keySet = bitcoin::getNewKeySet();
        if (empty($keySet['pubAdd']) || empty($keySet['privWIF'])) {
          die(LANG('ERROR_GEN_ADDRESS').' '.LANG('TRY_AGAIN_BACK'));
        }
		
		// check for errors in address
		if (!bitcoin::checkAddress($keySet['pubAdd'])) {
		  die(LANG('INVALID_ADDRESS').' '.LANG('TRY_AGAIN_BACK'));
		}

	    // encrypt private key using rsa
	    $encWIF = bin2hex(bitsci::rsa_encrypt($keySet['privWIF'], $pub_rsa_key));
	    $pub_add = $keySet['pubAdd'];
	    $key_data = "$encWIF:$pub_add";
	    $save_key_data = true;
	  }
    }
	
	// trim down decimal places on total
	$total = trim_decimal($total, $p_precision);
  
  // check if using the altcoin rpc
  } elseif ($cur === 'alt' && $use_altrpc) {
	
    // check if address already generated
    if (file_exists("backup/alt-$ted")) {

	  // get data we previously saved
	  $key_data = get_key_data("backup/alt-", $ted);
	  $key_arr = explode(':', $key_data);
	  $total = $key_arr[0];
	  $pub_add = $key_arr[1];

	  // check if we need to overwrite old db data
	  if (!isset($_SESSION['cur']) || $_SESSION['cur'] !== $cur) {
	    $save_key_data = true;
	  } else {
		$save_key_data = false;
	  }

	} else {
	  
	  // get altcoin price data
	  if (isset($_SESSION["$ted-alt_total"])) {
		$total = $_SESSION["$ted-alt_total"];
	  } else {
		$total = alt_btc_pair($total, $alt_btc_api);
		if ($total !== false && bccomp($total, '0') == 1) {
    	  $total = trim_decimal($total, $p_precision);
    	  $_SESSION["$ted-alt_total"] = $total;
		} else {
		  die(LANG('PROB_CALC_TOTAL').' '.LANG('TRY_AGAIN_BACK'));
		}
	  }
	
	  // connect to RPC client
	  $_SESSION[$rpc_client] = new RPCclient($rpc_user, $rpc_pass);

	  // generate a new address
	  $pub_add = $_SESSION[$rpc_client]->getnewaddress('bitshop');
  
	  // check for errors
      if (empty($pub_add) || !empty($_SESSION[$rpc_client]->error)) {
        die(LANG('ERROR_GEN_ADDRESS').' '.LANG('TRY_AGAIN_BACK'));
      }

	  // create some necessary vars
	  $key_data = "empty:$pub_add";
	  $save_key_data = true;
	}
	
	// save total in place of private key
    $alt_data = "$total:$pub_add";

  } else {
    die(LANG('INVALID_ACTION').' '.LANG('TRY_AGAIN_BACK'));
  }

  // save key data if necessary
  if ($save_key_data) {

	// save to database					
    if (update_key_data($order_id, $key_data)) {
	  $_SESSION['cur'] = $cur;
      $key_data = isset($alt_data) ? $alt_data : $key_data;
	  // save to disk
	  if (file_put_contents("backup/$cur-$ted", $key_data) !== false) {
	    chmod("backup/$cur-$ted", 0600);
	  } else {
	    die(LANG('UNEXPECTED_ERROR').' '.LANG('TRY_REFRESHING'));
	  }
    } else {
	  die(LANG('DATABASE_ERROR').' '.LANG('TRY_REFRESHING'));
    }
  }
  
} else {

  // trim down decimal places on total
  $total = trim_decimal($total, $p_precision);
}

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="robots" content="noindex,nofollow" />
	<title><?php echo LANG('PAYMENT_GATEWAY'); ?></title>
	
	<link rel="stylesheet" href="./../css/normalize.css" />
	<link rel="stylesheet" href="./../css/boilerplate.css" />
	<link rel="stylesheet" href="./../css/bootstrap.min.css" />	
	<link rel="stylesheet" href="./../css/bootstrap-responsive.min.css" />
	
	<script src="./../scripts/jquery.min.js"></script>
	<script src="./../scripts/jquery.qrcode.min.js"></script>
	<script src="./../scripts/bootstrap.min.js"></script>
	<script src="./../scripts/general.lib.js"></script>
	<script src="./../scripts/ajax.lib.js"></script>
	
	<script language="JavaScript" type="text/javascript">
	var ted = encodeURIComponent('<?php echo $ted; ?>');
	var cur = '<?php echo isset($cur)?$cur:'btc'; ?>';
	<?php if (isset($_GET['scan'])) { ?>
	var confHandle = 0;
	var timeHandle = 0;
	var stepHandle = 0;
	var stepCount = 0;
	var secsLeft = <?php echo $secs_left; ?>;
	var minText = '<?php echo LANG('MINUTE'); ?>';
	var minsText = '<?php echo LANG('MINUTES'); ?>';
	var less1min = '<?php echo LANG('LESS_THAN_ONE'); ?>';
	
	function minsLeftStr() {
	  secsLeft -= 10;
	  if (secsLeft <= 0) {
	    checkPaymentStatus();
		return '0 '+minsText;
	  } else if (secsLeft < 60) {
	    return less1min+' '+minText;
	  } else if (secsLeft < 90) {
	    return '1 '+minText;
	  } else {
	    return Math.round(secsLeft / 60)+' '+minsText;
	  }
	}
	
	function updateTimeLeft() {
	  $('#time_left').html(minsLeftStr());
	}
	  
	function updateProgress(pro_txt) {
	  pro_txt = pro_txt.split(':');
	  $('#pro_txt').html(pro_txt[0]+'%');
	  $('#pro_bar').css('width', pro_txt[0]+'%');
	  $('#con_sta').html("<b>Status:</b> "+pro_txt[1]);

	  if (pro_txt[1] == 'confirmed') {
		clearInterval(confHandle);
		clearInterval(stepHandle);
		clearInterval(timeHandle);
		redirect('<?php echo $success_url; ?>');
	  } else if (pro_txt[1] == 'order expired') {
		clearInterval(confHandle);
		clearInterval(stepHandle);
		clearInterval(timeHandle);
		redirect('<?php echo $cancel_url; ?>');
	  }
	}

	function stepProgress() {
	  if (stepCount >= <?php echo $pinc-1; ?>) {
		stepCount = 0;
	  } else {
	    var pro_txt = $('#pro_txt').html();
	    var pro_val = pro_txt.slice(0, -1);
		var new_pro = parseInt(pro_val) + 1;
		pro_val = new_pro + '%';
		if (new_pro < 100) {
		  $('#pro_bar').css('width', pro_val);
		  $('#pro_txt').html(pro_val);
		}
		stepCount++;
	  }
	}
	
	function handle_error(response) {
	  $('#alert_txt').html(response);
	  $('#error_box').show();
	}

	function checkPaymentStatus() {
		ajax_get('check-status.php', {'t': ted, 'c': cur}, updateProgress, handle_error);
	}

	function start_scan() {
	  confHandle = setInterval('checkPaymentStatus();', <?php echo round($ms_rt); ?>);
	  stepHandle = setInterval('stepProgress();', <?php echo round($ms_rt / $pinc); ?>);
	  checkPaymentStatus();
	  $('#btn_box').hide();
	  $('#prog_box').show();
	}

	$(document).ready(function() {
	  $('#qrcode').qrcode('<?php echo $coin_name2.':'.$pub_add; ?>');
	  $("#total_price").click(function() {
	    selectText('total_price');
	  });
	  $("#address").click(function() {
	    selectText('address');
	  });
	  timeHandle = setInterval('updateTimeLeft();', 10000);
	});
	
	<?php } else { ?>
	
	var btc_total = '<?php echo "$total BTC"; ?>';
	var unav_text = '<?php echo LANG('CURR_UNAVAILABLE').' '.LANG('TRY_AGAIN_LATER'); ?>';

	function confirmCancel() {
	  if (confirm('<?php echo LANG('SURE_CANCEL_TRAN'); ?>')) {
		document.cookie = 'ocode=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/';
		redirect('<?php echo $cancel_url; ?>');
	  }
	}
	
	function next_stage() {
	  $('#confirm_btn').addClass('disabled');
	  $('#confirm_btn').html('<?php echo LANG('LOADING'); ?>');
	  redirect('payment.php?scan&t='+ted+'&c='+cur);
	}
	
	<?php if ($use_altrpc) { ?>
	
	function show_result(response) {
	  if (response != '0') {
		$('#btc_logo').hide();
		$('#alt_logo').show();
		$('#total').html(response+' <?php echo $altcoin_code; ?>');	
		cur = 'alt';
	  } else {
	    $('#curr_list').val('btc');
	    $('#alert_txt').html(unav_text);
		$('#error_box').show();
	  }
	  $('#loader').hide();
	  $('#curr_list').show();
	}
	
	function handle_error(response) {
	  $('#curr_list').val('btc');
	  $('#alert_txt').html(response);
	  $('#error_box').show();
	  $('#loader').hide();
	  $('#curr_list').show();
	}
	
	$(document).ready(function() {
	  $('#curr_list').on('change', function() {
		if ($(this).val() == 'btc') {
		  $('#alt_logo').hide();
		  $('#btc_logo').show();
		  $('#total').html(btc_total);
		  cur = 'btc';
		} else {
		  $('#curr_list').hide();
		  $('#loader').show();
		  ajax_get('alt_price.php', {'t': ted}, show_result, handle_error);
		}
	  });
	});
	<?php }} ?>
	</script>
	
	<style>
	.alert {
		width:300px;
		margin-top:20px;
	}
	
	.icon {
		vertical-align:baseline;
	}
	
	.no_display {
		display:none;
	}
	
	.progress {
		width:200px;
	}
	
	#loader {
	  display:none;
	}
	
	#qrcode {
		margin-top:20px;
	}
	
	#address {
		font-size:22px;
		font-weight:bold;
		cursor:pointer;
		border-bottom: 1px dotted #000;
		display:inline;
	}
	
	#total_text {
		font-size:20px;
		font-weight:bold;
	}
	
	#total_price {
		cursor:pointer;
		border-bottom: 1px dotted #000;
	}
	</style>
</head>
<body>

<center>
  
  <div id="btc_logo">
    <br /><img src='./img/bitcoin_logo.png' alt='' />
  </div>
  <div id="alt_logo" class="no_display">
    <br /><img src='./img/altcoin_logo.png' alt='' />
  </div>
  
  <h1><?php echo LANG('PAYMENT_GATEWAY'); ?></h1>
  <hr style="width:300px" />
  
  <noscript>
    <div class="alert alert-error">
      <p><?php echo LANG('JS_NOTICE'); ?><p>
    </div>
  </noscript>
  
  <div id="error_box" class="alert alert-error no_display">
    <a class="close" onclick="$('#error_box').hide();" href="#">&times;</a>
    <p id="alert_txt"><p>
  </div>
  
  <?php if (!isset($_GET['scan'])) { ?>
  
  <div class="alert alert-info" style="width:300px">
    <?php echo LANG('PAYING_FOR').': <b>'.LANG('ORDER')." #$order_id</b>".
    '<br />'.LANG('PAID_TO').': <b>'.safe_str($seller).'</b>'.
	'<br />'.LANG('TOTAL').": <b id='total'>$total BTC</b>"; ?>
  </div>

  <label><b><?php echo LANG('PAY_METHODS'); ?>:</b></label>
  <select name="curr_list" id="curr_list" class="input-medium">
    <option value="btc" selected="selected"><?php echo LANG('BITCOIN'); ?> (BTC)</option>
	<?php if ($use_altrpc) { ?>
	<option value="alt"><?php safe_echo("$altcoin_name ($altcoin_code)"); ?></option>
	<?php } ?>
  </select>
  <img id="loader" src="./img/ajax_loader.gif" alt="<?php echo LANG('LOADING'); ?>" />

  <hr style="width:300px" />
  <button class="btn" onclick="confirmCancel();"><?php echo LANG('CANCEL'); ?></button>
  <button class="btn btn-primary" id="confirm_btn" onclick="next_stage();"><?php 
  echo LANG('CONTINUE'); ?></button>

  <?php } else { ?>
  
  <p><?php echo LANG('PLEASE_TRANSFER'); ?> <i><?php echo LANG('EXACTLY'); ?></i> 
  <span id="total_text"><span id="total_price"><?php echo "$total"; ?></span> 
  <?php echo $coin_code; ?></span> <?php echo LANG('TO_ADDRESS'); ?>:</p>
  
  <span id="address"><?php echo safe_str($pub_add); ?></span>
  <span>
    <?php echo '<a href="'.$coin_name2.':'.$pub_add.'?amount='.$total.
	'" title="'.str_replace('%', $coin_name1, LANG('CLICK_THIS_ICON')).
	'" target="_blank"><i class="icon icon-magnet"></i></a>';
	?>
  </span>
  
  <br clear="both" />
  <div id="qrcode"></div>
  <br clear="both" />

  <p><?php echo LANG('EXPIRES_IN')." <b id='time_left'>$time_left ".LANG('MINUTES').'</b>'; ?></p>
	
  <div id="btn_box">
    <p><?php echo str_replace('%', $coin_code, LANG('CLICK_THE_CONF')); ?></p>
    <a class="btn" href="./payment.php?t=<?php 
	echo $ted; ?>"><?php echo LANG('GO_BACK'); ?></a>
    <button class="btn btn-primary" onclick="start_scan();"><?php 
	echo LANG('CONFIRM_PAYMENT'); ?></button>
  </div>
  
  <div class="no_display" id="prog_box">
    <p><?php echo str_replace('%', $coin_name1, LANG('WAIT_WHILE_CONF')); ?></p>
    <p><b><?php echo LANG('PROGRESS'); ?>:</b></p>
    <div class="progress">
      <div class="bar" id="pro_bar" style="width:0%;">
	    <span id='pro_txt'>0%</span>
	  </div>
    </div>
    <p id="con_sta"><b><?php echo LANG('STATUS'); ?>:</b> 
    <?php echo LANG('CONFIRMING_PAYMENT'); ?></p>
  </div>
  
  <?php } ?>
  
</center>

</body>
</html>
<?php session_write_close(); ?>