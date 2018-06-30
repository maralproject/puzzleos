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

$dir = IO::publish($appProp->path."/lib");

Template::addHeader('<link rel="stylesheet" href="'.$dir.'/css/font-awesome.min.css"/>');

?>