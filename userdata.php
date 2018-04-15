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

define("__DENY_HTACCESS_HASH", "ee9a29b31d78b2e52120610ed51f732453580f7c");

/**
 * Store and manage user data
 * Only allowed to be called from applications
 */
class UserData{
	private static $cache;
	
	private static function init(){
		$caller = debug_backtrace()[1]["file"];
		$filenameStr = str_replace(__ROOTDIR,"",str_replace("\\","/",$caller));
		$filename = explode("/",$filenameStr);
		switch($filename[1]){
		case "applications":
			break;
		case "debug.php":
			if(PuzzleOSGlobal::$debug_app == "") return("");
			if(AppManager::isInstalled(PuzzleOSGlobal::$debug_app)){
				//Check the folder for user_data
				if(!IO::exists("/user_data/". PuzzleOSGlobal::$debug_app)){
					@mkdir(IO::physical_path("/user_data/". PuzzleOSGlobal::$debug_app));
				}
				return(PuzzleOSGlobal::$debug_app);
			}
			break;
		default:
			return("");
		}
		$appDir = $filename[2];
		$appname = AppManager::getNameFromDirectory($appDir);		
		//Check the folder for user_data
		if(!IO::exists("/user_data/". $appname)){
			@mkdir(IO::physical_path("/user_data/". $appname));
		}
		if(!IO::exists("/user_private/". $appname)){
			@mkdir(IO::physical_path("/user_private/". $appname));
			file_put_contents(IO::physical_path("/user_private/.htaccess"),"Deny from all\r\n");
		}else{
			if(hash("sha1",file_get_contents(__ROOTDIR . "/user_private/.htaccess")) != __DENY_HTACCESS_HASH)
				file_put_contents(IO::physical_path("/user_private/.htaccess"),"Deny from all\r\n");
		}
		return($appname);
	}
	
