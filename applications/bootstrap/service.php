<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */
 
$_bs_public = IO::publish($appProp->path . "/lib");

/* Load bootstrap-4.3.1 with jquery */

#Load in header
Template::addHeader('<script type="text/javascript" src="'.$_bs_public.'/jquery-3.4.1.min.js"></script>');
Template::addHeader('<link rel="stylesheet" href="'.$_bs_public.'/bootstrap-4.3.1/css/bootstrap.min.css"/>');
Template::addHeader('<script type="text/javascript" src="'.$_bs_public.'/popper.min.js"></script>');

#Load in post body
Template::appendBody('<script type="text/javascript" src="'.$_bs_public.'/bootstrap-4.3.1/js/bootstrap.min.js"></script>');