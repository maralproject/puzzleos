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

if(__getURI("app") == $appProp->appname){

	$language = new Language;
	
	if(!$_SESSION['account']['loggedIn']){
		if(__getURI("action") == "changepassword"){
			Template::setSubTitle($language->get("c_pass"));
		}else if(__getURI("action") == "forgot"){
			Template::setSubTitle($language->get("nh"));
		}else if(__getURI("action") == "verify" && (isset($_SESSION["account"]["confirm_activation"]) || isset($_SESSION["account"]["change_pass"]))){
			Template::setSubTitle($language->get("VER_CODE"));
		}else if(__getURI("action") == "signup"){
			Template::setSubTitle($language->get("signup"));
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

		if($_POST["phone"] != "") $_POST["phone"] = Accounts::getE164($_POST["phone"]);

		if($_POST["email"] != ""){
			if(Database::read("app_users_list","email","email",$_POST["email"]) != ""){
				$GLOBALS["aemsd"] = true;
				Prompt::postError($language->get("EMAIL_INV_USED"));
				return true;
			}
		}

		if($_POST["phone"] != ""){
			if(Database::read("app_users_list","phone","phone",$_POST["phone"]) != ""){
				$GLOBALS["nhpsd"] = true;
				Prompt::postError("No. Telpon sudah dipakai");
				return true;
			}
		}

		if($_POST['fullname'] == ""){
			Prompt::postError($language->get("NAME_INV_EMPTY"));
		}elseif(strlen($_POST['fullname']) > 50){
			Prompt::postError($language->get("NAME_INV_50"));
		}elseif($_POST['user'] == ""){
			Prompt::postError($language->get("USERNAME_INV_EMPTY"));
		}elseif(preg_match("/^[0-9a-z_]*+$/",$_POST["user"]) === false || strlen($_POST['fullname']) > 25){
			Prompt::postError($language->get("USERNAME_INV_CHAR"));
		}elseif(Database::read("app_users_list","username","username",$_POST["user"]) != ""){
			$GLOBALS["unmsd"] = true;
			Prompt::postError($language->get("USERNAME_INV_USED"));
		}else{
			if(Accounts::getSettings()["f_en_recaptcha"] == "on"){
				if(!Accounts::verifyRecapctha()){
					Prompt::postError($language->get("VER_CAPTCHA_ERR"));
					return true;
				}
			}

			$require_activation = Accounts::getSettings()["f_reg_activate"] == "on";

			if(Accounts::getSettings()["f_reg_required1"] == "on" || Accounts::$customM_UE){
				if(!filter_var($_POST["email"],FILTER_VALIDATE_EMAIL) || $_POST["email"] == ""){
					Prompt::postError($language->get("EMAIL_INV"));
					return true;
				}
			}

			if(Accounts::getSettings()["f_reg_required2"] == "on" || Accounts::$customM_UP){
				if(preg_match("/^[0-9\+]{8,15}$/", $_POST["phone"]) === FALSE || $_POST["phone"] == "") {
					Prompt::postError($language->get("PHONE_INV"));
					return true;
				}
			}

			$group_reg = Accounts::getSettings()["f_reg_group"] == "" ? Accounts::getRootGroupId(USER_AUTH_REGISTERED) : Accounts::getSettings()["f_reg_group"];
			Database::newRow("app_users_list",
				$group_reg,
				$_POST["fullname"],
				($require_activation?$_POST["email"]:""),
				($_POST["phone"]),
				"def",
				Accounts::hashPassword($_POST["password"]),
				$_POST["user"],
				($require_activation?"0":1),
				(time() + 600)
			);
			$f_id = Database::getLastId("app_users_list","id");
			
			if($require_activation){
				$act_code['confirm_activation']['email'] = $_POST['email'];
				if(Accounts::$customM_EN){
					$act_code['confirm_activation']['key'] = rand_str(6, "9012345678");
				}else{
					$act_code['confirm_activation']['key'] = rand_str(128);
				}
				$act_code['confirm_activation']['id'] = $f_id;
				$act_code['confirm_activation']['timeout'] = time();

				if(!Accounts::$customM_EN){
					Database::newRow("app_users_activate",$act_code['confirm_activation']['key'],json_encode($act_code),time());
					$link = __SITEURL ."/users/activate/".$act_code['confirm_activation']['key'];
					
					$path = $appProp->path;
					$email = $_POST['email'];
					
					$w = new Worker;
					$w->setTask(function($id,$app) use($path,$email,$link){
						new Application("phpmailer");
						$language = new Language($app);
						ob_start();
						include("$path/mail_template/activate.php");
						$mailer = new Mailer;
						$mailer->addRecipient = $email;
						$mailer->subject = $language->get("CYNE");
						$mailer->body = ob_get_clean();
						return $mailer->sendHTML();
					})->run(["standalone"=>true]);
					
					/* Redirect to login page */
					$_SESSION['account']['signup_emailsent'] = $act_code['confirm_activation']['key'];
					Prompt::postGood($language->get("ECHS").$email,true);
					redirect("users/login?redir=" . $_POST["redir"]);
				}else{
					$aclb = Accounts::$customM_F;
					$aclbr = $aclb($customM_UE ? $_POST['email'] : $_POST["phone"],$act_code['confirm_activation']['key']);
					if($aclbr === false){
						Prompt::postError($language->get("VER_ERR_SEND"),true);
						Database::deleteRow("app_users_list","id",$f_id); //Cancelling account creation
						unset($_SESSION["account"]["confirm_activation"]);
						redirect("users/login?redir=" . $_POST["redir"]);
					}else{
						$_SESSION["account"]["confirm_activation"] = $act_code["confirm_activation"];
						$_SESSION["account"]["confirm_activation"]["msg"] = $aclbr;
						$_SESSION['account']['confirm_activation']['timeout'] = time();
						redirect("users/verify?redir=" . $_POST["redir"]);
					}
				}
			}else{
				if($_POST["email"] != ""){
					$act_code['confirm_email']['new'] = $_POST['email'];
					$act_code['confirm_email']['id'] = $f_id;
					$act_code['confirm_email']['key'] = rand_str(128);
					$act_code['confirm_email']['timeout'] = time();
					$link = __SITEURL ."/users/verifyemail/".$act_code['confirm_email']['key'];

					Database::newRow("app_users_activate",$act_code['confirm_email']['key'],json_encode($act_code),time()+ 10 * T_MINUTE);
					
					$path = $appProp->path;
					$email = $_POST['email'];
					
					$w = new Worker;
					$w->setTask(function($id,$app) use($path,$email,$link){
						new Application("phpmailer");
						$language = new Language($app);
						ob_start();
						include("$path/mail_template/confirm_email.php");
						$mailer = new Mailer;
						$mailer->addRecipient = $email;
						$mailer->subject = $language->get("CYNE");
						$mailer->body = ob_get_clean();
						return $mailer->sendHTML();
					})->run(["standalone"=>true]);
				}

				/* Authenticate */
				Accounts::addSession($f_id);
				redirect($_POST["redir"]);
			}
		}

	}elseif(__getURI("action") == "activate" && !$_SESSION['account']['loggedIn']){
		/**
		 * Activate account
		 * URI	: /users/activate/$rand
		 * Note	: -
		 */

		$cv = $_POST["verification_confirm"] == "1" ? true : false;
		$token = ($cv?$_POST["code_input_usr"]:__getURI(2));
		$token_e = $cv ? ($_SESSION["account"]["confirm_activation"]["key"] == $token) : (Database::read("app_users_activate","id","id",$token) != "");

		if($token_e){
			if(!$cv) $act_code = json_decode(Database::read("app_users_activate","content","id",$token),true);
			else $act_code["confirm_activation"] = $_SESSION["account"]["confirm_activation"];

			Database::exec("UPDATE `app_users_list` SET enabled=1 WHERE `id`='?'",$act_code['confirm_activation']['id']);

			if(!$cv){
				Database::deleteRow("app_users_activate","id",$token);
				Prompt::postGood($language->get("E_CH2"),true);
				redirect("users/login");
			}else{
				unset($_SESSION["account"]["confirm_activation"]);
				Prompt::postGood($language->get("VER_SUCCEED"),true);
				redirect("users/login?redir=" . $_POST["redir"]);
			}
		}else{
			if(!$cv)
				redirect("users/login");
			else{
				$_SESSION["account"]["confirm_activation"]["wrong"] = true;
				Prompt::postError($language->get("VER_CODE_INV"),true);
				redirect("users/verify?redir=" . $_POST["redir"]);
			}
		}
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
					Prompt::postError($language->get("VER_CAPTCHA_ERR"));
					return;
				}
			}

			if(Accounts::authUserId($_POST['user'],$_POST['pass'])){
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
			POSGlobal::$session->endAll();
			POSGlobal::$session->open();
			POSGlobal::$session->write_cookie();
			Prompt::postGood($language->get("SETTINGS_UPDATED"),true);
		}else{
			Prompt::postError($language->get("ACT_ERR"),true);
		}
		redirect("admin/manage/users");
	}elseif(__getURI("action") == "logout"){
		/**
		 * Logout Action
		 * URI	: /users/logout
		 * Note	: -
		 */

		if(!$_SESSION['account']['loggedIn']){
			redirect(); //If user not logged in, abort this action
		}
		Accounts::rmSession();
		redirect();
	}else if(__getURI("action") == "ajax"){
		/**
		 * Ajax Action
		 * URI	: /users/ajax
		 * Note	: A part of panel.admin.php which used in admin panel
		 */
		
		if(Accounts::authAccess(USER_AUTH_SU)) require( $appProp->path . "/ajax.php");
	}elseif(__getURI("action") == "profile" && isset($_POST["tf"]) && $_POST["ineedtochangesettings"] == "pass" && $_SESSION['account']['loggedIn']){
		/**
		 * Change user account settings
		 * URI	: /users/profile
		 * Note	: -
		 */
		
		if($_POST["tf"] == "1"){
			if($_POST["phone"] != "") $_POST["phone"] = Accounts::getE164($_POST["phone"]);

			if($_POST["email"] != "" && !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){
				Prompt::postError($language->get("EMAIL_NOT_VALID"),true);
			}else if($_POST["phone"] != "" && preg_match("/^[0-9\+]{8,13}$/",$_POST["phone"]) === false){
				Prompt::postWarn($language->get("CHECK_YOUR_PHONE"),true);
			}else{
				if((Accounts::$customM_UE||Accounts::getSettings()["f_reg_required1"] == "on") && $_POST["email"] == ""){
					Prompt::postError($language->get("emailE"),true);
				}else if($_POST["email"] != "" && Database::read("app_users_list","id","email",$_POST["email"]) != "" && Database::read("app_users_list","id","email",$_POST["email"]) != $_SESSION["account"]["id"]){
					Prompt::postError($language->get("EMAIL_INV_USED"),true);
				}else if((Accounts::$customM_UP||Accounts::getSettings()["f_reg_required2"] == "on") && $_POST["phone"] == ""){
					Prompt::postWarn($language->get("CHECK_YOUR_PHONE"),true);
				}else if($_POST["phone"] != "" && Database::read("app_users_list","id","phone",$_POST["phone"]) != "" && Database::read("app_users_list","id","phone",$_POST["phone"]) != $_SESSION["account"]["id"]){
					Prompt::postError($language->get("PHONE_INV_USED"),true);
				}else if($_POST['name'] == ""){
					Prompt::postWarn($language->get("NAME_INV_EMPTY"),true);
				}else{
					$d = new DatabaseRowInput;
					$d->setField("name",$_POST["name"]);
					$d->setField("lang",$_POST["lang"]);
					$cf_s = -1;

					if(Accounts::$customM_EN){
						$cf = function(){
							$act_code['confirm_email']['new'] = Accounts::$customM_UE ?  $_POST['email'] : $_POST['phone'];
							$act_code['confirm_email']['id'] = $_SESSION['account']['id'];
							$act_code['confirm_email']['key'] = rand_str(6, "0123456789");
							$act_code['confirm_email']['timeout'] = time();
							$act_code['confirm_email']['camefromprofile'] = Accounts::$customM_UE ?  "email" : "phone";
							$aclb = Accounts::$customM_F;
							if($act_code['confirm_email']['new'] != ""){
								$aclbr = $aclb($act_code['confirm_email']['new'], $act_code['confirm_email']['key']);
								if($aclbr === false){
									return false;
								}else{
									$_SESSION["account"]["confirm_email"] = $act_code["confirm_email"];
									$_SESSION["account"]["confirm_email"]["msg"] = $aclbr;
									$_SESSION['account']['confirm_email']['timeout'] = time();
								}
								return true;
							}else{
								return false;
							}
						};

						if(Accounts::$customM_UE){
							if(($_POST['email'] != $_SESSION['account']['email']) && $_POST['email'] != ""){
								$cf_s = $cf();
							}
							$d->setField("phone",$_POST["phone"]);
							$_SESSION['account']['phone'] = $_POST['phone'];
						}else{
							if(($_POST['phone'] != $_SESSION['account']['phone']) && $_POST['phone'] != ""){
								$cf_s = $cf();
							}
							$d->setField("email",$_POST["email"]);
							$_SESSION['account']['email'] = $_POST['email'];
						}

					}else{
						$d->setField("phone",$_POST["phone"]);
						if($_POST['email'] != $_SESSION['account']['email'] && $_POST['email'] != ""){
							$act_code['confirm_email']['new'] = $_POST['email'];
							$act_code['confirm_email']['id'] = $_SESSION['account']['id'];
							$act_code['confirm_email']['key'] = rand_str(128);
							$act_code['confirm_email']['timeout'] = time();
							Database::newRow("app_users_activate",$act_code['confirm_email']['key'],json_encode($act_code),time()+ 10 * T_MINUTE);
							$link = __SITEURL ."/users/verifyemail/".$act_code['confirm_email']['key'];
								
							$path = $appProp->path;
							$email = $_POST['email'];
							
							$w = new Worker;
							$w->setTask(function($id,$app) use($path,$email,$link){
								new Application("phpmailer");
								$language = new Language($app);
								$mailer = new Mailer;
								$mailer->addRecipient = $email;
								$mailer->subject = $language->get("CYNE");
								ob_start();
								require("$path/mail_template/confirm_email.php");
								$mailer->body = ob_get_clean();
								return $mailer->sendHTML();
							})->run(["standalone"=>true]);
							
							Prompt::postGood($language->get("ECHS").$_POST['email'],true);
						}else if($_POST["email"] == ""){
							Database::exec("UPDATE `app_users_list` SET `email`='?' WHERE `id`='?';", "", $_SESSION['account']['id']);
							$_SESSION['account']['email'] = "";
						}
					}

					Prompt::postGood($language->get("CH_SAVED"),true);
					$_SESSION['account']['name'] = $_POST['name'];
					$_SESSION['account']['lang'] = $_POST['lang'];
					Database::updateRowAdvanced("app_users_list",$d,"id",$_SESSION['account']['id']);
					if(Accounts::$customM_EN){
						if($cf_s === false){
							Prompt::postError($language->get("VER_ERR"),true);
						}elseif($cf_s === true){
							redirect("users/verify?redir=/users");
						}
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

		$cv = $_POST["ver_emailaddr"] == "1" ? true : false;
		$token = ($cv?$_POST["code_input_usr"]:__getURI(2));
		$token_e = $cv ? ($_SESSION["account"]["confirm_email"]["key"] == $token) : (Database::read("app_users_activate","id","id",$token) != "");

		if($token_e){
			if(!$cv)
				$act_code = json_decode(Database::read("app_users_activate","content","id",__getURI(2)),true);
			else{
				$act_code["confirm_email"] = $_SESSION["account"]["confirm_email"];
			}
			$dbr = new DatabaseRowInput;

			if($act_code["confirm_email"]["camefromprofile"] != "phone"){
				$dbr->setField("email",$act_code["confirm_email"]["new"]);
				$_SESSION['account']['email'] = $act_code['confirm_email']['new'];
				Prompt::postGood($language->get("E_CH"),true);
			}else{
				$dbr->setField("phone",$act_code["confirm_email"]["new"]);
				$_SESSION['account']['phone'] = $act_code['confirm_email']['new'];
				Prompt::postGood($language->get("PHONE_SUCCEED"),true);
			}

			Database::updateRowAdvanced("app_users_list",$dbr,"id",$act_code['confirm_email']['id']);
			unset($act_code['confirm_email'], $_SESSION["account"]["confirm_email"]);
			Database::deleteRow("app_users_activate","id",__getURI(2));
		}else{
			$_SESSION["account"]["confirm_email"]["wrong"] = true;
			Prompt::postError($language->get("VER_CODE_INV"),true);
			redirect("users/verify?redir=/users");
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
			}
			$userid = Database::read("app_users_list","id","username",$_POST['user']);
			$useren = Database::read("app_users_list","enabled","username",$_POST['user']);
			if($userid == "" || $_POST["user"] == "" || $useren == "0"){
				Prompt::postError($language->get("ACC_INV_NOTFOUND"));
			}else{
				unset($_SESSION['account']['change_pass']);
				$act_code['change_pass']['linkClicked'] = 0;
				if(Accounts::$customM_EN){
					$act_code['change_pass']['key'] = rand_str(6,"8901234567");
				}else{
					$act_code['change_pass']['key'] = rand_str(128);
				}
				$act_code['change_pass']['id'] = $userid;
				$act_code['change_pass']['timeout'] = time();

				$contact_info = Accounts::$customM_UE ? Database::read("app_users_list","email","id",$userid) : Database::read("app_users_list","phone","id",$userid);
				if(!Accounts::$customM_EN){
					if($contact_info != ""){
						//If not set, by default we're sending code from email
						Database::newRow("app_users_activate",$act_code['change_pass']['key'],json_encode($act_code),time()+ 10 * T_MINUTE);
						$link = __SITEURL . "/users/changepassword/".$act_code['change_pass']['key'];
						
						$path = $appProp->path;
						$email = $_POST['email'];
						
						$w = new Worker;
						$w->setTask(function($id,$app) use($path,$email,$link,$userid){
							new Application("phpmailer");
							$language = new Language($app);
							$send = new Mailer;
							$send->addRecipient = Database::read("app_users_list","email","id",$userid);
							$send->subject = $language->get("prr");
							ob_start();
							require("$path/mail_template/reset_password.php");
							$send->body = ob_get_clean();
							return $send->sendHTML();
						})->run(["standalone"=>true]);
						
						Prompt::postGood($language->get("PRLHS"),true);
						redirect("users/forgot");
					}else{
						Prompt::postError($language->get("VER_ERR"),true);
						redirect("users/forgot");
					}
				}else{
					//If set, we're going to follow the rules by the requesting handler
					$aclb = Accounts::$customM_F;
					if($contact_info != ""){
						$aclbr = $aclb($contact_info, $act_code['change_pass']['key']);
						if($aclbr === false){
							Prompt::postError($language->get("VER_ERR_SEND"),true);
							redirect("users");
						}else{
							$_SESSION["account"]["change_pass"] = $act_code["change_pass"];
							$_SESSION["account"]["change_pass"]["msg"] = $aclbr;
							$_SESSION['account']['change_pass']['timeout'] = time();
						}
						redirect("users/verify?redir=" . $_POST["redir"]);
					}else{
						Prompt::postError($language->get("VER_ERR"),true);
						redirect("users/forgot");
					}
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
			$changePass_LC = $_SESSION['account']['change_pass']['linkClicked'] === 1 ? 1 : 0;

			if(!$_SESSION['account']['loggedIn']){
				//If user not logged in and don't have the link, delete all session and redirect to home
				if($changePass_LC != 1){
					Accounts::rmSession();
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
						$uidcp = ($changePass_LC == 1) ? $_SESSION['account']['change_pass']['id'] : $_SESSION["account"]["id"];
						Database::exec("UPDATE `app_users_list` SET `password`='?' WHERE `id`='?';", Accounts::hashPassword($_POST['passnew']),$uidcp);
						POSGlobal::$session->endUser($uidcp); //Logging out any user worldwide
						if($changePass_LC == 1){
							unset($_SESSION['account']['change_pass']);
						}
						Prompt::postGood($language->get("pass_changed"),true);
						redirect("users");
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

		if(!$_SESSION['account']['loggedIn'] && isset($_SESSION["account"]["change_pass"])){

			$cv = $_POST["ch_pass_confirm"] == 1 ? true : false;
			$token = ($cv?$_POST["code_input_usr"]:__getURI(2));
			$token_e = $cv ? ($_SESSION["account"]["change_pass"]["key"] == $token) : (Database::read("app_users_activate","id","id",$token) != "");

			if($token_e){
				if(!$cv){
					$act_code = json_decode(Database::read("app_users_activate","content","id",__getURI(2)),true);
					$_SESSION['account']['change_pass'] = $act_code["change_pass"]; //Restoring data to current session
					Database::deleteRow("app_users_activate","id",__getURI(2));
				}
				$_SESSION['account']['change_pass']['linkClicked'] = 1;
				redirect("users/changepassword");
			}else{
				if(isset($_POST["thiscamefromverify"])){
					$_SESSION["account"]["change_pass"]["wrong"] = true;
					Prompt::postError($language->get("VER_CODE_INV"),true);
					redirect("users/verify");
				}
			}
		}
	}
}
?>
