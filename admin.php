<?php
// initialize some variables
$errors = array();
$admin_call = true;

// common include file
require_once('./inc/common.inc.php');

// log out admin user
if ($page == 'logout') {

  // clear the session and cookies
  session_unset();
  session_destroy();
  unset_cookies();
  
  // NOW LOGGED OUT - goto home page
  redirect('./admin.php');
  exit;
}

// include index handler
require_once("./inc/admin/index.inc.php");
?>