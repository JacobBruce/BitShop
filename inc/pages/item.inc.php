<?php
$related_items = similar_files($file['FileName'], $file['FileID'], $file['FileCat'], $file['FileTags'], $file['FilePrice']);
$item_id = (int) safe_sql_str($_GET['id']);

if (!isset($_SESSION["_$item_id"])) {
  $_SESSION["_$item_id"] = array();
}

if (!empty($_GET['vote'])) {
  if (is_numeric($_GET['vote']) && ($_GET['vote'] <= 5) && ($_GET['vote'] >= 1)) {
    if (!isset($_SESSION["_$item_id"]['voted'])) {
      if (apply_vote($item_id, $_GET['vote'])) {
	    $_SESSION["_$item_id"]['voted'] = true;
	    $file['FileVoteSum'] += $_GET['vote'];
	    $file['FileVoteNum']++;
	    $vote_str = "<p class='happy_txt'>".LANG('VOTE_SUCCESSFUL')."</p>"; 
      } else {
	    $vote_str = "<p class='error_txt'>".LANG('VOTE_DB_ERROR')."</p>"; 
	  }
    } else {
      $vote_str = "<p class='error_txt'>".LANG('ALREADY_VOTED')."</p>";
    }
  } else {
    $vote_str = "<p class='error_txt'>".LANG('INVALID_RATING')."</p>";
  }
} else {
  $vote_str = '';
}

if(isset($_POST['review'])) {
  $errors['review'] = '';
  
  if ($anon_reviews === true || login_state() === 'valid') {
	  if (!is_numeric($_POST['rating']) || ($_POST['rating'] > 5) || ($_POST['rating'] < 1)){
		$errors['review'] .= "<p class='error_txt'>".LANG('INVALID_RATING')."</p>";
	  }
	  if (empty($_POST['review']) || strlen($_POST['review']) < 5) {
		$errors['review'] .= "<p class='error_txt'>".LANG('REVIEW_TOO_SHORT')."</p>";
	  } elseif (strlen($_POST['review']) > 1000){
		$errors['review'] .= "<p class='error_txt'>".LANG('REVIEW_TOO_LONG')."</p>";
	  }
	  if (empty($_POST['author']) || strlen($_POST['author']) < 2) {
		$errors['review'] .= "<p class='error_txt'>".LANG('NAME_TOO_SHORT')."</p>";
	  } elseif (strlen($_POST['author']) > 50){
		$errors['review'] .= "<p class='error_txt'>".LANG('NAME_TOO_LONG')."</p>";
	  }
	  if (empty($_SESSION['6_letters_code']) || strcasecmp($_SESSION['6_letters_code'], $_POST['6_letters_code']) != 0) {
		$errors['review'] .= "<p class='error_txt'>".LANG('BAD_SEC_CODE')."</p>";
	  }
  } else {
	$errors['review'] = "<p class='error_txt'>".LANG('INVALID_ACTION')."</p>";
  }

  if (empty($errors['review'])) {
	if (empty($_SESSION["_$item_id"]['review'])) {
	  if (submit_review($item_id, $_POST['rating'], $_POST['author'], $_POST['review'])) {
		$_SESSION["_$item_id"]['review'] = 1;
	    $revpage = true;
	  }
	} else {
      $errors['review'] .= "<p class='error_txt'>".LANG('ALREADY_REVIEWED')."</p>";
	}
  }
}

