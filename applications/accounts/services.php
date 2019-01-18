<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/** Register SU if no any user found */
if (file_exists(__ROOTDIR . "/create.admin")) {
	$ctn = unserialize(base64_decode(file_get_contents(__ROOTDIR . "/create.admin")));
	if ($ctn !== false) {
		unlink(__ROOTDIR . "/create.admin");
		Database::insert("app_users_list", [
			(new DatabaseRowInput)
				->setField("group", Database::read("app_users_grouplist", "id", "level", 0))
				->setField("name", "Administrator")
				->setField("lang", "def")
				->setField("password", password_hash($ctn["password"], PASSWORD_BCRYPT))
				->setField("username", $ctn["username"])
				->setField("registered_time", time())
		]);
	}
}

/** Setting up user session */
$ac = Accounts::getSettings();
PuzzleSession::get()->retain_on_same_pc = $ac["f_en_remember_me"] == "on";
PuzzleSession::get()->share_on_subdomain = $ac == "on";

if (!isset($_SESSION["account"])) Accounts::rmSession();

if ($_SESSION['account']['loggedIn'] == 1) {
	/** Re-check the user existance */
	if (!Accounts::isUserExists($_SESSION['account']['id'])) {
		Accounts::rmSession();
	} else {
		$profile = Database::getRow("app_users_list", "id", $_SESSION['account']['id']);
		$_SESSION['account']['email'] = $profile["email"];
		$_SESSION['account']['phone'] = $profile["phone"];
		$_SESSION['account']['lang'] = Accounts::getSettings()["f_profile_language"] == "on" ? $profile["lang"] : POSConfigGlobal::$default_language;
		$_SESSION['account']['name'] = $profile["name"];
		$_SESSION['account']['group'] = $profile["group"];
		unset($_SESSION['account']['change_pass']); //Remove any data about reset password
		unset($_SESSION['account']['confirm_activation']); //Remove any data about confirm activation
	}
}

/** Automatically remove old key when confirmation key not activated after 10m */
if (isset($_SESSION['account']['confirm_activation'])) {
	if ($_SESSION['account']['confirm_activation']['timeout'] + 10 * 60 < time()) {
		unset($_SESSION['account']['confirm_activation']);
	}
}
if (isset($_SESSION['account']['confirm_email'])) {
	if ($_SESSION['account']['confirm_email']['timeout'] + 10 * 60 < time()) {
		unset($_SESSION['account']['confirm_email']);
	}
}
if (isset($_SESSION['account']['change_pass'])) {
	if ($_SESSION['account']['change_pass']['timeout'] + 10 * 60 < time()) {
		unset($_SESSION['account']['change_pass']);
	}
}
if (isset($_SESSION['account']['change_pass']['linkClicked']))
	if ($_SESSION['account']['change_pass']['linkClicked'] == 1 && request("app") != "users") redirect("users");

/** Add some notice if users haven't change their password */
if ($_SESSION['account']['change_pass']['linkClicked'] === 1 && request(1) != "changepassword") {
	$language = new Language;
	Prompt::postInfo($language->get("PCYP"), true);
	redirect("users/changepassword");
}

if (is_cli()) {
	/** Automatically remove account that not activated more than 10 minutes */
	CronJob::register("rm_acc", function () {
		Database::execute("delete from `app_users_list` where enabled=0 and registered_time<'?'", time());
		Database::execute("delete from `app_users_activate` where expires<'?'", time());
	}, _CT()->interval(15 * T_MINUTE));
}