<?php
echo $breadcrumb;
  
if (!isset($_GET['id'])) {

  echo "<h1>".LANG('CATS_TITLE')."</h1>\n";
  
  if (!empty($categories) && ($categories != 'N/A')) {
    mysqli_data_seek($categories, 0);
	$col_index = 0;
	$col_html = array('', '');
	
    while ($category = mysqli_fetch_assoc($categories)) {
      if ($category['Active']) {	
	  
        $icon_url = empty($category['Image']) ? './img/list_dot.png' : $category['Image'];	  
        $col_html[$col_index] .= "<li class='head_li''><img src='$icon_url' ".
		"alt='' width='20' height='20' /> <a href='index.php?page=cats&amp;id=".
		$category['CatID']."LCAT-END".$category['CatID']."'>".safe_str($category['Name']).
		" (COUNT-CAT".$category['CatID'].")</a>";
		$cat_ids = $category['CatID'];
	    $sub_cats = get_scats(safe_sql_str($category['CatID']));
		$cat_cnt = '';
		
	    if (!empty($sub_cats) && ($sub_cats != 'N/A')) {
		  $col_html[$col_index] .= "<ul class='sub_list'>";
		  
		  while ($row = mysqli_fetch_assoc($sub_cats)) {
		    if ($row['Active']) {
			  $subc_count = count_file_cat(safe_sql_str($row['CatID']));
			  $icon_url = empty($row['Image']) ? './img/list_dot.png' : $row['Image'];
              $col_html[$col_index] .= "<li class='sub_li'><img src='$icon_url' alt='' width='14' height='14' /> ".
			  "<a href='index.php?page=cats&amp;id=".$row['CatID']."'>".safe_str($row['Name'])." (".$subc_count.")</a></li>\n";
			  $cat_ids .= ','.$row['CatID'];
			}
		  }
		  $col_html[$col_index] .= "</ul>";
		  $cat_cnt = '('.count_file_cats($cat_ids).')';
		  $col_html[$col_index] = str_replace('LCAT-END'.$category['CatID'], '&amp;action=all', $col_html[$col_index]);
		} else {
		  $cat_cnt = count_file_cat($category['CatID']);
		  $cat_cnt = ($cat_cnt>0) ? "($cat_cnt)" : '';
		  $col_html[$col_index] = str_replace('LCAT-END'.$category['CatID'], '', $col_html[$col_index]);
		}
		$col_html[$col_index] = str_replace('(COUNT-CAT'.$category['CatID'].')', "$cat_cnt", $col_html[$col_index]);
		$col_html[$col_index] .= "</li>\n";
      }
	  $col_index = ($col_index == 0) ? 1 : 0;
    }
  }
?>

<div class="row-fluid">
  <div class="span6">
	<ul class='cat_list'>
      <?php echo $col_html[0]; ?>
	</ul>
  </div>
  <div class="span6">
	<ul class='cat_list'>
      <?php echo $col_html[1]; ?>
	</ul>
  </div>
</div>
  
<?php
} else {

  $cat_id = (int)$_GET['id'];
  $sel_cat = get_cat($cat_id);
  $show_list = false;
  
  if (empty($sel_cat) || ($sel_cat == 'N/A')) {
    echo "<p>".LANG('CAT_NONEXISTENT')."</p>\n";
  } else {

    $sel_cat = mysqli_fetch_assoc($sel_cat);
	
    if (!isset($_GET['action'])) {
      if ($sel_cat['Parent'] <= 0) {
	    $sub_cats = get_scats($sel_cat['CatID']);
	    if (!empty($sub_cats) && ($sub_cats != 'N/A')) {
		
		  echo "<h1><a href='index.php?page=cats&amp;id=".$sel_cat['CatID'].
		  "&amp;action=all'>".safe_str($sel_cat['Name'])."</a></h1>\n";
		  echo "<ul class='cat_list'>\n";
		  
		  while ($row = mysqli_fetch_assoc($sub_cats)) {
		    if ($row['Active']) {
			  $icon_url = empty($row['Image']) ? './img/list_dot.png' : $row['Image'];
              echo "<li><img src='$icon_url' alt='' width='14' height='14' /> ".
			  "<a href='index.php?page=cats&amp;id=".$row['CatID']."'>".
			  safe_str($row['Name'])."</a></li>\n"; 
			}
		  }
		  echo "</ul>\n";
		  
	    } else {
	      $show_list = true;
		}
	  } else {
	    $show_list = true;
	  }
	} elseif (($_GET['action'] == 'all') || ($_GET['action'] == 'gen')) {
	  $show_list = true;
	} else {
	  echo "<p>".LANG('INVALID_ACTION')."</p>\n";
	}
	
	if ($show_list == true) {
	
	  if (empty($_GET['sn'])) { $_GET['sn'] = 12; }
	  if (empty($_GET['sm'])) { $_GET['sm'] = 'name'; }
	  if (empty($_GET['so'])) { $_GET['so'] = 'asc'; }
	  
	  if (empty($_SESSION['sn_cache'])) {
	    $_SESSION['sn_cache'] = $_GET['sn'];
	  } elseif ($_SESSION['sn_cache'] != $_GET['sn']) {
		$_GET['p'] = 1;
	  }
	  $_SESSION['sn_cache'] = $_GET['sn'];
	
	  if (isset($_GET['action']) && ($_GET['action'] == 'all')) {
	    $cat_ids = $sel_cat['CatID'];
        if ($sel_cat['Parent'] <= 0) {
	      $sub_cats = get_scats($sel_cat['CatID']);
		  if (!empty($sub_cats) && ($sub_cats != 'N/A')) {
		    while ($row = mysqli_fetch_assoc($sub_cats)) {
		      if ($row['Active']) {
                $cat_ids .= ','.$row['CatID'];
			  }
		    }
		  }
		}
        $item_num = count_file_cats($cat_ids);
        $page_num = (int) ceil($item_num / $_GET['sn']);
	  } else {
        $item_num = count_file_cat($cat_id);
        $page_num = (int) ceil($item_num / $_GET['sn']);
	  }

      if (empty($_GET['p'])) {
        $curr_page = 1;
      } else {
        $curr_page = (int) $_GET['p'];
	    if ($curr_page < 1) {
	      $curr_page = 1;
	    }
      }
	
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
	  
	  $cust_sets = "&amp;sn=".$_GET['sn']."&amp;sm=".$_GET['sm']."&amp;so=".$_GET['so'];
	  $action_html = (empty($_GET['action'])) ? '' : '&amp;action='.$_GET['action'];
	  
      if ($page_num > 1) {
	    $p_active = ($curr_page == 1) ? " class='active'" : '';
	    $nav_html = "<li$p_active><a href='?page=cats&amp;id=$cat_id&amp;p=1$cust_sets$action_html'>".LANG('FIRST')."</a></li>";
        for ($i=$start_page;$i<=$end_page;$i++) {
	      $p_active = ($i == $curr_page) ? " class='active'" : '';
          $nav_html .= "<li$p_active><a href='?page=cats&amp;id=$cat_id&amp;p=$i$cust_sets$action_html'>$i</a></li>";
        }
	    $p_active = ($curr_page == $page_num) ? " class='active'" : '';
	    $nav_html .= "<li$p_active><a href='?page=cats&amp;id=$cat_id&amp;p=$page_num$cust_sets$action_html'>".LANG('LAST')."</a></li>";
      }
    
	  if (!empty($nav_html)) {
	    $sort_align = 'left';
        echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
	  } else {
	    $sort_align = 'right';
	  }
?>
	
	<div class='float_<?php echo $sort_align; ?>' style='max-width:410px;'>
	  <form name="sort_form" method="get" action="index.php">
	    <input type="hidden" name="page" value="cats" />
		<input type="hidden" name="id" value="<?php echo $cat_id; ?>" />
		<input type="hidden" name="p" value="<?php echo $curr_page; ?>" />
		<?php
		if (isset($_GET['action'])) {
		  echo '<input type="hidden" name="action" value="'.$_GET['action'].'" />';
		}
		?>
        <select name='sn' style='width:120px;margin:0px;'>
		  <?php
		  for ($i=12;$i<121;$i+=12) { 
            echo "<option value='$i' ";
			if ($_GET['sn'] == $i) { echo "selected='selected'"; } 
			echo ">$i ".LANG('PER_PAGE')."</option>";
		  }
		  ?>
        </select>
        <select name='sm' style='width:120px;margin:0px;'>
          <option value='name' <?php if ($_GET['sm'] == 'name') { echo "selected='selected'"; } ?>><?php echo LANG('SORT_BY_NAME'); ?></option>
          <option value='price' <?php if ($_GET['sm'] == 'price') { echo "selected='selected'"; } ?>><?php echo LANG('SORT_BY_PRICE'); ?></option>
          <option value='date' <?php if ($_GET['sm'] == 'date') { echo "selected='selected'"; } ?>><?php echo LANG('SORT_BY_DATE'); ?></option>
		  <option value='sales' <?php if ($_GET['sm'] == 'sales') { echo "selected='selected'"; } ?>><?php echo LANG('SORT_BY_SALES'); ?></option>
        </select>
        <select name='so' style='width:110px;margin:0px;'>
          <option value='asc' <?php if ($_GET['so'] == 'asc') { echo "selected='selected'"; } ?>><?php echo LANG('ASCENDING'); ?></option>
          <option value='desc' <?php if ($_GET['so'] == 'desc') { echo "selected='selected'"; } ?>><?php echo LANG('DESCENDING'); ?></option>
        </select>
	    <button type='submit' class='btn' style='margin:0px;height:30px;'><?php echo LANG('GO'); ?></button>
	  </form>
    </div>
	
<?php
	  if ($sort_align == 'left') {
	    echo '<br clear="both" />';
	  }
	
	  echo '<h1>'.safe_str($sel_cat['Name']).'</h1>';
	
	  $sm = empty($_GET['sm']) ? 'FileID' : $_GET['sm'];
	  $so = empty($_GET['so']) ? 'ASC' : strtoupper($_GET['so']);
	
	  switch ($sm) {
        case 'sales':
          $sm = 'FileSales';
          break;
        case 'price':
          $sm = 'FilePrice';
          break;
        case 'name':
          $sm = 'FileName';
          break;
        case 'date':
          $sm = 'Created';
          break;
	    default:
		  $sm = 'FileName';
	      break;
	  }

	  $snum = (int) $_GET['sn'];
	  if (isset($_GET['action']) && ($_GET['action'] == 'all')) {
	    $files = list_files($cat_ids, (($curr_page-1) * $snum), $sm, $so, $snum, true);
	  } else {
	    $files = list_files($cat_id, (($curr_page-1) * $snum), $sm, $so, $snum);
	  }
	  
	  if (!empty($files) && ($files != 'N/A')) {
	    if (mysqli_num_rows($files) > 0) {
	      while ($file = mysqli_fetch_assoc($files)) {
		    echo item_box_html($file, false, $cat_id);
	      }
	    } else {
	      echo '<p>'.LANG('NO_ITEMS_IN_CAT').'</p>';
	    }
	  } else {
	    echo '<p>'.LANG('NO_ITEMS_IN_CAT').'</p>';
	  }
	}
  }
}
?>