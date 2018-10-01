<?php
use Dompdf\FrameDecorator\Page;
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * Manage applications
 */
class AppManager
{
	private static $AppList = null;

	/**
	 * @var Application
	 */
	private static $MainApp = null;

	public static function getMainApp()
	{
		return self::$MainApp;
	}

	/**
	 * Start the main application.
	 * @param string $app
	 * @return bool
	 */
	public static function startApp()
	{
		if (self::$MainApp instanceof Application)
			throw new PuzzleError("Main application can be only started once!");

		$defaultApp = __getURI("app") == "" ? POSConfigMultidomain::$default_application : __getURI("app");
		if ($defaultApp == "") {
			throw new PuzzleError("No any application to run!", "Please set one default application!");
		} else {
			self::$MainApp = (new Application())->prepare($defaultApp);
			try {
				if (!self::$MainApp->x_found() && POSConfigMultidomain::$super_application !== null) {
					//Reroute not found app to Super App
					self::$MainApp = (new Application())->prepare(POSConfigMultidomain::$super_application);
				}
				self::$MainApp->run();
				http_response_code(self::$MainApp->http_code);
			} catch (AppStartError $e) {
				abort(self::$MainApp->http_code, "Application Not Found", false);
				Template::setSubTitle("Not found");
			}
		}
	}

	/**
	 * Prepare table database for specified app
	 * @param string $rootname 
	 */
	public static function migrateTable($rootname)
	{
		$list_app = AppManager::listAll()[$rootname];
		if ($list_app["rootname"] != $rootname) throw new AppStartError("Application $rootname not found!", "", 404);
		
		/* Prepare the database */
		foreach (glob($list_app["dir"] . "/*.table.php") as $table_abstract) {
			$t = explode("/", rtrim($table_abstract, "/"));
			$table_name = str_replace(".table.php", "", end($t));
			$table_structure = include_ext($table_abstract);
			Database::newStructure("app_" . $list_app["rootname"] . "_" . $table_name, $table_structure);
		}
	}

