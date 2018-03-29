<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.1") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */
 
if(__getURI(2) == "newAJAX"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();
	$rand = rand(10,999);
	Database::newRow("app_users_list",Accounts::getRootGroupId(USER_AUTH_REGISTERED),$rand,"","","def","","");
	$id = Database::read("app_users_list","id","name",$rand);
	Database::exec("UPDATE `app_users_list` SET `name`='' WHERE `name`='?';", $rand) or die();
	die($id);
}elseif(__getURI(2) == "deleteAJAX"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();
	Database::deleteRow("app_users_list","id",$_POST["name"]) or die();
	die($_POST["name"]);
}elseif(__getURI(2) == "changeName"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();		
	Database::exec("UPDATE `app_users_list` SET `name`='?' WHERE `id`='?';", $_POST["names"],$_POST["name"]) or die();
	die($_POST["name"]);
}elseif(__getURI(2) == "changeMail"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();
	filter_var($_POST["mail"], FILTER_VALIDATE_EMAIL) or die();
	Database::exec("UPDATE `app_users_list` SET `email`='?' WHERE `id`='?';", $_POST["mail"], $_POST["name"]) or die();
	die($_POST["name"]);
}elseif(__getURI(2) == "changePhone"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();	
	if($_POST["phone"] == "") die($_POST["name"]);
	preg_match("/^(?!(?:\d*-){5,})(?!(?:\d* ){5,})\+?[\d- ]+$/",$_POST["phone"]) or die();
	Database::exec("UPDATE `app_users_list` SET `phone`='?' WHERE `id`='?';", $_POST["phone"], $_POST["name"]) or die();
	die($_POST["name"]);
}elseif(__getURI(2) == "changeLocal"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();		
	Database::exec("UPDATE `app_users_list` SET `lang`='?' WHERE `id`='?';",$_POST["local"],$_POST["name"]) or die();
	die($_POST["name"]);
}elseif(__getURI(2) == "changeUname"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();
	if($_POST["uname"] == "") die();
	if(Database::read("app_users_list","username","username",$_POST["uname"]) != "") die();
	Database::exec("UPDATE `app_users_list` SET `username`='?' WHERE `id`='?';", $_POST["uname"], $_POST["name"]) or die();
	die($_POST["name"]);
}elseif(__getURI(2) == "changePass"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();	
	if($_POST["paswd"] == "") die();	
	Database::exec("UPDATE `app_users_list` SET `password`='?' WHERE `id`='?';", Accounts::hashPassword($_POST["paswd"]),$_POST["name"]) or die();
	die($_POST["name"]);
}elseif(__getURI(2) == "changeGroup"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();
	(Accounts::getAuthLevel($_POST["toGroup"]) <= USER_AUTH_REGISTERED) or die();
	if($_POST["uid"] == $_SESSION['account']['id']) die(); //Donot allow user to change group by itself
	Database::exec("UPDATE `app_users_list` SET `group`='?' WHERE `id`='?';", $_POST["toGroup"],$_POST["uid"]) or die();
	die($_POST["uid"]);
}elseif(__getURI(2) == "rmGroup"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();
	if($_POST["gid"] == $_SESSION['account']['group']) die(); //Donot allow user to remove his group by himself
	if(Database::read("app_users_grouplist","system","id",$_POST["gid"]) == "1") die();
	if(AppManager::isOnGroup($_POST["gid"])) die("APP"); //There is some application owned by this group
	//Move all of the user back to "Registered" group
	Database::exec("UPDATE `app_users_list` SET `group`='".Accounts::getRootGroupId(USER_AUTH_REGISTERED)."' WHERE `group`='?';", $_POST["gid"]) or die();
	Database::deleteRow("app_users_grouplist","id",$_POST["gid"]) or die();
	die($_POST["gid"]);
}
elseif(__getURI(2) == "newGroup"){
	Accounts::authAccess(0) or die();
	($_POST["tf"] == "yes") or die();
	if($_POST["level"] < 1 || $_POST["level"] > 2) die();
	Database::newRow("app_users_grouplist",$_POST["name"],$_POST["level"]) or die();
	$l = new Language;
	Prompt::postGood($l->get("ch_saved"),true);
	die("SUCC");
}

?>