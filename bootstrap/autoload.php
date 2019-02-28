<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

spl_autoload_register(function ($c) {
    $r = __ROOTDIR . "/bootstrap";
    switch ($c) {
        case "Automattic\\Phone\\Iso3166":
        case "Automattic\\Phone\\Mobile_Validator":
            require("$r/vendor/automattic/phone/Mobile_Validator.php");
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
        default:
            if (($tok = strtok($c, "\\")) == "MatthiasMullie") {
                if (($tok2 = strtok("\\")) == "PathConverter") {
                    $path = "$r/vendor/minifier/matthiasmullie/path-converter/src/" . btfslash(strtok('')) . ".php";
                    if (file_exists($path)) require $path;
                } elseif ($tok2 == "Minify") {
                    $path = "$r/vendor/minifier/matthiasmullie/minify/src/" . btfslash(strtok('')) . ".php";
                    if (file_exists($path)) require $path;
                }
            } elseif ($tok == "Symfony") {
                if (($tok2 = strtok("\\")) == "Polyfill") {
                    if (($tok3 = strtok("\\")) == "Util") {
                        $path = "$r/vendor/superclosure/symfony/polyfill-util/" . btfslash(strtok('')) . ".php";
                        if (file_exists($path)) require $path;
                    } elseif ($tok3 == "Php56") {
                        $path = "$r/vendor/superclosure/symfony/polyfill-php56/" . btfslash(strtok('')) . ".php";
                        if (file_exists($path)) require $path;
                    }
                }
            } elseif ($tok == "SuperClosure") {
                $path = "$r/vendor/superclosure/jeremeamia/SuperClosure/src/" . btfslash(strtok('')) . ".php";
                if (file_exists($path)) require $path;
            } elseif ($tok == "PhpParser") {
                $path = "$r/vendor/superclosure/nikic/php-parser/lib/PhpParser/" . btfslash(strtok('')) . ".php";
                if (file_exists($path)) require $path;
            }
    }
});