<?php admin_valid();

$this_item = get_file(safe_sql_str($_GET['fid']));

if (!empty($this_item) && ($this_item != 'N/A')) {

  $this_item = mysqli_fetch_assoc($this_item);
  $file_code = $this_item['FileCode'];
  $file_name = 'uploads/'.$file_code;
	
  if (!empty($_POST)) {
    if (isset($_POST['delete'])) {
  
      if (file_exists($file_name)) {
	    if (unlink($file_name)) {
	      echo "<p class='happy_txt'>File was successfully deleted!</p>";
	    } else {
	      echo "<p class='happy_txt'>There was a problem deleting the file!</p>";
	    }
	  } else {
	    echo "<p class='happy_txt'>There was a problem locating the file!</p>";
	  }
  
    } elseif (!empty($_FILES['item_file'])) {

      if ($_FILES['item_file']["error"] > 0) { 
        echo "<p class='error_txt'>There was an error uploading the file: ".$_FILES['item_file']["error"].'</p>';
      } else {

	    $name_array = explode(".", $_FILES["item_file"]["name"]);
        $file_ext = end($name_array);

        if (file_exists($file_name)) {
          unlink($file_name);
        }
		
	    if (is_dir('uploads/')) {
          if (move_uploaded_file($_FILES['item_file']['tmp_name'], $file_name)) {
		    if ($this_item['FileType'] != $file_ext) {
			  edit_file(safe_sql_str($_GET['fid']), "FileType = '".safe_sql_str($file_ext)."'");
			  $this_item['FileType'] = $file_ext;
			}
            echo "<p class='happy_txt'>File was successfully uploaded!</p>";
          } else {
            echo "<p class='error_txt'>An unexpected error occurred!</p>";
          } 
	    } else {
          echo "<p class='error_txt'>ERROR: file folder could not be created! Check your PHP settings.</p>";
	    }
      }
    }
  }
} else {
  echo "<p class='error_txt'>Item ID does not exist!</p>";
}
?>

<p><b>Product File</b></p>

<p>Max upload size:
<?php echo str_replace('M', 'MB', ini_get('upload_max_filesize')); ?>
</p>

<?php
if (file_exists($file_name)) {
  echo '<p>File size: ';
  $file_size = filesize($file_name) / 1024 / 1024;
  $file_size = number_format($file_size, 2, '.', '');
  if ($file_size != $this_item['FileStock']) {
    edit_file($_GET['fid'], "FileStock = $file_size");
    echo $file_size.' MB';
  } else {
    echo $this_item['FileStock'].' MB';
  }
  echo '</p>';
?>

<p>File extension:
<?php echo '.'.trim($this_item['FileType'], '.'); ?>
</p>

<?php } ?>

<p>Current file:
<?php
if (file_exists($file_name)) {
  echo $file_name;
} else {
  echo 'none';
}
?>
</p>

<br clear="both" /><br />
<form name="fileup_form" action="" method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <input name="item_file" type="file" />
  <button type="submit" class="btn" name="upload">Upload</button>
  <?php if (file_exists($file_name)) { ?>
  <button type="submit" class="btn" name="delete">Delete</button>
  <?php } ?>
</form>

<p><a href='admin.php?page=items&action=edit&fid=<?php echo $_GET['fid']; ?>' title='Go back'>BACK</a></p>