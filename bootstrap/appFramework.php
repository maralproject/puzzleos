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

define("APP_DEFAULT", 1);
define("APP_NOT_DEFAULT", 0);
define("APP_CANNOT_DEFAULT", 3);

/**
 * Manage applications
 */
class AppManager{
	
	/**
	 * Define where main application is started or not
	 * @var bool
	 */
	public static $MainAppStarted = false;
	
	/**
	 * List of all installed applications
	 * @var array
	 */ 
	private static $AppList = NULL;
	
	/**
	 * Define main application instance 
	 * @var Application
	 */ 
	public static $MainApp = NULL;
	
	/**
	 * NOTE: Only allowed to run from index.php once
	 * Start the main application. You can use this to initiate app at debug.php
	 * @param string $app
	 * @return bool
	 */
	public static function startApp($app = ""){
		if(self::$MainAppStarted) throw new PuzzleError("Main application can be only started once!");
		POSGlobal::$http_code = 200; //200:ok, 403:Forbidden, 404:Not Found
		$defaultApp = POSConfigMultidomain::$default_application;		
		if(__getURI("app") == "" && $defaultApp == ""){
			throw new PuzzleError("No any application to run!","Please set one default application!");
		}else{
			self::$MainApp = new Application();
			if(POSConfigGlobal::$use_multidomain){
				if(in_array((__getURI("app") == ""?$defaultApp:__getURI("app")),POSConfigMultidomain::$restricted_app)){
					Template::setSubTitle("Not found");
					POSGlobal::$http_code = 404;
					header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
					self::$MainAppStarted = true;
					return false;
				}
			}
			try{
				self::$MainApp->run(__getURI("app") == ""?$defaultApp:__getURI("app"));
			}catch(AppStartError $e){
				switch($e->getCode()){
				case 404:
					POSGlobal::$http_code = 404;
					header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
					break;
				case 403:
					POSGlobal::$http_code = 403;
					header($_SERVER["SERVER_PROTOCOL"]." 403 Access Forbidden", true, 403);
					break;
				}
				Template::setSubTitle("Not found");	
				return false;
			}
		}
		self::$MainAppStarted = true;
		return(true);
	}
	
