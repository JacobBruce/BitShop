<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
	<div class="container">
	  <button type="button" class="btn btn-navbar" 
	  data-toggle="collapse" data-target=".nav-collapse">
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	  </button>
	  <a class="brand" href="#"><?php echo safe_str($site_name); ?></a>
	  <div class="nav-collapse collapse">
		<ul class="nav">
		  <li <?php if ($page == 'home') { echo 'class="active"'; } ?>>
			<a href="./" title="<?php echo LANG('HOME'); ?>">
			<i class="icon-home icon-white"></i></a>
		  </li>
		  <li <?php if ($page == 'cats') { echo 'class="active"'; } ?>>
			<a href="./?page=cats" title="<?php echo LANG('CATS_TITLE'); ?>">
			<i class="icon-list icon-white"></i></a>
		  </li>
		  <li <?php if ($page == 'search') { echo 'class="active"'; } ?>>
			<a href="./?page=search" title="<?php echo LANG('SEARCH_TITLE'); ?>">
			<i class="icon-search icon-white"></i></a>
		  </li>
		  <li <?php if ($page == 'settings') { echo 'class="active"'; } ?>>
			<a href="./?page=settings" title="<?php echo LANG('SETTINGS_TITLE'); ?>">
			<i class="icon-wrench icon-white"></i></a>
		  </li>
		  <li <?php if ($page == 'clients') { echo 'class="active"'; } ?>>
			<a href="./?page=clients" title="<?php echo LANG('CLIENT_TITLE'); ?>">
			<i class="icon-download-alt icon-white"></i></a>
		  </li>
		  <li <?php if ($page == 'account') { echo 'class="active"'; } ?>>
			<a href="./?page=account" title="<?php echo LANG('ACCOUNT_TITLE'); ?>">
			<i class="icon-user icon-white"></i></a>
		  </li>
		  <li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown">
			<i class="icon-info-sign icon-white"></i> <b class="caret"></b></a>
			<ul class="dropdown-menu">
              <li class="nav-header"><?php echo LANG('EXCHANGE_RATE'); ?></li>
			  <li>
			    <a href='./?page=settings'>1 BTC = <?php 
				safe_echo(bitsci::btc_num_format($exch_rate, 2).' '.$curr_code); ?></a>
			  </li>
			</ul>
		  </li>
		  <li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown">
			<i class="icon-question-sign icon-white"></i> <b class="caret"></b></a>
			<ul class="dropdown-menu">
			  <li class="nav-header"><?php echo LANG('EXTRA_LINKS'); ?></li>
		      <li <?php if ($page == 'terms') { echo 'class="active"'; } ?>>
			    <a href="./?page=terms"><?php echo LANG('TERMS'); ?></a>
		      </li>
		      <li <?php if ($page == 'about') { echo 'class="active"'; } ?>>
			    <a href="./?page=about"><?php echo LANG('ABOUT'); ?></a>
		      </li>
		      <li <?php if ($page == 'contact') { echo 'class="active"'; } ?>>
			    <a href="./?page=contact"><?php echo LANG('CONTACT'); ?></a>
		      </li>
			</ul>
		  </li>
		  <li <?php if ($page == 'cart') { echo 'class="active"'; } ?>>
			<a href="./?page=cart" title="<?php echo LANG('CART_TITLE'); ?>">
			<i class="icon-shopping-cart icon-white"></i>  
			(<?php echo count($_SESSION['cart']); ?>)</a>
		  </li>
		</ul>
		<ul class="nav pull-right">
		  <li>
            <form class="navbar-search" name="search_form" method="get" action="index.php">
			  <input type="hidden" name="page" value="search" />
              <input type="text" value="" name="q" maxlength="50" 
			  class="search-query" placeholder="search products" />
            </form>
		  </li>
		</ul>
	  </div><!--/.nav-collapse -->
	</div>
  </div>
</div>
	
<noscript>
  <div class="alert alert-error top_gap">
    <i class="icon-warning-sign"></i> <?php echo LANG('JS_NOTICE'); ?>
  </div>
</noscript>

<?php if (!empty($_COOKIE['ocode']) && ($page != 'buy')) { ?>
<div class="container">
  <div class="alert" id="order_alert">
    <button type='button' class='close' data-dismiss='alert' 
    onclick='clearOrderCookie();'>&times;</button>
    <i class="icon-exclamation-sign"></i> <?php echo LANG('INCOMPLETE_ORDER'); ?> 
    <a href="./sci/process-order.php?ocode=<?php echo safe_str($_COOKIE['ocode']); ?>"><?php 
    echo LANG('CLICK_HERE'); ?></a> <?php echo LANG('TO_COMPLETE'); ?>
  </div>
</div>
<script language="JavaScript">
function clearOrderCookie() {
  document.cookie = 'ocode=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/';
}
</script>
<?php } ?>
