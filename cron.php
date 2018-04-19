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

define("T_DAY", 86400);
define("T_HOUR", 3600);
define("T_MINUTE", 60);
class CronJob{
    private static $list=[];
    private static function init(){
		$caller = debug_backtrace()[1]["file"];
		$filenameStr = str_replace(__ROOTDIR,"",str_replace("\\","/",$caller));
		$filename = explode("/",$filenameStr);
        if($filename[1] != "applications") throw new PuzzleError("CronJob must be called from applications");
		$appDir = $filename[2];
		$appname = AppManager::getNameFromDirectory($appDir);
		return($appname);
	}

    /* Register cron job
	 * @param string $key
     * @param integer $interval
     * @param function $F
	 */
    public static function register($key, $interval, $F){
        if (strlen($key)>20) throw new PuzzleError("Key length must be less than 20 characters");
        if ($interval<15*T_MINUTE) throw new PuzzleError("Interval should be at least 15 minutes");
        if(!is_callable($F)) throw new PuzzleError("Incorrect parameter");

        $appname=self::init();
        if($appname == "") throw new PuzzleError("An error occured");
        self::$list[] = ["{$key}_{$appname}",$interval,&$F];
    }

    public static function run() {
        foreach(self::$list as $l){
            $lastExec = Database::read("cron","last_exec","key",$l[0]);

            if($lastExec == "") {
                $l[2]();
                Database::newRow("cron", $l[0], time());
            }else{
                if (time()-$lastExec >= $l[1]) {
                    $l[2]();
                    $update=new DatabaseRowInput;
                    $update->setField("last_exec", time());
                    Database::updateRowAdvanced("cron", $update, "key", $l[0]);
                }
            }
        }
    }
}

?>
