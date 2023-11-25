<?php admin_valid(); ?>

<script language="JavaScript">
var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

function delete_item(meth, sales) {
	if (sales > 0) {
		alert('This item cannot be removed because it has transactions connected to it. You should just deactivate this item instead of deleting it.');
	} else {
		if (confirm('Are you really sure you want to delete this item?')) {
			if (meth == 'keys' || meth == 'codes') {
				if (confirm('WARNING: The keys/codes attached to this product will also be deleted! Proceed?')) {
					redirect('admin.php?page=items&action=edit&task=delick&id=<?php if (!empty($_GET['fid'])) { echo $_GET['fid']; } ?>&toke='+csrf_token);
				}
			} else {
				redirect('admin.php?page=items&action=edit&task=delete&id=<?php if (!empty($_GET['fid'])) { echo $_GET['fid']; } ?>&toke='+csrf_token);
			}
		}
	}
}

function update_item(action) {
	if (confirm('Are you sure you want to '+action+' this item?')) {
		redirect('admin.php?page=items&action=edit&task='+action+'&fid=<?php if (!empty($_GET['fid'])) { echo $_GET['fid']; } ?>&toke='+csrf_token);
	}
}
</script>

<h1>Products</h1>

<?php
if (!empty($_GET['action'])) {

  if ($_GET['action'] === 'new') {
    require_once('inc/admin/newitem.inc.php');
	
  } elseif ($_GET['action'] === 'featured') {
    require_once('inc/admin/featitems.inc.php');
	
  } elseif ($_GET['action'] === 'search') {
    require_once('inc/admin/searchitems.inc.php');
	
  } else {
  
    if (!empty($_GET['task'])) {
	
	  if ($_SESSION['csrf_token'] !== $_GET['toke']) {
	  
	    echo "<p class='error_txt'>".LANG('INVALID_ACCESS')."</p>";
	
	  } elseif (($_GET['task'] === 'delete') && !empty($_GET['id']) && is_numeric($_GET['id'])) {
	  
		if (delete_file(safe_sql_str($_GET['id']))) {
		  echo "<p class='happy_txt'>The item was successfully deleted!</p>";
		} else {
		  echo "<p class='error_txt'>There was an error deleting the item!</p>";
		}
		
	  } elseif (($_GET['task'] === 'delick') && !empty($_GET['id']) && is_numeric($_GET['id'])) {
	 
		if (delete_codes(safe_sql_str($_GET['id']))) {
		  echo "<p class='happy_txt'>Successfully deleted all keys/codes!</p>";
		} else {
		  echo "<p class='error_txt'>Failed to delete all keys/codes!</p>";
		}
		
		if (delete_file(safe_sql_str($_GET['id']))) {
		  echo "<p class='happy_txt'>The item was successfully deleted!</p>";
		} else {
		  echo "<p class='error_txt'>There was an error deleting the item!</p>";
		}

	  } elseif (($_GET['task'] === 'deactivate' || $_GET['task'] === 'activate')) {
	  
		$item_state = ($_GET['task'] === 'activate') ? '1' : '0';
		if (edit_file(safe_sql_str($_GET['fid']), "FileActive = $item_state")) {
		  echo "<p class='happy_txt'>The item was successfully ".$_GET['task']."d!</p>";
		} else {
		  echo "<p class='error_txt'>There was a problem updating the item!</p>";
		}
	  }
    }
		   
    if (!empty($_GET['fid'])) {
	  $file = get_file(safe_sql_str($_GET['fid']));
	  
	  if (empty($file) || ($file === 'N/A')) {
	    echo "<p class='error_txt'>Item does not exist!</p>";
	  } else {
	    $file = mysqli_fetch_assoc($file);
		
		if (!empty($_GET['update'])) {
		  require_once('inc/admin/edititem.inc.php');
		  
		} elseif (!empty($_GET['suba'])) {
		  if ($_GET['suba'] == 'eimages') {
		    require_once('inc/admin/editimages.inc.php');
		  } elseif ($_GET['suba'] == 'ecodes') {
		    require_once('inc/admin/editcodes.inc.php');
		  } elseif ($_GET['suba'] == 'efile') {
		    require_once('inc/admin/editfile.inc.php');
		  }
		} else {
		  if ($file['FileMethod'] === 'download') {
		    $it_1 = 'Size (MB)';
		    $it_2 = 'Extension';
          } elseif ($file['FileMethod'] === 'keys') {
		    $it_1 = 'Life (days)';
		    $it_2 = 'File ID';
          } elseif ($file['FileMethod'] === 'ship') {
		    $it_1 = 'Stock';
		    $it_2 = 'Shipping';
		  } else {
		    $it_1 = 'Stock';
		    $it_2 = 'Type';
		  }
		  
		  if ($file['FilePrice'] > 0) {
		    $cn = $curr_code;
		  } else {
		    $cn = 'BTC';
		  }
		  
		  $file_cats = explode(',', $file['FileCat']);
		  $cat_names = '';
		  
		  foreach ($file_cats as $cat_key => $cat_id) {
		    $sel_cat = get_cat((int)$cat_id);
		  
		    if (empty($sel_cat) || ($sel_cat == 'N/A')) {
			  $cat_names .= "INVALID($sel_cat), ";
		    } else {
			  $sel_cat = mysqli_fetch_assoc($sel_cat);
			  $cat_names .= $sel_cat['Name'].', ';
		    }
		  }
		  
		  $cat_names = trim($cat_names, ", ");
?>

<p><b><?php safe_echo($file['FileName']); ?></b></p>

<table class='table table-striped table-bordered table-condensed' style='width:550px'>
<tr>
  <th>Item ID</th>
  <th><?php safe_echo($it_1); ?></th>
  <th>Price</th>
  <th>Active</th>
</tr><tr>
  <td><?php safe_echo($file['FileID']); ?></td>
  <td><?php safe_echo($file['FileStock']); ?></td>
  <td><?php safe_echo(abs($file['FilePrice'])." $cn"); ?></td>
  <td><?php safe_echo($file['FileActive'] ? 'yes' : 'no'); ?></td>
</tr><tr>
  <th>Categories</th>
  <th>Rating</th>
  <th><?php safe_echo($it_2); ?></th>
  <th>Sales</th>
</tr><tr>
  <td><?php safe_echo($cat_names); ?></td>
  <td><?php 
  if ($file['FileVoteNum'] > 0) {
    safe_echo(get_rating($file).' ('.$file['FileVoteNum'].' votes)');
  } else {
    echo 'no votes yet';
  }
  ?></td>
  <td><?php
  if ($file['FileMethod'] !== 'ship') {
    safe_echo($file['FileType']);
  } else {
    $ship_arr = explode(':', $file['FileType']);
    switch ($ship_arr[0]) {
      case 'fiat': safe_echo($ship_arr[1].' '.$curr_code); break;
      case 'global': safe_echo($global_shipping.' '.$curr_code); break;
      case 'weight': safe_echo(bcmul($weight_mult, $ship_arr[1]).' '.$curr_code); break;
	  default: safe_echo(empty($ship_arr[1]) ? 'free' : $ship_arr[1].' '.'BTC'); break;
    }
  }
  ?></td>
  <td><?php safe_echo($file['FileSales']); ?></td>
</tr><tr>
  <th colspan="4">Item Code</th>
</tr><tr>
  <td colspan="4"><?php safe_echo($file['FileCode']); ?></td>
</tr><tr>
  <th colspan="4">Description</th>
</tr><tr>
  <td colspan="4"><?php echo $file['FileDesc']; ?></td>
</tr></table>

<p><a href="admin.php?page=items&amp;action=edit" title="Go Back">BACK</a> | <a href="admin.php?page=items&amp;action=edit&amp;fid=<?php echo $_GET['fid']; ?>&amp;update=y" title="Edit Item">EDIT ITEM</a> | <a href="admin.php?page=items&amp;action=edit&amp;fid=<?php echo $_GET['fid']; ?>&amp;suba=eimages" title="Edit Item">EDIT IMAGES</a><?php if ($file['FileMethod'] == 'codes' || $file['FileMethod'] == 'keys') { ?> | <a href="admin.php?page=items&amp;action=edit&amp;fid=<?php echo $_GET['fid']; ?>&amp;suba=ecodes" title="Manage Codes">EDIT CODES</a><?php } elseif ($file['FileMethod'] == 'download') { ?> | <a href="admin.php?page=items&amp;action=edit&amp;fid=<?php echo $_GET['fid']; ?>&amp;suba=efile" title="Edit File">EDIT FILE</a><?php } ?> | <a href="#" title="Delete Item" onClick="delete_item(<?php echo "'".$file['FileMethod']."',".$file['FileSales']; ?>);">DELETE ITEM</a> | <a href='#' onClick="update_item('<?php echo ($file['FileActive'] ? 'deactivate' : 'activate'); ?>');"><?php echo ($file['FileActive'] ? 'DEACTIVATE ITEM' : 'ACTIVATE ITEM'); ?></a></p>

<?php 
    } 
  } 
} else {

  if (empty($_GET['p'])) {
    $curr_page = 1;
  } else {
    $curr_page = (int) $_GET['p'];
	if ($curr_page < 1) {
	  $curr_page = 1;
	}
  }
	
  $files = list_all_files(round(($curr_page-1) * 20));
  $item_num = count_files();
  $page_num = (int) ceil($item_num / 20);
  
  $start_page = $curr_page - 2;
  if ($start_page < 1) {
	$start_page = 1;
  }
	
  $end_page = $start_page + 4;
  if ($end_page > $page_num) {
	$end_page = $page_num;
	$start_page = $end_page - 4;
	if ($start_page < 1) {
	  $start_page = 1;
	}
  }
  
  if ($page_num > 1) {
    $p_active = ($curr_page == 1) ? " class='active'" : '';
    $nav_html = "<li$p_active><a href='admin.php?page=items&amp;action=edit&amp;p=1'>First</a></li>";
    for ($i=$start_page;$i<=$end_page;$i++) {
	  $p_active = ($i == $curr_page) ? " class='active'" : '';
      $nav_html .= "<li$p_active><a href='admin.php?page=items&amp;action=edit&amp;p=$i'>$i</a></li>";
    }
	$p_active = ($curr_page == $page_num) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='admin.php?page=items&amp;action=edit&amp;p=$page_num'>Last</a></li>";
  }
	
  if (!empty($nav_html)) {
    echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
  }
  
  if (!empty($item_num)) {
?>

<p><b>List of products:</b></p>

<table class='table table-striped table-bordered table-hover table-condensed'>
<tr>
  <th>Item ID</th>
  <th>Name</th>
  <th>Method</th>
  <th>Price</th>
  <th>Stock</th>
</tr>
<?php
  if (!empty($files) && ($files !== 'N/A')) {
    while ($row = mysqli_fetch_assoc($files)) {
	
	  if ($row['FilePrice'] > 0) {
	    $cn = $curr_code;
	  } else {
	    $cn = 'BTC';
	  }
		  
	  $row_class = ($row['FileActive'] == 1) ? 'success' : 'error';
	  $file_stock = ($row['FileMethod'] === 'download' || $row['FileMethod'] === 'keys') ? '&#8734;' : $row['FileStock'];
	  $row_link = 'admin.php?page=items&amp;action=edit&amp;fid='.$row['FileID'];
	  
      echo "<tr class='$row_class tr_link' onclick=\"document.location='$row_link';\"><td><a href='$row_link'>".
	  $row['FileID']."</a></td><td>".safe_str($row['FileName'])."</td><td>".$row['FileMethod'].
	  "</td><td>".abs($row['FilePrice'])." $cn</td><td>".$file_stock."</td></tr>";
    }
  }
?>
</table>

<?php
  } else {
    echo "<p>There are no products yet.</p>";
  }
?>

<p><a class="btn" href="admin.php?page=items">Go Back</a></p>
  
<?php
    }
  }
} else {
?>

<p><b>Select an option:</b></p>

<p>
  <a href="admin.php?page=items&action=new" title="Add New Product">NEW PRODUCT</a><br />
  <a href="admin.php?page=items&action=edit" title="Manage Products">MANAGE PRODUCTS</a><br />
  <a href="admin.php?page=items&action=featured" title="Featured Products">FEATURED PRODUCTS</a><br />
  <a href="admin.php?page=items&action=search" title="Search Products">SEARCH PRODUCTS</a><br />
  <a href="admin.php?page=home" title="Main Menu">BACK</a>
</p>

<?php } ?>
