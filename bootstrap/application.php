<?php

/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2020 PT SIMUR INDONESIA
 */

/**
 * Application Manager
 */
class AppManager
{
	private static $AppList = null;
	private static $MainApp = null;

	/**
	 * Get main application
	 * @return iApplication
	 */
	public static function getMainApp()
	{
		return self::$MainApp;
	}

	/**
	 * Start the main application.
	 * Main application can only be started once.
	 * @param string $x_app
	 * @return bool
	 */
	public static function startApp(string $x_app = null)
	{
		if ($x_app !== null && !is_callbyme()) throw new PuzzleError("Startup sequence violation");
		if (self::$MainApp !== null) throw new PuzzleError("Main application can be only started once");

		$request = $x_app ?? request(0);
		if ($request == "") {
			throw new PuzzleError("No any app to run! Please set a default application.");
		} else {
			try {
				self::$MainApp = iApplication::run($request, true);
			} catch (AppStartError $e) {
				switch ($e->getCode()) {
					case APP_ERROR_NOTFOUND:
						if ($x_app == null && POSConfigMultidomain::$super_application !== null) {
							self::startApp(POSConfigMultidomain::$super_application);
						} else {
							abort(404, "Not Found", false);
							Template::setSubTitle("Not found");
						}
						break;
					case APP_ERROR_FORBIDDEN:
						abort(403, "Forbidden", false);
						break;
				}
				// Next, let the template handle these thing from Template::loadTemplate();
			} catch (\Throwable $e) {
				PuzzleError::printErrorPage($e);
			}
		}
	}

	/**
	 * Prepare table database for specified app.
	 * @param string $rootname 
	 */
	public static function migrateTable(string $rootname)
	{
		$list_app = AppManager::listAll()[$rootname];
		if ($list_app["rootname"] != $rootname) {
			throw new PuzzleError("Application $rootname cannot be found.");
		}

		/* Prepare the database */
		$lookupdir = array_merge(
			glob($list_app["dir"] . "/*.table.php"),
			glob($list_app["dir"] . "/apptables/*.table.php")
		);
		foreach ($lookupdir as $table_abstract) {
			set_time_limit(0);
			$t = explode("/", rtrim($table_abstract, "/"));
			$table_name = str_replace(".table.php", "", end($t));
			$table_structure = include_ext($table_abstract);
			Database::newStructure("app_" . $list_app["rootname"] . "_" . $table_name, $table_structure);
		}
		set_time_limit(TIME_LIMIT);
	}

	/**
	 * List all Applications.
	 * @return array
	 */
	public static function listAll()
	{
		if (self::$AppList != null) return self::$AppList;

		$fval = function ($a, $f, $v) {
			foreach ($a as $t) if ($t[$f] == $v) return $t;
		};

		/* Caching database operation */
		try {
			$agroup = Database::readAll("app_users_grouplist");
		} catch (PuzzleError $e) {
			/* Rebuild grouplist */
			Database::newStructure("app_users_grouplist", require_ext(__ROOTDIR . "/applications/accounts/grouplist.table.php"));
			$agroup = Database::readAll("app_users_grouplist");
		}

		$appsec = Database::readAll("app_security");

		$a = [];
		foreach (IO::list_directory("/applications") as $dir) {
			if (!is_dir(IO::physical_path("/applications/$dir"))) continue;
			if ($dir != "." && $dir != "..") {
				if (IO::exists("/applications/$dir/manifest.ini")) {
					$manifest = parse_ini_file(IO::physical_path("/applications/$dir/manifest.ini"));
					if ($manifest["rootname"] == "") continue;
					if (isset($a[$manifest["rootname"]])) throw new PuzzleError("Rootname conflict detected on path: <b>" . __ROOTDIR . "/applications/" . $dir . "</b> and <b>" . $a[$manifest["rootname"]]["dir"] . "</b>");
					if (strlen($manifest["rootname"]) > 50) continue;

					#Filter pre-reserved rootname
					switch ($manifest["rootname"]) {
						case "assets":
						case "res":
						case "security":
							continue;
						default:
							break;
					}

					/* Make sure that rootname always lowercase */
					$manifest["rootname"] = strtolower($manifest["rootname"]);

					$group = $fval($appsec, "rootname", $manifest["rootname"])["group"];
					if (!isset($group)) $group = $fval($agroup, "level", $manifest["permission"])["id"];

					$a[$manifest["rootname"]] = [
						"name" => $manifest["rootname"],
						"rootname" => $manifest["rootname"],
						"dir" => __ROOTDIR . "/applications/$dir",
						"dir_name" => $dir,
						"title" => $manifest["title"],
						"desc" => $manifest["description"],
						"default" => ($manifest["canBeDefault"] == 0 ? APP_CANNOT_DEFAULT : (POSConfigMultidomain::$default_application == $manifest["rootname"] ? APP_DEFAULT : APP_NOT_DEFAULT)),
						"level" => $manifest["permission"],
						"group" => $group,
						"services" => explode(",", trim($manifest["services"])),
						"menus" => explode(",", trim($manifest["menus"])),
						"system" => ($fval($appsec, "rootname", $manifest["rootname"])["system"] == "1")
					];
					if ($a[$manifest["rootname"]]["services"][0] == "") $a[$manifest["rootname"]]["services"] = [];
					if ($a[$manifest["rootname"]]["menus"][0] == "") $a[$manifest["rootname"]]["menus"] = [];
				}
			}
		}
		self::$AppList = $a;
		return $a;
	}

