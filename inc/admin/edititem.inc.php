<?php admin_valid();

if (!empty($_GET['fid'])) {
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $meth = safe_sql_str($_POST['method']);
    $type = safe_sql_str($_POST['type']);
    $name = safe_sql_str($_POST['name']);
    $desc = safe_sql_str($_POST['desc']);
    $tags = safe_sql_str($_POST['tags']);
	$code = safe_sql_str($_POST['code']);
	$ship_type = safe_sql_str($_POST['ship_type']);
	$ship_data = safe_decimal($_POST['ship_data']);
    $price = safe_decimal($_POST['price']);
    $stock = safe_decimal($_POST['stock']);
    $cats = safe_sql_str(implode(',', $_POST['cats']));
	$curr = $_POST['currency'];
	
	if (($meth == 'ship' && $ship_data == '') || ($meth != 'ship' && $type == '') || $stock == '' ||  
	empty($cats) || empty($name) || empty($price) || empty($curr) || empty($desc) || empty($code)) {
	  $msg = "<p class='error_txt'>None of the fields can be empty except for tags!</p>";
	} else {
	  $price = ($curr == 'BTC') ? -$price : $price;
	  $tags = trim($tags, ", ");
	  $tags = empty($tags) ? '' : ", FileTags='$tags'";
	  if ($meth === 'ship') {
		$it_str = $ship_type;
		switch ($ship_type) {
		  case 'free': $it_str = 'coin:0'; break;
		  case 'coin': $it_str .= ":$ship_data"; break;
		  case 'fiat': $it_str .= ":$ship_data"; break;
		  case 'weight': $it_str .= ":$ship_data"; break;
		}
	  } else {
	    $it_str = $type;
	  }
	  $new_id = edit_file(safe_sql_str($_GET['fid']), "FileMethod='$meth', FileType='$it_str', FileStock=$stock, ".
	  "FileName='$name', FileDesc='$desc', FileCat='$cats', FilePrice=$price, FileCode='$code'$tags");
      if ($new_id > 0) {
        $msg = "<p class='happy_txt'><b>Item was successfully updated!</b></p>";
      } else {
        $msg = "<p class='error_txt'>There was an unexpected error!</p>\n";  
      }
	}
  }
} else {
  $msg = "<p class='error_txt'>File ID was not specified!</p>\n";
  $continue = 'no';
}

require_once(dirname(__FILE__).'/tinymce.inc.php');
?>

<script language="JavaScript">
var gshipping = '<?php echo $global_shipping; ?>';
var fiat_cc = '<?php echo $curr_code; ?>';

function update_phys(sel_val) {
  switch(sel_val) {
  case 'coin':
    $('#item_type').html('Shipping: <i data-toggle="tooltip" title="Shipping cost in BTC" class="icon-question-sign"></i>');
	$('#ship_data').removeAttr('readonly');
   break;
  case 'global':
    $('#item_type').html('Shipping:');
	$('#ship_data').val(gshipping);
	$('#ship_data').attr('readonly', 'readonly');
   break;
  case 'free':
    $('#item_type').html('Shipping:');
	$('#ship_data').val('0.0');
	$('#ship_data').attr('readonly', 'readonly');
   break;
  case 'weight':
    $('#item_type').html('Weight: <i data-toggle="tooltip" title="Weight of item in prefered unit" class="icon-question-sign"></i>');
	$('#ship_data').removeAttr('readonly');
   break;
  default:
    $('#item_type').html('Shipping: <i data-toggle="tooltip" title="Shipping cost in '+fiat_cc+'" class="icon-question-sign"></i>');
	$('#ship_data').removeAttr('readonly');
  }
}

$(document).ready(function(){
  $('#ship_type').on('change', function() {
    update_phys($(this).val());
  });
});
</script>

<p><b>Update Item</b></p>

<?php
if (!empty($msg)) { echo $msg; } 

