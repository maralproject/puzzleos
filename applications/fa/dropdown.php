<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

$l=new Language; $l->app="fontawesome";
?>
<input type="hidden" name="<?php echo $name?>" id="<?php echo $name?>" class="fa-input-hidden" value="<?php echo $selected?>">
<div style="position:relative;">
<button class="btn btn-secondary" onclick="$('.fa-explorer').not('#selector_<?php echo $name?>').hide();$('#selector_<?php echo $name?>').toggle();">
<i class="fa fa-<?php echo $selected?>" id="preview_<?php echo $name?>"></i> 
<span class="caret"></span>
</button>
<div id="selector_<?php echo $name?>" class="fa-explorer">
<div class="input-group" style="padding: 10px;width:100%;">
<input type="text" id="search_fa_<?php echo $name?>" placeholder="<?php $l->dump("SEARCH_ICON")?>" class="form-control">
</div>
<div class="fa-white-container">
<div class="row" style="margin:0px;padding:0px;" id="fa-con-<?php echo $name?>" name="<?php echo $name?>"></div>
</div>
</div>
</div>
<script>
_fa_configureIconInput("<?php echo $name?>","<?php echo $selected?>");
</script>