<?php

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

/**
 * Store and manage user data
 * Only allowed to be called from applications
 */
class UserData
{
	private static $cache;

	private static function init($key)
	{
		if (str_haschar($key, '/', "\\", '..', '*')) {
			throw new \InvalidArgumentException("Key invalid!");
		}

		$stack = debug_backtrace(1, 3);
		$caller = $stack[str_contains($stack[2]["function"], "call_user_func") ? 2 : 1]["file"];
		$filename = explode("/", str_replace(__ROOTDIR, "", btfslash($caller)));

		switch ($filename[1]) {
			case "applications":
				break;
			default:
				throw new PuzzleError("UserData can only be called from application");
		}

		$appDir = $filename[2];
		$appname = AppManager::getNameFromDirectory($appDir);
		preparedir(__ROOTDIR . "/storage/data/$appname");
		preparedir(__ROOTDIR . "/" . __PUBLICDIR . "/assets/$appname");
		return $appname;
	}

	/**
	 * Move uploaded file through HTTP POST to user directory
	 * Just like move_uploaded_file(), but moves it directly to user data directory
	 * for easy access.
	 * @param string $key
	 * @param string $inputname
	 * @param bool $secure
	 * @return bool
	 */
	public static function move_uploaded(string $key, string $inputname, bool $secure = false)
	{
		if ('' != ($appname = self::init($key))) {
			$fileext = pathinfo($_FILES[$inputname]['name'])['extension'] ?? '';
			if ($secure) {
				$filename = "/storage/data/$appname/$key.$fileext";
			} else {
				$filename = "/" . __PUBLICDIR . "/assets/$appname/$key.$fileext";
			}

			unset(self::$cache[$appname . $key]);
			return
				move_uploaded_file($_FILES[$inputname]['tmp_name'], IO::physical_path($filename)) &&
				Database::insertOnDuplicateUpdate("userdata", [
					"app" => $appname,
					"identifier" => $key,
					"physical_path" => $filename,
					"mime_type" => IO::get_mime($filename),
					"ver" => time(),
					"secure" => $secure ? 1 : 0,
				]);
		} else {
			return false;
		}
	}

	/**
	 * Move file from somewhere to user directory
	 * @param string $key
	 * @param string $path_to_file
	 * @param bool $secure
	 * @return bool
	 */
	public static function move(string $key, string $path_to_file, bool $secure = false)
	{
		if ('' != ($appname = self::init($key))) {
			$physical_path = IO::physical_path($path_to_file);
			if (!IO::exists($path_to_file) || is_dir($physical_path)) return false;

			$fileext = pathinfo($physical_path)['extension'] ?? '';
			if ($secure) {
				$filename = "/storage/data/$appname/$key.$fileext";
			} else {
				$filename = "/" . __PUBLICDIR . "/assets/$appname/$key.$fileext";
			}

			unset(self::$cache[$appname . $key]);
			return
				rename($physical_path, IO::physical_path($filename)) &&
				Database::insertOnDuplicateUpdate("userdata", [
					"app" => $appname,
					"identifier" => $key,
					"physical_path" => $filename,
					"mime_type" => IO::get_mime($filename),
					"ver" => time(),
					"secure" => $secure ? 1 : 0,
				]);
		} else {
			return false;
		}
	}

	/**
	 * Copy file somewhere to user directory
	 * @param string $key
	 * @param string $path_to_file
	 * @param bool $secure
	 * @return bool
	 */
	public static function copy(string $key, string $path_to_file, bool $secure = false)
	{
		if ('' != ($appname = self::init($key))) {
			$physical_path = IO::physical_path($path_to_file);
			if (!IO::exists($path_to_file) || is_dir($physical_path)) return false;

			$fileext = pathinfo($physical_path)['extension'] ?? '';
			if ($secure) {
				$filename = "/storage/data/$appname/$key.$fileext";
			} else {
				$filename = "/" . __PUBLICDIR . "/assets/$appname/$key.$fileext";
			}

			unset(self::$cache[$appname . $key]);
			return
				copy($physical_path, IO::physical_path($filename)) &&
				Database::insertOnDuplicateUpdate("userdata", [
					"app" => $appname,
					"identifier" => $key,
					"physical_path" => $filename,
					"mime_type" => IO::get_mime($filename),
					"ver" => time(),
					"secure" => $secure ? 1 : 0,
				]);
		} else {
			return false;
		}
	}