	/**
	 * List all Applications
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
			$agroup = Database::readAll("app_users_grouplist")->data;
		} catch (PuzzleError $e) {
			/* Rebuild grouplist */
			Database::newStructure("app_users_grouplist", require_ext(__ROOTDIR . "/applications/accounts/grouplist.table.php"));
			$agroup = Database::readAll("app_users_grouplist");
		}

		$appsec = Database::readAll("app_security")->data;

		$a = [];
		foreach (IO::list_directory("/applications") as $dir) {
			if (!is_dir(IO::physical_path("/applications/$dir"))) continue;
			if ($dir != "." && $dir != "..") {
				if (IO::exists("/applications/$dir/manifest.ini")) {
					$manifest = parse_ini_file(IO::physical_path("/applications/$dir/manifest.ini"));
					if ($manifest["rootname"] == "") continue;
					if (isset($a[$manifest["rootname"]])) throw new PuzzleError("Rootname conflict detected on path: <b>" . __ROOTDIR . "/applications/" . $dir . "</b> and <b>" . $a[$manifest["rootname"]]["dir"] . "</b>");
					if (strlen($manifest["rootname"]) > 50) continue;
					switch ($manifest["rootname"]) {
						//Filter pre-used rootname
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
	public static function isInstalled($name)
	{
		if ($name == "") throw new PuzzleError("Name cannot be empty!");
		return (isset(AppManager::listAll()[$name]));
	}

	/**
	 * Check if app is currently default
	 * @param string $name Application root name
	 * @return bool
	 */
	public static function isDefault($name)
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
	public static function isOnGroup($group_id)
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
	public static function chownApp($appname, $newgroup)
	{
		if ($appname == "") throw new PuzzleError("Name cannot be empty!");
		if (!AppManager::isInstalled($appname)) throw new PuzzleError("Application not found!");

		if (Database::read("app_security", "system", "rootname", $appname) == "1") throw new PuzzleError("Cannot chown system app"); //Do not allow to change system own

		$allowed_level = AppManager::listAll()[$appname]["level"];
		$new_level = Database::read("app_users_grouplist", "level", "id", $newgroup);
		if ($new_level <= $allowed_level) {
			if (Database::read("app_security", "rootname", "rootname", $appname) != "")
				return (Database::exec("UPDATE `app_security` SET `group`='?' WHERE `rootname`='?';", $newgroup, $appname));
			else
				return (Database::exec("INSERT INTO `app_security` (`rootname`, `group`, `system`) VALUES ('?', '?', 0);", $appname, $newgroup));
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
	public static function setDefaultByName($name)
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
	public static function getNameFromDirectory($directory)
	{
		$directory = IO::physical_path("/applications/$directory");
		if (!IO::exists("$directory/manifest.ini")) throw new PuzzleError("Application not found!");
		$manifest = parse_ini_file("$directory/manifest.ini");
		if ($manifest["rootname"] == "") throw new PuzzleError("Application manifest not initiated correctly!");
		return $manifest["rootname"];
	}
}

/**
 * Application instance
 */
class Application
{
	public static $MainAppStarted = false;

	private $appfound = false;
	private $forbidden = 1;
	private $apprunning = 0;
	private $prepared = false;
	private $group;
	private $level;

	/**
	 * Root directory. /applications/$appdir
	 */
	private $rootdir;

	/**
	 * User data directory. /user_data/$appdir
	 */
	private $datadir;

	/**
	 * URL path. http://localhost/puzzleos/$yourApp
	 * URL path. http://localhost/$yourApp
	 */
	private $uri;

	/**
	 * Physical directory. /www/cms/$appdir or C:/htdocs/cms/$appdir
	 */
	private $path;

	/**
	 * Application Name
	 */
	private $title;

	/**
	 * Application Description
	 */
	private $desc;

	/**
	 * Application root name
	 */
	private $appname;

	/**
	 * A variable that use to pass data from controller to view
	 */
	private $bundle = [];

	/**
	 * HTTP Code of an app
	 */
	private $http_code = 404;

	private $isMainApp = false;

	public function __construct($appname = "")
	{
		if ($appname != "") $this->run($appname);
		return $this;
	}

	public function __get($variable)
	{
		switch ($variable) {
			case "title":
				return ($this->title);
			case "desc":
				return ($this->desc);
			case "appname":
				return ($this->appname);
			case "path":
				return ($this->path);
			case "rootdir":
				return ($this->rootdir);
			case "uri":
			case "url":
				return ($this->uri);
			case "isForbidden":
				return ($this->forbidden == 1);
			case "isMainApp":
				return ($this->isMainApp);
			case "http_code":
				return ($this->http_code);
			default:
				throw new PuzzleError("Invalid input! => " . $variable);
		}
	}

	public function __set($variable, $value)
	{
		switch ($variable) {
			default:
				throw new PuzzleError("All properties are read-only!");
				break;
		}
	}

	private function __papp()
	{
		return (object)[
			"title" => $this->title,
			"desc" => $this->desc,
			"appname" => $this->appname,
			"path" => $this->path,
			"rootdir" => $this->rootdir,
			"uri" => $this->uri,
			"url" => $this->uri,
			"bundle" => &$this->bundle,
			"isMainApp" => $this->isMainApp,
			"http_code" => &$this->http_code
		];
	}

	public function x_found()
	{
		if (!is_callbyme()) throw new PuzzleError(__class__ . " violation!");
		return $this->appfound;
	}

	/**
	 * NOTE!:
	 * Prepare information for this app, NOT run the app
	 * To run the app, use run()
	 * @param string $name
	 * @return Application
	 */
	public function prepare($name)
	{
		if (!$this->prepared) {
			$meta = AppManager::listAll()[$name];
			$this->appname = $name;
			$this->appfound = false;

			if ($meta["rootname"] == $name) {
				$dir = $meta["dir_name"];
				$this->level = $meta["level"];
				$this->group = $meta["group"];
				$this->appfound = true;
				$this->title = $meta["title"];
				$this->desc = $meta["desc"];
				$this->path = IO::physical_path("/applications/" . $dir);
				$this->uri = __SITEURL . "/applications/" . $dir;
				$this->rootdir = "/applications/" . $dir;
				$this->datadir = "/user_data/" . $this->appname;
			}
			$this->prepared = true;
		}
		return $this;
	}

	/**
	 * Run an app in this instance
	 * @param string $name
	 * @return bool
	 */
	public function run($name = "")
	{
		$this->prepare($name);
		AppManager::migrateTable($name);

		if (!__isCLI()) {
			if (!self::$MainAppStarted) {
				self::$MainAppStarted = true;
				/**
				 * In multidomain mode, there is a feature called App resctriction,
				 * meaning the app cannot start as the main user interface for that session.
				 * But, that app can be still called and run by another apps if necessary,
				 * also it's services and menus still can be called
				 */
				if (POSConfigGlobal::$use_multidomain) {
					if (in_array($this->appname, POSConfigMultidomain::$restricted_app)) {
						$this->http_code = 404;
						throw new AppStartError("Application '{$this->appname}' not found", "", 404);
						return false;
					}
				}

				//Walaupun grup di database didefinisikan, tapi jika level aplikasi lebih kuat, maka kita ikut level aplikasi untuk autentikasi.
				$group_level = Accounts::getAuthLevel($this->group);

				if ($group_level > $this->level) {
					$this->forbidden = !Accounts::authAccess($this->level);
				} else {
					$this->forbidden = !Accounts::authAccessAdvanced($this->group);
				}

				$this->isMainApp = true;
			} else {
				$this->forbidden = !Accounts::authAccess($this->level);
				$this->isMainApp = false;
			}
		} else {
			//On CLI, user always authenticated as USER_AUTH_SU
			$this->forbidden = 0;
			$this->isMainApp = true;
		}

		if ($this->appfound) {
			$this->http_code = 200;
			if (!$this->forbidden) {
				if (include_once_ext($this->path . "/control.php", [
					"appProp" => $this->__papp()
				]) !== false) {
					$this->apprunning = 1;
					return true;
				}
			} else {
				$this->http_code = 403;
				throw new AppStartError("Application '{$this->appname}' forbidden", "", 403);
				return false;
			}
		}

		$this->http_code = 404;
		throw new AppStartError("Application '{$this->appname}' not found", "", 404);
		return false;
	}

	/**
	 * Load the small view of an app like widget
	 * You can read $...param from viewSmall.php using $arguments
	 * @param mixed ...$arguments Put anything that the app requires
	 */
	public function loadView(...$arguments)
	{
		if ($this->appfound && $this->apprunning == 1) {
			if ($this->forbidden == 0) {
				if (include_ext($this->path . "/viewSmall.php", [
					"appProp" => $this->__papp(),
					"arguments" => func_get_args()
				]) === false) {
					$this->http_code = 500;
					throw new AppStartError("Cannot load view for app <b>'{$this->title}'</b><br>", "", 500);
				}
			}
		}
	}

	/**
	 * Include Application file under Application context
	 * Which means, file can access $appProp
	 * @param mixed ...$arguments Put anything that the app requires
	 */
	public function loadContext($filename)
	{
		if ($this->appfound) {
			return (include_ext($this->path . "/" . ltrim($filename, "/"), [
				"appProp" => $this->__papp()
			]));
		}
	}

	/*
	 * Load the main page of the app
	 */
	public function loadMainView()
	{
		if ($this->appfound && $this->apprunning == 1) {
			if ($this->forbidden == 0) {
				if (include_once_ext(
					$this->path . "/viewPage.php",
					array_merge(["appProp" => $this->__papp()], $this->bundle)
				) === false) {
					$this->http_code = 500;
					throw new AppStartError("Cannot load view for app <b>'{$this->title}'</b><br>", "", 500);
				}
			}
		}
	}
}
?>
