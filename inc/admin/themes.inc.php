<?php admin_valid();

  echo "<h1>Themes</h1>\n";
 
  if (!empty($_GET['action'])) {
  
    if ($_GET['action'] === 'pages') {
      require_once('inc/admin/pages.inc.php');
    } elseif ($_GET['action'] === 'blocks') {
      require_once('inc/admin/blocks.inc.php');
    } elseif ($_GET['action'] === 'change') {
	
	  if (!empty($_GET['t'])) {
	    if ($_SESSION['csrf_token'] !== $_GET['toke']) {
		  echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
	    } elseif (update_config('main', array('template'=>$_GET['t']))) {
		  echo "<p class='happy_txt'>config file successfully updated</p>";
		  echo "<p>refreshing page to apply changes ...</p>";
		  redirect('admin.php?page=themes&action=change&r='.rand_str());
		} else {
		  echo "<p class='error_txt'>problem changing template</p>";
		}
	  }
	
      echo '<p><b>Select a theme:</b></p><p>';
      $theme_dirs = list_folders('./inc/themes/');
	  
      foreach($theme_dirs as $key => $value) {
	    $theme_name = safe_str($value);
	    if ($template != $value) {
          echo ($key+1).") <a href='admin.php?page=themes&amp;action=change&amp;".
		  "t=$theme_name&amp;toke=".$_SESSION['csrf_token']."'>$theme_name</a><br />";
		} else {
		  echo ($key+1).") $theme_name (active)<br />";
		}
      }
	  
	  echo "</p><p><a class='btn' href='admin.php?page=themes'>Go Back</a></p>";
	  
	  echo "<p><b>NOTE:</b> If you wish to create a custom template for BitShop just create a new folder in the /inc/themes/ directory and the name of that new folder will appear in the above list of themes so that you can choose it. Copy the contents of the default theme folder into your new folder as a starting point and modify them to create your new layout. The css files for all the templates should still be kept in the /css/ folder. Simply edit the /inc/themes/yourtheme/meta.inc.php file and include any css file your theme may need.</p>

	  <p>Keep in mind that you don't need to create a new theme to use a custom bootstrap stylesheet from a website like <a href='http://bootswatch.com' target='_blank'>bootswatch</a>. You could also just replace the existing bootstrap css file without needing to create a new theme in this menu. Using a custom theme will give you control over the actual layout of the pages and the HTML and PHP code behind them. Using a custom bootstrap stylesheet will only change the styling of page elements (eg font sizes and colors).</p>";
	  
	}
  } else {
?>

<p><b>Select an option:</b></p>
<p>
  <a href="admin.php?page=themes&action=change" title="Choose Shop Theme">CHANGE THEME</a><br />
  <a href="admin.php?page=themes&action=blocks" title="Edit Shop Blocks">EDIT BLOCKS</a><br />
  <a href="admin.php?page=themes&action=pages" title="Edit Shop Pages">EDIT PAGES</a><br />
  <a href="admin.php?page=home" title="Main Menu">BACK</a>
</p>

<?php } ?>