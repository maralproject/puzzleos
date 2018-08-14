<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.admin
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.0
 */

$l=new Language; $l->app="admin";
?>
<div style="max-width:1170px;margin:5px auto;">
	<ul class="nav nav-tabs" style="font-size:15pt;display:inline-block;">
	  <li class="active"><a data-toggle="tab" href="#conf"><i class="fa fa-cogs"></i></a></li>
	  <li><a data-toggle="tab" href="#templates"><i class="fa fa-paint-brush"></i></a></li>
	  <li><a data-toggle="tab" href="#apps"><i class="fa fa-th-large"></i></a></li>
	</ul>
	<div style="clear:both;height:10px;"></div>
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
		<button onclick="$('#conf_f').submit()" type="button" class="btn btn-success"><?php echo $l->get("SAVE_ON") . "<span style=\"display:block;font-size:8pt\">" . POSGlobal::$domain_zone . "</span>"?></button>
		<?php else:?>
		<input onclick="$('#conf_f').submit()" type="button" class="btn btn-success" value="<?php $l->dump("SAVE")?>">
		<?php endif;?>
	</div>
</div>