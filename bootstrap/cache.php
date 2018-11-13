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
class Cache
{
    private static function init($key)
    {
        if (str_haschar($key, '/', "\\", '..', '*')) throw new PuzzleError("Key invalid!");
        $stack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
        $path = explode("/", str_replace(__ROOTDIR, "", btfslash($stack[str_contains($stack[2]["function"],"call_user_func") ? 2 : 1]["file"])));
        switch ($path[1]) {
            case 'applications':
                $appname = AppManager::getNameFromDirectory($path[2]);
                preparedir(__ROOTDIR . "/storage/cache/applications/$appname");
                return __ROOTDIR . "/storage/cache/applications/$appname";
            case 'bootstrap':
                $fname = basename($path[3], ".php");
                preparedir(__ROOTDIR . "/storage/cache/system/$fname");
                return __ROOTDIR . "/storage/cache/system/$fname";
                break;
            default:
                throw new PuzzleError("Cache can be only used for Applications only!");
        }
    }

    /**
     * Store object in cache
     * @param string $key
     * @param mixed $object
     * @return bool
     */
    public static function store($key, $object)
    {
        $p = self::init($key);
        return file_put_contents("$p/$key", serialize($object)) !== false ? true : false;
    }

    /**
     * Get object in cache
     * @param string $key
     * @param callable $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $p = self::init($key);
        if (file_exists("$p/$key")) {
            return unserialize(file_get_contents("$p/$key"));
        } else {
            if (is_callable($default)) {
                return $default($key, function ($result) use ($p, $key) {
                    return file_put_contents("$p/$key", serialize($result)) !== false ? $result : false;
                });
            } else {
                return $default;
            }
        }
    }

    /**
     * Check if cache was present
     * @param string $key
     * @return bool
     */
    public static function exists($key)
    {
        $p = self::init($key);
        return (file_exists("$p/$key"));
    }

    /**
     * Read and remove cache
     * @param string $key
     * @return mixed
     */
    public static function pull($key)
    {
        $p = self::init($key);
        if (file_exists("$p/$key")) {
            $r = unserialize(file_get_contents("$p/$key"));
            unlink("$p/$key");
            return $r;
        }
    }
}
