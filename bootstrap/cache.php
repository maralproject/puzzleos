<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * PuzzleOS internal cache provider.
 * Useful to avoid database query everytime.
 */
class Cache{
    private static function init($key){
        if(str_contains($key,".")) throw new PuzzleError("Key cannot contains '.'");
        $path = explode("/",ltrim(str_replace(__ROOTDIR,"",btfslash(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,2)[1]["file"])),"/"));
        switch($path[0]){
        case 'applications':
            $appname = AppManager::getNameFromDirectory($path[1]);
            preparedir(__ROOTDIR . "/storage/cache/applications/$appname");
            return __ROOTDIR . "/storage/cache/applications/$appname";
        case 'bootstrap':
            $fname = basename($path[2],".php");
            preparedir(__ROOTDIR . "/storage/cache/system/$fname");
            return __ROOTDIR . "/storage/cache/system/$fname";
            break;
        default:
            throw new PuzzleError("Cache can be only used for Applications only!");
        }
    }

    public static function store($key,$object){
        $p = self::init($key);
        return file_put_contents("$p/$key",serialize($object)) !== false ? true : false;
    }

    public static function get($key,$default=false) {
        $p = self::init($key);
        if(file_exists("$p/$key")){
            return unserialize(file_get_contents("$p/$key"));
        }else{
            if(is_callable($default)){
                return $default($key);
            }else{
                return $default;
            }
        }
    }

    public static function exists($key){
        $p = self::init();
        return (file_exists("$p/$key"));
    }

    public static function pull($key){
        $p = self::init($key);
        if(file_exists("$p/$key")){
            $r = unserialize(file_get_contents("$p/$key"));
            unlink("$p/$key");
            return $r;
        }
    }
}

?>