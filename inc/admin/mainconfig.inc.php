<?php admin_valid(); ?>

  <p><b>Main Configuration</b></p>

  <div class="row-fluid">
    <div class="span6">
	  <label class="setlab" title="The installation path of this script (just / if installed at root).">Install directory:</label>
      <input type="text" name="install_dir" value="<?php echo $install_dir; ?>" />
	  <label class="setlab" title="Message to display when shop is disabled.">Disabled message:</label>
      <input type="text" name="disable_msg" value="<?php echo $disable_msg; ?>" />
	  <label class="setlab" title="Set this to false when you need to disable the shop for any reason (eg maintenance).">Website enabled:</label>
      <select name="site_enabled">
		<option value="true" <?php if ($site_enabled) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$site_enabled) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="This will determine what PHP errors are displayed. Set to 0 when the shop is live.">Error reporting:</label>
      <select name="error_level">
	  <?php
	  foreach ($error_levels as $key => $value) {
		$selected = ($key == $error_level) ? 'selected="selected"' : '';
	    echo "<option value='$key' $selected>$value</option>";
	  }
	  ?>
	  </select>
	  <label class="setlab" title="This will allow you to see detailed information about SQL errors. Set to false when the shop is live.">Debug SQL:</label>
      <select name="debug_sql">
		<option value="true" <?php if ($debug_sql) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$debug_sql) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="This will determine what language the shop will use.">Default Language:</label>
      <select name="locale">
	  <?php
		$lang_files = list_binaries('inc/langs/');
	  
        foreach ($lang_files as $key => $file) {

          if ($file != "index.html" && $file != "error_log") {

		    $file = explode('.', $file);
			$file = $file[0];
			if (isset($langarray["$file"])) {
		      $selected = ($locale == $file) ? 'selected="selected"' : '';
              echo "<option value='$file' $selected>".safe_str($langarray["$file"])."</option>";
			}
          }
        }
	  ?>
      </select>
	  <label class="setlab" title="This will determine what time zone the shop will use.">Time Zone:</label>
      <select name="time_zone">
	  <?php
	  foreach ($timezones as $key => $value) {
		$selected = ($value == $time_zone) ? 'selected="selected"' : '';
	    echo "<option value='$value' $selected>$key</option>";
	  }
	  ?>
	  </select>
	  <label class="setlab" title="Set this to true to display a small list of new products on the home page.">Show new products:</label>
      <select name="new_prods">
		<option value="true" <?php if ($new_prods) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$new_prods) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="Set this to true to display a small list of featured products on the home page.">Show featured:</label>
      <select name="feat_prods">
		<option value="true" <?php if ($feat_prods) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$feat_prods) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="Set this to true to display a list of best selling products in the side column.">Show best selling:</label>
      <select name="best_prods">
		<option value="true" <?php if ($best_prods) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$best_prods) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="Set this to true to display a list of top rated products in the side column.">Show top rated:</label>
      <select name="top_prods">
		<option value="true" <?php if ($top_prods) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$top_prods) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="Set this to true to enable the captcha security image on several different pages.">Show captcha:</label>
      <select name="show_captcha">
		<option value="true" <?php if ($show_captcha) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$show_captcha) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="Set this to true if you want only registered users to leave product reviews.">Allow anon reviews:</label>
      <select name="anon_reviews">
		<option value="true" <?php if ($anon_reviews) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$anon_reviews) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="The database port (usually 3306).">Database port:</label>
      <input type="text" name="db_port" value="<?php echo $db_port; ?>" />
	  <label class="setlab" title="The database server (usually localhost).">Database server:</label>
      <input type="text" name="db_server" value="<?php echo $db_server; ?>" />
	  <label class="setlab" title="The databse name.">Database name:</label>
      <input type="text" name="db_database" value="<?php echo $db_database; ?>" />
	  <label class="setlab" title="The database username.">Database username:</label>
      <input type="text" name="db_username" value="<?php echo $db_username; ?>" />
	  <label class="setlab" title="The database password.">Database password:</label>
      <input type="password" name="db_password" value="******" />  
	</div>
    <div class="span6">
	  <label class="setlab" title="The name of your shop.">Site name:</label>
      <input type="text" name="site_name" value="<?php echo $site_name; ?>" />
	  <label class="setlab" title="Your business slogan.">Site slogan:</label>
      <input type="text" name="site_slogan" value="<?php echo $site_slogan; ?>" />
	  <label class="setlab" title="The admin contact email.">Contact email:</label>
      <input type="text" name="contact_email" value="<?php echo $contact_email; ?>" />
	  <label class="setlab" title="Time before a session will expire (hours).">Session time:</label>
      <input type="text" name="sess_time" value="<?php echo $sess_time; ?>" />
	  <label class="setlab" title="Delete unconfirmed transactions older than this time (hours).">Clean time:</label>
      <input type="text" name="tran_clean_time" value="<?php echo $tran_clean_time; ?>" />
	  <label class="setlab" title="Time before a download link will expire (days).">Link expire time:</label>
      <input type="text" name="link_expire_time" value="<?php echo $link_expire_time; ?>" />
	  <label class="setlab" title="Days to lock file if hit limit exceeded.">File lock time:</label>
      <input type="text" name="file_lock_time" value="<?php echo $file_lock_time; ?>" />
	  <label class="setlab" title="Maximum hits from different IP's within 5 days.">File hit limit:</label>
      <input type="text" name="file_hit_limit" value="<?php echo $file_hit_limit; ?>" />
	  <label class="setlab" title="The maximum number of failed login attempts allowed.">Login fail limit:</label>
      <input type="text" name="login_fail_limit" value="<?php echo $login_fail_limit; ?>" />
	  <label class="setlab" title="Number of minutes to lock account if fail limit exceeded.">Login lock time:</label>
      <input type="text" name="login_lock_time" value="<?php echo $login_lock_time; ?>" />
	  <label class="setlab" title="Number of rounds used for password hashing.">Login hash rounds:</label>
      <input type="text" name="hash_rounds" value="<?php echo $hash_rounds; ?>" />
	  <label class="setlab" title="Require user to be logged in to access client file area.">Login for files:</label>
      <select name="login_for_files">
		<option value="true" <?php if ($login_for_files) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$login_for_files) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="Send emails using SMTP.">SMTP Enabled:</label>
      <select name="smtp_enable">
		<option value="true" <?php if ($smtp_enable) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$smtp_enable) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="Debug SMTP emails.">SMTP Debug:</label>
      <select name="smtp_debug">
		<option value="true" <?php if ($smtp_debug) { echo 'selected="selected"'; } ?>>true</option>
		<option value="false" <?php if (!$smtp_debug) { echo 'selected="selected"'; } ?>>false</option>
      </select>
	  <label class="setlab" title="The SMTP host.">SMTP Host:</label>
      <input type="text" name="smtp_host" value="<?php echo $smtp_host; ?>" />
	  <label class="setlab" title="The SMTP port.">SMTP Port:</label>
      <input type="text" name="smtp_port" value="<?php echo $smtp_port; ?>" />
	  <label class="setlab" title="The SMTP username.">SMTP username:</label>
      <input type="text" name="smtp_user" value="<?php echo $smtp_user; ?>" />
	  <label class="setlab" title="The SMTP password.">SMTP password:</label>
      <input type="password" name="smtp_pass" value="******" />
	</div>
  </div>