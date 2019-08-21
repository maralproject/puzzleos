<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

foreach (AppManager::listAll() as $data) {
	if (!empty($data["services"])) {

		$app = new Application();
		$app->prepare($data["rootname"]);
		AppManager::migrateTable($data["rootname"]);

		foreach ($data["services"] as $service) {
			if ($service == "") continue;
			if (!$app->loadContext($service)) {
				abort(500, "Internal Server Error", false);
				throw new PuzzleError("Cannot start '" . $data['name'] . "' services!", "Please recheck the existence of " . $data["dir"] . "/" . $service);
			}
		}
	}
}