<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

defined("IO_STREAM_BUFFER") or define("IO_STREAM_BUFFER", 102400);

/**
 * This class only features IO easiness like remove_r(), and copy_r().
 * You can also use the default php IO operation (fopen, fread, fseek...).
 */
class IO
{
	/**
	 * Stream file to browser then exit.
	 * @param string $filename Just use /path/to-path/file
	 */
	public static function streamFile($filename, $force_download = false, $custom_filename = null)
	{
		$filename = self::physical_path($filename);
		if (headers_sent()) throw new PuzzleError("Header is already sent! Cannot output file to browser!");
		if (!file_exists($filename)) throw new IOError("Filename " . str_replace(__ROOTDIR, "", $filename) . " not found!");
		while (ob_get_level()) ob_get_clean();

		if (!$force_download) {
			header('Content-Disposition: inline' . ($custom_filename !== null ? "; filename=\"$custom_filename\"" : ""));
		} else {
			header('Content-Disposition: attachment' . ($custom_filename !== null ? "; filename=\"$custom_filename\"" : ""));
		}

		$v = new FileStream($filename);
		$v->start();

		exit;
	}

	/**
	 * Publish private file/directory to public
	 * @param string $filename Just use /path/to-path/file
	 * @return string The public file path
	 */
	public static function publish($filename)
	{
		$filename = self::physical_path($filename);
		//Do not copy from destination which is it's parent
		if (starts_with(self::physical_path("/" . __PUBLICDIR . "/res"), $filename)) return false;
		set_time_limit(0);
		if (is_dir($filename)) {
			$hash = substr(md5($filename), 0, 10);
			if (!file_exists(__ROOTDIR . "/" . __PUBLICDIR . "/res/$hash")) {
				self::copy_r($filename, __ROOTDIR . "/" . __PUBLICDIR . "/res/$hash");
				self::remove_r_ext("/" . __PUBLICDIR . "/res/$hash", "php");
				self::remove_r_ext("/" . __PUBLICDIR . "/res/$hash", "ini");
			}
			set_time_limit(TIME_LIMIT);
			return "/res/$hash";
		} else {
			$name = end(explode("/", $filename));
			$ext = end(explode(".", $name));
			$name = rtrim(str_replace($ext, "", $name), ".");
			if ($ext == $name) $ext = "tmp";
			$hash = substr(md5_file($filename), 0, 10);
			if (!file_exists(__ROOTDIR . "/" . __PUBLICDIR . "/res/$name.1$hash.$ext"))
				@copy($filename, __ROOTDIR . "/" . __PUBLICDIR . "/res/$name.$hash.$ext");
			set_time_limit(TIME_LIMIT);
			return "/res/$name.$hash.$ext";
		}
	}

	/**
	 * Check existance of file or directory
	 * @param string $path Just use /path/to-path/file
	 * @return bool
	 */
	public static function exists($path)
	{
		return (file_exists(IO::physical_path($path)));
	}

	/**
	 * Get physical path of virtual path
	 * @param string $path Just use /path/to-path/file
	 * @return string
	 */
	public static function physical_path($path)
	{
		$path = btfslash($path);
		if (!file_exists($path) || str_contains($path, __ROOTDIR)) {
			//Assume that this directory is inside PuzzleOS env
			$path = str_replace(__ROOTDIR, "", $path);
			$path = __ROOTDIR . "/" . ltrim($path, "/");
		}
		return ($path);
	}

	/**
	 * Read the whole contents of file
	 * @param string $path Just use /path/to-path/file
	 * @return string
	 */
	public static function read($path)
	{
		if (!IO::exists($path)) return;
		return (file_get_contents(IO::physical_path($path)));
	}

	/**
	 * Write new file using file_put_contents()
	 * @param string $path Just use /path/to-path/file
	 * @param string $content
	 */
	public static function write($path, $content)
	{
		file_put_contents(IO::physical_path($path), $content);
	}

