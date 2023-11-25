<div id="cart_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3><?php echo LANG('EDIT_CART'); ?></h3>
  </div>
  <div class="modal-body">
    <form name="modal_form" id="modal_form" method="get" action="index.php">
	  <input type="hidden" name="page" value="cart" />
	  <input type="hidden" name="add" id="mdl_pid" value="" />
	  <p><b><?php echo LANG('ITEM'); ?>:</b> <span id="mdl_pnm"></span></p>
	  <div class="form-inline">
	    <span><b><?php echo LANG('QUANTITY'); ?>:</b> </span>
	    <input type="text" name="qnty" id="mdl_qnt" value="" class="input-small" maxlength="5" />
	  </div>
    </form>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button onclick="submit_modal();" class="btn btn-primary">Update</button>
  </div>
</div>

<?php
$shs_total = '0';
$sub_total = '0';
$sum_total = '0';
$row_total = '0';

function apply_discount($vouch, $discount) {
  global $shs_total;
  global $sub_total;
  global $sum_total;
  global $row_total;
  switch ($vouch['targ']) {
  case 0:
	$sub_total = bcsub($sub_total, $discount);
	$sum_total = bcsub($sum_total, $discount);
	$row_total = $discount;
	break;
  case 1:
	$shs_total = bcsub($shs_total, $discount);
	$sum_total = bcsub($sum_total, $discount);
	$row_total = $discount;
	break;
  }
}

