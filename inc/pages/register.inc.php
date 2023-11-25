<form name="register_form" id="register_form" method="post" action="">
  <div class="alert no_display" id="error_box">
	<span id='error_msg'></span>
  </div>
  <h1><?php echo LANG('REGISTER_TITLE'); ?></h1>
  <table class="form-horizontal" id="form_table" cellpadding="4px">
	<tr>
	  <td><b><?php echo LANG('EMAIL'); ?>:</b></td>
	  <td><input type="text" name="email" id="email" maxlength="50" value="" required="required" /></td>
	</tr>
	<tr>
	  <td><b><?php echo LANG('PASSWORD'); ?>:&nbsp;</b></td>
	  <td><input type="password" name="pass_txt" id="pass_txt" maxlength="99" required="required" /></td>
	</tr>
	<tr>
	  <td><b><?php echo LANG('REPEAT'); ?>:&nbsp;</b></td>
	  <td><input type="password" name="pass_rep" id="pass_rep" maxlength="99" required="required" /></td>
	</tr>
  </table>
  <br clear="both" />
  <img src="inc/captcha_code_file.php?rand=<?php echo rand(); ?>" id="captchaimg" /><br />
  <small><?php echo LANG('CANT_READ_IMG'); ?> <a href='javascript: refreshCaptcha();'><?php 
  echo LANG('CLICK_HERE'); ?></a> <?php echo LANG('TO_REFRESH'); ?></small>
  <label for='message'><?php echo LANG('REPEAT_SEC_CODE'); ?>:</label>
  <input name="6_letters_code" id="captcha" width="200" maxlength="6" type="text" required='required'>
  <br />
  <div class="float_left">
	<input type="submit" class="btn" value="<?php echo LANG('SUBMIT'); ?>" />
  </div>
</form>

<script language="javascript">
function handle_submit(response) {
  var res_arr = response.split(':');
  if (res_arr[0] == 'success') {
    $('#error_box').show().removeClass('alert-error').addClass('alert-success');
    $('#error_msg').html('Registration succeeded! Redirecting ...');
    setTimeout(function(){redirect('./?page=account');}, 1000);
  } else {
    $('#error_box').show().removeClass('alert-success').addClass('alert-error');
    $('#error_msg').html(response);
  }
}

function handle_error(response) {
    $('#error_box').show().removeClass('alert-success').addClass('alert-error');
    $('#error_msg').html(response);
}

$("#register_form").submit(function(event) {
  event.preventDefault();
  var pass_txt = $("#pass_txt").val();
  var pass_rep = $("#pass_rep").val();
  if (pass_txt == pass_rep) {
    var acc_email = $("#email").val();
    var captcha = $("#captcha").val();
    var password = encodeURIComponent(pass_txt);
    ajax_post('./inc/jobs/register.inc.php', 
    'email='+acc_email+'&pass='+password+'&code='+
    captcha, handle_submit, handle_error);
  } else {
    handle_error('<?php echo LANG('DO_NOT_MATCH'); ?>');
  }
});
</script>