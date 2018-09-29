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

//error_reporting(E_ALL);											//Debug
//error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR | E_NOTICE);	//Normal + Notice Report
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);				//Normal
//error_reporting(0);												//None

/* This code is used to stop ob_get_level() which is a stopper code */
if (ob_get_level()) ob_end_clean();

/**
 * Throw an error, write to error.log, and display it to user
 * If you want to bypass this exception, use try/catch
 */
class PuzzleError extends Exception{
	
	private $suggestion;
	
	public function __construct($message, $suggestion="", $code = -1, Exception $previous = null) {		
		$this->suggestion = $suggestion;
		parent::__construct($message, $code, $previous);

		$f = fopen(__ROOTDIR . "/error.log","a+");
		fwrite($f,date("d/m/Y H:i:s",time())." ".date_default_timezone_get()."\r\n");
		fwrite($f,"Message: ".$this->message."\r\n");
		fwrite($f,"Suggestion: ".$this->suggestion."\r\n");
		fwrite($f,"Caller: ".$this->getFile()."(".$this->getLine().")\r\n");
		fwrite($f,"URL: ".__HTTP_REQUEST."\r\n");
		fwrite($f,str_replace("#","\r\n#",$this->getTraceAsString()));
		fwrite($f,"\r\n=========\r\n");
		fclose($f);
    }
	
	public function __toString() {
		//Clear all buffer
		while (ob_get_level())	ob_get_clean();
		if(!__isCLI()):
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
					margin: auto;
					margin-top: 5vh;
					padding: 15px 25px!important;
					width: 100%;
					max-width: 600px;
					box-shadow: 1px 1px 4px 0px #9E9E9E;
					border-radius: 15px;
				}
			}
		</style>
	</head>
	<body style="margin:0px;width:100%;font-family:Roboto, sans-serif, arial;font-size:13pt;">
		<div id="wrap" style="padding:25px;">
		<div style="font-size:30pt;font-weight:bold;">Oops...</div>
		<ul>
			<li><?php echo nl2br($this->message)?></li>
			<?php if($this->suggestion!=""):?><li><?php echo $this->suggestion?></li><?php endif;?>						
		</ul>
		<br>
		<br>
		<p style="font-size:9pt;text-align:right;color:#a0a0a0;">For more information please see error.log</p>
		</div>
	</body>
</html>
		<?php
		else:
			echo("ERROR({$this->getCode()}): " . $this->message . "\n");
			return "";
		endif;
		exit;
    }
}

/**
 * For security aim, IO error don't output it's error to public.
 * Instead, it will show a database error message, while logging the
 * real error and stack trace in error.log file
 */
class IOError extends PuzzleError{
	public function __toString() {
		//Clear all buffer
		while (ob_get_level())	ob_get_clean();
		if(!__isCLI()):
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
					margin: auto;
					margin-top: 5vh;
					padding: 15px 25px!important;
					width: 100%;
					max-width: 600px;
					box-shadow: 1px 1px 4px 0px #9E9E9E;
					border-radius: 15px;
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
		else:
			echo("ERROR({$this->getCode()}): " . $this->message . "\n");
			return "";
		endif;
		exit;
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
		if(!__isCLI()):
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
					margin: auto;
					margin-top: 5vh;
					padding: 15px 25px!important;
					width: 100%;
					max-width: 600px;
					box-shadow: 1px 1px 4px 0px #9E9E9E;
					border-radius: 15px;
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
		else:
			echo("ERROR({$this->getCode()}): " . $this->message . "\n");
			return "";
		endif;
		exit;
    }
}

class AppStartError extends PuzzleError{}
class WorkerError extends PuzzleError{}

register_shutdown_function(function(){
	$e = error_get_last();
    if(in_array($e['type'],[E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE])){
        throw new PuzzleError("{$e['message']} on {$e['file']}({$e['line']})",null,$e['code']);
    }
});

?>
