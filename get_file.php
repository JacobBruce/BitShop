<?php
// include required files
require_once('lib/common.lib.php');
require_once('inc/config.inc.php');
require_once('inc/session.inc.php');
require_once("inc/langs/$locale.inc.php");

// connect to database
$conn = connect_to_db();

// check if file should be locked
if (!empty($_GET['code'])) {
  $down_code = preg_replace('/[^A-Za-z0-9]/i', '', $_GET['code']);		
  $lock_time = lock_ip_check('code'.$down_code, get_ip_hash(), $file_hit_limit, $file_lock_time);
} elseif (!empty($_GET['key'])) {
  $down_key = preg_replace('/[^A-Za-z0-9]/i', '', $_GET['key']);		
  $lock_time = lock_ip_check('key'.$down_key, get_ip_hash(), $file_hit_limit, $file_lock_time);
} else {
  die(LANG('NO_DOWN_KEY'));
}
if ($lock_time > 0) {
  die(LANG('DOWN_LIMIT_EX')." $lock_time ".LANG('DAYS').'.');
}

if (isset($_SESSION['user_data']['AccountID'])) {
  $account_id = $_SESSION['user_data']['AccountID'];
} else if ($login_for_files) {
  die(LANG('MUST_LOGIN'));
}
  
// return corresponding file
if (!empty($down_code)) {
  $code = get_code($down_code);
  if (!empty($code) && ($code != 'N/A')) {
    $code = mysqli_fetch_assoc($code);
	if ($login_for_files && !empty($code['AccountID']) && $account_id != $code['AccountID']) {
	  die(LANG('INVALID_ACCESS'));
	}
	if ($code['Available']) {
	  $time_diff = get_time_difference($code['Created'], mysqli_now());
	  if ($time_diff['days'] < $link_expire_time) {
	    $file = get_file($code['ItemID']);
		if (!empty($file) && ($file != 'N/A')) {
          $file = mysqli_fetch_assoc($file);
		  send_file_to_browser(str_replace(' ', '_', $file['FileName']).
		  '.'.trim($file['FileType'], '.'), 'uploads/'.$file['FileCode']);
		} else {
	      $err_msg = LANG('FILE_NOT_FOUND');
		}
	  } else {
	    $err_msg = LANG('DOWN_LINK_EX');
	  }
	} else {
	  $err_msg = LANG('CODE_DISABLED').' '.LANG('CONTACT_ADMIN');
	}
  } else {
	$err_msg = LANG('INVALID_DOWN_KEY');
  }
} elseif (!empty($down_key)) {
  $code = get_code(strtolower($down_key));
  if (!empty($code) && ($code !== 'N/A')) {
    $code = mysqli_fetch_assoc($code);
	if ($login_for_files && !empty($code['AccountID']) && $account_id != $code['AccountID']) {
	  die(LANG('INVALID_ACCESS'));
	}
	if ($code['Available']) {
	  $item_data = get_file($code['ItemID']);
	  if (!empty($item_data) && ($item_data !== 'N/A')) {
	    $item_data = mysqli_fetch_assoc($item_data);
	    $time_diff = get_time_difference($code['Created'], mysqli_now());
	    if ($time_diff['days'] < $item_data['FileStock']) {
	      $file = get_file($item_data['FileType']);
		  if (!empty($file) && ($file != 'N/A')) {
            $file = mysqli_fetch_assoc($file);
		    send_file_to_browser(str_replace(' ', '_', $file['FileName']).
		    '.'.trim($file['FileType'], '.'), 'uploads/'.$file['FileCode']);
		  } else {
	        $err_msg = LANG('FILE_NOT_FOUND');
		  }
	    } else {
	      $err_msg = LANG('FILE_KEY_EX');
	    }
      } else {
	    $err_msg = LANG('FILE_NOT_FOUND');
	  }
	} else {
	  $err_msg = LANG('CODE_DISABLED').' '.LANG('CONTACT_ADMIN');
	}
  } else {
	$err_msg = LANG('INVALID_DOWN_KEY');
  }
} else {
  $err_msg = LANG('INVALID_DOWN_KEY');
}

// print any errors that occurred
if (!empty($err_msg)) { echo $err_msg; }
?>