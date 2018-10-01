<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

function include_ext($__path, $vars = null)
{
	$vars != null ? extract($vars) : null;
	unset($vars);
	return include $__path;
}

function include_once_ext($__path, $vars = null)
{
	$vars != null ? extract($vars) : null;
	unset($vars);
	return include_once $__path;
}

function require_ext($__path, $vars = null)
{
	$vars != null ? extract($vars) : null;
	unset($vars);
	return require $__path;
}

function require_once_ext($__path, $vars = null)
{
	$vars != null ? extract($vars) : null;
	unset($vars);
	return require_once $__path;
}

/**
 * Shutdown PuzzleOS and send HTTP code to user
 * @param integer $code
 * @param string $text
 */
function abort(int $code, string $text = "", $exit = true)
{
	header($_SERVER["SERVER_PROTOCOL"] . " $code $text", true, $code);
	if ($exit) exit;
}

/**
 * Check if stack caller is from the file itself
 * @return bool
 */
function is_callbyme()
{
	$d = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
	return ($d[0]["file"] == $d[1]["file"]);
}

/**
 * Return the absolute internal path from apps or templates.
 * @param string $path 
 * @return string
 */
function my_dir($path)
{
	$p = ltrim(str_replace(__ROOTDIR, "", btfslash(debug_backtrace(null, 1)[0]["file"])), "/");
	$caller = explode("/", $p);
	switch ($caller[0]) {
		case "applications":
		case "templates":
			break;
		case "bootstrap":
			if (starts_with($p, "bootstrap/vendor/superclosure/")) {
				// When my_dir is called from a Worker Closure
				return __ROOTDIR . "/applications/" . $GLOBALS["_WORKER"]["appdir"] . "/" . ltrim(btfslash($path), "/");
			}
		default:
			return null;
	}
	return __ROOTDIR . "/" . $caller[0] . "/" . $caller[1] . "/" . ltrim(btfslash($path), "/");
}

/**
 * Find PHP binary location on server
 * Modified from Symfony Component
 * 
 * @url https://github.com/symfony/process/blob/master/PhpExecutableFinder.php
 * @return string If found
 * @return FALSE If not found
 */
function php_bin()
{
	if ($php = getenv('PHP_BINARY')) {
		if (!is_executable($php)) {
			return false;
		}
		return $php;
	}
	
	// PHP_BINARY return the current sapi executable
	if (PHP_BINARY && \in_array(\PHP_SAPI, array('cli', 'cli-server', 'phpdbg'), true)) {
		return PHP_BINARY;
	}

	if ($php = getenv('PHP_PATH')) {
		if (!@is_executable($php)) {
			return false;
		}
		return $php;
	}
	if ($php = getenv('PHP_PEAR_PHP_BIN')) {
		if (@is_executable($php)) {
			return $php;
		}
	}
	if (@is_executable($php = PHP_BINDIR . ('\\' === \DIRECTORY_SEPARATOR ? '\\php.exe' : '/php'))) {
		return $php;
	}
	
	// May be it's exists on system environment
	$paths = explode(PATH_SEPARATOR, getenv('PATH'));
	foreach ($paths as $path) {
		if (strstr($path, 'php.exe') && isset($_SERVER["WINDIR"]) && file_exists($path) && is_file($path)) {
			return $path;
		} else {
			$php_executable = $path . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
			if (file_exists($php_executable) && is_file($php_executable)) {
				return $php_executable;
			}
		}
	}

	return false;
}

/**
 * Convert object to Array
 *
 * @param object $d
 * @return array
 */
function obtarr($d)
{
	if (is_object($d)) $d = get_object_vars($d);

	if (is_array($d)) {
		return array_map(__FUNCTION__, $d);
	} else {
		return $d;
	}
}

/**
 * Replace first occurrence pattern in string
 * @param string $str_pattern Find
 * @param string $str_replacement Replace
 * @param string $string Source
 * @return string
 */
function str_replace_first($str_pattern, $str_replacement, $string)
{
	if (strpos($string, $str_pattern) !== false) {
		$occurrence = strpos($string, $str_pattern);
		return substr_replace($string, $str_replacement, strpos($string, $str_pattern), strlen($str_pattern));
	}
	return $string;
}

/**
 * Validate a json string
 * @param string $string
 * @return bool
 */
