<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 *
 * @software     Release: 2.0.2
 */

define("USER_AUTH_SU", 0);
define("USER_AUTH_EMPLOYEE", 1);
define("USER_AUTH_REGISTERED", 2);
define("USER_AUTH_PUBLIC", 3);

/**
 * Use this class to manage User, and authenticate user permission
 */
class Accounts{

	public static $customET_CE = NULL;
	public static $customET_RP = NULL;
	public static $customET_AC = NULL;

	public static $customM_F = NULL;
	public static $customM_M = NULL;
	public static $customM_EN = false;
	public static $customM_UE = false;
	public static $customM_UP = false;
	
	public static $aflfl = [];
	
	/**
	 * Register function to be executed after the user login attempt success
	 * @param Object $f
	 */
	public static function register_post_login_function($f){
		if(!is_callable($f)) throw new PuzzleError("Invalid function input");
		self::$aflfl[] = $f;
	}
	
	/**
	 * Get Session data.
	 * a.k.a. from $_SESSION["account"]
	 * 
	 * @return array
	 */
	public static function getSession(){
		return $_SESSION['account'];
	}

	/**
	 * Count the number of registered user
	 * @return integer
	 */
	public static function count(){
		return mysqli_num_rows(Database::exec("SELECT `id` from app_users_list"));
	}

	/**
	 * Re-format phone number according to E164 format, in Indonesia Country
	 * 
	 * @param string $phone
	 * @return string It will return empty if phone number cannot be parsed.
	 */
	public static function getE164($phone){
		if(!defined("LIBPHONENUM_H")){
			require("vendor/libphonenumber/vendor/autoload.php");
			define("LIBPHONENUM_H",1);
		}
		
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		try{
			//TODO: Change this things ASAP
			$proto = $phoneUtil->parse($phone, "ID");
			return $phoneUtil->format($proto, \libphonenumber\PhoneNumberFormat::E164);
		}catch(\libphonenumber\NumberParseException $e){
			return "";
		}
	}

	/**
	 * Change the confirmation through custom method
	 * The callable should return a message or FALSE boolean
	 * The callabe will receive 2 parameter ($email_or_phone,$code)
	 * 
	 * @param callable $F
	 * @param bool $email_or_phone TRUE for email, FALSE for phone
	 * @param string $custom_message
	 */
	public static function changeAccountActivationHandler($F, $email_or_phone = false, $custom_message = NULL){
		if(!is_callable($F)) throw new PuzzleError("Invalid parameter");
		self::$customM_F = &$F;
		self::$customM_EN = true;
		self::$customM_UE = $email_or_phone;
		self::$customM_UP = !$email_or_phone;
	}

	/**
	 * Change default email confirmation template
	 * Available variable :
	 * {name}, {email}, {link}
	 * 
	 * @param string $html
	 */
	public static function setEmailTemplate_ConfirmEmail($html){
		self::$customET_CE = $html;
	}

	/**
	 * Change default reset password template
	 * Available variable :
	 * {name}, {link}
	 * 
	 * @param string $html
	 */
	public static function setEmailTemplate_ResetPassword($html){
		self::$customET_RP = $html;
	}

	/**
	 * Change default activate account template
	 * Available variable :
	 * {name}, {link}
	 * 
	 * @param string $html
	 */
	public static function setEmailTemplate_ActivateAccount($html){
		self::$customET_AC = $html;
	}

	/**
	 * Get settings
	 * 
	 * @return array
	 */
	public static function getSettings(){
		return(json_decode(UserData::read("settings"),true));
	}

	/**
	 * Verify Re-Captcha after login
	 * 
	 * @return bool
	 */
	public static function verifyRecapctha(){
		$result = file_get_contents(
			'https://www.google.com/recaptcha/api/siteverify', 
			false, 
			stream_context_create([
				'http' => [
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query([
						'secret' => self::getSettings()["f_recaptcha_secret"],
						'response' => $_POST["g-recaptcha-response"],
						'remoteip' => $_SERVER["REMOTE_ADDR"]
					])
				]
			])
		);
		if ($result === FALSE) throw new PuzzleError("Cannot contact Google for Recaptcha");
		return(json_decode($result)->success);
	}

