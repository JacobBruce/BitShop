<?php
$show_form = true;
$show_ship = shipping_req();

if (!isset($_SESSION['shipping']) || !isset($_SESSION['sum_total']) 
|| !isset($_SESSION['cart']) || count($_SESSION['cart']) < 1) {

  echo "<p class='error_txt'>".LANG('SESSION_RESET').' '.LANG('TRY_AGAIN_BACK')."</p>";

} else {
  
  if (!empty($_POST['email'])) {
	if (is_injected($_POST['email']) || !validate_maxlength($_POST['email'], 50)) {  
	  $errors['order_form'] = LANG('INJECTED_EMAIL');	
	} elseif(empty($_POST['c_email']) || ($_POST['email'] != $_POST['c_email'])) {
	  $errors['order_form'] = LANG('DIFF_EMAIL_ADDS');
	} elseif (!empty($_SESSION['user_data']) && $_SESSION['user_data']['Email'] != $_POST['email']) {
	  $errors['order_form'] = LANG('INJECTED_EMAIL');
	} elseif (!empty($_POST['note']) && strlen($_POST['note'] > 200)) {
	  $errors['order_form'] = LANG('NOTE_TOO_LONG');
	} elseif(empty($_SESSION['6_letters_code']) || 
	strcasecmp($_SESSION['6_letters_code'], $_POST['6_letters_code']) != 0) {
	  $errors['order_form'] = LANG('BAD_SEC_CODE');	
	} else {

	  if (!empty($_POST['client_type']) && ($_POST['client_type'] == 'tcon')) {
		$_SESSION['client_type'] = 'tcon';
	  } else {
		$_SESSION['client_type'] = 'ncon';
	  }

	  if (empty($_POST['note'])) {
	    $_SESSION['order_note'] = 'n/a';
	  } else {
	    $_SESSION['order_note'] = $_POST['note'];
	  }
	  
	  if (empty($_POST['gateway'])) {
	    $_SESSION['gateway'] = 'default';
	  } else {
	    $_SESSION['gateway'] = $_POST['gateway'];
	  }
	  
	  if ($show_ship) {
	    if (empty($_POST['suburb']) || empty($_POST['country']) ||
		empty($_POST['state']) || empty($_POST['address']) ||
		(empty($_POST['zipcode']) && strlen($_POST['zipcode']) <= 1)) {
		  $errors['order_form'] = LANG('SHIP_ADDRESS_REQ');
		} else {
	      $_SESSION['address'] = $_POST['address']."\n".$_POST['suburb'].
		  ", ".$_POST['state'].', '.$_POST['zipcode']."\n".$_POST['country'];
		}
	  } else {
	    $_SESSION['address'] = 'n/a';
	  }
	  
	  if (!isset($account_id)) {
		  $_SESSION['buyer_email'] = $_POST['email'];
		  $account = get_account_byemail($_POST['email']);
		  
		  if (empty($account) || ($account === 'N/A')){
			if (check_email_dns($_POST['email'])) {
			
			  $password = generate_password();
			  $_SESSION['account'] = create_account($_POST['email'], pass_hash($password, $hash_rounds));
			  
			  if ($_SESSION['account']) {		  
				$body = safe_str(str_replace('%', $seller, RAW_LANG('THANKS_SHOPPING')))."\n". 
				LANG('NEW_ACC_CREATED')."\n".LANG('LOGIN_WITH')."\n\n".
				LANG('EMAIL').": ".$_POST['email']." \n".
				LANG('PASSWORD').": $password";
				  
				if ($smtp_enable) {
				  $subject = RAW_LANG('NEW_ACCOUNT');
				  send_smtp_email($_POST['email'], $subject, $body);
				} else {
				  $subject = rfc1342b(RAW_LANG('NEW_ACCOUNT'));
				  mail($_POST['email'], $subject, $body, get_mail_headers());
				}
				  
			  } else {
				$errors['order_form'] = LANG('DATABASE_ERROR');
			  }
			} else {
			  $errors['order_form'] = LANG('INJECTED_EMAIL');
			}
		  } else {
			$errors['order_form'] = LANG('ALREADY_IN_USE').' <a href="./?page=login">'.LANG('GO_TO').' '.LANG('LOGIN_TITLE').'</a>';
		  }  
	  } else {
		  $_SESSION['buyer_email'] = $_SESSION['user_data']['Email'];
		  $_SESSION['account'] = $account_id;
	  }

	  if (empty($errors['order_form'])) {
	    echo "<p>".LANG('WAIT_WHILE')."</p>";
	    redirect('./sci/process-order.php');
	    $show_form = false;
	  }
	}
  } elseif (!empty($_POST)) {
	$errors['order_form'] = LANG('EMPTY_EMAIL');
  }
  
  if ($show_form) {
?>

<?php if (!empty($errors['order_form'])) { ?>
<div class="alert alert-block alert-error">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <?php echo $errors['order_form']; ?>
</div>
<?php } ?>

<form name="order_form" method="post" action="">

	<div class="row-fluid">
	  <div class="span6">

		<h1><?php echo LANG('YOUR_ORDER'); ?>:</h1>
		<div class="well no_border">
		  <?php
		  $ship_total = ($_SESSION['shipping'] === '-1') ? '0' : $_SESSION['shipping'];
		  if (!manual_delivery_req()) {
		    if (bccomp($ship_total, '0') == 0) {
			  $_SESSION['shipping'] = '-1';
		    }
		  }
		  $shs_btc = bitsci::btc_num_format($ship_total);
		  $sub_btc = bitsci::btc_num_format($_SESSION['sub_total']);
		  $sum_btc = bitsci::btc_num_format($_SESSION['sum_total']);
		  ?>
		  <p><b><?php echo LANG('SUB_TOTAL'); ?>:</b> <?php echo $sub_btc; ?> BTC</p>
		  <p><b><?php echo LANG('SHIPPING'); ?>:</b> <?php echo $shs_btc; ?> BTC</p>
		  <p><b><?php echo LANG('TOTAL'); ?>:</b> <?php echo $sum_btc; ?> BTC 
		  (<?php echo $curr_symbol.$_SESSION['sum_cfiat'].' '.$curr_code; ?>)</p>
		</div>
		
		<p>
		  <label id="conn_label" title="<?php echo LANG('IF_CONN_THROUGH'); 
		  ?>"><b><?php echo LANG('SELECT_CONN_TYPE'); ?>:</b><span><sup><a 
		  href="#" onclick="show_tooltip();">?</a></sup></span></label>
		  <?php echo LANG('NORMAL_CLIENT'); ?>: 
		  <input type="radio" name="client_type" value="ncon"<?php 
		  if ($_SESSION['client_type'] === 'ncon') { echo ' checked="checked"'; } ?> />
		  &nbsp;&nbsp;
		  <?php echo LANG('TOR_CLIENT'); ?>: 
		  <input type="radio" name="client_type" value="tcon"<?php 
		  if ($_SESSION['client_type'] === 'tcon') { echo ' checked="checked"'; } ?> />
		</p>
		
		<br />
		<label><b><?php echo LANG('PAYMENT_GATEWAY'); ?>:</b></label> 
		<select name="gateway" id="gateway_list" class="input-medium">
		  <?php if ($use_defgate) { ?>
		  <option value="default" selected="selected"><?php 
		  echo LANG('DEFAULT').' '.LANG('GATEWAY'); ?></option>
		  <?php } 	  
		  foreach ($gateways as $key => $value) {
			if ($gateways[$key][0]) {
		      echo "<option value='$key'>".safe_str($value[1]).' '.LANG('GATEWAY').'</option>';
			}
		  }
		  ?>
		</select>

		<br /><br />
		<p><b><?php echo LANG('PAY_METHODS'); ?>:</b></p>
		<p><?php if ($use_defgate) {
		echo LANG('DEFAULT').' '.LANG('GATEWAY').': BTC';
		if ($use_altrpc) {
		  echo ', '.safe_str($altcoin_code);
		}
		?><br /><?php } 	  
		foreach ($gateways as $key => $value) {
		  if ($gateways[$key][0]) {
		    echo safe_str($value[1]).' '.LANG('GATEWAY').': '.safe_str($value[2]).'<br />';
		  }
	    }
		?></p>
		
		<?php if ($show_captcha && $show_ship) { ?>
		<br clear="both" />
		<img src="inc/captcha_code_file.php?rand=<?php echo rand(); ?>" id="captchaimg" /><br />
		<small><?php echo LANG('CANT_READ_IMG'); ?> <a href='javascript: refreshCaptcha();'><?php
		echo LANG('CLICK_HERE'); ?></a> <?php echo LANG('TO_REFRESH'); ?></small>
		<label for='message'><?php echo LANG('REPEAT_SEC_CODE'); ?>:</label>
		<input type="text" name="6_letters_code" class="input-large" maxlength="6" required='required' />
		<?php
		} elseif ($show_ship) {
		  $_SESSION['6_letters_code'] = 'abc';
		  echo '<input type="hidden" name="6_letters_code" value="abc" />';
		}
		?>
	
	  </div>
	  <div class="span6">
	  
		<h5><?php echo LANG('COMPLETE_ORDER'); ?>:</h5>
		<label><?php echo LANG('EMAIL'); ?>:</label>
		<input type="text" name="email" id="email" class="input-large" maxlength="50" <?php 
		if (!empty($_SESSION['user_data'])) {
		  echo 'value="';
		  safe_echo($_SESSION['user_data']['Email']);
		  echo '" readonly';
		} elseif (!empty($_POST['email'])) {
		  echo 'value="';
		  safe_echo($_POST['email']);
		  echo "\" required='required'";
		}
		?> />
		<?php if (empty($_SESSION['user_data'])) { ?>
		<br clear="both" />
		<label><?php echo LANG('CONFIRM_EMAIL'); ?>:</label>
		<input type="text" name="c_email" id="c_email" class="input-large" maxlength="50" value="<?php 	
		  if (!empty($_POST['c_email'])) { safe_echo($_POST['c_email']); } ?>" required='required' />
		<?php
		} else {
		  echo '<input type="hidden" name="c_email" id="c_email" maxlength="50" value="';
		  safe_echo($_SESSION['user_data']['Email']);
		  echo '" />';
		}
		?>
		<br clear="both" />
		<label><?php echo LANG('NOTE').' ('.LANG('OPTIONAL').')'; ?>:</label>
		<textarea name="note" id="note" class="input-large" maxlength="500"><?php 
		if (!empty($_POST['note'])) { safe_echo($_POST['note']); } ?></textarea>
	  
	    <?php
		if (!empty($_SESSION['user_data']['AddressID'])) {
		  $address_id = (int) $_SESSION['user_data']['AddressID'];
		  $address_arr = mysqli_fetch_assoc(get_address($address_id));
		}
		
		if ($show_ship) {
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
		<?php } ?>
		
		<?php if ($show_captcha && !$show_ship) { ?>
		<br clear="both" />
		<img src="inc/captcha_code_file.php?rand=<?php echo rand(); ?>" id="captchaimg" /><br />
		<small><?php echo LANG('CANT_READ_IMG'); ?> <a href='javascript: refreshCaptcha();'><?php
		echo LANG('CLICK_HERE'); ?></a> <?php echo LANG('TO_REFRESH'); ?></small>
		<label for='message'><?php echo LANG('REPEAT_SEC_CODE'); ?>:</label>
		<input type="text" name="6_letters_code" class="input-large" maxlength="6" required='required' />
		<?php
		} elseif (!$show_ship) {
		  $_SESSION['6_letters_code'] = 'abc';
		  echo '<input type="hidden" name="6_letters_code" value="abc" />';
		}
		?>

	  </div>
	</div>

	<hr />
	<center>
	  <a class="btn" href="./?page=cart"><?php echo LANG('GO_BACK'); ?></a>
	  <button type="submit" class="btn btn-primary"><?php echo LANG('GO_TO').
	  ' '.LANG('PAYMENT_GATEWAY'); ?></button>
	</center>

</form>

<?php if (empty($_SESSION['user_data'])) { ?>
<div class='alert alert-warning'>
  <?php echo '<b>'.LANG('IMPORTANT').':</b> '.
  LANG('IF_NOT_REGISTERED').' '.LANG('ENSURE_EMAIL_VALID'); ?>
</div>
<?php } ?>

<script language="JavaScript">
function show_tooltip() {
  $('#conn_label').tooltip('show');
}
</script>

<?php
  }
}
?>