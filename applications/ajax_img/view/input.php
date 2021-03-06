<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

$l = new Language;
?>

<?php if(!defined("_IMAGEUPLOADER_")):?>
	<?php define("_IMAGEUPLOADER_",1)?>
	<script>
	var L_NO_FILE = "<?php $l->dump("NO_FILE")?>";
	var L_TOO_BIG = "<?php $l->dump("TOO_BIG")?>";
	var L_UPGRADE = "<?php $l->dump("UPGRADE")?>";
	</script>
	<?php require(my_dir("/js/form_action.js.php"))?>

	<?php ob_start()?>
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
		opacity: 0;
		outline: none;
		background: white;
		cursor: inherit;
		display: block;
	}
	</style>
	<?php echo Minifier::outCSSMin(); ?>
<?php endif?>

<form style="height:33px;" class="img_ajax">
	<span class="btn btn-<?php echo $bootstrap_style?> btn-file upload_box">
		<i class="fa fa-upload"></i> 
		<?php echo $label?> 
		<input <?php if($shrink):?>shrink="yes"<?php endif?> name="file" key="<?php echo $key?>" preview="<?php echo $preview_selector?>" type="file" accept="image/*">
	</span>
	<div class="upload_progress" style="max-width:300px;display:none;">
		<div class="progress" style="height:10px;">
			<div class="progress-bar progress-bar-striped active progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
		</div>
	</div>
</form>