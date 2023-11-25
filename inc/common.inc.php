<?php
// call required includes
require_once('ticker/market_stats.php');
require_once('lib/common.lib.php');
require_once('inc/config.inc.php');
require_once('sci/config.php');
require_once('inc/session.inc.php');
require_once("inc/langs/$locale.inc.php");
require_once('inc/seo.inc.php');

// set timezone (can be from session)
date_default_timezone_set($time_zone);

// connect to database
$conn = connect_to_db();

// get the account permissions
if (login_state() === 'valid') {
  $perms = $group_perms[$_SESSION['user_data']['PermGroup']];
}

// get current page
if (empty($_GET['page'])) {
  $page = 'home';
} else {
  $page = urlencode($_GET['page']);
}

// show the login page if necessary
if ($page == 'account' && !isset($perms)) {
  $page = 'login';
}

// clean any form input
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $_POST = clean_form_input($_POST);
  // check if in admin area or account area
  if (isset($admin_call) || $page === 'account') {
    // make sure CSRF token was passed to us
    if (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token']) || 
	$_SESSION['csrf_token'] !== $_POST['csrf_token']) {
	  die(LANG('INVALID_ACCESS'));
	}
  }
}

// get user-specified price data
if (isset($market_data[$curr_code])) {
  $cust_stats = $market_data[$curr_code];
  $exch_rate = $cust_stats;
} else {
  $cust_stats = null;
}

// get default price data
if (isset($market_data[$curr_orig])) {
  $orig_stats = $market_data[$curr_orig];
  $exch_orig = $orig_stats;
} else {
  $orig_stats = null;
}

// get exchange rates for BTC
$exch_rate = ($cust_stats===null) ? 0.0 : ($cust_stats['15m'] + $cust_stats['last']) / 2.0;
$exch_orig = ($orig_stats===null) ? 0.0 : ($orig_stats['15m'] + $orig_stats['last']) / 2.0;

// hide fiat currency symbol if using different currency
$curr_symbol = ($curr_code === $curr_orig) ? $curr_symbol : '';

// get categories from db
$categories = get_pcats();

// get unit symbol for BTC prices
$dec_unit = $unit_symbols[$dec_shift];

// create breadcrumbs for cats and item pages
if ($page == 'item') {
  if (!empty($_GET['id']) & is_numeric($_GET['id'])) {
    $file = get_file(safe_sql_str($_GET['id']));
    if (!empty($file) && ($file != 'N/A')) {
      $file = mysqli_fetch_assoc($file);
	  $file_id = $file['FileID'];
	  $cat_ids = explode(',', $file['FileCat']);
	  $cat_id = isset($_GET['cat']) ? (int)$_GET['cat'] : $cat_ids[0];
      $sel_cat = mysqli_fetch_assoc(get_cat($cat_id));
	  if ($sel_cat['Parent'] > 0) {
	    $par_cat = mysqli_fetch_assoc(get_cat($sel_cat['Parent']));
	    $cat_crumb = "<span class='divider'>/</span></li><li><a href='".
	    "?page=cats&amp;id=".$par_cat['CatID']."'>".$par_cat['Name']."</a> ";
	  } else { $cat_crumb = ''; }
	  $cat_crumb .= "<span class='divider'>/</span></li><li>".
	  "<a href='?page=cats&amp;id=$cat_id'>".$sel_cat['Name']."</a> ";
      $breadcrumb = "<ul class='breadcrumb'><li><a href='./'>".LANG('HOME')."</a> ".
      "<span class='divider'>/</span></li><li><a href='?page=cats'>".LANG('CATEGORIES').
	  "</a> $cat_crumb<span class='divider'>/</span></li><li class='active'>".
	  $file['FileName']."</li></ul>";
	}
  }
} elseif ($page == 'cats') {
  $c_active = (empty($_GET['id'])) ? " class='active'" : ''; 
  $breadcrumb = "<ul class='breadcrumb'><li><a href='./'>".LANG('HOME').
  "</a> <span class='divider'>/</span></li><li$c_active>";
  if ($c_active == '') {
    $breadcrumb .= "<a href='?page=cats'>".LANG('CATEGORIES').
	"</a> <span class='divider'>/</span></li>";
    if (isset($_GET['id'])) {
	  $sel_cat = get_cat(safe_sql_str($_GET['id']));
	  if (empty($sel_cat) || ($sel_cat == 'N/A')) {
        $breadcrumb .= "<li class='active'>".LANG('UNKNOWN')."</li></ul>";
	  } else {
	    $sel_cat = mysqli_fetch_assoc($sel_cat);
	    if ($sel_cat['Parent'] > 0) {
	      $par_cat = mysqli_fetch_assoc(get_cat($sel_cat['Parent']));
	      $breadcrumb .= "<li><a href='?page=cats&amp;id=".$par_cat['CatID'].
		  "'>".$par_cat['Name']."</a> <li><span class='divider'>/</span>";
	    }
        $breadcrumb .= "<li class='active'>".$sel_cat['Name']."</li></ul>";
	  }
	} else {
      $breadcrumb .= "<li class='active'>".LANG('NOT_FOUND')."</li></ul>";
	}
  } else {
    $breadcrumb .= LANG('CATEGORIES')." </li></ul>";
  }
}

