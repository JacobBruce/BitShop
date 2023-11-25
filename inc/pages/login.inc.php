<div class="row-fluid">
  <div class="span7">
	<form name="login_form" id="login_form" method="post" action="">
	  <div class="alert no_display" id="error_box">
		<span id='error_msg'></span>
	  </div>
	  <h1><?php 
	  if (isset($_GET['admin'])) {
	    echo LANG('ADLOG_TITLE');
      } else {
	    echo LANG('LOGIN_TITLE');
      }
	  ?></h1>
	  <table class="form-horizontal" id="form_table" cellpadding="4px">
		<tr>
		  <td><b><?php if (isset($_GET['admin'])) { 
		  echo LANG('USERNAME'); } else { echo LANG('EMAIL'); } ?>:</b></td>
		  <td><input type="text" name="email" id="email" 
		  class="login_input" maxlength="50" value="" required="required" /></td>
		</tr>
		<tr>
		  <td><b><?php echo LANG('PASSWORD'); ?>:</b></td>
		  <td><input type="password" name="pass_txt" id="pass_txt" 
		  class="login_input" maxlength="99" required="required" /></td>
		</tr>
	  </table>
	  <br />
	  <div class="float_left">
	    <label id="conn_label" title="<?php echo LANG('IF_CONN_THROUGH'); 
		?>"><?php echo LANG('SELECT_CONN_TYPE'); ?>:<span><sup><a 
		href="#" onclick="show_tooltip();">?</a></sup></span></label>
	    <small><?php echo LANG('NORMAL_CLIENT'); ?>: </small>
		<input type="radio" name="lock" id="lock1" value="1" checked="checked" />
	    &nbsp;<small><?php echo LANG('TOR_CLIENT'); ?>: </small>
		<input type="radio" name="lock" id="lock0" value="0" />
	  </div>
	  <div class="float_left">
	    <input type="submit" class="btn login_btn" value="<?php echo LANG('LOGIN'); ?>" />
	  </div>
	  <br clear="both" />
	</form>
  </div>
  <div class="span5">
    <?php if (!isset($_GET['admin'])) { ?>
    <h3><?php echo LANG('DONT_HAVE_ACCOUNT'); ?></h3>
	<p><?php echo LANG('TAKE_A_MOMENT'); ?> <a href="./?page=register"><?php echo LANG('NEW_ACCOUNT'); ?></a>.</p>
    <h3><?php echo LANG('FORGOT_PASS'); ?></h3>
	<p><?php echo LANG('ENTER_YOUR_EMAIL'); ?>:</p>
	<form name="reset_form" id="reset_form" class="form-horizontal" method="post" action="">
	  <div class="input-append">
	    <input type="text" name="reset_add" id="remail" class="input-medium" maxlength="99" required />
	    <input type="submit" id="reset_btn" class="btn" value="<?php echo LANG('SUBMIT'); ?>" />
	  </div>
	</form>
	<?php } ?>
  </div>
</div>

<script language="javascript">
var ip_hash = '<?php safe_echo(get_ip_hash()); ?>';
var rounds = <?php echo $hash_rounds; ?>;
var qry_str = '<?php echo url_query_str(); ?>';

function show_tooltip() {
  $('#conn_label').tooltip('show');
}

function hash_pass(pass) {
  var result = CryptoJS.SHA256(CryptoJS.SHA256(pass)+pass);
  for (var i=0; i<rounds; i++) {
    result = CryptoJS.SHA256(result.toString());
  }
  return result.toString();
}

function handle_login(response) {
  var res_arr = response.split(':');
  if (res_arr[0] == 'success') {
    $('#error_box').show().removeClass('alert-error').addClass('alert-success');
    $('#error_msg').html('<?php echo LANG('CREDS_VERIFIED'); ?>');
	<?php if (isset($_GET['admin'])) { ?>
    setTimeout(function(){redirect('./admin.php'+qry_str);}, 1000);
	<?php } else { ?>
	setTimeout(function(){redirect('./?page=account');}, 1000);
	<?php } ?>
  } else {
    $('#error_box').show().removeClass('alert-success').addClass('alert-error');
    $('#error_msg').html(response);
  }
}

function handle_reset(response) {
  var res_arr = response.split(':');
  if (res_arr[0] == 'success') {
    $('#error_box').show().removeClass('alert-error').addClass('alert-success');
    $('#error_msg').html('<?php echo LANG('RECOV_EMAIL_SENT'); ?>: '+res_arr[1]);
  } else {
    $('#error_box').show().removeClass('alert-success').addClass('alert-error');
    $('#error_msg').html(response);
  }
}

function handle_error(response) {
    $('#error_box').show().removeClass('alert-success').addClass('alert-error');
    $('#error_msg').html(response);
}

$("#reset_form").submit(function(event) {
  event.preventDefault();
  var r_email = $("#remail").val();
  ajax_post('./inc/jobs/recover.inc.php', 
  'email='+r_email, handle_reset, handle_error);
});

$("#login_form").submit(function(event) {
  event.preventDefault();
  var pass_text = $("#pass_txt").val();
  var acc_email = $("#email").val();
  var ip_lock = $('input[name=lock]:checked', '#login_form').val();
  var pass_hash = hash_pass(pass_text);
  var toke_hash = CryptoJS.SHA256(ip_hash+pass_hash);
  ajax_post('./inc/jobs/login.inc.php', 
  'email='+acc_email+'&pass='+toke_hash+
  '&lock='+ip_lock<?php if (isset($_GET['admin'])) 
  { echo "+'&admin=1'"; } ?>, handle_login, handle_error);
});
</script>