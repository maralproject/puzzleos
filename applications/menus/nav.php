<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.menus
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.1
 */
 
//$loc = MENU_DEFAULT_POSITION_LEFT;
switch($location){
	case "bottom":
		$loc = MENU_DEFAULT_POSITION_BOTTOM;
		break;
	case "top":
		$loc = MENU_DEFAULT_POSITION_TOP;
		break;
	case "right":
		$loc = MENU_DEFAULT_POSITION_RIGHT;
		break;
	case "left":
	default:
		$loc = MENU_DEFAULT_POSITION_LEFT;
}
ob_start();
$buf = "";
$data = Database::readAll("app_menus_main", "WHERE `location`='?'", $loc);
for($i=0;$i < $data->num;$i++){
	if(!Accounts::authAccessAdvanced($data->data[$i]["minUser"])) continue;
	$activePg = false;
	if(__HTTP_URI == ""){
		$activePg = (strpos("/".ltrim($data->data[$i]["link"],"/"), "/".AppManager::$MainApp->appname) !== false);		
	}else{
		$activePg = (strpos( "/".__HTTP_URI, "/".ltrim($data->data[$i]["link"],"/")) !== false);
	}
	$buf .= '<a href="'.__SITEURL.'/'.ltrim($data->data[$i]["link"],"/").'"><li '.($activePg?'class="active"':'').'><i class="fa fa-'.$data->data[$i]["fa"].'"></i><span>'.$data->data[$i]["name"].'</span></li></a>';
}
echo $buf;
if($buf == "") 
	ob_clean();
?>