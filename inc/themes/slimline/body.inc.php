  <div id="bodycon">
    <div class="row-fluid">
      <?php require_once(dirname(__FILE__).'/side.inc.php'); ?>
      <div class="span9">
        <?php
        if (!empty($admin_call)) {
          echo "<div id='admincon'>";
          require_once('inc/admincon.inc.php');
          echo "</div>";
        } else {
          require_once('inc/pagecon.inc.php');	  
		  if ($page == 'home') {
			if ($new_prods) {
		      require(dirname(__FILE__).'/newfiles.inc.php');
			}
			if ($feat_prods) {
		      require(dirname(__FILE__).'/featfiles.inc.php');
			}
			if ($rss_feed) {
		      require(dirname(__FILE__).'/tranlist.inc.php');
			}
		  }
        }
        ?>
      </div>
    </div>
  </div>