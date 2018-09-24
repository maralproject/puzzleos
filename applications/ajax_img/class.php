<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * This is part of upload_img_ajax app
 */
class ImageUploader{
	
	/**
	 * Print input file HTML Form
	 * @param string $key
	 * @param string $label
	 * @param string $bootstrap_style
	 * @param string $preview_selector
	 */
	public static function dumpForm($key, $label, $bootstrap_style = "default", $preview_selector = ""){
		if(isset($_SESSION["ImageUploader"][$key])){
			UserData::remove($_SESSION["ImageUploader"][$key]);
			unset($_SESSION["ImageUploader"][$key]);
		}
		include(my_dir("view/input.php"));
	}
	
	/**
	 * Get file name in the server
	 * @param string $key
	 * @return string
	 */
	public static function getFileName($key){
		return(UserData::getPath($_SESSION["ImageUploader"][$key]));
	}
	
	/**
	 * Get public URL address
	 * @param string $key
	 * @return string
	 */
	public static function getURL($key){
		return(UserData::getURL($_SESSION["ImageUploader"][$key],true));
	}
}
?>