<?php

/*
START CRITICAL/CONNECTION DB FUNCTIONS
*/

$mysql_regx_sow = '';
$mysql_regx_eow = '';

function graceful_crash($sql_str='N/A', $sql_obj=null) {

  if (isset($GLOBALS['conn'])) {
    $_SESSION['sql_errno'] = trim(trim($GLOBALS['conn']->errno, ' '), "\n");
    $_SESSION['sql_error'] = trim(trim($GLOBALS['conn']->error, ' '), "\n");
    $_SESSION['sql_query'] = trim(trim($sql_str, ' '), "\n");
  } else {
    $_SESSION['sql_errno'] = trim(trim($sql_obj->connect_errno, ' '), "\n");
    $_SESSION['sql_error'] = trim(trim($sql_obj->connect_error, ' '), "\n");
    $_SESSION['sql_query'] = $sql_str;
  }
  
  if (!isset($GLOBALS['hide_crash'])) {
    redirect($GLOBALS['base_url'].'error.php');
  }
}

function connect_to_db() {

  if (isset($GLOBALS['conn'])) {
    return $GLOBALS['conn'];

  } elseif (isset($GLOBALS['db_port'])) {
  
	global $db_port;
	global $db_server;
	global $db_database;
	global $db_username;
	global $db_password;
	global $mysql_regx_sow;
	global $mysql_regx_eow;
	
    $mysqli = new mysqli($db_server, $db_username, 
	          $db_password, $db_database, $db_port);
			  
    if ($mysqli->connect_errno) {
      graceful_crash('N/A', $mysqli);
    } else {
      if ($mysqli->server_version < 80004) {
        $mysql_regx_sow = '[[:<:]]';
		$mysql_regx_eow = '[[:>:]]';
      } else {
        $mysql_regx_sow = '\\b';
		$mysql_regx_eow = '\\b';
      }
  
	  return $mysqli;
	}
  } else {
    die('ERROR: no access to db config');
  }
}

function mysqli_result($res, $row, $field=0) { 
  $res->data_seek($row); 
  $datarow = $res->fetch_array(); 
  return $datarow[$field]; 
}

function mysqli_now() {
  global $conn;
  $sql_str = "SELECT UTC_TIMESTAMP()";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    if ($sql_result->num_rows <= 0) {
      return 'N/A';
    } else {
      return mysqli_result($sql_result, 0);
    }
  } else {
	graceful_crash($sql_str);
  }
}

/*
START GENERAL/GLOBAL DB FUNCTIONS
*/

function select_from($table, $sel_str, $rules='') {
  global $conn;
  $sql_str = "
  SELECT $sel_str
  FROM $table
  $rules";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    if ($sql_result->num_rows <= 0) {
      return 'N/A';
    } else {
      return $sql_result;
    }
  } else {
	graceful_crash($sql_str);
  }
}

function select_from_where($table, $sel_str, $where, $rules='') {
  global $conn;
  $sql_str = "
  SELECT $sel_str
  FROM $table
  WHERE $where
  $rules";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    if ($sql_result->num_rows <= 0) {
      return 'N/A';
    } else {
      return $sql_result;
    }
  } else {
	graceful_crash($sql_str);
  }
}

function insert_into($table, $ins_str, $values) {
  global $conn;
  $sql_str = "
  INSERT INTO $table (".$ins_str.")
  VALUES (".$values.")";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    return $conn->insert_id;
  } else {
	graceful_crash($sql_str);
  }
}

function insert_into_where($table, $ins_str, $values, $where) {
  global $conn;
  $sql_str = "
  INSERT INTO $table (".$ins_str.")
  VALUES (".$values.")
  WHERE $where";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    return $conn->insert_id;
  } else {
	graceful_crash($sql_str);
  }
}

function update_where($table, $set_str, $value, $where, $rules='') {
  global $conn;
  $sql_str = "
  UPDATE $table 
  SET $set_str = $value 
  WHERE $where
  $rules";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {		
    return $sql_result;
  } else {
	graceful_crash($sql_str);
  }
}

function multi_update($table, $set_str, $where, $rules='') {
  global $conn;
  $sql_str = "
  UPDATE $table 
  SET $set_str 
  WHERE $where
  $rules";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {		
    return $sql_result;
  } else {
	graceful_crash($sql_str);
  }
}

function delete_from($table, $where) {
  global $conn;
  $sql_str = "
  DELETE FROM $table 
  WHERE $where";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {		
    return $sql_result;
  } else {
	graceful_crash($sql_str);
  }
}

/*
START PRODUCT DB FUNCTIONS
*/

function get_file($item_id) {
  $item_id = (int) $item_id;
  return select_from_where('Products', '*', "FileID = $item_id", "LIMIT 1");
}

function active_files() {
  return select_from_where('Products', '*', "FileActive = 1");
}

function new_files($num_files) {
  $num_files = (int) $num_files;
  return select_from_where('Products', '*', "FileActive = 1", 
  "ORDER BY Created DESC LIMIT $num_files");
}

function best_file() {
  $result = select_from_where('Products', '*', "FileActive = 1", 
  "ORDER BY FileSales DESC LIMIT 1");
  if (!empty($result) && ($result !== 'N/A')) {
    return mysqli_fetch_assoc($result);
  } else {
    return 'N/A';
  }
}

