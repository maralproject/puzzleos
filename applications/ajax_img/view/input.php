<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

$l = new Language;

?>

<?php if($GLOBALS["ImageUploader"]["scLoaded"] != 1):?>
	<?php $GLOBALS["ImageUploader"]["scLoaded"] = 1;?>
	<script type="text/javascript" src="<?php echo IO::publish(my_dir("/js/jquery.form.min.js"))?>"></script>
	<script>
	var L_NO_FILE = "<?php $l->dump("NO_FILE")?>";
	var L_TOO_BIG = "<?php $l->dump("TOO_BIG")?>";
	var L_UPGRADE = "<?php $l->dump("UPGRADE")?>";
	</script>
	<?php require_once( __ROOTDIR . "/applications/ajax_img/js/form_action.js.php")?>
<?php endif;?>

<?php ob_start();?>
<style>
.btn-file {
	position: relative;
	overflow: hidden;
}
.btn-file input[type=file] {
	position: absolute;
	top: 0;
	right: 0;
	min-width: 100%;
	min-height: 100%;
	font-size: 100px;
	text-align: right;
	filter: alpha(opacity=0);
	opacity: 0;
	outline: none;
	background: white;
	cursor: inherit;
	display: block;
}
</style>
<?php echo FastCache::outCSSMin(); ?>

<form action="<?php echo __SITEURL?>/upload_img_ajax/upload" msz="<?php echo php_max_upload_size()?>" style="height:33px;" method="post" enctype="multipart/form-data" class="img_ajax">
	<span class="btn btn-<?php echo $bootstrap_style?> btn-file upload_box">
		<i class="fa fa-upload"></i> 
		<?php echo $label?> 
		<input name="file" key="<?php echo $key;?>" preview="<?php echo $preview_selector;?>" type="file">
		<input type="hidden" name="key" value="<?php echo $key;?>">
		<input type="hidden" name="prev"><br>
	</span>
	<div class="upload_progress" style="max-width:300px;display:none;">
		<div class="progress" style="height:10px;">
			<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
		</div>
	</div>
</form>