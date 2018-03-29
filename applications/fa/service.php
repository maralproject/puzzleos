<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.1.1") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.fontawesome
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.1.1
 */
 
Template::addHeader('<link rel="stylesheet" href="'.__SITEURL.'/applications/fa/lib/css/font-awesome.min.css"/>');
//Template::addHeader('<style type="text/css">'.file_get_contents(IO::physical_path("'.__SITEURL.'/applications/fa/lib/css/font-awesome.min.css")).'</style>');

?>