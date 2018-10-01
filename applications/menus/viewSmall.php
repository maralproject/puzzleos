<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */
 
$location = $arguments[0];
if($location == "") throw new PuzzleError("Location cannot be empty!");

foreach(AppManager::listAll() as $app){
	/* Donot show menu from restricted app */
	if(in_array($app["rootname"],POSConfigMultidomain::$restricted_app) || !Accounts::authAccess($app["level"])) continue;
	foreach($app["menus"] as $menu){
		$file = trim(strtok($menu,">"));
		$location_config = trim(strtok(""));
		if($location_config == $location){
			//Include this menu
			try{
				(new Application($app["rootname"]))->loadContext($file);
			}catch(AppStartError $e){
				continue;
			}
		}
	}
}

?>