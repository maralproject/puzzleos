<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.1") or die("You need to upgrade the system");
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
?>
<h3><?php $l->dump("MANAGE_USERS");?></h3>
<h5 id="savedtext" style="display:none;color:green;font-weight:bold;"></h5>
<h5 id="errortext" style="display:none;color:red;font-weight:bold;"></h5>
<?php ob_start()?>
<style>
table input[type='text'], table input[type='password'], table select:not(.form-control) {
	height: 30px;
	border: solid 0px #00E3E3;
	font-size: 14px;
	color: #474747;
	min-width:100px;
	width:100%;
	-webkit-transition: all 0.30s ease-in-out;
	-moz-transition: all 0.30s ease-in-out;
	-ms-transition: all 0.30s ease-in-out;
	-o-transition: all 0.30s ease-in-out;
	outline: none;
	padding: 3px 0px 3px 3px;
	margin: 5px 1px 3px 0px;
}
table input[type='text']:focus, table input[type='password']:focus, table select:focus:not(.form-control){
	box-shadow: 0 0 5px rgba(81, 203, 238, 1);
	padding: 3px 0px 3px 3px;
	margin: 5px 1px 3px 0px;
	border: 1px solid rgba(81, 203, 238, 1);
}
table select{
	padding-left:0px!important;
	padding-right:0px!important;
}
#user_config td{
	vertical-align:middle;
}
</style>
<?php 
	echo FastCache::outCSSMin();
	ob_start();
?>
<script>
$(document).on("click",".delUser",function(e){
	e.stopPropagation();
	if(confirm("Delete this user?")){			
		$.post("<?php echo __SITEURL?>/users/ajax/deleteAJAX", {tf:"yes", name:$(this).parent().attr("name")},function(data){
			if(data == "") {fail();return;}
			$("tr[name='" + data + "']").remove();
			user_config_changed = 1;
			succ();
		});
	}
});
$(document).on("change",".name",function(e){
	e.stopPropagation();
	$.post("<?php echo __SITEURL?>/users/ajax/changeName", {tf:"yes", name:$(this).parent().parent().attr("name"), names:$(this).val()},function(data){
		if(data == "") {fail();return;}
		user_config_changed = 1;
		succ();
	})
});
$(document).on("change",".mail",function(e){
	e.stopPropagation();
	$.post("<?php echo __SITEURL?>/users/ajax/changeMail", {tf:"yes", name:$(this).parent().parent().attr("name"), mail:$(this).val()},function(data){
		if(data == "") {fail();return;}
		succ();
	})
});
$(document).on("change",".phone",function(e){
	e.stopPropagation();
	$.post("<?php echo __SITEURL?>/users/ajax/changePhone", {tf:"yes", name:$(this).parent().parent().attr("name"), phone:$(this).val()},function(data){
		if(data == "") {fail();return;}
		succ();
	})
});
$(document).on("change",".languageList",function(e){
	e.stopPropagation();
	$.post("<?php echo __SITEURL?>/users/ajax/changeLocal", {tf:"yes", name:$(this).parent().parent().parent().attr("name"), local:$(this).val()},function(data){
		if(data == "") {fail();return;}
		succ();
	})
});
$(document).on("change",".uname",function(e){
	e.stopPropagation();
	$.post("<?php echo __SITEURL?>/users/ajax/changeUname", {tf:"yes", name:$(this).parent().parent().attr("name"), uname:$(this).val()},function(data){
		if(data == "") {fail();return;}
		succ();
	})
});
$(document).on("change",".pwd",function(e){
	e.stopPropagation();
	$.post("<?php echo __SITEURL?>/users/ajax/changePass", {tf:"yes", name:$(this).parent().parent().attr("name"), paswd:$(this).val()},function(data){
		if(data == "") {fail();return;}
		$("input[name='pwd_" + data + "']").val("").attr("placeholder","<?php $l->dump("CHANGED");?>");
		succ();
	})
});
$(document).on("click",".new_user",function(e){
	e.stopPropagation();
	$.post("<?php echo __SITEURL?>/users/ajax/newAJAX", {tf:"yes"},function(data){
		if(data == "") {fail();return;}
		var html = '<tr name="%INPUTID%">\
				<td><input class="qtyin name" type="text" name="name_%INPUTID%" value="" placeholder="<?php $l->dump("CLICK_TOE");?>"></td>\
				<td><span><?php echo Accounts::getGroupName(Accounts::getRootGroupId(USER_AUTH_REGISTERED));?></span></td>\
				<td><input class="qtyin mail" type="text" name="mail_%INPUTID%" value="" placeholder="<?php $l->dump("CLICK_TOE");?>"></td>\
				<td><input class="qtyin phone" type="text" name="phone_%INPUTID%" value="" placeholder="<?php $l->dump("CLICK_TOE");?>"></td>\
				<td style="width:200px;"><?php echo str_replace("\r\n","",LangManager::getForm("lang_%INPUTID%","def",false,false,false))?></td>\
				<td><input class="qtyin pwd" autocomplete="new-password" type="password" name="pwd_%INPUTID%" placeholder="Empty"></td>\
				<td><input class="qtyin uname" type="text" name="uname_%INPUTID%" value="" placeholder="<?php $l->dump("CLICK_TOE");?>"></td>\
				<td style="text-align:center;font-size:14pt;font-weight:bold;"  class="click_available delUser"><i class="fa fa-trash-o"></i></td>\
			</tr>';
		$(".addBigButton").before(html.replace(new RegExp("%INPUTID%", 'g'), data));
		user_config_changed = 1;
		succ();
	});
});
</script><?php echo FastCache::getJSFile()?>
<div class="table-responsive">
<table class="table table-hover" id="user_config" style="width:100%;">
<tr>
	<th><?php $l->dump("NAME");?></th>
	<th><?php $l->dump("AUTH");?></th>
	<th><?php $l->dump("EMAIL");?></th>
	<th><?php $l->dump("PHONE");?></th>
	<th style="width:200px;"><?php $l->dump("LOCALIZATION");?></th>
	<th><?php $l->dump("PASSWORD");?></th>
	<th><?php $l->dump("USERNAME");?></th>
	<th><?php $l->dump("DELETE");?></th>
