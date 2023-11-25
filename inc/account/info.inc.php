<?php
if (login_state() === 'valid') {

  if (!empty($_POST['email'])) {
    if ($_POST['email'] !== $_SESSION['user_data']['Email']) {
	  if (empty($_POST['email']) || !validate_maxlength($_POST['email'], 50) ||
	  is_injected($_POST['email']) || !check_email_dns($_POST['email'])) {
	    $alert['error'] = true;
	  }
	}
	if (isset($alert['error'])) {
	  $alert['msg'] = LANG('INJECTED_EMAIL');
    } elseif (set_account_info($account_id, 
	$_POST['email'], $_POST['rname'], $_POST['phone'])) {
	  $_SESSION['user_data']['Email'] = $_POST['email'];
	  $_SESSION['user_data']['RealName'] = $_POST['rname'];
	  $_SESSION['user_data']['Phone'] = $_POST['phone'];
	  $alert['error'] = false;
	  $alert['msg'] = LANG('UPDATE_SUCCESS');
	} else {
	  $alert['error'] = true;
	  $alert['msg'] = LANG('DATABASE_ERROR');
	}
  } elseif (!empty($_POST['country'])) {
    if (empty($_SESSION['user_data']['AddressID'])) {
	  $addr_id = create_address($_POST['country'], $_POST['state'], 
	  $_POST['zipcode'], $_POST['suburb'], $_POST['address']);
	  if ($addr_id) {
	    if (link_address($account_id, $addr_id)) {
	      $_SESSION['user_data']['AddressID'] = $addr_id;
	      $alert['error'] = false;
	      $alert['msg'] = LANG('UPDATE_SUCCESS');
		} else {
	      $alert['error'] = true;
	      $alert['msg'] = LANG('DATABASE_ERROR');
		}
	  } else {
	    $alert['error'] = true;
	    $alert['msg'] = LANG('DATABASE_ERROR');
	  }
	} else {
	  if (set_address($_SESSION['user_data']['AddressID'], $_POST['country'], 
	  $_POST['state'], $_POST['zipcode'], $_POST['suburb'], $_POST['address'])) {
	    $alert['error'] = false;
	    $alert['msg'] = LANG('UPDATE_SUCCESS');
	  } else {
	    $alert['error'] = true;
	    $alert['msg'] = LANG('DATABASE_ERROR');
	  }
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

<div class="row-fluid">
  <div class="span6">
	<form name="info_form" method="post" action="">
	  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
	  <h1><?php echo LANG('MY_DETAILS'); ?></h1>
	  <label><?php echo LANG('EMAIL'); ?>:</label>
	  <input required='required' type="text" name="email" value="<?php 
	  safe_echo($_SESSION['user_data']['Email']); ?>" maxlength="50" />
	  <label><?php echo LANG('NAME'); ?>:</label>
	  <input type="text" name="rname" value="<?php 
	  safe_echo($_SESSION['user_data']['RealName']); ?>" maxlength="50" />
	  <label><?php echo LANG('PHONE'); ?>:</label>
	  <input type="text" name="phone" value="<?php 
	  safe_echo($_SESSION['user_data']['Phone']); ?>" maxlength="20" />
	  <br clear="both" />
	  <input type="submit" class="btn" value="<?php echo LANG('APPLY'); ?>" />
	  <hr />
	  <p><?php echo '<b>'.LANG('IMPORTANT').':</b> '.LANG('IF_EMAIL_CHANGED'); ?></p>
	</form>
  </div>
  <div class="span6">
    <form name="address_form" method="post" action="">
	  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
	  <?php
	  if (!empty($_SESSION['user_data']['AddressID'])) {
	    $address_id = (int) $_SESSION['user_data']['AddressID'];
	    $address_arr = mysqli_fetch_assoc(get_address($address_id));
	  }
	  ?>
	  <p><b><?php echo LANG('SHIPPING_ADDRESS'); ?>:</b></p>
	  <label><?php echo LANG('COUNTRY'); ?>:</label>
	  <input type="text" name="country" class="input-large" maxlength="60" value="<?php 
	  if (!empty($_POST['country'])) { safe_echo($_POST['country']); } elseif 
	  (!empty($address_arr['Country'])) { safe_echo($address_arr['Country']); } 
	  ?>" required='required' />
	  <br clear="both" />
	  <div class="float_left">
	    <label><?php echo LANG('STATE'); ?>:</label>
	    <input type="text" name="state" class="input-small" maxlength="50" value="<?php 
	    if (!empty($_POST['state'])) { safe_echo($_POST['state']); } elseif 
	    (!empty($address_arr['State'])) { safe_echo($address_arr['State']); } 
	    ?>" required='required' />
	  </div>
	  <div class="float_left" id="zip_box">
	    <label><?php echo LANG('ZIPCODE'); ?>:</label>
	    <input type="text" name="zipcode" class="input-small" maxlength="10" value="<?php 
	    if (!empty($_POST['zipcode'])) { safe_echo($_POST['zipcode']); } elseif 
	    (!empty($address_arr['Zipcode'])) { safe_echo($address_arr['Zipcode']); } 
	    ?>" required='required' />
	  </div>
	  <br clear="both" />
	  <label><?php echo LANG('SUBURB'); ?>:</label>
	  <input type="text" name="suburb" class="input-large" maxlength="50" value="<?php 
	  if (!empty($_POST['suburb'])) { safe_echo($_POST['suburb']); } elseif 
	  (!empty($address_arr['Suburb'])) { safe_echo($address_arr['Suburb']); } 
	  ?>" required='required' />
	  <label><?php echo LANG('ADDRESS'); ?>:</label>
	  <input type="text" name="address" class="input-large" maxlength="80" value="<?php 
	  if (!empty($_POST['address'])) { safe_echo($_POST['address']); } elseif 
	  (!empty($address_arr['Address'])) { safe_echo($address_arr['Address']); } 
	  ?>" required='required' />
	  <br clear="both" />
	  <input type="submit" class="btn" value="<?php echo LANG('APPLY'); ?>" />
    </form>
  </div>
</div>

<?php
  echo '<p><a class="btn" href="./?page=account">'.LANG('GO_BACK').'</a></p>';
} else {
  require_once('./inc/pages/login.inc.php');
}
?>