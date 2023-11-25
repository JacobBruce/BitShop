<?php
if (isset($admin_call)) {
  require_once("./inc/themes/$template/index.inc.php");
} else {
  echo "<p>Invalid page access</p>";
}
?>