</tr>
<?php
	foreach(Database::readAll("app_users_list")->data as $u){
		$itself = ($_SESSION['account']['id'] == $u["id"]?true:false);
		echo('
		<tr name="'.$u["id"].'">
			<td><input class="qtyin name" type="text" name="name_'.$u["id"].'" value="'.$u["name"].'" placeholder="'.$l->get("CLICK_TOE").'"></td>
			<td><span title="'.($u["group"] == 0?$l->get("DONOT_CH_SU"):'').'">'.Accounts::getGroupName($u["group"]).'</span></td>
			<td><input class="qtyin mail" type="text" name="mail_'.$u["id"].'" value="'.$u["email"].'" placeholder="'.$l->get("CLICK_TOE").'"></td>
			<td><input class="qtyin phone" type="text" name="phone_'.$u["id"].'" value="'.$u["phone"].'" placeholder="'.$l->get("CLICK_TOE").'"></td>
			<td style="width:200px;">'.LangManager::getForm("lang_".$u["id"],$u["lang"],false,false,false).'</td>
			<td><input class="qtyin pwd" autocomplete="new-password" type="password" name="pwd_'.$u["id"].'" placeholder="'.$l->get("UNCHANGED").'"></td>
			<td><input class="qtyin uname" type="text" name="uname_'.$u["id"].'" value="'.$u["username"].'" placeholder="'.$l->get("CLICK_TOE").'"></td>
			<td style="text-align:center;font-size:14pt;font-weight:bold;" class="'.($itself?"":"click_available delUser").'">'.($itself?"":"<i class=\"fa fa-trash-o\"></i>").'</td>
		</tr>
		');
	}
?>
<tr class="addBigButton" style="display:none;"></tr>
</table>
</div>
<div style="text-align:center;margin-top:25px;"><button class="btn btn-info new_user"><?php $l->dump("new_user")?></button></div>
<script>
function fail(){
	showMessage("<?php $l->dump("CH_NOT_SAVED");?>","danger");
}
function succ(){	
	showMessage("<?php $l->dump("CH_SAVED");?>","success");
}
</script>