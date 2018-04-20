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

/* Add trigger for CronJob
 */
class CronTrigger {
    private $interval=0;
    private $exec=-1;
    private $hour=-1;
    private $day=-1;
    private $date=-1;
    private $month=-1;
    private $year=-1;
    /* Add an interval
	 * @param integer $seconds
	 */

     public function getInterval() {
         return $this->interval;
     }
     public function getExec() {
         return $this->exec;
     }

    public function isExecutable($lastExec) {
        if ($lastExec=="") return ($this->interval > 0 || $this->exec);
        $interval_executable=(time()-$lastExec >= $this->interval && $this->interval > 0);
        $hour_executable=($this->hour>-1)? (floor(CRON_TIME/3600) > floor($lastExec/3600)) : 1;
        $day_executable=($this->day>-1)? (floor(CRON_TIME/86400) > floor($lastExec/86400)) : 1;
        $date_executable=($this->date>-1)? (floor(CRON_TIME/86400) > floor($lastExec/86400)) : 1;
        $month_executable=($this->month>-1)? strcmp(date("n-Y", CRON_TIME), date("n-Y", $lastExec)) : 1;
        $year_executable=($this->year>-1)? strcmp(date("Y", CRON_TIME), date("Y", $lastExec)) : 1;
        $trigger_executable=($hour_executable && $day_executable && $date_executable && $month_executable && $year_executable) && $this->exec==1;
        return ($interval_executable || $trigger_executable);
    }

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
         if (date("G", CRON_TIME)==$hour) {
            $this->exec=1;
            $this->hour=$hour;
         }
         else $this->exec=0;
         return $this;
     }

     /* Add day trigger
     * Day 1 (Mon) through 7 (Sun)
 	 * @param day number $day
 	 */
     public function day($day) {
         if ($this->interval>0) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
         $today=date("N", CRON_TIME);
         if ($day==$today) {
             $this->exec=1;
             $this->day=$day;
         }
         else $this->exec=0;
         return $this;
     }

     /* Add date trigger
 	 * @param integer $date
 	 */
     public function date($date) {
         if ($this->interval>0) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
         $currentDate=date("j", CRON_TIME);
         if ($date==$currentDate) {
             $this->exec=1;
             $this->date=$date;
         }
         else $this->exec=0;
         return $this;
     }

     /* Add month trigger
 	 * @param integer $month
 	 */
     public function month($month) {
         if ($this->interval>0) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
         $currentMonth=date("n", CRON_TIME);
         if ($month==$currentMonth) {
             $this->exec=1;
             $this->month=$month;
         }
         else $this->exec=0;
         return $this;
     }

     /* Add year trigger
 	 * @param integer $year
 	 */
     public function year($year) {
         if ($this->interval>0) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
         ;
         $currentYear=date("Y", CRON_TIME);
         if ($month==$currentYear) {
             $this->exec=1;
             $this->year=$year;
         }
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
        $appname=self::init();
        if($appname == "") throw new PuzzleError("An error occured");

        self::$list[] = ["{$key}_{$appname}",$trigger,&$F];
    }

    public static function run() {
        foreach(self::$list as $l){
            $lastExec = Database::read("cron","last_exec","key",$l[0]);
            if($lastExec == "") {
                if ($l[1]->isExecutable($lastExec)) {
                    $l[2]();
                    Database::newRow("cron", $l[0], time());
                }
            }else{
                if ($l[1]->isExecutable($lastExec)) {
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
