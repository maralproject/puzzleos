<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

$language = new Language;

if (!$_SESSION['account']['loggedIn']) {
	if ((request("action") == "changepassword") && $_SESSION['account']['change_pass']['linkClicked'] === 1) {
		require("views/change_reset_password.php");
	} elseif ((request("action") == "forgot")) {
		require("views/reset_password_form.php");
	} elseif ((request("action") == "verify") && (isset($_SESSION["account"]["confirm_activation"]) || isset($_SESSION["account"]["change_pass"]) || isset($_SESSION["account"]["tfa"]))) {
		require("views/code_verification.php");
	} elseif (request("action") == "signup" && Accounts::getSettings()["f_en_registration"] == "on") {
		require("views/signup.php");
	} else {
		require("views/main_login.php");
	}
} else {
	if (request("action") == "changepassword") {
		require("views/change_password.php");
	} elseif ((request("action") == "verify") && (isset($_SESSION["account"]["confirm_email"]))) {
		require("views/code_verification.php");
	} else {
		require("views/change_info.php");
	}
}