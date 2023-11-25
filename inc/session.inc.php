<?php
// make sure we have access to config vars
require_once(dirname(__FILE__).'/../sci/config.php');

// start the session
session_start();

// check session status
if (session_expired($sess_time)) {
  session_unset();
}

// save account id to global variable
if (isset($_SESSION['user_data']['AccountID'])) {
  $account_id = $_SESSION['user_data']['AccountID'];
}

// check if user is trying to update settings
if (isset($_POST['curr_list'])) {
  if (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token']) || 
  $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
	die(LANG('INVALID_ACCESS'));
  }
  if (isset($market_data[$_POST['curr_list']]) &&
  isset($langarray[$_POST['lang_list']]) &&
  in_array($_POST['client_type'], array('ncon', 'tcon'))) {
    $_SESSION['curr_code'] = $_POST['curr_list'];
    $_SESSION['language'] = $_POST['lang_list'];
	$_SESSION['time_zone'] = $_POST['time_list'];
    $_SESSION['client_type'] = $_POST['client_type'];
	$alert['error'] = false;
    if (login_state() === 'valid') {
	  $conn = connect_to_db();
	  $settings = array(
	    'curr' => $_POST['curr_list'],
	    'lang' => $_POST['lang_list'],
		'zone' => $_POST['time_list'],
	    'clit' => $_POST['client_type']
	  );
	  $settings = json_encode($settings);
	  if (!set_account_settings($account_id, $settings)) {
        $alert['error'] = true;
	  }
    }
  } else {
    $alert['error'] = true;
  }
}

// initialize cart if necessary
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = array();
}

// initialize client type
if (!isset($_SESSION['client_type'])) {
  $_SESSION['client_type'] = 'ncon';
}

// save IP address to session
if (!isset($_SESSION['ip_address'])) {
  $_SESSION['ip_address'] = get_remote_ip();
}

// set user-specified language
if (isset($_SESSION['language'])) {
  $locale = $_SESSION['language'];
} else {
  $_SESSION['language'] = $locale;
}

// set user-specified timezone
if (isset($_SESSION['time_zone'])) {
  $time_zone = $_SESSION['time_zone'];
} else {
  $_SESSION['time_zone'] = $time_zone;
}

// set user-specified currency
if (isset($_SESSION['curr_code'])) {
  $curr_orig = $curr_code;
  if (!isset($admin_call)) {
    $curr_code = $_SESSION['curr_code'];
  }
} else {
  $_SESSION['curr_code'] = $curr_orig = $curr_code;
}
?>