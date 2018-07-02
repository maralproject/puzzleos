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
 * @software     Release: 2.0.0
 */

/**
 * Language Manager
 */
class LangManager{
	/** 
	 * Define if language is forced
	 * @var bool
	 */
	public static $forced = false;
		
	/** 
	 * Define if language is forced
	 * @var string
	 */
	public static $forcedLang = "";
	
	/**
	 * Get Language Form in HTML
	 * @param string $id DOM Element ID
	 * @param string $val Active language value
	 * @param bool $without_default Hide default language option
	 * @param bool $location_option Show based on location language option
	 * @param bool $use_logo Show icon in HTML form
	 * @return string
	 */
	public static function getForm($id,$val,$without_default = true,$location_option = true, $use_logo = true){
		$id = $id==""?"lang":$id;
		$val = $val==""?"lang":$val;
		//Increase the language option for your user here!
		$r = '<div class="input-group">
				'.($use_logo?'<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-language"></i></span>':'').'
				<select name="'.$id.'" id="'.$id.'_selector" class="form-control languageList" data-live-search="true">';
		if($location_option) $r .= '<option value="loc"'.($val=="loc"?' selected':'').'>Based on location</option>';
		if(!$without_default) $r .= '<option value="def"'.($val=="def"?' selected':'').'>Default</option>';
		
		/* Finding through app which language are available */
		$locale = require(__ROOTDIR . "/bootstrap/locale.php");
		$temp = [];
		
		foreach(IO::list_directory("applications") as $appdir){
			if($appdir == "." || $appdir == "..") continue;
			if(!is_dir(IO::physical_path("applications/$appdir"))) continue;
			foreach(glob(IO::physical_path("applications/$appdir/*.lang.php")) as $language_file){
				$li = explode("/",rtrim($language_file,"/"));
				$language = str_replace(".lang.php","",end($li));
				//Set a flag
				$temp[$language] = [$language,$locale[$language]];
			}
		}
		unset($locale);

		foreach($temp as $l){
			$r .= "<option value=\"".$l[0]."\"".($val==$l[0]?' selected':'').">".$l[1]."</option>";
		}
		$r .= '</select></div>';
		return($r);
	}

	/**
	 * Print Language Form in HTML
	 * @param string $id DOM Element ID
	 * @param string $val Active language value
	 * @param bool $without_default Hide default language option
	 * @param bool $location_option Show based on location language option
	 * @param bool $use_logo Show icon in HTML form
	 */
	public static function dumpForm($id,$val,$without_default = true,$location_option = true, $use_logo = true){
		echo(LangManager::getForm($id,$val,$without_default,$location_option,$use_logo));
	}

	/**
	 * Force to change displayed language
	 * @param string $langCode Language code
	 */
	public static function force($langCode){
		self::$forced = true;
		self::$forcedLang = $langCode;
	}

	/**
	 * Force to change displayed language
	 * @return string
	 */
	public static function getDisplayedNow(){
		$langs = "en_US";
		if($_SESSION['account']['loggedIn'] == 0){
			$langs = POSConfigGlobal::$default_language;
		}else{
			$langs = $_SESSION['account']['lang'];
			if($langs == "def") $langs = POSConfigGlobal::$default_language;
		}
		if($langs == "loc") {
			$httpaccept = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$locale = require(__ROOTDIR . "/bootstrap/locale.php");
			foreach($locale as $k=>$l){
				if(preg_match("/".$httpaccept."/",$k)){
					$langs = $k;
					break;
				}
			}
			unset($locale);
		}
		if(self::$forced){
			$langs = self::$forcedLang;
		}
		return($langs);
	}
}

/**
 * Language Instance
 */
class Language{
	/**
	 * Set the language for specific app
	 */
	public $app;
	
	public function __construct($app = ""){
		//If app is empty, get the referer and get the app
		$uri = explode("/",str_replace(__ROOTDIR . "/","",str_replace("\\", "/", debug_backtrace()[0]["file"])));
		if($uri[0] == "applications" && $app == ""){
			foreach(AppManager::listAll() as $data){
				if($data["dir_name"] == $uri[1]){
					$this->app = $data["rootname"];
					return;
				}
			}
		}
		$this->app = $app;
	}
	
	/**
	 * Get language
	 * @param string $code Language code
	 * @return string
	 */
	public function get($code){
		$langs = "en_US";
		if($_SESSION['account']['loggedIn'] == 0){
			$langs = POSConfigGlobal::$default_language;
		}else{
			$langs = $_SESSION['account']['lang'];
			if($langs == "def") $langs = POSConfigGlobal::$default_language;
		}
		if($langs == "loc") { //Previous value usr
			$httpaccept = str_replace("_","-",locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']));
			$locale = require(__ROOTDIR . "/bootstrap/locale.php");
			foreach($locale as $k=>$l){
				if(preg_match("/".$httpaccept."/",$k)){
					$langs = $k;
					break;
				}
			}
			unset($locale);
		}
		if(LangManager::$forced){
			$langs = LangManager::$forcedLang;
		}
		if(IO::exists(AppManager::listAll()[$this->app]["dir"]."/".$langs.".lang.php")){
			$LANG = include(AppManager::listAll()[$this->app]["dir"]."/".$langs.".lang.php");
			return($LANG[strtoupper($code)]);
			unset($LANG);
		}else{
			if(IO::exists(AppManager::listAll()[$this->app]["dir"]."/en-US.lang.php")){
				$LANG = include(AppManager::listAll()[$this->app]["dir"]."/en-US.lang.php");
				return($LANG[strtoupper($code)]);
				unset($LANG);
			}else
				return($code);
		}
	}

	/**
	 * Print language
	 * @param string $code Language code
	 */
	public function dump($code){
		echo ($this->get($code));
	}
}

/* Defining the first choosen language */
define("__LOCALE_NOW",LangManager::getDisplayedNow());
?>