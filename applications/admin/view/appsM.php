<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

$s_app = new Application; $s_app->run("search_box");
$s = new SearchBox("AppMan_");
$acc_app = new Application("users");
$s->setSubmitable(false);
$s->setDynamic(true);
$appList = AppManager::listAll();
foreach($appList as $a){
	$list = [];
	$list[0] = $a["name"];
	$list[1] = $a["title"];
	$list[2] = $a["desc"];
	$s->putData($list,$a["name"]);
}

ob_start();?>
<style>
.truncate {
	overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
}
.appgrid .btn{
	margin-bottom:5px;
}
small{
	font-size:11pt!important;
}
.grid{
	margin-bottom:10px;
	height:112px;
}
</style><?php echo Minifier::getCSSFile()?>
<?php $s->dumpSearchBox(); ?>
<div class="row">
<div style="clear:both;"></div>
<?php 
	$l = new Language; $l->app="admin";
	$empty = true;
	foreach(AppManager::listAll() as $d){
		$empty = false;
		$dDis = ($d["default"]==1);
		$dTitle = $l->get("SET_AS_DEFAULT");;
		if($d["default"]==APP_DEFAULT) $dTitle = $l->get("CURRENTLY_DEFAULT");
		if($d["default"]==APP_CANNOT_DEFAULT) $dTitle = $l->get("NOT_AVAILABLE");
		$theresrv = "";
		foreach(AppManager::listAll()[$d["name"]]["services"] as $q){
			$theresrv = '<span class="label label-primary" style="font-size:6pt;">Services</span>';
			break;
		}
		$appR = in_array($d["name"],POSConfigMultidomain::$restricted_app);
		?>
		<div class="grid col-lg-4 col-sm-12 <?php echo $s->getDomClass($d["name"])?>">
		<table appid="<?php echo $d["name"]?>" class="appgrid" style="width:100%;">
		<tr>
			<td class="title" rowspan="3" style="width:60px;vertical-align:top;text-align:center;padding-top:10px;">
				<i class="fa fa-th-large fa-2x"></i>
			</td>
			<td class="truncate" style="font-size:15pt;"><?php echo $d["title"].' '.$theresrv?></td>
		</tr>
		<tr>
			<td class="desc truncate">
				<small><?php echo $d["desc"]?></small>
			</td>
		</tr>
		<tr>
			<td class="desc truncate" style="padding-top:5px;">
				<small><i class="fa fa-lock"></i> 
				<?php if($d["system"] == "0" && $d["default"]!="3"):?>
				<?php $acc_app->loadView("group_button",["auth_".$d["name"],$d["group"],$d["level"]])?>
				<?php else:?>
				<?php echo Accounts::getGroupName($d["group"])?>
				<?php endif?>
				</small>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:right;height:35px;">
				<?php if(!$d["system"] && POSConfigGlobal::$use_multidomain):?>
				<button stat="<?php echo($appR?1:0)?>" style="<?php echo($dDis?"display:none":"")?>" appid="<?php echo $d["name"]?>" type="button" class="restrict btn btn-xs btn-<?php echo($appR?"primary":"danger")?>">
					<?php echo($appR?$l->get("TURN_ON"):$l->get("TURN_OFF"))?>
				</button>
				<?php endif?>
				
				<?php if($d["default"]!=3):?>
				<button appid="<?php echo $d["name"]?>" type="button" id="sdb-<?php echo $d["name"]?>" style="<?php echo($appR?"display:none":"")?>" class="sdb btn btn-xs <?php echo($dDis?"disabled":"")?> <?php echo($d["default"]==3?"btn-danger":"btn-success")?>"><?php echo $dTitle?></button>
				<?php endif?>
				
				<?php if(file_exists($d["dir"]."/panel.admin.php")):?>
				<a href="<?php echo __SITEURL?>/admin/manage/<?php echo $d["name"]?>"><button type="button" class="btn btn-secondary btn-xs"><?php echo $l->get("MANAGE")?></button></a>
				<?php endif?>
			</td>
		</tr>
		</table></div>
		<?php
	}
	
	if($empty) echo('<h3><i class="fa fa-check"></i> '. $l->get("NO_APPS").'</h3>');
?>
<script>
var l_sad = "<?php $l->dump("SET_AS_DEFAULT");?>";
var l_cd = "<?php $l->dump("CURRENTLY_DEFAULT");?>";
var l_tf = "<?php $l->dump("TURN_OFF");?>";
var l_to = "<?php $l->dump("TURN_ON");?>";
function cu_show(){showMessage("<?php $l->dump("CONFIGURATION_UPDATED")?>","success");}
</script>
<?php ob_start()?>
<script>
$(".restrict").on("click",function(){
	let x=$(this);
	if(x.attr("stat")=="0"){
		$.post("<?php echo __SITEURL?>/admin/restrictApp/",{
			appid:x.attr("appid")
		},function(d){
			if(d != "Y"){
				showMessage(d,"danger");
				return;
			}
			x.next("button.sdb").hide();
			x.html(l_to);
			x.removeClass("btn-danger");
			x.addClass("btn-primary");
			x.attr("stat","1");cu_show();
		});
	}else{
		$.post("<?php echo __SITEURL?>/admin/unrestrictApp/",{
			appid:x.attr("appid")
		},function(d){
			if(d != "Y"){
				showMessage(d,"danger");
				return;
			}
			x.next("button.sdb").show();
			x.html(l_tf);
			x.removeClass("btn-info");
			x.addClass("btn-danger");
			x.attr("stat","0");cu_show();
		});		
	}
});
$(".sdb").on("click",function(){
	if($(this).is(".disabled")) return;
	$.post("<?php echo __SITEURL?>/admin/setDef/" + $(this).attr("appid"),function(r){
		$("button.sdb.disabled").prev("button.restrict").show();
		$(".sdb").removeClass("disabled");
		$(".sdb").html(l_sad);
		$("#sdb-" + r).addClass("disabled");
		$("button.sdb.disabled").prev("button.restrict").hide();
		$("#sdb-" + r).html(l_cd);
		cu_show();
	});
});
$(".usergroup-input").on("change",function(){
	$.post("<?php echo __SITEURL?>/admin/chownApp",{appid: $(this).parents(".appgrid").attr("appid"), own: $(this).val()},function(r){
		if(r == "SUCC")	cu_show();
	});
});
</script>
<?php echo Minifier::getJSFile()?>
</div>