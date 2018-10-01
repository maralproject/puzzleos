<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

define("MENU_DEFAULT_POSITION_LEFT", 0);
define("MENU_DEFAULT_POSITION_TOP", 1);
define("MENU_DEFAULT_POSITION_RIGHT", 2);
define("MENU_DEFAULT_POSITION_BOTTOM", 3);

//Do nothing here
if($appProp->isMainApp){
	if(__getURI("action") == "new"){
		if($_POST["trueData"] == "yes"){
			$r = rand(0,999);
			Database::newRow("app_menus_main",$r,"","tags",Accounts::getRootGroupId(USER_AUTH_PUBLIC),0) or die();
			$id = Database::read("app_menus_main","id","name",$r);
			Database::exec("UPDATE `app_menus_main` SET `name`='' WHERE `name`='?';", $r) or die();			
			die($id);
		}
		die(1);
	}else if(__getURI("action") == "delete"){
		($_POST["trueData"] == "yes") or die();
		Database::deleteRow("app_menus_main","id",$_POST["name"]) or die();
		die($_POST["name"]);
	}else if(__getURI("action") == "changeIcon"){
		($_POST["trueData"] == "yes") or die();
		Database::exec("UPDATE `app_menus_main` SET `fa`='?' WHERE `id`='?';",$_POST["val"],$_POST["name"]) or die();			
		die($_POST["name"]);
	}else if(__getURI("action") == "changeName"){
		($_POST["trueData"] == "yes") or die();
		Database::exec("UPDATE `app_menus_main` SET `name`='?' WHERE `id`='?';",$_POST["val"],$_POST["name"]) or die();			
		die($_POST["name"]);			
	}else if(__getURI("action") == "changeLink"){
		($_POST["trueData"] == "yes") or die();
		Database::exec("UPDATE `app_menus_main` SET `link`='?' WHERE `id`='?';",$_POST["val"],$_POST["name"]) or die();			
		die($_POST["name"]);			
	}else if(__getURI("action") == "changeAuth"){
		($_POST["trueData"] == "yes") or die();
		Database::exec("UPDATE `app_menus_main` SET `minUser`='?' WHERE `id`='?';",$_POST["val"],$_POST["name"]) or die();			
		die($_POST["name"]);	
	}else if(__getURI("action") == "changePos"){
		($_POST["trueData"] == "yes") or die();
		Database::exec("UPDATE `app_menus_main` SET `location`='?' WHERE `id`='?';",$_POST["val"],$_POST["name"]) or die();			
		die($_POST["name"]);	
	}else
		return false;
}
?>