function top_file() {
  $result = select_from_where('Products', '*', "FileActive = 1", 
  "ORDER BY (FileVoteSum / FileVoteNum) DESC LIMIT 1");
  if (!empty($result) && ($result !== 'N/A')) {
    return mysqli_fetch_assoc($result);
  } else {
    return 'N/A';
  }
}

function best_files($num_files) {
  $num_files = (int) $num_files;
  return select_from_where('Products', '*', "FileActive = 1", 
  "ORDER BY FileSales DESC LIMIT $num_files");
}

function top_files($num_files) {
  $num_files = (int) $num_files;
  return select_from_where('Products', '*', "FileActive = 1", 
  "ORDER BY (FileVoteSum / FileVoteNum) DESC LIMIT $num_files");
}

function count_file_cat($cat_id) {
  $cat_id = (int) $cat_id;
  global $mysql_regx_sow;
  global $mysql_regx_eow;
  return mysqli_result(select_from_where('Products', 'COUNT(*)', 
  "(FileCat REGEXP '$mysql_regx_sow$cat_id$mysql_regx_eow') AND FileActive = 1"), 0);
}

function count_file_cal($cat_ids) {
  global $mysql_regx_sow;
  global $mysql_regx_eow;
  $qry_arr = array();
  foreach ($cat_ids as $key => $value) {
    $cat_id = (int)$value;
    $qry_arr[] = "(FileCat REGEXP '$mysql_regx_sow$cat_id$mysql_regx_eow')";
  }
  return mysqli_result(select_from_where('Products', 'COUNT(*)', 
  "(".implode(" OR ", $qry_arr).") AND FileActive = 1"), 0);
}

function count_file_cats($cat_ids) {
  $cat_ids = explode(',', $cat_ids);
  $cat_count = 0;
  if (count($cat_ids) > 1) {
	$cat_count = count_file_cal($cat_ids);
  } else {
    $cat_count = count_file_cat($cat_ids);
  }
  return $cat_count;
}

function list_files($cat_id, $start, $sort_meth='FileID', 
         $sort_order='ASC', $item_num=12, $multi_cat=false) {
  global $mysql_regx_sow;
  global $mysql_regx_eow;
  if (($sort_order != 'ASC') && ($sort_order != 'DESC')) {
    $sort_order = 'ASC';
  }
  $start = (int) $start;
  if ($multi_cat == true) {
    $cat_ids = explode(',', $cat_id);
	$ids_exp = (int) $cat_ids[0];
    for ($i=1;;$i++) {
	  if (isset($cat_ids[$i])) {
        $id_val = (int) $cat_ids[$i];
	    $ids_exp .= "|$id_val";
	  } else {
	    break;
	  }
    }
    return select_from_where('Products', '*',
	"(FileCat REGEXP '$mysql_regx_sow($ids_exp)$mysql_regx_eow') AND FileActive = 1", 
	"ORDER BY $sort_meth $sort_order LIMIT $start, $item_num");
  } else {
    $cat_id = (int) $cat_id;
    return select_from_where('Products', '*',
	"(FileCat REGEXP '$mysql_regx_sow$cat_id$mysql_regx_eow') AND FileActive = 1", 
	"ORDER BY $sort_meth $sort_order LIMIT $start, $item_num");
  }
}

function create_file($type, $stock, $name, $desc, $cats, $tags, $code, $price, $method) {
  $type = safe_sql_str($type);
  $name = safe_sql_str($name);
  $desc = safe_sql_str($desc);
  $cats = safe_sql_str($cats);
  $tags = safe_sql_str($tags);
  $code = safe_sql_str($code);
  $method = safe_sql_str($method);
  $price = safe_decimal($price);
  $stock = (int) $stock;
  return insert_into('Products', 'FileType, FileStock, FileName, FileDesc, '.
                                 'FileCat, FileTags, FileCode, FilePrice, FileMethod',
					             "'$type', $stock, '$name', '$desc', '$cats', ".
								 "'$tags', '$code', $price, '$method'");
}

function edit_file($item_id, $set_str) {
  $item_id = (int) $item_id;
  return multi_update('Products', $set_str, "FileID = $item_id");
}

function list_all_files($start) {
  $start = (int) $start;
  return select_from('Products', '*', "ORDER BY FileID DESC LIMIT $start, 20");
}

function count_files() {
  return mysqli_result(select_from('Products', 'COUNT(*)'), 0);
}

function count_active_files() {
  return mysqli_result(select_from_where('Products', 'COUNT(*)', 'FileActive = 1'), 0);
}

function count_inactive_files() {
  return mysqli_result(select_from_where('Products', 'COUNT(*)', 'FileActive = 0'), 0);
}

function delete_file($item_id) {
  $item_id = (int) $item_id;
  return delete_from('Products', "FileID = $item_id");
}

