<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.phpmailer
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */

require_once( $appProp->path . "/class/PHPMailerAutoload.php"); 
 
/*
 * Mailer Instance
 */
class Mailer extends PHPMailer{
	/*
	 * This mailer class uses PHPMailer. Access the $instance for more advanced features from PHPMailer
	 * like DKIM, signing etc. Mailer class already set some things up from config.php like SMTP, auth, etc.
	 */
	
	public function __set($name,$value) {
		switch($name){
		case "addRecipient":
			$this->addAddress($value);
			break;
		case "addReplyTo":
			$this->addReplyTo($value);
			break;
		case "addCC":
			$this->addCC($value);
			break;
		case "addBCC":
			$this->addBCC($value);
			break;
		case "attachFile":
			$this->addAttachment(IO::physical_path($value));
			break;
		case "subject":
			$this->Subject = $value;
			break;
		case "body":
			$this->Body = $value;
			break;
		case "altBody":
			$this->AltBody = $value;
			break;
		}
	}
	
	public function __get($value){
		switch($value){
		case "error":
			return($this->ErrorInfo);
		}
	}
	
	function __construct() {
		$this->setFrom(ConfigurationMailer::$From,ConfigurationMailer::$Sender);
		
		$lang = explode("-",LangManager::getDisplayedNow())[0];
		$this->setLanguage($lang);
		
		//Future, configure SMTP here
		if(!ConfigurationMailer::$UsePHP){
			$this->isSMTP();
			$this->Host = ConfigurationMailer::$smtp_host;
			$this->SMTPAuth = ConfigurationMailer::$smtp_use_auth;
			$this->Username = ConfigurationMailer::$smtp_username;
			$this->Password = ConfigurationMailer::$smtp_password;
			if($smtp["Encryption"]!="none")	$this->SMTPSecure = ConfigurationMailer::$smtp_encryption;
			$this->Port = ConfigurationMailer::$smtp_port;
		}
	}
	
	/*
	 * Send HTML based email also with AltBody for non HTML client
	 * @return bool
	 */
	public function sendHTML(){
		$this->isHTML(true);
		return($this->send());
	}
	
	/*
	 * Send plain-text email
	 * @return bool
	 */
	public function sendPlain(){
		$this->isHTML(false);
		return($this->send());
	}
}
?>