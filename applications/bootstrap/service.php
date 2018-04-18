<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.bootstrap
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */
 
/* This file is manually configured to load between bootstrap version and themes */

/* Load bootstrap-3.3.7 */
require_once("bootstrap-3.3.7.php");

/* Load bootstrap-4.0.0b2 */
//require_once("bootstrap-4.0.0b2.php");

/* ========== End of loader ========== */

/* Custom Bootstrap Function */
/* Add hashing link to a tab */

/**
 * Bootstrap-select provided by https://silviomoreto.github.io/bootstrap-select/ *
 */
//Template::addHeader('<script type="text/javascript" src="'.__SITEURL.'/applications/bootstrap/lib/bootstrap-select/js/bootstrap-select.min.js"></script>');
//Template::addHeader('<link rel="stylesheet" href="'.__SITEURL.'/applications/bootstrap/lib/bootstrap-select/css/bootstrap-select.min.css"/>');

/**
 * Bootstrap-tagsinput provided by https://bootstrap-tagsinput.github.io/bootstrap-tagsinput/examples/ *
 */
//Template::addHeader('<script type="text/javascript" src="'.__SITEURL.'/applications/bootstrap/lib/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>');
//Template::addHeader('<link rel="stylesheet" href="'.__SITEURL.'/applications/bootstrap/lib/bootstrap-tagsinput/bootstrap-tagsinput.css"/>');

/**
 * Typeahead provided by http://twitter.github.io/typeahead.js/examples/ *
 */
//Template::addHeader('<script type="text/javascript" src="'.__SITEURL.'/applications/bootstrap/lib/typeahead/typeahead.bundle.min.js"></script>');

/**
 * Jquery-datepicker provided by https://github.com/fengyuanchen/datepicker
 */
function __bs_enable_datepicker(){
	Template::addHeader('<script type="text/javascript" src="'.__SITEURL.'/applications/bootstrap/lib/jquery-datepicker/datepicker.min.js"></script>',true);
	Template::addHeader('<link rel="stylesheet" href="'.__SITEURL.'/applications/bootstrap/lib/jquery-datepicker/datepicker.min.css"/>');
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