function sort_files($sql_objects, $keywords, $start, $item_num) {
  $file_arr = array();
  $sort_fin = array();
  $sort_arr = array();
  $sort_arr[] = array();
  $i = 0;
  mysqli_data_seek($sql_objects, $start);
  while ($sql_arr = mysqli_fetch_array($sql_objects)) {
    $file_arr[$i] = $sql_arr;
    $filename = preg_replace('/[^a-z0-9]/i', '_', $sql_arr['FileName']);
    $filename = '_'.strtolower($filename).'_';
    $matches = 0;
    foreach ($keywords as $key => $value) {
	  if (strlen($value) < 3) {
	    $keyword = '_'.strtolower($value).'_';
	  } else {
	    $keyword = strtolower($value);
	  }
	  if (strpos($filename, $keyword) !== false) {
        $matches++;
	  }
    }
	$sort_arr[$matches][] = $i;
	$i++;
	if ($i >= $item_num) {
		break;
	}
  }
  $c = 0;
  for ($i=9;$i>=0;$i--) { 
	if (array_key_exists($i, $sort_arr)) {
	  foreach ($sort_arr[$i] as $key => $value) {
		$sort_fin[$c] = $file_arr[$value];
		$c++;
	  }
	}
  }
  return $sort_fin;
}

function search_files($search_phrase, $start, $item_num, $allitems=0) {
  $start = (int) $start;
  $search_phrase = safe_sql_str($search_phrase);
  $keywords = explode(' ', trim($search_phrase));
  $like_qrs = array();
  $result = array();
  foreach ($keywords as $key => $value) {
    $keyword = preg_replace('/[^a-z0-9]/i', '_', $value);
	$keywords[$key] = $keyword;
	$klen = strlen($keyword);
    if ($klen > 2) {
	  $like_qrs[$key] = "(FileName LIKE '%$keyword%')";	  
	} else {
	  $slen = $klen+1;
	  $like_qrs[$key] = "((LOCATE(' $keyword ', FileName) <> 0) OR ".
	    "(LOCATE(' $keyword', RIGHT(FileName, $slen)) <> 0))";
	}
  }
  $like_qrs = '('.implode(' OR ', $like_qrs).')';
  if ($like_qrs != '()') {
    $allquery = ($allitems == 0) ? '(FileActive = 1) AND ' : 
	'((FileActive = 1) OR (FileActive = 0)) AND ';
	$result['query'] = select_from_where('Products', '*', $allquery.$like_qrs);
	if (!empty($result['query']) && ($result['query'] !== 'N/A')) {
      $result['count'] = mysqli_num_rows($result['query']);
	} else { $result['count'] = 0; }
	if ($result['count'] > 1) {
	  $result['query'] = sort_files($result['query'], $keywords, $start, $item_num);
	} elseif ($result['count'] == 1) {
	  $result['query'] = array(mysqli_fetch_assoc($result['query']));
	} else { $result['query'] = 0; }
  } else {
    $result['count'] = 0;
	$result['query'] = 0;
  }
  return $result;
}

function search_tags($tag, $start, $item_num, $allitems=0) {
  global $mysql_regx_sow;
  global $mysql_regx_eow;
  $start = (int) $start;
  $tag = safe_sql_str($tag);
  $result = array();
  if (!empty($tag)) {
    $tag_regex = "(FileTags REGEXP '$mysql_regx_sow$tag$mysql_regx_eow')";
	$start_query = ($allitems == 0) ? '(FileActive = 1) AND ' : '';
    $result['query'] = select_from_where('Products', '*', $start_query.$tag_regex, "LIMIT 9");	
	if (!empty($result['query']) && ($result['query'] !== 'N/A')) {
      $result['count'] = mysqli_num_rows($result['query']);
	} else { $result['count'] = 0; }	
	if ($result['count'] > 1) {
	  $result['query'] = sort_files($result['query'], array($tag), $start, $item_num);
	} elseif ($result['count'] == 1) {
	  $result['query'] = array(mysqli_fetch_assoc($result['query']));
	} else { $result['query'] = 0; }
  } else {
    $result['count'] = 0;
	$result['query'] = 0;
  }
  return $result;
}

function similar_files($search_phrase, $item_id, $item_cats, $item_tags, $item_price) {
  global $mysql_regx_sow;
  global $mysql_regx_eow;
  $item_id = (int) $item_id;
  $item_price = safe_decimal($item_price);
  $item_cats = safe_sql_str($item_cats);
  $item_tags = safe_sql_str($item_tags);
  $search_phrase = safe_sql_str($search_phrase);
  $keywords = explode(' ', trim($search_phrase));
  $long_kws = array();
  $tag_arr = explode(',', $item_tags);
  $cat_ids = explode(',', $item_cats);
  $i = 0; $ids_exp = ''; $tag_exp = '';
  foreach ($keywords as $key => $value) {
    $keyword = preg_replace('/[^a-z]/i', '_', $value);
    if (strlen($keyword) > 2) {
	  $long_kws[$i] = $keyword;
	  $i++;
	}
  }
  $search_phrase = implode('%', $long_kws);
  if (!empty($search_phrase)) {
    if (count($cat_ids) > 1) {
      foreach ($cat_ids as $key => $value) {
	    $ids_exp .= ((int)$value).'|';
      }
      $ids_exp = trim($ids_exp, "|");
	  $cat_regex = "(FileCat REGEXP '$mysql_regx_sow($ids_exp)$mysql_regx_eow')";
	} else {
      $cat_regex = "(FileCat REGEXP '$mysql_regx_sow$item_cats$mysql_regx_eow')";
	}
    if (count($tag_arr) > 1) {
      foreach ($tag_arr as $key => $value) {
	    $tag_exp .= trim($value)."|";
      }
      $tag_exp = trim($tag_exp, "|");
	  $tag_regex = "OR (FileTags REGEXP '$mysql_regx_sow($tag_exp)$mysql_regx_eow')";
	} elseif (!empty($item_tags)) {
      $tag_regex = "OR (FileTags REGEXP '$mysql_regx_sow$item_tags$mysql_regx_eow')";
	} else {
      $tag_regex = '';
    }
    return select_from_where('Products', '*', "(FileActive = 1) AND ".
	"(FileID <> $item_id) AND ((FileName LIKE '%$search_phrase%') OR ".
	"($cat_regex AND ABS(FilePrice - $item_price) < 1) $tag_regex)", "LIMIT 12");
  } else {
    return 0;
  }
}

