<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

$l=new Language;?>
<div style="max-width:1170px;margin:5px auto;">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link active" data-toggle="tab" href="#conf"><i style="margin-right:5px" class="fa fa-cogs"></i>
			Settings
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#templates"><i style="margin-right:5px" class="fa fa-paint-brush"></i>
			Theme
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#apps"><i style="margin-right:5px" class="fa fa-th-large"></i>
			Applications
			</a>
		</li>
	</ul>
	<div style="clear:both;height:25px;"></div>
	<div class="tab-content" style="padding-bottom:15vh;">
		<div id="apps" class="tab-pane">
			<?php include("appsM.php");?>
		</div>
		<div id="templates" class="tab-pane">
			<?php include("templatesM.php");?>
		</div>
		<div id="conf" class="tab-pane active">
			<form id="conf_f" method="POST" action="<?php echo __SITEURL?>/admin/saveConfig">
				<?php include("confM.php");?>
				<input type="hidden" name="trueForm" value="1">
				<input type="submit" class="btn btn-success" style="display:none!important" value="">
			</form>
		</div>
	</div>	
	<div id="confirmation_bar">
		<?php if(POSConfigGlobal::$use_multidomain):?>
		<button onclick="$('#conf_f').submit()" type="button" class="btn btn-success"><?php echo $l->get("SAVE_ON") . "<span style=\"display:block;font-size:8pt\">" . POSConfigMultidomain::zone() . "</span>"?></button>
		<?php else:?>
		<input onclick="$('#conf_f').submit()" type="button" class="btn btn-success" value="<?php $l->dump("SAVE")?>">
		<?php endif;?>
	</div>
</div>

<script>
$(function () {
	$("[data-toggle=tooltip]").tooltip();
});
</script>