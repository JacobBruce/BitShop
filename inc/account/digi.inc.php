<?php if (login_state() === 'valid') {

if (empty($_GET['p'])) {
  $curr_page = 1;
} else {
  $curr_page = (int) $_GET['p'];
  if ($curr_page < 1) {
	$curr_page = 1;
  }
}

$prods = list_account_codes($account_id, round(($curr_page-1) * 20));
$tran_num = count_account_codes($account_id);
$page_num = (int) ceil($tran_num / 20);

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
  $nav_html = "<li$p_active><a href='./?page=account&amp;action=myprods&amp;p=1'>First</a></li>";
  for ($i=$start_page;$i<=$end_page;$i++) {
	$p_active = ($i == $curr_page) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='./?page=account&amp;action=myprods&amp;p=$i'>$i</a></li>";
  }
  $p_active = ($curr_page == $page_num) ? " class='active'" : '';
  $nav_html .= "<li$p_active><a href='./?page=account&amp;action=myprods&amp;p=$page_num'>Last</a></li>";
}

if (!empty($nav_html)) {
  echo "<div class='pagination float_right' style='max-width:350px;margin:0px;'><ul>$nav_html</ul></div>";
}

echo '<h1>'.LANG('MY_DIG_ITEMS').'</h1>';
  
if (!empty($tran_num)) {
?>

<div id="prod_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3 id="prod_name">&nbsp;</h3>
  </div>
  <div class="modal-body">
    <center><p id="prod_code" class="copy_link"></p></center>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<script language="JavaScript">
function show_modal(name, code) {
	$('#prod_name').html(name);
	$('#prod_code').html(code);	
	$('#prod_modal').modal('show');
}

$("#prod_code").click(function() {
	selectText('prod_code');
});
</script>

<table class='table table-striped table-bordered table-hover table-condensed'>
<tr>
  <th><?php echo LANG('ORDER'); ?></th>
  <th><?php echo LANG('ITEM'); ?></th>
  <th><?php echo LANG('TYPE'); ?></th>
  <th><?php echo LANG('EXPIRES'); ?></th>
  <th><?php echo LANG('ACTIONS'); ?></th>
</tr>

<?php
  if (!empty($prods) && ($prods !== 'N/A')) {
	while ($row = mysqli_fetch_assoc($prods)) {
	
	  $item = mysqli_fetch_assoc(get_file($row['ItemID']));
	  
	  switch ($item['FileMethod']) {
		case 'download': 
		  $itype = LANG('FILE');
		  $time_diff = get_time_difference($row['Created'], mysqli_now());
		  
		  if ($time_diff['days'] < $link_expire_time) {
		  
			$action_link = "<a class='btn' href='./get_file.php?code=".
			$row['CodeData']."'>".LANG('DOWN_FILE').'</a>';
			
			$days_left = $link_expire_time - $time_diff['days'];
			$hours_left = ($link_expire_time*24) - $time_diff['hours'];
			$mins_left = ($link_expire_time*24*60) - $time_diff['minutes'];
			
			if ($hours_left > 24) {
			  $time_left = $days_left.' '.LANG('DAYS');
			} else {
			  if ($mins_left > 60) {  
				$time_left = $hours_left.' '.LANG('HOURS');
			  } else {
				$time_left = $mins_left.' '.LANG('MINUTES');
			  }
			}
		  } else {
			$time_left = LANG('DOWN_LINK_EX');
			$action_link = LANG('NOT_APPLICABLE');
		  }
		  
		  break;
		  
		case 'keys': 
		  $itype = LANG('KEY');
		  $time_diff = get_time_difference($row['Created'], mysqli_now());
		  if ($time_diff['days'] < $item['FileStock']) {
		  
			$action_link = "<a class='btn' href='#' onclick=\"show_modal('".
			safe_str($item['FileName'])."', '".$row['CodeData']."');\">".
			LANG('SHOW_KEY')."</a> <a class='btn' href='./?page=clients&amp;".
			"key=".$row['CodeData']."'>".LANG('ACCESS_FILE').'</a>';
			
			$days_left = $item['FileStock'] - $time_diff['days'];
			$hours_left = ($item['FileStock']*24) - $time_diff['hours'];
			$mins_left = ($item['FileStock']*24*60) - $time_diff['minutes'];
			
			if ($hours_left > 24) {
			  $time_left = $days_left.' '.LANG('DAYS');
			} else {
			  if ($mins_left > 60) {  
				$time_left = $hours_left.' '.LANG('HOURS');
			  } else {
				$time_left = $mins_left.' '.LANG('MINUTES');
			  }
			}
		  } else {
			$time_left = LANG('PROD_KEY_EX');
			$action_link = LANG('NOT_APPLICABLE');
		  }
		  
		  break;
		  
		case 'codes': 
		  $itype = LANG('CODE');
		  $time_left = LANG('UNKNOWN');
		  
		  $action_link = "<a class='btn' href='#' onclick=\"show_modal('".
		  safe_str($item['FileName'])."', '".$row['CodeData'].
		  "');\">".LANG('SHOW_CODE').'</a>';
		  
		  break;
		  
		default: 
		  $itype = LANG('UNKNOWN');
		  $time_left = LANG('UNKNOWN');
		  $action_link = LANG('NOT_APPLICABLE');
	  }

	  $ord_link = './?page=account&amp;action=myorders&amp;id='.$row['OrderID'];
	  $item_link = './?page=item&amp;id='.$row['ItemID']; 
	  
	  echo "<tr><td><a href='$ord_link'>#".$row['OrderID']."</a></td><td>".
	  "<a href='$item_link'>".$item['FileName']."</a><td>$itype</td><td>".
	  "$time_left</td></td><td>$action_link<small></small></td></tr>";
	}
  }
	
  echo '</table>';
  
} else {
  echo '<p>'.LANG('NO_ITEMS_YET').'</p>';
}

echo '<p><a class="btn" href="./?page=account">'.LANG('GO_BACK').'</a></p>';

} else {
  require_once('./inc/pages/login.inc.php');
}
?>