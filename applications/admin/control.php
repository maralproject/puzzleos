<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */
 
if(__getURI("app") == "admin"){
	new Application("phpmailer");
	$l = new Language;
	if(__getURI("action") == "changeTemplate"){
		Template::setDefaultByName(__getURI(2));
		redirect("admin#templates");
	}else if(__getURI("action") == "saveConfig"){
		if($_POST["trueForm"] == "1"){
			POSConfigDB::$username = str_replace("'","\"",$_POST["dbuser"]);
			POSConfigDB::$password = str_replace("'","\"",$_POST["dbpass"]);
			POSConfigDB::$host = str_replace("'","\"",$_POST["dbhost"]);
			POSConfigDB::$database_name = str_replace("'","\"",$_POST["dbdb"]);
			POSConfigGlobal::$default_language = str_replace("'","\"",$_POST["deflang"]);
			POSConfigGlobal::$sitename = str_replace("'","\"",$_POST["sitename"]);
			POSConfigGlobal::$timezone = str_replace("'","\"",$_POST["timezone"]);
			POSConfigGlobal::$copyright	= str_replace("'","\"",$_POST["copytext"]);
			POSConfigGlobal::$meta_description = str_replace("'","\"",$_POST["metadesc"]);
			POSConfigMailer::$From = str_replace("'","\"",$_POST["mailfrom"]);
			POSConfigMailer::$Sender = str_replace("'","\"",$_POST["mailname"]);
			POSConfigMailer::$UsePHP = ($_POST["use_smtp"]!="on"? true:false);
			POSConfigMailer::$smtp_host = str_replace("'","\"",$_POST["smtp_host"]);
			POSConfigMailer::$smtp_username = str_replace("'","\"",$_POST["smtp_user"]);
			POSConfigMailer::$smtp_password = str_replace("'","\"",$_POST["smtp_pass"]);
			POSConfigMailer::$smtp_encryption = str_replace("'","\"",$_POST["smtp_enc"]);
			POSConfigGlobal::$error_code = (int) str_replace("'","\"",$_POST["ep"]);
			POSConfigMailer::$smtp_port = str_replace("'","\"",$_POST["smtp_port"]);
			POSConfigMailer::$smtp_use_auth = ($_POST["smtp_auth"]!="on"? false:true);
			POSConfigGlobal::$use_multidomain = ($_POST["allow_mdomain"]!="on"?false:true);
			
			if(POSConfigDB::commit()){				
				if(POSConfigGlobal::commit()){
					if(POSConfigMailer::commit()){					
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
		POSConfigMultidomain::$restricted_app[] = $_POST["appid"];
		try{
			POSConfigMultidomain::commit();
		}catch(PuzzleError $e){
			die($e->getMessage());
		}
		die("Y");
	}else if(__getURI("action") == "unrestrictApp"){
		if($_POST["appid"] == "") redirect("admin");
		POSConfigMultidomain::$restricted_app = array_diff(POSConfigMultidomain::$restricted_app,[$_POST["appid"]]);
		try{
			POSConfigMultidomain::commit();
		}catch(PuzzleError $e){
			die($e->getMessage());
		}
		die("Y");
	}else if(__getURI("action") == "addDomain"){
		if($_POST["trueForm"] == 1){
			try{
				if(POSConfigMultidomain::addDomain($_POST["domain"]) == true) die("yes");			
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
				if(POSConfigMultidomain::removeDomain($_POST["domain"]) == true) die("yes");			
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