<?php
require_once('../sci/config.php');
require_once('../lib/common.lib.php');

session_start();

?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Install BitShop</title>
	
	<link rel="stylesheet" href="../css/normalize.css" />
	<link rel="stylesheet" href="../css/boilerplate.css" />
	<link rel="stylesheet" href="../css/bootstrap.min.css" />
	<link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
	<link rel="stylesheet" href="../css/main.css" />
	<script src="../scripts/jquery.min.js"></script>
	<script src="../scripts/bootstrap.min.js"></script>
	<script src="../scripts/general.lib.js"></script>
	<script src="../scripts/rsa/prng4.js"></script>
	<script src="../scripts/rsa/rng.js"></script>
	<script src="../scripts/rsa/rsa.js"></script>
	<script src="../scripts/rsa/rsa2.js"></script>
	<script src="../scripts/rsa/base64.js"></script>
	<script src="../scripts/rsa/jsbn.js"></script>
	<script src="../scripts/rsa/jsbn2.js"></script>
</head>
<body style="padding:20px;text-align:center;">
<?php
$_GET['step'] = empty($_GET['step']) ? 1 : $_GET['step'];

if ($_GET['step'] == 1) {
  if (isset($_POST['lcal'])) {
	$conn = new mysqli($_POST['dbsr'], $_POST['dbun'], $_POST['dbpw'], '', $_POST['dbpt']);
	if ($conn->connect_error) {
	  echo "<p class='error_txt'>Could not establish database connection!<br />".
	  "Check that your database username, password, and port are correct.<br />".
	  "Error: ".$conn->connect_error."</p>";
	} else {
	  if (!mysqli_query($conn, 'CREATE DATABASE IF NOT EXISTS '.$_POST['dbdb'])) {
	    echo "<p class='error_txt'>Could not create new database (".$_POST['dbdb'].").<br />".
	    "If the problem persists try creating the database manually.<br />".
	    "Error: ".mysqli_error($conn)."</p>";
      } else {
	    if (!($conn->select_db($_POST['dbdb']))) {
	      echo "<p class='error_txt'>Could not select database: ".$_POST['dbdb']."<br />".
	      "Error: ".mysqli_error($conn)."</p>";
	    } else {
		  $queries = file_get_contents('mysql_db.sql');
		  if (!($conn->multi_query($queries))) {
	        echo "<p class='error_txt'>Could not create new tables in ".$_POST['dbdb'].".<br />".
	        "If the problem persists try creating the tables manually using mysql_db.sql.<br />".
	        "Error: ".mysqli_error($conn)."</p>";
		  } else {
			while ($conn->more_results()){
			  $conn->next_result();
			  $conn->use_result();
			}
			if (isset($_POST['def_cats']) && $_POST['def_cats'] == 'yes') {
			  $query_str = "INSERT INTO Categories (CatID, 
			  CatPos, Parent, Name, Image, Active) VALUES
			  (1, 1, 0, 'Software Keys', './pics/cat_1.png', 1),
			  (2, 2, 0, 'Digital Files', './pics/cat_2.png', 1),
			  (3, 3, 0, 'Gift Codes', './pics/cat_3.png', 1),
			  (4, 4, 0, 'Other Stuff', './pics/cat_4.png', 1);";
			  if (!($conn->query($query_str))) {
	            $error = "<p class='error_txt'>Failed to create default categories.</p>";
			  }
			}
	        if (!empty($_POST['apss1']) && ($_POST['apss1'] != $_POST['apss2'])) {
	          echo "<p class='error_txt'>The admin password specified is either blank or not repeated properly.</p>";
	        } else {
			  $idir = trim($_POST['idir'], '/');
			  $_POST['idir'] = empty($idir) ? '/' : "/$idir/";
              $new_config = array(
	            'db_port' => $_POST['dbpt'], 'db_server' => $_POST['dbsr'],
	            'db_database' => $_POST['dbdb'], 'db_username' => $_POST['dbun'],
	            'db_password' => $_POST['dbpw'], 'install_dir' => $_POST['idir'], 
				'locale' => $_POST['lcal'], 'rand_str' => rand_str() 
              );
			  $account = get_account_byemail('admin');
			  if (empty($account) || ($account === 'N/A')){
			    $pass_hash = pass_hash($_POST['apss1'], $hash_rounds);
			    if (!create_account('admin', $pass_hash, 2)) {
			      $error = "<p class='error_txt'>Failed to create admin account!</p>";
			    }
			  }
			  if (empty($error)) {
                if (update_config('main', $new_config)) {
	              redirect('install.php?step=2');
                } else {
                  echo "<p class='error_txt'>Failed to apply settings!</p>";
                }
			  } else {
			    echo $error;
			  }
	        }
		  }
		}
	  }
	}

	$locale = $_POST['lcal'];
	$install_dir = $_POST['idir'];
	$db_port = $_POST['dbpt'];
	$db_server = $_POST['dbsr'];
	$db_database = $_POST['dbdb'];
	$db_username = $_POST['dbun'];
  }
