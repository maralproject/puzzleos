<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

$arg1 = $arguments[0];
$arg2 = $arguments[1];
$l = new Language; $l->app="fontawesome";
if($arg1 == "getdropChoiceInput"){
	if(!$GLOBALS["fa_view_called"]){
		$GLOBALS["fa_view_called"] = true;
		ob_start();?>
		<script>
		var item_keywords_fa = [<?php
			require("array.php");
			foreach($font_awesome_icons as $k=>$d){
				echo("'".$k."',");
			}
		?>];
		var defaultFaList = "<?php
			foreach($font_awesome_icons as $k=>$d){
			$f = str_replace("fa-","",$k);
			echo('<div id=\'fa_fill_'.$k.'\' class=\'col-2 fa-icon click_available pick_fa\' fV=\''.$f.'\' key=\''.$k.'\'><i class=\'fa '.$k.'\'></i></div>');
			}
		?>";
		function _fa_configureIconInput(name,choice){
			$("#fa-con-" + name).html(defaultFaList);
			$('#selector_' + name + ".pick_fa").bind("click",function(){
				$("#" + $(this).parent().attr("name")).val($(this).attr("fV")).trigger("change");
				$("#preview_" + $(this).parent().attr("name")).removeClass().addClass("fa " + $(this).attr("key"));
				$("#selector_" + $(this).parent().attr("name")).hide();
			});
			$("#search_fa_" + name).keyup(function(){
				var keywordIndex;
				$.each(item_keywords_fa, function(r) {
					if($("#search_fa_" + name).val()!=""){
					var rSearchTerm = new RegExp($("#search_fa_" + name).val(),'i');
						if (item_keywords_fa[r].match(rSearchTerm)) {
							$("#fa-con-" + name + " #fa_fill_" + item_keywords_fa[r]).show();
						}else{
							$("#fa-con-" + name + " #fa_fill_" + item_keywords_fa[r]).hide();
						}
					}else{
						$("#fa-con-" + name + " #fa_fill_" + item_keywords_fa[r]).show();
					}
				});
			});
			$("body").bind("click",function(){$('#selector_' + name).hide();});
			$('#selector_' + name).bind("click",function(e){e.stopPropagation();});
			$('#selector_' + name).parent().bind("click",function(e){e.stopPropagation();});
		}
		</script><?php echo Minifier::outJSMin();?>
		<?php ob_start();?><style>
		.fa-explorer{
			display:none;position:absolute;z-index:999;background: #f0f0f0;border-radius: 7px;border: 1px solid #c0c0c0;width:240px;margin-top:3px;margin-left:5px;
		}
		.fa-white-container{
			width:100%;background:white;height:150px;border-bottom-left-radius:7px;border-bottom-right-radius:7px;padding:5px;overflow-y:auto;
		}
		.fa-icon{
			border:1px solid #e0e0e0;height:35px;padding:0px;margin:3px;font-size:20px;
		}
		</style><?php echo Minifier::outCSSMin();?>
		<?php
	}
	$name = $arg2[0];
	$selected = $arg2[1];
	include("dropdown.php");
}else if($arg1 == "JSNewInput"){
	/* This views is used to load fontawesome scripts and js on another part of HTML */
	if(!$GLOBALS["fa_view_called"]){
		$GLOBALS["fa_view_called"] = true;
		ob_start();?>
		<script>
		var item_keywords_fa = [<?php
			require("array.php");
			foreach($font_awesome_icons as $k=>$d){
				echo("'".$k."',");
			}
		?>];
		var defaultFaList = "<?php
			foreach($font_awesome_icons as $k=>$d){
			$f = str_replace("fa-","",$k);
			echo('<div id=\'fa_fill_'.$k.'\' class=\'col-2 fa-icon click_available pick_fa\' fV=\''.$f.'\' key=\''.$k.'\'><i class=\'fa '.$k.'\'></i></div>');
			}
		?>";
		function _fa_configureIconInput(name,choice){
			$("#preview_" + name).removeClass().addClass("fa fa-" + choice);
			$("#" + name).val(choice);
			$("#fa-con-" + name).html(defaultFaList);
			$('#selector_' + name + " .pick_fa").bind("click",function(){
				$("#" + $(this).parent().attr("name")).val($(this).attr("fV")).trigger("change");
				$("#preview_" + $(this).parent().attr("name")).removeClass().addClass("fa " + $(this).attr("key"));	
				$("#selector_" + $(this).parent().attr("name")).hide();	
			});	
			$("#search_fa_" + name).keyup(function(){
				var keywordIndex;
				$.each(item_keywords_fa, function(r) {
					if($("#search_fa_" + name).val()!=""){
					var rSearchTerm = new RegExp($("#search_fa_" + name).val(),'i');
						if (item_keywords_fa[r].match(rSearchTerm)) {
							$("#fa-con-" + name + " #fa_fill_" + item_keywords_fa[r]).show();
						}else{
							$("#fa-con-" + name + " #fa_fill_" + item_keywords_fa[r]).hide();
						}
					}else{
						$("#fa-con-" + name + " #fa_fill_" + item_keywords_fa[r]).show();
					}
				});	
			});
			$("body").bind("click",function(){$('#selector_' + name).hide();});
			$('#selector_' + name).bind("click",function(e){e.stopPropagation();});
			$('#selector_' + name).parent().bind("click",function(e){e.stopPropagation();});
		}
		</script><?php echo Minifier::outJSMin();?>
		<?php ob_start();?><style>
		.fa-explorer{
			display:none;position:absolute;z-index:999;background: #f0f0f0;border-radius: 7px;border: 1px solid #c0c0c0;width:240px;margin-top:3px;margin-left:5px;
		}
		.fa-white-container{
			width:100%;background:white;height:150px;border-bottom-left-radius:7px;border-bottom-right-radius:7px;padding:5px;overflow-y:auto;
		}
		.fa-icon{
			border:1px solid #e0e0e0;height:35px;padding:0px;margin:3px;font-size:20px;
		}
		</style><?php echo Minifier::outCSSMin();?>
		<?php
		//$search = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
		//$replace = array('>','<','\\1');
		//$buffer = preg_replace($search, $replace, ob_get_clean());
		//echo $buffer;
	}
	if(!$GLOBALS["fa_js_called"]){
		$GLOBALS["fa_js_called"] = true;
		ob_start();?>
		<script>
		function getNewIconInput(inputName){
			var html = '<input type="hidden" name="%INPUTNAME%" id="%INPUTNAME%" class="fa-input-hidden" value="tags">\
			<div style="position:relative;">\
				<button class="btn btn-secondary" onclick="$(\'.fa-explorer\').not(\'#selector_%INPUTNAME%\').hide();$(\'#selector_%INPUTNAME%\').toggle();">\
					<i class="fa fa-tags" id="preview_%INPUTNAME%"></i> <span class="caret"></span>\
				</button>\
				<div id="selector_%INPUTNAME%" class="fa-explorer">\
					<div class="input-group" style="padding: 10px;width:100%;">\
						<input type="text" id="search_fa_%INPUTNAME%" placeholder="<?php $l->dump("SEARCH_ICON");?>" class="form-control">\
					</div>\
					<div class="fa-white-container">\
						<div class="row" style="margin:0px;padding:0px;" id="fa-con-%INPUTNAME%" name="%INPUTNAME%">\
						</div>\
					</div>\
				</div>\
			</div>';
			return (html.replace(new RegExp("%INPUTNAME%", 'g'), inputName));
		}
		</script><?php echo Minifier::outJSMin();?>
		<?php
	}
}