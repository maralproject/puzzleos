<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.admin
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */
 
if(__getURI("app") == "admin"){
	$l = new Language; $l->app="admin";
	if(__getURI("action") == "changeTemplate"){
		Template::setDefaultByName(__getURI(2));
		redirect("admin#templates");
	}else if(__getURI("action") == "saveConfig"){
		if($_POST["trueForm"] == "1"){
			ConfigurationDB::$username = str_replace("'","\"",$_POST["dbuser"]);
			ConfigurationDB::$password = str_replace("'","\"",$_POST["dbpass"]);
			ConfigurationDB::$host = str_replace("'","\"",$_POST["dbhost"]);
			ConfigurationDB::$database_name = str_replace("'","\"",$_POST["dbdb"]);
			ConfigurationGlobal::$default_language = str_replace("'","\"",$_POST["deflang"]);
			ConfigurationGlobal::$sitename = str_replace("'","\"",$_POST["sitename"]);
			ConfigurationGlobal::$timezone = str_replace("'","\"",$_POST["timezone"]);
			ConfigurationGlobal::$copyright	= str_replace("'","\"",$_POST["copytext"]);
			ConfigurationGlobal::$meta_description = str_replace("'","\"",$_POST["metadesc"]);
			ConfigurationMailer::$From = str_replace("'","\"",$_POST["mailfrom"]);
			ConfigurationMailer::$Sender = str_replace("'","\"",$_POST["mailname"]);
			ConfigurationMailer::$UsePHP = ($_POST["use_smtp"]!="on"? true:false);
			ConfigurationMailer::$smtp_host = str_replace("'","\"",$_POST["smtp_host"]);
			ConfigurationMailer::$smtp_username = str_replace("'","\"",$_POST["smtp_user"]);
			ConfigurationMailer::$smtp_password = str_replace("'","\"",$_POST["smtp_pass"]);
			ConfigurationMailer::$smtp_encryption = str_replace("'","\"",$_POST["smtp_enc"]);
			ConfigurationGlobal::$error_code = (int) str_replace("'","\"",$_POST["ep"]);
			ConfigurationMailer::$smtp_port = str_replace("'","\"",$_POST["smtp_port"]);
			ConfigurationMailer::$smtp_use_auth = ($_POST["smtp_auth"]!="on"? false:true);
			ConfigurationGlobal::$use_multidomain = ($_POST["allow_mdomain"]!="on"?false:true);
			
			if(ConfigurationDB::commit()){				
				if(ConfigurationGlobal::commit()){
					if(ConfigurationMailer::commit()){					
						Prompt::postGood($l->get("CONFIGURATION_UPDATED"),true);
						redirect("admin");
					}
				}
			}
			Prompt::postGood($l->get("ACTION_FAILED"),true);
			redirect("admin");
			die();
		}
		redirect("admin");
	}else if(__getURI("action") == "setDef"){
		AppManager::setDefaultByName(__getURI(2));
		die(__getURI(2));
	}else if(__getURI("action") == "chownApp"){
		if(AppManager::chownApp($_POST["appid"],$_POST["own"]) === true){
			die("SUCC");
		}else
			die();
	}else if(__getURI("action") == "restrictApp"){
		if($_POST["appid"] == "") redirect("admin");
		ConfigurationMultidomain::$restricted_app[] = $_POST["appid"];
		try{
			ConfigurationMultidomain::commit();
		}catch(PuzzleError $e){
			die($e->getMessage());
		}
		die("Y");
	}else if(__getURI("action") == "unrestrictApp"){
		if($_POST["appid"] == "") redirect("admin");
		ConfigurationMultidomain::$restricted_app = array_diff(ConfigurationMultidomain::$restricted_app,[$_POST["appid"]]);
		try{
			ConfigurationMultidomain::commit();
		}catch(PuzzleError $e){
			die($e->getMessage());
		}
		die("Y");
	}else if(__getURI("action") == "addDomain"){
		if($_POST["trueForm"] == 1){
			try{
				if(ConfigurationMultidomain::addDomain($_POST["domain"]) == true) die("yes");			
			}catch(PuzzleError $e){
				die($e->getMessage());
			}
		}else{
			redirect("admin");
		}
		die();
	}else if(__getURI("action") == "rmDomain"){
		if($_POST["trueForm"] == 1){
			try{
				if(ConfigurationMultidomain::removeDomain($_POST["domain"]) == true) die("yes");			
			}catch(PuzzleError $e){
				die($e->getMessage());
			}
		}else{
			redirect("admin");
		}
		die();
	}else if(__getURI("action") == "testEmailSend"){
		$test = new Mailer();
		$test->addRecipient = $_POST["des"];
		$test->subject = "Test email from PuzzleOS";
		$test->body = "You have successfully configure your email settings!";
		if($test->sendPlain()){
			die("f");
		}else{
			die($test->error);
		}
		die();
	}
}
?>