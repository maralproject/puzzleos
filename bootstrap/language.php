<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * Language Manager for Administration
 */
class LangManager
{
	/** 
	 * Define if language is forced
	 * @var integer
	 */
	private static $forced = false;

	/**
	 * Get Language Form in HTML
	 * @param string $id DOM Element ID
	 * @param string $val Active language value
	 * @param bool $without_default Hide default language option
	 * @param bool $location_option Show based on location language option
	 * @param bool $use_logo Show icon in HTML form
	 * @return string
	 */
	public static function getForm($id, $val, $without_default = true, $location_option = true, $use_logo = true)
	{
		$id = $id == "" ? "lang" : $id;
		$val = $val == "" ? "lang" : $val;
		//Increase the language option for your user here!
		$r = '<div class="input-group">
				' . ($use_logo ? '<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-language"></i></span>' : '') . '
				<select name="' . $id . '" id="' . $id . '_selector" class="form-control languageList" data-live-search="true">';
		if ($location_option) $r .= '<option value="loc"' . ($val == "loc" ? ' selected' : '') . '>Based on location</option>';
		if (!$without_default) $r .= '<option value="def"' . ($val == "def" ? ' selected' : '') . '>Default</option>';
		
		/* Finding through app which language are available */
		$locale = require(__ROOTDIR . "/bootstrap/locale.php");
		$temp = [];

		foreach (IO::list_directory("applications") as $appdir) {
			if ($appdir == "." || $appdir == "..") continue;
			if (!is_dir(IO::physical_path("applications/$appdir"))) continue;
			foreach (glob(IO::physical_path("applications/$appdir/*.lang.php")) as $language_file) {
				$li = explode("/", rtrim($language_file, "/"));
				$language = str_replace(".lang.php", "", end($li));
				//Set a flag
				$temp[$language] = [$language, $locale[$language]];
			}
		}
		unset($locale);

		foreach ($temp as $l) {
			$r .= "<option value=\"" . $l[0] . "\"" . ($val == $l[0] ? ' selected' : '') . ">" . $l[1] . "</option>";
		}
		$r .= '</select></div>';
		return ($r);
	}

	/**
	 * Print Language Form in HTML
	 * @param string $id DOM Element ID
	 * @param string $val Active language value
	 * @param bool $without_default Hide default language option
	 * @param bool $location_option Show based on location language option
	 * @param bool $use_logo Show icon in HTML form
	 */
	public static function dumpForm($id, $val, $without_default = true, $location_option = true, $use_logo = true)
	{
		echo (LangManager::getForm($id, $val, $without_default, $location_option, $use_logo));
	}

	/**
	 * Force to change displayed language
	 * @param string $langCode Language code
	 */
	public static function force($language_code)
	{
		$locale = require "locale.php";
		if (!isset($locale[$language_code])) return false;
		self::$forced = $language_code;
		return true;
	}

	public static function isForced()
	{
		return self::$forced;
	}

	/**
	 * Force to change displayed language
	 * @return string
	 */
	public static function getDisplayedNow($fallback = "en-US")
	{
		if (self::$forced !== false) {
			return self::$forced;
		} else {
			if ($_SESSION['account']['loggedIn'] == 0) {
				$langs = POSConfigGlobal::$default_language;
			} else {
				$langs = $_SESSION['account']['lang'];
				if ($langs == "def") $langs = POSConfigGlobal::$default_language;
			}

			if ($langs == "loc") { //Previous value usr
				$httpaccept = str_replace("_", "-", locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']));
				$locale = require "locale.php";
				foreach ($locale as $k => $l) {
					if (preg_match("/" . $httpaccept . "/", $k)) {
						$langs = $k;
						break;
					}
				}
				if ($langs == "") $langs = $fallback;
			}

			return $langs;
		}
	}
}

/**
 * Language Instance
 */
class Language
{
	private $set = [];

	public function __set($v, $l)
	{
		if ($v == "app")
			$this->__construct($l);
		else
			throw new PuzzleError("Invalid input!");
	}

	public function __construct($app = "")
	{
		//If app is empty, get the referer and get the app
		$uri = explode("/", str_replace(__ROOTDIR . "/", "", btfslash(debug_backtrace()[0]["file"])));
		if ($uri[0] == "applications" && $app == "") {
			$dir = $uri[1];
		} elseif ($app != "") {
			$dir = AppManager::listAll()[$app]["dir_name"];
			if ($dir == "") throw new PuzzleError("Cannot find $app application");
		} else {
			throw new PuzzleError("Language can only be loaded from Application");
		}

		//Define Fallback language, in case language not defined in application
		$fallback = "en_US";

		if (LangManager::isForced() !== false) {
			$langs = LangManager::isForced();
		} else {
			$langs = LangManager::getDisplayedNow();
		}

		if (file_exists(__ROOTDIR . "/applications/$dir/$langs.lang.php")) {
			$this->set = include_ext(__ROOTDIR . "/applications/$dir/$langs.lang.php");
		} else {
			if (file_exists(__ROOTDIR . "/applications/$dir/$fallback.lang.php")) {
				$this->set = include_ext(__ROOTDIR . "/applications/$dir/$fallback.lang.php");
			} else {
				$this->set = [];
			}
		}
	}

	/**
	 * Get language
	 * @param string $code Language code
	 * @return string
	 */
	public function get($code)
	{
		return ($this->set[strtoupper($code)]);
	}

	/**
	 * Print language
	 * @param string $code Language code
	 */
	public function dump($code)
	{
		echo ($this->get($code));
	}
}
