<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.admin
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.1.1
 */

if(__getURI("action") == "manage"){
	$app = __getURI(2);
	if($app == "") redirect("admin#apps");
	$GLOBALS["app"]["managing"] = new Application;
	if($GLOBALS["app"]["managing"]->run($app)){
		if(!include($GLOBALS["app"]["managing"]->path."/panel.admin.php")){
			redirect("admin#apps");
		}
	}else{
		redirect("admin#apps");
	}
}else
	include("view/main.php");
?>