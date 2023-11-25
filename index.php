<?php
// initialize some variables
$errors = array();
$index_call = true;

// common include file
require_once('./inc/common.inc.php');

// log out user
if ($page == 'logout') {

  // clear the session and cookies
  session_unset();
  session_destroy();
  
  // NOW LOGGED OUT - goto login page
  redirect('./?page=login');
  exit;
}

// check if website is enabled
if ($site_enabled || admin_valid(false,false)) {

  // include template files
  require_once("./inc/themes/$template/index.inc.php");
	
} else {

  // display offline message
  echo $disable_msg;
}
?>