function is_json($string)
{
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Redirect to another page
 * Better work if loaded in the app controller
 * @param string $app e.g. "users/login"
 */
function redirect($app = "")
{
	$app = ltrim($app, "/");
	$app = preg_replace("/\s+/", "", $app);
	PuzzleSession::writeCookie();
	if (headers_sent()) {
		die("<script>window.location='/$app';</script>");
	} else {
		header("Location: /$app");
		abort(302, "Found");
	}
}

/**
 * Get the real bytes from PHP size format
 * @param integer $php_size
 * @return int
 */
function get_bytes($php_size)
{
	$php_size = trim($php_size);
	$last = strtolower($php_size[strlen($php_size) - 1]);
	switch ($last) {
		case 'g':
			$php_size *= 1024;
		case 'm':
			$php_size *= 1024;
		case 'k':
			$php_size *= 1024;
	}
	return $php_size;
}

/**
 * Get maximum file size allowed by PHP to be uploaded
 * Use this information to prevent something wrong in your app
 * when user upload a very large data.
 * @return integer
 */
function php_max_upload_size()
{
	$max_upload = get_bytes(ini_get('post_max_size'));
	$max_upload2 = get_bytes(ini_get('upload_max_filesize'));
	return (int)(($max_upload < $max_upload2 && $max_upload != 0) ? $max_upload : $max_upload2);
}

/**
 * Generate random string based on character list
 * @param integer $length 
 * @param string $chr 
 * @return string
 */
function rand_str($length, $chr = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ")
{
	$clen = strlen($chr);
	$rs = '';
	for ($i = 0; $i < $length; $i++) {
		$rs .= $chr[rand(0, $clen - 1)];
	}
	return $rs;
}

/**
 * Convert all backward slash to forward slash
 * @param string $str 
 * @return string
 */
function btfslash($str)
{
	return str_replace("\\", "/", $str);
}

/**
 * Check if directory exist or not. If not exists create it.
 * @param string $dir 
 * @param callable $post_prep_func 
 * @return string 
 */
function preparedir($dir, $post_prep_func = null)
{
	if (!file_exists($dir)) {
		@mkdir($dir);
		if (is_callable($post_prep_func)) $post_prep_func();
	}
}

/**
 * Check if string is startsWith
 * @param string $haystack 
 * @param string $needle 
 * @return string 
 */
function starts_with($haystack, $needle)
{
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

/**
 * Check if string is endsWith
 * @param string $haystack 
 * @param string $needle 
 * @return string 
 */
function ends_with($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) return true;
	return (substr($haystack, -$length) === $needle);
}

/**
 * Check if string contains
 * @param string $haystack 
 * @param string $needle 
 * @return bool
 */
function str_contains($haystack, $needle)
{
	return (strpos($haystack, $needle) !== false);
}

/**
 * Check if strings contains some charlist
 * @param string $haystack
 * @param array $chrlist
 * @return bool
 */
function str_haschar($haystack, ...$chrlist)
{
	foreach ($chrlist as $c) {
		if (str_contains($haystack, $c)) return true;
	}
	return false;
}

/**
 * Get HTTP URI based on index
 * @param string $index e.g. "app", "action", or index
 * @return string
 */
function __getURI($index)
{
	if (__isCLI()) return null; //No URI on CLI

	$a = explode("/", __HTTP_URI);
	$a[0] = $a["app"] = ($a[0] == "" ? POSConfigMultidomain::$default_application : $a[0]);
	$a["action"] = $a[1];

	if (is_integer($index)) {
		$key = $index;
	} else {
		$key = strtolower($index);
	}
	return (isset($a[$key]) ? $a[$key] : null);
}

/**
 * Match required version with system version
 * Return TRUE if system requirement fulfilled
 * @param string $version Required function
 * @return bool
 */
function __requiredSystem($version)
{
	return (version_compare(__POS_VERSION, $version, ">="));
}

/**
 * Get if current environment is in CLI or not
 * @return bool
 */
function __isCLI()
{
	return (PHP_SAPI == "cli" && (defined("__POSCLI") || defined("__POSWORKER")));
}

/**
 * Get a new CronTrigger instances
 * @return CronTrigger
 */
function _CT()
{
	return new CronTrigger();
}

/**
 * A custom class like stdObject,
 * the differences is, you can fill it with a bunch of fucntion
 */
class PObject
{
	protected $methods = [];
	public function __construct(array $options)
	{
		$this->methods = $options;
	}
	public function __call($name, $arguments)
	{
		$callable = null;
		if (array_key_exists($name, $this->methods)) $callable = $this->methods[$name];
		else if (isset($this->$name)) $callable = $this->$name;

		if (!is_callable($callable)) throw new PuzzleError("Method {$name} does not exists");

		return call_user_func_array($callable, $arguments);
	}
}
?>