<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

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

foreach(Database::readAll("app_menus_main", "WHERE `location`='?'", $loc) as $d){
	if(!PuzzleUser::isGroupAccess(PuzzleUserGroup::get($d["minUser"]))) continue;
	$activePg = __HTTP_URI == "" ? str_contains("/".ltrim($d["link"],"/"), "/".AppManager::getMainApp()->appname) : str_contains("/".__HTTP_URI,  "/".ltrim($d["link"],"/"));
	echo '<li class="nav-menu '.($activePg?'active':'').'">
		<a class="nav-link" href="'.__SITEURL.'/'.ltrim($d["link"],"/").'"><i class="fa fa-'.$d["fa"].'"></i><span>'.$d["name"].'</span></a>
	</li>';
}