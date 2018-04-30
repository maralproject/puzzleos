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
define("CRON_TIME", time());

/**
 * Add a trigger for CronJob event
 * If a CronJob event is already executed at one of the specified CronTrigger,
 * the job will NOT be executed
 */
class CronTrigger{
    private $exec=1;
    private $hour=-1;
    private $day=-1;
    private $date=-1;
    private $month=-1;
    private $year=-1;
    private $interval=0;

	public function __construct(){
		return $this;
	}

    public function getExec(){
        return $this->exec;
    }

    public function isExecutable($lastExec) {
        if ($lastExec=="") return $this->exec;
		if (CRON_TIME-(int)$lastExec-$this->interval>=0) {
            $this->exec*=1;
        }
        else $this->exec*=0;
        $hour_executable=($this->hour>-1) ? (floor(CRON_TIME/3600) > floor($lastExec/3600)) : 1;
        $day_executable=($this->day>-1) ? (floor(CRON_TIME/86400) > floor($lastExec/86400)) : 1;
        $date_executable=($this->date>-1) ? (floor(CRON_TIME/86400) > floor($lastExec/86400)) : 1;
        $month_executable=($this->month>-1) ? (idate("Y", CRON_TIME)*100+idate("m", CRON_TIME)) > (idate("Y",$lastExec)*100+idate("m", $lastExec)) : 1;
        $year_executable=($this->year>-1) ? (idate("Y", CRON_TIME)) > (idate("Y",$lastExec)) : 1;
        $trigger_executable=($hour_executable && $day_executable && $date_executable && $month_executable && $year_executable) && $this->exec==1;
        return ($trigger_executable);
    }

    /**
     * Add an interval trigger
     * You can use T_MINUTE, T_HOUR, T_DAY definition: see /cron.php
     * @param integer $seconds
     */
    public function interval($seconds) {
        if ($this->hour!=-1 || $this->day!=-1 || $this->date!=-1 || $this->month!=-1 || $this->year!=-1) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
        if ($seconds<15*T_MINUTE) throw new PuzzleError("Interval should be at least 15 minutes");
        $this->interval=$seconds;
        return $this;
    }

    /**
     * Add an hour trigger (24-hour format)
     * @param integer $hour
     */
    public function hour($hour) {
        if (idate("H", CRON_TIME)==$hour) {
        $this->exec*=1;
        $this->hour=$hour;
        }
        else $this->exec*=0;
        return $this;
    }

    /**
     * Add day trigger
     * Day 0 (Sun) through 6 (Sat)
     * @param day number $day
     */
    public function day($day) {
        $today=idate("w", CRON_TIME);
        if ($day==$today) {
            $this->exec*=1;
            $this->day=$day;
        }
        else $this->exec*=0;
        return $this;
    }

    /**
     * Add date trigger
     * @param integer $date
     */
    public function date($date) {
        $currentDate=idate("d", CRON_TIME);
        if ($date==$currentDate) {
            $this->exec*=1;
            $this->date=$date;
        }
        else $this->exec*=0;
        return $this;
    }

    /**
     * Add month trigger
     * @param integer $month
     */
    public function month($month) {
        $currentMonth=idate("m", CRON_TIME);
        if ($month==$currentMonth) {
            $this->exec*=1;
            $this->month=$month;
        }
        else $this->exec*=0;
        return $this;
    }

    /**
     * Add year trigger
     * @param integer $year
     */
    public function year($year) {
        $currentYear=idate("Y", CRON_TIME);
        if ($year==$currentYear) {
            $this->exec*=1;
            $this->year=$year;
        }
        else $this->exec*=0;
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
     * @param CronTrigger ...$trigger
     * @param function $F
	 */
    public static function register($key, $F, ...$trigger){
        if (strlen($key)>20) throw new PuzzleError("Key length must be less than 20 characters");
        if(!is_callable($F)) throw new PuzzleError("Incorrect parameter");
        $appname=self::init();
        if($appname == "") throw new PuzzleError("An error occured");

        foreach($trigger as $ct) {
            if(!is_a($ct,"CronTrigger")) throw new PuzzleError("Trigger should be generated from CronTrigger");
            self::$list[] = ["{$key}_{$appname}",$ct,&$F];
        }
    }

    public static function run(){
		if(file_exists(__ROOTDIR . "/cron.lock")) throw new PuzzleError("Cannot run 2 cron instances simultaneusly");

		//Prevent running cron simultaneusly
		error_reporting(E_ERROR | E_WARNING);
		file_put_contents(__ROOTDIR . "/cron.lock",1);
		ini_set('max_execution_time',0); //Disable PHP timeout

		register_shutdown_function(function(){
			@unlink(__ROOTDIR . "/cron.lock");
		});

        foreach(self::$list as $l){
            $lastExec = Database::read("cron","last_exec","key",$l[0]);
            if($lastExec == "") {
                if($l[1]->isExecutable($lastExec)){
					//We'll use try/catch to prevent cron from stuck by one error
					try{
						$f = $l[2];
						$f(); //Preventing error on PHP 5.6
						Database::newRow("cron", $l[0], CRON_TIME);
					}catch(Exception $e){
						echo("ERROR: " . $e->getMessage() . "\n");
					}
                }
            }else{
                if($l[1]->isExecutable($lastExec)){
					//We'll use try/catch to prevent cron from stuck by one error
					try{
						$f = $l[2];
						$f(); //Preventing error on PHP 5.6
						$update=new DatabaseRowInput;
						$update->setField("last_exec", CRON_TIME);
						Database::updateRowAdvanced("cron", $update, "key", $l[0]);
					}catch(Exception $e){
						echo("ERROR: " . $e->getMessage() . "\n");
					}
                }
            }
        }
		unlink(__ROOTDIR . "/cron.lock");
    }
}

/**
 * Get a new CronTrigger instances
 * @return CronTrigger
 */
function _CT(){
	return new CronTrigger();
}
?>
