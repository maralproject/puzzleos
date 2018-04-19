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


// INTERVAL VALUES
define("T_DAY", 86400);
define("T_HOUR", 3600);
define("T_MINUTE", 60);


class CronJob   {
    private static $list=[[]];
    private static function getTime() {
        return time();
    }
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
    /* Register cron job
	 * @param string $key
     * @param integer $interval
     * @param function $F
	 */
    public static function register($key, $interval, $F){
        $appname=self::init();
        $isEnabled=Database::read("cron", "enabled", "key", $key."_".$appname);
        if ($isEnabled=="") {
            Database::newRow("cron", $key."_".$appname, 1, date("Y-m-d_H:i:s", self::getTime()), date("Y-m-d_H:i:s", self::getTime()), $interval);
            //self::$list[$key]=&$F;
            $newJob=array(
                "key" => $key."_".$appname,
                "func" => &$F
            );
            array_push(self::$list, $newJob);
        }
        if ($isEnabled=="1") {
            $update=new DatabaseRowInput;
            $update->setField("interval", $interval);
            Database::updateRowAdvanced("cron", $update, "key", $key."_".$appname);
            $newJob=array(
                "key" => $key."_".$appname,
                "func" => &$F
            );
            array_push(self::$list, $newJob);
        }
    }

    public static function run() {
        foreach(self::$list as $l) {
            $isEnabled=Database::read("cron", "enabled", "key", $l["key"]);
            $nextExec=Database::read("cron", "next_exec", "key", $l["key"]);
            $interval=Database::read("cron", "interval", "key", $l["key"]);
            if ($isEnabled=="1" && strcmp(date("Y-m-d_H:i:s", self::getTime()), $nextExec)>0) {
                $l["func"]();
                $update=new DatabaseRowInput;
                $update->setField("last_exec", date("Y-m-d_H:i:s", self::getTime()));
                $update->setField("next_exec", date("Y-m-d_H:i:s", self::getTime()+$interval));
                Database::updateRowAdvanced("cron", $update, "key", $l["key"]);
            }
        }
    }
}

?>
