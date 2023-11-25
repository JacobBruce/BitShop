<?php 
$errors = '';
$name = '';
$visitor_email = '';
$user_message = '';
$pdone = false;

if (isset($_POST['message'])) {
	
	$name = $_POST['name'];
	$visitor_email = $_POST['email'];
	$user_message = $_POST['message'];
	
	// Do validations
	if (empty($name)||empty($visitor_email)) {
		$errors .= LANG('NAME_EMAIL_REQ');	
	}
	if (is_injected($visitor_email)) {
		$errors .= "\n ".LANG('BAD_EMAIL');
	}
	if (empty($_SESSION['6_letters_code'] ) ||
		strcasecmp($_SESSION['6_letters_code'], $_POST['6_letters_code']) != 0) {
		$errors .= "\n ".LANG('BAD_SEC_CODE');
	}
	
	// send email if no errors
	if (empty($errors)) {
		
		$headers = get_mail_headers($visitor_email);			
		$body = "A user $name submitted the contact form:\n".
		"Name: $name\nEmail: $visitor_email\n".
		"Message:\n\n$user_message\n\nIP: ".get_remote_ip();
		
		if ($smtp_enable) {
			$subject = RAW_LANG('CONT_FORM_SUBJECT');
			if (send_smtp_email($contact_email, $subject, $body, $visitor_email) === true) {
				$pdone = true;
			} else {
				$errors .= "\n ".LANG('ERROR_SEND_EMAIL').' '.LANG('TRY_AGAIN_LATER');
			}
		} else {
			$subject = rfc1342b(RAW_LANG('CONT_FORM_SUBJECT'));
			if (mail($contact_email, $subject, $body, $headers)) {
				$pdone = true;
			} else {
				$errors .= "\n ".LANG('ERROR_SEND_EMAIL').' '.LANG('TRY_AGAIN_LATER');
			}
		}
	}
}

if ($pdone == false) { ?>

<center>
  <h1><?php echo LANG('CONTACT_TITLE'); ?></h1>
  <?php
	if(!empty($errors)){
	  echo "<p class='error_txt'>".nl2br($errors)."</p>";
	}
  ?>
  <form method="POST" name="contact_form" action="./?page=contact"> 

	<label for='name'><?php echo LANG('NAME'); ?>:</label>
	<input type="text" name="name" value="<?php safe_echo($name); ?>" required='required'>

	<label for='email'><?php echo LANG('EMAIL'); ?>:</label>
	<input type="email" name="email" value="<?php safe_echo($visitor_email); ?>" required='required'>

	<label for='message'><?php echo LANG('MESSAGE'); ?>:</label>
	<textarea name="message" required='required'><?php safe_echo($user_message); ?></textarea>

	<?php if ($show_captcha) { ?>
	<br clear="both" />
	<img src="inc/captcha_code_file.php?rand=<?php echo rand(); ?>" id="captchaimg" /><br />
	<small><?php echo LANG('CANT_READ_IMG'); ?> <a href='javascript: refreshCaptcha();'><?php 
	echo LANG('CLICK_HERE'); ?></a> <?php echo LANG('TO_REFRESH'); ?></small>
	<label for='message'><?php echo LANG('REPEAT_SEC_CODE'); ?>:</label>
	<input name="6_letters_code" width="200" maxlength="6" type="text" required='required'>

	<?php
	} else {
	  $_SESSION['6_letters_code'] = 'abc';
	  echo '<input type="hidden" name="6_letters_code" value="abc" />';
	}
	?>
	<br />
	<button class="btn" type="submit"><?php echo LANG('SUBMIT'); ?></button>
  </form>
</center>

<?php 
} else { 
  echo '<h3>'.LANG('MESSAGE_SENT').'</h3>';
} ?>