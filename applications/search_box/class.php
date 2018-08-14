<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.search_box
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.1
 */

/**
 * This is part of search_box application
 */
class SearchBox{
	private $data = [];
	private $dataAsArray = true;
	private $dynamic = true;
	private $inputName = "s";
	private $prefix = "SE_";
	private $clearURL = "/";
	private $submitable = true;
	private $customWidth = "400px";
	private $hideAll = false;
	private $customHint = false;
	private $hintText = "";
	
	/**
	 * @param string $prefix
	 */
	public function __construct($prefix){		
		$this->prefix = $prefix;
		$this->identifier = [];
		$this->data = [];
	}
	
	/**
	 * Set custom width
	 * @param string $width
	 */
	public function setWidth($width){
		$this->customWidth = $width;
	}
	
	/**
	 * Add data to the list
	 * @param array $data
	 * @param string $identifier
	 * @return bool
	 */
	public function putData($data,$identifier){
		//Add data to the list
		if($this->dataAsArray){
			if(empty($data)) return false;
			if(!is_array($data)) throw new PuzzleError("The SearchBox data must be in array!");	
		}
		//$this->data[$identifier] = $data;
		$this->data[str_replace(" ","_",$identifier)] = $data;
		return true;
	}
	
	/**
	 * Set custom hint in the search field
	 * @param string $text
	 */
	public function setCustomHint($text){
		$this->customHint = true;
		$this->hintText = $text;
	}	
	
	/**
	 * Set data mode as array
	 * @param bool $bool
	 */
	public function setDataAsArray($bool){
		if(!is_bool($bool)) return;
		$this->dataAsArray = $bool;
		$this->data = [];
	}
	
	/**
	 * Set to hide results when search box is empty
	 * @param bool $bool
	 */
	public function hideNow($bool){
		if(!is_bool($bool)) return;
		$this->hideAll = $bool;
	}
	
	/**
	 * Make the search bar can be submitted by browser as form
	 * @default true
	 * @param bool $bool
	 */
	public function setSubmitable($bool){
		if(!is_bool($bool)) return;
		$this->submitable = $bool;
	}
	
	/**
	 * Set the default URL of the search bar
	 * e.g. /someapp?s=keyword, it means the default URL is /someapp
	 * 
	 * @param string $url
	 */
	public function setClearURL($url){
		$this->clearURL = $url;
	}
	
	/**
	 * Set the search box and search item dynamically searched in browser
	 * @default true
	 * @param bool $bool
	 */
	public function setDynamic($bool){
		if(!is_bool($bool)) return;
		$this->dynamic = $bool;		
	}
	
	/**
	 * Set input name for the search box
	 * @param string $dom
	 */
	public function setInputName($dom){
		$this->inputName = $dom;
	}
	
	/**
	 * Get the DOM class for each searchable element
	 * @return string
	 */
	public function getDomClass($identifier){
		return($this->prefix . " ". $this->prefix . "_" . str_replace(" ","_",$identifier));
	}
	
	/**
	 * Print the searchbox
	 */
	public function dumpSearchBox($withIcon = true){
		if(!isset($_GET[$this->inputName]))
			$_GET[$this->inputName] = "";
		include("search_box_html.php");
	}
}
?>