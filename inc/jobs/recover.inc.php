<?php
require_once(dirname(__FILE__).'/../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/../session.inc.php');
require_once(dirname(__FILE__)."/../langs/$locale.inc.php");

if (!empty($_POST['email'])) {

  if (empty($_POST['email'])) {
    die(LANG('EMAIL_EMPTY'));
  } elseif (is_injected($_POST['email']) || !check_email_dns($_POST['email'])) {
    die(LANG('INJECTED_EMAIL'));
  }
  
  $hide_crash = true;
  $conn = connect_to_db();
  $account = get_account_byemail($_POST['email']); 
  
  if (!empty($account) && $account !== 'N/A') {
    $account = mysqli_fetch_assoc($account);
  } else {
    die(LANG('NO_SUCH_ACCOUNT'));
  }

  $message = LANG('RESET_REQUESTED').":\r\n\r\n";
  $message .= $account['Email']."\r\n\r\n";
  $message .= LANG('VISIT_LINK_BELOW').":\r\n\r\n";
  $message .= $base_url.'?page=reset&id='.$account['AccountID'];
  $message .= '&code='.hash('sha256', $account['PassHash']);

  if ($smtp_enable) {
    $subject = RAW_LANG('ACCOUNT_RECOVERY');
    if (send_smtp_email($_POST['email'], $subject, $message) === true) {
	  echo "success:".$_POST['email'];
    } else {
	  echo LANG('ERROR_SEND_EMAIL');
    }
  } else {
    $subject = rfc1342b(RAW_LANG('ACCOUNT_RECOVERY'));
    if (mail($_POST['email'], $subject, $message, get_mail_headers())) {
	  echo "success:".$_POST['email'];
    } else {
	  echo LANG('ERROR_SEND_EMAIL');
    }
  }
}
?>