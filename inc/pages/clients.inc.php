<?php
$show_form = false;
$client_state = $login_for_files ? login_state() : 'valid';

if (!empty($_GET['key'])) {
  $pkey = strtolower($_GET['key']);
} elseif (!empty($_POST['key'])) {
  $pkey = strtolower($_POST['key']);
}

if (!empty($pkey)) {

  if ($client_state == 'valid') {
    $pkey_data = get_code($pkey);
  
    if (!empty($pkey_data) && ($pkey_data !== 'N/A')) {
      $pkey_data = mysqli_fetch_assoc($pkey_data);
	  $item_data = get_file($pkey_data['ItemID']);
	  
	  if (!$login_for_files || empty($pkey_data['AccountID']) || $pkey_data['AccountID'] == $account_id) {
	
	    if (!empty($item_data) && ($item_data !== 'N/A')) {
	      $item_data = mysqli_fetch_assoc($item_data);
	  
	      if ($item_data['FileMethod'] == 'keys') {
	        $file_data = get_file($item_data['FileType']);
		
	        if (!empty($file_data) && ($file_data !== 'N/A')) {	
	          $file_data = mysqli_fetch_assoc($file_data);
	          $time_diff = get_time_difference($pkey_data['Created'], mysqli_now());
		      $time_left = ($item_data['FileStock'] * 24) - $time_diff['hours'];
		  
		      if ($time_left <= 0) {
 		        $remaining = '0 '.LANG('HOURS');
		      } else {
		        $remaining = ($time_left < 49) ? $time_left.' '.LANG('HOURS') 
			    : round($time_left / 24).' '.LANG('DAYS');
		      }
	          $down_link = $base_url.'get_file.php?key='.urlencode($pkey);
		  
	          echo '<h1>'.$file_data['FileName']."</h1>\n".
			  "<div id='file_desc'>".$file_data['FileDesc'].'</div>'.
		      '<p><b>'.LANG('KEY_EXPIRES').":</b> $remaining</p>".
		      '<p><b>'.LANG('FILE_TYPE').':</b> '.$file_data['FileType'].'</p>'.
		      '<p><b>'.LANG('FILE_SIZE').':</b> '.$file_data['FileStock'].' MB</p>'.
	          '<p><b>'.LANG('DOWNLOAD').'</b>:';
		  
		      if ($time_left > 0) {
		        echo "<br /><a href='$down_link'>".safe_str($down_link)."</a></p>";
			    echo "<br /><div class='alert alert-warning'><b>".
			    LANG('IMPORTANT').':</b> '.LANG('MAY_BE_LOCKED').'</div>';
		      } else {
		        echo ' '.LANG('LINK_EXPIRED').'</p>';
		      }

            } else {
              $error_str = LANG('FILE_NONEXISTENT');
	          $show_form = true;
	        }
          } else {
            $error_str = LANG('PROD_KEY_INVALID');
	        $show_form = true;
	      }
        } else {
          $error_str = LANG('PROD_NONEXISTENT');
	      $show_form = true;
	    }
      } else {
        $error_str = LANG('PROD_KEY_INVALID');
	    $show_form = true;
	  }
    } else {
      $error_str = LANG('PROD_KEY_INVALID');
	  $show_form = true;
    }
  } else {
    $error_str = LANG('MUST_LOGIN');
	$show_form = true;
  }
} else {
  $show_form = true;
}

if ($show_form) {
  if (!empty($error_str)) {
    echo "<div class='alert alert-error'><button type='button' ".
	"class='close' data-dismiss='alert'>&times;</button>$error_str</div>";
  }
?>

<center>
  <h1><?php echo LANG('CLIENT_TITLE'); ?></h1>
  <hr style="width:300px;" />
  <p><?php echo LANG('ENTER_PROD_KEY'); ?>:</p>
  <form class="form-search" name="client_files" method="get" action="index.php">
    <div class="input-append">
	  <input type="hidden" name="page" value="clients" />
      <input type="text" value="" name="key" maxlength="50" class="search-query" />
      <button type="submit" class="btn"><?php echo LANG('SUBMIT'); ?></button>
    </div>
  </form>
</center>

<?php } ?>