	/**
	 * Remove directory recursively
	 * @param string $dir Just use /path/to-path/file
	 */
	public static function remove_r($dir)
	{
		$dir = IO::physical_path($dir);
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (is_dir($dir . "/" . $object))
						IO::remove_r($dir . "/" . $object);
					else
						if (!unlink($dir . "/" . $object)) return false;
				}
			}
			if (!rmdir($dir)) return false;
		}
		return true;
	}

	/**
	 * Remove all files in directory with specific extension
	 * recursively.
	 *
	 * @param string $dir Just use /path/to-path/file
	 * @param string $ext
	 */
	public static function remove_r_ext($dir, $ext)
	{
		$dir = IO::physical_path($dir);
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (is_dir($dir . "/" . $object)) {
						IO::remove_r_ext($dir . "/" . $object, $ext);
					} else {
						if (end(explode(".", $object)) == $ext) {
							if (!unlink($dir . "/" . $object)) return false;
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * Copy directory recursively
	 * @param string $src Just use /path/to-path/file
	 * @param string $dst Just use /path/to-path/file
	 */
	public static function copy_r($src, $dst)
	{
		$src = IO::physical_path($src);
		$dst = IO::physical_path($dst);
		$dir = opendir($src);
		@mkdir($dst);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src . '/' . $file)) {
					self::copy_r($src . '/' . $file, $dst . '/' . $file);
				} else {
					copy($src . '/' . $file, $dst . '/' . $file);
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
	public static function move_r($src, $dst)
	{
		self::copy_r($src, $dst);
		self::remove_r($src);
	}

	/**
	 * Move directory
	 * @param string $old Just use /path/to-path/file
	 * @param string $new Just use /path/to-path/file
	 */
	public static function move($old, $new)
	{
		rename($old, $new);
	}

	/**
	 * Get mime type of a file
	 * @param string $path Just use /path/to-path/file
	 * @return string
	 */
	public static function get_mime($path)
	{
		return (mime_content_type(IO::physical_path($path)));
	}

	/**
	 * Get directory list
	 * @param string $path Just use /path/to-path/file
	 * @return array
	 */
	public static function list_directory($path)
	{
		return (scandir(IO::physical_path($path)));
	}
}

/**
 * Originally VideoStream, renamed to FileStream
 *
 * @author Rana
 * @link http://codesamplez.com/programming/php-html5-video-streaming-tutorial
 */
class FileStream
{
	private $path = "";
	private $stream = "";
	private $buffer = IO_STREAM_BUFFER;
	private $start = -1;
	private $end = -1;
	private $size = 0;

	function __construct($filePath)
	{
		$this->path = $filePath;
	}

	/**
	 * Open stream
	 */
	private function open()
	{
		if (!($this->stream = fopen($this->path, 'rb')))
			throw new PuzzleError('Could not open stream for reading');
	}

	/**
	 * Set proper header to serve the video content
	 */
	private function setHeader()
	{
		ob_get_clean();
		header("Content-Type: " . mime_content_type($this->path));
		header('Pragma: public');
		header("Cache-Control: max-age=2628000, public");
		header("Expires: " . gmdate(DATE_RFC1123, time() + 2628000) . ' GMT');
		header("Last-Modified: " . gmdate(DATE_RFC1123, @filemtime($this->path)) . ' GMT');
		if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == gmdate(DATE_RFC1123, @filemtime($this->path)) . ' GMT') {
			header('HTTP/1.1 304 Not Modified');
			die();
		}
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
			} else {
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
		} else {
			header("Content-Length: " . $this->size);
		}
	}

	/**
	 * close curretly opened stream
	 */
	private function end()
	{
		fclose($this->stream);
		exit;
	}

	/**
	 * perform the streaming of calculated range
	 */
	private function stream()
	{
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
			$i += $bytesToRead;
		}
	}

	/**
	 * Start streaming content
	 */
	function start()
	{
		$this->open();
		$this->setHeader();
		$this->stream();
		$this->end();
	}
}
