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

/* Add trigger for CronJob
 */
class CronTrigger {
    public $interval=0;
    public $exec=-1;
    /* Add an interval
	 * @param integer $seconds
	 */
    public function interval($seconds) {
        if ($this->exec>-1) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
        if ($seconds<15*T_MINUTE) throw new PuzzleError("Interval should be at least 15 minutes");
        $this->interval+=$seconds;
        return $this;
    }

    /* Add hour trigger
	 * @param integer $hour
	 */
     public function hour($hour) {
         if ($this->interval>0) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
         $time=time();
         if (date("G", $time)==$hour) $this->exec=1;
         else $this->exec=0;
         return $this;
     }

     /* Add day trigger
     * Day 1 (Mon) through 7 (Sun)
 	 * @param day number $day
 	 */
     public function day($day) {
         if ($this->interval>0) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
         $time=time();
         $today=date("N", $time);
         if ($day==$today) $this->exec=1;
         else $this->exec=0;
         return $this;
     }

     /* Add date trigger
 	 * @param integer $date
 	 */
     public function date($date) {
         if ($this->interval>0) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
         $time=time();
         $currentDate=date("j", $time);
         if ($date==$currentDate) $this->exec=1;
         else $this->exec=0;
         return $this;
     }

     /* Add month trigger
 	 * @param integer $month
 	 */
     public function month($month) {
         if ($this->interval>0) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
         $time=time();
         $currentMonth=date("n", $time);
         if ($month==$currentMonth) $this->exec=1;
         else $this->exec=0;
         return $this;
     }

     /* Add year trigger
 	 * @param integer $year
 	 */
     public function year($year) {
         if ($this->interval>0) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
         $time=time();
         $currentYear=date("Y", $time);
         if ($month==$currentYear) $this->exec=1;
         else $this->exec=0;
         return $this;
     }
}

class CronJob {
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
     * @param CronTrigger $trigger
     * @param function $F
	 */
    public static function register($key, $trigger, $F){
        if (strlen($key)>20) throw new PuzzleError("Key length must be less than 20 characters");
        if(!is_callable($F)) throw new PuzzleError("Incorrect parameter");
        $time=time();
        $appname=self::init();
        if($appname == "") throw new PuzzleError("An error occured");

        self::$list[] = ["{$key}_{$appname}",$trigger,&$F];
    }

    public static function run() {
        foreach(self::$list as $l){
            $lastExec = Database::read("cron","last_exec","key",$l[0]);
            if($lastExec == "") {
                if ($l[1]->interval > 0 || $l[1]->exec) {
                    $l[2]();
                    Database::newRow("cron", $l[0], time());
                }
            }else{
                if ((time()-$lastExec >= $l[1]->interval && $l[1]->interval > 0) || ($l[1]->exec && time()-mktime(0,0,0,date("n", $lastExec),date("j", $lastExec),date("Y", $lastExec)) > T_DAY)) {
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
