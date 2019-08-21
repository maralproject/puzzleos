<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

spl_autoload_register(function($c){
	if($c == "ImageUploader") 
		require_once(my_dir("class.php"));
});