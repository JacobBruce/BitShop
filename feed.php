<?php
require_once('./inc/config.inc.php');
require_once('./lib/common.lib.php');
date_default_timezone_set($time_zone);
header('Content-Type: application/rss+xml; charset=UTF-8');
echo "<?xml version=\"1.0\"?>\n";
?>
<rss version="2.0">
  <channel>
    <title><?php spec_echo($site_name); ?></title>
    <link><?php echo $base_url; ?></link>
    <description><?php spec_echo($site_slogan); ?></description>
	<language><?php echo strtolower($locale); ?></language>
    <?php require_once('./inc/rss.inc'); ?>
  </channel>
</rss>
