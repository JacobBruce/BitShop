<?php
require_once('../inc/config.inc.php');
require_once('../lib/common.lib.php');
session_start();
?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Database Update</title>
	
	<link rel="stylesheet" href="../css/normalize.css" />
	<link rel="stylesheet" href="../css/boilerplate.css" />
	<link rel="stylesheet" href="../css/bootstrap.min.css" />
	<link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
	<link rel="stylesheet" href="../css/main.css" />
	<script src="../scripts/jquery.min.js"></script>
	<script src="../scripts/bootstrap.min.js"></script>
	<script src="../scripts/general.lib.js"></script>
</head>
<body style="padding:20px;">
  <center>
  
	<h1>Database Update</h1>
	<h4>For BitShop v1.1.6</h4>
	
	<?php
	if (isset($_GET['run'])) {
	  $conn = connect_to_db();

	  // creates the new tables
	  //$queries = file_get_contents('update_db.sql');
	  //$result = $conn->multi_query($queries);
	  
	  // clear out results from multiple queries
	  /*if ($result) {
		while ($conn->more_results()){
		  $conn->next_result();
		  $conn->use_result();
		}
	  } else {
		die("<p class='error_txt'>Could not create new tables!<br />".
		"Error: ".mysqli_error($conn)."</p>");
	  }*/

	  if ($conn->query("ALTER TABLE Products ".
	  "MODIFY COLUMN FileCat TINYTEXT NOT NULL,".
	  "MODIFY COLUMN FileDesc VARCHAR(10000) NOT NULL,".
	  "ADD FileTags TINYTEXT NOT NULL;")) {
	?>
	
	<p>Your database has been successfully updated!</p>  
	<p><a href="../admin.php">GO TO ADMIN AREA</a></p>
	
	<?php
	  } else {
	    echo "<p>An error occurred:</p>";
	    echo "<pre>".$conn->error."</pre>";
	  }
	} else {
	  echo "<a class='btn' href='./update_db.php?run'>Run Update</a>";
	}
	?>
	
  </center>
</body>
</html>