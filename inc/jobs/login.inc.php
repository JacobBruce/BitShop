<?php
require_once(dirname(__FILE__).'/../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/../session.inc.php');
require_once(dirname(__FILE__)."/../langs/$locale.inc.php");

$hide_crash = true;
$conn = connect_to_db();
$utc_now = mysqli_now();

if (($_SERVER['REQUEST_METHOD'] == 'POST') && !empty($_POST['email'])) {
  if (empty($_POST['pass'])) {
    echo LANG('PASS_EMPTY');
  } else {
    if (!validate_maxlength($_POST['email'], 50) || strlen($_POST['pass']) <> 64) {
	  echo LANG('INVALID_ACTION');
	} else {
	  $account = get_account_byemail($_POST['email']);
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
		  $toke_hash = hash('sha256', get_ip_hash().$account['PassHash']);
		  if ($_POST['pass'] == $toke_hash) {
			$perms = get_perms($account['PermGroup']);
		    if (isset($_POST['admin'])) {
			  if ($perms['admin_access'] !== true) {
			    die(LANG('INVALID_ACCESS'));
			  }
			}
		    set_last_time($account['AccountID'], $utc_now, get_remote_ip());
			session_regenerate_id();
	        $_SESSION['ip_address'] = get_remote_ip();
	        $_SESSION['lock_ip'] = (bool) $_POST['lock'];
			$_SESSION['user_data'] = $account;
			$_SESSION['user_perms'] = $perms;
			$_SESSION['csrf_token'] = bin2hex(crypt_random_string(32));
			if (!empty($account['Settings'])) {
			  $settings = json_decode($account['Settings'], true);
			}
			if (!empty($settings['curr'])) {
			  $_SESSION['curr_code'] = $settings['curr'];
			}
			if (!empty($settings['lang'])) {
			  $_SESSION['language'] = $settings['lang'];
			}
			if (!empty($settings['zone'])) {
			  $_SESSION['time_zone'] = $settings['zone'];
			}
			if (!empty($settings['clit'])) {
			  $_SESSION['client_type'] = $settings['clit'];
			}
			echo "success";
		  } else {
			$account['FailCount']++;
			if (!set_lock_count($account['AccountID'], $account['FailCount'])) {
			  die(LANG('DATABASE_ERROR'));
			}
			echo LANG('PASS_INCORRECT');
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
  }
}
?>