<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/* Register SU if no any user found */
if(file_exists(__ROOTDIR."/create.admin")){
	$ctn=unserialize(base64_decode(file_get_contents(__ROOTDIR."/create.admin")));
	if($ctn !== false){
		unlink(__ROOTDIR."/create.admin");
		Database::newRow("app_users_list", 
			Database::read("app_users_grouplist","id","level",0),
			"Administrator","","","def",password_hash($ctn["password"], PASSWORD_BCRYPT),$ctn["username"],1,time());
	}
}

/* Setting up user session */
$ac = Accounts::getSettings();
PuzzleSession::get()->retain_on_same_pc = $ac["f_en_remember_me"] == "on";
PuzzleSession::get()->share_on_subdomain = $ac == "on";

if(!isset($_SESSION["account"])) Accounts::rmSession();

if($_SESSION['account']['loggedIn'] == 1){
	/* Re-check the user existance */
	if(!Accounts::isUserExists($_SESSION['account']['id'])){
		Accounts::rmSession();
	}else{	
		$_SESSION['account']['email'] = Database::read("app_users_list","email","id",$_SESSION['account']['id']);
		$_SESSION['account']['phone'] = Database::read("app_users_list","phone","id",$_SESSION['account']['id']);
		$_SESSION['account']['lang'] = Database::read("app_users_list","lang","id",$_SESSION['account']['id']);
		$_SESSION['account']['name'] = Database::read("app_users_list","name","id",$_SESSION['account']['id']);
		$_SESSION['account']['group'] = Database::read("app_users_list","group","id",$_SESSION['account']['id']);
		unset($_SESSION['account']['change_pass']); //Remove any data about reset password
		unset($_SESSION['account']['confirm_activation']); //Remove any data about confirm activation
	}
}

/**
 * Automatically remove old key when email not confirmed after 10m
 */
if(isset($_SESSION['account']['confirm_activation'])){
	if ($_SESSION['account']['confirm_activation']['timeout'] + 10 * 60 < time()) {
		unset($_SESSION['account']['confirm_activation']);
	}
}
if(isset($_SESSION['account']['confirm_email'])){
	if ($_SESSION['account']['confirm_email']['timeout'] + 10 * 60 < time()) {
		unset($_SESSION['account']['confirm_email']);
	}
}
if(isset($_SESSION['account']['change_pass'])){
	if ($_SESSION['account']['change_pass']['timeout'] + 10 * 60 < time()) {
		unset($_SESSION['account']['change_pass']);
	}
}
if(isset($_SESSION['account']['change_pass']['linkClicked']))
	if($_SESSION['account']['change_pass']['linkClicked'] == 1 && AppManager::getMainApp()->appname != "users") redirect("users");
	
//Add some notice if users haven't change their password
if($_SESSION['account']['change_pass']['linkClicked'] === 1 && __getURI(1) != "changepassword"){
	$language = new Language;
	Prompt::postInfo($language->get("PCYP"),true);
	redirect("users/changepassword");
}

if(__isCLI()){
	//Automatically remove account that not activated longer than 10 minutes
	CronJob::register("rm_acc",function(){
		Database::exec("delete from `app_users_list` where enabled=0 and registered_time<'?'", time());
		Database::exec("delete from `app_users_activate` where expires<'?'", time());
	},_CT()->interval(15*T_MINUTE));
}
?>