function apply_vote($item_id, $vote) {
  $item_id = (int) $item_id;
  $vote = (int) $vote;
  return multi_update('Products', "FileVoteSum = FileVoteSum + $vote, ".
  "FileVoteNum = FileVoteNum + 1", "FileID = $item_id AND FileActive = 1", "LIMIT 1");
}


/*
START REVIEWS DB FUNCTIONS
*/

function submit_review($item_id, $rating, $author, $review) {
  $item_id = (int) $item_id;
  $rating = (int) $rating;
  $author = safe_sql_str($author);
  $review = safe_sql_str($review);
  return insert_into('Reviews', 'ItemID, Rating, Author, Review', 
                     "$item_id, $rating, '$author', '$review'");
}

function get_reviews($item_id, $start=0) {
  $item_id = (int) $item_id;
  $start = (int) $start;
  return select_from('Reviews', '*', "WHERE ItemID = $item_id AND Confirmed = 1".
					 " ORDER BY RevID DESC LIMIT $start, 10");
}

function get_review($rev_id) {
  $rev_id = (int) $rev_id;
  return select_from('Reviews', '*', "WHERE RevID = $rev_id");
}

function list_reviews($start) {
  $start = (int) $start;
  return select_from('Reviews', '*', "ORDER BY RevID DESC LIMIT $start, 20");
}

function count_reviews() {
  return mysqli_result(select_from('Reviews', 'COUNT(*)'), 0);
}

function count_prod_reviews($item_id) {
  $item_id = (int) $item_id;
  return mysqli_result(select_from_where('Reviews', 'COUNT(*)', 
					   "ItemID = $item_id AND Confirmed = 1"), 0);
}

function update_review($rev_id, $new_rev) {
  $rev_id = (int) $rev_id;
  $new_rev = safe_sql_str($new_rev);
  return update_where('Reviews', "Review", "'$new_rev'", "RevID = $rev_id");
}

function toggle_review($rev_id, $new_state) {
  $rev_id = (int) $rev_id;
  $new_state = (int) $new_state;
  return update_where('Reviews', "Confirmed", $new_state, "RevID = $rev_id");
}

function delete_review($rev_id) {
  $rev_id = (int) $rev_id;
  return delete_from('Reviews', "RevID = $rev_id");
}

/*
START ORDER DB FUNCTIONS
*/

function save_order($account_id, $total, $shipping, $cart_str, $address, $note, $code, $key_data=false) {
  $account_id = (int) $account_id;
  $total = safe_decimal($total);
  $shipping = safe_decimal($shipping);
  $cart_str = safe_sql_str($cart_str);
  $address = safe_sql_str($address);
  $note = safe_sql_str($note);
  $code = safe_sql_str($code);
  if ($key_data !== false) {
    $key_data = safe_sql_str($key_data);
    return insert_into('Orders', 'AccountID, Total, Shipping, Cart, Address, Note, Code, KeyData',
           "$account_id, $total, $shipping, '$cart_str', '$address', '$note', '$code', '$key_data'");
  } else {
    return insert_into('Orders', 'AccountID, Total, Shipping, Cart, Address, Note, Code',
           "$account_id, $total, $shipping, '$cart_str', '$address', '$note', '$code'");
  }
}

function confirm_order($order_id, $code, $status, $amount, $currency) {
  $order_id = (int) $order_id;
  $status = safe_sql_str($status);
  $code = safe_sql_str($code);
  $amount = safe_decimal($amount);
  $currency = safe_sql_str($currency);
  return multi_update('Orders', "TranCode = '$code', Status = '$status', Amount = $amount,".
                      "Currency = '$currency', DatePaid = UTC_TIMESTAMP()", "OrderID = $order_id");
}

function set_order_status($order_id, $status) {
  $order_id = (int) $order_id;
  $status = safe_sql_str($status);
  return update_where('Orders', 'Status', "'$status'", "OrderID = $order_id");
}

function get_order_byid($order_id) {
  $order_id = (int) $order_id;
  return select_from_where('Orders', '*', "OrderID = $order_id LIMIT 1");
}

function get_order_bycode($tran_code) {
  $tran_code = safe_sql_str($tran_code);
  return select_from_where('Orders', '*', "Code = '$tran_code' LIMIT 1");
}

function edit_order($order_id, $set_str, $value) {
  $order_id = (int) $order_id;
  return update_where('Orders', $set_str, $value, "OrderID = $order_id");
}

function list_all_orders($start) {
  $start = (int) $start;
  return select_from('Orders', '*', "ORDER BY OrderID DESC LIMIT $start, 20");
}

