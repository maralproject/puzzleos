<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

$dir = IO::publish($appProp->path."/lib");

Template::addHeader('<link rel="stylesheet" href="'.$dir.'/css/all.min.css"/>');
Template::addHeader('<link rel="stylesheet" href="'.$dir.'/css/v4-shims.min.css"/>');
