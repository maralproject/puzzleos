<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

#PuzzleOS Core library
spl_autoload_register(function ($c) {
    $r = __ROOTDIR . "/bootstrap";
    switch ($c) {
        case "Accounts":
        case "PuzzleUser":
        case "PuzzleUserGroup":
        case "PuzzleUserConfig":
        case "PuzzleUserOTP":
        case "PuzzleUserRecaptcha":
        case "PuzzleUserGA":
            require(__ROOTDIR . "/applications/accounts/class/$c.php");
            break;
        case "PuzzleUserException\\MissingField":
        case "PuzzleUserException\\InvalidField":
        case "PuzzleUserException\\UserNotFound":
        case "PuzzleUserException\\GroupNotFound":
        case "PuzzleUserException\\FailedToSendOTP":
            require(__ROOTDIR . "/applications/accounts/exception/" . end(explode("\\", $c)) . ".php");
            break;
        case "Automattic\\Phone\\Iso3166":
        case "Automattic\\Phone\\Mobile_Validator":
            require(__ROOTDIR . "/includes/automattic/phone/Mobile_Validator.php");
            break;
        case "FileStream":
        case "IO":
            require("$r/iosystem.php");
            break;
        case "Minifier":
            require("$r/minifier.php");
            break;
        case "Prompt":
            require("$r/message.php");
            break;
        case "Log":
            require("$r/log.php");
            break;
        case "UserData":
            require("$r/userdata.php");
            break;
        case "LangManager":
        case "Language":
            require("$r/language.php");
            break;
        case "Template":
            require("$r/templates.php");
            break;
        case "Worker":
            require("$r/worker.php");
            break;
        case "PuzzleCLI":
            require("$r/cli.php");
            break;
        case "Cache":
            require("$r/cache.php");
            break;
        case "CronJob":
        case "CronTrigger":
            require("$r/cron.php");
            break;
    }
});

#External Library from Composer
require(__ROOTDIR . "/includes/vendor/autoload.php");
