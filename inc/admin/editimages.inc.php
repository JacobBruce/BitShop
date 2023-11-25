<?php admin_valid();

if (!empty($_POST)) {

  if (empty($_POST['thumb_num'])) {
    $img_opt = 'prev_image';
	$file_src = 'pics/'.$_GET['fid'].'/preview';
	$img_ext = get_img_ext($file_src);
	$full_src = $file_src.$img_ext;
  } else {
    $img_opt = 'thumb_image';
	$file_src = 'pics/'.$_GET['fid'].'/thumbs/'.$_POST['thumb_num'];
	$img_ext = get_img_ext($file_src);
	$full_src = $file_src.$img_ext;
  }

  if (isset($_POST['delete'])) {
  
    if (file_exists($full_src)) {
	  if (unlink($full_src)) {
	    echo "<p class='happy_txt'>Image was successfully deleted!</p>";
	  } else {
	    echo "<p class='happy_txt'>There was a problem deleting the image!</p>";
	  }
	} else {
	  echo "<p class='happy_txt'>There was a problem locating the image!</p>";
	}
  
  } elseif (!empty($_FILES['prev_image']) || !empty($_FILES['thumb_image'])) {

    if ($_FILES[$img_opt]["size"] < 2000000) {
  
      switch ($_FILES[$img_opt]["type"]) {
	    case 'image/jpeg': $img_ext = 'jpg'; break;
	    case 'image/gif': $img_ext = 'gif'; break;
	    case 'image/png': $img_ext = 'png'; break;
	    case 'image/bmp': $img_ext = 'bmp'; break;
	    default: $img_ext = 'invalid'; break;
	  }
	
	  if ($img_ext !== 'invalid') {
        if ($_FILES[$img_opt]["error"] > 0) { 
          echo "<p class='error_txt'>There was an error uploading the file: ".$_FILES[$img_opt]["error"].'</p>';
        } else {

          if ($img_opt === 'prev_image') {
            $file_name = 'pics/'.$_GET['fid']."/preview.$img_ext";
	      } else {
            $file_name = 'pics/'.$_GET['fid'].'/thumbs/'.$_POST['thumb_num'].".$img_ext";
	      }
	
          if (file_exists($file_name)) {
            unlink($file_name);
          }
	  
	      if (!is_dir('pics/'.$_GET['fid'].'/')) {
	        mkdir('pics/'.$_GET['fid'].'/');
	      }
	      if (!is_dir('pics/'.$_GET['fid'].'/thumbs/')) {
	        mkdir('pics/'.$_GET['fid'].'/thumbs/');
	      }
	  
	      if (is_dir('pics/'.$_GET['fid'].'/') && is_dir('pics/'.$_GET['fid'].'/thumbs/')) {
            if (move_uploaded_file($_FILES[$img_opt]['tmp_name'], $file_name)) {
              echo "<p class='happy_txt'>Image was successfully uploaded!</p>";
            } else {
              echo "<p class='error_txt'>An unexpected error occurred!</p>";
            } 
	      } else {
            echo "<p class='error_txt'>ERROR: image folder could not be created! Check your PHP settings.</p>";
	      }
        }
	  } else {
	    echo "<p class='error_txt'>ERROR: image format not supported!</p>";
	  }
    } else {
      echo "<p class='error_txt'>ERROR: the image is too large!</p>";
    }
  }
}
?>

<script language="JavaScript">
function upThumb(tNum) {
  var ts = document.getElementById('thumb_nspan');
  ts.innerHTML = tNum+': ';
  document.thumb_form.thumb_num.value = tNum;
}
</script>

<p><b>Preview image:</b></p>

<?php
$file_src = 'pics/'.$_GET['fid'].'/preview';
$img_ext = get_img_ext($file_src);
$full_src = $file_src.$img_ext;
		 
if (!empty($img_ext)) {
  echo "<img src='$full_src?".microtime()."' alt='Loading ...' width='200' />";
} else {
  echo "<img src='img/no-image.png' alt='Loading ...' width='200' />";
}
?>

<br clear="both" /><br />
<form name="prev_form" enctype="multipart/form-data" action="" method="post">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
  <input name="prev_image" type="file" />
  <button type="submit" class="btn" name="upload">Upload</button>
  <button type="submit" class="btn" name="delete">Delete</button>
</form>

<hr />

<p><b>Thumbnail images:</b></p>

<?php
for ($i = 1; $i <= 5; $i++) {

  $thumb_src = "pics/".$_GET['fid']."/thumbs/$i";
  $img_ext = get_img_ext($thumb_src);
  $full_src = $thumb_src.$img_ext;

  if (!empty($img_ext)) {
	echo "<a href='#' onclick='upThumb($i)'><div class='thumb_box'><img src='$full_src?".microtime()."' alt='[loading thumbnail]' width='100%' /></div></a>";
  } else {
    echo "<a href='#' onclick='upThumb($i)'><div class='thumb_box'>$i</div></a>";
  }
}
?>

<br clear="both" /><br />
<form name="thumb_form" enctype="multipart/form-data" action="" method="post">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <span id="thumb_nspan">1: </span>
  <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
  <input type="hidden" name="thumb_num" value="1" />
  <input name="thumb_image" type="file" />
  <button type="submit" class="btn" name="upload">Upload</button>
  <button type="submit" class="btn" name="delete">Delete</button>
</form>

<hr />
<p><a class='btn' href='admin.php?page=items&action=edit&fid=<?php echo $_GET['fid']; ?>'>Go Back</a></p>