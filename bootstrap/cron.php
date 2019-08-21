<?php

/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

/**
 * Add a trigger for CronJob event
 * If a CronJob event is already executed at one of the specified CronTrigger,
 * the job will NOT be executed
 */
class CronTrigger
{
    private $exec = 1;
    private $hour = -1;
    private $day = -1;
    private $date = -1;
    private $month = -1;
    private $year = -1;
    private $interval = 0;

    public function __construct()
    {
        return $this;
    }

    public function getExec()
    {
        return $this->exec;
    }

    public function isExecutable($lastExec)
    {
        if ($lastExec == "") return $this->exec;
        if (START_TIME - (int) $lastExec - $this->interval >= 0) {
            $this->exec *= 1;
        } else $this->exec *= 0;
        $hour_executable = ($this->hour > -1) ? (floor(START_TIME / 3600) > floor($lastExec / 3600)) : 1;
        $day_executable = ($this->day > -1) ? (floor(START_TIME / 86400) > floor($lastExec / 86400)) : 1;
        $date_executable = ($this->date > -1) ? (floor(START_TIME / 86400) > floor($lastExec / 86400)) : 1;
        $month_executable = ($this->month > -1) ? (idate("Y", START_TIME) * 100 + idate("m", START_TIME)) > (idate("Y", $lastExec) * 100 + idate("m", $lastExec)) : 1;
        $year_executable = ($this->year > -1) ? (idate("Y", START_TIME)) > (idate("Y", $lastExec)) : 1;
        $trigger_executable = ($hour_executable && $day_executable && $date_executable && $month_executable && $year_executable) && $this->exec == 1;
        return ($trigger_executable);
    }

    /**
     * Add an interval trigger
     * You can use T_MINUTE, T_HOUR, T_DAY definition: see /cron.php
     * @param integer $seconds
     * @return CronTrigger
     */
    public function interval($seconds)
    {
        if ($this->hour != -1 || $this->day != -1 || $this->date != -1 || $this->month != -1 || $this->year != -1) throw new PuzzleError("Can't add interval <b>and</b> specified time at once");
        if ($seconds < T_MINUTE) throw new PuzzleError("Interval should be at least one minutes");
        $this->interval = $seconds;
        return $this;
    }

    /**
     * Add an hour trigger (24-hour format)
     * @param integer $hour
     * @return CronTrigger
     */
    public function hour($hour)
    {
        if (idate("H", START_TIME) == $hour) {
            $this->exec *= 1;
            $this->hour = $hour;
        } else $this->exec *= 0;
        return $this;
    }

    /**
     * Add day trigger
     * Day 0 (Sun) through 6 (Sat)
     * @param day number $day
     * @return CronTrigger
     */
    public function day($day)
    {
        $today = idate("w", START_TIME);
        if ($day == $today) {
            $this->exec *= 1;
            $this->day = $day;
        } else $this->exec *= 0;
        return $this;
    }

    /**
     * Add date trigger
     * @param integer $date
     * @return CronTrigger
     */
    public function date($date)
    {
        $currentDate = idate("d", START_TIME);
        if ($date == $currentDate) {
            $this->exec *= 1;
            $this->date = $date;
        } else $this->exec *= 0;
        return $this;
    }

    /**
     * Add month trigger
     * @param integer $month
     * @return CronTrigger
     */
    public function month($month)
    {
        $currentMonth = idate("m", START_TIME);
        if ($month == $currentMonth) {
            $this->exec *= 1;
            $this->month = $month;
        } else $this->exec *= 0;
        return $this;
    }

    /**
     * Add year trigger
     * @param integer $year
     * @return CronTrigger
     */
    public function year($year)
    {
        $currentYear = idate("Y", START_TIME);
        if ($year == $currentYear) {
            $this->exec *= 1;
            $this->year = $year;
        } else $this->exec *= 0;
        return $this;
    }
}

class CronJob
{
    private static $list = [];

    private static function init()
    {
        $caller = debug_backtrace()[1]["file"];
        $filenameStr = str_replace(__ROOTDIR, "", btfslash($caller));
        $filename = explode("/", $filenameStr);
        if ($filename[1] != "applications") throw new PuzzleError("CronJob must be called from applications");
        $appDir = $filename[2];
        $appname = AppManager::getNameFromDirectory($appDir);
        return ($appname);
    }

    /* Register cron job
     * @param string $key
     * @param CronTrigger ...$trigger
     * @param function $F
     */
    public static function register($key, $F, ...$trigger)
    {
        // Can only be called on CLI
        if (!is_cli()) return; 
        if (strlen($key) > 20) throw new PuzzleError("Key length must be less than 20 characters");
        if (!is_callable($F)) throw new PuzzleError("Incorrect parameter");
        $appname = self::init();
        if ($appname == "") throw new PuzzleError("An error occured");

        foreach ($trigger as $ct) {
            if (!$ct instanceof CronTrigger) throw new PuzzleError("Trigger should be generated from CronTrigger");
            self::$list[] = ["{$key}_{$appname}", $ct, &$F];
        }
    }

    /**
     * Run the cron
     * @param bool $force Use this to force all cron run even not in their time
     */
    public static function run(bool $forced = false)
    {
        $fp = fopen(__ROOTDIR . "/cron.lock", "w");
        //Prevent running cron simultaneusly
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            POSConfigGlobal::$error_code |= E_ERROR;
            ini_set('max_execution_time', 0); //Disable PHP timeout

            foreach (self::$list as $l) {
                $lastExec = Database::read("cron", "last_exec", "key", $l[0]);
                try {
                    if ($lastExec == "") {
                        if ($l[1]->isExecutable($lastExec) || $forced) {
                            $f = $l[2];
                            $f(); //Preventing error on PHP 5.6
                            Database::insert("cron", [
                                (new DatabaseRowInput)
                                    ->setField("key", $l[0])
                                    ->setField("last_exec", START_TIME)
                            ]);
                        }
                    } else {
                        if ($l[1]->isExecutable($lastExec) || $forced) {
                            $f = $l[2];
                            $f(); //Preventing error on PHP 5.6
                            Database::update(
                                "cron",
                                (new DatabaseRowInput)->setField("last_exec", START_TIME),
                                "key",
                                $l[0]
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    PuzzleError::handleErrorControl($e, false);
                }
            }

            // Release lock
            flock($fp, LOCK_UN);
        } else {
            throw new PuzzleError("Cannot run 2 cron instances simultaneusly");
        }
    }
}
