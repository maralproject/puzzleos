<?php

use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\WebProcessor;

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2019-2020 PT SIMUR INDONESIA
 */

/**
 * A helper class to quickly log your event
 * using Monolog class
 * 
 * @static @method void emergency($message, array [$array])
 * @static @method void alert($message, array [$array])
 * @static @method void critical($message, array [$array])
 * @static @method void error($message, array [$array])
 * @static @method void warning($message, array [$array])
 * @static @method void notice($message, array [$array])
 * @static @method void info($message, array [$array])
 * @static @method void debug($message, array [$array])
 */
class Log
{
    /**
     * Monolog singleton context
     * @var Logger 
     */
    private static $singleton;

    public static function __setup()
    {
        if (!is_callbyme()) throw new PuzzleError("Cannot call setup from outside class");

        $stream_handler = new StreamHandler(__LOGDIR . "/logging.log");
        $log = new Logger("PuzzleLog");
        $log->pushHandler($stream_handler);
        if (!is_cli()) {
            $log->pushProcessor(new WebProcessor());
            $log->pushProcessor(function ($record) {
                $record['extra']['cli'] = is_cli();
                $record['extra']['session'] = session_id();
                return $record;
            });
        }
        self::$singleton = $log;
    }

    /**
     * Get monolog instance
     * @return Logger
     */
    public static function getMonolog()
    {
        return self::$singleton;
    }

    /**
     * Push another handler to monolog
     */
    public static function pushHandler(HandlerInterface $handler)
    {
        return self::$singleton->pushHandler($handler);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func([self::$singleton, $name], ...$arguments);
    }
}

Log::__setup();