	/**
	 * List all Applications
	 * @return array
	 */
	public static function listAll(){
		if(self::$AppList != NULL) return self::$AppList;
		
		$fval = function($a,$f,$v){
			foreach($a as $t){
				if($t[$f] == $v) return $t;
			}
		};
		
		/* Caching database operation */
		try{
			$agroup = Database::readAll("app_users_grouplist")->data;
		}catch(PuzzleError $e){
			/* Rebuild grouplist */
			Database::newStructure("app_users_grouplist",require_once(__ROOTDIR . "/applications/accounts/grouplist.table.php"));
			$agroup = Database::readAll("app_users_grouplist");
		}
		
		$appsec = Database::readAll("app_security")->data;
		
		$a = [];
		foreach(IO::list_directory("/applications") as $dir){
			if(!is_dir(IO::physical_path("/applications/$dir"))) continue;
			if($dir != "." && $dir != ".."){
				if(IO::exists("/applications/$dir/manifest.ini")){
					$manifest = parse_ini_file(IO::physical_path("/applications/$dir/manifest.ini"));
					if($manifest["rootname"] == "") continue;
					if(isset($a[$manifest["rootname"]])) continue;
					switch($manifest["rootname"]){
						//Filter pre-used rootname
						case "security":
							continue;
						default:
							break;
					}
					
					/* Make sure that rootname always lowercase */
					$manifest["rootname"] = strtolower($manifest["rootname"]);					
					
					$group = $fval($appsec,"rootname",$manifest["rootname"])["group"];
					if($group == "" || $group == "NULL") $group = $fval($agroup,"level",$manifest["permission"])["id"];
					
					$a[$manifest["rootname"]] = [
						"name" 		=> $manifest["rootname"],
						"rootname"	=> $manifest["rootname"],
						"dir" 		=> __ROOTDIR."/applications/" . $dir,
						"dir_name"	=> $dir,
						"title" 	=> $manifest["title"],
						"desc" 		=> $manifest["description"],
						"default" 	=> ($manifest["canBeDefault"] == 0 ? APP_CANNOT_DEFAULT : (POSConfigMultidomain::$default_application == $manifest["rootname"] ? APP_DEFAULT : APP_NOT_DEFAULT)),
						"level" 	=> $manifest["permission"],
						"permission"=> $group,
						"group" 	=> $group,
						"services" 	=> explode(",",trim($manifest["services"])),
						"menus"		=> explode(",",trim($manifest["menus"])),
						"system" 	=> ($fval($appsec,"rootname",$manifest["rootname"])["system"] == "1")
					];
					if($a[$manifest["rootname"]]["services"][0] == "") $a[$manifest["rootname"]]["services"] = [];
					if($a[$manifest["rootname"]]["menus"][0] == "") $a[$manifest["rootname"]]["menus"] = [];
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
	public static function isInstalled($name){
		if($name == "") throw new PuzzleError("Name cannot be empty!");
		return(isset(AppManager::listAll()[$name]));
	}

	/**
	 * Check if app is currently default
	 * @param string $name Application root name
	 * @return bool
	 */
	public static function isDefault($name){
		if($name == "") throw new PuzzleError("Name cannot be empty!");
		if(!AppManager::isInstalled($name)) throw new PuzzleError("Application not found!");
		return($name == POSConfigMultidomain::$default_application);
	}

	/**
	 * See if there is some application registered to a user group
	 * @param integer $group_id Group ID
	 * @return bool
	 */
	public static function isOnGroup($group_id){
		foreach(AppManager::listAll() as $data){
			if($data["group"] == $group_id)	return true;
		}
		return false;
	}

	/**
	 * Change application group ownership
	 * @param integer $appname Application rootname
	 * @param integer $newgroup Group ID
	 * @return bool
	 */
	public static function chownApp($appname, $newgroup){
		if($appname == "") throw new PuzzleError("Name cannot be empty!");
		if(!AppManager::isInstalled($appname)) throw new PuzzleError("Application not found!");
		
		if(Database::read("app_security","system","rootname",$appname) == "1") throw new PuzzleError("Cannot chown system app"); //Do not allow to change system own
		
		$allowed_level = AppManager::listAll()[$appname]["level"];
		$new_level = Database::read("app_users_grouplist","level","id",$newgroup);
		if($new_level <= $allowed_level){
			if(Database::read("app_security","rootname","rootname",$appname) != "")
				return(Database::exec("UPDATE `app_security` SET `group`='?' WHERE `rootname`='?';",$newgroup,$appname));
			else
				return(Database::exec("INSERT INTO `app_security` (`rootname`, `group`, `system`) VALUES ('?', '?', 0);", $appname, $newgroup));
		}else{
			throw new PuzzleError("Cannot set the owner of the app lower than allowed!");
		}
		return false;
	}

	/**
	 * Set default app by application root name
	 * @param string $name Application root name
	 * @return bool
	 */
	public static function setDefaultByName($name){		
		if($name == "") throw new PuzzleError("Name cannot be empty!");
		if(!AppManager::isInstalled($name)) throw new PuzzleError("Application not found!");
		POSConfigMultidomain::$default_application = $name;
		return POSConfigMultidomain::commit();
	}
	
	/**
	 * Find application rootname based on it's directory name.
	 * e.g. "accounts" not "/applications/accounts"
	 * @param string $directory
	 * @return string
	 */
	public static function getNameFromDirectory($directory){
		$directory = IO::physical_path("/applications/$directory");
		if(!IO::exists("$directory/manifest.ini")) throw new PuzzleError("Application not found!");
		$manifest = parse_ini_file("$directory/manifest.ini");
		return $manifest["rootname"];
	}
}

/**
 * Application instance
 */
class Application{
	private $appfound = false;
	private $forbidden = 1;
	private $view_loaded;
	private $apprunning = 0;

	/**
	 * Root directory. /applications/$appdir
	 */
	protected $rootdir;
	
	/**
	 * User data directory. /user_data/$appdir
	 */
	protected $datadir;
	
	/**
	 * URL path. http://localhost/puzzleos/$yourApp
	 * URL path. http://localhost/$yourApp
	 */
	protected $uri;
		
	/**
	 * Physical directory. /www/cms/$appdir or C:/htdocs/cms/$appdir
	 */
	protected $path;

	/**
	 * Application Name
	 */
	protected $title;
	/**
	 * Application Description
	 */
	protected $desc;
	/**
	 * Application root name
	 */
	protected $appname;
	
	public function __construct($appname = ""){
		if($appname != "") $this->run($appname);
		return $this;
	}

	public function __get($variable){
        switch($variable){
		case "title":
			return($this->title);
			break;
		case "desc":
			return($this->desc);
			break;
		case "appname":
			return($this->appname);
			break;
		case "path":
			return($this->path);
			break;
		case "rootdir":
			return($this->rootdir);
			break;
		case "uri":
		case "url":
			return($this->uri);
			break;
		case "isForbidden":
			return($this->forbidden == 1);
			break;
		default:
			throw new PuzzleError("Invalid input! => " . $variable);
			break;
		}
    }

	public function __set($variable,$value){
        switch($variable){
		default:
			throw new PuzzleError("All properties are read-only!");
			break;
		}
    }
	
	/**
	 * NOTE!:
	 * Prepare information for this app, NOT run the app
	 * To run the app, use run()
	 * @param string $name
	 * @return Application
	 */
	public function prepare($name){
		$list_app = AppManager::listAll()[$name];
		if($list_app["rootname"] != $name) {
			throw new AppStartError("Application $name not found!","",404);
		}
		
		$dir = $list_app["dir_name"];
		$this->appfound = true;
		
		$this->title = $list_app["title"];
		$this->desc = $list_app["desc"];
		$this->appname = $name;
		$this->path = IO::physical_path("/applications/".$dir);
		$this->uri = __SITEURL . "/applications/" . $dir;
		$this->view_loaded = 0;
		$this->rootdir = "/applications/".$dir;
		$this->datadir = "/user_data/".$this->appname;
		
		return $this;
	}

	/**
	 * Run an app in this instance
	 * @param string $name
	 * @return bool
	 */
	public function run($name){
		$list_app = AppManager::listAll()[$name];
		if($list_app["rootname"] != $name) {
			throw new AppStartError("Application `$name` not found!",__HTTP_URI,404);
		}
		
		/* Prepare the database */
		foreach(glob($list_app["dir"] . "/*.table.php") as $table_abstract){
			$t = explode("/",rtrim($table_abstract,"/"));
			$table_name = str_replace(".table.php","",end($t));			
			$table_structure = include($table_abstract);
			Database::newStructure("app_" . $list_app["rootname"] . "_" . $table_name,$table_structure);
		}
		
		$dir = $list_app["dir_name"];
		$this->appfound = true;
		
		if(!defined("__POSCLI")){
			if(!AppManager::$MainAppStarted){
				/* In multidomain mode, there is a feature called App resctriction,
				 * meaning the app cannot start as the main user interface for that session.
				 * But, that app can be still called and run by another apps if necessary,
				 * also it's services and menus still can be called
				 */
				if(POSConfigGlobal::$use_multidomain){
					if(in_array($list_app["rootname"],POSConfigMultidomain::$restricted_app)){					
						$this->appfound = false;
						return false;
					}
				}
				 
				//If user level > app level, then authAccess
				//If user level = app level, compare
				$user_level = Accounts::getAuthLevel($_SESSION['account']['group']);
				$app_level = $list_app["permission"];
				if($user_level == $app_level){
					$this->forbidden = ($_SESSION['account']['group'] == $list_app["group"]?0:1);
					if($this->forbidden != 0){
						switch($_SESSION['account']['group']){
							case 0:
							case 1:
							case 2:
							case 3:
								$this->forbidden = 0;
								break;
							default:
						}
					}
				}else{
					$this->forbidden = (Accounts::authAccess($list_app["permission"]) ? 0 :1);
				}
			}else{
				$this->forbidden = (Accounts::authAccess($list_app["permission"]) ? 0 :1);
			}
		}else{
			//On CLI, user always authenticated as USER_AUTH_SU
			$this->forbidden = 0;
		}
		
		if($this->forbidden == 0){
			$this->title = $list_app["title"];
			$this->desc = $list_app["desc"];
			$this->appname = $name;
			$this->path = IO::physical_path("/applications/".$dir);
			$this->uri = __SITEURL . "/applications/" . $dir;
			$this->view_loaded = 0;
			$this->rootdir = "/applications/".$dir;
			$this->datadir = "/user_data/".$this->appname;
			$appProp = $this;
			if(!include_once($this->path . "/control.php")){
				return(false);
			}
			$this->apprunning = 1;
			return(true);
		}else{
			throw new AppStartError("Application `$name` forbidden","",403);
		}
		return(false);
	}

	/**
	 * Load the small view of an app like widget
	 * You can read $...param from viewSmall.php using $arguments
	 * @param mixed ...$param Put anything that the app requires
	 * @return object
	 */
	public function loadView(...$param){
		if($this->appfound){
			if($this->forbidden == 0){
				$appProp = $this;
				$arguments = func_get_args();
				if((!include($this->path."/viewSmall.php")) && ($this->view_loaded == 0)){
					throw new AppStartError("Cannot load view for app <strong>'".$this->title."'</strong><br>","",503);
				}else{
					$this->view_loaded = 1;
				}
			}
		}else{
			throw new AppStartError("Application `{$this->appname}` not found","",404);
		}
	}

	/*
	 * Load the main page of the app
	 */
	public function loadMainView(){
		if($this->appfound){
			if($this->forbidden == 0){
				$appProp = $this;
				if((!include_once($this->path."/viewPage.php")) && ($this->view_loaded == 0)){
					throw new PuzzleError("Cannot load view for app <strong>'".$this->title."'</strong><br>");
				}else{
					$this->view_loaded = 1;
				}
			}
		}else{
			throw new AppStartError("Application `{$this->appname}` not found","",404);
		}
	}
}
?>
