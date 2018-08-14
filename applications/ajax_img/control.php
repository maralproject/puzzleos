<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.upload_img_ajax
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.1
 */

if(!isset($_SESSION["ImageUploader"])) 
	$_SESSION["ImageUploader"] = [];

if(__getURI("app") == "upload_img_ajax"){
	if(__getURI("action") == "upload"){
		include("upload.php");
	}else{
		redirect("");
	}
}

?>