<h3><?php echo LANG('LATEST_TRANS'); ?>:</h3>
<table class="table table-condensed table-striped table-bordered">
<tbody id="tl_body">
  <tr>
    <td><?php echo LANG('LOADING_FEED'); ?></td>
  </tr>
</tbody>
</table>

<script language="JavaScript" type="text/javascript">
function update_tranlist() {
	var i_num = 0;
	$.ajax({
		type: "GET",
		url: "feed.php?r="+Math.random(),
		dataType: "xml",
		success: function(xml) {	
			$('#tl_body').html('');
			$(xml).find('item').each(function(){
				if (i_num < <?php echo (empty($feed_num) ? 5 : $feed_num); ?>) {
					var i_title = $(this).find('title').text();
					var i_link = $(this).find('link').text();
					var i_desc = $(this).find('description').text();
					var i_pubd = $(this).find('pubDate').text();
					var rss_item = '<tr><td><a href="'+i_link+'">'+i_title+
					'</a></td><td>'+i_desc+'</td><td>'+i_pubd+'</td></tr>';
					$('#tl_body').append(rss_item);
					i_num++;
				}
			});
			if (i_num < 1) {
				$('#tl_body').append('<tr><td><?php echo LANG('RSS_FEED_EMPTY'); ?></td></tr>');
			}
		}
	});
}

var tl_tick = setInterval(update_tranlist, 10000);
update_tranlist();
</script>