function list_new_orders($limit) {
  $limit = (int) $limit;
  return select_from('Orders', '*', "ORDER BY OrderID DESC LIMIT $limit");
}

function list_unconfirmed_orders() {
  return select_from_where('Orders', '*', "Status != 'Confirmed'");
}

function update_key_data($order_id, $key_data) {
  $order_id = (int) $order_id;
  $key_data = safe_sql_str($key_data);
  return update_where('Orders', 'KeyData', "'$key_data'", "OrderID = $order_id");
}

function list_account_orders($acc_id, $start) {
  $acc_id = (int) $acc_id;
  $start = (int) $start;
  return select_from_where('Orders', '*', "AccountID = $acc_id", 
                           "ORDER BY OrderID DESC LIMIT $start, 20");
}

function count_account_orders($acc_id) {
  $acc_id = (int) $acc_id;
  return mysqli_result(select_from_where('Orders', 'COUNT(*)', "AccountID = $acc_id"), 0);
}

function export_conf_keys() {
  return select_from_where('Orders', 'OrderID, Amount, Shipping, KeyData', 
  "(INSTR(KeyData, 'empty:') = 0) AND (Status = 'Confirmed' OR Amount > 0)");
}

function list_conf_keys($start) {
  $start = (int) $start;
  return select_from_where('Orders', 'OrderID, Amount, Shipping, KeyData', 
  "(INSTR(KeyData, 'empty:') = 0) AND (Status = 'Confirmed' OR Amount > 0)", 
  "ORDER BY OrderID DESC LIMIT $start, 20");
}

function list_all_keys() {
  return select_from_where('Orders', 'OrderID, Amount, Shipping, KeyData', 
  "(INSTR(KeyData, 'empty:') = 0)", "ORDER BY OrderID DESC");
}

function list_key_data($start) {
  $start = (int) $start;
  return select_from_where('Orders', 'OrderID, Total, Shipping, KeyData', 
  "KeyData != 'empty:empty' AND Status = 'Confirmed'", 
  "ORDER BY OrderID DESC LIMIT $start, 20");
}

function remove_key_data($order_id) {
  $order_id = (int) $order_id;
  return update_where('Orders', 'KeyData', "'empty:empty'", "OrderID = $order_id");
}

function remove_all_keys() {
  return update_where('Orders', 'KeyData', "'empty:empty'",
  "(Status = 'Confirmed') AND (INSTR(KeyData, 'empty:') = 0)");
}

function count_conf_keys() {
  return mysqli_result(select_from_where('Orders', 'COUNT(*)', 
  "(INSTR(KeyData, 'empty:') = 0) AND (Status = 'Confirmed' OR Amount > 0)"), 0);
}

function count_orders() {
  return mysqli_result(select_from('Orders', 'COUNT(*)'), 0);
}

function delete_order($order_id) {
  $order_id = (int) $order_id;
  return delete_from('Orders', "OrderID = $order_id");
}

function delete_all_orders() {
  return delete_from('Orders', "OrderID > 0");
}

function delete_unc_orders() {
  return delete_from('Orders', "Status = 'Unconfirmed'");
}

function order_num($days=0, $confirmed=true) {
  $days = (int) $days;
  if ($confirmed) {
    $conf_str = "(Status = 'Confirmed')";
    $date_targ = 'DatePaid';
  } else {
    $conf_str = "(Status != 'Confirmed')";
    $date_targ = 'Created';
  }
  if ($days == 0) {
	  return mysqli_result(select_from_where('Orders', 'COUNT(*)', "$conf_str"), 0);
  } else {
	  return mysqli_result(select_from_where('Orders', 'COUNT(*)', 
	  "$conf_str AND $date_targ BETWEEN UTC_TIMESTAMP() - INTERVAL $days DAY AND UTC_TIMESTAMP()"), 0);
  }
}

function total_customers() {
	return mysqli_result(select_from_where('Orders', 'COUNT(DISTINCT(AccountID))', "Status = 'Confirmed'"), 0);
}

function total_income($days=0) {
  $days = (int) $days;
  $result = array();
  if ($days == 0) {
    $orders = select_from_where('Orders', 'Amount, Currency', "Status = 'Confirmed'");
  }  else {
    $orders = select_from_where('Orders', 'Amount, Currency', 
    "(Status = 'Confirmed') AND DatePaid BETWEEN UTC_TIMESTAMP() ".
    "- INTERVAL $days DAY AND UTC_TIMESTAMP()");
  }
  if ($orders != 'N/A' && mysqli_num_rows($orders) > 0) {
    while ($row = mysqli_fetch_assoc($orders)) {
      if (isset($result[$row['Currency']])) {
	    $result[$row['Currency']] = bcadd($result[$row['Currency']], $row['Amount']);
	  } else {
	    $result[$row['Currency']] = $row['Amount'];
	  }
    }
  } else {
    return 0;
  }
  return $result;
}

