<?php

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

/**
 * Push message or notification to user
 */
class Prompt
{
	/**
	 * This var contain list of propmt for temporary
	 * @var string
	 */
	public static $prompt = "";
	const closeBtn = "<button onclick=\"$(this).parent().remove();\" type=\"button\" class=\"close\">×</button>";

	private static function sendNextPage($message)
	{
		$_SESSION["__POSPROMPT"] .= $message;
	}

	/**
	 * Post error type message
	 * @param string $message
	 * @param bool $postInTheNextPage
	 */
	public static function postError($message, $postInTheNextPage = false)
	{
		$message = '<div auto_dismiss="yes" class="systemMessage alert-danger"><ul><li>' . $message . '</li></ul>' . self::closeBtn . '</div>';
		if (!$postInTheNextPage) {
			self::$prompt .= $message;
		} else {
			Prompt::sendNextPage($message);
		}
	}

	/**
	 * Post good type message
	 * @param string $message
	 * @param bool $postInTheNextPage
	 */
	public static function postGood($message, $postInTheNextPage = false)
	{
		$message = '<div auto_dismiss="yes" class="systemMessage alert-success"><ul><li>' . $message . '</li></ul>' . self::closeBtn . '</div>';
		if (!$postInTheNextPage) {
			self::$prompt .= $message;
		} else {
			Prompt::sendNextPage($message);
		}
	}

	/**
	 * Post warning type message
	 * @param string $message
	 * @param bool $postInTheNextPage
	 */
	public static function postWarn($message, $postInTheNextPage = false)
	{
		$message = '<div auto_dismiss="yes" class="systemMessage alert-warning"><ul><li>' . $message . '</li></ul>' . self::closeBtn . '</div>';
		if (!$postInTheNextPage) {
			self::$prompt .= $message;
		} else {
			Prompt::sendNextPage($message);
		}
	}

	/**
	 * Post information type message
	 * @param string $message
	 * @param bool $postInTheNextPage
	 */
	public static function postInfo($message, $postInTheNextPage = false)
	{
		$message = '<div auto_dismiss="yes" class="systemMessage alert-info"><ul><li>' . $message . '</li></ul>' . self::closeBtn . '</div>';
		if (!$postInTheNextPage) {
			self::$prompt .= $message;
		} else {
			Prompt::sendNextPage($message);
		}
	}

	/**
	 * NOTE: Only use this on view.php controller
	 * Post information type message
	 * @param string $message
	 */
	public static function postInfoInScript($message)
	{
		echo ("<script>showMessage('$message','info');</script>");
	}

	/**
	 * NOTE: Only use this on view.php controller
	 * Post warning type message
	 * @param string $message
	 */
	public static function postWarnInScript($message)
	{
		echo ("<script>showMessage('$message','warning');</script>");
	}

	/**
	 * NOTE: Only use this on view.php controller
	 * Post good type message
	 * @param string $message
	 */
	public static function postGoodInScript($message)
	{
		echo ("<script>showMessage('$message','success');</script>");
	}

	/**
	 * NOTE: Only use this on view.php controller
	 * Post error type message
	 * @param string $message
	 */
	public static function postErrorInScript($message)
	{
		echo ("<script>showMessage('$message','danger');</script>");
	}

	/**
	 * NOTE: Only loaded on template controller!
	 * Print all prompt.
	 */
	public static function printPrompt()
	{
		echo ('<div class="systemMessage_wrap">' . self::$prompt . '</div>');
	}
}

/* Check if there is any pending notification */
if (isset($_SESSION["__POSPROMPT"])) {
	Prompt::$prompt .= $_SESSION["__POSPROMPT"];
	unset($_SESSION["__POSPROMPT"]);
}