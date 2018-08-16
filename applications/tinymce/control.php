<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.tinymce
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.2
 */

/* This file also handle image upload from TinyMCE */
if($appProp->appname == AppManager::$MainApp->appname){
	switch(__getURI(1)){
	case "uploadImage":
		$hash = substr(md5_file($_FILES["file"]["tmp_name"]),0,5);
		UserData::move_uploaded($_FILES["file"]["name"] . ".$hash","file");
		die(json_encode(['location' => UserData::getURL($_FILES["file"]["name"] . ".$hash")]));
		break;
	default:
		redirect();
	}
}

$js = IO::publish($appProp->path."/js/tinymce");

ob_start();?>
<script>
$.ajax({
	url: "<?php echo $js?>/tinymce.min.js",
	dataType: "script",
	cache:true,
	success: function(){
		$(document).trigger("tinyMCE_loaded");
		tinymce.suffix = ".min";
		tinyMCE.baseURL = "<?php echo $js?>";
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
			theme_url: ('<?php echo $js?>/themes/modern/theme.min.js'),
			skin_url:  ('<?php echo $js?>/skins/lightgray/'),
		});
	}
});
</script>
<?php Template::appendBody(FastCache::outJSMin(),true); ?>