function monthly_income() {
  $result = array();
  $orders = select_from_where('Orders', 'Amount, Currency', 
  "(Status = 'Confirmed') AND MONTH(DatePaid) = MONTH(UTC_TIMESTAMP()) ".
  "AND YEAR(DatePaid) = YEAR(UTC_TIMESTAMP())");
  if ($orders != 'N/A' && mysqli_num_rows($orders) > 0) {
    while ($row = mysqli_fetch_assoc($orders)) {
      if (isset($result[$row['Currency']])) {
	    $result[$row['Currency']] = bcadd($result[$row['Currency']], $row['Amount']);
	  } else {
	    $result[$row['Currency']] = $row['Amount'];
	  }
    }
  } else {
    return 0;
  }
  return $result;
}

/*
START CODES DB FUNCTIONS
*/

function insert_code($code_data, $item_id, $acc_id, $ord_id) {
  $item_id = (int) $item_id;
  $acc_id = (int) $acc_id;
  $ord_id = (int) $ord_id;
  $code_data = safe_sql_str($code_data);
  return insert_into('Codes', 'CodeData, ItemID, OrderID, AccountID', 
                     "'$code_data', $item_id, $ord_id, $acc_id");
}

function update_code($set_str, $code_id) {
  $code_id = (int) $code_id;
  return multi_update('Codes', $set_str, "CodeID = $code_id");
}

function enable_code($code_id) {
  $code_id = (int) $code_id;
  return update_where('Codes', 'Available', '1', "CodeID = $code_id");
}

function enable_codes($item_id) {
  $item_id = (int) $item_id;
  return update_where('Codes', 'Available', '1', "ItemID = $item_id");
}

function disable_code($code_id) {
  $code_id = (int) $code_id;
  return update_where('Codes', 'Available', '0', "CodeID = $code_id");
}

function disable_codes($item_id) {
  $item_id = (int) $item_id;
  return update_where('Codes', 'Available', '0', "ItemID = $item_id");
}

function delete_code($code_id) {
  $code_id = (int) $code_id;
  return delete_from('Codes', "CodeID = $code_id");
}

function delete_codes($item_id) {
  $item_id = (int) $item_id;
  return delete_from('Codes', "ItemID = $item_id");
}

function count_codes($item_id) {
  $item_id = (int) $item_id;
  return mysqli_result(select_from_where('Codes', 'COUNT(*)', "ItemID = $item_id"), 0); 
}

function count_active_codes($item_id) {
  $item_id = (int) $item_id;
  return mysqli_result(select_from_where('Codes', 'COUNT(*)', 
  "ItemID = $item_id AND Available = 1"), 0); 
}

function get_code($code) {
  $code = safe_sql_str($code);
  return select_from_where('Codes', '*', "CodeData = '$code'", "LIMIT 1");
}

function get_codes($item_id, $start) {
  $item_id = (int) $item_id;
  $start = (int) $start;
  return select_from_where('Codes', '*', "ItemID = $item_id", "LIMIT $start, 20"); 
}

function get_order_codes($order_id) {
  $order_id = (int) $order_id;
  return select_from_where('Codes', '*', "OrderID = $order_id");
}

function claim_code($item_id, $acc_id, $ord_id) {
  $item_id = (int) $item_id;
  $acc_id = (int) $acc_id;
  $ord_id = (int) $ord_id;
  return multi_update('Codes', "AccountID = $acc_id, OrderID = $ord_id, Available = 0", 
                      "ItemID = $item_id AND Available = 1 LIMIT 1");
}

function list_account_codes($acc_id, $start) {
  $acc_id = (int) $acc_id;
  $start = (int) $start;
  return select_from_where('Codes', '*', "AccountID = $acc_id", 
                           "ORDER BY OrderID DESC LIMIT $start, 20");
}

function count_account_codes($acc_id) {
  $acc_id = (int) $acc_id;
  return mysqli_result(select_from_where('Codes', 'COUNT(*)', "AccountID = $acc_id"), 0);
}

/*
START CATEGORY DB FUNCTIONS
*/

function insert_cat($cat_pos, $parent, $name, $image, $active) {
  $cat_pos = (int) $cat_pos;
  $parent = (int) $parent;
  $active = (int) $active;
  return insert_into('Categories', 'CatPos, Parent, Name, Image, Active', 
  "$cat_pos, $parent, '$name', '$image', $active");
}

function get_cats() {
  return select_from('Categories', '*', "ORDER BY CatPos ASC"); 
}

function get_pcats($active_only=true) {
  $extra = $active_only ? ' AND Active = 1' : '';
  return select_from_where('Categories', '*', "Parent = 0$extra", "ORDER BY CatPos ASC"); 
}

function get_scats($parent, $active_only=true) {
  $parent = (int) $parent;
  $extra = $active_only ? ' AND Active = 1' : '';
  return select_from_where('Categories', '*', "Parent = $parent$extra", "ORDER BY CatPos ASC"); 
}

function get_cat($cat_id) {
  $cat_id = (int) $cat_id;
  return select_from_where('Categories', '*', "CatID = $cat_id", "LIMIT 1"); 
}

function edit_cat($cat_id, $set_str, $value) {
  $cat_id = (int) $cat_id;
  return update_where('Categories', $set_str, $value, "CatID = $cat_id");
}

function update_cat($cat_id, $parent, $name, $image) {
  $parent = (int) $parent;
  $cat_id = (int) $cat_id;
  return multi_update('Categories', "Parent = $parent, Name = '$name', ".
                      "Image = '$image'", "CatID = $cat_id");
}

function delete_cat($cat_id) {
  $cat_id = (int) $cat_id;
  return delete_from('Categories', "CatID = $cat_id");
}

