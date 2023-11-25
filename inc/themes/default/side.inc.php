      <div class="span3">
        <div class="well" id="side_nav">
          <ul class="nav nav-list" style="padding:8px 0;">
		  
            <?php if (isset($admin_call) && admin_valid(false,true)) { ?>
            <li class="nav-header"><?php echo LANG('NAVIGATION'); ?></li>
			<li <?php if (empty($page) || $page == 'home') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=home'><?php echo LANG('HOME'); ?></a>
			</li>
            <li <?php if ($page == 'wallet') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=wallet'><?php echo LANG('WALLET'); ?></a>
			</li>
            <li <?php if ($page == 'orders') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=orders'><?php echo LANG('ORDERS'); ?></a>
			</li>
            <li <?php if ($page == 'items') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=items'><?php echo LANG('PRODUCTS'); ?></a>
			</li>
            <li <?php if ($page == 'editcats') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=editcats'><?php echo LANG('CATEGORIES'); ?></a>
			</li>
            <li <?php if ($page == 'accounts') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=accounts'><?php echo LANG('ACCOUNTS'); ?></a>
			</li>
            <li <?php if ($page == 'vouchers') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=vouchers'><?php echo LANG('VOUCHERS'); ?></a>
			</li>
            <li <?php if ($page == 'reviews') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=reviews'><?php echo LANG('REVIEWS'); ?></a>
			</li>
			<li <?php if ($page == 'themes') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=themes'><?php echo LANG('THEMES'); ?></a>
			</li>
            <li <?php if ($page == 'system') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=system'><?php echo LANG('SYSTEM'); ?></a>
			</li>
            <li <?php if ($page == 'settings') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=settings'><?php echo LANG('SETTINGS'); ?></a>
			</li>
            <li <?php if ($page == 'logout') { echo "class='active'"; } ?>>
			  <a href='admin.php?page=logout'><?php echo LANG('LOGOUT'); ?></a>
			</li>
			
            <?php
            } else {
			
			  $show_norm_menus = true;
              echo '<li class="nav-header">'.LANG('CATEGORIES').'</li>';
              if (empty($cat_id)) {
                $cat_id = (($page == 'cats') && !empty($_GET['id'])) ? $_GET['id'] : 0;
              }
			  
			  $root_id = $cat_id;
			  if (($cat_id > 0) && (($page == 'item') || ($page == 'cats'))) {
			    $curr_cat = get_cat(safe_sql_str($cat_id));
			    if (!empty($curr_cat) && ($curr_cat != 'N/A')) {
			      $curr_cat = mysqli_fetch_assoc($curr_cat);
				  if ($curr_cat['Parent'] > 0) {
				    $root_id = $curr_cat['Parent'];
				  }
			    }
			  }
			  
			  if (!empty($categories) && ($categories != 'N/A')) {
			  	mysqli_data_seek($categories, 0);
                while ($category = mysqli_fetch_assoc($categories)) {
				  if ($category['Active']) {
                    $c_active = ($root_id == $category['CatID']) ? ' class="active"' : '' ;
					$img_html = (empty($category['Image'])) ? '' : "<img src='".
					  $category['Image']."' alt='' width='20' height='20' />";
                    echo "<li$c_active><a href='index.php?page=cats&amp;id=".$category['CatID'].
					  "'>".$img_html.' '.safe_str($category['Name'])."</a></li>";
				  }
                }
			  }
			}
			?>
          </ul>
        </div>
		<?php
		if (isset($show_norm_menus)) {
		  if ($best_prods) { require(dirname(__FILE__).'/bestfiles.inc.php'); }
		  if ($top_prods) { require(dirname(__FILE__).'/topfiles.inc.php'); }
		}
		?>
      </div>
