<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

if($appProp->isMainApp){
	if(request("action") == "upload"){
		if(!isset($_SESSION["ImageUploader"])) $_SESSION["ImageUploader"] = [];
		return include("upload.php");
	}else{
		return false;
	}
}