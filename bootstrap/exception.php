<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * Needs to be disabled, as it can cause security problem.
 * Imagine if user input is php://filter/convert.base64-encode/resource=index.php, and feeded into:
 * 		include("php://filter/convert.base64-encode/resource=index.php");
 * PHP will output the entire file as base64.
 * 
 * This is just the first step, user application can do stream_wrapper_restore("php")
 * to restore php stream wrapper.
 */
stream_wrapper_unregister("php");

error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);

/**
 * Throw an error, write it to error.log, and display it to user
 */
class PuzzleError extends Exception
{
	private $suggestion;

	public static function printPage($msg = "", $suggestion = "")
	{
		include __ROOTDIR . "/templates/system/500.php";
	}

	public function __construct($message, $suggestion = "", $code = -1, Exception $previous = null, bool $log = true)
	{
		$this->suggestion = $suggestion;
		parent::__construct($message, $code, $previous);

		if ($log) {
			$f = fopen(__ROOTDIR . "/error.log", "a+");
			fwrite($f, date("d/m/Y H:i:s", time()) . " " . date_default_timezone_get() . "\r\n");
			fwrite($f, "Message: " . $this->message . "\r\n");
			fwrite($f, "Suggestion: " . $this->suggestion . "\r\n");
			fwrite($f, "Caller: " . $this->getFile() . "(" . $this->getLine() . ")\r\n");
			fwrite($f, "URL: " . __HTTP_REQUEST . "\r\n");
			fwrite($f, str_replace("#", "\r\n#", $this->getTraceAsString()));
			fwrite($f, "\r\n=========\r\n");
			fclose($f);
		}
	}

	public function __toString()
	{
		//Clear all buffer
		while (ob_get_level()) ob_get_clean();
		if (!is_cli()) {
			self::printPage($this->message, $this->suggestion);
		} else {
			echo ("ERROR({$this->getCode()}): " . $this->message . "\n");
		}
		return "";
	}
}

/**
 * For security aim, IO error don't output it's error to public.
 * Instead, it will show a database error message, while logging the
 * real error and stack trace in error.log file
 */
class IOError extends PuzzleError
{
	public function __toString()
	{
		//Clear all buffer
		while (ob_get_level()) ob_get_clean();
		if (!is_cli()) {
			parent::printPage("We cannot locate file");
		} else {
			echo ("ERROR({$this->getCode()}): " . $this->message . "\n");
		}
		return "";
	}
}

/**
 * For security purpose, database error do not output it's error to public.
 * Instead, it will show a database error message, and log
 * the real error and stack trace in error.log file.
 */
class DatabaseError extends PuzzleError
{
	public function __toString()
	{
		//Clear all buffer
		while (ob_get_level()) ob_get_clean();
		if (!is_cli()) {
			parent::printPage("Something error on database execution.");
		} else {
			echo ("ERROR({$this->getCode()}): " . $this->message . "\n");
		}
		return "";
	}
}

class AppStartError extends PuzzleError
{
}
class WorkerError extends PuzzleError
{
}

register_shutdown_function(function () {
	$e = error_get_last();
	if (in_array($e['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
		abort(500, "Internal Server Error", false);
		throw new PuzzleError("{$e['message']} on {$e['file']}({$e['line']})", null, $e['code']);
	}
});