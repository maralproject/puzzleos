<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.upload_img_ajax
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.1.3
 */

$l = new Language;
$l->app = "upload_img_ajax";

$filetype = "png";
function compress($source, $reducerpoint = 600) {
	$source = IO::physical_path($source);
	$info = getimagesize($source);	
	list($imgw, $imgh) = getimagesize($source);
	$img;
	$new_image;
	$imgFile;
    if ($info['mime'] == 'image/jpeg') $img = imagecreatefromjpeg($source);
    elseif ($info['mime'] == 'image/png') $img = imagecreatefrompng($source);
    elseif ($info['mime'] == 'image/pjpeg') $img = imagecreatefromjpeg($source);
	if(($imgw > $reducerpoint) || ($imgh > $reducerpoint)){
		if($imgw > $imgh){
			//Landscape
			$ratio = $reducerpoint / $imgw;
			$height = $imgh * $ratio; 
			$new_image = imagecreatetruecolor($reducerpoint, $height);
			imagealphablending($new_image,false);
			imagesavealpha($new_image,true);
			$transparent = imagecolorallocatealpha($new_image,255,255,255,127);
			imagefilledrectangle($new_image,0,0,$imgw,$imgh,$transparent);
			imagecopyresampled($new_image, $img, 0, 0, 0, 0, $reducerpoint, $height, $imgw, $imgh);
		}else{
			//portrait or square
			$ratio = $reducerpoint / $imgh;
			$width = $imgw * $ratio; 
			$new_image = imagecreatetruecolor($width, $reducerpoint);
			imagealphablending($new_image,false);
			imagesavealpha($new_image,true);
			$transparent = imagecolorallocatealpha($new_image,255,255,255,127);
			imagefilledrectangle($new_image,0,0,$imgh,$imgw,$transparent);
			imagecopyresampled($new_image, $img, 0, 0, 0, 0, $width, $reducerpoint, $imgw, $imgh);
		}		
		ob_start();
		imagepng($new_image);	
		$imgFile = ob_get_contents();
		ob_end_clean();
		imagedestroy($img);
		imagedestroy($new_image);
	}else{
		//File already small. No compression needed
		imagedestroy($img);
		return(file_get_contents($source));
	}	
    return $imgFile;
}

if(isset($_FILES["file"]) && $_FILES["file"]["error"]== UPLOAD_ERR_OK){	
	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
		die();
	}	
	
	if ($_FILES["file"]["size"] > 5242880) {
		echo($_POST["prev"]);
		Prompt::postErrorInScript($l->get("TOO_BIG"));
		die();
	}
	
	$fileext = ""; $img;
	
	switch(strtolower($_FILES['file']['type'])){
		case 'image/png': 
			$fileext = "png";
		case 'image/jpeg':
			$fileext = "jpg";
		case 'image/pjpeg':
			$fileext = "jpg";
			break;
		case 'image/x-icon':
			echo($_POST["prev"]);
			die(Prompt::postWarnInScript($l->get("ICO_NOT_SUPPORT")));
			break;
		default:
			echo($_POST["prev"]);
			die(Prompt::postErrorInScript($l->get("NOT_VALID")));
	}
	
	$key = $_POST["key"];
	$id = $key . '.' . session_id() . '.' . $fileext;
	$_SESSION["ImageUploader"][$key] = $id;
	
	if(UserData::move_uploaded($id,"file")){
		$img = compress(UserData::getPath($id));
		UserData::remove($id);
		UserData::store($id,$img,$filetype);
		die('<div style="margin-top:10px;background:url(\''. __SITEURL . UserData::getURL($id,true) .'\') center center / contain no-repeat;width:inherit;height:inherit;"></div>');
	}else{
		echo($_POST["prev"]);
		die(Prompt::postErrorInScript($l->get("ERROR_UPLOAD")));
	}
	
	echo($_POST["prev"]);
	die(Prompt::postErrorInScript($l->get("ERROR_UNKNOWN")));
}

redirect("");

?>