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
 * @software     Release: 2.0.1
 */

/**
 * Push message or notification to user
 */
class Prompt{
	/**
	 * This var contain list of propmt for temporary
	 * @var string
	 */
	public static $prompt = "";
	
	private static function sendNextPage($message){
		$_SESSION["__POSPROMPT"] .= $message;
	}
	
	/**
	 * Post error type message
	 * @param string $message
	 * @param bool $postInTheNextPage
	 */
	public static function postError($message, $postInTheNextPage = false){
		$message = '<div auto_dismiss="yes" class="systemMessage alert-danger"><button onclick="$(this).parent().remove();" type="button" class="close">×</button><ul><li>'.$message.'</li></ul></div>';
		if(!$postInTheNextPage){
			self::$prompt .= $message;
		}else{
			Prompt::sendNextPage($message);
		}
	}
	
	/**
	 * Post good type message
	 * @param string $message
	 * @param bool $postInTheNextPage
	 */
	public static function postGood($message, $postInTheNextPage = false){
		$message = '<div auto_dismiss="yes" class="systemMessage alert-success"><button onclick="$(this).parent().remove();" type="button" class="close">×</button><ul><li>'.$message.'</li></ul></div>';
		if(!$postInTheNextPage){
			self::$prompt .= $message;
		}else{
			Prompt::sendNextPage($message);
		}
	}
	
	/**
	 * Post warning type message
	 * @param string $message
	 * @param bool $postInTheNextPage
	 */
	public static function postWarn($message, $postInTheNextPage = false){		
		$message = '<div auto_dismiss="yes" class="systemMessage alert-warning"><button onclick="$(this).parent().remove();" type="button" class="close">×</button><ul><li>'.$message.'</li></ul></div>';
		if(!$postInTheNextPage){
			self::$prompt .= $message;
		}else{
			Prompt::sendNextPage($message);
		}
	}
	
	/**
	 * Post information type message
	 * @param string $message
	 * @param bool $postInTheNextPage
	 */
	public static function postInfo($message, $postInTheNextPage = false){
		$message = '<div auto_dismiss="yes" class="systemMessage alert-info"><button onclick="$(this).parent().remove();" type="button" class="close">×</button><ul><li>'.$message.'</li></ul></div>';
		if(!$postInTheNextPage){
			self::$prompt .= $message;
		}else{
			Prompt::sendNextPage($message);
		}
	}
	
	/**
	 * NOTE: Only use this on view.php controller
	 * Post information type message
	 * @param string $message
	 */
	public static function postInfoInScript($message){
		echo("<script>showMessage('$message','info');</script>");		
	}
	
	/**
	 * NOTE: Only use this on view.php controller
	 * Post warning type message
	 * @param string $message
	 */
	public static function postWarnInScript($message){
		echo("<script>showMessage('$message','warning');</script>");		
	}
	
	/**
	 * NOTE: Only use this on view.php controller
	 * Post good type message
	 * @param string $message
	 */
	public static function postGoodInScript($message){
		echo("<script>showMessage('$message','success');</script>");		
	}
	
	/**
	 * NOTE: Only use this on view.php controller
	 * Post error type message
	 * @param string $message
	 */
	public static function postErrorInScript($message){
		echo("<script>showMessage('$message','danger');</script>");		
	}
	
	/**
	 * NOTE: Only loaded on template controller!
	 * Print all prompt.
	 */
	public static function printPrompt(){
		$l = new Language; $l->app="admin";	?>
		<div class="systemMessage_wrap">
		<?php echo self::$prompt?>
		</div>
		<?php
	}
}

/* Check if there is any pending notification */
if(isset($_SESSION["__POSPROMPT"])){
	Prompt::$prompt .= $_SESSION["__POSPROMPT"];
	unset($_SESSION["__POSPROMPT"]);
}
?>