<?php
defined("__POSEXEC") or diedefined("__POSEXEC") or die("No direct access allowed!");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */

/**
 * This class only features for IO easiness
 * You can also use the default php file operation (fopen, fread, fseek...)
 */ 
class IO{	
	/**
	 * Output a file to browser then exit()
	 * @param string $filename Just use /path/to-path/file
	 */
	public static function streamFile($filename,$force_download = false, $custom_filename = NULL){
		$filename = self::physical_path($filename);		
		if(headers_sent()) throw new PuzzleError("Header is already sent! Cannot output file to browser!");
		if(!file_exists($filename)) throw new IOError("Filename ".str_replace(__ROOTDIR, "",$filename)." not found!");
		while (ob_get_level())	ob_get_clean();
		
		if(!$force_download){
			header('Content-Disposition: inline'. ($custom_filename !== NULL ? "; filename=\"$custom_filename\"" : ""));
		}else{
			header('Content-Disposition: attachment'. ($custom_filename !== NULL ? "; filename=\"$custom_filename\"" : ""));
		}
		
		$v = new FileStream($filename);
		$v->start();
		
		exit();
	}

	/**
	 * Check existance of file or directory
	 * @param string $path Just use /path/to-path/file
	 * @return bool
	 */
	public static function exists($path){
		return(file_exists(IO::physical_path($path)));
	}
	
	/**
	 * Get physical path of virtual path
	 * @param string $path Just use /path/to-path/file
	 * @return string
	 */
	public static function physical_path($path){
		$path = str_replace("\\","/",$path);
		if(!file_exists($path) || strpos($path,__ROOTDIR) !== false){
			//Assume that this directory is inside PuzzleOS env
			$path = str_replace(__ROOTDIR,"",$path);
			$path = __ROOTDIR . "/" . ltrim($path,"/");			
		}
		return($path);
	}
	
	/**
	 * Read the whole contents of file
	 * @param string $path Just use /path/to-path/file
	 * @return string
	 */
	public static function read($path){
		if(!IO::exists($path)) return;
		return(file_get_contents(IO::physical_path($path)));
	}
	
	/**
	 * Write new file using file_put_contents()
	 * @param string $path Just use /path/to-path/file
	 * @param string $content
	 */
	public static function write($path, $content){
		file_put_contents(IO::physical_path($path),$content);
	}
	
	/**
	 * Remove directory recursively
	 * @param string $dir Just use /path/to-path/file
	 */
	public static function remove_r($dir) { 
		$dir = IO::physical_path($dir);
		if (is_dir($dir)) { 			
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (is_dir($dir."/".$object))
						IO::remove_r($dir."/".$object);
					else
					if(!unlink($dir."/".$object)) return false;
				} 
			}
			if(!rmdir($dir)) return false; 
		}
		return true;
	}

	/**
	 * Copy directory recursively
	 * @param string $src Just use /path/to-path/file
	 * @param string $dst Just use /path/to-path/file
	 */
	public static function copy_r($src,$dst) { 
		$src = IO::physical_path($src);
		$dst = IO::physical_path($dst);
		$dir = opendir($src); 
		@mkdir($dst); 
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
					IO::copy_r($src . '/' . $file,$dst . '/' . $file); 
				} else { 
					copy($src . '/' . $file,$dst . '/' . $file); 
				} 
			} 
		} 
		closedir($dir); 
	} 
	
	/**
	 * Move directory recursively
	 * @param string $src Just use /path/to-path/file
	 * @param string $dst Just use /path/to-path/file
	 */
	public static function move_r($src,$dst){
		IO::copy_r($src,$dst);
		IO::remove_r($src);
	}
	
	/**
	 * Move directory
	 * @param string $old Just use /path/to-path/file
	 * @param string $new Just use /path/to-path/file
	 */
	public static function move($old, $new){
		rename($old,$new);
	}
	
	/**
	 * Get mime type of a file
	 * @param string $path Just use /path/to-path/file
	 * @return string
	 */
	public static function get_mime($path){
		return(mime_content_type(IO::physical_path($path)));
	}
	
	/**
	 * Get directory list
	 * @param string $path Just use /path/to-path/file
	 * @return array
	 */
	public static function list_directory($path){
		return(scandir(IO::physical_path($path)));
	}
}

/**
 * Originally VideoStream, renamed to FileStream
 *
 * @author Rana
 * @link http://codesamplez.com/programming/php-html5-video-streaming-tutorial
 */
class FileStream{
	private $path = "";
	private $stream = "";
	private $buffer = 102400;
	private $start = - 1;
	private $end = - 1;
	private $size = 0;

	function __construct($filePath){
		$this->path = $filePath;
	}

	/**
	 * Open stream
	 */
	private function open(){
		if(!($this->stream = fopen($this->path, 'rb')))
			throw new PuzzleError('Could not open stream for reading');
	}

	/**
	 * Set proper header to serve the video content
	 */
	private function setHeader(){
		ob_get_clean();
		header("Content-Type: " . mime_content_type($this->path));
		header("Cache-Control: max-age=2592000, public");
		header("Expires: " . gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT');
		header("Last-Modified: " . gmdate('D, d M Y H:i:s', @filemtime($this->path)) . ' GMT');
		$this->start = 0;
		$this->size = filesize($this->path);
		$this->end = $this->size - 1;
		header("Accept-Ranges: 0-" . $this->end);
		if (isset($_SERVER['HTTP_RANGE'])) {
			$c_start = $this->start;
			$c_end = $this->end;
			list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
			if (strpos($range, ',') !== false) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header("Content-Range: bytes $this->start-$this->end/$this->size");
				exit;
			}

			if ($range == '-') {
				$c_start = $this->size - substr($range, 1);
			}
			else {
				$range = explode('-', $range);
				$c_start = $range[0];
				$c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
			}

			$c_end = ($c_end > $this->end) ? $this->end : $c_end;
			if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header("Content-Range: bytes $this->start-$this->end/$this->size");
				exit;
			}

			$this->start = $c_start;
			$this->end = $c_end;
			$length = $this->end - $this->start + 1;
			fseek($this->stream, $this->start);
			header('HTTP/1.1 206 Partial Content');
			header("Content-Length: " . $length);
			header("Content-Range: bytes $this->start-$this->end/" . $this->size);
		}else{
			header("Content-Length: " . $this->size);
		}
	}

	/**
	 * close curretly opened stream
	 */
	private function end(){
		fclose($this->stream);
		exit;
	}

	/**
	 * perform the streaming of calculated range
	 */
	private function stream(){
		$i = $this->start;
		set_time_limit(0);
		while (!feof($this->stream) && $i <= $this->end) {
			$bytesToRead = $this->buffer;
			if (($i + $bytesToRead) > $this->end) {
				$bytesToRead = $this->end - $i + 1;
			}

			$data = fread($this->stream, $bytesToRead);
			echo $data;
			flush();
			$i+= $bytesToRead;
		}
	}

	/**
	 * Start streaming video content
	 */
	function start(){
		$this->open();
		$this->setHeader();
		$this->stream();
		$this->end();
	}
}

?>