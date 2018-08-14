<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.admin
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.0
 */

$s_app = new Application; $s_app->run("search_box");
$s = new SearchBox("AppMan_");
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

ob_start();
?>
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
</style><?php echo FastCache::getCSSFile()?>
<?php $s->dumpSearchBox(); ?>
<div class="row">
<div style="clear:both;"></div>
<?php 
	$l = new Language; $l->app="admin";
	$empty = true;
	foreach(AppManager::listAll() as $d){
		//if(!file_exists($d[1]."/panel.admin.php")) continue;
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
		echo('
		<div class="grid col-lg-4 col-sm-12 '.$s->getDomClass($d["name"]).'">
		<table appid="'.$d["name"].'" class="appgrid" style="width:100%;">
		<tr>
			<td class="title" rowspan="3" style="width:60px;vertical-align:top;text-align:center;padding-top:10px;"><i class="fa fa-th-large fa-2x"></i></td>
			<td class="truncate" style="font-size:15pt;">
				'.$d["title"].' '.$theresrv.'
			</td>
		</tr>
		<tr>
			<td class="desc truncate">
				<small>'.$d["desc"].'</small>
			</td>
		</tr>
		<tr>
			<td class="desc truncate" style="padding-top:5px;">
				<small><i class="fa fa-lock"></i> '.(($d["system"] == "0" && $d["default"]!="3") ? Accounts::getGroupPromptButton("auth_".$d["name"],$d["group"],$d["level"]) : Accounts::getGroupName($d["group"])).'</small>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:right;height:35px;">
				'.((!$d["system"] && POSConfigGlobal::$use_multidomain)?'<button stat="'.($appR?1:0).'" style="'.($dDis?"display:none":"").'" appid='.$d["name"].' type="button" class="restrict btn btn-xs btn-'.($appR?"primary":"danger").'">'.($appR?$l->get("TURN_ON"):$l->get("TURN_OFF")).'</button>':'').'
				'.($d["default"]==3?"":'<button appid='.$d["name"].' type="button" id="sdb-'.$d["name"].'" style="'.($appR?"display:none":"").'" class="sdb btn btn-xs '.($dDis?"disabled":"").' '.($d["default"]==3?"btn-danger":"btn-success").'">'.$dTitle.'</button>').(file_exists($d["dir"]."/panel.admin.php")?'
				<a href="'.__SITEURL.'/admin/manage/'.$d["name"].'"><button type="button" class="btn btn-default btn-xs">'.$l->get("MANAGE").'</button></a>':"").'
			</td>
		</tr>
		</table></div>
		');
	}
	if($empty){
		echo('<h3><i class="fa fa-check"></i> '. $l->get("NO_APPS").'</h3>');
	}
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
<?php echo FastCache::getJSFile()?>
</div>