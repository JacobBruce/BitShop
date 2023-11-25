<?php admin_valid(); ?>

  <p><b>SCI Configuration</b></p>

  <div class="row-fluid">
    <div class="span6">
	  <label class="setlab" title="Number of decimal places in crypto payments (default gateway).">Payment precision:</label>
      <input type="text" name="p_precision" value="<?php echo $p_precision; ?>" />
	  <label class="setlab" title="Allow a bit of wiggle room for inexact payments (default gateway).">Payment variance:</label>
      <input type="text" name="p_variance" value="<?php echo $p_variance; ?>" />
	  <label class="setlab" title="The thousands separator used when displaying price values.">Thousands separator:</label>
      <input type="text" name="t_separator" value="<?php echo $t_separator; ?>" />
	  <label class="setlab" title="The decimal separator used when displaying price values.">Decimal separator:</label>
      <input type="text" name="d_separator" value="<?php echo $d_separator; ?>" />
	  <label class="setlab" title="Shipping rate for all products using the Global Rate (fiat value).">Shipping rate:</label>
      <input type="text" name="global_shipping" value="<?php echo $global_shipping; ?>" />
	  <label class="setlab" title="Cost per weight unit for Weight Based shipping (fiat value).">Weight multiplier:</label>
      <input type="text" name="weight_mult" value="<?php echo $weight_mult; ?>" />
	  <label class="setlab" title="Maximum number of vouchers allowed in cart.">Voucher limit:</label>
      <input type="text" name="voucher_limit" value="<?php echo $voucher_limit; ?>" />
	  <label class="setlab" title="Minutes before an order expires if not paid (default gateway).">Order expire time:</label>
      <input type="text" name="order_expire_time" value="<?php echo $order_expire_time; ?>" />
	</div>
	<div class="span6">
	  <label class="setlab" title="The name of your business.">Business name:</label>
      <input type="text" name="seller" value="<?php echo $seller; ?>" />
	  <label class="setlab" title="The currency symbol used for fiat price values.">Fiat symbol:</label>
      <input type="text" name="curr_symbol" value="<?php echo $curr_symbol; ?>" />
	  <label class="setlab" title="The currency code of the fiat currency you wish to use (eg USD, AUD, GBP).">Fiat code:</label>
	  <select name="curr_code">
	  <?php
	  foreach ($market_data as $key => $value) {
	    $sel = ($key == $curr_code) ? ' selected="selected"' : '';
	    echo "<option value='$key'$sel>$key</option>\n";
	  }
	  ?>
	  </select>
	  <label class="setlab" title="Display BTC values using different units (eg mBTC = millibit).">Bitcoin units:</label>
	  <select name="dec_shift">
	  <?php
	  foreach ($unit_symbols as $key => $value) {
	    $sel = ($key == $dec_shift) ? ' selected="selected"' : '';
	    echo "<option value='$key'$sel>".$value."BTC</option>\n";
	  }
	  ?>
	  </select>
	  <label class="setlab" title="Exchange rates will be updated when older than the chosen amount of time.">Ticker update time:</label>
      <select name="price_update">
	  <?php
	  $ptimes = array('1' => '1 minute', '5' => '5 minutes', '10' => '10 minutes', '15' => '15 minutes', '30' => '30 minutes');
	  foreach ($ptimes as $key => $value) {
		$selected = ($key == $price_update) ? 'selected="selected"' : '';
	    echo "<option value='$key' $selected>$value</option>";
	  }
	  ?>
	  </select>
	  <label class="setlab" title="Enable this to have an email sent to the admin whenever an order is placed.">Send admin email:</label>
      <select name="send_email">
		<option value="true" <?php if ($send_email) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$send_email) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="Enable this to log orders to the RSS feed and display the feed on the home page.">Enable RSS feed:</label>
      <select name="rss_feed">
		<option value="true" <?php if ($rss_feed) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$rss_feed) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="The explorer API used to check address balances by the default gateway.">Explorer API:</label>
      <select name="explorer_api">
		<option value="0" <?php if ($explorer_api==0) { echo 'selected="selected"'; } ?>>blockchain.com</option>
		<option value="1" <?php if ($explorer_api==1) { echo 'selected="selected"'; } ?>>sochain.com</option>
      </select>
	</div>
  </div>