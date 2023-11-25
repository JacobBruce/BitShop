<?php admin_valid(); ?>

<h1>Settings</h1>

<?php
if (isset($_POST['error_level'])) {
  $new_config = array();
  foreach ($_POST as $key => $value) {
	if ($key == 'set_form') { 
	  continue; 
	} elseif ($value === '******') {
	  $value = $GLOBALS[$key];
	}
	if ($key == 'install_dir') {
	  $value = trim($value, '/');
	  $value = empty($value) ? '/' : "/$value/";
	}
	$new_config[$key] = $value;
  }
  if (update_config('main', $new_config)) {
    echo "<p class='happy_txt'>Update was successful!</p>";
	echo "<p>Reloading page to apply changes...</p>";
	redirect('admin.php?page=settings&action=main');
  } else {
    echo "<p class='error_txt'>Update failed!</p>";
  }
} elseif (!empty($_POST['seller'])) {
  $new_config = array();
  foreach ($_POST as $key => $value) {
	if ($key == 'set_form') { 
	  continue; 
	} elseif ($value === '******') {
	  $value = $GLOBALS[$key];
	}
	$new_config[$key] = $value;
  }
  if (update_config('sci', $new_config)) {
    echo "<p class='happy_txt'>Update was successful!</p>";
	echo "<p>Reloading page to apply changes...</p>";
	redirect('admin.php?page=settings&action=sci');
  } else {
    echo "<p class='error_txt'>Update failed!</p>";
  }
} elseif (!empty($_POST['gateway'])) {
  $new_config = array();
  foreach ($_POST as $key => $value) {
	if ($key == 'gateway' || $key == 'set_form') { 
	  continue; 
	} elseif ($value === '******') {
	  $value = $GLOBALS[$key];
	}
	$new_config[$key] = $value;
  }
  $gate_dir = preg_replace("/[^a-z]/i", '', $_POST['gateway']);
  if (update_config('gate', $new_config, $gate_dir)) {
    echo "<p class='happy_txt'>Update was successful!</p>";
  } else {
    echo "<p class='error_txt'>Update failed!</p>";
  }
} elseif (!empty($_POST['keyw'])) {
  $new_titles = '';
  foreach ($page_titles as $key => $value) {
	if ($key == 'item') { continue; }
	$_POST[$key] = str_replace("'", "\'", $_POST[$key]);
	$new_titles .= "	'$key' => '".$_POST[$key]."',\n";
  }
  $new_titles = rtrim(rtrim($new_titles, "\n"), ',');
  $new_config = "<?php
// SEO keywords for head section
\$keywords = '".$_POST['keyw']."';

// full page titles
\$page_titles = array(\n$new_titles\n);
?>";

  if (file_put_contents('inc/seo.inc.php', $new_config)) {
    echo "<p class='happy_txt'>Update was successful!</p>";
	echo "<p>Reloading page to apply changes...</p>";
	redirect('admin.php?page=settings&action=seo');
  } else {
    echo "<p class='error_txt'>Update failed!</p>";
  }
  
} elseif (!empty($_POST['pub_key'])) {
  $new_config = array(
	'pub_rsa_key' => $_POST['pub_key']
  );
  if (update_config('sci', $new_config)) {
    echo "<p class='happy_txt'>Update was successful!</p>";
	echo "<p>Reloading page to apply changes...</p>";
	redirect('admin.php?page=settings&action=rsa');
  } else {
    echo "<p class='error_txt'>Failed to apply settings!</p>";
  }
} elseif (!empty($_POST['email_set'])) {
  if (file_put_contents('inc/email_body.inc', $_POST['email_set'])) {
    echo "<p class='happy_txt'>Update was successful!</p>";
  } else {
    echo "<p class='error_txt'>Update failed!</p>";
  }
}

if (!empty($_GET['action'])) {
  if ($_GET['action'] === 'rsa') {
?>

<p><b>RSA Key Generator</b></p>

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

<form name='set_form' id='set_form' method='post' action=''>
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
  <div>
    <label>Public Key:</label>
    <input type="text" name="pub_key" id="pub_key" value="" style="width:400px;" />
    <br />
    <label>Private Key:</label>
    <input type="text" id="priv_key" value="" style="width:400px;" />
  </div>
  <button type="button" class="btn" onClick="gen_keys();">generate new key pair</button> 
  <button type='button' class='btn' onClick="use_key();">use this public key</button>
  <button type='button' class='btn' onClick="save_keys();">save key pair</button>
</form>

<div style="width:500px;">

  <p><b>Information:</b> after you generate new public and private RSA keys, click 'use this public key' to save the new public RSA key to your config file. The public key is only used to encrypt data, only the private key can decrypt data encrypted with the public key.</p>

  <p>The private RSA key must be stored offline, click 'save key pair' to save the private and public keys to your computer. When viewing an order in the admin panel you will see an option to decrypt the private bitcoin key. The decryption happens client-side with JavaScript.</p>

  <p>When you choose to decrypt the private bitcoin key attached to an order you will be required to input the private RSA key generated here. If you lose the private RSA key <u>you wont be able to access your BTC</u> because you wont be able to decrypt the bitcoin keys.</p>

</div>

<p><a href='admin.php?page=settings' class='btn'>Go Back</a></p>

<?php
  } else {
    echo "<form name='set_form' method='post' action=''>";
	echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'" />';
    if ($_GET['action'] === 'main') {
      require_once('inc/admin/mainconfig.inc.php');
    } elseif($_GET['action'] === 'sci') {
      require_once('inc/admin/sciconfig.inc.php');
	} elseif($_GET['action'] === 'seo') {
	  require_once('inc/admin/seoconfig.inc.php');
	} elseif($_GET['action'] === 'gate') {
	  require_once('inc/admin/gateconfig.inc.php');
	} elseif($_GET['action'] === 'email') {
	  echo "<p><b>Edit the email template file:</b></p>";
      echo "<textarea name='email_set' id='email_set' style='width:550px;height:350px;'>".
      safe_str(file_get_contents('inc/email_body.inc'))."</textarea>";
	  echo "<p>SELLER_NAME will be replaced with your business name.<br />";
	  echo "TRAN_CODE will be replaced with the transaction code.<br />";
	  echo "TOTAL_PAID will be replaced with the total amount paid.<br />";
	  echo "DATE_PAID will be replaced with the date the payment was confirmed.<br />";
	  echo "DESTINATION will be replaced with the payment address/wallet.<br />";
	  echo "ITEM_LIST will be replaced with the list of items purchased.</p>";
	}
    echo "<br /><a href='admin.php?page=settings' class='btn'>Go Back</a> ".
	     "<button type='submit' class='btn'>Update</button></form>";
  }
} else {
?>

<p><b>Select an option:</b></p>

<p>
  <a href="admin.php?page=settings&action=main" title="Edit Main Settings">MAIN SETTINGS</a><br />
  <a href="admin.php?page=settings&action=sci" title="Edit SCI Settings">SCI SETTINGS</a><br />
  <a href="admin.php?page=settings&action=seo" title="Edit SEO Settings">SEO SETTINGS</a><br />
  <a href="admin.php?page=settings&action=gate" title="Edit Gateway Settings">GATEWAY SETTINGS</a><br />
  <a href="admin.php?page=settings&action=email" title="Edit Email Template">EMAIL TEMPLATE</a><br />
  <a href="admin.php?page=settings&action=rsa" title="Generate RSA Keys">RSA KEYGEN</a><br />
  <a href="admin.php?page=home" title="Main Menu">BACK</a>
</p>

<?php } ?>