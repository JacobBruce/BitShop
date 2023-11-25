<?php
if (isset($index_call) || isset($admin_call)) {
  require_once(dirname(__FILE__).'/meta.inc.php');
?>
<body>
  <?php require_once(dirname(__FILE__).'/menu.inc.php'); ?>
  <div class="container">
    <?php require_once(dirname(__FILE__).'/body.inc.php'); ?>
    <?php require_once(dirname(__FILE__).'/foot.inc.php'); ?>
  </div>
</body>
</html>
<?php
} else {
  echo '<p>'.LANG('INVALID_ACCESS').'</p>';
}
?>