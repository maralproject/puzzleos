<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

$dir = IO::publish($appProp->path);

Template::addHeader('<link rel="stylesheet" href="'.$dir.'/falib/css/all.min.css"/>');
Template::addHeader('<link rel="stylesheet" href="'.$dir.'/falib/css/v4-shims.min.css"/>');
