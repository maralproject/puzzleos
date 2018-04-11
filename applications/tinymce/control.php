<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.tinymce
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */

/* This file also handle image upload from TinyMCE */
if($appProp->appname == AppManager::$MainApp->appname){
	switch(__getURI(1)){
	case "uploadImage":
		$time = time();
		UserData::move_uploaded($_FILES["file"]["name"] . ".$time","file");
		die(json_encode(array('location' => __SITEURL . UserData::getPath($_FILES["file"]["name"] . ".$time"))));
		break;
	default:
		redirect();
	}
}
?>

<?php ob_start();?>
<script>
$.ajax({
	url: "<?php echo $appProp->uri?>/js/tinymce/tinymce.min.js",
	dataType: "script",
	cache:true,
	success: function(){	
		$.ajax({
			url: "<?php echo $appProp->uri?>/js/tinymce/jquery.tinymce.min.js",
			dataType: "script",
			cache:true,
			success: function(){
				$(document).trigger("tinyMCE_loaded");
				tinymce.suffix = ".min";
				tinyMCE.baseURL = "<?php echo $appProp->uri?>/js/tinymce";
				tinymce.init({
					codemirror: {
						indentOnInit: true,
						path: 'CodeMirror',
						config: {
							mode: 'application/x-httpd-php',
							lineNumbers: true
						},
						saveCursorPosition: false
					},
					height:300,
					images_upload_url: "<?php echo __SITEURL . "/tinymce/uploadImage"?>",
					images_upload_base_path: "<?php echo __SITEURL?>",
					images_upload_credentials: true,
					selector: 'textarea.tinymce',
					plugins:[
						'codemirror advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
						'searchreplace wordcount visualblocks visualchars fullscreen insertdatetime media nonbreaking',
						'save table contextmenu directionality emoticons template paste textcolor'
					],
					skin:"lightgrey",
					theme_url: ('<?php echo $appProp->uri?>/js/tinymce/themes/modern/theme.min.js'),
					skin_url:  ('<?php echo $appProp->uri?>/js/tinymce/skins/lightgray/'),
				});
			}
		});
	}
});
</script>
<?php Template::appendBody(FastCache::outJSMin(),true); ?>