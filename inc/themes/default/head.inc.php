<noscript>
  <div class="alert alert-error top_gap">
    <i class="icon-warning-sign"></i> <?php echo LANG('JS_NOTICE'); ?>
  </div>
</noscript>

<?php if (!empty($_COOKIE['ocode']) && ($page != 'buy')) { ?>
<div class="alert top_gap" id="order_alert">
  <button type='button' class='close' data-dismiss='alert' onclick='clearOrderCookie();'>&times;</button>
  <i class="icon-exclamation-sign"></i> <?php echo LANG('INCOMPLETE_ORDER'); ?> 
  <a href="./sci/process-order.php?ocode=<?php echo safe_str($_COOKIE['ocode']); ?>"><?php 
  echo LANG('CLICK_HERE'); ?></a> <?php echo LANG('TO_COMPLETE'); ?>
</div>
<script language="JavaScript">
function clearOrderCookie() {
  document.cookie = 'ocode=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/';
}
</script>
<?php } ?>

<header>
  <div class="row-fluid">
    <div class="span12">
      <div id="info_con">
        <?php echo LANG('EXCHANGE_RATE'); ?>:<br />
        1 BTC = <?php safe_echo(bitsci::btc_num_format($exch_rate, 2).' '.$curr_code); ?>
      </div>
      <div id="name_con">
        <h2 id="name_txt"><?php safe_echo($site_name); ?></h2>
		<span id="slogan_txt"><?php safe_echo($site_slogan); ?></span>   
      </div>
    </div>
  </div>
</header>