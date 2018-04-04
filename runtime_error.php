<?php
defined("__POSEXEC") or die("No direct access allowed!");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
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

//error_reporting(E_ALL);											//Debug
//error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR | E_NOTICE);	//Normal + Notice Report
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);				//Normal
//error_reporting(0);												//None

/* This code is used to stop ob_get_level() which is a stopper code */
if (ob_get_level()) ob_end_clean();

/**
 * For security aim, IO error donot output it's error to public.
 * Instead, it will show a database error message, while logging the
 * real error and stack trace in error.log file
 */
class IOError extends PuzzleError{
	public function __toString() {
		//Clear all buffer
		while (ob_get_level())	ob_get_clean();
		?>
<html>
	<head>
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo __SYSTEM_NAME?> Error</title>
		<style>
			li{
				margin-left:-40px;
				list-style:none;
				margin-bottom:10px;
			}
			@media(min-width:650px){
				#wrap{
					margin:auto;margin-top:5vh;padding:15px!important;width:100%;max-width:600px;							
					-webkit-box-shadow: 0px 0px 8px 0px #e0e0e0;
					-moz-box-shadow: 0px 0px 8px 0px #e0e0e0;
					box-shadow: 0px 0px 8px 0px #e0e0e0;
				}
			}
		</style>
	</head>
	<body style="margin:0px;width:100%;font-family:Roboto, sans-serif, arial;font-size:13pt;">
		<div id="wrap" style="padding:25px;">
		<div style="font-size:30pt;font-weight:bold;">Oops...</div>
		<ul>
			<li>We cannot locate the file.</li>
		</ul>
		<br>
		<br>
		<p style="font-size:9pt;text-align:right;color:#a0a0a0;">For more information please see error.log</p>
		</div>
	</body>
</html>
		<?php
		die();
    }
}

/**
 * For security aim, Database error donot output it's error to public.
 * Instead, it will show a database error message, while logging the
 * real error and stack trace in error.log file
 */
class DatabaseError extends PuzzleError{
	public function __toString() {
		//Clear all buffer
		while (ob_get_level())	ob_get_clean();
		?>
<html>
	<head>
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo __SYSTEM_NAME?> Error</title>
		<style>
			li{
				margin-left:-40px;
				list-style:none;
				margin-bottom:10px;
			}
			@media(min-width:650px){
				#wrap{
					margin:auto;margin-top:5vh;padding:15px!important;width:100%;max-width:600px;							
					-webkit-box-shadow: 0px 0px 8px 0px #e0e0e0;
					-moz-box-shadow: 0px 0px 8px 0px #e0e0e0;
					box-shadow: 0px 0px 8px 0px #e0e0e0;
				}
			}
		</style>
	</head>
	<body style="margin:0px;width:100%;font-family:Roboto, sans-serif, arial;font-size:13pt;">
		<div id="wrap" style="padding:25px;">
		<div style="font-size:30pt;font-weight:bold;">Oops...</div>
		<ul>
			<li>Something error on database execution.</li>
		</ul>
		<br>
		<br>
		<p style="font-size:9pt;text-align:right;color:#a0a0a0;">For more information please see error.log</p>
		</div>
	</body>
</html>
		<?php
		die();
    }
}

class AppStartError extends PuzzleError{
	
}

/**
 * Throw an error, write to error.log, and display it to user
 * If you want to bypass this exception, use try/catch
 */
class PuzzleError extends Exception{
	
	private $suggestion;
	
	public function __construct($message, $suggestion="", $code = 0, Exception $previous = null) {		
		$this->suggestion = $suggestion;
		parent::__construct($message, $code, $previous);

		$f = fopen(__DIR__ . "/error.log","a+");
		fwrite($f,date("d/m/Y H:i:s",strtotime("now"))." ".date_default_timezone_get()."\r\n");
		fwrite($f,"Message: ".$this->message."\r\n");
		fwrite($f,"Suggestion: ".$this->suggestion."\r\n");
		fwrite($f,"Caller: ".$this->getFile()." line ".$this->getLine()."\r\n");
		fwrite($f,str_replace("#","\r\n#",$this->getTraceAsString()));
		fwrite($f,"\r\n=========\r\n");
		fclose($f);
    }
	
	public function __toString() {
		//Clear all buffer
		while (ob_get_level())	ob_get_clean();
		?>
<html>
	<head>
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo __SYSTEM_NAME?> Error</title>
		<style>
			li{
				margin-left:-40px;
				list-style:none;
				margin-bottom:10px;
			}
			@media(min-width:650px){
				#wrap{
					margin:auto;margin-top:5vh;padding:15px!important;width:100%;max-width:600px;							
					-webkit-box-shadow: 0px 0px 8px 0px #e0e0e0;
					-moz-box-shadow: 0px 0px 8px 0px #e0e0e0;
					box-shadow: 0px 0px 8px 0px #e0e0e0;
				}
			}
		</style>
	</head>
	<body style="margin:0px;width:100%;font-family:Roboto, sans-serif, arial;font-size:13pt;">
		<div id="wrap" style="padding:25px;">
		<div style="font-size:30pt;font-weight:bold;">Oops...</div>
		<ul>
			<li><?php echo str_replace("\n","<br>",$this->message)?></li>
			<?php if($this->suggestion!=""):?><li><?php echo $this->suggestion?></li><?php endif;?>						
		</ul>
		<br>
		<br>
		<p style="font-size:9pt;text-align:right;color:#a0a0a0;">For more information please see error.log</p>
		</div>
	</body>
</html>
		<?php
		die();
    }
}

register_shutdown_function(function(){
	$last_error = error_get_last();
	if ($last_error['type'] === E_ERROR || $last_error['type'] === E_PARSE || $last_error['type'] === E_COMPILE_ERROR) {
		//throw new PuzzleError($last_error['message'] . " on " . $last_error["file"] . ":" . $last_error["line"]);
	}
})

?>
