<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<meta name="description" content="<?php safe_echo($site_slogan); ?>" />
	<meta name="keywords" content="<?php safe_echo($keywords); ?>" />

	<title><?php echo safe_str($site_name).' - '.$page_title; ?></title>
	
	<style>body { padding-top:60px; padding-bottom:40px; }</style>
	<link rel="stylesheet" href="css/normalize.css" />
	<link rel="stylesheet" href="css/boilerplate.css" />
	<link rel="stylesheet" href="css/bootstrap.min.css" />
	<link rel="stylesheet" href="css/bootstrap-responsive.min.css" />
	<link rel="stylesheet" href="css/main.css" />
	
	<!--[if lt IE 9]>
	<script src="scripts/html5shiv.min.js"></script>
	<![endif]-->
	<?php require_once('scripts/common.js.php'); ?>
</head>