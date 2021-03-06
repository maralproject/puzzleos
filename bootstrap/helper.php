<?php

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2020 PT SIMUR INDONESIA
 */

/**
 * Get HTTP Request method
 */
function m()
{
	return $_SERVER['REQUEST_METHOD'];
}

/**
 * Include PHP file outside the scope.
 */
function include_ext(string $__path, array $vars = null)
{
	$vars != null ? extract($vars) : null;
	unset($vars);
	return include $__path;
}

/**
 * Include PHP file once outside the scope.
 */
function include_once_ext(string $__path, array $vars = null)
{
	$vars != null ? extract($vars) : null;
	unset($vars);
	return include_once $__path;
}

/**
 * Require PHP file outside the scope.
 */
function require_ext(string $__path, array $vars = null)
{
	$vars != null ? extract($vars) : null;
	unset($vars);
	return require $__path;
}

/**
 * Floor number with precision
 */
function floorp(float $val, int $precision)
{
	$mult = pow(10, $precision);
	return floor($val * $mult) / $mult;
}

/**
 * Require PHP file once outside the scope.
 */
function require_once_ext(string $__path, array $vars = null)
{
	$vars != null ? extract($vars) : null;
	unset($vars);
	return require_once $__path;
}

/**
 * Shutdown PuzzleOS and send HTTP code to the client.
 * 
 * @param integer $code
 * @param string $text
 * @param boolean $exit
 */
function abort(int $code, string $text = null, $exit = true)
{
	if (class_exists('PuzzleSession') && PuzzleSession::get()) PuzzleSession::get()->writeCookie();
	if (!is_cli()) {
		header($_SERVER['SERVER_PROTOCOL'] . " $code $text", true, $code);
	}
	if ($exit) exit;
}

/**
 * Check if stack caller is from the file itself.
 * 
 * @return bool
 */
function is_callbyme()
{
	$d = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
	return ($d[0]['file'] == $d[1]['file']);
}

/**
 * Return the absolute internal path from apps or templates.
 * 
 * @param string $path 
 * @return string
 */
function my_dir(string $path = null)
{
	$p = ltrim(str_replace(__ROOTDIR, '', btfslash(debug_backtrace(null, 1)[0]['file'])), '/');

	if (starts_with($p, 'closure://')) {
		// This function called from a closure. Only resolve if this is a worker.
		if (Worker::inEnv()) {
			return __ROOTDIR . '/applications/' . $WORKER['appdir'] . '/' . ltrim(btfslash($path), '/');
		}
	}

	$caller = explode('/', $p);
	switch ($caller[0]) {
		case 'applications':
		case 'templates':
			break;
		default:
			return null;
	}
	return __ROOTDIR . '/' . $caller[0] . '/' . $caller[1] . '/' . ltrim(btfslash($path), '/');
}

/**
 * Return the absolute storage path for this app
 * 
 * @param string $path 
 * @param bool $private 
 * @return string
 */
