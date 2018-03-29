<?php
defined("__POSEXEC") or diedefined("__POSEXEC") or die("No direct access allowed!");
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

/**
 * Serives Manager
 */
class Services{		
	/**
	 * Read all services that belongs to an app
	 * @param string $app Application root name
	 * @return array
	 */
	public static function listServiceOnApps($app){
		return(AppManager::listAll()[$app]["services"]);
	}
}

foreach(AppManager::listAll() as $data){
	if(!empty($data["services"])){
		/* Prepare the database before loading the service */
		foreach(glob($data["dir"] . "/*.table.php") as $table_abstract){
			$t = explode("/",rtrim($table_abstract,"/"));
			$table_name = str_replace(".table.php","",end($t));
			$table_structure = include($table_abstract);			
			Database::newStructure("app_" . $data["rootname"] . "_" . $table_name,$table_structure);
		}
		foreach($data["services"] as $service){
			if($service == "") continue;
			
			/* Preparing information */
			$appProp = new Application();
			$appProp->prepare($data["rootname"]);
			
			if(!include_once($data["dir"]."/".$service)){			
				throw new PuzzleError("Cannot start '".$data['name']."' services!", "Please recheck the existence of ".$data["dir"]."/".$service);
			}
			
			unset($appProp);
		}
	}	
}
?>