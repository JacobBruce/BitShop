<?php
require_once('../../sci/config.php');
require_once('../../lib/common.lib.php');
session_start();
?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Database Update</title>
	
	<link rel="stylesheet" href="../../css/normalize.css" />
	<link rel="stylesheet" href="../../css/boilerplate.css" />
	<link rel="stylesheet" href="../../css/bootstrap.min.css" />
	<link rel="stylesheet" href="../../css/bootstrap-responsive.min.css" />
	<link rel="stylesheet" href="../../css/main.css" />
	<script src="../../scripts/jquery.min.js"></script>
	<script src="../../scripts/bootstrap.min.js"></script>
	<script src="../../scripts/general.lib.js"></script>
</head>
<body style="padding:20px;">
  <center>
	<?php
	$step = 1;

	if (isset($_POST['dbpt'])) {
	  $conn = new mysqli($_POST['dbsr'], $_POST['dbun'], $_POST['dbpw'], $_POST['dbdb'], $_POST['dbpt']);
	  if ($conn->connect_error) {
		echo "<p class='error_txt'>Could not establish database connection!<br />".
		"Error: ".$conn->connect_error."</p>";
	  } else {
		$step = 2;
	  }
	}

	if ($step == 1) {
	?>


    <form name="update_form" method="post" action="">
  
	  <h1>Database Update</h1>
	  <h4>For BitShop v9.9.1 to v1.0.6</h4>
	  <hr style="width:400px" />
  
	  <label class="setlab" title="The database port (usually 3306).">Database port:</label>
      <input type="text" name="dbpt" value="<?php echo $db_port; ?>" />
	  <label class="setlab" title="The database server (usually localhost).">Database server:</label>
      <input type="text" name="dbsr" value="<?php echo $db_server; ?>" />
	  <label class="setlab" title="The databse name.">Database name:</label>
      <input type="text" name="dbdb" value="<?php echo $db_database; ?>" />
	  <label class="setlab" title="The database username.">Database username:</label>
      <input type="text" name="dbun" value="<?php echo $db_username; ?>" />
	  <label class="setlab" title="The database password.">Database password:</label>
      <input type="password" name="dbpw" value="<?php if (!empty($_POST['dbpw'])) { echo $_POST['dbpw']; } ?>" />
	  
	  <br /><br />
	  <button type="submit" class="btn btn-warning">Start Database Update</button>
	  <hr style="width:400px" />
	  
	  <div class="alert alert-warning" style="display:inline">
		<b>IMPORTANT:</b> Make sure you create a backup of your BitShop database before updating.
	  </div>
	  
    </form>

	<?php 
	} elseif ($step == 2) {
	  
	  // creates the new tables
	  $queries = file_get_contents('update_db.sql');
	  $result = $conn->multi_query($queries);
	  
	  // clear out results from multiple queries
	  if ($result) {
		while ($conn->more_results()){
		  $conn->next_result();
		  $conn->use_result();
		}
	  } else {
		die("<p class='error_txt'>Could not create new tables!<br />".
		"Error: ".mysqli_error($conn)."</p>");
	  }
	  
	  // update the existing Products table
	  $result = select_from('Products', '*');
	  while ($prod = mysqli_fetch_assoc($result)) {
		$vote_str = trim($prod['FileVotes'], ',');
		if (empty($vote_str)) {
		  $vote_sum = 0;
		  $vote_num = 0;
		} else {
		  $votes = explode(',', $vote_str);
		  $vote_sum = array_sum($votes);
		  $vote_num = count($votes);
		}
		if (!edit_file($prod['FileID'], "FileVoteSum = $vote_sum, FileVoteNum = $vote_num")) {
		  die("<p class='error_txt'>Could not update Products table!<br />".
		  "Error: ".mysqli_error($conn)."</p>");
		}
	  }
	  
	  // remove any unused database fields
	  $conn->query("ALTER TABLE Products DROP FileVotes;");
	?>

	<h1>Congratulations!</h1>
	<p>Your database has been successfully updated!</p>  
	<p><a href="../../admin.php">GO TO ADMIN AREA</a></p>

	<?php } ?>
  </center>
</body>
</html>