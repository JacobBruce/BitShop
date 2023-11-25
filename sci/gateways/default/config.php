<?php
// use the default payment gateway?
$use_defgate = true;

// use testnet in the default gateway (for testing)
$use_testnet = false;

// use rpc to support altcoin in default gateway
$use_altrpc = false;

// use custom list of bitcoin addresses
$use_address_list = false;

// number of confirmations needed on payments
$confirm_num = 0;

// amount of time between each refresh (in seconds)
$refresh_time = 15;

// amount the progress bar increases with each refresh
$prog_inc = 5;

// number of confirmations needed for rpc altcoin
$alt_confirms = 1;

// refresh time for rpc altcoin (in seconds)
$alt_refresh = 10;

// amount the progress bar increases with rpc altcoin
$alt_pinc = 20;

// altcoin details
$altcoin_name = 'Litecoin';
$altcoin_code = 'LTC';

// altcoin RPC setup
$rpc_user = '';
$rpc_pass = '';

// ignore anything under this line
$rpc_client = 'altdaemon';
$alt_btc_api = 'https://min-api.cryptocompare.com/data/price?fsym='.strtoupper($altcoin_code).'&tsyms=BTC';
define('RPC_CLIENT', $rpc_client);
?>