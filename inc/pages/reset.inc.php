<?php
if (empty($_GET['id']) || empty($_GET['code'])) {
  echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
} else {
?>
<div class="alert no_display" id="error_box">
  <span id='error_msg'></span>
</div>
	  
<h1><?php echo LANG('RESET_PASS'); ?></h1>

<form name="reset_form" id="reset_form" method="post" action="">
  <label><?php echo LANG('NEW_PASS'); ?>:</label>
  <input type="password" name="npass" id="npass" value="" maxlength="99" required />
  <label><?php echo LANG('REPEAT'); ?>:</label>
  <input type="password" name="rpass" id="rpass" value="" maxlength="99" required />
  <br clear="both" />
  <input type="submit" class="btn" value="<?php echo LANG('APPLY'); ?>" />
</form>

<script language="javascript">
var aid = '<?php echo (int)$_GET['id']; ?>';
var code = '<?php echo $_GET['code']; ?>';

function handle_reset(response) {
  if (response == 'success') {
    $('#error_box').show().removeClass('alert-error').addClass('alert-success');
    $('#error_msg').html('<?php echo LANG('UPDATE_SUCCESS'); ?>');
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
  var npass = $("#npass").val();
  var rpass = $("#rpass").val();
  if (npass != rpass) {
    handle_error('<?php echo LANG('DO_NOT_MATCH'); ?>');
  } else {
    ajax_post('./inc/jobs/reset.inc.php', 
    'id='+aid+'&code='+code+'&pass='+npass, 
    handle_reset, handle_error);
  }
});
</script>
<?php } ?>