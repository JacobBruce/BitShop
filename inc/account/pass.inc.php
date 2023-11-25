<?php
if (login_state() === 'valid') {

  if (!empty($_POST['opass'])) {
    $pass_hash = pass_hash($_POST['opass'], $hash_rounds);
	if ($pass_hash === $_SESSION['user_data']['PassHash']) {
      if ($_POST['opass'] !== $_POST['npass']) {
	    if ($_POST['npass'] === $_POST['rpass']) {
	      $pass_msg = check_pass_strength($_POST['npass']);
	      if ($pass_msg !== true) {
	        $alert['error'] = true;
		    $alert['msg'] = $pass_msg;
	      } else {
		    $new_hash = pass_hash($_POST['npass'], $hash_rounds);
			if (set_account_pass($account_id, $new_hash)) {
			  $_SESSION['user_data']['PassHash'] = $new_hash;
			  $alert['error'] = false;
			  $alert['msg'] = LANG('UPDATE_SUCCESS');
			} else {
			  $alert['error'] = true;
			  $alert['msg'] = LANG('DATABASE_ERROR');
			}
		  }
	    } else {
	      $alert['error'] = true;
	      $alert['msg'] = LANG('DO_NOT_MATCH');
	    }
	  } else {
	    $alert['error'] = true;
	    $alert['msg'] = LANG('SAME_PASSWORDS');
      }
	} else {
	  $alert['error'] = true;
	  $alert['msg'] = LANG('PASS_INCORRECT');
	}
  }

  if (!empty($alert['msg'])) {
?>
<div class="alert <?php if ($alert['error']) { echo 
'alert-error'; } else { echo 'alert-success'; } ?>">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <?php echo $alert['msg']; ?>
</div>
<?php } ?>

<form name="pass_form" method="post" action="">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <h1><?php echo LANG('CHANGE_PASS'); ?></h1>
  <label><?php echo LANG('OLD_PASS'); ?>:</label>
  <input type="password" name="opass" value="" maxlength="99" required />
  <label><?php echo LANG('NEW_PASS'); ?>:</label>
  <input type="password" name="npass" value="" maxlength="99" required />
  <label><?php echo LANG('REPEAT'); ?>:</label>
  <input type="password" name="rpass" value="" maxlength="99" required />
  <br clear="both" />
  <a class="btn" href="./?page=account"><?php echo LANG('GO_BACK'); ?></a> 
  <input type="submit" class="btn" value="<?php echo LANG('APPLY'); ?>" />
</form>

<?php
} else {
  require_once('./inc/pages/login.inc.php');
}
?>