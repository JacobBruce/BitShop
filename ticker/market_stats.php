<?php
require_once(dirname(__FILE__).'/../cron/tick_update.php');

$ticker_path = dirname(__FILE__).'/'.$price_json_file;

if (file_exists($ticker_path)) {
	$ticker_mod_time = filemtime($ticker_path);
	if (time() - $ticker_mod_time >= $price_update*60) update_ticker(false);
	$market_data = json_decode(file_get_contents($ticker_path), true);
} else {
	die("Could not locate $ticker_path!");
}
?>