	/**
	 * Hash password
	 * This function use default php password_hash()
	 * 
	 * @param string $password
	 * @return string
	 */
	public static function hashPassword($password){
		return(password_hash($password, PASSWORD_BCRYPT));
	}

	/**
	 * Verify Password Hash
	 * This function use default php password_verify()
	 * 
	 * @param string $password
	 * @param string $hash
	 * @return bool
	 */
	public static function verifyHashPass($password,$hash){
		return(password_verify($password,$hash));
	}

	/**
	 * Get authentication type in string
	 * 
	 * @param integer $auth
	 * @return string
	 */
	public static function translateAuth($auth){
		if($auth == 0) return("Superuser");
		if($auth == 1) return("Employees");
		if($auth == 2) return("Regitered");
		if($auth == 3) return("Public");
	}

	/**
	 * Get system group id from USER_AUTH type
	 * 
	 * @param integer $level Selected authentication type, use "USER_AUTH_*" constant!
	 * @return integer
	 */
	public static function getRootGroupId($level){
		switch($level){
		case USER_AUTH_SU:
		case USER_AUTH_EMPLOYEE:
		case USER_AUTH_REGISTERED:
		case USER_AUTH_PUBLIC:
			break;
		default:
			throw new PuzzleError("Invalid Level!");
		}
		return Database::read("app_users_grouplist","id","level",$level);
	}

	/**
	 * Get group name by id
	 * 
	 * @param integer $group_id Selected authentication type
	 * @return integer
	 */
	public static function getGroupName($group_id){
		return(Database::read("app_users_grouplist","name","id",$group_id));
	}

	/**
	 * Get authentication level by group id
	 * 
	 * @param integer $group_id Selected authentication type
	 * @return integer
	 */
	public static function getAuthLevel($group_id){
		return(Database::read("app_users_grouplist","level","id",$group_id));
	}

	/**
	 * Get user details
	 * 
	 * @param string $userID User ID
	 * @return array If success
	 * @return NULL If user doesn't exists
	 */
	public static function getDetails($userID){
		if(Database::read("app_users_list","id","id",$userID) != $userID) return NULL;
		$s['email'] = Database::read("app_users_list","email","id",$userID);
		$s['phone'] = Database::read("app_users_list","phone","id",$userID);
		$s['lang'] = Database::read("app_users_list","lang","id",$userID);
		$s['name'] = Database::read("app_users_list","name","id",$userID);
		$s['group'] = Database::read("app_users_list","group","id",$userID);
		return $s;
	}
	
	/**
	 * Find user based on email, phone, or name
	 * 
	 * @param string $email_phone_name 
	 * @return array
	 */
	public static function findUser($email_phone_name, $limit = NULL){
		if(strlen($email_phone_name) < 3) return [];
		$p = self::getE164($email_phone_name);
		if($p == "") $p = "NULL";
		return(Database::toArray(Database::exec(
			"SELECT `id`,`name`,`group`,`email`,`registered_time` from `app_users_list` where name like '%?%' or email like '%?%' or phone like '%?%'".($limit!==NULL?" LIMIT ?":""),
		$email_phone_name,$email_phone_name,$p,$limit))->data);
	}

	/**
	 * Check if user exists or not
	 * 
	 * @param string $userID User ID
	 * @return bool
	 */
	public static function isUserExists($userID){
		return(Database::read("app_users_list","id","id",$userID) == $userID);
	}

	/**
	 * Add login session
	 * 
	 * @param string $userID User ID
	 * @return bool
	 */
	public static function addSession($userID){
		if(Database::read("app_users_list","enabled","id",$userID) != 1) return false;
		$_SESSION['account']['loggedIn'] = 1;
		$_SESSION['account']['id'] = $userID;
		$_SESSION['account']['email'] = Database::read("app_users_list","email","id",$userID);
		$_SESSION['account']['phone'] = Database::read("app_users_list","phone","id",$userID);
		$_SESSION['account']['lang'] = Database::read("app_users_list","lang","id",$userID);
		$_SESSION['account']['name'] = Database::read("app_users_list","name","id",$userID);
		$_SESSION['account']['group'] = Database::read("app_users_list","group","id",$userID);
		return true;
	}

