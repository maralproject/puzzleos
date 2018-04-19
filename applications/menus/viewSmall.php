<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.menus
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */
 
$location = $arguments[0];

if($location == "") throw new PuzzleError("Location cannot be empty!");

$menus = [];
foreach(AppManager::listAll() as $app){
	/* Donot show menu from restricted app */
	if(in_array($app["rootname"],ConfigurationMultidomain::$restricted_app) || !Accounts::authAccess($app["level"])) continue;
	foreach($app["menus"] as $menu){
		$exp = explode(">",$menu);
		$file = trim($exp[0]);
		$location_config = trim($exp[1]);
		if($location_config == $location){
			//Include this menu
			try{
				new Application($app["rootname"]);
			}catch(AppStartError $e){
				continue;
			}
			if(!include($app["dir"] . "/$file")){
				throw new PuzzleError("Cannot load menu for ". $app["title"]);
			}
		}
	}
}

?>