?>

<h1>Step 1</h1>
<p><b>Critical Settings</b></p>

<form name='set_form' method='post' action='?step=1'>
  <div class="row-fluid">
    <div class="span12">
	  <label class="setlab" title="This will determine what language the shop will use.">Language:</label>
      <select name="lcal">
	  <?php
		$lang_files = list_binaries('../inc/langs/');
	  
        foreach ($lang_files as $key => $file) {
		  
          if ($file != "index.html" && $file != "error_log") {

		    $file = explode('.', $file);
			$file = $file[0];
		    $selected = ($locale == $file) ? 'selected="selected"' : '';
            echo "<option value='$file' $selected>".safe_str($langarray["$file"])."</option>";

          }
        }
	  ?>
      </select>
	  <label class="setlab" title="The installation path of this script (just / if installed at root).">Install directory:</label>
      <input type="text" name="idir" value="<?php echo $install_dir; ?>" />
	  <label class="setlab" title="The database port (usually 3306).">Database port:</label>
      <input type="text" name="dbpt" value="<?php echo $db_port; ?>" />
	  <label class="setlab" title="The database server (usually localhost).">Database server:</label>
      <input type="text" name="dbsr" value="<?php echo $db_server; ?>" />
	  <label class="setlab" title="The database name.">Database name:</label>
      <input type="text" name="dbdb" value="<?php echo $db_database; ?>" />
	  <label class="setlab" title="The database username.">Database username:</label>
      <input type="text" name="dbun" value="<?php echo $db_username; ?>" />
	  <label class="setlab" title="The database password.">Database password:</label>
      <input type="password" name="dbpw" value="<?php if (!empty($_POST['dbpw'])) { echo $_POST['dbpw']; } ?>" />
	  <label class="setlab" title="The password required for logging into this admin area.">Admin Password:</label>
      <input type="password" name="apss1" value="<?php if (!empty($_POST['apss1'])) { echo $_POST['apss1']; } ?>" />
	  <label class="setlab" title="Repeat the admin password specified above.">Repeat Password:</label>
      <input type="password" name="apss2" value="<?php if (!empty($_POST['apss2'])) { echo $_POST['apss2']; } ?>" />
	  <div class="form-inline">
	    <label class="setlab" title="Insert default categories into the database.">Create Default Categories:</label> 
        <input type="checkbox" name="def_cats" value="yes" checked="checked" />
	  </div>
	</div>
  </div>
  <br /><button type='submit' class='btn'>Next</button>
</form>
  
