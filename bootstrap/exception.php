<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 MARAL INDUSTRIES
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

/**
 * Throw an error, write it to error.log, and display it to user
 */
class PuzzleError extends Exception
{
	private $suggestion;
	private static $printed = false;

	private static function wLog($message, $suggestion, $file, $line, $trace)
	{
		$f = fopen(__ROOTDIR . "/error.log", "a+");
		fwrite($f, date("d/m/Y H:i:s", time()) . " " . date_default_timezone_get() . "\r\n");
		fwrite($f, "Message: " . $message . "\r\n");
		fwrite($f, "Suggestion: " . $suggestion . "\r\n");
		fwrite($f, "Caller: $file($line)\r\n");
		fwrite($f, "URL: " . __HTTP_REQUEST . "\r\n");
		fwrite($f, "Session: " . session_id() . "\r\n");
		fwrite($f, str_replace("#", "\r\n#", $trace));
		fwrite($f, "\r\n=========\r\n");
		fclose($f);
	}

	public static function printPage($msg = "", $suggestion = "")
	{
		if (self::$printed) return;

		$active = POSConfigMultidomain::$default_template;
		if (file_exists(__ROOTDIR . "/templates/$active/500.php")) {
			include __ROOTDIR . "/templates/$active/500.php";
		} else {
			include __ROOTDIR . "/templates/system/500.php";
		}

		self::$printed = true;
	}

	public static function handleErrorView(\Throwable $e)
	{
		ob_end_flush();
		while (ob_get_level()) ob_get_clean();
		self::wLog($e->getMessage(), $e->suggestion ?? "", $e->getFile(), $e->getLine(), $e->getTraceAsString());
		self::printPage($e->getMessage());
		abort(500, "Internal Server Error");
	}

	public static function handleErrorControl(\Throwable $e, bool $abort = true)
	{
		ob_end_flush();
		while (ob_get_level()) ob_get_clean();
		self::wLog($e->getMessage(), $e->suggestion ?? "", $e->getFile(), $e->getLine(), $e->getTraceAsString());
		return $abort ? abort(500, "Internal Server Error") : $e->__toString();
	}

	public function __construct($message, $suggestion = "", int $code = -1, Exception $previous = null, string $file = null, int $line = null)
	{
		$this->suggestion = $suggestion;
		parent::__construct($message, $code, $previous);
		if ($file) $this->file = $file;
		if ($line) $this->line = $line;
	}

	public function __toString()
	{
		if (is_cli()) {
			self::wLog($this->getMessage(), $this->suggestion ?? "", $this->getFile(), $this->getLine(), $this->getTraceAsString());
			echo "\n---\n" . parent::__toString() . "\n";
		} else {
			self::handleErrorView($this);
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
{ }
class WorkerError extends PuzzleError
{ }

register_shutdown_function(function () {
	$e = error_get_last();
	if ($e['type'] & (E_COMPILE_ERROR | E_CORE_ERROR | 1)) {
		// Something went wrong with PHP code. Not by catchable errors.
		PuzzleError::handleErrorView(new PuzzleError($e['message'], "PHP Core/Compile error occured", $e['type'], null, $e['file'], $e['line']));
	}
});