// add and remove items from cart
if ($page === 'cart') {
  if (isset($_GET['empty'])) {
    $_SESSION['cart'] = array();
	$_SESSION['vouchers'] = array();
  }
  if (!empty($_GET['add'])) {
    $item_id = (int)$_GET['add'];
	$item_obj = get_file($item_id);
	if (!empty($item_obj) && $item_obj !== 'N/A') {
      $item_arr = mysqli_fetch_assoc($item_obj);
      $_SESSION['cart'][$item_id] = array();
      $_SESSION['cart'][$item_id]['id'] = $item_arr['FileID'];
      $_SESSION['cart'][$item_id]['cats'] = $item_arr['FileCat'];
      $_SESSION['cart'][$item_id]['name'] = $item_arr['FileName'];
      $_SESSION['cart'][$item_id]['price'] = $item_arr['FilePrice'];
      $_SESSION['cart'][$item_id]['type'] = $item_arr['FileMethod'];
	  $_SESSION['cart'][$item_id]['quant'] = 1;
	  $ship_cost = '0.0'; $ship_curr = $curr_orig;
	  if (isset($_GET['qnty'])) {
	    $item_qnt = (int)$_GET['qnty'];
		if ($item_qnt > 0) {
		  $_SESSION['cart'][$item_id]['quant'] = $item_qnt;
		}
	  }
	  if ($item_arr['FileMethod'] === 'ship') {
		$ship_info = get_ship_info($item_arr, $ship_curr);
		$ship_cost = $ship_info['cost'];
		$ship_curr = $ship_info['curr'];
	  }
	  $_SESSION['cart'][$item_id]['ship_cost'] = $ship_cost;
	  $_SESSION['cart'][$item_id]['ship_curr'] = $ship_curr;
	} else {
	  $errors['cart'] = LANG('INVALID_ITEM_ID');
	}
  } elseif (!empty($_GET['remove'])) {
    if (isset($_SESSION['cart'][$_GET['remove']])) {
      unset($_SESSION['cart'][$_GET['remove']]);
    }
  }
  // handle voucher stuff
  if (!empty($_POST['voucher'])) {
    if (count($_SESSION['vouchers']) < $voucher_limit) {
      $voucher = get_voucher_bycode($_POST['voucher']);
	  if (!empty($voucher) && $voucher !== 'N/A') {
	    $voucher = mysqli_fetch_assoc($voucher);
	    if (!dupe_voucher($voucher) && check_voucher($voucher)) {
		  if ($voucher['ItemID'] == 0) {
		    $_SESSION['vouchers'][] = array(
		    'id'   =>   $voucher['VouchID'], 'name' => $voucher['Name'], 
		    'item_id' => $voucher['ItemID'], 'targ' => $voucher['Target'], 
		    'value' => $voucher['Discount'], 'type' => $voucher['UseType']);
		  } else {
	        array_unshift($_SESSION['vouchers'], array(
		    'id'   =>   $voucher['VouchID'], 'name' => $voucher['Name'], 
		    'item_id' => $voucher['ItemID'], 'targ' => $voucher['Target'], 
		    'value' => $voucher['Discount'], 'type' => $voucher['UseType']));
		  }
	    } else {
	      $errors['cart'] = LANG('INVALID_VOUCHER');
	    }
	  } else {
	    $errors['cart'] = LANG('INVALID_VOUCHER');
	  }
	} else {
	  $errors['cart'] = LANG('VOUCHER_LIMIT');
	}
  } elseif (!empty($_GET['remvcc'])) {
    unset_voucher($_GET['remvcc']);
  }
}

// set product page title dynamically
$page_titles['item'] = (empty($file['FileName'])) ? LANG('NOT_FOUND') : $file['FileName'];

// get page title
if (isset($admin_call)) {
  $page_title = $page_titles['admin'];
} elseif (array_key_exists($page, $page_titles)) {
  $page_title = $page_titles[$page];
} else {
  if (file_exists('inc/pages/'.$page.'.inc.php')) {
    $page_title = $page_titles['untitled'];
  } else {
    $page_title = $page_titles['notfound'];
  }
}
?>