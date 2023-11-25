<?php admin_valid(); 

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
	  case 'keys': 
	    require_once('./inc/admin/keys.inc.php');
		break;
	  case 'list':
	    require_once('./inc/admin/list.inc.php');
	    break;
	  default: echo LANG('INVALID_ACTION');
	}
} else {
?>

<h1>Wallet</h1>
<p><b>Select an option:</b></p><p>
<a href="admin.php?page=wallet&amp;action=keys" title="Automatically generated bitcoin key pairs">AUTO-GEN ADDRESSES</a><br />
<a href="admin.php?page=wallet&amp;action=list" title="Custom list of Bitcoin address">CUSTOM ADDRESS LIST</a><br />
<a href="admin.php?page=home" title="Main Menu">BACK</a></p>

<?php } ?>