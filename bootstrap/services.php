<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

foreach(AppManager::listAll() as $data){
	if(!empty($data["services"])){
		AppManager::migrateTable($data["rootname"]);
		foreach($data["services"] as $service){
			if($service == "") continue;
			
			// Preparing information 
			$app = new Application();
			$app->prepare($data["rootname"]);
			
			$appProp = (object)[
				"title"		=> $app->title,
				"desc"		=> $app->desc,
				"appname"	=> $app->appname,
				"path"		=> $app->path,
				"rootdir"	=> $app->rootdir,
				"uri"		=> $app->uri,
				"url"		=> $app->uri
			];
			
			$_f = function() use($service,$appProp){
				return include($appProp->path."/".$service);
			};
			
			if(!$_f()) throw new PuzzleError("Cannot start '".$data['name']."' services!", "Please recheck the existence of ".$data["dir"]."/".$service);
		}
	}
}
?>