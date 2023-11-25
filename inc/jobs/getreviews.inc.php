<?php
require_once(dirname(__FILE__).'/../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/../session.inc.php');

$hide_crash = true;
$conn = connect_to_db();

if (!empty($_GET['item_id']) && !empty($_GET['start'])) {
  $reviews = get_reviews($_GET['item_id'], $_GET['start']);
  if (!empty($reviews) && ($reviews !== 'N/A')) {
    while ($row = mysqli_fetch_assoc($reviews)) {
	  echo '<div class="well no_border"><div class="rev_head"><span class="rev_stars">';
	  for ($i=1;$i<6;$i++) {
		if ($i <= $row['Rating']) {
		  echo "<img src='img/star_bright.png' border='0' alt='*' />";
		} else {
		  echo "<img src='img/star_dull.png' border='0' alt='*' />";
		}
	  }		
	  echo '</span><h5 class="rev_author">'.safe_str($row['Author']).
	  '</h5></div><p class="review_txt">'.str_replace("\n", '<br />', 
	  safe_str($row['Review'])).'</p><div class="float_right smaller">'.
	  safe_str(format_time($row['Created'])).'</div><br clear="all" /></div>';
    }
  }
}
?>