<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

Accounts::authAccess(0) or die;
($_POST["tf"] == "yes") or die;

switch(request(2)){
	case "newAJAX":
		$rand = rand(10, 999);
		Database::insert("app_users_list",[
			(new DatabaseRowInput)
			->setField("group",Accounts::getRootGroupId(USER_AUTH_REGISTERED))
			->setField("name","")
			->setField("lang","def")
			->setField("password","")
			->setField("username","")
			->setField("registered_time",time())
		]);
		$id = Database::max("app_users_list","id");
		die($id);
	case "deleteAJAX":
		Database::delete("app_users_list","id",$_POST["name"]) or die;
		die($_POST["name"]);
	case "changeName":
		Database::update("app_users_list", (new DatabaseRowInput)->setField("name",$_POST["name"]),"id",$_POST["name"]) or exit;
		die($_POST["name"]);
	case "changeMail":
		if (Accounts::getSettings()["f_reg_required1"] == "on" && $_POST["mail"] == "") die;
		else if ($_POST["mail"] != "") filter_var($_POST["mail"], FILTER_VALIDATE_EMAIL) or die;
		Database::update("app_users_list",(new DatabaseRowInput)->setField("email",$_POST["mail"]),"id",$_POST["name"]) or die;
		die($_POST["name"]);
	case "changePhone":
		if (Accounts::getSettings()["f_reg_required2"] == "on" && $_POST["phone"] == "") die;
		else if ($_POST["phone"] != "") $_POST["phone"] = Accounts::getE164($_POST["phone"]);
		Database::update("app_users_list",(new DatabaseRowInput)->setField("phone",$_POST["phone"]),"id",$_POST["name"]) or die;
		die($_POST["name"]);
	case "changeLocal":
		Database::update("app_users_list",(new DatabaseRowInput)->setField("lang",$_POST["local"]),"id",$_POST["name"]) or die;
		die($_POST["name"]);
	case "changeUname":
		if ($_POST["uname"] == "") die;
		if (Database::read("app_users_list", "username", "username", $_POST["uname"]) != "") die;
		Database::update("app_users_list",(new DatabaseRowInput)->setField("username",$_POST["uname"]),"id",$_POST["name"]) or die;
		die($_POST["name"]);
	case "changePass":
		if ($_POST["paswd"] == "") die;
		Database::update("app_users_list",(new DatabaseRowInput)->setField("password",Accounts::hashPassword($_POST["paswd"])),"id",$_POST["name"]) or die;
		die($_POST["name"]);
	case "changeGroup":
		(Accounts::getAuthLevel($_POST["toGroup"]) <= USER_AUTH_REGISTERED) or die;
		if ($_POST["uid"] == $_SESSION['account']['id']) die; //Donot allow user to change group by itself
		Database::update("app_users_list",(new DatabaseRowInput)->setField("group",$_POST["toGroup"]),"id",$_POST["uid"]) or die;
		die($_POST["uid"]);
	case "rmGroup":
		if ($_POST["gid"] == $_SESSION['account']['group']) die; //Donot allow user to remove his group by himself
		if (Database::read("app_users_grouplist", "system", "id", $_POST["gid"]) == "1") die;
		if (AppManager::isOnGroup($_POST["gid"])) die("APP"); //There is some application owned by this group
		if (Accounts::getSettings()["f_reg_group"] == $_POST["gid"]) die("APP"); //Group is used by registration process
		//Move all of the user back to "Registered" group
		Database::update("app_users_list",(new DatabaseRowInput)->setField("group",Accounts::getRootGroupId(USER_AUTH_REGISTERED)),"group",$_POST["gid"]) or die;
		Database::delete("app_users_grouplist", "id", $_POST["gid"]) or die;
		die($_POST["gid"]);
	case "newGroup":
		if ($_POST["level"] < 1 || $_POST["level"] > 2) die;
		Database::insert("app_users_grouplist",[
			(new DatabaseRowInput)
			->setField("name",$_POST["name"])
			->setField("level",$_POST["level"])
		]) or die;
		Prompt::postGood((new Language)->get("ch_saved"), true);
		die("SUCC");
}
