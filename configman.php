<?php
defined("__POSEXEC") or die("No direct access allowed!");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */

/**
 * Global variables in PuzzleOS
 */
class PuzzleOSGlobal{
	/**
	 * Current HTTP Code Response 
	 * @var int
	 */	
	public static $http_code = 200;
		
	/**
	 * Mock app to be run in debug.php 
	 * @var string
	 */	
	public static $debug_app = NULL;
	
	/**
	 * Current domain zone where PuzzleOS in
	 * @var string
	 */	
	public static $domain_zone = "";
	
	/**
	 * Session handler instance
	 * @var PuzzleSession
	 */
	public static $session = NULL;
	
	/**
	 * This variable set up by bootstrap.php
	 * @var array
	 */
	public static $uri;
}

/**
 * Database Configuration stuff
 */
class ConfigurationDB{
	/**
	 * @var string 
	 */
	public static $username;
	
	/**
	 * @var string 
	 */
	public static $password;
	
	/**
	 * @var string 
	 */
	public static $host;
	
	/**
	 * @var string 
	 */
	public static $database_name;
	
	private static function findAndReplace(&$file, $search, $replace){
		foreach($file as $line=>$text){
			if(strstr($text,$search) == false) continue;
			$file[$line] = $replace . "\r\n";
			return;
		}
		throw new PuzzleError("Cannot find $search in configuration!");
	}
	
	/**
	 * After making change to the configuration stuff, 
	 * you can commit the change using this function
	 * to keep it permanent
	 * @return bool
	 */
	public static function commit(){
		if(!IO::exists("/configs/root.sys.php")) throw new PuzzleError("No configuration file available!", "Please reconfigure configuration files!");
		$current_config = file(__ROOTDIR . "/configs/root.sys.php");
		
		self::findAndReplace($current_config,'ConfigurationDB::$username','ConfigurationDB::$username = "'.self::$username.'";');
		self::findAndReplace($current_config,'ConfigurationDB::$password','ConfigurationDB::$password = "'.self::$password.'";');
		self::findAndReplace($current_config,'ConfigurationDB::$host','ConfigurationDB::$host = "'.self::$host.'";');
		self::findAndReplace($current_config,'ConfigurationDB::$database_name','ConfigurationDB::$database_name = "'.self::$database_name.'";');
		
		return file_put_contents(__ROOTDIR . "/configs/root.sys.php",implode("",$current_config)) == FALSE ? false :true;
	}
}

/**
 * Mailer Configuration stuff
 */
class ConfigurationMailer{
	/**
	 * @var string 
	 */
	public static $From;
	
	/**
	 * @var string 
	 */
	public static $Sender;
	
	/**
	 * Send mail using PHP mail() if TRUE, otherwise by 
	 * SMTP using PHPMailer() stuff
	 * @var bool 
	 */
	public static $UsePHP;
	
	/**
	 * @var string 
	 */
	public static $smtp_host;
	
	/**
	 * @var string 
	 */
	public static $smtp_port;
	
	/**
	 * @var string 
	 */
	public static $smtp_username;
	
	/**
	 * @var string 
	 */
	public static $smtp_password;
	
	/**
	 * Value can be either "none","tls", or "ssl"
	 * @var string 
	 */
	public static $smtp_encryption;
	
	/**
	 * Tell if PuzzleOS need to send username and password to
	 * SMTP server for authentication
	 * @var bool */
	public static $smtp_use_auth;
	
	private static function findAndReplace(&$file, $search, $replace){
		foreach($file as $line=>$text){
			if(strstr($text,$search) == false) continue;
			$file[$line] = $replace . "\r\n";
			return;
		}
		throw new PuzzleError("Cannot find $search in configuration!");
	}
	
