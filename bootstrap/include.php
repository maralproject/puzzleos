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
 * @software     Release: 2.0.0
 
/* This file never included in the system
 * It just used as reference for programming in PHP environment */
throw new PuzzleError("This file should never be included!");
die();

/* This is a variable to get application meta data from control.php
 * NOTE: Can only be loaded from control.php as $appProp variable
 * 
 * e.g. $appProp->rootdir to get application directory
 */
class appProp extends Application{
	
};

/* This is variable to be used in template controller file.
 * NOTE: Can only be loaded from template controller file as $tmpl variable
 * 
 * e.g. $tmpl->dumpHeaders() to printout pre-loaded header
 */
class tmpl{	
	/* Print all pre-loaded header */
	public function dumpHeaders(){};
	
	/* Print all prompt in template */
	public function printPrompt(){};
	
	/* Get current application instance */
	public $app = new appProp;
	
	/* Get HTTP code response to detect some abnormality */
	public $http_code;
	
	/* Get all pre-loaded body's footer content */
	public $postBody;
	
	/* Get template URL 
	 * e.g http://localhost/templates/something
	 */
	public $url;
	
	/**
	 * Get Path system dir
	 * e.g. /templates/blank
	 */
	public $path;
	
	/* Get copyright text */
	public $copyright;
	
	/* Get pre-set title */
	public $title;
}
?>