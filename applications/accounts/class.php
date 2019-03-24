<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 *
 */

use Automattic\Phone\Mobile_Validator;

define("USER_AUTH_SU", 0);
define("USER_AUTH_EMPLOYEE", 1);
define("USER_AUTH_REGISTERED", 2);
define("USER_AUTH_PUBLIC", 3);

/**
 * Use this class to manage User, and authenticate user permission
 */
class Accounts
{
	private static $cache = [];

	public static $customET_CE = null;
	public static $customET_RP = null;
	public static $customET_AC = null;

	public static $customM_F = null;
	public static $customM_M = null;
	public static $customM_EN = false;
	public static $customM_UE = false;
	public static $customM_UP = false;

	public static $aflfl = [];

	public static function purgeCache()
	{
		self::$cache = [];
	}

	/**
	 * Register function to be executed after the user login attempt success
	 * @param Callable $f
	 */
	public static function register_post_login_function($f)
	{
		if (!is_callable($f)) throw new PuzzleError("Invalid function input");
		self::$aflfl[] = $f;
	}

	/**
	 * Get Session data.
	 * a.k.a. from $_SESSION["account"]
	 * 
	 * @return array
	 */
	public static function getSession()
	{
		return $_SESSION['account'];
	}

	/**
	 * Count the number of registered user
	 * @return integer
	 */
	public static function count()
	{
		return Database::execute("SELECT count(`id`) from app_users_list")->fetch_row()[0];
	}

