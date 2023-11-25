<?php
// start the session
session_start();

// call required includes
require_once('lib/common.lib.php');
require_once('inc/config.inc.php');

$page = 'error';
$page_titles[$page] = 'Database Error';

?><!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bitshop - Database Error</title>
  <style>
  body {
	font-family: Helvetica, Geneva, Tahoma;
	font-size:14px;
  }
  
  pre {
    overflow:auto;
  }
  
  #container {
    width:500px;
    margin-left:auto;
    margin-right:auto;
	margin-top:25px;
  }
  </style>
</head>
<body>

  <div id='container'>
  
	<h1>We have a problem...</h1>
	<p><b>The application encountered a fatal database error.</b></p>
	<hr style='width:100%;' />

	<?php
	$xhtml_bug_str = "<p>Please <a href='./index.php?page=contact'>contact ".
					 "the webmaster</a> if the problem persists.</p>";
	 
	if (admin_valid(false,false) || ($debug_sql === true)) {	
	  $sql_errno = isset($_SESSION['sql_errno']) ? safe_str($_SESSION['sql_errno']) : 'n/a';
	  $sql_error = isset($_SESSION['sql_error']) ? safe_str($_SESSION['sql_error']) : 'n/a';
	  $sql_query = isset($_SESSION['sql_query']) ? safe_str($_SESSION['sql_query']) : 'n/a';
	  
	  $error_code = "<p style='color:red'><u>Error Code</u>: </p><p>$sql_errno</p>";
	  $error_code .= "<p style='color:red'><u>Error Message</u>: </p><p>$sql_error</p>";
	  $error_code .= "<p style='color:red'><u>MySQL Query</u>: <pre>$sql_query</pre></p>";
	  
	  echo $error_code."<hr style='width:100%;' />";
	}
  
	$xhtml_bug_str .= 
	"<p>Click <a href='./index.php' title='Go home'>here</a> to go back to the home page.</p>";
  
	echo $xhtml_bug_str;
	?>
  </div>

</body>
</html>