	/**
	 * Check if an app is installed or not
	 * @param string $name Application root name
	 * @return bool
	 */
	public static function isInstalled(string $name)
	{
		if ($name == "") throw new PuzzleError("Name cannot be empty!");
		return (isset(AppManager::listAll()[$name]));
	}

	/**
	 * Check if app is currently default
	 * @param string $name Application root name
	 * @return bool
	 */
	public static function isDefault(string $name)
	{
		if ($name == "") throw new PuzzleError("Name cannot be empty!");
		if (!AppManager::isInstalled($name)) throw new PuzzleError("Application not found!");
		return ($name == POSConfigMultidomain::$default_application);
	}

	/**
	 * See if there is some application registered to a user group
	 * @param integer $group_id Group ID
	 * @return bool
	 */
	public static function isOnGroup(int $group_id)
	{
		foreach (AppManager::listAll() as $data)
			if ($data["group"] == $group_id) return true;
		return false;
	}

	/**
	 * Change application group ownership
	 * @param integer $appname Application rootname
	 * @param integer $newgroup Group ID
	 * @return bool
	 */
	public static function chownApp(int $appname, int $newgroup)
	{
		if ($appname == "") throw new PuzzleError("Name cannot be empty!");
		if (!AppManager::isInstalled($appname)) throw new PuzzleError("Application not found!");

		if (Database::read("app_security", "system", "rootname", $appname) == "1") throw new PuzzleError("Cannot chown system app"); //Do not allow to change system own

		$allowed_level = AppManager::listAll()[$appname]["level"];
		$new_level = Database::read("app_users_grouplist", "level", "id", $newgroup);
		if ($new_level <= $allowed_level) {
			if (Database::read("app_security", "rootname", "rootname", $appname) != "")
				return (Database::execute("UPDATE `app_security` SET `group`='?' WHERE `rootname`='?';", $newgroup, $appname));
			else
				return (Database::execute("INSERT INTO `app_security` (`rootname`, `group`, `system`) VALUES ('?', '?', 0);", $appname, $newgroup));
		} else {
			throw new PuzzleError("Cannot set the owner of the app lower than allowed!");
		}
		return false;
	}

	/**
	 * Set default app by application root name
	 * @param string $name Application root name
	 * @return bool
	 */
	public static function setDefaultByName(string $name)
	{
		if ($name == "") throw new PuzzleError("Name cannot be empty!");
		if (!AppManager::isInstalled($name)) throw new PuzzleError("Application not found!");
		POSConfigMultidomain::$default_application = $name;
		return POSConfigMultidomain::commit();
	}

	/**
	 * Find application rootname based on it's directory name.
	 * e.g. "accounts" not "/applications/accounts"
	 * @param string $directory
	 * @return string
	 */
	public static function getNameFromDirectory(string $directory)
	{
		$directory = IO::physical_path("/applications/$directory");
		if (!IO::exists("$directory/manifest.ini")) throw new PuzzleError("Application not found!");
		$manifest = parse_ini_file("$directory/manifest.ini");
		if ($manifest["rootname"] == "") throw new PuzzleError("Application manifest not initiated correctly!");
		return $manifest["rootname"];
	}
}

/**
 * Application Model
 * @property-read string $title Application Name
 * @property-read string $desc Application Description
 * @property-read string $rootname Rootname of the app
 * @property-read string $path Physical directory. /www/cms/$appdir or C:/htdocs/cms/$appdir
 * @property-read string $rootdir Root directory. /applications/$appdir
 * @property-read bool $isMainApp Check if this app is the main app
 */
class iApplication
{
	private static $loaded = [];

	private $rootname;
	private $title;
	private $desc;
	private $path;
	private $rootdir;

	/**
	 * Group id in which this app belongs to
	 */
	private $group;

	/**
	 * Application Guard Level
	 * 0 => Your app only visible to Superuser
	 * 1 => Your app visible to Employee and Superuser
	 * 2 => Your app visible to Registered User, Employee, and Superuser
	 * 3 => Your app visible to everyone including Guest
	 */
	private $level;


	/**
	 * Flag: this app is a call from AppManager
	 * to start the main app
	 */
	private $_xStartup = false;

	/**
	 * A variable that use to pass data from controller to view
	 */
	private $bundle = [];

	/**
	 * Flag: This app still in preload mode.
	 */
	private $preload_mode = true;

