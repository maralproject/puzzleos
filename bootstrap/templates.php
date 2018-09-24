<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * Template loader and manager
 */
class Template{
	
	private static $active = "";
	private static $url = "";
	private static $dir = "";
	private static $addOnHeader = "";
	private static $addOnBody = "";
	private static $Loaded = false;
	private static $SubTitle = NULL;
	private static $header_md5 = [];
	private static $body_md5 = [];
	private static $templateList = NULL;

	/** 
	 * Add SubTitle on the title page
	 * NOTE: Only executed before main view is loaded
	 * @param string $str Subtitle
	 */
	public static function setSubTitle($str){
		self::$SubTitle = $str;
	}
	
	/**
	 * List all Template
	 * @return array
	 */
	public static function listAll(){
		if(isset(self::$templateList)) return self::$templateList;
		$a = [];
		foreach(IO::list_directory("/templates") as $dir){
			if(!is_dir(IO::physical_path("/templates/$dir"))) continue;
			if($dir != "." && $dir != ".." && $dir != "system"){
				if(IO::exists("/templates/$dir/manifest.ini")){
					$manifest = parse_ini_file(IO::physical_path("/templates/$dir/manifest.ini"));
					$a[$dir] = [
						"name" 			=> $dir,
						"active" 		=> self::$active == $dir,
						"title" 		=> $manifest["title"],
						"controller"	=> $manifest["controller"],
						"dir"			=> IO::physical_path("/templates/$dir")
					];
				}
			}
		}		
		self::$templateList = $a;
		return $a;
	}
	
	/**
	 * NOTE: Only loaded from index.php
	 * Load the template as the UI
	 */
	public static function loadTemplate(){		
		if(self::$Loaded) return;
		flush();
				
		self::$active = POSConfigMultidomain::$default_template;
		self::$url = IO::publish(__ROOTDIR . "/templates/".self::$active);
		self::$dir = __ROOTDIR . "/templates/".self::$active;
		
		if(!IO::exists(self::$dir . "/manifest.ini"))
			throw new PuzzleError("Template " . self::$active . " not exists!","Please check the manifest!");
			
		$manifest = parse_ini_file(IO::physical_path(self::$dir . "/manifest.ini"));
		$tmpl = new PObject(array(
			"dumpHeaders" => function() { 
				echo self::$addOnHeader;
				require(__ROOTDIR . "/templates/system/pre_headers.php"); 
			},
			"printPrompt" => function() { 
				Prompt::printPrompt(); 
			}
		));
		
		$tmpl->app = &AppManager::$MainApp;
		$tmpl->http_code = &POSGlobal::$http_code;
		$tmpl->postBody = &self::$addOnBody;
		$tmpl->url = self::$url;
		$tmpl->path = self::$dir;
		$tmpl->copyright = POSConfigGlobal::$copyright;
		$tmpl->title = (self::$SubTitle === NULL ? ($tmpl->app->title) : self::$SubTitle);
		$tmpl->navigation = new Application; 
		$tmpl->navigation->run("menus");
		
		/**
		 * Minifiy the template On-The-Go
		 * Script taken from
		 * https://stackoverflow.com/questions/6225351/how-to-minify-php-page-html-output
		 */
		if(!defined("DISABLE_MINIFY")) ob_start(function($b){return preg_replace(['/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s'],['>','<','\\1'],$b);});
		
		if(!include_ext(self::$dir."/".$manifest["controller"],["tmpl"=>$tmpl]))
			throw new PuzzleError("Cannot load template!", "Please set the default template");
		
		self::$Loaded = true;
	}
	
	/**
	 * Append a header
	 * @param string $text
	 * @param bool $once Do not add the same header twice
	 */
	public static function addHeader($text,$once = false){
		if($once){
			if(isset(self::$header_md5[md5($text)])) return;
			self::$header_md5[md5($text)] = "yes";
		}
		self::$addOnHeader .= $text . PHP_EOL;
	}
	
	/**
	 * Append a body HTML
	 * @param string $text
	 * @param bool $once
	 */
	public static function appendBody($text,$once = false){
		if($once){
			if(isset(self::$body_md5[md5($text)])) return;
			self::$body_md5[md5($text)] = "yes";
		}
		self::$addOnBody .= $text . PHP_EOL;
	}
	
	/**
	 * Set default template by root name
	 * @param $name Template root name
	 * @return bool
	 */
	public static function setDefaultByName($name){
		POSConfigMultidomain::$default_template = $name;
		return POSConfigMultidomain::commit();
	}
}
?>