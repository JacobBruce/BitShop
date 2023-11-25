<?php admin_valid();
require_once(dirname(__FILE__).'/tinymce.inc.php');

if (empty($_GET['t'])) {

  echo '<p><b>Select a theme to edit:</b></p><p>';
  $theme_dirs = list_folders('./inc/themes/');

  foreach($theme_dirs as $key => $value) {
    $theme_name = urlencode($value);
    echo ($key+1).") <a href='admin.php?page=themes&amp;action=blocks&amp;t=$theme_name'>$theme_name</a><br />";
  }
  echo '</p>';

} else {
  $theme = $_GET['t'];
}

if (!isset($theme)) {

  echo "<p><a class='btn' href='admin.php?page=themes'>Go Back</a></p>";
  
} elseif (empty($_POST['page_data'])) {
  
  if (isset($_GET['newblock'])) {
  
    if (empty($_POST['block_name'])) {
      echo "<p><b>Input the block name:</b></p>";
      echo "<form class='form-inline' name='page_form' method='post' action=''>";
	  echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'" />';
      echo "<div><input type='text' value='' name='block_name' id='block_name' maxlength='30' /> ";
      echo "<button class='btn' type='submit'>Submit</button></div></form>";
      echo "<p><a class='btn' href='admin.php?page=themes&amp;action=blocks&amp;t=$theme'>Go Back</a></p>";
	} else {
	  if (file_put_contents("inc/themes/$theme/".$_POST['block_name'].'.inc.php', "<h1>New Block</h1>")) {
	    echo "<p class='happy_txt'>Block was successfully created!</p>";
	  } else {
	    echo "<p class='error_txt'>The block could not be created!</p>";
	  }
	  
	  echo "<p><a class='btn' href='admin.php?page=themes&amp;action=blocks&amp;t=$theme'>Go Back</a></p>";
	}
	
  } elseif (empty($_GET['blockname'])) {

    echo "<p><b>Edit a block:</b></p>";
	
    if (!empty($_GET['delete'])) {
	  if ($_SESSION['csrf_token'] !== $_GET['toke']) {
	    echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
      } elseif (@unlink("inc/themes/$theme/".$_GET['delete'])) {
	    echo "<p class='happy_txt'>Block was successfully deleted!</p>";
	  } else {
	    echo "<p class='error_txt'>The block could not be deleted!</p>";
	  }
    }
	
	echo '<p>';

	$theme_files = list_binaries("inc/themes/$theme/");
  
    if (!empty($theme_files)) {
	  foreach ($theme_files as $key => $file) {

        if ($file != "." && $file != ".." && $file != "index.html" && $file != "cgi-bin" && $file != ".htaccess" && $file != "error_log" && $file != "_vti_bin" && $file != "_private" && $file != "_vti_cnf" && $file != "_vti_pvt" && $file != "_vti_log" && $file != "_vti_txt") {

		  $page_url = "admin.php?page=themes&amp;action=blocks";
          echo "<a href='$page_url&amp;t=$theme&amp;blockname=$file'>$file</a> ".
		  "(<a href='#' onClick=\"delete_item('$file', '$theme')\">delete</a>)<br />";

        }
      }
	  
	  echo '</p>';
?>

<p><a class='btn' href='admin.php?page=themes&amp;action=blocks'>Go Back</a>
<a class='btn' href='admin.php?page=themes&amp;action=blocks&amp;t=<?php 
safe_echo($theme); ?>&amp;newblock'>New Block</a></p>
  
<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

function delete_item(file, theme) {
	if (confirm('Are you really sure you want to delete this block?')) {
		redirect('admin.php?page=themes&action=blocks&t='+theme+'&delete='+file+'&toke='+csrf_token);
	}
}
</script>
	  
<?php
    }
  } else {
?>
	<div style='max-width:560px;height:30px;'>
	  <div class='float_right'>
	    <button class='btn btn-mini' type='button' onClick='toggle_editor();'>Toggle Graphical Editor</button>
	  </div>
	  <div class='float_left'>
        <p>Editing <?php safe_echo($_GET['blockname']); ?>:</p>
	  </div>
	</div>

    <form name='page_form' method='post' action=''>
	  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
	  <div style="width:560px;">
        <textarea name='page_data' id='page_data' class='page_data'><?php safe_echo(file_get_contents("inc/themes/$theme/".$_GET['blockname'])); ?></textarea>
        <br /><a class='btn' href='admin.php?page=themes&amp;action=blocks&amp;t=<?php echo $theme; ?>'>Go Back</a> 
		<button type='submit' id='sub_btn' class='btn'>Update</button>
	  </div>
	</form>
	<p><b>WARNING:</b> the graphical editor may break blocks containing PHP code.</p>
<?php
  }
} else {
  if (file_put_contents("inc/themes/$theme/".$_GET['blockname'], $_POST['page_data'])) {
    echo "<p class='happy_txt'>Update was successful!</p>";
  } else {
    echo "<p class='error_txt'>Update failed!</p>";
  }
  echo "<p><a class='btn' href='admin.php?page=themes&amp;action=blocks&amp;t=$theme'>Go Back</a></p>";
}
?>