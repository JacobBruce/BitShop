<?php admin_valid();

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	if (($_POST['method'] == 'ship' && $_POST['ship_data'] == '') || ($_POST['method'] != 'ship' && $_POST['itype'] == '') || $_POST['stock'] == '' || 
	empty($_POST['cats']) || empty($_POST['name']) || empty($_POST['desc']) || empty($_POST['price']) || empty($_POST['currency'])) {
	  $msg = "<p class='error_txt'>None of the fields can be empty except for tags!</p>";
	} else {
	  $price = ($_POST['currency'] == 'BTC') ? -$_POST['price'] : $_POST['price'];
	  $code = bin2hex(crypt_random_string(16));
	  $cats_str = implode(',', $_POST['cats']);
	  $tags = trim($_POST['tags'], ", ");
	  if ($_POST['method'] === 'ship') {
		$it_str = $_POST['ship_type'];
		switch ($_POST['ship_type']) {
		  case 'free': $it_str = 'coin:0'; break;
		  case 'coin': $it_str .= ':'.$_POST['ship_data']; break;
		  case 'fiat': $it_str .= ':'.$_POST['ship_data']; break;
		  case 'weight': $it_str .= ':'.$_POST['ship_data']; break;
		}
	  } else {
	    $it_str = $_POST['itype'];
	  }
	  $new_id = create_file($it_str, $_POST['stock'], $_POST['name'], 
				$_POST['desc'], $cats_str, $tags, $code, $price, $_POST['method']);
      if ($new_id > 0) {
        $msg = "<p class='happy_txt'>New item successfully created! ".
        "(<a href='admin.php?page=items&action=edit&fid=$new_id'>edit item</a>)</p>";	
		if ($_POST['method'] === 'download') {
          $msg .= "<div class='alert'><button type='button' class='close' data-dismiss='alert'>&times;</button><p><b>UPLOADING:</b> You can upload and attach a file to this product by using the &quot;edit item&quot; link displayed above and then selecting &quot;EDIT FILE&quot;. Or if the size of the file exceeds the upload limit dictated by your web host you can manually upload a file for this product via FTP by renaming your file to: <b>$code</b> (no file extension) and then upload the file into the <i>uploads</i> folder. If you don't rename the file correctly or upload it into the wrong folder, the download will not work when the file is purchased.</p></div>";	
		} elseif ($_POST['method'] === 'codes') {
		  $stock_check = 0;
		  $codes = explode("\n", $_POST['codes']); 
		  foreach ($codes as $key => $value) {
			if (!empty($value)) {
		      insert_code(trim($value), $new_id, 0, 0);
			  $stock_check++;
		    }
		  }
		  if ($stock_check <> $_POST['stock']) {
		    edit_file($new_id, "FileStock = $stock_check");
		  }
		}
      } else {
        $msg = "<p class='error_txt'>There was an unexpected error!</p>\n";  
      }
	}
  }
?>

<script language="JavaScript">
var gshipping = '<?php echo $global_shipping; ?>';
var fiat_cc = '<?php echo $curr_code; ?>';

function update_form(sel_val) {
  
  switch(sel_val) {
  case 'email':
    $('#code_tr').hide();
    $('#item_name').html('Item Name:');
    $('#item_fprice').html('Item Price:');
    $('#item_type').html('Item Type: <i data-toggle="tooltip" title="The type of item (can be anything)" class="icon-question-sign"></i>');
    $('#item_size').html('Item Stock:');
	$('#stock').removeAttr('readonly');
	$('#phys_opts').hide();
	$('#itype').show();
   break;
  case 'codes':
	$('#code_tr').show();
    $('#item_name').html('Code Name:');
    $('#item_fprice').html('Code Price:');
    $('#item_type').html('Code Type: <i data-toggle="tooltip" title="The type of code (can be anything)" class="icon-question-sign"></i>');
	$('#item_size').html('Code Stock:');
	$('#stock').attr('readonly', 'readonly');
    if ($('#codes').val().trim() == '') {
	  $('#stock').val('0');
	} else {
      $('#stock').val(($('#codes').val()).lineCount().toString());
	}
	$('#phys_opts').hide();
	$('#itype').show();
   break;
  case 'keys':
	$('#code_tr').hide();
    $('#item_name').html('Key Name:');
    $('#item_fprice').html('Key Price:');
    $('#item_type').html('Key File: <i data-toggle="tooltip" title="ID of the file linked to this key" class="icon-question-sign"></i>');
    $('#item_size').html('Key Life: <i data-toggle="tooltip" title="Days before key will expire" class="icon-question-sign"></i>');
	$('#stock').removeAttr('readonly');
	$('#phys_opts').hide();
	$('#itype').show();
   break;
   case 'ship':
	$('#code_tr').hide();
    $('#item_name').html('Item Name:');
    $('#item_fprice').html('Item Price:');
    $('#item_type').html('Item Shipping: <i data-toggle="tooltip" title="Shipping cost in '+fiat_cc+'" class="icon-question-sign"></i>');
    $('#item_size').html('Item Stock:');
	$('#stock').removeAttr('readonly');
	$('#phys_opts').show();
	$('#itype').hide();
   break;
  default:
	$('#code_tr').hide();
    $('#item_name').html('File Name:');
    $('#item_fprice').html('File Price:');
    $('#item_type').html('File Type: <i data-toggle="tooltip" title="File extension (e.g. mp3)" class="icon-question-sign"></i>');
    $('#item_size').html('File Size: <i data-toggle="tooltip" title="File size in megabytes (MB)" class="icon-question-sign"></i>');
	$('#stock').removeAttr('readonly');
	$('#phys_opts').hide();
	$('#itype').show();
  }
}

