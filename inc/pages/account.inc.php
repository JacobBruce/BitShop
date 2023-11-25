<?php
if (login_state() === 'valid') {

  if (isset($_GET['action'])) {
    if ($_GET['action'] == 'details') {
	  require_once('./inc/account/info.inc.php');
	} elseif ($_GET['action'] == 'password') {
	  require_once('./inc/account/pass.inc.php');
	} elseif ($_GET['action'] == 'settings') {
	  require_once('./inc/pages/settings.inc.php');
	} elseif ($_GET['action'] == 'myorders') {
	  require_once('./inc/account/ords.inc.php');
	} elseif ($_GET['action'] == 'myprods') {
	  require_once('./inc/account/digi.inc.php');
	//} elseif ($_GET['action'] == 'wishlist') {
	//  require_once('./inc/account/wish.inc.php');
	//} elseif ($_GET['action'] == 'support') {
	//  require_once('./inc/account/supp.inc.php');
	} else {
	  echo "<p>Invalid action specified.</p>";
	}
  } else {
?>

<h1><?php echo LANG('ACCOUNT_TITLE'); ?></h1>
<hr />
<p><b>Actions:</b></p>
<ul>
  <li><a href="./?page=account&amp;action=details">Update my details</a></li>
  <li><a href="./?page=account&amp;action=password">Change my password</a></li>
  <li><a href="./?page=account&amp;action=settings">Change my settings</a></li>
  <li><a href="./?page=account&amp;action=myprods">View my digital items</a></li>
  <li><a href="./?page=account&amp;action=myorders">View my orders</a></li>
  <!--<li><a href="./?page=account&amp;action=wishlist">View my wish list</a></li>--> 
  <!--<li><a href="./?page=support">Visit support centre</a></li>-->
  <li><a href="./?page=logout">Logout</a></li>
</ul>

<?php
  }
} else {
  require_once('./inc/pages/login.inc.php');
}
?>