	private function guardCheck()
	{
		if (!is_cli()) {
			if ($this->_xStartup) {
				/**
				 * In multidomain mode, there is a feature called App resctriction,
				 * meaning the app cannot start as the main user interface for that session.
				 * But, that app can be still called and run by another apps if necessary,
				 * also it's services and menus still can be called
				 */
				if (POSConfigGlobal::$use_multidomain) {
					if (in_array($this->rootname, POSConfigMultidomain::$restricted_app)) {
						throw new AppStartError("Application forbidden", "", APP_ERROR_FORBIDDEN);
					}
				}

				// Walaupun grup di database didefinisikan, tapi jika level aplikasi lebih kuat, maka kita ikut level aplikasi untuk autentikasi.
				$group_level = PuzzleUserGroup::get($this->group)->level;
				if ($group_level > $this->level) {
					$forbidden = !PuzzleUser::isAccess($this->level);
				} else {
					$forbidden = !PuzzleUser::isGroupAccess(PuzzleUserGroup::get($this->group));
				}

				if ($forbidden) {
					throw new AppStartError("Application forbidden", "", APP_ERROR_FORBIDDEN);
				}
			}
		}
	}

	private function getAppProp()
	{
		return (object) [
			"title" => $this->title,
			"desc" => $this->desc,
			"rootname" => $this->rootname,
			"path" => $this->path,
			"rootdir" => $this->rootdir,
			"bundle" => &$this->bundle,
			"isMainApp" => $this->_xStartup
		];
	}

	public function __get($variable)
	{
		switch ($variable) {
			case "title":
				return ($this->title);
			case "desc":
				return ($this->desc);
			case "rootname":
				return ($this->rootname);
			case "path":
				return ($this->path);
			case "rootdir":
				return ($this->rootdir);
			case "isMainApp":
				return ($this->_xStartup);
			default:
				throw new PuzzleError("Invalid input " . $variable);
		}
	}

	/**
	 * Lights up the app. Include the control.php
	 * @return self
	 */
	public function lightup(bool $_xStartup = false)
	{
		if ($this->preload_mode) {
			if ($_xStartup && debug_backtrace(1, 2)[1]['class'] != self::class) throw new PuzzleError("Startup sequence violation");
			$this->_xStartup = $_xStartup;
			AppManager::migrateTable($this->rootname);
			$this->guardCheck();
			include_once_ext($this->path . "/control.php", [
				"appProp" => $this->getAppProp()
			]);
			$this->preload_mode = false;
		}
		return $this;
	}

	private function __construct(string $rootname, bool $preload = false)
	{
		$meta = AppManager::listAll()[$rootname];
		if ($meta["rootname"] == $rootname) {
			$dir = $meta["dir_name"];
			$this->level = $meta["level"];
			$this->group = $meta["group"];
			$this->title = $meta["title"];
			$this->desc = $meta["desc"];
			$this->path = IO::physical_path("/applications/" . $dir);
			$this->rootdir = "/applications/" . $dir;
			$this->rootname = $rootname;
			if (!$preload) $this->lightup();
		} else {
			throw new AppStartError("Application $rootname not found.", "", APP_ERROR_NOTFOUND);
		}
	}

	/**
	 * Load the small view of an app like widget
	 * You can read $...param from viewSmall.php using $arguments
	 * @param mixed ...$arguments Put anything that the app requires
	 */
	public function loadView(...$arguments)
	{
		if ($this->preload_mode) throw new AppStartError("Application is not started", "", APP_ERROR_NOTRUNNING);
		if (include_ext($this->path . "/viewSmall.php", [
			"appProp" => $this->getAppProp(),
			"arguments" => $arguments
		]) === false) {
			throw new AppStartError("Cannot load view for this app", "", APP_ERROR_NOVIEW);
		}
	}

	/**
	 * Include Application file under Application context
	 * Which means, the file can access $appProp
	 */
	public function loadContext(string $filename)
	{
		return (include_ext($this->path . "/" . ltrim($filename, "/"), [
			"appProp" => $this->getAppProp()
		]));
	}

	/*
	 * Load the main page of the app
	 */
	public function loadMainView()
	{
		if ($this->preload_mode) throw new AppStartError("Application is not started", "", APP_ERROR_NOTRUNNING);
		if (include_once_ext(
			$this->path . "/viewPage.php",
			array_merge(["appProp" => $this->getAppProp()], $this->bundle)
		) === false) {
			throw new AppStartError("Cannot load view for this app", "", APP_ERROR_NOVIEW);
		}
	}

	/**
	 * Start an application
	 * @return self
	 */
	public static function run(string $rootname, bool $_xStartup = false)
	{
		if ($_xStartup) {
			if (debug_backtrace(1, 2)[1]['class'] != AppManager::class) throw new PuzzleError("Startup sequence violation");
		}
		return (self::$loaded[$rootname] ?? self::$loaded[$rootname] = new self($rootname))->lightup($_xStartup);
	}

	/**
	 * Preload an app
	 * @return self
	 */
	public static function preload(string $rootname)
	{
		return self::$loaded[$rootname] ?? self::$loaded[$rootname] = new self($rootname, true);
	}
}
