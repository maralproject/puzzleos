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

if(!version_compare(PHP_VERSION,"5.6.0",">=")){
	throw new PuzzleError("Please upgrade PHP at least 5.6.0!");
}

error_reporting(0);

if(PHP_SAPI == "cli" && !defined("__POSCLI")) die("\nPlease use\n     sudo -u www-data php puzzleos\n\n");
if(PHP_SAPI != "cli" && defined("__POSCLI")) die("Please use index.php as Directory Main File!");

/***********************************
 * Define the global variables
 ***********************************/
if(!defined("__SYSTEM_NAME")) define("__SYSTEM_NAME", "PuzzleOS");
define("__POS_VERSION", "1.2.3");
define("__ROOTDIR", str_replace("\\","/",dirname(__FILE__)));

define("__HTTP_HOST",$_SERVER["HTTP_HOST"]);
define("__HTTP_PROTOCOL",(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://");
define("__HTTP_REQUEST",ltrim( str_replace( str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]) , "" , $_SERVER['REQUEST_URI']),"/"));
define("__SITEURL", __HTTP_PROTOCOL . $_SERVER['HTTP_HOST'] . str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]));
define("__HTTP_URI",ltrim(str_replace(__SITEURL,"",explode("?", str_replace( str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]) , "" , $_SERVER['REQUEST_URI']))[0]),"/"));

set_time_limit(30);
require_once("runtime_error.php");

/***********************************
 * Maintenance Mode Handler
 *
 * To enter maintenance mode,
 * create blank "site.offline" file
 * in the root directory
 ***********************************/
if(file_exists(__ROOTDIR . "/site.offline")){
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	header('Retry-After: 300');//300 seconds

	include( __ROOTDIR . "/templates/system/503.php" );
	exit;
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
define("__SITENAME", ConfigurationGlobal::$sitename);
define("__SITELANG", ConfigurationGlobal::$default_language);
define("__TIMEZONE", ConfigurationGlobal::$timezone);

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
	PuzzleOSGlobal::$session->write_cookie();
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
 * Get maximum file size allowed by PHP to be uploaded
 * Use this information to prevent something wrong in your app
 * when user upload a very large data.
 * @return integer
 */
function php_max_upload_size(){
	$max_upload = get_bytes(ini_get('post_max_size'));
	$max_upload2 = get_bytes(ini_get('upload_max_filesize'));
	return (int)(($max_upload < $max_upload2 && $max_upload != 0) ? $max_upload:$max_upload2);
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
	if(isset(PuzzleOSGlobal::$uri[$key])) return(PuzzleOSGlobal::$uri[$key]);
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

/**
 * Define the URIs
 */
PuzzleOSGlobal::$uri = explode("/",__HTTP_URI);
PuzzleOSGlobal::$uri["APP"] = PuzzleOSGlobal::$uri[0];
if(PuzzleOSGlobal::$uri["APP"] == "") PuzzleOSGlobal::$uri["APP"] = ConfigurationMultidomain::$default_application;
PuzzleOSGlobal::$uri["ACTION"] = (isset(PuzzleOSGlobal::$uri[1]) ? PuzzleOSGlobal::$uri[1] : "");

/**
 * A custom class like stdObject,
 * the differences is, you can fill it with a bunch of fucntion
 */
class PObject{
	protected $methods = [];
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
require_once("cron.php");
require_once("cli.php");
require_once("services.php");

/***********************************
 * Write cookies to browser. Session
 * config can be modified on each
 * app services if necessary using
 *
 * PuzzleOSGlobal::$session
 ***********************************/
PuzzleOSGlobal::$session->write_cookie();

/***********************************
 * Process private file if requested
 * from browser. Public file handled
 * by Apache2 directly
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
