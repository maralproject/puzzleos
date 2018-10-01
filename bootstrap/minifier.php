<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * Cache and minify on-demand css, js, or any file instantly
 */
class Minifier
{
	/**
	 * Same with ob_start()
	 */
	public static function mark()
	{
		ob_start();
	}

	/**
	 * Start caching file and get the cached URL file.
	 * @param string $file_ext Only "css", or "js"
	 * @return string
	 */
	public static function start($file_ext, $return = false)
	{
		$data = ob_get_clean();
		$hash = substr(md5($data), 0, 10);
		if (!$return) {
			$path = "/" . __PUBLICDIR . "/cache/" . $hash . '.' . $file_ext;
			if (file_exists(__ROOTDIR . $path)) return ("/cache/" . $hash . '.' . $file_ext);
		}
		$data = str_replace(__SITEURL, "#_SITEURL#", $data);
		if ($file_ext == "") return;
		switch ($file_ext) {
			case "js":
				$data = str_replace('type="text/javascript"', "", $data);
				$data = str_replace("type='text/javascript'", "", $data);
				$data = preg_replace("/\s*<(\h|)script(\h|)>\s*/", "", $data);
				$data = preg_replace("/\s*<\/(\h|)script(\h|)>\s*/", "", $data);
			/* remove comments */
				$data = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $data);
			/* remove tabs, spaces, newlines, etc. */
				$data = str_replace(array("\r\n", "\r", "\t", "\n", '  ', '    ', '     '), '', $data);
				$data = str_replace(array(' = ', '= ', ' ='), '=', $data);
			/* remove other spaces before/after ) */
				$data = preg_replace(array('(( )+\))', '(\)( )+)'), ')', $data);
				break;
			case "css":
				$data = str_replace('type="text/css"', "", $data);
				$data = str_replace("type='text/css'", "", $data);
				$data = preg_replace("/\s*<(\h|)style(\h|)>\s*/", "", $data);
				$data = preg_replace("/\s*<\/(\h|)style(\h|)>\s*/", "", $data);
			/* remove comments */
				$data = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $data);
			/* remove tabs, spaces, newlines, etc. */
				$data = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '     '), '', $data);
				$data = str_replace(array(' : ', ': ', ' :'), ':', $data);
			/* remove other spaces before/after ; */
				$data = preg_replace(array('(( )+{)', '({( )+)'), '{', $data);
				$data = preg_replace(array('(( )+})', '(}( )+)', '(;( )*})'), '}', $data);
				$data = preg_replace(array('(;( )+)', '(( )+;)'), ';', $data);
				break;
			default:
				throw new PuzzleError("Only css and js are available!");
		}
		$data = str_replace("#_SITEURL#", __SITEURL, $data);
		if (!$return) {
			if (!file_exists(__ROOTDIR . $path)) IO::write($path, $data);
			return ("/cache/" . $hash . '.' . $file_ext);
		} else {
			return $data;
		}
	}

	/**
	 * Start minifiying JS and get instant script.
	 * @return string
	 */
	public static function outJSMin()
	{
		//ob_flush();return; //Use this to disable caching
		$file = Minifier::start("js", true);
		return ('<script type="text/javascript">' . $file . '</script>');
	}

	/**
	 * Start caching file and get instant script to include JS file.
	 * @return string
	 */
	public static function getJSFile()
	{
		//ob_flush();return; //Use this to disable caching
		$file = __SITEURL . Minifier::start("js");
		return ('<script type="text/javascript" src="' . $file . '"></script>');
	}

	/**
	 * Start minifiying CSS file and get instant style.
	 * @return string
	 */
	public static function outCSSMin()
	{
		$file = Minifier::start("css", true);
		return ('<style type="text/css">' . $file . '</style>');
	}

	/**
	 * Start caching file and get instant script to include CSS file.
	 * @return string
	 */
	public static function getCSSFile()
	{
		$file = __SITEURL . Minifier::start("css");
		return ('<link rel="stylesheet" type="text/css" href="' . $file . '"/>');
	}
}

?>