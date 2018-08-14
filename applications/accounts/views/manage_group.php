<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 *
 * @software     Release: 2.0.0
 */

$l=new Language;$l->app="users";

$dataLvl  = [];
$dataLvl[0] = Database::readAll("app_users_grouplist","WHERE `level`=0")->data;
$dataLvl[1] = Database::readAll("app_users_grouplist","WHERE `level`=1")->data;
$dataLvl[2] = Database::readAll("app_users_grouplist","WHERE `level`=2")->data;
$dataLvl[3] = Database::readAll("app_users_grouplist","WHERE `level`=3")->data;

$a = new Application;
$b = $a->run("search_box"); if(!$b) throw new PuzzleError("Cannot load Application `SearchBox`");
$se = new SearchBox("UGL_");
$se->setSubmitable(false);
$se->setInputName("ugl_search");
$se->setCustomHint($l->get("FIND_USER"));
$usersList = [];
foreach(Database::readAll("app_users_list")->data as $ugl){
	$usersList[0] = $ugl["name"];
	$usersList[1] = $ugl["email"];
	$usersList[2] = $ugl["username"];
	$se->putData($usersList,$ugl["id"]);
}
?>
<?php ob_start();?>
<style>
.horizontal_axes{
	width:100%;
	padding:10px;
	overflow:hidden;
}
.h_child{
	float:left;
	margin-right:20px;
}
.h_child .close{
	margin-left:5px;
	margin-top:-3px;
	height:0px;
}
.h_child .tag{
	font-size:9pt;
	margin-right:10px;
	font-weight:bold;
	color:#303030;
}
.white .tag{
	color:white;
}
.user_card{
	font-size:9pt;
	float:left;
	padding:12px;
	cursor:pointer;
}
.user_card:before{
	font-family:FontAwesome;
	content:"\f007";
	margin-right:10px;
}
.group_card:before{
	font-family:FontAwesome;
	content:"\f0c0"!important;
	margin-right:10px;
}
.ng_table td{
	border:none;
	padding:5px;
	color:inherit;
}
.ng_table{
	color:inherit;
}
.ng_table .btn-link{
	color:inherit;
}
.ng_table{
	width:100%;
}
.group_card{
	color:black!important;
}
#addGroup{
	display:none!important;
}
</style>
<?php echo FastCache::getCSSFile();?>
<h3><?php $l->dump("group_editor")?> <i onclick="newGroup();" class="fa fa-plus btn btn-link addItem"></i></h3>
<?php $se->dumpSearchBox();?>
<div level="0" class="horizontal_axes" style="background-color:#B9F6CA;">
	<?php
	foreach($dataLvl[0] as $d){
		echo('<div gid="'.$d["id"].'" class="h_child">
			<div class="tag">'.$d["name"].''.($d["system"]==0?'<div class="close">&times;</div>':' <i class="fa fa-lock"></i>').'</div>');
		foreach(Database::readAll("app_users_list","WHERE `group`=".$d["id"])->data as $ul){
			echo('<div uid="'.$ul["id"].'" class="user_card material_card ripple '.$se->getDomClass($ul["id"]).'">'.$ul["name"].'</div>');
		}
		echo('</div>');
	}
	?>
</div>
<div style="clear:both;"></div>
<div level="1" class="horizontal_axes" style="background-color:#69F0AE;">
	<?php
	foreach($dataLvl[1] as $d){
		echo('<div gid="'.$d["id"].'" class="h_child">
			<div class="tag">'.$d["name"].''.($d["system"]==0?'<div class="close">&times;</div>':' <i class="fa fa-lock"></i>').'</div>');
		foreach(Database::readAll("app_users_list","WHERE `group`=".$d["id"])->data as $ul){
			echo('<div uid="'.$ul["id"].'" class="user_card material_card ripple '.$se->getDomClass($ul["id"]).'">'.$ul["name"].'</div>');
		}
		echo('</div>');
	}
	?>
</div>
<div style="clear:both;"></div>
<div level="2" class="horizontal_axes" style="background-color:#00E676;">
	<?php
	foreach($dataLvl[2] as $d){
		echo('<div gid="'.$d["id"].'" class="h_child">
			<div class="tag">'.$d["name"].''.($d["system"]==0?'<div class="close">&times;</div>':' <i class="fa fa-lock"></i>').'</div>');
		foreach(Database::readAll("app_users_list","WHERE `group`=".$d["id"])->data as $ul){
			echo('<div uid="'.$ul["id"].'" class="user_card material_card ripple '.$se->getDomClass($ul["id"]).'">'.$ul["name"].'</div>');
		}
		echo('</div>');
	}
	?>
</div>
<div style="clear:both;"></div>
<div id="groupList" style="display:none!important;">
	<?php $l->dump("assign1")?> <span class="umname"></span> <?php $l->dump("assign2")?>:
	<div uid="" class="umove" style="max-height:250px;overflow:auto;">
	<?php
	foreach($dataLvl[0] as $d){
		echo('<div uid="'.$d["id"].'" class="group_card user_card material_card ripple">'.$d["name"].'</div>');
	}
	?>
	<div style="clear:both;border-bottom:1px solid white;"></div>
	<?php
	foreach($dataLvl[1] as $d){
		echo('<div uid="'.$d["id"].'" class="group_card user_card material_card ripple">'.$d["name"].'</div>');
	}
	?>
	<div style="clear:both;border-bottom:1px solid white;"></div>
	<?php
	foreach($dataLvl[2] as $d){
		echo('<div uid="'.$d["id"].'" class="group_card user_card material_card ripple">'.$d["name"].'</div>');
	}
	?>
	<div style="clear:both;"></div>
	</div>
</div>
<div id="addGroup">
<?php $l->dump("add_ugroup")?>:
<form method="POST" onsubmit="submitGroup($(this));return false;">
<table class="ng_table">
	<tr>
		<td><?php $l->dump("group_name")?>:</td>
		<td><?php $l->dump("level")?>:</td>
		<td></td>
	</tr>
	<tr>
		<td><input autocomplete="off" id="groupName" type="text" ></td>
		<td>
			<select class="authList" name="level" id="level">
			<option value="1"><?php $l->dump("EMPLOYEE")?></option>
			<option value="2" selected><?php $l->dump("REGISTERED")?></option>
			</select>
		</td>
		<td><button class="btn btn-default" type="submit"><i style="color:inherit;" class="fa fa-check"></i></button></td>
	</tr>
</table>
</form>
</div>
<script>
var currentUser = <?php echo $_SESSION['account']['id']?>;
var changeSaved_L = "<?php $l->dump("ch_saved")?>";
var youCannot_l = "<?php $l->dump("you_no_move")?>";
var sure_to_rm = "<?php $l->dump("confirm_rm_group")?>?";
var group_blank = "<?php $l->dump("gname_no_blank")?>!";
var error_l = "<?php $l->dump("ch_not_saved")?>!";
var groupApp = "<?php $l->dump("app_on_group")?>!";
</script>
<?php ob_start();?>
<script>
function submitGroup(f){
	hideMessage();
	if(f.find("#groupName").val() == ""){
		showMessage(group_blank,"danger");
	}else{
		$.post("<?php echo __SITEURL?>/users/ajax/newGroup",{tf:"yes", name:f.find("#groupName").val(), level:f.find("#level").val()},function(r){
			if(r==""){
				showMessage(error_l,"danger");
			}else{
				location.reload();
			}
		});
	}
}
function newGroup(){
	showMessage($("#addGroup").html(),"info","addGroup",false);
	setTimeout(function(){$("#systemMessage_wrap #groupName").focus()},5);
}
$(".h_child .close").on("click",function(){
	if(confirm(sure_to_rm)){
		$.post("<?php echo __SITEURL?>/users/ajax/rmGroup",{tf:"yes", gid:$(this).parent().parent().attr("gid")},function(r){
			if(r == ""){
				showMessage(youCannot_l,"danger");
			}else if(r == "APP"){
				showMessage(groupApp,"danger");
			}else{
				user_config_changed = 1;
				$(".h_child[gid=" + r + "] .user_card").detach().appendTo(".h_child[gid=<?php echo Accounts::getRootGroupId(USER_AUTH_REGISTERED)?>]");
				$(".h_child[gid=" + r + "]").remove();
				$("#groupList .group_card[uid=" + r + "]").remove();
				showMessage(changeSaved_L,"success");
			}
		});
	}
});
$(".h_child .user_card").on("click",function(){
	if($(this).attr("uid") == currentUser){
		showMessage(youCannot_l,"danger");
	}else{
		$("#groupList .umove").attr("uid",$(this).attr("uid"));
		$("#groupList .umname").html($(this).html());
		hideMessage();
		showMessage($("#groupList").html(),"info","select",false);
		$(".umove[uid=" + $(this).attr("uid") + "] .group_card").on("click",function(){
			hideMessage();
			var gto = $(this).attr("uid");
			$.post("<?php echo __SITEURL?>/users/ajax/changeGroup",{tf:"yes", uid:$(this).parent().attr("uid"), toGroup:$(this).attr("uid")},function(r){
				user_config_changed = 1;
				$(".h_child .user_card[uid=" + r + "]").detach().appendTo(".h_child[gid=" + gto + "]");
				showMessage(changeSaved_L,"success");
			});
		});
	}
});
</script>
<?php echo FastCache::getJSFile();?>