function count_sub_cats($cat_id) {
  $cat_id = (int) $cat_id;
  return mysqli_result(select_from_where('Categories', 'COUNT(*)', "Parent = $cat_id"), 0); 
}

function swap_cat_pos($cat_id1, $cat_id2, $cat_pos1, $cat_pos2) {
  $cat_pos1 = (int) $cat_pos1;
  $cat_pos2 = (int) $cat_pos2;
  return edit_cat($cat_id1, 'CatPos', $cat_pos2) && edit_cat($cat_id2, 'CatPos', $cat_pos1);
}


/*
START ADDRESS FUNCTIONS
*/

function create_address($country, $state, $zipcode, $suburb, $address) {
  $country = safe_sql_str($country);
  $state = safe_sql_str($state);
  $zipcode = safe_sql_str($zipcode);
  $suburb = safe_sql_str($suburb);
  $address = safe_sql_str($address);
  return insert_into('Addresses', 'Country, State, Suburb, Zipcode, Address', 
  "'$country', '$state', '$suburb', '$zipcode', '$address'");
}

function get_address($addr_id) {
  $addr_id = (int) $addr_id;
  return select_from_where('Addresses', '*', "AddressID = $addr_id", "LIMIT 1");
}

function set_address($addr_id, $country, $state, $zipcode, $suburb, $address) {
  $addr_id = (int) $addr_id;
  $country = safe_sql_str($country);
  $state = safe_sql_str($state);
  $zipcode = safe_sql_str($zipcode);
  $suburb = safe_sql_str($suburb);
  $address = safe_sql_str($address);
  return multi_update('Addresses', "Country = '$country', State = '$state', Suburb = ".
  "'$suburb', Zipcode = '$zipcode', Address = '$address'", "AddressID = $addr_id", "LIMIT 1");
}

/*
START ACCOUNT DB FUNCTIONS
*/

function create_account($email, $pass_hash, $perm_group=0) {
  $email = safe_sql_str($email);
  $pass_hash = safe_sql_str($pass_hash);
  $perm_group = (int) $perm_group;
  return insert_into('Accounts', 'Email, PassHash, PermGroup', "'$email', '$pass_hash', $perm_group");
}

function get_account_byid($acc_id) {
  $acc_id = (int) $acc_id;
  return select_from_where('Accounts', '*', "AccountID = $acc_id", "LIMIT 1"); 
}

function get_account_byemail($email) {
  $email = safe_sql_str($email);
  return select_from_where('Accounts', '*', "Email = '$email'", "LIMIT 1"); 
}

function set_lock_count($acc_id, $fail_count) {
  $acc_id = (int) $acc_id;
  $fail_count = (int) $fail_count;
  return update_where('Accounts', 'FailCount', $fail_count, "AccountID = $acc_id", "LIMIT 1");
}

function set_last_time($acc_id, $utc_now, $last_ip) {
  $acc_id = (int) $acc_id;
  $last_ip = safe_sql_str($last_ip);
  return multi_update('Accounts', "LastIP = '$last_ip', LastTime = '$utc_now'", 
                      "AccountID = $acc_id", "LIMIT 1");
}

function set_account_pass($acc_id, $pass_hash) {
  $acc_id = (int) $acc_id;
  $pass_hash = safe_sql_str($pass_hash);
  return update_where('Accounts', 'PassHash', "'$pass_hash'", "AccountID = $acc_id", "LIMIT 1");
}

function set_account_email($acc_id, $email) {
  $acc_id = (int) $acc_id;
  $email = safe_sql_str($email);
  return update_where('Accounts', 'Email', "'$email'", "AccountID = $acc_id", "LIMIT 1");
}

function set_account_group($acc_id, $group) {
  $acc_id = (int) $acc_id;
  $group = (int) $group;
  return update_where('Accounts', 'PermGroup', $group, "AccountID = $acc_id", "LIMIT 1");
}

function set_account_settings($acc_id, $settings) {
  $acc_id = (int) $acc_id;
  $settings = safe_sql_str($settings);
  return update_where('Accounts', 'Settings', "'$settings'", "AccountID = $acc_id", "LIMIT 1");
}

function set_account_info($acc_id, $email, $name, $phone) {
  $acc_id = (int) $acc_id;
  $email = safe_sql_str($email);
  $name = safe_sql_str($name);
  $phone = safe_sql_str($phone);
  return multi_update('Accounts', "Email = '$email', RealName = '$name', ".
                     "Phone = '$phone'", "AccountID = $acc_id", "LIMIT 1");
}

function get_account_address($acc_id) {
  $acc_id = (int) $acc_id;
  return get_address(mysqli_result(select_from_where('Accounts', 
         'AddressID', "AccountID = $acc_id", "LIMIT 1"), 0));
}

function link_address($acc_id, $addr_id) {
  $acc_id = (int) $acc_id;
  $addr_id = (int) $addr_id;
  return update_where('Accounts', 'AddressID', "$addr_id", "AccountID = $acc_id", "LIMIT 1");
}

function remove_account($acc_id) {
  $acc_id = (int) $acc_id;
  return delete_from('Accounts', "AccountID = $acc_id");
}