	/**
	 * After making change to the configuration stuff, 
	 * you can commit the change using this function
	 * to keep it permanent
	 * @return bool
	 */
	public static function commit(){
		if(!is_bool(self::$UsePHP)) throw new PuzzleError("UsePHP must be boolean!");
		if(!is_bool(self::$smtp_use_auth)) throw new PuzzleError("smtp_use_auth code must be boolean!");
		
		if(ConfigurationGlobal::$use_multidomain){
			if(!file_exists(__ROOTDIR . "/configs/".PuzzleOSGlobal::$domain_zone.".config.php")) throw new PuzzleError("No configuration file available!", "Please reconfigure configuration files!");
			$current_config = file(__ROOTDIR . "/configs/".PuzzleOSGlobal::$domain_zone.".config.php");
		}else{
			if(!IO::exists("/configs/root.sys.php")) throw new PuzzleError("No configuration file available!", "Please reconfigure configuration files!");
			$current_config = file(__ROOTDIR . "/configs/root.sys.php");			
		}
		
		self::findAndReplace($current_config,'ConfigurationMailer::$From','ConfigurationMailer::$From = "'.self::$From.'";');
		self::findAndReplace($current_config,'ConfigurationMailer::$Sender','ConfigurationMailer::$Sender = "'.self::$Sender.'";');
		self::findAndReplace($current_config,'ConfigurationMailer::$UsePHP','ConfigurationMailer::$UsePHP = '. (self::$UsePHP ? 'true':'false') .';');

		self::findAndReplace($current_config,'ConfigurationMailer::$smtp_host','ConfigurationMailer::$smtp_host = "'.self::$smtp_host.'";');
		self::findAndReplace($current_config,'ConfigurationMailer::$smtp_username','ConfigurationMailer::$smtp_username = "'.self::$smtp_username.'";');
		self::findAndReplace($current_config,'ConfigurationMailer::$smtp_password','ConfigurationMailer::$smtp_password = "'.self::$smtp_password.'";');
		self::findAndReplace($current_config,'ConfigurationMailer::$smtp_encryption','ConfigurationMailer::$smtp_encryption = "'.self::$smtp_encryption.'";');
		self::findAndReplace($current_config,'ConfigurationMailer::$smtp_port','ConfigurationMailer::$smtp_port = "'.self::$smtp_port.'";');
		self::findAndReplace($current_config,'ConfigurationMailer::$smtp_use_auth','ConfigurationMailer::$smtp_use_auth = '. (self::$smtp_use_auth ? 'true':'false') . ';');
		 
		if(ConfigurationGlobal::$use_multidomain){
			return file_put_contents(__ROOTDIR . "/configs/".PuzzleOSGlobal::$domain_zone.".config.php",implode("",$current_config)) == FALSE ? false :true;
		}else{
			return file_put_contents(__ROOTDIR . "/configs/root.sys.php",implode("",$current_config)) == FALSE ? false :true;
		}
	}
}

/**
 * Global Configuration stuff
 */
class ConfigurationGlobal{
	/**
	 * PHP Error code for error_reporting()
	 * You can insert like E_ERROR|E_WARNING
	 * @var int 
	 */
	public static $error_code;
	
	/**
	 * Allow PuzzleOS to serve multidomain web-apps
	 * @var bool 
	 */
	public static $use_multidomain;
	
	/**
	 * Specify using standard language code, or special identifier
	 * e.g. "id-ID" for Indonesian, or "loc" for use the browser's language
	 * @var string 
	 */
	public static $default_language;
	
	/**
	 * @var string 
	 */
	public static $sitename;
	
	/**
	 * Specify using standard timezone string
	 * e.g. "Asia/Jakarta"
	 * @var string 
	 */
	public static $timezone;
	
	/**
	 * Copyright text
	 * @var string 
	 */
	public static $copyright;
	
	/**
	 * Meta description
	 * @var string 
	 */
	public static $meta_description;
		
	private static function findAndReplace(&$file, $search, $replace){
		foreach($file as $line=>$text){
			if(strstr($text,$search) == false) continue;
			$file[$line] = $replace . "\r\n";
			return;
		}
		throw new PuzzleError("Cannot find $search in configuration!");
	}
	
