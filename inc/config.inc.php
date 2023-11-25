<?php
// turn on/off error reporting (0 when live)
$error_level = E_ALL;

// show sql debug info on error (false when live)
$debug_sql = true;

// database connection settings
$db_port = 3306;
$db_server = 'localhost';
$db_database = '';
$db_username = '';
$db_password = '';

// install directory ('/' if installed at root)
$install_dir = '/';

// disable/enable website
$site_enabled = true;

// display message when disabled
$disable_msg = 'Website is currently undergoing maintenance.';

// website name
$site_name = 'BitShop v1';

// website slogan
$site_slogan = 'your custom slogan here';

// your contact email
$contact_email = 'your_email@mail.com';

// enable newest products?
$new_prods = true;

// enable custom featured products?
$feat_prods = true;

// enable best selling products?
$best_prods = true;

// enable top rated products?
$top_prods = true;

// enable captcha at checkout?
$show_captcha = true;

// anonymous reviews allowed?
$anon_reviews = true;

// force login for file downloads?
$login_for_files = true;

// max session time (hours)
$sess_time = 4;

// maximum number of failed login attempts
$login_fail_limit = 5;

// minutes to lock account if over fail limit
$login_lock_time = 60;

// download link expire time (days)
$link_expire_time = 2;

// days to lock file if hit limit exceeded
$file_lock_time = 7;

// max hits from diff IP's within 5 days
$file_hit_limit = 3;

// delete unconfirmed tx's older than (hours)
$tran_clean_time = 48;

// number of rounds used to hash passwords
$hash_rounds = 8;

// shop template/theme
$template = 'default';

// random string used for security
$rand_str = 'CHANGETHISSTRING';

// app language (front end only)
$locale = 'en-US';

// default time zone used by server
$time_zone = 'UTC';

// BitShop version
$bs_version = '1.1.9';

// SMTP email setup
$smtp_enable = false;
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_auth = true;
$smtp_meth = 'tls';
$smtp_user = 'email@gmail.com';
$smtp_pass = 'pass';
$smtp_debug = false;

/* IGNORE ANYTHING UNDER THIS LINE */
$inter_prot = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
$serv_name = empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['SERVER_NAME'];
$serv_name = ($_SERVER['SERVER_PORT'] == 80) ? $serv_name : $serv_name.':'.$_SERVER['SERVER_PORT'];
$http_host = empty($_SERVER['HTTP_HOST']) ? $serv_name : $_SERVER['HTTP_HOST'];
$base_url = $inter_prot.$http_host.$install_dir;
bcscale(8);
ini_set('display_errors', 1);
error_reporting($error_level);
?>