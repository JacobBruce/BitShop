<?php
  if (admin_valid(false,true)) {
    if (!empty($page)) {
      if (file_exists("inc/admin/$page.inc.php")) {
        require_once("inc/admin/$page.inc.php");
      } else {
	    echo "<p>The requested page was not found, sorry! :(</p>";  
      }
    } else {
      require_once('inc/admin/home.inc.php');
    }
  } else {
    $_GET['admin'] = true;
    require_once('inc/pages/login.inc.php');
  }
?>