<?php admin_valid();
  if (empty($_GET['q'])) {
?>

<p>Search phrase:</p>
<form class="form-search" name="search_items" method="get" action="admin.php">
  <div class="input-append">
    <input type="hidden" name="page" value="items" />
	<input type="hidden" name="action" value="search" />
    <input type="text" value="" name="q" maxlength="50" class="search-query" />
    <button type="submit" class="btn">search</button>
  </div>
</form>
<p><a class="btn" href="admin.php?page=items">Go Back</a></p>

<?php
  } else {

	if (empty($_GET['sn'])) { $_GET['sn'] = 12; }
	  
	if (empty($_SESSION['sns_cache'])) {
	  $_SESSION['sns_cache'] = $_GET['sn'];
	} elseif ($_SESSION['sns_cache'] != $_GET['sn']) {
	  $_GET['p'] = 1;
	}
	$_SESSION['sns_cache'] = $_GET['sn'];

    if (empty($_GET['p'])) {
      $curr_page = 1;
    } else {
      $curr_page = (int) $_GET['p'];
	  if ($curr_page < 1) {
	    $curr_page = 1;
	  }
    }
	
	$snum = (int) $_GET['sn'];
	$safe_q = safe_str(urlencode($_GET['q']));
    $result = search_files($_GET['q'], ($curr_page-1) * $snum, $snum, 1);
    $page_num = (int) ceil($result['count'] / $snum);

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
	
    $cust_sets = "&amp;sn=".$_GET['sn'];

    if ($page_num > 1) {
	  $p_active = ($curr_page == 1) ? " class='active'" : '';
	  $nav_html = "<li$p_active><a href='admin.php?page=items&amp;action=search&amp;q=$safe_q&amp;p=1$cust_sets'>First</a></li>";
      for ($i=$start_page;$i<=$end_page;$i++) {
	    $p_active = ($i == $curr_page) ? " class='active'" : '';
        $nav_html .= "<li$p_active><a href='admin.php?page=items&amp;action=search&amp;q=$safe_q&amp;p=$i$cust_sets'>$i</a></li>";
      }
	  $p_active = ($curr_page == $page_num) ? " class='active'" : '';
	  $nav_html .= "<li$p_active><a href='admin.php?page=items&amp;action=search&amp;q=$safe_q&amp;p=$page_num$cust_sets'>Last</a></li>";
    }
    
	echo '<ul class="breadcrumb"><li>search phrase: '.safe_str($_GET['q']).
	'<span class="divider">|</span></li><li>number of results: '.$result['count'].
	'<span class="divider">|</span></li><li>showing '.$snum.' per page</li></ul>';
	
	if (!empty($nav_html)) {
      $sort_align = 'left';
      echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
	} else {
	  $sort_align = 'right';
	}
?>

	<div class='float_<?php echo $sort_align; ?>' style='max-width:180px;'>
	  <form name="sort_form" method="get" action="admin.php">
        <input type="hidden" name="page" value="items" />
	    <input type="hidden" name="action" value="search" />
		<input type="hidden" name="q" value="<?php echo $safe_q; ?>" />
		<input type="hidden" name="p" value="<?php echo $curr_page; ?>" />
        <select name='sn' style='width:120px;margin:0px;'>
		  <?php
		  for ($i=12;$i<121;$i+=12) { 
            echo "<option value='$i' ";
			if ($_GET['sn'] == $i) { echo "selected='selected'"; } 
			echo ">$i per page</option>";
		  }
		  ?>
        </select>
	    <button type='submit' class='btn' style='margin:0px;height:30px;'>GO</button>
	  </form>
    </div>

<?php

	if ($sort_align == 'left') {
	  echo '<br clear="both" />';
	  echo "<p><b>Product Search</b></p>";
	} else {
	  echo "<p><b>Product Search<br />&nbsp;</b></p>";
	}
	
	if (!empty($result['query']) && ($result['query'] != 'N/A')) {
	  if (count($result['query']) > 0) {
	    foreach ($result['query'] as $key => $file) {
		  echo item_box_html($file, true);
	    }
	  } else {
	    echo "<p>Sorry, nothing was found :(</p>";
	  }
	} else {
	  echo "<p>Sorry, nothing was found :(</p>";
	}  
  }
?>