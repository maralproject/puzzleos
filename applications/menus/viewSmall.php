<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */
 
$location = $arguments[0];
if($location == "") throw new PuzzleError("Location cannot be empty!");

foreach(AppManager::listAll() as $app){
	/* Donot show menu from restricted app */
	if(in_array($app["rootname"],POSConfigMultidomain::$restricted_app) || !PuzzleUser::isAccess($app["level"])) continue;
	foreach($app["menus"] as $menu){
		$file = trim(strtok($menu,">"));
		$location_config = trim(strtok(""));
		if($location_config == $location){
			//Include this menu
			try{
				(iApplication::run($app["rootname"]))->loadContext($file);
			}catch(AppStartError $e){
				continue;
			}
		}
	}
}
