<?php
if (isset($alert['error'])) {
  if ($alert['error']) {
    $alert['msg'] = LANG('UPDATE_ERROR').' '.LANG('TRY_AGAIN_LATER');
  } else {
    $alert['msg'] = LANG('UPDATE_SUCCESS');
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

<h1><?php echo LANG('SETTINGS_TITLE'); ?></h1>

<form name="settings_form" method="post" action="">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <label for="curr_list"><b><?php echo LANG('REF_CURR'); ?>:</b></label>
  <select name="curr_list" class="input-medium">
    <?php
	foreach ($market_data as $key => $value) {
	  $sel = ($key == $_SESSION['curr_code']) ? ' selected="selected"' : '';
	  echo "<option value='$key'$sel>$key</option>\n";
	}
	?>
  </select>

  <label for="lang_list"><b><?php echo LANG('SELECT_LANG'); ?>:</b></label>
  <select name="lang_list" class="input-medium">
    <?php
	foreach ($langarray as $key => $value) {
	  $sel = ($key == $_SESSION['language']) ? ' selected="selected"' : '';
	  echo "<option value='$key'$sel>$value</option>\n";
	}
	?>
  </select>
  
  <label for="time_list"><b><?php echo LANG('SELECT_TIMEZONE'); ?>:</b></label>
  <select name="time_list" class="input-medium">
    <?php
	foreach ($timezones as $key => $value) {
	  $sel = ($value == $_SESSION['time_zone']) ? ' selected="selected"' : '';
	  echo "<option value='$value'$sel>$key</option>";
	}
	?>
  </select>
  
  <label><b><?php echo LANG('SELECT_CONN_TYPE'); ?>:</b></label>
  <?php echo LANG('NORMAL_CLIENT'); ?>: 
  <input type="radio" name="client_type" value="ncon"<?php 
  if ($_SESSION['client_type'] === 'ncon') { echo ' checked="checked"'; } ?> />
  &nbsp;&nbsp;
  <?php echo LANG('TOR_CLIENT'); ?>: 
  <input type="radio" name="client_type" value="tcon"<?php 
  if ($_SESSION['client_type'] === 'tcon') { echo ' checked="checked"'; } ?> />  
  <br /><br />

  <?php
  if (login_state() !== 'valid') {
	echo '<button type="submit" class="btn">'.LANG('APPLY').'</button><hr />';
	echo '<p><b>'.LANG('NOTE').':</b> '.LANG('SETT_NOTE').
	' <a href="./?page=register">'.LANG('NEW_ACCOUNT').'</a>.</p>';
  } elseif ($page === 'account') {
	echo '<a class="btn" href="./?page=account">'.LANG('GO_BACK').'</a> ';
	echo '<button type="submit" class="btn">'.LANG('APPLY').'</button>';
  } else {
	echo '<button type="submit" class="btn">'.LANG('APPLY').'</button>';
  }
  ?>
</form>