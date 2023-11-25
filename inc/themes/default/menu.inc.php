<div class="row-fluid">
  <div class="span12">
    <div class="navbar">
      <div class="navbar-inner">
        <div class="container" style="width:auto;">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <div class="nav-collapse">
            <ul class="nav">
			  <li <?php if ($page == 'home') { echo 'class="active"'; } ?>>
				<a href="./" title="<?php echo LANG('HOME'); ?>">
				<i class="icon-home"></i></a>
			  </li>
			  <li <?php if ($page == 'cats') { echo 'class="active"'; } ?>>
				<a href="./?page=cats" title="<?php echo LANG('CATS_TITLE'); ?>">
				<i class="icon-list"></i></a>
			  </li>
			  <li <?php if ($page == 'search') { echo 'class="active"'; } ?>>
				<a href="./?page=search" title="<?php echo LANG('SEARCH_TITLE'); ?>">
				<i class="icon-search"></i></a>
			  </li>
			  <li <?php if ($page == 'settings') { echo 'class="active"'; } ?>>
				<a href="./?page=settings" title="<?php echo LANG('SETTINGS_TITLE'); ?>">
				<i class="icon-wrench"></i></a>
			  </li>
			  <li <?php if ($page == 'clients') { echo 'class="active"'; } ?>>
				<a href="./?page=clients" title="<?php echo LANG('CLIENT_TITLE'); ?>">
				<i class="icon-download-alt"></i></a>
			  </li>
			  <li <?php if ($page == 'account') { echo 'class="active"'; } ?>>
				<a href="./?page=account" title="<?php echo LANG('ACCOUNT_TITLE'); ?>">
				<i class="icon-user"></i></a>
			  </li>
			  <li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown">
				<i class="icon-question-sign"></i> <b class="caret"></b></a>
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
				<i class="icon-shopping-cart"></i>  
				(<?php echo count($_SESSION['cart']); ?>)</a>
			  </li>
            </ul>
            <ul class="nav pull-right">
              <li>
                <form class="navbar-search" name="search_form" method="get" action="index.php">
				  <input type="hidden" name="page" value="search" />
                  <input type="text" value="" name="q" maxlength="50" class="search-query" 
				  placeholder="<?php echo LANG('SEARCH_PRODS'); ?>" />
                </form>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>