	/**
	 * Write a file into user data directory
	 * @param string $key
	 * @param mixed $content
	 * @param string $file_ext
	 * @param bool $secure
	 * @return bool
	 */
	public static function store(string $key, $content, string $file_ext, bool $secure = false)
	{
		if ('' != ($appname = self::init($key))) {
			if ($secure) {
				$filename = "/storage/data/$appname/$key.$file_ext";
			} else {
				$filename = "/" . __PUBLICDIR . "/assets/$appname/$key.$file_ext";
			}

			unset(self::$cache[$appname . $key]);
			return
				IO::write($filename, $content) &&
				Database::insertOnDuplicateUpdate("userdata", [
					"app" => $appname,
					"identifier" => $key,
					"physical_path" => $filename,
					"mime_type" => IO::get_mime($filename),
					"ver" => time(),
					"secure" => $secure ? 1 : 0,
				]);
		} else {
			return false;
		}
	}

	/**
	 * Check if key belongs to a file
	 * 
	 * @param string $key
	 * @return bool
	 */
	public static function exists(string $key)
	{
		if ('' != ($appname = self::init($key))) {
			$filename = Database::readByStatement("userdata", "physical_path", "WHERE `app`='?' AND `identifier`='?'", $appname, $key);
			return ($filename != '' && IO::exists($filename));
		} else {
			return false;
		}
	}

	/**
	 * Get file path in system.
	 * e.g. /storage/data/app/file.ext
	 * @param string $key
	 * @param bool $with_cache_control
	 * @return string
	 */
	public static function getPath(string $key)
	{
		if ('' != ($appname = self::init($key))) {
			$filename = Database::readByStatement("userdata", "physical_path", "WHERE `app`='?' AND `identifier`='?'", $appname, $key);
			if ($filename != '') {
				return (__ROOTDIR . $filename);
			}
		}
	}

	/**
	 * Get URL address of the file.
	 * e.g. /assets/app/file.ext
	 * @param string $key
	 * @param bool $with_cache_control
	 * @return string
	 */
	public static function getURL(string $key, bool $with_cache_control = false)
	{
		if ('' != ($appname = self::init($key))) {
			$d = Database::getRowByStatement("userdata", "WHERE `app`='?' AND `identifier`='?'", $appname, $key);
			$filename = $d["physical_path"];
			if ($filename != '') {
				if ($with_cache_control) $filename .= "?v=" . $d["ver"];
				if ($d["secure"]) {
					return (str_replace("/storage/data", "/assets", $filename));
				} else {
					return (str_replace("/" . __PUBLICDIR, "", $filename));
				}
			}
		}
	}

	/**
	 * Read file instantly, using file_get_contents()
	 * @param string $key
	 * @return mixed
	 */
	public static function read(string $key)
	{
		if ('' != ($appname = self::init($key))) {
			$filename = Database::readByStatement("userdata", "physical_path", "WHERE `app`='?' AND `identifier`='?'", $appname, $key);
			if ($filename != '') {
				return self::$cache[$appname . $key] ?? self::$cache[$appname . $key] = file_get_contents(IO::physical_path($filename));
			}
		}
	}

	/**
	 * Read save MIME type
	 * @param string $key
	 * @return mixed
	 */
	public static function getMIME(string $key)
	{
		if ('' != ($appname = self::init($key))) {
			return Database::readByStatement("userdata", "mime_type", "WHERE `app`='?' AND `identifier`='?'", $appname, $key);
		}
	}

	/**
	 * Remove file
	 * @param string $key
	 * @return bool
	 */
	public static function remove(string $key)
	{
		if ('' != ($appname = self::init($key))) {
			$filename = Database::readByStatement("userdata", "physical_path", "WHERE `app`='?' AND `identifier`='?'", $appname, $key);
			unset(self::$cache[$appname . $key]);
			return
				Database::deleteByStatement("userdata", "WHERE `app`='?' AND `identifier`='?'", $appname, $key) &&
				unlink(IO::physical_path($filename));
		} else {
			return false;
		}
	}
}
