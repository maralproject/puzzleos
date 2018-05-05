<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.5") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.1.1
 */

$language = new Language;
$needToGoChPass = false;

if(!$_SESSION['account']['loggedIn']){
	$changePass_LC = 0;
	if(isset($_SESSION['account']['change_pass']['linkClicked'])) $changePass_LC = $_SESSION['account']['change_pass']['linkClicked'];
	if($changePass_LC == 1) $needToGoChPass = true;
	if((__getURI("action") == "changepassword") || $needToGoChPass){
		if($changePass_LC == 1){
			require_once("views/change_reset_password.php");
		}else{
			redirect("users");
		}
	}elseif((__getURI("action") == "forgot")){
		require_once("views/reset_password_form.php");
	}elseif((__getURI("action") == "verify") && (isset($_SESSION["account"]["confirm_activation"]) || isset($_SESSION["account"]["change_pass"]))){
		require_once("views/code_verification.php");
	}elseif(__getURI("action") == "signup" && Accounts::getSettings()["f_en_registration"] == "on"){
		require_once("views/signup.php");
	}else{
		require_once("views/main_login.php");
	}
}else{
	if(__getURI("action") == "changepassword") {
		require_once("views/change_password.php");
	}elseif((__getURI("action") == "verify") && (isset($_SESSION["account"]["confirm_email"]))){
		require_once("views/code_verification.php");
	}else{
		require_once("views/change_info.php");
	}
}
?>