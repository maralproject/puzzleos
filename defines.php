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

if(!defined("__POS_CLI")){
	if(PHP_SAPI == "cli") 
		die("PuzzleOS\r\n========\r\nPlease call puzzleos.php from CLI instead from index.php\r\n\r\n");
}
 
define("__SYSTEM_NAME", "PuzzleOS");
define("__POS_VERSION", "1.2.3");
define("__ROOTDIR", str_replace("\\","/",dirname(__FILE__)));
set_time_limit(30);

require_once("runtime_error.php");

if(!version_compare(PHP_VERSION,"5.6.0",">=")){
	throw new PuzzleError("Please upgrade PHP at least 5.6.0!");
}

/***********************************
 * Maintenance Mode Handler
 * 
 * To enter maintenance mode,
 * create blank "site.offline" file 
 * in the root directory
 ***********************************/
if(file_exists( __ROOTDIR . "/site.offline" )){
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	header('Retry-After: 300');//300 seconds
	
	include( __ROOTDIR . "/templates/system/503.php" );
	die();
}

/***********************************
 * Making sure that cache folder 
 * always exists
 ***********************************/
if(!file_exists(__ROOTDIR . "/cache")){
	@mkdir(__ROOTDIR . "/cache");
	file_put_contents(__ROOTDIR . "/cache/.htaccess",'Header set Cache-Control "max-age=2628000, public"');
}else{
	if(!file_exists(__ROOTDIR . "/cache/.htaccess"))
		file_put_contents(__ROOTDIR . "/cache/.htaccess",'Header set Cache-Control "max-age=2628000, public"');
}

/***********************************
 * Making sure that user_data folder 
 * always exists
 ***********************************/
if(!file_exists(__ROOTDIR . "/user_data")){
	@mkdir(__ROOTDIR . "/user_data");
}
if(!file_exists(__ROOTDIR . "/user_private")){
	@mkdir(__ROOTDIR . "/user_private");
}

/***********************************
 * Get the configuration files
 ***********************************/
require_once('configman.php');

/***********************************
 * Configuring user session
 ***********************************/
require_once("session.php");

/**
 * Define and handle the error settings
 */
error_reporting(ConfigurationGlobal::$error_code);

/***********************************
 * Define global functions
 ***********************************/

/**
 * Convert object to Array
 *
 * Still in BETA
 * @param object $d
 * @return array
 */
function objectToArray($d) {
	if (is_object($d)) {
		$d = get_object_vars($d);
	}
	
	if (is_array($d)) {
		return array_map(__FUNCTION__, $d);
	}else{
		return $d;
	}
}
 
/**
 * Replace first occurrence pattern in string
 * @param string $str_pattern Find
 * @param string $str_replacement Replace
 * @param string $string Source
 * @return string
 */
function str_replace_first($str_pattern, $str_replacement, $string){
	if (strpos($string, $str_pattern) !== false){
        $occurrence = strpos($string, $str_pattern);
        return substr_replace($string, $str_replacement, strpos($string, $str_pattern), strlen($str_pattern));
    }
    return $string;
}

/**
 * Validate a json string
 * @param string $string 
 * @return bool
 */
function is_json($string){
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Redirect to another page
 * Better work if loaded in the app controller
 * @param string $app e.g. "users/login"
 */
function redirect($app = ""){	
	$app = ltrim($app,"/");
	$app = preg_replace("/\s+/","",$app);
	header("Location: ".__SITEURL."/" . $app);
	die("<script>window.location='".__SITEURL."/$app';</script>");
}

/**
 * Get the real bytes from PHP size format
 * @param integer $php_size
 * @return int
 */
function get_bytes($php_size) {
    $php_size = trim($php_size);
    $last = strtolower($php_size[strlen($php_size)-1]);
    switch($last) {
        case 'g':
            $php_size *= 1024;
        case 'm':
            $php_size *= 1024;
        case 'k':
            $php_size *= 1024;
    }
    return $php_size;
}

/**
 * Get HTTP URI
 * @param string $name e.g. "app", "action", or index
 * @return string
 */
function __getURI($name){
	if(is_integer($name)){
		$key = $name;
	}else{
		$key = strtoupper($name);
	}
	if(isset($GLOBALS["__POSURI"][$key])) return($GLOBALS["__POSURI"][$key]);
	return("");
}

/**
 * Match required version with system version
 * Return TRUE if system requirement fulfilled
 * @param string $version Required function
 * @return bool
 */
function __requiredSystem($version){
	return(version_compare(__POS_VERSION,$version,">="));
}

/***********************************
 * Define the global variables
 ***********************************/

define("__HTTP_HOST",$_SERVER["HTTP_HOST"]);
define("__HTTP_PROTOCOL",(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://");
define("__HTTP_REQUEST",ltrim( str_replace( str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]) , "" , $_SERVER['REQUEST_URI']),"/"));
define("__HTTP_URI",ltrim(explode("?", str_replace( str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]) , "" , $_SERVER['REQUEST_URI']))[0],"/"));

/* The order must be like this */
define("__SITEURL", __HTTP_PROTOCOL . $_SERVER['HTTP_HOST'] . str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]));
define("__SITENAME", ConfigurationGlobal::$sitename);
define("__SITELANG", ConfigurationGlobal::$default_language);
define("__TIMEZONE", ConfigurationGlobal::$timezone);

/**
 * Define the URIs
 */
$GLOBALS["__POSURI"] = explode("/",__HTTP_URI);
$GLOBALS["__POSURI"]["APP"] = $GLOBALS["__POSURI"][0];
if($GLOBALS["__POSURI"]["APP"] == "") $GLOBALS["__POSURI"]["APP"] = ConfigurationMultidomain::$default_application;
$GLOBALS["__POSURI"]["ACTION"] = (isset($GLOBALS["__POSURI"][1]) ? $GLOBALS["__POSURI"][1] : "");

/**
 * A custom class like stdObject,
 * the differences is, you can fill it with a bunch of fucntion
 */
class PObject{
	protected $methods = array();
	public function __construct(array $options){
		$this->methods = $options;
	}
	public function __call($name, $arguments){
		$callable = null;
		if (array_key_exists($name, $this->methods)) $callable = $this->methods[$name];
		else if(isset($this->$name)) $callable = $this->$name;
		
		if (!is_callable($callable)) throw new PuzzleError("Method {$name} does not exists");
		
		return call_user_func_array($callable, $arguments);
	}
}

require_once("iosystem.php");
require_once("fastcache.php");
require_once("message.php");
require_once("userdata.php");
require_once("language.php");

/* Removing installation folder if exist */
if(IO::exists("/install")){
	$r = IO::remove_r("/install");
	if(!$r) throw new PuzzleError("Please remove /install directory for security purpose");
}

/***********************************
 * If ALWAYS_HTTPS is on, make sure
 * the client served over HTTPS
 ***********************************/
if( __HTTP_PROTOCOL == "http://" && defined("ALWAYS_HTTPS")){
	header("Location: ".str_replace("http://","https://",__SITEURL));
	exit;
}

/***********************************
 * All depedencies have been loaded
 ***********************************/
require_once("templates.php");
require_once("time.php");
require_once("appFramework.php");
require_once("services.php");

/***********************************
 * Loading stuff in the cron job
 ***********************************/
require_once("cron.php");

/***********************************
 * Process private file if requested
 * from browser
 ***********************************/
if(__getURI(0) == "user_data"){
	$f = "/" . str_replace("user_data/","user_private/",__HTTP_URI);
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