	/**
	 * After making change to the configuration stuff, 
	 * you can commit the change using this function
	 * to keep it permanent
	 * @return bool
	 */
	public static function commit(){
		if(!is_bool(self::$use_multidomain)) throw new PuzzleError("use_multidomain code must be boolean!");		
		if(!is_int(self::$error_code)) throw new PuzzleError("error_code must be int!");		
		
		if(!file_exists(__ROOTDIR . "/configs/root.sys.php")) throw new PuzzleError("No configuration file available!", "Please reconfigure configuration files!");
		$current_config = file(__ROOTDIR . "/configs/root.sys.php");		
		self::findAndReplace($current_config,'ConfigurationGlobal::$use_multidomain','ConfigurationGlobal::$use_multidomain = '. (self::$use_multidomain ? 'true':'false') .';');
		if(file_put_contents(__ROOTDIR . "/configs/root.sys.php",implode("",$current_config)) == FALSE) return false;
		
		if(ConfigurationGlobal::$use_multidomain){
			PuzzleOSGlobal::$domain_zone = explode(":", $_SERVER["HTTP_HOST"])[0];
			if(ConfigurationMultidomain::addDomain(PuzzleOSGlobal::$domain_zone) != true){
				return false;
			}
		}else{			
			PuzzleOSGlobal::$domain_zone = "{root}";
		}
		
		$current_config = NULL;
		
		if(ConfigurationGlobal::$use_multidomain){
			if(!file_exists(__ROOTDIR . "/configs/".PuzzleOSGlobal::$domain_zone.".config.php")) throw new PuzzleError("No configuration file available!", "Please reconfigure configuration files!");
			$current_config = file(__ROOTDIR . "/configs/".PuzzleOSGlobal::$domain_zone.".config.php");
		}else{
			if(!IO::exists("/configs/root.sys.php")) throw new PuzzleError("No configuration file available!", "Please reconfigure configuration files!");
			$current_config = file(__ROOTDIR . "/configs/root.sys.php");
		}
		
		self::findAndReplace($current_config,'ConfigurationGlobal::$error_code','ConfigurationGlobal::$error_code = '.self::$error_code.';');
		self::findAndReplace($current_config,'ConfigurationGlobal::$default_language','ConfigurationGlobal::$default_language = "'.self::$default_language.'";');
		self::findAndReplace($current_config,'ConfigurationGlobal::$sitename','ConfigurationGlobal::$sitename = "'.self::$sitename.'";');
		self::findAndReplace($current_config,'ConfigurationGlobal::$timezone','ConfigurationGlobal::$timezone = "'.self::$timezone.'";');
		self::findAndReplace($current_config,'ConfigurationGlobal::$copyright','ConfigurationGlobal::$copyright = "'.self::$copyright.'";');
		self::findAndReplace($current_config,'ConfigurationGlobal::$meta_description','ConfigurationGlobal::$meta_description = "'.self::$meta_description.'";');
		
		if(ConfigurationGlobal::$use_multidomain){
			return file_put_contents(__ROOTDIR . "/configs/".PuzzleOSGlobal::$domain_zone.".config.php",implode("",$current_config)) == FALSE ? false :true;
		}else{
			return file_put_contents(__ROOTDIR . "/configs/root.sys.php",implode("",$current_config)) == FALSE ? false :true;
		}
	}
}

/**
 * Cross-domain Configuration stuff
 */
class ConfigurationMultidomain{
	
	/**
	 * Default Application for homepage
	 * @var string 
	 */
	public static $default_application;
	
	/**
	 * Default template to be used
	 * @var string 
	 */
	public static $default_template;
	
	/**
	 * Restricted app to be run
	 * This config very useful on multidomain configuration
	 * @var array
	 */
	public static $restricted_app = [];
	
	private static function findAndReplace(&$file, $search, $replace){
		foreach($file as $line=>$text){
			if(strstr($text,$search) == false) continue;
			$file[$line] = $replace . "\r\n";
			return;
		}
		throw new PuzzleError("Cannot find $search in configuration!");
	}
	
	/**
	 * Set-up another domain zone in multidomain mode
	 * @param string $domain_zone Something like "domain.com" or "subdomain.domain.com" without port, domain with www. will be included in this domain
	 * @return bool
	 */
	public static function addDomain($domain_zone){
		if($domain_zone == "{root}" || $domain_zone == "") throw new PuzzleError("Invalid domain zone!");
		if(stristr($domain_zone,":")) throw new PuzzleError("Please remove port from domain_zone!");
		if(stristr($domain_zone,"@")) throw new PuzzleError("Please remove credetials from domain_zone!");
		if(stristr($domain_zone,"/")) throw new PuzzleError("Just input the domain in domain_zone!");
		if(!is_array(self::$restricted_app)) throw new PuzzleError("Restricted app must be in array!");
		
		/* Removing www. from domain */
		$domain_zone = str_replace("www.","",$domain_zone);
		
		//Create new domain_config in database
		if(Database::read("multidomain_config","host","host",$domain_zone) == ""){
			if(!Database::newRow("multidomain_config",$domain_zone,self::$default_application,self::$default_template,json_encode(self::$restricted_app))) return false;
		}
		
		if(!file_exists(__ROOTDIR . "/configs/$domain_zone.config.php")){
			//Clone from root.sys.php
			if(!IO::exists("/configs/root.sys.php")) throw new PuzzleError("No configuration file available!", "Please reconfigure configuration files!");
			$root_config = file(__ROOTDIR . "/configs/root.sys.php");
			
			self::findAndReplace($root_config,'ConfigurationDB::$username',"/* Please configure database username from root.sys.php */");
			self::findAndReplace($root_config,'ConfigurationDB::$password',"/* Please configure database password from root.sys.php */");
			self::findAndReplace($root_config,'ConfigurationDB::$host',"/* Please configure database host from root.sys.php */");
			self::findAndReplace($root_config,'ConfigurationDB::$database_name',"/* Please configure database name from root.sys.php */");
			self::findAndReplace($root_config,'ConfigurationGlobal::$use_multidomain',"/* Please configure multidomain from root.sys.php */");
			
			return (file_put_contents(__ROOTDIR . "/configs/$domain_zone.config.php",implode("",$root_config)) == FALSE ? false :true);
		}
		
		return true;
	}
	
