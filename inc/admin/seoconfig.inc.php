<?php admin_valid(); 
$tcount = count($page_titles);
$tchalf = floor($tcount / 2)
?>

  <p><b>SEO Configuration</b></p>

  <div class="row-fluid">
    <div class="span6">
	
      <label class="setlab" title="Keywords used by search engines to classify your website (separate with comma).">Meta Keywords:</label>
      <input type="text" name="keyw" value="<?php echo $keywords; ?>" />
  
      <?php
	  $it = 2;
      foreach ($page_titles as $key => $value) {
	    if ($key == 'item') { continue; }
        $title = $key;
        $title[0] = strtoupper($key[0]);
        echo "<label class='setlab'>$title Page Title:</label>";
        echo "<input type='text' name='$key' value='$value' />";
		if ($it == $tchalf) { 
		  echo '</div><div class="span6">';
		}
		$it++;
      }
      ?>
  
    </div>
  </div>