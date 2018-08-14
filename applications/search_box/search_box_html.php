<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.search_box
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.1
 */
 
$l = new Language; $l->app = "search_box";
?>
<?php if($this->submitable):?><form method="GET" style="margin-bottom:0px;"><?php endif;?>
<div class="input-group" style="max-width:<?php echo $this->customWidth;?>;<?php if(!$withIcon):?>width:100%;<?php endif;?>">
  <?php if($withIcon):?><span class="input-group-addon"><i class="fa fa-search"></i></span><?php endif;?>
  <?php if($_GET[$this->inputName] != ""):?>
  <div class="close" style="position: absolute;top: 6px;font-size: 22px;z-index: 999;right: 10px;color:#a0a0a0;background-color:white;">
	<a href="<?php echo $this->clearURL;?>">&times;</a>
  </div>
  <?php endif;?>
  <span id="<?php echo $this->prefix?>-notice"style="display:none;"><small><?php $l->dump("sfoap");?></small></span>
  <input data-prefix="<?php echo $this->prefix?>" data-hidefirst="<?php echo $this->hideAll?"yes":"no"?>" data-dynamic="<?php echo $this->dynamic?"yes":"no"?>" autocomplete="off" placeholder="<?php echo($this->customHint?$this->hintText:$l->get("find"))?>" name="<?php echo $this->inputName;?>" type="text" class="form-control searchbox-input" value="<?php echo $_GET[$this->inputName]?>">  
</div>
<?php if($this->submitable):?></form><?php endif;?>
<?php ob_start();?>
<script>
	$(document).ready(function(){				
		//Put the search data on every element
		let c=JSON.parse('<?php echo str_replace("'","\\'",json_encode($this->data))?>');
		let a=$(".<?php echo $this->prefix?>[class^='<?php echo $this->prefix?>'],.<?php echo $this->prefix?>[class*=' <?php echo $this->prefix?>']");
		$.each(a,function(d){
			let b=$(this).attr('class').split(/\s+/).filter(function(e){
				return e.indexOf("<?php echo $this->prefix?>_") != -1 && e != "<?php echo $this->prefix?>";
			})[0];
			$(".<?php echo $this->prefix?>."+b).attr("data-searchbox",JSON.stringify(c[b.replace("<?php echo $this->prefix?>_","")]));
		});
	});
</script>
<?php echo FastCache::outJSMin(); ob_start();?>
<script>
	//Create the search algorithm
	$(document).on("keyup change","input.searchbox-input",function(e){
		e.stopPropagation();
		let x=$(this);
		if(x.attr("data-dynamic") == "no") return;
		let a=$("."+x.attr("data-prefix"));
		if(x.val().length >= 2){
			//Do the search						
			$.each(a,function(d){
				try{
					let y=$(this);
					let z=false;
					let b=JSON.parse($(this).attr("data-searchbox"));
					$.each(b,function(i){
						if(this.toString().toUpperCase().trim().indexOf(x.val().toUpperCase().trim()) >= 0){
							y.show();
							z=true;
							return false;
						}
					});
					if(!z) y.hide();
				}catch(e){
					console.log(e);
				}
			});
		}else{
			a.toggle(x.attr("data-hidefirst")=="no");
		}
	});
</script>
<?php Template::appendBody(FastCache::outJSMin(),true);?>
<div style="clear:both;height:15px;"></div>