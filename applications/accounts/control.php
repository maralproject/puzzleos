<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.3") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.1.3
 */
 
/**
* Since version 1.1.2, Accounts class always available to everyone
*/

if(__getURI("app") == $appProp->appname){

	$language = new Language;
	
	if(!$_SESSION['account']['loggedIn']){
		if(__getURI("action") == "changepassword"){
			Template::setSubTitle($language->get("c_pass"));
		}else if(__getURI("action") == "forgot"){
			Template::setSubTitle($language->get("nh"));
		}else{
			Template::setSubTitle($language->get("login"));
		}
	}	
	
	if(!isset($_POST["trueLogin"])) $_POST["trueLogin"] = 0;
	if(__getURI("action") == "signup" && !$_SESSION['account']['loggedIn'] && $_POST["trueLogin"] == "1" && Accounts::getSettings()["f_en_registration"] == "on"){
		/**
		 * Signup action
		 * URI	: /users/signup
		 * Note	: -
		 */
		
		if($_POST['fullname'] == ""){
			Prompt::postError("Nama lengkap tidak boleh kosong");
		}elseif($_POST['user'] == ""){
			Prompt::postError("Username tidak boleh kosong");
		}elseif($_POST['password'] != $_POST['password2']){
			Prompt::postError("Password tidak sama");
		}elseif(Database::read("app_users_list","username","username",$_POST["user"]) != ""){
			Prompt::postError("Nama user sudah dipakai");
		}elseif(Database::read("app_users_list","email","email",$_POST["email"]) != ""){
			Prompt::postError("Email sudah dipakai");
		}else{
			if(Accounts::getSettings()["f_en_recaptcha"] == "on"){
				if(!Accounts::verifyRecapctha()){
					Prompt::postError("Verifikasi manusia gagal");
					return;
				}
			}
			
			$require_activation = Accounts::getSettings()["f_reg_activate"] == "on";
			if($require_activation || Accounts::getSettings()["f_reg_required1"] == "on"){
				if(!filter_var($_POST["email"],FILTER_VALIDATE_EMAIL) || $_POST["email"] == ""){
					Prompt::postError("Email wajib diisi");
					return;
				}
			}
			
			$group_reg = Accounts::getSettings()["f_reg_group"] == "" ? Accounts::getRootGroupId(USER_AUTH_REGISTERED) : Accounts::getSettings()["f_reg_group"];
			Database::newRow("app_users_list",
				$group_reg,
				$_POST["fullname"],
				($require_activation?$_POST["email"]:""),
				"",
				"def", 
				Accounts::hashPassword($_POST["password"]),
				$_POST["user"],
				($require_activation?"0":1),
				(time() + 600)
			);		
			$f_id = Database::getLastId("app_users_list","id");
		
			$length = 128;
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			$mailer = new Mailer;
			$mailer->addRecipient = $_POST['email'];
			$mailer->subject = $language->get("CYNE");
			
			if($require_activation){
				unset($_SESSION['account']['confirm_activation']);
				$link = __SITEURL ."/users/activate/".$randomString;
				$_SESSION['account']['confirm_activation']['email'] = $_POST['email'];
				$_SESSION['account']['confirm_activation']['key'] = $randomString;
				$_SESSION['account']['confirm_activation']['id'] = $f_id;
				$_SESSION['account']['confirm_activation']['timeout'] = time();
				ob_start();
				require( $appProp->path . "/mail_template/activate.php");						
				$mailer->body = ob_get_clean();
				if($mailer->sendHTML() == 1){
					Prompt::postGood($language->get("ECHS").$_POST['email'],true);
				}else{
					Prompt::postError($language->get("CSCE"),true);
				}
				
				/* Redirect to login page */
				redirect("users/login?signup=success&redir=" . $_POST["redir"]);
			}else{
				if(Accounts::getSettings()["f_reg_required1"] == "on" && $_POST["email"] != ""){
					unset($_SESSION['account']['confirm_email']);
					$link = __SITEURL ."/users/verifyemail/".$randomString;
					$_SESSION['account']['confirm_email']['new'] = $_POST['email'];
					$_SESSION['account']['confirm_email']['id'] = $f_id;
					$_SESSION['account']['confirm_email']['key'] = $randomString;
					$_SESSION['account']['confirm_email']['timeout'] = time();
					ob_start();
					require( $appProp->path . "/mail_template/confirm_email.php");
					$mailer->body = ob_get_clean();
					$mailer->sendHTML();
				}
				
				/* Authenticate */
				Accounts::addSession($f_id);
				redirect($_POST["redir"]);
			}
		}
		 
	}elseif(__getURI("action") == "activate" && !$_SESSION['account']['loggedIn']){
		/**
		 * Activate email address
		 * URI	: /users/activate/$rand
		 * Note	: -
		 */
		 
		if(__getURI(2) == $_SESSION['account']['confirm_activation']['key']){
			if($_SESSION['account']['confirm_activation']['timeout'] + 10 * 60 < time()) {
				unset($_SESSION['account']['confirm_activation']);
			}else{
				Database::exec("UPDATE `app_users_list` SET enabled=1 WHERE `email`='?';",$_SESSION['account']['confirm_activation']['email']);
				unset($_SESSION['account']['confirm_activation']);
				Prompt::postGood($language->get("E_CH2"),true);
			}
		}
		redirect("users/login");
	}elseif(__getURI("action") == "login" && $_POST["trueLogin"] == "1"){
		/**
		 * Login Action
		 * URI	: /users/login
		 * Note	: -
		 */
		if(!isset($_POST['redir'])) $_POST['redir'] = "";
		
		if($_SESSION['account']['loggedIn']){
			//Don't allow user to login again once loggedin
			redirect("users");
		}
		
		if($_POST["user"] != "" && $_POST["pass"] != ""){
			$en_recaptcha = Accounts::getSettings()["f_en_recaptcha"] == "on";
			if($en_recaptcha){
				if(!Accounts::verifyRecapctha()){
					Prompt::postError("Verifikasi manusia gagal");
					return;
				}
			}
			
			if(Accounts::authUserId($_POST['user'],$_POST['pass'])){
				Accounts::addSession(Accounts::findUserID($_POST['user']));
				if($_POST['redir'] == ""){
					redirect();
				}else{
					redirect(html_entity_decode($_POST['redir']));
				}
			}else{
				$GLOBALS["ULFailed"] = true;
				Prompt::postError($language->get("dcyc"));
			}
		}else{
			$GLOBALS["ULFailed"] = true;
			Prompt::postError($language->get("dcyc"));
		}
	}elseif(__getURI("action") == "update" && Accounts::authAccess(USER_AUTH_SU)){
		/**
		 * Update configuration action
		 * URI	: /users/update
		 * Note	: Only superuser allowed to do this
		 */
		 
		$o = [];
		foreach($_POST as $k=>$d){
			if(substr($k,0,2) == "f_") $o[$k] = $d;
		}
		
		if(UserData::store("settings",json_encode($o),"json",true)){
			PuzzleOSGlobal::$session->endAll();
			PuzzleOSGlobal::$session->open();
			PuzzleOSGlobal::$session->write_cookie();
			Prompt::postGood("Pengaturan telah diperbarui",true);
		}else{
			Prompt::postError("Aksi gagal",true);
		}
		redirect("admin/manage/users");
	}elseif(__getURI("action") == "logout"){
		/**
		 * Logout Action
		 * URI	: /users/logout
		 * Note	: -
		 */
		 
		if(!$_SESSION['account']['loggedIn']){
			//If user not logged in, abort this action
			redirect();
		}
		Accounts::rmSession();
		redirect();
	}else if(__getURI("action") == "ajax"){
		/**
		 * Logout Action
		 * URI	: /users/ajax
		 * Note	: A part of panel.admin.php which used in admin panel
		 */
		 
		require( $appProp->path . "/ajax.php");
	}elseif(__getURI("action") == "profile" && isset($_POST["tf"]) && $_POST["ineedtochangesettings"] == "pass" && $_SESSION['account']['loggedIn']){
		/**
		 * Change user account settings
		 * URI	: /users/profile
		 * Note	: -
		 */
		if($_POST["tf"] == "1"){			
			if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) && $_POST["email"] != ""){
				Prompt::postError($language->get("EMAIL_NOT_VALID"),true);
			}else if(!preg_match("/^(?!(?:\d*-){5,})(?!(?:\d* ){5,})\+?[\d- ]+$/",$_POST["phone"]) && $_POST["phone"] != ""){
				Prompt::postWarn($language->get("CHECK_YOUR_PHONE"),true);
			}else{
				if(Accounts::getSettings()["f_reg_required1"] == "on" && $_POST["email"] == ""){
					Prompt::postError($language->get("emailE"),true);
				}else if($_POST["email"] != "" && Database::read("app_users_list","id","email",$_POST["email"]) != "" && Database::read("app_users_list","id","email",$_POST["email"]) != $_SESSION["account"]["id"]){
					Prompt::postError("Email sudah dipakai",true);
				}else if(Accounts::getSettings()["f_reg_required2"] == "on" && $_POST["phone"] == ""){
					Prompt::postWarn($language->get("CHECK_YOUR_PHONE"),true);
				}else if($_POST["phone"] != "" && Database::read("app_users_list","id","phone",$_POST["phone"]) != "" && Database::read("app_users_list","id","phone",$_POST["phone"]) != $_SESSION["account"]["id"]){
					Prompt::postError("Telepon sudah dipakai",true);
				}else if($_POST['name'] == ""){
					Prompt::postWarn("Nama Lengkap tidak boleh kosong",true);
				}else{
					Database::exec("UPDATE `app_users_list` SET `name`='?', `lang`='?', `phone`='?' WHERE `id`='?'", $_POST['name'], $_POST['lang'], $_POST['phone'], $_SESSION['account']['id']);
					$_SESSION['account']['phone'] = $_POST['phone'];
					$_SESSION['account']['name'] = $_POST['name'];
					$_SESSION['account']['lang'] = $_POST['lang'];
					Prompt::postGood($language->get("CH_SAVED"),true);
					if($_POST['email'] != $_SESSION['account']['email'] && $_POST['email'] != ""){
						unset($_SESSION['account']['confirm_email']);
						$length = 128;
						$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						$charactersLength = strlen($characters);
						$randomString = '';
						for ($i = 0; $i < $length; $i++) {
							$randomString .= $characters[rand(0, $charactersLength - 1)];
						}
						$_SESSION['account']['confirm_email']['new'] = $_POST['email'];
						$_SESSION['account']['confirm_email']['id'] = $_SESSION['account']['id'];
						$_SESSION['account']['confirm_email']['key'] = $randomString;
						$_SESSION['account']['confirm_email']['timeout'] = time();
						$link = __SITEURL ."/users/verifyemail/".$randomString;
						$mailer = new Mailer;
						$mailer->addRecipient = $_POST['email'];
						$mailer->subject = $language->get("CYNE");
						ob_start();
						require( $appProp->path . "/mail_template/confirm_email.php");						
						$mailer->body = ob_get_clean();
						if($mailer->sendHTML() == 1){
							Prompt::postGood($language->get("ECHS").$_POST['email'],true);
						}else{					
							Prompt::postError($language->get("CSCE"),true);
						}
					}else if($_POST["email"] == ""){						
						Database::exec("UPDATE `app_users_list` SET `email`='?' WHERE `id`='?';", "", $_SESSION['account']['id']);
						$_SESSION['account']['email'] = "";
					}
				}
			}
			
		}else{
			redirect("users");
		}
		
		redirect("users/profile");
	}elseif(__getURI("action") == "verifyemail" && $_SESSION['account']['loggedIn']){
		/**
		 * Verify email address
		 * URI	: /users/verifyemail/$rand
		 * Note	: -
		 */
		 
		if(__getURI(2) == $_SESSION['account']['confirm_email']['key']){
			if($_SESSION['account']['confirm_email']['timeout'] + 10 * 60 < time()) {
				unset($_SESSION['account']['confirm_email']);
			}else{
				Database::exec("UPDATE `app_users_list` SET `email`='?' WHERE `id`='?'", $_SESSION['account']['confirm_email']['new'], $_SESSION['account']['confirm_email']['id']);
				$_SESSION['account']['email'] = $_SESSION['account']['confirm_email']['new'];
				unset($_SESSION['account']['confirm_email']);
				Prompt::postGood($language->get("E_CH"),true);
			}
		}		
		redirect("users");
	}elseif(__getURI("action") == "forgot" && isset($_POST["realforgotpaswd"]) && $_POST["datafromforgotout"] == "1"){
		/**
		 * Forgot password
		 * URI	: /users/forgot
		 * Note	: This will send email and confirm it at /users/changepassword/$rand
		 */
		
		if($_POST["realforgotpaswd"] == 1){		
			if($_SESSION['account']['loggedIn']){
				//Prevent forgot password from this way when logged in
				redirect("users");
				die();
			}
			$userid = Database::read("app_users_list","id","email",$_POST['email']);
			if($userid == "" || $_POST["email"] == ""){
				Prompt::postWarn($language->get("naawth"));
			}else{
				unset($_SESSION['account']['change_pass']);
				$_SESSION['account']['change_pass']['linkClicked'] = 0;
				$length = 128;
				$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$charactersLength = strlen($characters);
				$randomString = '';
				for ($i = 0; $i < $length; $i++) {
					$randomString .= $characters[rand(0, $charactersLength - 1)];
				}
				$_SESSION['account']['change_pass']['key'] = $randomString;
				$_SESSION['account']['change_pass']['id'] = Database::read("app_users_list","id","email",$_POST['email']);
				$_SESSION['account']['change_pass']['timeout'] = time();
				$link = __SITEURL . "/users/changepassword/".$randomString;
				$send = new Mailer;
				$send->addRecipient = $_POST['email'];
				$send->subject = $language->get("prr");
				ob_start();
				require( $appProp->path . "/mail_template/reset_password.php");			
				$send->body = ob_get_clean();
				if($send->sendHTML() == 1){
					Prompt::postGood($language->get("PRLHS"));
				}else{			
					Prompt::postError($language->get("CSCE"));
				}
			}
		}
	}elseif(__getURI("action") == "changepassword" && isset($_POST["realcpass"]) && $_POST["datafromresetpassafterverify"] == "ok"){
		/**
		 * Change password from forgot password and from inside
		 * URI	: /users/changepassword
		 * Note	: -
		 */
		
		if($_POST["realcpass"] == "1"){		
			$changePass_LC = 0;
			if(isset($_SESSION['account']['change_pass']['linkClicked']))
				$changePass_LC = $_SESSION['account']['change_pass']['linkClicked'];
			
			if(!$_SESSION['account']['loggedIn']){
				//If user not logged in and don't have the link, delete all session and redirect to home
				if($changePass_LC != 1){
					redirect("users");
				}
			}
						
			if((!Accounts::verifyHashPass($_POST['passold'], Database::read("app_users_list","password","id",$_SESSION['account']['id']))) && ($changePass_LC != 1)){
				Prompt::postError($language->get("DCYP"));
			}else{
				if($_POST['passnew'] != ""){
					if($_POST['passnew'] != $_POST['passver']){
						Prompt::postError($language->get("pnm"));
					}else{			
						if($changePass_LC == 1){
							Database::exec("UPDATE `app_users_list` SET `password`='?' WHERE `id`='?';", Accounts::hashPassword($_POST['passnew']),$_SESSION['account']['change_pass']['id']);			
							unset($_SESSION['account']['change_pass']);
							redirect("users");
						}else{
							Database::exec("UPDATE `app_users_list` SET `password`='?' WHERE `id`='?';",Accounts::hashPassword($_POST['passnew']),$_SESSION['account']['id']);			
							Prompt::postGood($language->get("pass_changed"));
						}
					}
				}else{
					Prompt::postError($language->get("pass_E"));
				}
			}
		}else{
			redirect("users");
		}
	}elseif(__getURI("action") == "changepassword"){
		/**
		 * Respond Forgot password
		 * URI	: /users/changepassword/$rand
		 * Note	: Allow to be accessed from logged in or not state
		 */
		 
		if(!$_SESSION['account']['loggedIn']){
			if(__getURI(2) == $_SESSION['account']['change_pass']['key']){
				if ($_SESSION['account']['change_pass']['timeout'] + 10 * 60 < time()) {
					//Key is expired
					unset($_SESSION['account']['change_pass']);
					redirect("users");
					die();
				} else {
					//Time still exists
					$_SESSION['account']['change_pass']['linkClicked'] = 1;
					redirect("users","changepassword"); //Auto redirect to remove token from addressbar
					//redirect("users","ch_passdo"); //Disabled, because the view is controlled already in place
				}
			}
		}
	}
	
	/**
	 * Add some notice
	 */
	$changePass_LC = 0;
	if(isset($_SESSION['account']['change_pass']['linkClicked'])) $changePass_LC = $_SESSION['account']['change_pass']['linkClicked'];
	if($changePass_LC == 1){
		Prompt::postInfo($language->get("PCYP"));
	}
}
?>