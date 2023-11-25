<?php
  if (!empty($page)) {
    if (file_exists("inc/pages/$page.inc.php")) {
      require_once("inc/pages/$page.inc.php");
    } else {
	  echo "<p>The requested page was not found, sorry! :(</p>";  
    }
  } else {
    require_once('inc/pages/home.inc.php');
  }
?>