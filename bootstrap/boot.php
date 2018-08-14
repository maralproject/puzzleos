<?php
defined("__POSEXEC") or die("No direct access allowed!");
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @package      maral.puzzleos.core
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 *
 * @software     Release: 2.0.1
 */

define("DISABLE_MINIFY",1);
//define("DB_DEBUG",1);

/***********************************
 * Initial Checking
 ***********************************/
if(!version_compare(PHP_VERSION,"7.0.0",">=")) die("PuzzleOS need PHP7 in order to work!");
if(PHP_SAPI == "cli")
	if(!defined("__POSCLI") && !defined("__POSWORKER")) die("Please use \"sudo -u www-data php puzzleos\"\n");
error_reporting(0);

/***********************************
 * Define the global variables
 ***********************************/
define("__SYSTEM_NAME", "PuzzleOS");
define("__POS_VERSION", "2.0.1");

//Return /path/to/directory
define("__ROOTDIR", str_replace("\\","/",dirname(__DIR__)));

defined("__PUBLIC_D") or define("__PUBLIC_D","public");

//Return something.com
define("__HTTP_HOST",$_SERVER["HTTP_HOST"]);

//Return "https://" or "http://"
define("__HTTP_PROTOCOL",(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://");

//Return http://something.com
define("__SITEURL", __HTTP_PROTOCOL . $_SERVER['HTTP_HOST'] . str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]));

//Return applications/dompetdinar/assets/base_1.gif?my=you
define("__HTTP_REQUEST",ltrim(str_replace(__SITEURL,"",str_replace(str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]) , "" , $_SERVER['REQUEST_URI'])),"/"));

//Return applications/dompetdinar/assets/base_1.gif
define("__HTTP_URI", explode("?",__HTTP_REQUEST)[0]);

set_time_limit(30);
require("exception.php");

/***********************************
 * Maintenance Mode Handler
 *
 * To enter maintenance mode,
 * create "site.offline" file
 * in the root directory
 ***********************************/
if(file_exists(__ROOTDIR . "/site.offline")){
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	header('Retry-After: 300');

	include(__ROOTDIR . "/templates/system/503.php");
	exit;
}

/***********************************
 * Define global functions
 ***********************************/
require("functions.php");

/***********************************
 * Get the configuration files
 ***********************************/
require('configman.php');
error_reporting(POSConfigGlobal::$error_code);
define("__SITENAME", POSConfigGlobal::$sitename);
define("__SITELANG", POSConfigGlobal::$default_language);
define("__TIMEZONE", POSConfigGlobal::$timezone);

/***********************************
 * Configuring user session
 ***********************************/
require("session.php");

/***********************************
 * Prepare all directories
 ***********************************/
preparedir(__ROOTDIR . "/storage");
preparedir(__ROOTDIR . "/storage/dbcache");
preparedir(__ROOTDIR . "/storage/data");
preparedir(__ROOTDIR . "/".__PUBLIC_D."/assets");
preparedir(__ROOTDIR . "/".__PUBLIC_D."/res");
preparedir(__ROOTDIR . "/".__PUBLIC_D."/cache",function(){
	file_put_contents(__ROOTDIR . "/".__PUBLIC_D."/cache/.htaccess",'Header set Cache-Control "max-age=2628000, public"');
});

/***********************************
 * Process incoming Request
 ***********************************/
POSGlobal::$uri = explode("/",__HTTP_URI);
POSGlobal::$uri["APP"] = POSGlobal::$uri[0];
if(POSGlobal::$uri["APP"] == "") POSGlobal::$uri["APP"] = POSConfigMultidomain::$default_application;
POSGlobal::$uri["ACTION"] = (isset(POSGlobal::$uri[1]) ? POSGlobal::$uri[1] : "");

require("iosystem.php");
require("fastcache.php");
require("message.php");
require("userdata.php");
require("language.php");

/***********************************
 * Removing installation directory
 ***********************************/
if(IO::exists("/".__PUBLIC_D."/install")){
	$r = IO::remove_r("/".__PUBLIC_D."/install");
	if(!$r) throw new PuzzleError("Please remove /".__PUBLIC_D."/install directory manually for security purpose");
}

/***********************************
 * Loading another features
 ***********************************/
require("templates.php");
require("time.php");
require("appFramework.php");
require("cron.php");
require("cli.php");
require("services.php");
require("worker.php");

/* Must be loaded after services */
POSGlobal::$session->write_cookie();

/***********************************
 * Process private file if requested
 * from browser. Public file handled
 * by Webserver directly
 ***********************************/
if(__getURI(0) == "assets"){
	$f = "/" . str_replace("assets/","storage/data/",__HTTP_URI);
	$d = Database::readAll("userdata","where `physical_path`='?'",$f)->data[0];
	$app = $d["app"];
	if($app != ""){
		try{
			$app = new Application($app);
			if(!$app->isForbidden){
				if(file_exists($app->path . "/authorize.userdata.php")){
					$file_key = $d["identifier"];
					$result = include($app->path . "/authorize.userdata.php");
					if($result) IO::streamFile($f);
				}else{
					IO::streamFile($f);
				}
			}
		}catch(AppStartError $e){}
	}
}
?>
