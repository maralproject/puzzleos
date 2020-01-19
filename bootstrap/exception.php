<?php

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
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
	private static $printed = false;

	private static function wLog(\Throwable $error, $suggestion, $file, $line, $trace)
	{
		Log::emergency(get_class($error) . ": " . $error->getMessage(), [
			"suggestion" => $suggestion,
			"caller" => "$file($line)",
			"url" => __HTTP_REQUEST,
			"session" => session_id(),
			"env" => is_cli() ? ($GLOBALS["_WORKER"] ? "WORKER" : "CLI") : "WEB",
			"stack_trace" => $trace
		]);
	}

	private static function printPage($msg = "", $suggestion = "")
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

	public static function saveErrorLog(\Throwable $e)
	{
		self::wLog($e, $e->suggestion ?? "", $e->getFile(), $e->getLine(), $e->getTraceAsString());
	}

	public static function printErrorPage(\Throwable $e)
	{
		self::saveErrorLog($e);
		ob_end_flush();
		while (ob_get_level()) ob_get_clean();
		if (is_cli()) {
			echo "\n--\n" . $e;
		} else {
			$message = "";
			if ($e instanceof DatabaseError || $e instanceof IOError) {
				$message = ((string) $e);
			} else {
				$message = get_class($e) . ": " . $e->getMessage();
			}
			abort(500, "Internal Server Error", false);
			self::printPage($message, $e->suggestion ?? "");
			exit;
		}
	}

	public $suggestion;
	public function __construct($message, $suggestion = "", int $code = -1, Exception $previous = null, string $file = null, int $line = null)
	{
		$this->suggestion = $suggestion;
		parent::__construct($message, $code, $previous);
		if ($file) $this->file = $file;
		if ($line) $this->line = $line;
	}
}

/**
 * For security aim, IO error don't output it's error to public.
 * Instead, it will show a generic error message, while logging the
 * real error and stack trace in logging file
 */
class IOError extends PuzzleError
{
	public function __toString()
	{
		return "We cannot locate the file.";
	}
}

/**
 * For security purpose, database error do not output it's error to public.
 * Instead, it will show a database error message, and log
 * the real error and stack trace in logging file.
 */
class DatabaseError extends PuzzleError
{
	public function __toString()
	{
		return "Something error on database execution.";
	}
}

class AppStartError extends PuzzleError
{
}

register_shutdown_function(function () {
	$e = error_get_last();
	if ($e['type'] & (E_COMPILE_ERROR | E_CORE_ERROR | 1)) {
		// Something went wrong with PHP code. Not by catchable errors.
		$error = new PuzzleError($e['message'], "PHP Core/Compile error occured", $e['type'], null, $e['file'], $e['line']);
		PuzzleError::printErrorPage($error);
	}
});