<?php
} elseif ($_GET['step'] == 2) {
  if (isset($_POST['pub_key'])) {
    $new_config = array('pub_rsa_key' => $_POST['pub_key']);
    if (update_config('sci', $new_config)) {
	  redirect('install.php?step=3');
    } else {
      echo "<p class='error_txt'>Failed to apply settings, check config file permissions.</p>";
    }
  }
?>

<h1>Step 2</h1>
<p><b>RSA Key Setup</b></p>

<script language="JavaScript">
var keys_saved = false;

function gen_keys() {
	keys_saved = false;
	var rsa = new RSAKey();
	rsa.generate(1024, '10001');

	n_value = rsa.n.toString(16);
	d_value = rsa.d.toString(16);
	p_value = rsa.p.toString(16);
	q_value = rsa.q.toString(16);
	dmp1_value = rsa.dmp1.toString(16);
	dmq1_value = rsa.dmq1.toString(16);
	coeff_value = rsa.coeff.toString(16);

	$("#pub_key").val(n_value);
	$("#priv_key").val(d_value+':'+p_value+':'+q_value+':'+dmp1_value+':'+dmq1_value+':'+coeff_value);
}

function save_keys() {
	if ($("#pub_key").val() != '' && $("#priv_key").val() != '') {
		var key_data = "PUBLIC KEY:\r\n\r\n"+$("#pub_key").val()+
			"\r\n\r\nPRIVATE KEY:\r\n\r\n"+$("#priv_key").val();
		downloadBlob(key_data, 'rsa_keys', 'txt');
		keys_saved = true;
	} else {
		alert('Please generate a new keypair first!');
	}
}

function use_key() {
	if (!keys_saved) {
		alert('Please save the key pair before proceeding.');
	} else if ($("#pub_key").val() != '' && $("#priv_key").val() != '') {
		$('#set_form').submit();
	} else {
		alert('Please generate a new keypair first!');
	}
}

$(document).ready(function() {
    $("input:text").focus(function() { $(this).select(); } );
});
</script>

<form name="kgf">
  <button type="button" class="btn" onClick="gen_keys();">generate new key pair</button>
  <button type="button" class="btn" onClick="save_keys();">save key pair</button>
</form>

<form name='set_form' id='set_form' method='post' action='?step=2'>
  <div>
    <b>Public Key:</b><br />
    <input type="text" name="pub_key" id="pub_key" value="" style="width:400px;" />
    <br /><br />
    <b>Private Key:</b><br />
    <input type="text" id="priv_key" value="" style="width:400px;" />
  </div>
  <br />
  <button type='button' class='btn' onClick="use_key();">Apply</button> 
  <a class="btn" href="./install.php?step=3">Skip</a>
</form>

<div style="width:500px;margin-left:auto;margin-right:auto;margin-top:20px;">

  <p><b>Information:</b></p>
  
  <p><b>Information:</b> after you generate new public and private RSA keys, click 'Apply' to save the new public RSA key to your config file. The public key is only used to encrypt data, only the private key can decrypt data encrypted with the public key.</p>

  <p>The private RSA key must be stored offline, click 'save key pair' to save the private and public keys to your computer. When viewing an order in the admin panel you will see an option to decrypt the private bitcoin key. The decryption happens client-side with JavaScript.</p>

  <p>When you choose to decrypt the private bitcoin key attached to an order you will be required to input the private RSA key generated here. If you lose the private RSA key <u>you wont be able to access your BTC</u> because you wont be able to decrypt the bitcoin keys.</p>
  
  <p><b>To ensure that your RSA keypair is working correctly, you should make a test transaction using the testnet or a small amount of BTC. Then decrypt the Bitcoin private key associated to the test transaction to make sure you can access your BTC when you need to cash out.</b></p>

</div>

<?php } elseif ($_GET['step'] == 3) {
  if (empty($sec_str)) {
    $new_config = array('sec_str' => rand_str());
    if (!update_config('sci', $new_config)) {
      die("ERROR: Failed updating settings, ensure sci/config.php has correct permissions.");
    }
  }
?>

<h1>Congratulations!</h1>
  
<div style="width:600px;margin-left:auto;margin-right:auto;">

  <p>You have nearly finished installing BitShop but there are still a few things you need to do. First of all login to the administration area with the username &quot;admin&quot; and the password you chose in the first step. You can access the admin area by visiting www.yoursite.com/admin.php and once logged in click on 'Settings' in the side menu. Then select MAIN SETTINGS and update all the settings to your requirements. Do the same for SCI SETTINGS, SEO SETTINGS, and GATEWAY SETTINGS.</p>
  
  <p>If you wish you can also create a cron job which points to the clean_trans.php script, it will delete old unconfirmed transactions every time the script runs. This can be done using a command such as <i>php -q /home/serverpath/cron/clean_trans.php</i></p>
  
  <p><b>You should now delete the install folder as it represents a security risk if left on your server after the installation is complete.</b><p>
  
  <p><a href="../admin.php">GO TO ADMIN AREA</a></p>
  
</div>

<?php } ?>
</body>
</html>