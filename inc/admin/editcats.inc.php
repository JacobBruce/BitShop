<?php admin_valid();
	
  $all_cats = get_pcats(false);
  echo "<h1>Categories</h1>\n";
  
  if (!empty($_GET['cid']) && !isset($_GET['pid']) && !isset($_GET['task'])) {
	
    if (!empty($_POST)) {

	  $bad_par = false;
	  
	  if ($_POST['cat_parent'] > 0) {
        $par_cat = get_cat(safe_sql_str($_POST['cat_parent']));
        if (!empty($par_cat) && ($par_cat != 'N/A')) {
          $par_cat = mysqli_fetch_assoc($par_cat);
		  if ($par_cat['Parent'] > 0) {
		    $bad_par = 1;
		  } elseif (count_sub_cats($_GET['cid']) > 0) {
		    $bad_par = 2;
		  }
		}
	  }
	  
	  if ($bad_par === 1) {
	    echo "<p class='error_txt'>Parent category cannot be another sub-category!</p>";
	  } elseif ($bad_par === 2) {
	    echo "<p class='error_txt'>Remove sub-categories before choosing parent!</p>";
	  } elseif (empty($_POST['cat_name'])) {
	    echo "<p class='error_txt'>You must supply a category name!</p>";
	  } elseif (!is_numeric($_POST['cat_parent'])) {
	    echo "<p class='error_txt'>Invalid parent specified!</p>";
	  } else {
	    if (update_cat(safe_sql_str($_GET['cid']), $_POST['cat_parent'],
		safe_sql_str($_POST['cat_name']), safe_sql_str($_POST['cat_icon']))) {
		  echo "<p class='happy_txt'>Category successfully updated!</p>";
	    } else {
		  echo "<p class='error_txt'>Failed to update category!</p>";
	    }
	  }
    }
	
    $sel_cat = get_cat(safe_sql_str($_GET['cid']));
    if (!empty($sel_cat) && ($sel_cat != 'N/A')) {
      $sel_cat = mysqli_fetch_assoc($sel_cat);
	  
?>

<p><b>Edit category:</b></p>

<form class="form-inline" name='cat_form' method='post' action=''>
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <table cellpadding="5">
	<tr><td><label for="cat_parent" style="margin-top:6px;">Parent:</label></td>
	<td><select name="cat_parent" style="width:215px;"><?php
	  if (!empty($all_cats) && ($all_cats != 'N/A')) {
		mysqli_data_seek($all_cats, 0);
		echo '<option value="0">None</option>';
		while ($category = mysqli_fetch_assoc($all_cats)) {
		  $selected = ($sel_cat['Parent']==$category['CatID']) ? 'selected="selected"' : '';
		  echo "<option value='".$category['CatID']."' $selected>".safe_str($category['Name']).'</option>';
		}
		
	  }
	?></select></td></tr>
	<tr><td><label for="cat_name" style="margin-top:6px;">Name:</label></td>
	<td><input name="cat_name" type="text" maxlength="255" value="<?php safe_echo($sel_cat['Name']); ?>" style="width:200px;" /></td></tr>
	<tr><td><label for="cat_icon" style="margin-top:6px;">Icon:</label></td>
	<td><input name="cat_icon" type="text" maxlength="255" value="<?php safe_echo($sel_cat['Image']); ?>" style="width:200px;" /></td></tr>
  </table>
  <br />
  <a class='btn' href='admin.php?page=editcats<?php 
  if (!empty($_GET['p'])) echo '&amp;pid='.$_GET['p']; ?>'>Go Back</a> 
  <button type='submit' name='submit' class='btn'>Update</button>
</form>

<?php
    } else {
	  echo "<p class='error_txt'>Category not found.</p>";
    }
  } elseif (isset($_GET['new'])) {
  
    $cat_count = mysqli_num_rows($all_cats);
	$cat_index = (isset($_GET['pid'])) ? count_sub_cats($_GET['pid'])+1 : $cat_count+1;
	
    if (!empty($_POST)) {

	  $bad_par = false;
		  
	  if ($_POST['cat_parent'] > 0) {
        $par_cat = get_cat(safe_sql_str($_POST['cat_parent']));
        if (!empty($par_cat) && ($par_cat != 'N/A')) {
          $par_cat = mysqli_fetch_assoc($par_cat);
		  if ($par_cat['Parent'] > 0) {
		    $bad_par = true;
		  }
		}
	  }
	  
	  if ($bad_par == true) {
	    echo "<p class='error_txt'>Parent category cannot be another sub-category!</p>";
	  } elseif (empty($_POST['cat_name'])) {
	    echo "<p class='error_txt'>You must supply a category name!</p>";
	  } elseif (!is_numeric($_POST['cat_parent'])) {
	    echo "<p class='error_txt'>Invalid parent specified!</p>";
	  } else {
	    if (insert_cat($cat_index, $_POST['cat_parent'], 
		safe_sql_str($_POST['cat_name']), safe_sql_str($_POST['cat_icon']), 1)) {
		  echo "<p class='happy_txt'>Category successfully created!</p>";
	    } else {
		  echo "<p class='error_txt'>Failed to create category!</p>";
	    }
	    $all_cats = get_pcats(false);
	  }
    }
?>

<p><b>Create new category:</b></p>

<form class='form-inline' name='cat_form' method='post' action=''>
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <table cellpadding="5">
	<tr><td><label for="cat_parent" style="margin-top:6px;">Parent:</label></td>
	<td><select name="cat_parent" style="width:215px;"><?php
	  $par_ind = empty($_GET['new']) ? 0 : (int)$_GET['new'];
	  if (!empty($all_cats) && ($all_cats != 'N/A')) {
		mysqli_data_seek($all_cats, 0);
		echo '<option value="0">None</option>';
		while ($category = mysqli_fetch_assoc($all_cats)) {
		  $selected = ($par_ind==$category['CatID']) ? 'selected="selected"' : '';
		  echo "<option value='".$category['CatID']."' $selected>".safe_str($category['Name']).'</option>';
		}
		
	  }
	?></select></td></tr>
	<tr><td><label for="cat_name" style="margin-top:6px;">Name:</label></td>
	<td><input name="cat_name" type="text" maxlength="255" value="" style="width:200px;" /></td></tr>
	<tr><td><label for="cat_icon" style="margin-top:6px;">Icon:</label></td>
	<td><input name="cat_icon" type="text" maxlength="255" value="" style="width:200px;" /></td></tr>
  </table>
  <br />
  <a class='btn' href='admin.php?page=editcats<?php 
  if (!empty($_GET['new'])) echo '&amp;pid='.$_GET['new']; ?>'>Go Back</a> 
  <button class='btn' type='submit' name='submit'>Submit</button>
</form>

<?php
  } else {
  
    if (!empty($_GET['task'])) {
	  if ($_SESSION['csrf_token'] !== $_GET['toke']) {
	    echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
      } elseif ($_GET['task'] == 'toggle') {
	    if ($_GET['newstate'] == 1) {
	      if (edit_cat($_GET['cid'], 'Active', 1)) {
		    echo "<p class='happy_txt'>Category successfully enabled!</p>";
	      } else {
		    echo "<p class='error_txt'>Failed to enable category!</p>";
	      }
		} else {
	      if (edit_cat($_GET['cid'], 'Active', 0)) {
		    echo "<p class='happy_txt'>Category successfully disabled!</p>";
	      } else {
		    echo "<p class='error_txt'>Failed to disable category!</p>";
	      }
		}
	  } elseif ($_GET['task'] == 'delete') {
	    if (delete_cat($_GET['cid'])) {
		  echo "<p class='happy_txt'>Category successfully deleted!</p>";
	    } else {
		  echo "<p class='error_txt'>Failed to delete category!</p>";
	    }
	  } elseif ($_GET['task'] == 'movecat') {
	    if (swap_cat_pos($_GET['cid1'], $_GET['cid2'], $_GET['cpos1'], $_GET['cpos2'])) {
		  echo "<p class='happy_txt'>Category position updated!</p>";
	    } else {
		  echo "<p class='error_txt'>Failed to move category!</p>";
	    }
	  }
	  $all_cats = get_pcats(false);
	}
	
	$cat_thead = 'Children';
	$cat_par_str = ', "none"';
	$parg = '';
    if (isset($_GET['pid'])) {
	  $parg = '&amp;p='.$_GET['pid'];
	  $cat_par_str = ', '.$_GET['pid'];
      $sel_cat = get_cat(safe_sql_str($_GET['pid']));
      if (!empty($sel_cat) && ($sel_cat != 'N/A')) {
        $sel_cat = mysqli_fetch_assoc($sel_cat);
		$all_cats = get_scats($sel_cat['CatID'], false);
		$cat_thead = 'Parent';
	  } else {
	    echo "<p class='error_txt'>Invalid category ID!</p>";
	  }
	}
?>

<p><b>List of categories:</b></p>
<table class='table table-striped table-bordered table-hover table-condensed'>
<tr><th>Name</th><th>ID</th><th><?php echo $cat_thead; ?></th><th>Icon</th><th>Actions</th></tr>

<?php
	$cat_ind_arr = array();
	$cat_row_ind = 0;
    if (!empty($all_cats) && ($all_cats != 'N/A')) {
      mysqli_data_seek($all_cats, 0);
      while ($category = mysqli_fetch_assoc($all_cats)) {
	  
	    $cat_ind_arr[$category['CatID']] = $category['CatPos'];
		
	  	if (isset($_GET['pid'])) {
		  $subc_show = safe_str($sel_cat['Name']);
		} else {
		  $child_count = count_sub_cats($category['CatID']);
		  $subc_show = ($child_count == 0) ? 0 : "<a href='admin.php?page".
		  "=editcats&amp;pid=".$category['CatID']."'>$child_count</a>";
		}
		
	    $cat_img = (empty($category['Image'])) ? 'None' : 
		"<img width='20' height='20' src='".$category['Image']."' alt='' />";
		
		if ($category['Active'] == 1) {
		  $cat_tog = 'DISABLE';
		  $row_class = 'success';
		  $action = 0;
		} else {
		  $cat_tog = 'ENABLE';
		  $row_class = 'error';
		  $action = 1;
		}
		
	    echo "<tr class='$row_class'><td>".$category['Name']."</td><td>".$category['CatID'].
		"</td><td>$subc_show</td><td>$cat_img</td><td><a href='admin.php?page=editcats".
		"$parg&amp;cid=".$category['CatID']."'>EDIT</a> | <a href='javascript:toggle_cat(".
		$category['CatID'].", $action$cat_par_str);'>$cat_tog</a> | <a href='javascript:del_cat(".
		$category['CatID']."$cat_par_str);'>REMOVE</a><div class='float_right' ".
		"style='width:13px;height:22px;position:relative;top:-5px;overflow:visible'>".
		"<div style='width:13px;height:13px'><a class='no_deco' href='javascript:move_cat_up".
		"($cat_row_ind$cat_par_str)'>˄</a></div><div style='width:13px;height:13px'><a class='no_deco' href='".
		"javascript:move_cat_down($cat_row_ind$cat_par_str)'>˅</a></div></div></td></tr>";

		$cat_row_ind++;
	  }
	}
?>

</table>

<p><?php
	if (isset($_GET['pid'])) {
	  echo "<a class='btn' href='admin.php?page=editcats'>Go Back</a> ";
	  echo "<a class='btn' href='admin.php?page=editcats&amp;new=".$_GET['pid']."'>New Sub-category</a>";
	} else {
	  echo "<a class='btn' href='admin.php?page=editcats&amp;new'>New Category</a>";
	}
?></p>

<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';
var cat_inds = [<?php echo implode(',', array_keys($cat_ind_arr)); ?>];
var cat_poss = [<?php echo implode(',', $cat_ind_arr); ?>];

function del_cat(cat_id, p) {
	if (confirm('Are you sure you want to delete this category?')) {
		const parg = (p === 'none') ? '' : '&pid='+p;
		redirect('admin.php?page=editcats'+parg+'&cid='+cat_id+'&task=delete&toke='+csrf_token);
	}
}

function toggle_cat(cat_id, new_state, p) {
	if (new_state == 1) { var action = 'enable'; } else { var action = 'disable'; }
	if (confirm('Are you sure you want to '+action+' this category?')) {
		const parg = (p === 'none') ? '' : '&pid='+p;
		redirect('admin.php?page=editcats'+parg+'&cid='+cat_id+'&task=toggle&newstate='+new_state+'&toke='+csrf_token);
	}
}

function move_cat_up(i, p) {
	if (i > 0) {
		const parg = (p === 'none') ? '' : '&pid='+p;
		redirect('admin.php?page=editcats'+parg+'&task=movecat&cid1='+cat_inds[i]+'&cid2='+
		cat_inds[i-1]+'&cpos1='+cat_poss[i]+'&cpos2='+cat_poss[i-1]+'&toke='+csrf_token);
	} else {
		alert('This category is already first');
	}
}

function move_cat_down(i, p) {
	if (i < cat_inds.length-1) {
		const parg = (p === 'none') ? '' : '&pid='+p;
		redirect('admin.php?page=editcats'+parg+'&task=movecat&cid1='+cat_inds[i]+'&cid2='+
		cat_inds[i+1]+'&cpos1='+cat_poss[i]+'&cpos2='+cat_poss[i+1]+'&toke='+csrf_token);
	} else {
		alert('This category is already last');
	}
}
</script>

<?php } ?>