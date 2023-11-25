<?php
require_once(dirname(__FILE__).'/default/config.php');
require_once(dirname(__FILE__).'/coinbase/config.php');
require_once(dirname(__FILE__).'/gocoin/config.php');
require_once(dirname(__FILE__).'/paypal/config.php');
require_once(dirname(__FILE__).'/../config.php');

$gateways = array(
	'paypal' => array($enable_paypal, 'PayPal', $curr_code),
	'coinbase' => array($enable_coinbase, 'Coinbase', 'BTC, BCH, LTC, ETH, DAI, USDC'),
	'gocoin' => array($enable_gocoin, 'GoCoin', 'BTC, BCH, LTC, ETH, EOS, DASH')
);
?>