<?php
require_once(dirname(__FILE__).'/../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/../session.inc.php');
require_once(dirname(__FILE__)."/../langs/$locale.inc.php");

$hide_crash = true;
$conn = connect_to_db();

$utc_now = mysqli_now();

if (!empty($_POST['id']) && !empty($_POST['code'])) {

  if (empty($_POST['pass'])) {
    die(LANG('PASS_EMPTY'));
  }

  $account = get_account_byid($_POST['id']);
  if (!empty($account) && ($account !== 'N/A')){
	$account = mysqli_fetch_assoc($account);
	$time_diff = get_time_difference($account['LastTime'], $utc_now);
	$time_left = $login_lock_time - $time_diff['minutes'];
	if ($account['FailCount'] >= $login_fail_limit) {
	  if ($time_left <= 0) {
		$account['FailCount'] = 0;
		if (!set_lock_count($account['AccountID'], $account['FailCount'])) {
		  die(LANG('DATABASE_ERROR'));
		}
	  }
	}
	if ($account['FailCount'] < $login_fail_limit) {
	  $toke_hash = hash('sha256', $account['PassHash']);
	  if ($_POST['code'] == $toke_hash) {
	    $pass_msg = check_pass_strength($_POST['pass']);
	    if ($pass_msg !== true) {
		  die($pass_msg);
	    } else {
		  $new_hash = pass_hash($_POST['pass'], $hash_rounds);
		  if (set_account_pass($account['AccountID'], $new_hash)) {
			echo "success";
		  } else {
		    die(LANG('DATABASE_ERROR'));
		  }
		}
	  } else {
		$account['FailCount']++;
		if (!set_lock_count($account['AccountID'], $account['FailCount'])) {
		  die(LANG('DATABASE_ERROR'));
		}
		echo LANG('INVALID_ACCESS');
		if ($account['FailCount'] == $login_fail_limit) {
		  if (!set_last_time($account['AccountID'], $utc_now, get_remote_ip())) {
			die('<br />'.LANG('DATABASE_ERROR'));
		  }
		  echo ' '.str_replace('%', $login_lock_time, LANG('ACCOUNT_LOCKED'));
		}
	  }
	} else {
	  echo str_replace('%', $time_left, LANG('TEMP_LOCKED'));
	}
  } else {
	echo LANG('NO_SUCH_ACCOUNT');
  }

}
?>