function storage(string $path = null, bool $private = true)
{
	$basepath = $private ? '/storage/data/' : '/public/assets/';
	$p = ltrim(str_replace(__ROOTDIR, '', btfslash(debug_backtrace(null, 1)[0]['file'])), '/');

	if (starts_with($p, 'closure://')) {
		// This function called from a closure. Only resolve if this is a worker.
		if (Worker::inEnv()) {
			preparedir($prefixdir = (__ROOTDIR . $basepath . $WORKER['app']));
			return $prefixdir . '/' . ltrim(btfslash($path), '/');
		}
	}

	$caller = explode('/', $p);
	switch ($caller[0]) {
		case 'applications':
			break;
		default:
			return null;
	}
	preparedir($prefixdir = (__ROOTDIR . $basepath . AppManager::getNameFromDirectory($caller[1])));
	return $prefixdir . '/' . ltrim(btfslash($path), '/');
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
	if (PHP_BINARY && in_array(PHP_SAPI, array('cli', 'cli-server', 'phpdbg'), true)) {
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
	if (@is_executable($php = PHP_BINDIR . ('\\' === DIRECTORY_SEPARATOR ? '\\php.exe' : '/php'))) {
		return $php;
	}

	// May be it's exists on system environment
	$paths = explode(PATH_SEPARATOR, getenv('PATH'));
	foreach ($paths as $path) {
		if (strstr($path, 'php.exe') && isset($_SERVER['WINDIR']) && file_exists($path) && is_file($path)) {
			return $path;
		} else {
			$php_executable = $path . DIRECTORY_SEPARATOR . 'php' . (isset($_SERVER['WINDIR']) ? '.exe' : '');
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
 * Quick shortcut for echo htmlspecialchars()
 */
function h(string $html = null)
{
	echo htmlspecialchars($html);
}

/**
 * Quick shortcut for nl2br(htmlspecialchars())
 */
function hnl2br(string $html = null)
{
	echo nl2br(htmlspecialchars($html));
}

/**
 * Quick shortcut for echo json_encode()
 */
function j($json_data)
{
	echo json_encode($json_data);
}

/**
 * Quick shortcut for echo json_encode(), in obfuscated way
 */
function je($json_data)
{
	echo 'JSON.parse(window["\x61\x74\x6f\x62"](`' . base64_encode(json_encode($json_data)) . '`))';
}

/**
 * Replace first occurrence pattern in string.
 * 
 * @param string $find Find
 * @param string $replace Replace
 * @param string $haystack Source
 * @return string
 */
function str_replace_first(string $find, string $replace, string $haystack)
{
	if (strpos($find, $haystack) !== false) {
		return substr_replace($find, $replace, strpos($find, $haystack), strlen($haystack));
	}
	return $find;
}

/**
 * Validate a JSON string.
 * 
 * @param string $string
 * @return bool
 */
function is_json(string $string = null)
{
	try {
		json_decode($string, false, 512, JSON_THROW_ON_ERROR);
		return true;
	} catch (JsonException $e) {
		return false;
	}
}

/**
 * Redirect to another page.
 * Better work if loaded before HTML output is starting.
 * Use "http://" or "https://" to redirect outside this site.
 * 
 * @param string $app e.g. "users/login"
 * @param int $http_code HTTP redirection code 3xx
 */
function redirect(string $url = '', int $http_code = 302)
{
	//Removing trailing slashes in leftside
	$app = ltrim($url, '/');

	//Removing all whitespace
	$app = preg_replace('/\s+/', '', $app);

	//Checking if URL requested is local or not
	if (!starts_with($app, 'http://') && !starts_with($app, 'https://')) {
		$app = '/' . $app;
	}

	//Writing out cookie before leaving
	PuzzleSession::get()->writeCookie();

	if (headers_sent()) {
		// Clearing buffer
		ob_end_flush();
		while (ob_get_level()) ob_get_clean();
		// Redirecting
		echo ('<script>window.location="' . $app . '";</script>');
	} else {
		header('Location: ' . $app);
	}
	abort($http_code);
}

/**
 * Send JSON data to client and shutdown PuzzleOS
 * 
 * @param mixed $jsonable Any variable that can be used with json_encode
 */
function json_out($jsonable, int $code = 200)
{
	if (headers_sent()) {
		throw new PuzzleError('Cannot send JSON data, header is already sent');
	} else {
		header('Content-Type:application/json');
		http_response_code($code);
		die(json_encode($jsonable));
	}
}

function prec(float $number, int $precision = 4)
{
	return floatval(number_format((float) $number, $precision, '.', ''));
}

/**
 * Get the bytes from PHP size format (e.g. 128M)
 * 
 * @param integer $php_size
 * @return int
 */
function get_php_bytes($php_size)
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
 * Get maximum file size allowed by PHP to be uploaded.
 * Use this information to prevent something wrong in your app
 * when user upload a very large data.
 * 
 * @return integer
 */
function php_max_upload_size()
{
	$max_upload = get_php_bytes(ini_get('post_max_size'));
	$max_upload2 = get_php_bytes(ini_get('upload_max_filesize'));
	return (int) (($max_upload < $max_upload2 && $max_upload != 0) ? $max_upload : $max_upload2);
}

/**
 * Generate random string based on character list.
 * 
 * @param integer $length 
 * @param string $chr 
 * @return string
 */
function rand_str(int $length, string $chr = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
	$clen = strlen($chr);
	$rs = '';
	for ($i = 0; $i < $length; $i++) {
		$rs .= $chr[rand(0, $clen - 1)];
	}
	return $rs;
}

/**
 * Convert all backward slash to forward slash.
 * 
 * @param string $str 
 * @return string
 */
function btfslash(string $str = null)
{
	return str_replace('\\', '/', $str);
}

/**
 * Convert all forward slash to backward slash.
 * 
 * @param string $str 
 * @return string
 */
function ftbslash(string $str = null)
{
	return str_replace('/', '\\', $str);
}

/**
 * Check if directory exist or not. If not exists create it.
 * 
 * @param string $dir 
 * @param callable $post_prep_func 
 * @return string 
 */
function preparedir(string $dir, callable $post_prep_func = null)
{
	if (!file_exists($dir)) {
		@mkdir($dir);
		if ($post_prep_func !== null) $post_prep_func();
	}
}

/**
 * Check if string is startsWith.
 * 
 * @param string $haystack 
 * @param string $needle 
 * @return string 
 */
function starts_with(string $haystack, string $needle)
{
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

/**
 * Check if string is endsWith.
 * 
 * @param string $haystack 
 * @param string $needle 
 * @return string 
 */
function ends_with(string $haystack, string $needle)
{
	$length = strlen($needle);
	if ($length == 0) return true;
	return (substr($haystack, -$length) === $needle);
}

/**
 * Check if string contains.
 * 
 * @param string $haystack 
 * @param string $needle 
 * @return bool
 */
function str_contains(string $haystack, string $needle)
{
	return (strpos($haystack, $needle) !== false);
}

/**
 * Check if strings contains some charlist.
 * 
 * @param string $haystack
 * @param array $chrlist
 * @return bool
 */
function str_haschar(string $haystack, ...$chrlist)
{
	foreach ($chrlist as $c) {
		if (str_contains($haystack, $c)) return true;
	}
	return false;
}

/**
 * Get HTTP Request index.
 * 
 * @param string $index e.g. "app", "action", or index
 * @return string
 */
function request($index)
{
	if (is_cli()) return null; //No URI on CLI

	$a = explode('/', __HTTP_URI);
	$a[0] = $a['app'] = ($a[0] == '' ? POSConfigMultidomain::$default_application : $a[0]);
	$a['action'] = $a[1];

	if (is_integer($index)) {
		$key = $index;
	} else {
		$key = strtolower($index);
	}
	return (isset($a[$key]) ? urldecode($a[$key]) : null);
}

/**
 * Check if version of current PuzzleOS meets the required version.
 * 
 * @param string $version Required function
 * @return bool
 */
function need_version(string $version)
{
	return (version_compare(__POS_VERSION, $version) >= 0);
}

/**
 * Check CLI environment.
 * 
 * @return bool
 */
function is_cli()
{
	return (PHP_SAPI == 'cli' && (defined('__POSCLI') || defined('__POSWORKER')));
}

/**
 * Check if current environment is Windows.
 * 
 * @return bool
 */
function is_win()
{
	return (str_contains(PHP_OS, 'WIN'));
}

/**
 * Get a new CronTrigger instances.
 * 
 * @return CronTrigger
 */
function _CT()
{
	return new CronTrigger();
}

/**
 * A custom class like stdObject,
 * the differences is, you can fill it with a bunch of fucntion.
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
