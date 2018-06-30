<?php
defined("__POSEXEC") or die("No direct access allowed!");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.0
 */

/**
 * Cache on-demand css, js, or any file instantly
 */ 
class FastCache{
	/**
	 * Same with ob_start()
	 */
	public static function mark(){
		ob_start();
	}
	
	/**
	 * Start caching file and get the cached URL file.
	 * @param string $file_ext Only "css", or "js"
	 * @return string
	 */
	public static function start($file_ext, $return = false){
		$data = ob_get_clean();
		$data = str_replace(__SITEURL , "#_SITEURL#", $data);
		if($file_ext == "") return;
		switch($file_ext){
		case "js":
			$data = str_replace('type="text/javascript"',"",$data);
			$data = str_replace("type='text/javascript'","",$data);
			$data = preg_replace("/\s*<(\h|)script(\h|)>\s*/","",$data);
			$data = preg_replace("/\s*<\/(\h|)script(\h|)>\s*/","",$data);
			/* remove comments */
			$data = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $data);
			/* remove tabs, spaces, newlines, etc. */
			$data = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $data);
			$data = str_replace(array(' = ','= ',' ='), '=', $data);
			/* remove other spaces before/after ) */
			$data = preg_replace(array('(( )+\))','(\)( )+)'), ')', $data);
			break;
		case "css":
			$data = str_replace('type="text/css"',"",$data);
			$data = str_replace("type='text/css'","",$data);
			$data = preg_replace("/\s*<(\h|)style(\h|)>\s*/","",$data);
			$data = preg_replace("/\s*<\/(\h|)style(\h|)>\s*/","",$data);
			/* remove comments */
			$data = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $data);
			/* remove tabs, spaces, newlines, etc. */
			$data = str_replace(array("\r\n","\r","\n","\t",'  ','    ','     '), '', $data);
			$data = str_replace(array(' : ',': ',' :'), ':', $data);
			/* remove other spaces before/after ; */
			$data = preg_replace(array('(( )+{)','({( )+)'), '{', $data);
			$data = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $data);
			$data = preg_replace(array('(;( )+)','(( )+;)'), ';', $data);
			break;
		default:
			throw new PuzzleError("Only css and js are available!");
		}		
		$data = str_replace("#_SITEURL#", __SITEURL , $data);
		if(!$return){
			$hash = substr(hash("md5",$data),0,10);
			$path = "/public/cache/" . $hash . '.' . $file_ext;
			if(!IO::exists($path)){						
				IO::write($path,$data);	
			}
			return("/cache/" . $hash . '.' . $file_ext);
		}else{
			return $data;
		}
	}
	
	/**
	 * Start minifiying JS and get instant script.
	 * @return string
	 */
	public static function outJSMin(){
		//ob_flush();return; //Use this to disable caching
		$file = FastCache::start("js",true);
		return('<script type="text/javascript">'.$file.'</script>');
	}
	
	/**
	 * Start caching file and get instant script to include JS file.
	 * @return string
	 */
	public static function getJSFile(){
		//ob_flush();return; //Use this to disable caching
		$file = __SITEURL . FastCache::start("js");
		return('<script type="text/javascript" src="'.$file.'"></script>');
	}
	
	/**
	 * Start minifiying CSS file and get instant style.
	 * @return string
	 */
	public static function outCSSMin(){
		$file = FastCache::start("css",true);
		return('<style type="text/css">'.$file.'</style>');
	}
	
	/**
	 * Start caching file and get instant script to include CSS file.
	 * @return string
	 */
	public static function getCSSFile(){
		$file = __SITEURL . FastCache::start("css");
		return('<link rel="stylesheet" type="text/css" href="'.$file.'"/>');
	}
}

?>