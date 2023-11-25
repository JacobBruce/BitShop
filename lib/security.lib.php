<?php
/**
* Security Functions
*
* @author Jacob Bruce
* www.bitfreak.info
*/

// Replace unsafe characters and return string
function safe_str($string) {
  return htmlentities($string, ENT_QUOTES, "UTF-8");
}

// Replace unsafe characters and echo string
function safe_echo($string) {
  echo safe_str($string);	
}

// Replace special characters and return string
function spec_str($string) {
  return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Replace special characters and echo string
function spec_echo($string) {
  echo spec_str($string);	
}

// Clean input string
function clean_string_input($string) {	
  if (ini_get('magic_quotes_gpc')) {
    $string = stripslashes($string);
  }
  return trim($string);
}

// Return a string safe for database queries
function safe_sql_str($str, $encode_ent=false, $conn_var='conn') {
  $str = @trim($str);
  if ($encode_ent) {
	$str = safe_str($str);
  }
  if (get_magic_quotes_gpc()) {
	$str = stripslashes($str);
  }
  if (isset($GLOBALS[$conn_var])) {
	$str = $GLOBALS[$conn_var]->real_escape_string($str);
  } elseif (!get_magic_quotes_gpc()) {
	$str = addslashes($str);
  }
  return $str;
}

// clean a decimal value
function safe_decimal($number) {
  if (is_numeric($number)) { return $number; }
  $result = preg_replace('/[^0-9\.\-]/', '', $number);
  if (is_numeric($result)) {
    return $result;
  } else {
    return 0;
  }
}

// Clean form input (support for multi-dim arrays)
function clean_form_input($input) {
  foreach ($input as $field_name => $value) {
    if (is_array($value)) {
	  foreach ($value as $mf_name => $mf_value) {
		$value[$mf_name] = clean_string_input($mf_value); 
	  }
	} else {
	  $input[$field_name] = clean_string_input($value);
    }
  }
  return $input;
}

// Function to validate against any email injection attempts
function is_injected($str) {
  $injections = array(
              '(\n+)',
              '(\r+)',
              '(\t+)',
              '(%0A+)',
              '(%0D+)',
              '(%08+)',
              '(%09+)'
              );
  $inject = join('|', $injections);
  $inject = "/$inject/i";
  if(preg_match($inject,$str)) {
    return true;
  } else {
    return false;
  }
}

// Check that email domain has valid DNS records
function check_email_dns($email_str) {
  if (strpos($email_str, '@') !== false) {
    $addr_parts = explode("@", $email_str);
	if (count($addr_parts) == 2) {
	  list($userName, $mailDomain) = $addr_parts;
      $uName = preg_replace('/[^(\x20-\x7F)]*/','', $userName);
      if (!empty($uName) && ($uName == $userName)) {
        if (!empty($mailDomain) && checkdnsrr($mailDomain, "MX")) { 
          return true;
        } else { 
          return false; 
        } 
      } else {
        return false;   
      }
	} else {
	  return false;
	}
  } else {
    return false;
  }
}

/***********************************************************************
* This function checks a field to be sure it is a string and (if 
* so) compares the length of the field to its maximum allowed value.
* This may be MAXLENGTH for INPUT or an arbitrary limit for a TEXTAREA.
*
* Argument(s): 
*    $input - input from a string field
*    $maxlength - maximum allowed value for that field
*
* Returns:
*    boolean: TRUE if $input is valid; FALSE if not.
***********************************************************************/

function validate_maxlength($input, $maxlength) {
  if(!is_string($input) || (strlen($input) > $maxlength)) { 
    return false;  // Validation failed.
  }
  return true;  // Field is valid.
}

/***********************************************************************
* Compares the input from a SELECT, checkbox or radio group to
* the valid options for that field. This function will handle both
* string (radio and single select) and array (checkbox groups, multi-
* select) fields.
*
* Argument(s): 
*    $selected - the cleaned string or array input from field
*    $control - array containing the valid options for this field
*
* Returns:
*    TRUE if input is valid; FALSE if not.
***********************************************************************/

function validate_selected($selected, $control, $check_keys=false) {
  
  if (!$check_keys) {	  
    // Is $selected an array? (multi-select or checkbox)
    if(is_array($selected)) {
      // If so, loop through the input array.
      foreach($selected as $current_selected) {
        // Test the input against the control array of allowed options.
        if(!in_array($current_selected, $control)) {
                return false;  // Invalid input.
        }
      }
    } else { //The data is a string (single SELECT or radio).

      // Test the input against the control array of allowed options.
      if(!in_array($selected, $control)) {
        return false;  // Invalid input.
      }
    }
    return true;  // Field is valid.
	
  } else {
	foreach ($selected as $item) {
	  if (!array_key_exists($item, $control)) {
	    return false;
	  }
	}
	return true;
  }
}

// check password strength (return true if strong enough)
function check_pass_strength($pwd, $min=6, $max=99) {
	
  $error = '';
  
  if (strlen($pwd) < $min) { // too short?
	$error .= LANG('PASS_TOO_SHORT')."<br />";
  }

  if (strlen($pwd) > $max) { // too long?
	$error .= LANG('PASS_TOO_SHORT')."<br />";
  }

  if (!preg_match("#[0-9]+#", $pwd)) { // any numbers?
	$error .= LANG('NEEDS_NUMBER')."<br />";
  }

  if (!preg_match("#[a-z]+#", $pwd)) { // any letters?
	$error .= LANG('NEEDS_LETTER')."<br />";
  }

  //if (!preg_match("#[A-Z]+#", $pwd)) { // any caps?
	//$error .= "Password must include at least one capital letter! <br />";
  //}

  if (empty($error)) {
	return true;
  } else {
	return $error;  
  }
}

// generate a random password of specified length
function generate_password($length = 8) {

  // define possible characters
  $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

  // we refer to the length of $possible a few times, so let's grab it now
  $maxlength = strlen($possible);
	
  // repeat generation process a maximum of 99 times
  for ($i=0; $i<99; $i++) {
	  
    // reset password and length counter
    $i = 0;
	$password = '';
    
    // add random characters to $password until $length is reached
    while ($i < $length) { 

      // pick a random character from the possible ones
      $char = substr($possible, mt_rand(0, $maxlength-1), 1);
	  
      // add it onto the end of whatever we've already got...
      $password .= $char;
      $i++;
    }
	
	// exit loop if password is strong enough
	if (check_pass_strength($password) === true) { break; }
  }
  
  return $password;
}

/*
* Hashes a password for a specified number of rounds
*
* @param string $pass
* @param int $rounds
* @param string $algo
*/
function pass_hash($pass, $rounds=6, $algo='sha256') {
  $result = hash($algo, hash($algo, $pass).$pass);
  for ($i=0;$i<$rounds;$i++) {
    $result = hash($algo, $result);
  }
  return $result;
}

/*
* Ensures an ip address is valid.
*
* @param string $ip
*/
function validate_ip($ip) {
  if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
    return false;
  } else {
    return true;
  }
}

/*
* returns secure hash of client IP
*/
function get_ip_hash($algo='sha256') {
  return hash($algo, $_SESSION['ip_address']);
}
?>