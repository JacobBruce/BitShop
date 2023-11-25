<?php admin_valid();
require_once(dirname(__FILE__).'/tinymce.inc.php');

if (empty($_POST['page_data'])) {

  if (isset($_GET['newpage'])) {
  
    if (empty($_POST['page_name'])) {
      echo "<p><b>Input the page name:</b></p>";
      echo "<form class='form-inline' name='page_form' method='post' action=''><div>";
	  echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'" />';
      echo "<input type='text' value='' name='page_name' id='page_name' maxlength='30' /> ";
      echo "<button class='btn' type='submit'>Submit</button></div></form>";
      echo "<p><a class='btn' href='admin.php?page=themes&amp;action=pages'>Go Back</a></p>";
	} else {
	  if (file_put_contents('inc/pages/'.$_POST['page_name'].'.inc.php', "<h1>New Page</h1>")) {
	    echo "<p class='happy_txt'>File was successfully created!</p>";
	  } else {
	    echo "<p class='error_txt'>The file could not be created!</p>";
	  } 
	  echo "<p><a class='btn' href='admin.php?page=themes&amp;action=pages'>Go Back</a></p>";
	}
	
  } elseif (empty($_GET['filename'])) {

    echo "<p><b>Edit a page:</b></p>";
	
    if (!empty($_GET['delete'])) {
	  if ($_SESSION['csrf_token'] !== $_GET['toke']) {
	    echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
      } elseif (@unlink('inc/pages/'.$_GET['delete'])) {
	    echo "<p class='happy_txt'>File was successfully deleted!</p>";
	  } else {
	    echo "<p class='error_txt'>The file could not be deleted!</p>";
	  }
    }
	
	echo '<p>';
  
	$page_files = list_binaries('inc/pages/');

    if (!empty($page_files)) {
      foreach ($page_files as $key => $file) {

        if ($file != "index.html" && $file != "cgi-bin" && $file != ".htaccess" && $file != "error_log" && $file != "_vti_bin" && $file != "_private" && $file != "_vti_cnf" && $file != "_vti_pvt" && $file != "_vti_log" && $file != "_vti_txt") {

		  $page_url = "admin.php?page=themes&amp;action=pages";
          echo "<a href='$page_url&amp;filename=$file'>$file</a> ".
		  "(<a href='#' onClick=\"delete_item('$file')\">delete</a>)<br />";

        }
      }
	  
	  echo '</p>';
?>

<p><a class='btn' href='admin.php?page=themes'>Go Back</a>
<a class='btn' href='admin.php?page=themes&amp;action=pages&amp;newpage'>New Page</a></p>

<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

function delete_item(file) {
	if (confirm('Are you really sure you want to delete this page?')) {
		redirect('admin.php?page=themes&action=pages&delete='+file+'&toke='+csrf_token);
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
        <p>Editing <?php safe_echo($_GET['filename']); ?>:</p>
	  </div>
	</div>

    <form name='page_form' method='post' action=''>
	  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
	  <div style="width:560px;">
        <textarea name='page_data' id='page_data' class='page_data'><?php safe_echo(file_get_contents('inc/pages/'.$_GET['filename'])); ?></textarea>
        <br /><a class='btn' href='admin.php?page=themes&amp;action=pages'>Go Back</a> 
		<button type='submit' id='sub_btn' class='btn'>Update</button>
	  </div>
	</form>
	<p><b>WARNING:</b> the graphical editor may break pages containing PHP code.</p>
	<p><b>NOTE:</b> the content of pages will not change between themes.<p>
<?php
  }
} else {
  if (!empty($_POST['page_data'])) {
    if (file_put_contents('inc/pages/'.$_GET['filename'], $_POST['page_data'])) {
	  echo "<p class='happy_txt'>Update was successful!</p>";
	} else {
      echo "<p class='error_txt'>Update failed!</p>";
	}
  }
  echo "<p><a class='btn' href='admin.php?page=themes&amp;action=pages'>Go Back</a></p>";
}
?>