	public static function removeDomain($domain_zone){
		if($domain_zone == "{root}" || $domain_zone == "") throw new PuzzleError("Invalid domain zone!");
		if(stristr($domain_zone,":")) throw new PuzzleError("Please remove port from domain_zone!");
		if(stristr($domain_zone,"@")) throw new PuzzleError("Please remove credetials from domain_zone!");
		if(stristr($domain_zone,"/")) throw new PuzzleError("Just input the domain in domain_zone!");
		if(!is_array(self::$restricted_app)) throw new PuzzleError("Restricted app must be in array!");
		
		/* Removing www. from domain */
		$domain_zone = str_replace("www.","",$domain_zone);
		
		if(!file_exists(__ROOTDIR . "/configs/$domain_zone.config.php")){
			throw new PuzzleError("Cannot find registered domain");
		}
		
		Database::deleteRow("multidomain_config","host",$domain_zone);
		unlink(__ROOTDIR . "/configs/$domain_zone.config.php");
		
		return true;
	}
	
	/**
	 * After making change to the configuration stuff, 
	 * you can commit the change using this function
	 * to keep it permanent
	 * @return bool
	 */
	public static function commit(){
		if(!is_array(self::$restricted_app)) throw new PuzzleError("Restricted app must be in array!");
		
		/* Make sure that all system apps are not in restricted app */
		foreach(AppManager::listAll() as $app){
			if(!$app["system"]) continue;
			if(in_array($app["rootname"],self::$restricted_app)) throw new PuzzleError("System app cannot be restricted!");
		}
		return Database::exec("UPDATE `multidomain_config` SET `default_app`='?', `default_template`='?', `restricted_app`='?' WHERE `host`='?'",self::$default_application,self::$default_template,json_encode(self::$restricted_app), PuzzleOSGlobal::$domain_zone);
	}	
}

if(!file_exists(__ROOTDIR . "/configs")) @mkdir(__ROOTDIR . "/configs");
if(!file_exists(__ROOTDIR . "/configs/root.sys.php")){
	if(file_exists(__ROOTDIR . "/install")){	
		header("Location: //" . $_SERVER['HTTP_HOST'] . str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]) . "/install");
		die();
	}else 
		throw new PuzzleError("No configuration file!","Please build configuration file under /configs directory");
}

/* Include the main config.php */
require_once(__ROOTDIR . '/configs/root.sys.php');

require_once("database.php");

/* Build system table in the database */
require_once("systables.php");

/* Removing www. and port from domain */
$host_without_port = str_replace("www.","",explode(":", $_SERVER["HTTP_HOST"])[0]);
PuzzleOSGlobal::$domain_zone = (ConfigurationGlobal::$use_multidomain ? $host_without_port : "{root}");

/* Include configuration for specific domain */
if(ConfigurationGlobal::$use_multidomain){
	if(substr($host_without_port,0,1) == '{') throw new PuzzleError("Not a valid domain!");
	if(!file_exists("configs/$host_without_port.config.php")){
		try{
			throw new PuzzleError("PuzzleOS accessed from unregistered domain @ {$host_without_port}");
		}catch(PuzzleError $e){}
		
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
		include( __ROOTDIR . "/templates/system/404.php" );
		exit;
	}
	require_once("configs/$host_without_port.config.php");
	ConfigurationMultidomain::$restricted_app = json_decode(Database::read("multidomain_config","restricted_app","host",PuzzleOSGlobal::$domain_zone),true);
}else{
	ConfigurationMultidomain::$restricted_app = [];
}

unset($host_without_port);

/* Define default stuff */
ConfigurationMultidomain::$default_application = Database::read("multidomain_config","default_app","host", PuzzleOSGlobal::$domain_zone);
ConfigurationMultidomain::$default_template = Database::read("multidomain_config","default_template","host", PuzzleOSGlobal::$domain_zone);
?>