function enable_account($acc_id) {
  $acc_id = (int) $acc_id;
  return update_where('Accounts', 'Enabled', '1', "AccountID = $acc_id", "LIMIT 1");
}

function disable_account($acc_id) {
  $acc_id = (int) $acc_id;
  return update_where('Accounts', 'Enabled', '0', "AccountID = $acc_id", "LIMIT 1");
}

function list_all_accounts($start) {
  $start = (int) $start;
  return select_from('Accounts', '*', "ORDER BY AccountID DESC LIMIT $start, 20");
}

function count_accounts() {
  return mysqli_result(select_from('Accounts', 'COUNT(*)'), 0);
}

function search_accounts($search_phrase, $start) {
  $start = (int) $start;
  $search_phrase = safe_sql_str($search_phrase);
  return select_from_where('Accounts', '*', 
						   "(RealName LIKE '%$search_phrase%') OR (Email LIKE '%$search_phrase%')",
						   "ORDER BY AccountID DESC LIMIT $start, 20");
}

/*
START VOUCHER FUNCTIONS
*/

function create_voucher($name, $code, $disc, $item_id, $target, $type, $credits) {
  $item_id = (int) $item_id;
  $target = (int) $target;
  $type = (int) $type;
  $credits = (int) $credits;
  $name = safe_sql_str($name);
  $code = safe_sql_str($code);
  $disc = safe_decimal($disc);
  return insert_into('Vouchers', 'ItemID, Name, CodeData, Discount, Target, UseType, Credits', 
				     "$item_id, '$name', '$code', $disc, $target, $type, $credits");
}

function edit_voucher($vouch_id, $name, $code, $disc, $item_id, $target, $type, $credits) {
  $vouch_id = (int) $vouch_id;
  $item_id = (int) $item_id;
  $target = (int) $target;
  $type = (int) $type;
  $credits = (int) $credits;
  $name = safe_sql_str($name);
  $code = safe_sql_str($code);
  $disc = safe_decimal($disc);
  return multi_update('Vouchers', "ItemID=$item_id, ".
  "Name='$name', CodeData='$code', Discount=$disc, Target=$target, ".
  "UseType=$type, Credits=$credits", "VouchID = $vouch_id", "LIMIT 1");
}

function get_voucher_byid($vouch_id) {
  $vouch_id = (int) $vouch_id;
  return select_from_where('Vouchers', '*', "VouchID = $vouch_id", "LIMIT 1");
}

function get_voucher_bycode($vouch_code) {
  $vouch_code = safe_sql_str($vouch_code);
  return select_from_where('Vouchers', '*', "CodeData = '$vouch_code'", "LIMIT 1");
}

function update_voucher($vouch_id) {
  $vouch_id = (int) $vouch_id;
  return update_where('Vouchers', 'Credits', 'Credits-1', "VouchID = $vouch_id", "LIMIT 1");
}

function enable_voucher($vouch_id) {
  $vouch_id = (int) $vouch_id;
  return update_where('Vouchers', 'Enabled', '1', "VouchID = $vouch_id", "LIMIT 1");
}

function disable_voucher($vouch_id) {
  $vouch_id = (int) $vouch_id;
  return multi_update('Vouchers', "Enabled = 0, Credits = 0", "VouchID = $vouch_id", "LIMIT 1");
}

function list_vouchers($start) {
  $start = (int) $start;
  return select_from('Vouchers', '*', "ORDER BY VouchID DESC LIMIT $start, 20");
}

function count_vouchers() {
  return mysqli_result(select_from('Vouchers', 'COUNT(*)'), 0);
}

function remove_vouchers() {
  return delete_from('Vouchers', "VouchID > 0");
}

function remove_voucher($vouch_id) {
  $vouch_id = (int) $vouch_id;
  return delete_from('Vouchers', "VouchID = $vouch_id");
}

/*
START BTC ADDRESS FUNCTIONS
*/

function create_btc_add($address) {
  $address = safe_sql_str($address);
  return insert_into('BtcAdds', 'Address', "'$address'");
}

function get_btc_address($add_id) {
  $add_id = (int) $add_id;
  return select_from_where('BtcAdds', '*', "AddID = $add_id", "LIMIT 1");
}

function get_add_byadd($address) {
  $address = safe_sql_str($address);
  return select_from_where('BtcAdds', '*', "Address = '$address'", "LIMIT 1");
}

function enabled_address() {
  return select_from_where('BtcAdds', '*', "Enabled = 1", "LIMIT 1");
}

function enable_address($add_id) {
  $add_id = (int) $add_id;
  return update_where('BtcAdds', 'Enabled', '1', "AddID = $add_id", "LIMIT 1");
}

function disable_address($add_id) {
  $add_id = (int) $add_id;
  return update_where('BtcAdds', 'Enabled', '0', "AddID = $add_id", "LIMIT 1");
}

function list_btc_adds($start) {
  $start = (int) $start;
  return select_from('BtcAdds', '*', "ORDER BY AddID DESC LIMIT $start, 20");
}

function count_btc_adds() {
  return mysqli_result(select_from('BtcAdds', 'COUNT(*)'), 0);
}

function remove_all_adds() {
  return delete_from('BtcAdds', "AddID > 0");
}

function remove_btc_add($add_id) {
  $add_id = (int) $add_id;
  return delete_from('BtcAdds', "AddID = $add_id");
}
?>
