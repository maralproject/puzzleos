<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

/**
 * Create simple ImageUploader form with AJAX
 */
class ImageUploader
{

	/**
	 * Print input file HTML Form
	 * @param string $key
	 * @param string $label
	 * @param string $bootstrap_style
	 * @param string $preview_selector
	 * @param bool $shrink
	 */
	public static function dumpForm($key, $label, $bootstrap_style = "secondary", $preview_selector = "", bool $shrink = true)
	{
		if (isset($_SESSION["ImageUploader"][$key])) {
			UserData::remove($_SESSION["ImageUploader"][$key]);
			unset($_SESSION["ImageUploader"][$key]);
		}
		include(my_dir("view/input.php"));
	}

	public static function dumpPreviewTemplate($imgurl = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=")
	{
		include(my_dir("view/preview.php"));
	}

	/**
	 * Get file name in the server
	 * @param string $key
	 * @return string
	 */
	public static function getFileName($key)
	{
		return (UserData::getPath($_SESSION["ImageUploader"][$key]));
	}

	/**
	 * Get public URL address
	 * @param string $key
	 * @return string
	 */
	public static function getURL($key)
	{
		return (UserData::getURL($_SESSION["ImageUploader"][$key], true));
	}

	/**
	 * Compress PNG, JPEG image
	 * @return string Raw data of the compressed image.
	 */
	public static function compressImage(string $source, int $reducerpoint = 700)
	{
		$source = IO::physical_path($source);

		$type = exif_imagetype($source);
		switch ($type) {
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg($source);
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($source);
				break;
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($source);
				break;
			case IMAGETYPE_BMP:
				$image = imagecreatefrombmp($source);
				break;
			default:
				throw new Exception("Image not supported");
		}

		$imgwidth = imagesx($image);
		$imgheight = imagesy($image);

		if (($imgwidth > $reducerpoint) || ($imgheight > $reducerpoint)) {
			if ($imgwidth > $imgheight) {
				$scale = $reducerpoint / $imgwidth;
			} else {
				$scale = $reducerpoint / $imgheight;
			}
		} else {
			$scale = 1;
		}

		$new_w = $imgwidth * $scale;
		$new_h = $imgheight * $scale;
		$targetCanvas = imagecreatetruecolor($new_w, $new_h);
		imagecolortransparent(
			$targetCanvas,
			imagecolorallocatealpha($targetCanvas, 0, 0, 0, 127)
		);
		imagealphablending($targetCanvas, false);
		imagesavealpha($targetCanvas, true);
		imagefill($targetCanvas, 0, 0, imagecolorallocatealpha($targetCanvas, 0, 0, 0, 127));

		imagecopyresampled($targetCanvas, $image, 0, 0, 0, 0, $new_w, $new_h, $imgwidth, $imgheight);
		imagetruecolortopalette($targetCanvas, false, 255);
		imagedestroy($image);

		ob_start();
		if ($type == IMAGETYPE_PNG) {
			imagepng($targetCanvas, null, 9, PNG_ALL_FILTERS);
		} else if ($type == IMAGETYPE_JPEG) {
			imagejpeg($targetCanvas, null, 50);
		}
		$rawimg = ob_get_contents();
		ob_end_clean();
		imagedestroy($targetCanvas);

		return $rawimg;
	}
}
