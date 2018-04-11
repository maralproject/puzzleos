<?php
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

define("__POSEXEC", 1);

/* Some custom preferences */
//define("DB_DEBUG",1);				//Enable this line to verbose output database queries to db.log
define("DISABLE_MINIFY",1);			//Enable this line if browser says "ERR_CONTENT_DECODING_FAILED"
//define("ALWAYS_HTTPS",1);			//Enable this line to force HTTPS

require_once("bootstrap.php");

/* Your DEBUG Code should goes here before main app run */
//include_once("debug.php");

/* Run the requested app and resources */
AppManager::startApp();

/* 	Load templates
 *	This will load the UI for the client */
Template::loadTemplate();

?>
