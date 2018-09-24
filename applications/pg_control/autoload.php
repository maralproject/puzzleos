<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

spl_autoload_register(function($c){
	if($c == "PageControl") 
		require_once(my_dir("class.php"));
});
?>