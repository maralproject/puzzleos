<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */
 
$_bs_public = IO::publish($appProp->path . "/lib");

/* Load bootstrap-3.3.7 */
require("bootstrap-3.3.7.php");

ob_start();?>
<script>
function Bootstrap_LinkTab(){
	// Change hash for page-reload
	$('.nav-tabs a').on('click', function (e) {
		e.preventDefault();
		window.location.hash = $(this).attr("href");
	});
	var hash=document.location.hash;
	if (hash){$('.nav-tabs a[href="'+hash+'"]').click();}
}
</script>
<?php Template::addHeader(Minifier::outJSMin(),true); ob_start();?>
<script>
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip(); 
	});
	$(document).on('shown.bs.modal',".modal", function(){
		$(this).addClass("modalOpened");
		window.history.pushState(null,"modal","#dlgclosed");
		window.history.pushState(null,"modal","#dlg");
	});
	$(document).on('hide.bs.modal',".modal", function(){
		$(this).removeClass("modalOpened");
		if(location.hash == "#dlg") history.back();
	});
	$(window).on("hashchange",function(){
		if(document.location.hash == "#dlgclosed")
			$(".modal.modalOpened").modal("hide");		
	});
</script>
<?php Template::appendBody(Minifier::outJSMin(),true);?>