	/**
	 * Re-format phone number according to E164 format.
	 * If country code not specified, it will asssume in Indonesia (+62)
	 * by default.
	 * 
	 * If country code, or phone number formatting from another country
	 * detected, we will use that country.
	 * e.g. (817) 569-8900 from USA
	 * 
	 * @param string $phone
	 * @param bool $getCountry 
	 * @return array If phone number is parsed, and $getCountry is true
	 * @return string If phone number is parsed
	 * @return null If parsing is failed
	 */
	public static function getE164($phone, $getCountry = false)
	{
		$a = new Mobile_Validator;
		$r = $a->normalize($phone);
		if (empty($r)) {
			$r = $a->normalize($phone, "ID");
		}
		return $getCountry ? $r : $r[0];
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
	public static function changeAccountActivationHandler($F, $email_or_phone = false, $custom_message = null)
	{
		if (!is_callable($F)) throw new PuzzleError("Invalid parameter");
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
	public static function setEmailTemplate_ConfirmEmail($html)
	{
		self::$customET_CE = $html;
	}

	/**
	 * Change default reset password template
	 * Available variable :
	 * {name}, {link}
	 * 
	 * @param string $html
	 */
	public static function setEmailTemplate_ResetPassword($html)
	{
		self::$customET_RP = $html;
	}

	/**
	 * Change default activate account template
	 * Available variable :
	 * {name}, {link}
	 * 
	 * @param string $html
	 */
	public static function setEmailTemplate_ActivateAccount($html)
	{
		self::$customET_AC = $html;
	}

	/**
	 * Get settings
	 * @return array
	 */
	public static function getSettings()
	{
		if (!isset(self::$cache["settings"]))
			self::$cache["settings"] = json_decode(UserData::read("settings"), true);
		return (self::$cache["settings"]);
	}

	/**
	 * Verify Re-Captcha after login
	 * @return bool
	 */
	public static function verifyRecapctha()
	{
		$result = file_get_contents(
			'https://www.google.com/recaptcha/api/siteverify',
			false,
			stream_context_create([
				'http' => [
					'header' => "Content-type: application/x-www-form-urlencoded\r\n",
					'method' => 'POST',
					'content' => http_build_query([
						'secret' => self::getSettings()["f_recaptcha_secret"],
						'response' => $_POST["g-recaptcha-response"],
						'remoteip' => $_SERVER["REMOTE_ADDR"]
					])
				]
			])
		);
		if ($result === false) throw new PuzzleError("Cannot contact Google for Recaptcha");
		return (json_decode($result)->success);
	}

	/**
	 * Hash password using default php password_hash(BCRYPT)
	 * @param string $password
	 * @return string
	 */
	public static function hashPassword($password)
	{
		return (password_hash($password, PASSWORD_BCRYPT));
	}

	/**
	 * Verify Password Hash
	 * This function use default php password_verify()
	 * 
	 * @param string $password
	 * @param string $hash
	 * @return bool
	 */
	public static function verifyHashPass($password, $hash)
	{
		return (password_verify($password, $hash));
	}

	/**
	 * Get authentication type in string
	 * @param integer $auth
	 * @return string
	 */
	public static function translateAuth($auth)
	{
		switch ($auth) {
			case 0:
				return ("Superuser");
			case 1:
				return ("Employees");
			case 2:
				return ("Regitered");
			case 3:
				return ("Public");
			default:
				throw new PuzzleError("Authentication level is invalid");
		}
	}

	/**
	 * Get system group id from USER_AUTH type
	 * 
	 * @param integer $level Selected authentication type, use "USER_AUTH_*" constant!
	 * @return integer
	 */
	public static function getRootGroupId($level)
	{
		switch ($level) {
			case USER_AUTH_SU:
			case USER_AUTH_EMPLOYEE:
			case USER_AUTH_REGISTERED:
			case USER_AUTH_PUBLIC:
				break;
			default:
				throw new PuzzleError("Invalid Level!");
		}
		return Database::read("app_users_grouplist", "id", "level", $level);
	}

	/**
	 * Get group name by id
	 * 
	 * @param integer $group_id Selected authentication type
	 * @return integer
	 */
	public static function getGroupName($group_id)
	{
		return (Database::read("app_users_grouplist", "name", "id", $group_id));
	}

	/**
	 * Get authentication level by group id
	 * 
	 * @param integer $group_id Selected authentication type
	 * @return integer
	 */
	public static function getAuthLevel($group_id)
	{
		return (Database::read("app_users_grouplist", "level", "id", $group_id));
	}

	/**
	 * Get user details
	 * 
	 * @param string $userID User ID
	 * @return array If success
	 * @return NULL If user doesn't exists
	 */
	public static function getDetails($userID)
	{
		$profile = Database::getRow("app_users_list", "id", $userID);
		if ($profile == null) return null;
		$s['email'] = $profile["email"];
		$s['phone'] = $profile["phone"];
		$s['lang'] = $profile["lang"];
		$s['name'] = $profile["name"];
		$s['group'] = $profile["group"];
		return $s;
	}

	/**
	 * Find user based on email, phone, or name
	 * 
	 * @param string $email_phone_name 
	 * @return array
	 */
	public static function findUser($email_phone_name, $limit = null)
	{
		if (strlen($email_phone_name) < 3) return [];
		$p = self::getE164($email_phone_name);
		if ($p == "") $p = "NULL";
		return (Database::toArray(Database::execute(
			"SELECT `id`,`name`,`group`,`email`,`registered_time` from `app_users_list` where name like '%?%' or email like '%?%' or phone like '%?%'" . ($limit !== null ? " LIMIT ?" : ""),
			$email_phone_name,
			$email_phone_name,
			$p,
			$limit
		)));
	}

	/**
	 * Check if user exists or not
	 * 
	 * @param int $userID User ID
	 * @return bool
	 */
	public static function isUserExists(int $userID)
	{
		return (Database::read("app_users_list", "id", "id", $userID) == $userID);
	}

	/**
	 * Add login session
	 * 
	 * @param int $userID User ID
	 * @return bool
	 */
	public static function addSession(int $userID)
	{
		if (Database::read("app_users_list", "enabled", "id", $userID) != 1) return false;
		$_SESSION['account']['loggedIn'] = 1;
		$_SESSION['account']['id'] = $userID;
		$_SESSION['account']['email'] = Database::read("app_users_list", "email", "id", $userID);
		$_SESSION['account']['phone'] = Database::read("app_users_list", "phone", "id", $userID);
		$_SESSION['account']['lang'] = Database::read("app_users_list", "lang", "id", $userID);
		$_SESSION['account']['name'] = Database::read("app_users_list", "name", "id", $userID);
		$_SESSION['account']['group'] = Database::read("app_users_list", "group", "id", $userID);

		foreach (self::$aflfl as $alf) {
			if ($alf() === false) self::rmSession();
		}
		return $_SESSION['account']['loggedIn'] === 1;
	}

	/**
	 * Ask challenge for TFA to current logged-in user.
	 * Once this function is called, it will call rmSession()
	 * @param string $url_callback A callback after the challenge completed
	 * 
	 * @return bool FALSE if failed
	 * @return string URL to redirect the challenge if success
	 */
	public static function challengeTFA(string $url_callback = "/")
	{
		if ($_SESSION['account']['loggedIn'] === 1) {
			if ($_SESSION['account']['tfa_cache'] >= time()) return $url_callback;
			$user = self::getDetails($userid = $_SESSION['account']['id']);
			$challengeCode = rand_str(6, "0987654321");
			if (!self::$customM_EN) {
				if (!filter_var($user["email"], FILTER_VALIDATE_EMAIL)) {
					return false;
				} else {
					$w = new Worker;
					$path = __DIR__;
					$w->setTask(function ($id, $app) use ($challengeCode, $user, $path) {
						new Application("phpmailer");
						$language = new Language($app);
						ob_start();
						include("$path/mail_template/tfa.php");
						$mailer = new Mailer;
						$mailer->addRecipient = $user["email"];
						$mailer->subject = $language->get("VER_CODE");
						$mailer->body = ob_get_clean();
						return $mailer->sendHTML();
					})->run(["standalone" => true]);
					$email = explode("@", $user["email"]);
					$_SESSION["account"]["tfa"]["msg"] = $language->get("TFA_INFO2");
					$_SESSION["account"]["tfa"]["msg"] .= " " . substr($email[0], 0, 2) . "*****@" . $email[1];
				}
			} else {
				$aclb = Accounts::$customM_F;
				$aclbr = $aclb($customM_UE ? $user['email'] : $user["phone"], $challengeCode);
				$_SESSION["account"]["tfa"]["msg"] = $aclbr;
				if ($aclbr === false) {
					unset($_SESSION["account"]["tfa"]);
					return false;
				}
			}

			$_SESSION["account"]["tfa"]['code'] = $challengeCode;
			$_SESSION['account']['tfa']['timeout'] = time() + (5 * T_MINUTE);
			$_SESSION['account']['tfa']['signin'] = $userid;
			self::rmSession();
			return ("users/verify?redir=" . $_POST["redir"]);
		}
		return false;
	}

	/**
	 * Authenticate a user. If success, add the user session.
	 * 
	 * @param string $username
	 * @param string $password
	 * @param bool $addSession
	 * @return mixed
	 */
	public static function authUserId($username, $password, bool $addSession = true)
	{
		if (self::$customM_UE && !self::$customM_UP) {
			if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
				$userid = Database::read("app_users_list", "id", "email", strtolower($username));
			} else {
				$userid = Database::read("app_users_list", "id", "username", strtolower($username));
			}
		} elseif (!self::$customM_UE && self::$customM_UP) {
			$userid = Database::read("app_users_list", "id", "username", strtolower($username));
			if ($userid == "") {
				$phone = self::getE164(strtolower($username));
				if ($phone != "") $userid = Database::read("app_users_list", "id", "phone", self::getE164(strtolower($username)));
			}
		} else {
			$userid = Database::read("app_users_list", "id", "username", strtolower($username));
		}
		if ($userid == "" || Database::read("app_users_list", "enabled", "id", $userid) != 1) return false;
		$auth_user = $userid != "" ? 1 : 0;
		$auth_pass = self::verifyHashPass($password, Database::read("app_users_list", "password", "id", $userid));
		if ($auth_user && $auth_pass) {
			if ($addSession) self::addSession($userid);
			return (int)$userid;
		}
		return false;
	}

	/**
	 * Get User ID based on username
	 * @param string $username Username will be converted to lowercase
	 * @return integer
	 */
	public static function findUserID($username)
	{
		return (Database::read("app_users_list", "id", "username", strtolower($username)));
	}

	/**
	 * Remove login session
	 */
	public static function rmSession()
	{
		$_SESSION['account']['loggedIn'] = 0;
		$_SESSION['account']['id'] = -1;
		$_SESSION['account']['email'] = "";
		$_SESSION['account']['phone'] = "";
		$_SESSION['account']['name'] = "";
		$_SESSION['account']['lang'] = "en";
		$_SESSION['account']['group'] = self::getRootGroupId(USER_AUTH_PUBLIC);

		unset($_SESSION['account']['tfa_cache']);
	}

	/**
	 * Compare user authentication with authentication level
	 * @param integer $required_level USER_AUTH_SU, USER_AUTH_EMPLOYEE, USER_AUTH_REGISTERED, USER_AUTH_PUBLIC
	 * @return bool
	 */
	public static function authAccess($required_level)
	{
		//On CLI, user always authenticated as USER_AUTH_SU
		if (is_cli() && defined("__POSCLI")) return true;

		if ($_SESSION['account']['loggedIn'] == 0) {
			return ($required_level >= USER_AUTH_PUBLIC);
		} else {
			return (self::getAuthLevel($_SESSION['account']["group"]) <= $required_level);
		}
	}

	/**
	 * Compare user authentication with group
	 * @param integer $required_group_id
	 * @return bool
	 */
	public static function authAccessAdvanced($required_group_id)
	{
		//On CLI, user always authenticated as USER_AUTH_SU
		if (is_cli() && defined("__POSCLI")) return true;

		$result = false;

		$level_required = self::getAuthLevel($required_group_id);
		$level_user = self::getAuthLevel($_SESSION['account']['group']);

		if ($level_user == $level_required) {
			switch ($required_group_id) {
				case 1:
				case 2:
				case 3:
					return true;
				default:
					switch ($_SESSION['account']['group']) {
						case 1:
						case 2:
						case 3:
							return true;
						default:
							return ($_SESSION['account']['group'] == $required_group_id);
					}
			}
		} else {
			return ($level_user < $level_required);
		}
	}

	/**
	 * Get user id currently logged in
	 * @return integer
	 */
	public static function getUserId()
	{
		return $_SESSION["account"]["id"];
	}
}

if (Accounts::getSettings()["f_reg_activate"] == "on") Accounts::$customM_UE = true;