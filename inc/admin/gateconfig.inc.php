<?php
admin_valid();

$options = '';
$gate = isset($_POST['gateway']) ? $_POST['gateway'] : 'default';

foreach ($gateways as $key => $value) {
  $sel_txt = ($gate == $key) ? 'selected="selected"' : '';
  $options .= "<option value='$key' $sel_txt>$key</option>\n";
}
?>

<div class="alert no_display" id="error_box">
  <span id='error_msg'></span>
</div>
	  
<div class="row-fluid">
  <div class="span6">
  
	<div id="form_box"><p>Loading ...</p></div>
	
  </div>
  <div class="span6">

	<p><b>Configure a Gateway:</b></p>
	<select id="gate_list">
	  <option value='default' <?php if ($gate == 'default') 
	  { echo 'selected="selected"'; } ?>>default</option>
	  <?php echo $options; ?>
	</select>
	
  </div>
</div>

<script language="JavaScript">
function handle_error(response) {
    $('#error_box').show();
    $('#error_msg').html(response);
}

function handle_success(response) {
	$('#error_box').hide();
	$('#form_box').html(response);
}
	  
function load_config(gate) {
	ajax_get('./inc/jobs/getfile.inc.php', 
	'gate='+gate, handle_success, handle_error);
}

$('#gate_list').change(function() {
	load_config(this.value);
});

$(document).ready(function() {
	load_config($('#gate_list').val());
});
</script>