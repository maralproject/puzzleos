<?php
defined("__POSEXEC") or die("No direct access allowed!");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.1
 */

foreach(AppManager::listAll() as $data){
	if(!empty($data["services"])){
		AppManager::migrateTable($data["rootname"]);
		foreach($data["services"] as $service){
			if($service == "") continue;
			
			// Preparing information 
			$appProp = new Application();
			$appProp->prepare($data["rootname"]);
			
			if(!include($data["dir"]."/".$service)){			
				throw new PuzzleError("Cannot start '".$data['name']."' services!", "Please recheck the existence of ".$data["dir"]."/".$service);
			}
			
			unset($appProp);
		}
	}
}
?>