	/**
	 * Authenticate a user
	 * 
	 * @param string $username Username will be converted to lowercase
	 * @param string $pass
	 * @return bool
	 */
	public static function authUserId($username,$pass){
		if(self::$customM_UE && !self::$customM_UP){
			if(filter_var($username,FILTER_VALIDATE_EMAIL)){
				$userid = Database::read("app_users_list","id","email",strtolower($username));
			}else{
				$userid = Database::read("app_users_list","id","username",strtolower($username));
			}
		}elseif(!self::$customM_UE && self::$customM_UP){
			$userid = Database::read("app_users_list","id","username",strtolower($username));
			if($userid == ""){
				$phone = self::getE164(strtolower($username));
				if($phone != "") $userid = Database::read("app_users_list","id","phone",self::getE164(strtolower($username)));
			}
		}else{
			$userid = Database::read("app_users_list","id","username",strtolower($username));
		}
		if($userid == "" || Database::read("app_users_list","enabled","id",$userid) != 1) return false;
		$auth_user = $userid != "" ? 1 : 0;
		$auth_pass = self::verifyHashPass($pass,Database::read("app_users_list","password","id",$userid));
		if($auth_user && $auth_pass){
			self::addSession($userid);
			foreach(self::$aflfl as $alf) $alf();
			return true;
		}
		return false;
	}

	/**
	 * Get User ID based on username
	 * @param string $username Username will be converted to lowercase
	 * @return integer
	 */
	public static function findUserID($username){
		return(Database::read("app_users_list","id","username",strtolower($username)));
	}

	/**
	 * Remove login session
	 */
	public static function rmSession(){
		$_SESSION['account']['loggedIn'] = 0;
		$_SESSION['account']['id'] = -1;
		$_SESSION['account']['email'] = "";
		$_SESSION['account']['phone'] = "";
		$_SESSION['account']['name'] = "";
		$_SESSION['account']['lang'] = "en";
		$_SESSION['account']['group'] = self::getRootGroupId(USER_AUTH_PUBLIC);
	}

	/**
	 * Compare user authentication with authentication level
	 * @param string $required_level USER_AUTH_SU, USER_AUTH_EMPLOYEE, USER_AUTH_REGISTERED, USER_AUTH_PUBLIC
	 * @return bool
	 */
	public static function authAccess($required_level){
		//On CLI, user always authenticated as USER_AUTH_SU
		if(__isCLI() && defined("__POSCLI")) return true;

		if($_SESSION['account']['loggedIn'] == 0){
			return($required_level >= USER_AUTH_PUBLIC);
		}else{
			return(self::getAuthLevel($_SESSION['account']["group"]) <= $required_level);
		}
	}

	/**
	 * Compare user authentication with group
	 * @param string $requiredGroup User group ID
	 * @return bool
	 */
	public static function authAccessAdvanced($requiredGroup){
		//On CLI, user always authenticated as USER_AUTH_SU
		if(__isCLI() && defined("__POSCLI")) return true;

		$result = false;
		
		$level_required = self::getAuthLevel($requiredGroup);
		$level_user = self::getAuthLevel($_SESSION['account']['group']);
		
		if($level_user == $level_required){
			if($_SESSION['account']['group'] == $requiredGroup) return true;
			switch($_SESSION['account']['group']){				
			case 2:case 3: 
				return true;
			default: 
				return false;
			}
		}else{
			return ($level_user < $level_required);
		}
	}

    /**
	 * Get logged-in user id
	 * @return string
	 */
     public static function getUserId() {
         return $_SESSION["account"]["id"];
     }
}

if(Accounts::getSettings()["f_reg_activate"] == "on" ) Accounts::$customM_UE = true;
?>
