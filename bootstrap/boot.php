<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2020 PT SIMUR INDONESIA
 */ 

(function () {
error_reporting(E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR);
require "oem.php";
require "defines.php";
require "helper.php";
require "autoload.php";

error_reporting(0);
require "exception.php";

if (!version_compare(PHP_VERSION, "7.0.0") < 0) {
	die("ERROR:\tPlease upgrade your PHP version at least to 7.0.0");
}

if (PHP_SAPI == "cli" && (!defined("__POSCLI") && !defined("__POSWORKER"))) {
	die("ERROR:\tCLI Execution Aborted.");
}

set_time_limit(TIME_LIMIT);

/***********************************
 * Setting up the security stuff
 ***********************************/
header("X-XSS-Protection: 1; mode=block");
if (defined("X_FRAME_OPTIONS_DENY")) header("X-Frame-Options: sameorigin");

/***********************************
 * Maintenance Mode Handler
 * Now, CLI can still running even
 * maintenance mode is enabled.
 *
 * To enter maintenance mode,
 * create "site.offline" file
 * in the root directory
 ***********************************/
if (file_exists(__ROOTDIR . "/site.offline")) {
	if (PHP_SAPI == "cli") {
		define("__MAINTENANCE", true);
	} else {
		header('Retry-After: 300');
		abort(503, "Under Maintenance", false);
		include(__ROOTDIR . "/templates/system/503.php");
		exit;
	}
} else {
	define("__MAINTENANCE", false);
}

/***********************************
 * Prepare all directories
 ***********************************/
preparedir(__ROOTDIR . "/storage");
preparedir(__ROOTDIR . "/storage/logs");
preparedir(__ROOTDIR . "/storage/dbcache");
preparedir(__ROOTDIR . "/storage/data");
preparedir(__ROOTDIR . "/storage/cache");
preparedir(__ROOTDIR . "/storage/cache/applications");
preparedir(__ROOTDIR . "/storage/cache/bootstrap");
preparedir(__ROOTDIR . "/" . __PUBLICDIR . "/assets");
preparedir(__ROOTDIR . "/" . __PUBLICDIR . "/res");
preparedir(__ROOTDIR . "/" . __PUBLICDIR . "/cache", function () {
	file_put_contents(__ROOTDIR . "/" . __PUBLICDIR . "/cache/.htaccess", 'Header set Cache-Control "max-age=2628000, public"');
});

try {
	/***********************************
	 * Get the configuration files
	 ***********************************/
	require "configman.php";

	/***********************************
	 * Removing installation directory
	 ***********************************/
	if (file_exists(__ROOTDIR . "/" . __PUBLICDIR . "/install")) {
		if (!IO::remove_r("/" . __PUBLICDIR . "/install"))
			throw new PuzzleError("Please remove /" . __PUBLICDIR . "/install directory manually for security purpose");
	}

	/***********************************
	 * Feature that must be loaded
	 * without autoloader
	 ***********************************/
	require("session.php");
	require("time.php");
	require("application.php");
	require("services.php");

	/***********************************
	 * Writing session to cookie
	 ***********************************/
	PuzzleSession::writeCookie();

	/***********************************
	 * Process private file if requested
	 * from browser. Public file handled
	 * by Webserver directly
	 ***********************************/
	if (request(0) == "assets" && !is_cli()) {
		$f = urldecode("/" . str_replace("assets/", "storage/data/", __HTTP_URI));
		$d = Database::getRowByStatement("userdata", "where `physical_path`='?'", $f);
		$appProp = $d["app"];
		if ($appProp != "") {
			try {
				$appProp = iApplication::run($appProp);
				/**
				 * File access including checking the user permission 
				 * by calling `PuzzleUser::isAccess()`
				 * should be done by the authorize.userdata.php itself.
				 */
				if (file_exists($appProp->path . "/authorize.userdata.php")) {
					if (((function ($file_key, $file_mime) use ($appProp) {
						return include($appProp->path . "/authorize.userdata.php");
					})($d["identifier"], $d["mime_type"])) !== true) {
						abort(404, "File not found");
					}
				} else {
					abort(403, "Private asset");
				}
				IO::streamFile($f);
			} catch (AppStartError $e) {
				abort(404, "File not found");
			}
		}
	}
} catch (Throwable $e) {
	PuzzleError::printErrorPage($e);
	abort(500, "Internal Server Error");
}
})();