	/**
	 * Move uploaded file through HTTP POST to user directory
	 * Just like move_uploaded_file(), but moves it directly to user data directory
	 * for easy access.
	 * 
	 * @param string $key
	 * @param string $inputname
	 * @param bool $secure
	 * @return bool
	 */
	public static function move_uploaded($key,$inputname,$secure = false){
		$appname = self::init();
		if($appname == "") return false;
		$fileext = strtolower(end(explode(".",$_FILES[$inputname]['name'])));
		if(!$secure)
			$filename = "/user_data/$appname/$key.$fileext";
		else
			$filename = "/user_private/$appname/$key.$fileext";
		$oldfile = Database::readArg("userdata","physical_path","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		if($oldfile != ""){
			//If old file is present, it'll be overwritten
			unlink(IO::physical_path($oldfile));
			Database::deleteRowArg("userdata","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		}
		if(!move_uploaded_file($_FILES[$inputname]['tmp_name'], IO::physical_path($filename))) return false;
		Database::newRow("userdata",$appname,$key,$filename,IO::get_mime($filename),time(),$secure?1:0);
		return true;
	}
	
	/**
	 * Move file from somewhere to user directory
	 * 
	 * @param string $key
	 * @param string $path_to_file
	 * @param bool $secure
	 * @return bool
	 */
	public static function move($key,$path_to_file,$secure = false){
		$appname = self::init();
		if($appname == "") return false;
		if(!IO::exists($path_to_file)) return false;
		if(is_dir(IO::physical_path($path_to_file))) return false;
		
		$path_to_file_e = explode("/",$path_to_file);		
		$fileext = strtolower(end(explode(".",$path_to_file)));
		
		if(!$secure)
			$filename = "/user_data/$appname/" . $key . "." . $fileext;
		else
			$filename = "/user_private/$appname/" . $key . "." . $fileext;
		
		$oldfile = Database::readArg("userdata","physical_path","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		if($oldfile != ""){
			unlink(IO::physical_path($oldfile));
			Database::deleteRowArg("userdata","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		}
		
		if(!rename(IO::physical_path($path_to_file),IO::physical_path($filename))) return(false);
		Database::newRow("userdata",$appname,$key,$filename,IO::get_mime($filename),time(),$secure?1:0);
		
		if($path_to_file_e[1] == "user_data"){
			//Changing file ownership
			Database::deleteRowArg("userdata","WHERE `physical_path`='?'",$path_to_file);			
		}
		return true;
	}
	
	/**
	 * Copy file somewhere to user directory
	 * 
	 * @param string $key
	 * @param string $path_to_file
	 * @param bool $secure
	 * @return bool
	 */
	public static function copy($key,$path_to_file,$secure = false){
		$appname = self::init();
		if($appname == "") return false;
		if(!IO::exists($path_to_file)) return false;
		if(is_dir(IO::physical_path($path_to_file))) return false;
		$fileext = strtolower(end(explode(".",$path_to_file)));
		
		if(!$secure)
			$filename = "/user_data/$appname/" . $key . "." . $fileext;
		else
			$filename = "/user_private/$appname/" . $key . "." . $fileext;
		
		$oldfile = Database::readArg("userdata","physical_path","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		if($oldfile != ""){
			unlink(IO::physical_path($oldfile));
			Database::deleteRowArg("userdata","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		}
		if(!copy(IO::physical_path($path_to_file),IO::physical_path($filename))) return(false);
		Database::newRow("userdata",$appname,$key,$filename,IO::get_mime($filename),time(),$secure?1:0);
		return true;
	}
	
	/**
	 * Get file path.
	 * e.g. /user_data/app/file.ext
	 * 
	 * If you use cache control, it will look like this:
	 * /user_data/app/file.ext?v=0123
	 * 
	 * @param string $key
	 * @param bool $with_cache_control
	 * @return string
	 */
	public static function getPath($key, $with_cache_control = false, $reveal_private_path = false){
		$appname = self::init();
		if($appname == "") return false;
		$d = Database::readAll("userdata","WHERE `app`='?' AND `identifier`='?'",$appname,$key)->data[0];
		$filename = $d["physical_path"];
		if($with_cache_control){
			$filename .= "?v=" . $d["ver"];
		}
		return($reveal_private_path ? $filename : str_replace("user_private/","user_data/",$filename));
	}
	
	/**
	 * Write a file into user data directory
	 * 
	 * @param string $key
	 * @param contents $content
	 * @param string $file_ext
	 * @param bool $secure
	 * @return bool
	 */
	public static function store($key,$content,$file_ext,$secure = false){
		$appname = self::init();
		if($appname == "") return false;
		if(!$secure)
			$filename = "/user_data/$appname/" . $key . "." . $file_ext;
		else
			$filename = "/user_private/$appname/" . $key . "." . $file_ext;
		$oldfile = Database::readArg("userdata","physical_path","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		if($oldfile != ""){
			unlink(IO::physical_path($oldfile));
			Database::deleteRowArg("userdata","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		}
		IO::write($filename,$content);
		unset(self::$cache[$appname.$key]);
		return Database::newRow("userdata",$appname,$key,$filename,IO::get_mime($filename),time(),$secure?1:0);
	}
	
	/**
	 * Check if key belongs to a file
	 * 
	 * @param string $key
	 * @return bool
	 */
	public static function exists($key){
		$appname = self::init();
		if($appname == "") return false;
		$filename = Database::readArg("userdata","physical_path","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		return($filename != "" && IO::exists($filename));
	}
	
	/**
	 * Read file instantly, using file_get_contents()
	 * 
	 * @param string $key
	 * @return contents
	 */
	public static function read($key){		
		$appname = self::init();
		if($appname == "") return false;
		$filename = Database::readArg("userdata","physical_path","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		if(!isset(self::$cache[$appname.$key])){
			$ctn = file_get_contents(IO::physical_path($filename));
			self::$cache[$appname.$key] = $ctn;
		}else{
			$ctn = self::$cache[$appname.$key];
		}
		return($ctn);
	}
	
	/**
	 * Read save MIME type
	 * 
	 * @param string $key
	 * @return contents
	 */
	public static function getMIME($key){		
		$appname = self::init();
		if($appname == "") return false;
		$mime = Database::readArg("userdata","mime_type","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		return($mime);
	}
	
	/**
	 * Remove file
	 * 
	 * @param string $key
	 * @return bool
	 */
	public static function remove($key){
		$appname = self::init();
		if($appname == "") return false;		
		$filename = Database::readArg("userdata","physical_path","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
		if(!unlink(IO::physical_path($filename))) return false;
		unset(self::$cache[$appname.$key]);
		return Database::deleteRowArg("userdata","WHERE `app`='?' AND `identifier`='?'",$appname,$key);
	}
}
?>