function update_phys(sel_val) {
  switch(sel_val) {
  case 'coin':
    $('#item_type').html('Item Shipping: <i data-toggle="tooltip" title="Shipping cost in BTC" class="icon-question-sign"></i>');
	$('#ship_data').removeAttr('readonly');
   break;
  case 'global':
    $('#item_type').html('Item Shipping:');
	$('#ship_data').val(gshipping);
	$('#ship_data').attr('readonly', 'readonly');
   break;
  case 'free':
    $('#item_type').html('Item Shipping:');
	$('#ship_data').val('0.0');
	$('#ship_data').attr('readonly', 'readonly');
   break;
  case 'weight':
    $('#item_type').html('Item Weight: <i data-toggle="tooltip" title="Weight of item in preferred unit" class="icon-question-sign"></i>');
	$('#ship_data').removeAttr('readonly');
   break;
  default:
    $('#item_type').html('Item Shipping: <i data-toggle="tooltip" title="Shipping cost in '+fiat_cc+'" class="icon-question-sign"></i>');
	$('#ship_data').removeAttr('readonly');
  }
}

$(document).ready(function(){
  $('#code_tr').hide();
  
  $('#codes').keyup(function(){
    if ($('#codes').val().trim() == '') {
	  $('#stock').val('0');
	} else {
      $('#stock').val(($('#codes').val()).lineCount().toString());
	}
  });
  
  $('#method').on('change', function() {
    update_form($(this).val());
  });
  
  $('#ship_type').on('change', function() {
    update_phys($(this).val());
  });
  
  <?php
  if (!empty($_POST['method'])) {
    echo "$('#method').val('".$_POST['method']."').change();";
  }
  ?>
});
</script>
<?php require_once(dirname(__FILE__).'/tinymce.inc.php'); ?>

  <p><b>Add New Item</b></p>

  <?php if (!empty($msg)) { echo $msg; } ?>
  <form class="form-inline" action="" method="post" name="newdown_form" target="_self">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
    <table cellpadding="5" cellspacing="0" border="0" width="400">
      <tr><td width="40%">Sell Method:</td><td align="right">
	    <select name="method" id="method">
		  <option value="download" selected="selected">File Download (unlimited stock)</option>
		  <option value="keys">File Key (unlimited stock)</option>
		  <option value="email">Manual Email (limited stock)</option>
		  <option value="codes">Code List (limited stock)</option>
		  <option value="ship">Physical Item (limited stock)</option>
		</select>
	  </td></tr>
      <tr><td width="20%" id="item_name">File Name:</td><td align="right">
	    <input name="name" type="text" maxlength="50" value="<?php if (!empty($_POST['name'])) { echo $_POST['name']; } ?>" />
	  </td></tr>
      <tr><td id="item_fprice">File Price:</td><td align="right">
		<input name="price" type="text" maxlength="10" style="width:132px;" value="<?php if (!empty($_POST['price'])) { echo $_POST['price']; } ?>" />
		<select name="currency" id="currency" style="width:70px;">
		  <option value="<?php safe_echo($curr_code); ?>" selected="selected"><?php safe_echo($curr_code); ?></option>
		  <option value="BTC">BTC</option>
        </select>
	  </td></tr>
      <tr><td id="item_type">File Type: <i data-toggle="tooltip" title="File extension (e.g. mp3)" class="icon-question-sign"></i></td>
	  <td align="right">
	    <input name="itype" id="itype" type="text" maxlength="50" value="<?php if (!empty($_POST['itype'])) { echo $_POST['itype']; } 
		?>" <?php if (isset($_POST['method']) && $_POST['method'] == 'ship') { echo 'style="display:none"'; } ?> />
		<div id="phys_opts" style="<?php if (!isset($_POST['method']) || $_POST['method'] != 'ship') { echo 'display:none;'; } ?>width:100%;">
		  <input id="ship_data" name="ship_data" type="text" maxlength="10" style="width:132px;" <?php
		  $ship_type = empty($_POST['ship_type']) ? 'fiat' : $_POST['ship_type'];
		  $ship_data = empty($_POST['ship_data']) ? '' : $_POST['ship_data'];
		  $sd_extra = '';
		  switch ($ship_type) {
		    case 'global': $ship_data = $global_shipping; $sd_extra = 'readonly'; break;
			case 'free': $ship_data = '0.0'; $sd_extra = 'readonly'; break;
		  }
		  echo "value='$ship_data' $sd_extra"; ?> />
		  <select name="ship_type" id="ship_type" style="width:70px;">
		    <option value="fiat" <?php if ($ship_type == 'fiat') { echo 'selected="selected"'; } ?>><?php safe_echo($curr_code); ?></option>
		    <option value="coin" <?php if ($ship_type == 'coin') { echo 'selected="selected"'; } ?>>BTC</option>
		    <option value="global" <?php if ($ship_type == 'global') { echo 'selected="selected"'; } ?>>Global Rate</option>
		    <option value="free" <?php if ($ship_type == 'free') { echo 'selected="selected"'; } ?>>Free Shipping</option>
		    <option value="weight" <?php if ($ship_type == 'weight') { echo 'selected="selected"'; } ?>>Weight Based</option>
          </select>
		</div>
	  </td></tr>
      <tr><td id="item_size">File Size: <i data-toggle="tooltip" title="File size in megabytes (MB)" class="icon-question-sign"></i></td>
	  <td align="right">
	    <input name="stock" id="stock" type="text" maxlength="10" value="<?php if (!empty($_POST['stock'])) { echo $_POST['stock']; } ?>" />
	  </td></tr>
      <tr><td>Tags: <i data-toggle="tooltip" title="Comma separated list of tags/keywords" class="icon-question-sign"></i></td>
	  <td align="right">
	    <input name="tags" id="tags" type="text" maxlength="250" value="<?php if (!empty($_POST['tags'])) { echo $_POST['tags']; } ?>" />
	  </td></tr>
      <tr><td id="item_cat">Categories:</td><td align="right">
		<select name="cats[]" id="categories" size="5" multiple><?php
		  $cats = get_cats();
		  if (!empty($cats) && ($cats != 'N/A')) {
		    while ($cat = mysqli_fetch_assoc($cats)) {
		      echo "\n\t\t  <option value='".$cat['CatID']."' ";
			  if (isset($_POST['cats']) && in_array($cat['CatID'], $_POST['cats'])) { 
			    echo "selected='selected'";
			  }
			  echo ">".safe_str($cat['Name'])."</option>";
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
		    Description (supports HTML):
		  </div>
		  <br clear="all" />
		</div>
		<div style="width:400px;">
	      <textarea name="desc" id="page_data" maxlength="10000" style="width:390px;height:250px;"><?php if (!empty($_POST['desc'])) { echo $_POST['desc']; } ?></textarea>
		</div>
      </td></tr>
	  <tr id="code_tr"><td colspan="2">
	    Code/key List (one per line): <br />
		<textarea id="codes" name="codes" maxlength="999999" style="width:390px;height:250px;"><?php if (!empty($_POST['codes'])) { echo $_POST['codes']; } ?></textarea>
	  </td></tr>
	  <tr><td>
	    <a class="btn" href="admin.php?page=items">Go Back</a> 
	    <button class="btn" name="submit_btn" type="submit">Submit</button>
	  </td></tr>
	</table>
	
	<br />
	<div class="accordion" id="accordion2" style="width:400px;margin-left:5px;">
	  <div class="accordion-group">
		<div class="accordion-heading">
		  <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
			<b>Sell Method Information</b>
		  </a>
		</div>
		<div id="collapseOne" class="accordion-body collapse">
		  <div class="accordion-inner">
			<p><u>File Download</u>: this will allow you to sell a file which can be downloaded by customers immediately after their payment is confirmed. This type of product will have unlimited stock because it can be downloaded an infinite number of times. The download link supplied to the buyer upon purchase will expire in 2 days to prevent link sharing.</p>
			
			<p><u>File Key</u>: this will allow you to sell product keys which are generated on demand (unlimited) and allow customers to instantly download the corresponding file from the 'Client Files' page. These keys can also be set to expire (input 0 for no expiry). To use this method you must first create a File Download product and then deactivate it. Then create a File Key product and put the Item ID in the 'Key File' field.</p>
			
			<p><u>Manual Email</u>: this will allow you to sell basically any digital product by manually emailing the item to the buyer after the payment has been confirmed. This type of product will have a limited stock because you may have a limited number of items. This method is slow but probably the safest way to sell digital products.</p>
			
			<p><u>Code List</u>: this will allow you to sell codes which are selected from a custom list of codes. When the customers payment has been confirmed they will receive a code from that list (via automatic email) and then that code is taken out of stock. This type of product will have limited stock because each code in the list is only sold once.</p>
			
			<p><u>Physical Item</u>: this will allow you to sell physical items which you will manually ship to customers. The shipping cost will be calculated based on the item weight. The weight multiplier value can be modified in the SCI settings. This type of product will have limited stock because physical items can only be sold once.</p>
		  </div>
		</div>
	  </div>
	</div>

  </form>