<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.2") or die("You need to upgrade the system");
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

if(!isset($_SESSION["account"])){
	Accounts::rmSession();
}

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
	}
}

/**
 * Automatically remove old key when email not confirmed after 10m
 */
 
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
	if($_SESSION['account']['change_pass']['linkClicked'] == 1 && __getURI("app") != "users") redirect("users");
?>