<script language="JavaScript">
tinyMCE.init({

	theme: "modern",
	content_css : 'css/bootstrap.min.css',

	plugins : "code,link,image,anchor,emoticons,searchreplace,"+
			   "textcolor,autolink,lists,spellchecker,pagebreak,"+
			   "layer,table,insertdatetime,preview,media,paste,"+
			   "directionality,nonbreaking,fullscreen,visualchars",
	
	valid_elements : "*[*]",
	forced_root_block: '',

	image_advtab: true,
    toolbar:false,

	width : "100%",
	height: 350,

	relative_urls : true,
	document_base_url : "<?php echo $site_url . '/'; ?>"
	
});

function toggle_editor() {
	tinyMCE.execCommand('mceToggleEditor', false, 'page_data');
}
</script>
