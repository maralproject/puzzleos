<?php
use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2019 PT SIMUR INDONESIA
 */

/**
 * A helper class to quickly log your event
 * using Monolog class
 */
class Log
{
    /** @var Logger */
    private static $singleton;
    public static function __setup()
    {
        if (!is_callbyme()) throw new PuzzleError("Cannot call setup from outside class");
        self::$singleton = new Logger("PuzzleLog");
        self::$singleton->pushHandler(new StreamHandler(__LOGDIR . "/logging.log"));
    }
    public static function getMonolog()
    {
        return self::$singleton;
    }
    public static function pushHandler(HandlerInterface $handler)
    {
        return self::$singleton->pushHandler($handler);
    }
    public static function emergency($message, $array = [])
    {
        return self::$singleton->emergency($message, $array);
    }
    public static function alert($message, $array = [])
    {
        return self::$singleton->alert($message, $array);
    }
    public static function critical($message, $array = [])
    {
        return self::$singleton->critical($message, $array);
    }
    public static function error($message, $array = [])
    {
        return self::$singleton->error($message, $array);
    }
    public static function warning($message, $array = [])
    {
        return self::$singleton->warning($message, $array);
    }
    public static function notice($message, $array = [])
    {
        return self::$singleton->notice($message, $array);
    }
    public static function info($message, $array = [])
    {
        return self::$singleton->info($message, $array);
    }
    public static function debug($message, $array = [])
    {
        return self::$singleton->debug($message, $array);
    }
}

Log::__setup();
