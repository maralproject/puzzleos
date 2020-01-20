<?php

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2020 PT SIMUR INDONESIA
 */

if (request("action") == "manage") {
	$app = request(2);
	if ($app == "") redirect("admin#apps");
	try {
		$GLOBALS["app"]["managing"] = iApplication::run($app);
		if (!include($GLOBALS["app"]["managing"]->path . "/panel.admin.php")) {
			redirect("admin#apps");
		}
	} catch (AppStartError $e) {
		redirect("admin#apps");
	}
} else
	include("view/main.php");