if (empty($revpage)) {
  if (!empty($item_id) & is_numeric($item_id)) {
    if (!empty($file) && ($file != 'N/A')) {	
	  if ($file['FileActive']) {
	
	    $item_name = $file['FileName'];
	    $item_meth = $file['FileMethod'];
	  
	    if ($file['FileMethod'] === 'download') {
	      $item_stock = 1;
	    } elseif ($file['FileMethod'] === 'keys') {
	      $item_stock = 9999;
	    } else {
	      $item_stock = (int)$file['FileStock'];
	    }
	  
	    echo $breadcrumb;
        echo "<h1>".safe_str($file['FileName'])."</h1>\n";
?>

<div class="row-fluid">
  <div class="span3">
    <?php
    $pic_url = "pics/$item_id/preview";	
	$img_ext = get_img_ext($pic_url);
    $full_src = $pic_url.$img_ext;
		  
	if (empty($img_ext)) {
	  $full_src = 'img/no-image.png';
	}
	?>
	<div style="max-width:200px;">
      <img id='item_image' class='img-rounded' src="<?php 
	  echo $full_src; ?>" alt="<?php echo LANG('LOADING_IMG'); ?>" />
	  <div id="fimg_box">
	    <a id='fimg_link' href="#" onClick="popWindow('<?php 
		echo $full_src; ?>', 500, 500)"><?php echo LANG('FULL_SIZE'); ?></a>
	  </div>
	</div>
  </div>
  <div class="span5">
    <div style="margin-bottom:10px;">
      <?php echo $file['FileDesc']; ?>
	</div>
  </div>
  <div class="span4">
    <div class="float_right">
    <?php
	if (is_numeric($exch_rate) && is_numeric($exch_orig)) {
	  $btc_price = get_btc_price($file['FilePrice'], $exch_orig);
	  $fiat_price = get_fiat_price($file['FilePrice'], $exch_rate, $exch_orig);
	  $fiat_price = bitsci::btc_num_format($fiat_price, 2);
	  $short_price = bitsci::btc_num_format($btc_price, 5, $dec_shift);
	  //$btc_price = bitsci::btc_num_format($btc_price, 8, $dec_shift);
	?>
	<p id='item_price'>
	  <span id='btc_price'>
	    <b><?php safe_echo($short_price.' '.$dec_unit.'BTC'); ?></b>
	  </span>
	  <br />
	  <span class="smaller"><?php echo LANG('OR').' '.$curr_symbol.$fiat_price.' '.$curr_code; ?></span>
	</p>
	<?php
	  if ($file['FileMethod'] === 'download') {
	    echo "<p class='smaller'><b>".LANG('FILE_SIZE')."</b>: ".safe_str($file['FileStock'])." MB</p>";
	    $item_promo = "<span class='label label-success bot_gap'>".LANG('INSTANT_DOWNLOAD')."</span>";
		$enable_buy = true;
	  } elseif ($file['FileMethod'] === 'keys') {
	    echo "<p class='smaller'><b>".LANG('KEY_LIFE')."</b>: ".safe_str($file['FileStock']).' '.LANG('DAYS')."</p>";
	    $item_promo = "<span class='label label-success bot_gap'>".LANG('INSTANT_ACCESS')."</span>";
		$enable_buy = true;
	  } elseif ($file['FileMethod'] === 'email' || $file['FileMethod'] === 'ship') {
	    $item_promo = '';
	    if ($file['FileMethod'] === 'ship') {
		  $ship_arr = explode(':', $file['FileType']);
		  if ($ship_arr[0] == 'coin' && $ship_arr[1] == 0) {
	        $item_promo = "<span class='label label-success bot_gap'>".LANG('FREE_SHIPPING')."</span>";
		  } else {
		    $ship_info = get_ship_info($file, $curr_orig);
		    $shipp_btc = ($ship_info['curr'] == 'BTC') ? 
		      $ship_info['cost'] : get_btc_price($ship_info['cost'], $exch_orig);
			$shipp_btc = bitsci::btc_num_format($shipp_btc, 5, $dec_shift);
		    echo '<span class="smaller"><b>'.LANG('SHIPPING').'</b>: '.$shipp_btc.' '.$dec_unit.'BTC</span>';
		  }
		}
	    if ($file['FileStock'] < 1) {
	      echo "<span class='label label-important bot_gap'>".LANG('OUT_OF_STOCK')."</span>";
		  $enable_buy = false;
	    } else {
	      echo "<p class='smaller'><b>".LANG('STOCK')."</b>: ".safe_str($file['FileStock'])."</p>";
		  $enable_buy = true;
	    }
	  } else {
	    if ($file['FileStock'] < 1) {
	      echo "<span class='label label-important bot_gap'>".LANG('OUT_OF_STOCK')."</span>";
		  $enable_buy = false;
	    } else {
		  $item_promo = "<span class='label label-success bot_gap'>".LANG('INSTANT_EMAIL')."</span>";
	      echo "<p class='smaller'><b>".LANG('STOCK')."</b>: ".safe_str($file['FileStock'])."</p>";
		  $enable_buy = true;
	    }
	  }
	  if ($enable_buy) {
	?>
	<form name="buy_form" method="get">
	  <div class="well" style="padding:10px;">
		<input type="hidden" name="page" value="cart" />
		<input type="hidden" name="add" value="<?php echo $item_id ?>" />
	    <?php
		echo $item_promo;
	    if ($file['FileMethod'] !== 'download') {
	    ?>
	    <div style="margin-bottom:10px;">
		  <b><?php echo LANG('QUANTITY'); ?></b>: 
		  <input type="text" name="qnty" id="quant" value="1" maxlength="5" />
		</div>
		<?php } ?>
	    <button class="btn btn-large" type="submit"><?php echo strtoupper(LANG('ADD_TO_CART')); ?> <i class="icon-shopping-cart"></i></button>
	  </div>
    </form>
    <?php } ?>
	<div style="margin-bottom:10px;">
      <div>
	    <?php
	    if ($file['FileVoteNum'] > 0) {
	      $item_score = get_rating($file);
	    } else {
	      $item_score = '?';
	    }
	    echo "<b>".LANG('RATING').":</b> $item_score/5";
	    ?>
      </div>
	  <div>
	    <?php
		$round_score = round($item_score);
	    for ($i=1;$i<=5;$i++) {
          echo "<a class='star' style='cursor:pointer;' title='".LANG('GIVE_RATING_OF')." $i/5' ".
	      "onclick=\"redirect(Base64.decode('".base64_encode("?page=item&id=$item_id&vote=$i")."'));\">\n";
		  if ($round_score >= $i) {
            echo "<img src='img/star_bright.png' border='0' alt='*' /></a>";
		  } else {
		    echo "<img src='img/star_dull.png' border='0' alt='*' /></a>";
		  }
	    }
        ?>
      </div>
	  <div style='font-size:10px'>
	    <span><?php echo LANG('NUMBER_OF_VOTES'); ?>: <?php echo $file['FileVoteNum']; ?></span>
	    <?php echo $vote_str; ?>
      </div>
	</div>
	<?php
	} else {
	  echo "<p class='error_txt'>".LANG('PROB_CALC_PRICE').' '.LANG('TRY_REFRESHING')."</p>\n";
	}
	?>
	</div>
  </div>
</div>
<div class="row-fluid">
  <div class="span12">
    <ul class="thumbnails">         
      <?php
	  for ($i = 1; $i <= 5; $i++) {
	    $thumb_src = "pics/".$file['FileID']."/thumbs/$i";
	    $img_ext = get_img_ext($thumb_src);
	    if (!empty($img_ext)) {
		  $full_src = $thumb_src.$img_ext;
	      echo "<li><a class='thumbnail' href='#' onClick=\"show_thumb('$full_src');\">".
		  "<img src='$full_src' alt='[".LANG('LOADING_IMG')."]' style='height:90px;' /></a></li> ";
	    }
	  }
	  $review_count = count_prod_reviews($item_id);
	  ?>
	</ul>
	<hr />
	<div class="tabbable">
      <ul class="nav nav-tabs">
        <li class="active"><a href="#tab1" data-toggle="tab"><i class="icon-comment"></i> <?php echo LANG('REVIEWS')." ($review_count)"; ?></a></li>
        <li><a href="#tab2" data-toggle="tab"><i class="icon-th-large"></i> <?php echo LANG('RELATED_PRODS'); ?></a></li>
        <?php
		if (!empty($file['FileTags'])) {
		  echo '<li><a href="#tab3" data-toggle="tab"><i class="icon-tags"></i> '.LANG('TAGS').'</a></li>';
		}
		?>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="tab1">
		  <?php
		    $reviews = get_reviews($item_id);
		    if (!empty($reviews) && ($reviews !== 'N/A')) {
			  echo '<div id="review_con" style="width:auto">';
			  while ($row = mysqli_fetch_assoc($reviews)) {
			    echo '<div class="well no_border"><div class="rev_head"><span class="rev_stars">';
				for ($i=1;$i<6;$i++) {
				  if ($i <= $row['Rating']) {
				    echo "<img src='img/star_bright.png' border='0' alt='*' />";
				  } else {
				    echo "<img src='img/star_dull.png' border='0' alt='*' />";
				  }
				}		
				echo '</span><h5 class="rev_author">'.safe_str($row['Author']).
				'</h5></div><p class="review_txt">'.str_replace("\n", '<br />', 
				safe_str($row['Review'])).'</p><div class="float_right smaller">'.
				safe_str(format_time($row['Created'])).'</div><br clear="all" /></div>';
			  }
			  echo '</div>';
			  if ($review_count > 10) {
				echo '<button id="more_btn" class="btn btn-link" onclick="load_more_reviews()">'.LANG('SHOW_MORE').'</button>';
				echo '&nbsp;<img class="no_display" id="more_loader" src="./sci/img/ajax_loader.gif" height="30" />';
			  }
	        } else {
		  ?>
          <p><?php echo LANG('NO_REVIEWS'); ?></p>
		  <?php } ?><hr />
		  <h4><?php echo LANG('SUBMIT_REVIEW'); ?>:</h4>
		  <?php if (!empty($errors['review'])) { echo $errors['review']; } 
		  if ($anon_reviews === true || login_state() === 'valid') { ?>
          <form name="review_form" action="" method="post">
            <div>
			  <label><?php echo LANG('YOUR_NAME'); ?>:</label>
			  <input name="author" type="text" maxlength="50" required='required' value="<?php 
			  if (!empty($_POST['author'])) { safe_echo($_POST['author']); } ?>" />
			</div>
			<div>
			  <label><?php echo LANG('PRODUCT_RATING'); ?>:</label>
              <select name="rating">
                <option value="1">1 <?php echo LANG('STAR'); ?></option>
                <option value="2">2 <?php echo LANG('STARS'); ?></option>
                <option value="3">3 <?php echo LANG('STARS'); ?></option>
                <option value="4">4 <?php echo LANG('STARS'); ?></option>
                <option value="5">5 <?php echo LANG('STARS'); ?></option>
              </select>
			</div>
			<div>
			  <label><?php echo LANG('PRODUCT_REVIEW'); ?>:</label>
			  <textarea name="review" maxlength="980" required='required'><?php 
			    if (!empty($_POST['review'])) { safe_echo($_POST['review']); } 
			  ?></textarea>
			</div>
			<?php if ($show_captcha) { ?>
			<div>
 			  <img src="inc/captcha_code_file.php?rand=<?php echo rand(); ?>" id="captchaimg" /><br />
  			  <small><?php echo LANG('CANT_READ_IMG'); ?> <a href='javascript: refreshCaptcha();'><?php
			  echo LANG('CLICK_HERE'); ?></a> <?php echo LANG('TO_REFRESH'); ?></small>
 			  <label for='message'><?php echo LANG('REPEAT_SEC_CODE'); ?>:</label>
 			  <input name="6_letters_code" width="200" maxlength="6" type="text" required='required'>
			</div>
		    <?php
		    } else {
			  $_SESSION['6_letters_code'] = 'abc';
			  echo '<input type="hidden" name="6_letters_code" value="abc" />';
		    }
		    ?>
            <button type="submit" class="btn"><?php echo LANG('SUBMIT'); ?></button>
          </form>
		  <?php } else { echo '<p>'.LANG('ACCOUNT_REQUIRED').'</p>'; } ?>
        </div>
        <div class="tab-pane" id="tab2">
		  <?php
		  if (!empty($related_items) && ($related_items !== 'N/A')) {
			while ($row = mysqli_fetch_assoc($related_items)) {
		      echo item_box_html($row);
			}
		  } else {
            echo '<p>'.LANG('NO_RELATED_PRODS').'</p>';
		  }
		  ?>
        </div>
        <?php
		if (!empty($file['FileTags'])) {
		  $tags = explode(',', $file['FileTags']);
		  echo '<div class="tab-pane" id="tab3">';
		  foreach ($tags as $tag_key => $tag_val) {
		    $tag_enc = rawurlencode(trim($tag_val));
		    echo "<a href='./?page=search&amp;tag=$tag_enc'><span ".
			"class='label label-info'>".safe_str($tag_val).'</span></a>&nbsp;';
		  }
		  echo '</div>';
		}
		?>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
<?php echo 'var item_id = '.$item_id.';'; ?>
<?php echo 'var stock = '.$item_stock.';'; ?>
var rstart = 0;
var rpp = 10;

$('.bit_btn').click(function() {
	if ($('#quant').length) {
		var quant = $('#quant').val();
		if (quant < 1) {
			alert('<?php echo LANG('QUANTITY_TOO_LOW'); ?>');
			return false;
		} else if (isNaN(quant) || (stock < quant)) {
			alert('<?php echo LANG('QUANTITY_TOO_HIGH'); ?>');
			return false;
		} else {
			return true;
		}
	} else {
		return true;
	}
});

function show_thumb(thumb_src) {
	$('#item_image').attr("src", thumb_src);
	$('#fimg_link').click(function () {
	  popWindow(thumb_src, 500, 500);
	});
}

function handle_success(response) {
	$('#more_loader').hide();
	if (response == null || response == '') {
		$('#more_btn').hide();
	} else {
		$('#more_btn').toggleClass('disabled', false);
		$('#review_con').append(response);
	}
	if (rstart+rpp >= <?php echo $review_count; ?>) {
		$('#more_btn').hide();
	}
}

function handle_error(response) {
	$('#more_loader').hide();
	$('#more_btn').toggleClass('disabled', false);
    alert(response);
}

function load_more_reviews() {
	rstart += rpp;
	$('#more_btn').toggleClass('disabled', true);
	$('#more_loader').show();
	ajax_get('./inc/jobs/getreviews.inc.php',
	'item_id='+item_id+'&start='+rstart, handle_success, handle_error);
}
</script>

<?php
      } else {
        echo "<p class='error_txt'>".LANG('ITEM_REMOVED')."</p>";
	  }
    } else {
      echo "<p class='error_txt'>".LANG('INVALID_ITEM_ID')."</p>";
    }
  } else {
    echo "<p class='error_txt'>".LANG('INVALID_ITEM_ID')."</p>";
  }
} else {
  echo "<h1>Success!</h1><p>".LANG('REVIEW_SUBMITTED')."</p>".
  "<p><a class='btn' href='?page=item&id=$item_id'>".LANG('GO_BACK')."</a></p>";
}
?>