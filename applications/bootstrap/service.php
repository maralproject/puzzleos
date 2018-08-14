<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.bootstrap
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.0
 */
 
$_bs_public = IO::publish($appProp->path . "/lib");

/* Load bootstrap-3.3.7 */
require_once("bootstrap-3.3.7.php");

/**
 * Jquery-datepicker provided by https://github.com/fengyuanchen/datepicker
 */
function __bs_enable_datepicker(){
	$_bs_public = IO::publish(__DIR__ . "/lib");
	Template::addHeader('<script type="text/javascript" src="'.$_bs_public.'/jquery-datepicker/datepicker.min.js"></script>',true);
	Template::addHeader('<link rel="stylesheet" href="'.$_bs_public.'/jquery-datepicker/datepicker.min.css"/>');
}

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
<?php Template::addHeader(FastCache::outJSMin(),true); ob_start();?>
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
<?php Template::appendBody(FastCache::outJSMin(),true);?>