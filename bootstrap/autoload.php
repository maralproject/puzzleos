<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

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
            #Loader Logic, extracted from Composer by hand.
            if (($tok = strtok($c, "\\")) == "MatthiasMullie") {
                if (($tok2 = strtok("\\")) == "PathConverter") {
                    $path = "$r/vendor/minifier/matthiasmullie/path-converter/src/" . btfslash(strtok('')) . ".php";
                } elseif ($tok2 == "Minify") {
                    $path = "$r/vendor/minifier/matthiasmullie/minify/src/" . btfslash(strtok('')) . ".php";
                }
            } elseif ($tok == "Symfony") {
                if (($tok2 = strtok("\\")) == "Polyfill") {
                    if (($tok3 = strtok("\\")) == "Util") {
                        $path = "$r/vendor/superclosure/symfony/polyfill-util/" . btfslash(strtok('')) . ".php";
                    } elseif ($tok3 == "Php56") {
                        $path = "$r/vendor/superclosure/symfony/polyfill-php56/" . btfslash(strtok('')) . ".php";
                    } elseif ($tok3 == "Php72") {
                        $path = "$r/vendor/qr/symfony/polyfill-php72/" . btfslash(strtok('')) . ".php";
                    } elseif ($tok3 == "Mbstring") {
                        $path = "$r/vendor/qr/symfony/polyfill-mbstring/" . btfslash(strtok('')) . ".php";
                    } elseif ($tok3 == "Ctype") {
                        $path = "$r/vendor/qr/symfony/polyfill-ctype/" . btfslash(strtok('')) . ".php";
                    } elseif ($tok3 == "Intl") {
                        if (($tok4 = strtok("\\")) == "Idn") {
                            $path = "$r/vendor/qr/symfony/polyfill-intl-idn/" . btfslash(strtok('')) . ".php";
                        }
                    }
                } else if ($tok2 == "Component") {
                    if (($tok3 = strtok("\\")) == "PropertyAccess") {
                        $path = "$r/vendor/qr/symfony/property-access/" . btfslash(strtok('')) . ".php";
                    } else if ($tok3 == "OptionsResolver") {
                        $path = "$r/vendor/qr/symfony/options-resolve/" . btfslash(strtok('')) . ".php";
                    } else if ($tok3 == "Mime") {
                        $path = "$r/vendor/qr/symfony/mime/" . btfslash(strtok('')) . ".php";
                    } else if ($tok3 == "Inflector") {
                        $path = "$r/vendor/qr/symfony/inflector/" . btfslash(strtok('')) . ".php";
                    } else if ($tok3 == "HttpFoundation") {
                        $path = "$r/vendor/qr/symfony/http-foundation/" . btfslash(strtok('')) . ".php";
                    }
                }
            } elseif ($tok == "SuperClosure") {
                $path = "$r/vendor/superclosure/jeremeamia/SuperClosure/src/" . btfslash(strtok('')) . ".php";
            } elseif ($tok == "PhpParser") {
                $path = "$r/vendor/superclosure/nikic/php-parser/lib/PhpParser/" . btfslash(strtok('')) . ".php";
            } else if ($tok == "Zxing") {
                $path = "$r/vendor/qr/khanamiryan/qrcode-detector-decoder/lib/" . btfslash(strtok('')) . ".php";
            } else if ($tok == "BaconQrCode") {
                $path = "$r/vendor/qr/bacon/bacon-qr-code/src/" . btfslash(strtok('')) . ".php";
            } else if ($tok == "MyCLabs") {
                if (($tok2 = strtok("\\")) == "Enum") {
                    $path = "$r/vendor/qr/myclabs/php-enum/src/" . btfslash(strtok('')) . ".php";
                }
            } else if ($tok == "Endroid") {
                if (($tok2 = strtok("\\")) == "QrCode") {
                    $path = "$r/vendor/qr/endroid/qr-code/src/" . btfslash(strtok('')) . ".php";
                } else if ($tok2 == "Installer") {
                    $path = "$r/vendor/qr/endroid/installer/src/" . btfslash(strtok('')) . ".php";
                }
            } else if ($tok == "DASPRiD") {
                if (($tok2 = strtok("\\")) == "Enum") {
                    $path = "$r/vendor/qr/dasprid/enum/src/" . btfslash(strtok('')) . ".php";
                }
            }

            #Loading file
            if ($path && is_file($path)) require $path;
    }
});