if (empty($continue)) {
  if (isset($_POST['method'])) {
	$file['FileMethod'] = $_POST['method'];
  }
  $ship_arr = ($file['FileMethod'] == 'ship') ? explode(':', $file['FileType']) : array('fiat');
  $ship_type = empty($_POST['ship_type']) ? $ship_arr[0] : $_POST['ship_type'];
  $ship_data = empty($_POST['ship_data']) ? ((count($ship_arr) > 1) ? $ship_arr[1] : '') : $_POST['ship_data'];
  if ($file['FileMethod'] == 'download') {
	$type_str = 'Type: <i data-toggle="tooltip" title="File extension (e.g. mp3)" class="icon-question-sign"></i>';
	$stock_str = 'Size: <i data-toggle="tooltip" title="File size in megabytes (MB)" class="icon-question-sign"></i>';
	$meth_cur = 'Instant Download';
	$meth_val = 'download';
  } elseif ($file['FileMethod'] == 'keys') {
	$type_str = 'File: <i data-toggle="tooltip" title="ID of the file linked to this key" class="icon-question-sign"></i>';
	$stock_str = 'Life: <i data-toggle="tooltip" title="Days before key will expire" class="icon-question-sign"></i>';
	$meth_cur = 'File Key';
	$meth_val = 'keys';
  } elseif ($file['FileMethod'] == 'ship') {
    $ship_str = ($ship_type == 'weight') ? 'Weight: ' : 'Shipping: ';
	$type_str = $ship_str.'<i data-toggle="tooltip" title="Weight of item in preferred unit" class="icon-question-sign"></i>';
	$stock_str = 'Stock:';
	$meth_cur = 'Physical Item';
	$meth_val = 'ship';
  } elseif ($file['FileMethod'] == 'email') {
	$type_str = 'Type: <i data-toggle="tooltip" title="The type of item (can be anything)" class="icon-question-sign"></i>';
	$stock_str = 'Stock:';
	$meth_cur = 'Manual Email';
	$meth_alt = 'Code List';
	$malt_val = 'codes';
  } else {
	$type_str = 'Type: <i data-toggle="tooltip" title="The type of item (can be anything)" class="icon-question-sign"></i>';
	$stock_str = 'Stock:';
	$meth_cur = 'Code List';
	$meth_alt = 'Manual Email';
	$malt_val = 'email';
  }
?>
<form class="form-inline" action="" method="post" name="newdown_form" target="_self">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <table cellpadding="5" cellspacing="0" border="0" style="width:400px;"><tr>
  <tr><td align="left">Method:</td><td align="right">
	<select name="method" id="method" <?php if (!isset($meth_alt)) { echo "disabled='disabled'"; } ?>>
	  <option value="<?php echo $file['FileMethod']; ?>" selected='selected'><?php echo $meth_cur; ?></option>
	  <?php if (isset($meth_alt)) { ?>
	  <option value="<?php echo $malt_val; ?>"><?php echo $meth_alt; ?></option>
	</select>
	<?php } else { ?>
	</select>
	<input name="method" type="hidden" value="<?php echo $meth_val; ?>" />
	<?php } ?>
  </td></tr>
  <tr><td align="left">Name:</td><td align="right">
  <input name="name" type="text" maxlength="50" value="<?php if (!empty($_POST['name'])) { 
  echo $_POST['name']; } else { echo $file['FileName']; } ?>" /></td></tr>
  <tr><td align="left">Price:</td><td align="right">
	<input name="price" type="text" maxlength="10" style="width:132px;" value="<?php 
	if (!empty($_POST['price'])) { echo $_POST['price']; } else { echo abs($file['FilePrice']); } ?>" />
	<select name="currency" id="currency" style="width:70px;">
	  <?php 
	  if (!empty($_POST['currency'])) {
		if ($_POST['currency'] == 'BTC') {
		  $opt2_sel = "selected='selected'";
		  $opt1_sel = '';
		} else {
		  $opt1_sel = "selected='selected'";
		  $opt2_sel = '';
		}
	  } else {
		if ($file['FilePrice'] > 0) {
		  $opt1_sel = "selected='selected'";
		  $opt2_sel = '';
		} else {
		  $opt2_sel = "selected='selected'";
		  $opt1_sel = '';
		}
	  }
	  ?>
	  <option value="<?php safe_echo($curr_code); ?>" <?php 
	  echo $opt1_sel; ?>><?php safe_echo($curr_code); ?></option>
	  <option value="BTC" <?php echo $opt2_sel; ?>>BTC</option>
	</select>
  </td></tr>
  <tr><td align="left" id="item_type"><?php echo $type_str; ?></td><td align="right">
	<input name="type" type="text" maxlength="50" value="<?php
	if (!empty($_POST['type'])) { echo $_POST['type']; } else { echo $file['FileType']; }
	?>" <?php if ($file['FileMethod'] == 'ship') { echo 'style="display:none"'; } ?> />
	<div id="phys_opts" style="<?php if ($file['FileMethod'] != 'ship') { echo 'display:none;'; } ?>width:100%;">
	  <input id="ship_data" name="ship_data" type="text" maxlength="10" style="width:132px;" <?php
	  $sd_extra = '';
	  switch ($ship_type) {
		case 'global': $ship_data = $global_shipping; $sd_extra = 'readonly'; break;
		case 'free': $ship_data = '0.0'; $sd_extra = 'readonly'; break;
		case 'coin': if ($ship_data == 0) { $ship_type = 'free'; $sd_extra = 'readonly'; } break;
	  }
	  echo "value='$ship_data' $sd_extra";
	  ?> />
	  <select name="ship_type" id="ship_type" style="width:70px;">
		<option value="fiat" <?php if ($ship_type == 'fiat') { echo 'selected="selected"'; } ?>><?php safe_echo($curr_code); ?></option>
		<option value="coin" <?php if ($ship_type == 'coin') { echo 'selected="selected"'; } ?>>BTC</option>
		<option value="global" <?php if ($ship_type == 'global') { echo 'selected="selected"'; } ?>>Global Rate</option>
		<option value="free" <?php if ($ship_type == 'free') { echo 'selected="selected"'; } ?>>Free Shipping</option>
		<option value="weight" <?php if ($ship_type == 'weight') { echo 'selected="selected"'; } ?>>Weight Based</option>
	  </select>
	</div>
  </td></tr>
  <tr><td align="left"><?php echo $stock_str; ?></td><td align="right">
	<input name="stock" type="text" maxlength="10" value="<?php 
	if (!empty($_POST['stock'])) { echo $_POST['stock']; } else { echo $file['FileStock']; } ?>" />
  </td></tr>
  <tr><td align="left">Tags: <i data-toggle="tooltip" title="Comma separated list of tags/keywords" class="icon-question-sign"></i></td><td align="right">
	<input name="tags" id="tags" type="text" maxlength="250" value="<?php 
	if (!empty($_POST['tags'])) { echo $_POST['tags']; } else { safe_echo($file['FileTags']); } ?>" />
  </td></tr>
  <tr <?php if ($file['FileMethod'] !== 'download') { echo 'style="display:none"'; } 
  ?>><td align="left">Code: <i data-toggle="tooltip" title="Code used for product file" class="icon-question-sign"></i></td><td align="right">
	<input name="code" type="text" maxlength="50" value="<?php 
	if (!empty($_POST['code'])) { echo $_POST['code']; } else { echo $file['FileCode']; } ?>" />
  </td></tr>
  <tr><td align="left">Categories:</td><td align="right">
	<select name="cats[]" size="5" multiple>
	  <?php 
	  $sel_cats = empty($_POST['cats']) ? explode(',', $file['FileCat']) : $_POST['cats'];
	  $cats = get_cats();
	  if (!empty($cats) && ($cats != 'N/A')) {
		while ($cat = mysqli_fetch_assoc($cats)) {
		  $sel_str = '';
		  if (in_array($cat['CatID'], $sel_cats)) {
			$sel_str = "selected='selected'";
		  }
		  echo "<option value='".$cat['CatID'].
		  "' $sel_str>".safe_str($cat['Name'])."</option>";
		}
	  }
	  ?>
	</select>
  </td></tr>
  <tr><td colspan="2">
	<div style="width:100%;margin-bottom:5px;">
	  <div class='float_right'>
		<button class='btn btn-mini' type='button' onClick='toggle_editor();'>Toggle Graphical Editor</button>
	  </div>
	  <div class='float_left'>
		Description:
	  </div>
	  <br clear="all" />
	</div>
	<div style="width:400px;">
	  <textarea name="desc" id="page_data" maxlength="10000" style="width:390px;height:250px;"><?php if (!empty($_POST['desc'])) { echo $_POST['desc']; } else { echo $file['FileDesc']; } ?></textarea>
	</div>
  </td></tr>
  <tr><td colspan="2">
	<a class="btn" href="admin.php?page=items&action=edit&fid=<?php echo $_GET['fid']; ?>">Go Back</a> 
	<button type="submit" class="btn">Submit</button>
  </td></tr>
  </table>
</form>

<?php } ?>
