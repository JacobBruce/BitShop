<?php
require_once(dirname(__FILE__).'/../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/../session.inc.php');
require_once(dirname(__FILE__)."/../langs/$locale.inc.php");

$hide_crash = true;
$conn = connect_to_db();

$utc_now = mysqli_now();

if (!empty($_POST['email'])) {
  if (empty($_SESSION['6_letters_code']) ||
	strcasecmp($_SESSION['6_letters_code'], $_POST['code']) != 0) {
	echo LANG('BAD_SEC_CODE');
  } elseif (empty($_POST['pass'])) {
    echo LANG('PASS_EMPTY');
  } elseif (empty($_POST['email']) || !validate_maxlength($_POST['email'], 50)) {
	echo LANG('EMAIL_EMPTY');
  } elseif(is_injected($_POST['email']) || !check_email_dns($_POST['email'])) {
	echo LANG('INJECTED_EMAIL');
  } else {
    $pass_msg = check_pass_strength($_POST['pass']);
	if ($pass_msg !== true) {
	  echo $pass_msg; 
	} else {
	  $account = get_account_byemail($_POST['email']);
	  if (empty($account) || ($account === 'N/A')){
	    unset($_SESSION['6_letters_code']);
	    $pass_hash = pass_hash($_POST['pass'], $hash_rounds);
	    $account_id = create_account($_POST['email'], $pass_hash);
	    if ($account_id) {		
		  $body = LANG('NEW_ACC_CREATED')."\n".LANG('LOGIN_WITH')."\n\n".
		  LANG('EMAIL').": ".$_POST['email']." \n".
		  LANG('PASSWORD').": ".$_POST['pass'];
		  if ($smtp_enable) {
		    $subject = RAW_LANG('NEW_ACCOUNT');
			send_smtp_email($_POST['email'], $subject, $body);
		  } else {
			$subject = rfc1342b(RAW_LANG('NEW_ACCOUNT'));
			mail($_POST['email'], $subject, $body, get_mail_headers());	  
		  }
		  echo "success:$account_id";
		} else {
		  echo LANG('DATABASE_ERROR');
		}
	  } else {
		echo LANG('ALREADY_IN_USE');
	  }
	}
  }
}
?>