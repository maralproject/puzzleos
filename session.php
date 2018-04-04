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
 
 
/* Make sure that session available to all subdomain */
//TODO: Code below can lead to security issues

//if(ConfigurationGlobal::$use_multidomain) ini_set('session.cookie_domain', explode(":", $_SERVER["HTTP_HOST"])[0]);
 
session_start();

if (!isset($_SESSION['__POSinitiated'])){
	//Preventing user to fixating the session
    session_regenerate_id();
    $_SESSION['__POSinitiated'] = true;	
	//TODO: add session __POSAgent to capture IP, user agent, and location where this session is accessed;
}

?>