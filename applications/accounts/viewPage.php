<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.2
 */

$language = new Language;

if(!$_SESSION['account']['loggedIn']){
	if((__getURI("action") == "changepassword") && $_SESSION['account']['change_pass']['linkClicked'] === 1){
		require("views/change_reset_password.php");
	}elseif((__getURI("action") == "forgot")){
		require("views/reset_password_form.php");
	}elseif((__getURI("action") == "verify") && (isset($_SESSION["account"]["confirm_activation"]) || isset($_SESSION["account"]["change_pass"]))){
		require("views/code_verification.php");
	}elseif(__getURI("action") == "signup" && Accounts::getSettings()["f_en_registration"] == "on"){
		require("views/signup.php");
	}else{
		require("views/main_login.php");
	}
}else{
	if(__getURI("action") == "changepassword") {
		require("views/change_password.php");
	}elseif((__getURI("action") == "verify") && (isset($_SESSION["account"]["confirm_email"]))){
		require("views/code_verification.php");
	}else{
		require("views/change_info.php");
	}
}
?>