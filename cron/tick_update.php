<?php
// call required includes
require_once(dirname(__FILE__).'/../sci/config.php');
require_once(dirname(__FILE__).'/../ticker/config.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');

function update_ticker($show_output) {
	global $ticker_api_url, $price_json_file, $curr_code;
	
	if ($show_output) echo "<p>Requesting data from $ticker_api_url ...</p>\n";
	$json_result = bitsci::curl_simple_post($ticker_api_url);

	if (!empty($json_result)) {

	  // decode json string
	  $json_array = json_decode($json_result, true);
	  
	  // check json array
	  if (!empty($json_array) && isset($json_array[$curr_code]) &&
	  isset($json_array[$curr_code]["last"]) && $json_array[$curr_code]["last"] > 0.0) {

		// open local file for writing
		$fp = fopen(dirname(__FILE__)."/../ticker/$price_json_file", "w");
	  
		// write to our opened file.
		if (fwrite($fp, $json_result) === FALSE) { 
		  if ($show_output) echo "<p>FAILURE! Could not write data to local file!</p>\n";
		} else {
		  if ($show_output) echo "<p>SUCCESS! $price_json_file has been updated!</p>\n";
		}
	  
		// release file handle
		fclose($fp);
	  
	  } else {
		if ($show_output) die("<p>FAILURE! API returned invalid data!</p>");
	  }
	} else {
	  if ($show_output) die("<p>FAILURE! API is not responding!</p>");
	}
}

if (!isset($index_call) && !isset($admin_call)) update_ticker(true);

?>