if (empty($_SESSION['cart'])) {
  echo '<h1>'.LANG('CART_TITLE').'</h1>';
  echo "<p>".LANG('CART_IS_EMPTY')."</p>\n".
  "<p>".LANG('BROWSE_OUR_PRODS').
  ":</p><ul><li><a href='./?page=search'>".
  LANG('SEARCH_TITLE')."</a></li>".
  "\n<li><a href='./?page=cats'>".
  LANG('CATS_TITLE')."</a></li></ul>";
} else {
  
  if (!isset($_SESSION['vouchers'])) {
    $_SESSION['vouchers'] = array();
  } else {
    if (!validate_vouchers()) {
	  $errors['cart'] = LANG('INVALID_VOUCHER');
	}
  }
?>

<?php if (!empty($errors['cart'])) { ?>
<div class="alert alert-block alert-error">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <?php echo $errors['cart']; ?>
</div>
<?php } ?>

<p class="float_right"><a href="./?page=cart&amp;empty"><?php echo LANG('EMPTY_CART'); ?></a></p>
<h1><?php echo LANG('CART_TITLE'); ?></h1>

<table class='table table-striped'>
<tr><th><?php echo LANG('ITEM'); ?></th>
<th><?php echo LANG('PRICE'); ?></th>
<th><?php echo LANG('SHIPPING'); ?></th>
<th><?php echo LANG('TOTAL'); ?></th></tr>
  
<?php
  $ship_costs = array();
  
  foreach ($_SESSION['cart'] as $key => $item) {
  
    $price_btc = get_btc_price($item['price'], $exch_orig);
	
	$item_id = (int)$item['id'];
	$item_name = safe_str($item['name']);
	$shipp_btc = ($item['ship_curr'] == 'BTC') ? 
		$item['ship_cost'] : bcdiv($item['ship_cost'], $exch_orig);
	$ship_costs[$item_id] = $shipp_btc;
	
    $qnt_total = bcmul($price_btc, $item['quant']);
    $shr_total = bcmul($shipp_btc, $item['quant']);	
	$row_total = bcadd($shr_total, $qnt_total);
	
	$shs_total = bcadd($shs_total, $shr_total);
	$sub_total = bcadd($sub_total, $qnt_total);
	$sum_total = bcadd($sum_total, $row_total);
	
	$shr_btc = bitsci::btc_num_format($shr_total);
	$qnt_btc = bitsci::btc_num_format($qnt_total);
	$row_btc = bitsci::btc_num_format($row_total);
	
	if ($item['type'] === 'download') {
	  $edit_icon = '';
	} else {
	  $edit_icon = " <a href='#' onclick='show_modal(\"$item_name\", ".
	  "$key, ".$item['quant'].");' title='".strtolower(LANG('EDIT_CART')).
	  "'><i class='icon-pencil'></i></a>";
	}
	
    echo "<tr><td width='35%'>".$item['quant']." &#215; <a href='".
	"./?page=item&amp;id=$item_id'>$item_name</a> $edit_icon</td>\n".
	"<td>$qnt_btc BTC</td><td>$shr_btc BTC</td><td>$row_btc BTC <a ".
	"href='./?page=cart&amp;remove=$key' title='".LANG('REM_FROM_CART').
	"'><i class='icon-remove pull-right'></i></a></td></tr>";
  }
  
  foreach ($_SESSION['vouchers'] as $k => $vouch) {
 
	$price_disc = 0;
	$ship_disc = 0;
	
	if ($vouch['item_id'] > 0) {
	  $item = item_from_cart($vouch['item_id']);
	  $price_btc = get_btc_price($item['price'], $exch_orig);
	  $shipp_btc = $ship_costs[$vouch['item_id']];
	}
	
	if ($vouch['value'] > 0) {
	  $per_val = bcdiv($vouch['value'], '100');
	  if ($vouch['targ'] == 0) {
	    $ship_disc = '0.00000000';
		if ($vouch['item_id'] == 0) {
		  $price_disc = bcmul($per_val, $sub_total);
		} else {  
	      $price_disc = bcmul($per_val, $price_btc);
		}
	    apply_discount($vouch, $price_disc);
	  } else {
		$price_disc = '0.00000000';
		if ($vouch['item_id'] == 0) {
		  $ship_disc = bcmul($per_val, $shs_total);
		} else {
	      $ship_disc = bcmul($per_val, $shipp_btc);
		}
		apply_discount($vouch, $ship_disc);
	  }
	} else {
	  $fiat_val = ltrim($vouch['value'], '-');
	  $btc_val = get_btc_price($fiat_val, $exch_orig);
	  if ($vouch['targ'] == 0) {
	    $ship_disc = '0.00000000';
		if (isset($price_btc) && $btc_val > $price_btc) {
		  $price_disc = $price_btc;
		} else {
		  $price_disc = $btc_val;      
		}
		apply_discount($vouch, $price_disc);
	  } else {
		$price_disc = '0.00000000';
		if (isset($shipp_btc) && $btc_val > $shipp_btc) {
		  $ship_disc = $shipp_btc;
		} else {
		  $ship_disc = $btc_val;
		}
	    apply_discount($vouch, $ship_disc);
	  }
	}

	$vouch_type = ($vouch['type'] == 1) ? LANG('VOUCHER') : LANG('COUPON');
	$vouch_txt = "$vouch_type: ".safe_str($vouch['name']);

    echo "<tr><td>$vouch_txt</td>\n<td>".
	"-$price_disc BTC</td><td>-$ship_disc BTC</td><td>-$row_total BTC ".
	"<a href='./?page=cart&amp;remvcc=".$vouch['id']."' title='".
	LANG('REM_FROM_CART')."'><i class='icon-remove pull-right'></i></a></td></tr>";
  }
	
  if (bccomp($sub_total, '0') == -1 || bccomp($sum_total, '0') == -1) {
  
    $_SESSION['valid_order'] = false;
	echo '</table><p class="error_txt">'.LANG('PROB_CALC_TOTAL').'</p>';
	
  } else {
  
	$_SESSION['valid_order'] = true;
	$_SESSION['shipping'] = $shs_total;
	$_SESSION['sub_total'] = $sub_total;
	$_SESSION['sum_total'] = $sum_total;
	$_SESSION['exch_rate'] = $exch_orig;
	$_SESSION['sum_fiat'] = bitsci::btc_num_format(bcmul($sum_total, $exch_orig), 2);
	$_SESSION['sum_cfiat'] = bitsci::btc_num_format(bcmul($sum_total, $exch_rate), 2);
	
	$shs_fiat = bitsci::btc_num_format(bcmul($shs_total, $exch_rate), 2);
	$sub_fiat = bitsci::btc_num_format(bcmul($sub_total, $exch_rate), 2);
	$sum_fiat = bitsci::btc_num_format(bcmul($sum_total, $exch_rate), 2);

	echo "<tr class='cart_lrow'><td></td>
	<td>$sub_total&nbsp;BTC<br />= 
	$sub_fiat&nbsp;$curr_code</td>
	<td>$shs_total&nbsp;BTC<br />= 
	$shs_fiat&nbsp;$curr_code</td>
	<td><b>$sum_total&nbsp;BTC<br />= 
	$sum_fiat&nbsp;$curr_code</b></td>
	</tr></table>";
?>

<a href="./?page=buy"><button class="btn btn-primary float_right"><?php echo LANG('PROCEED_CHECKOUT'); ?></button></a>

<form name="vouch_form" method="post" action="">
  <div class="input-append">
    <label><?php echo LANG('OPTIONAL_VOUCHER'); ?>:</label>
    <input type="text" name="voucher" value="" class="input-medium" maxlength="255" />
    <button class="btn" id="vouch_btn"><?php echo LANG('APPLY'); ?></button>
  </div>
</form>

<?php } ?>
<script language="JavaScript">
function show_modal(name, id, quant) {
	$('#mdl_pnm').html(name);
	$('#mdl_pid').val(id);
	$('#mdl_qnt').val(quant);
	$('#cart_modal').modal('show');
}

function submit_modal() {
	$('#modal_form').submit();
}
</script>
<?php } ?>