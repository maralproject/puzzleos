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
	($_POST["trueData"] == "yes") or die();

	switch(request("action")){
		case "new":
			$r = rand(0,999);
			Database::insert("app_menus_main",[
				(new DatabaseRowInput)
				->setField("name","")
				->setField("link","")
				->setField("fa","tags")
				->setField("minUser",Accounts::getRootGroupId(USER_AUTH_PUBLIC))
				->setField("location",MENU_DEFAULT_POSITION_LEFT)
			]) or die;
			$id = Database::max("app_menus_main","id");
			die($id);
		case "delete":
			Database::delete("app_menus_main","id",$_POST["name"]) or die();
			die($_POST["name"]);
		case "changeIcon":
			Database::execute("UPDATE `app_menus_main` SET `fa`='?' WHERE `id`='?';",$_POST["val"],$_POST["name"]) or die();			
			die($_POST["name"]);
		case "changeName":
			Database::execute("UPDATE `app_menus_main` SET `name`='?' WHERE `id`='?';",$_POST["val"],$_POST["name"]) or die();			
			die($_POST["name"]);			
		case "changeLink":
			Database::execute("UPDATE `app_menus_main` SET `link`='?' WHERE `id`='?';",$_POST["val"],$_POST["name"]) or die();			
			die($_POST["name"]);			
		case "changeAuth":
			Database::execute("UPDATE `app_menus_main` SET `minUser`='?' WHERE `id`='?';",$_POST["val"],$_POST["name"]) or die();			
			die($_POST["name"]);	
		case "changePos":
			Database::execute("UPDATE `app_menus_main` SET `location`='?' WHERE `id`='?';",$_POST["val"],$_POST["name"]) or die();			
			die($_POST["name"]);	
		default:
			return false;
	}
}