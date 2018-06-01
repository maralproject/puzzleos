<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 *
 * @software     Release: 1.2.3
 */

define("USER_AUTH_SU", 0);
define("USER_AUTH_EMPLOYEE", 1);
define("USER_AUTH_REGISTERED", 2);
define("USER_AUTH_PUBLIC", 3);

/**
 * Use this class to manage User, and authenticate user permission
 */
class Accounts{
	/**
	 * @var array
	 */
	private static $users = [];

	public static $customET_CE = NULL;
	public static $customET_RP = NULL;
	public static $customET_AC = NULL;

	public static $customM_F = NULL;
	public static $customM_M = NULL;
	public static $customM_EN = false;
	public static $customM_UE = false;
	public static $customM_UP = false;

	/**
	 * Count the number of registered user
	 * @return integer
	 */
	public static function count(){
		return mysqli_num_rows(Database::exec("SELECT `id` from app_users_list"));
	}

	/**
	 * Re-format phone number according to E164 format, in Indonesia Country
	 * @param string $phone
	 * @return string
	 */
	public static function getE164($phone){
		//Speedup things. Donot load the library if not required
		require_once("lib/libphonenumber/vendor/autoload.php");
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
	 * @param callable $F
	 * @param bool $email_or_phone TRUE for email, FALSE for phone
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
	 */
	public static function setEmailTemplate_ConfirmEmail($html){
		self::$customET_CE = $html;
	}

	/**
	 * Change default reset password template
	 * Available variable :
	 * {name}, {link}
	 */
	public static function setEmailTemplate_ResetPassword($html){
		self::$customET_RP = $html;
	}

	/**
	 * Change default activate account template
	 * Available variable :
	 * {name}, {link}
	 */
	public static function setEmailTemplate_ActivateAccount($html){
		self::$customET_AC = $html;
	}

	public static function getSettings(){
		return(json_decode(UserData::read("settings"),true));
	}

	public static function verifyRecapctha(){
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = [
			'secret' => self::getSettings()["f_recaptcha_secret"],
			'response' => $_POST["g-recaptcha-response"],
			'remoteip' => $_SERVER["REMOTE_ADDR"]
		];

		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE) throw new PuzzleError("Cannot contact Google for Recaptcha");
		return(json_decode($result)->success);
	}

	/**
	 * Hash password
	 * This function use default php password_hash()
	 * @param string $password
	 * @return string
	 */
	public static function hashPassword($password){
		return(password_hash($password, PASSWORD_BCRYPT));
	}

	/**
	 * Verify Password Hash
	 * This function use default php password_verify()
	 * @param string $password
	 * @param string $hash
	 * @return bool
	 */
	public static function verifyHashPass($password,$hash){
		return(password_verify($password,$hash));
	}

	/**
	 * Get authentication type in string
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
	 * @param integer $level Selected authentication type, use "USER_AUTH" constant!
	 * @return integer
	 */
	public static function getRootGroupId($level){
		return Database::read("app_users_grouplist","id","level",$level);
	}

	/**
	 * Get user group name
	 * @param integer $group Selected authentication type
	 * @return integer
	 */
	public static function getGroupName($group){
		return(Database::read("app_users_grouplist","name","id",$group));
	}

	/**
	 * Get authentication level based on the group id
	 * @param integer $group Selected authentication type
	 * @return integer
	 */
	public static function getAuthLevel($group){
		return(Database::read("app_users_grouplist","level","id",$group));
	}

	/**
	 * Return user group input
	 * Get button which will show Propmt input
	 * @param string $input_name Hidden input name
	 * @param string $group Selected group
	 * @param integer $level_option USER_AUTH_SU, USER_AUTH_EMPLOYEE, USER_AUTH_REGISTERED, USER_AUTH_PUBLIC
	 * @return string
	 */
	public static function getGroupPromptButton($input_name,$group,$level_option = USER_AUTH_PUBLIC){
		switch($level_option){
			case USER_AUTH_SU:
			case USER_AUTH_EMPLOYEE:
			case USER_AUTH_REGISTERED:
			case USER_AUTH_PUBLIC:
			break;
			default:
				return("");
		}
		if(!self::$users["printedDiv"]){
			$dataLvl  = [];
			$dataLvl[0] = Database::readAll("app_users_grouplist","WHERE `level`=0")->data;
			$dataLvl[1] = Database::readAll("app_users_grouplist","WHERE `level`=1")->data;
			$dataLvl[2] = Database::readAll("app_users_grouplist","WHERE `level`=2")->data;
			$dataLvl[3] = Database::readAll("app_users_grouplist","WHERE `level`=3")->data;
			$l = new Language;$l->app="users";
			ob_start();
			?>
			<style>
			.user_card{
				font-size:9pt;
				float:left;
				padding:12px;
				cursor:pointer;
			}
			.user_card:before{
				font-family:FontAwesome;
				content:"\f007";
				margin-right:10px;
			}
			.group_card:before{
				font-family:FontAwesome;
				content:"\f0c0"!important;
				margin-right:10px;
			}
			.group_card{
				color:black!important;
			}
			.ugitem:hover{
				border-bottom:none!important;
			}
			</style>
			<?php $t1 = FastCache::outCSSMin(); ob_start();?>
			<script>
			var formHtmlUGSEL;
			function UGLB_SelectGroup(btn){
				hideMessage();
				showMessage(formHtmlUGSEL,"info","GroupSel",false);
				$(".ugsel").attr("inputid",btn.attr("inputid"));
				switch(btn.attr("level")){
					case "0":
						$(".ugsel[inputid=" + btn.attr("inputid") + "] .ugitem[level=1]").remove();
					case "1":
						$(".ugsel[inputid=" + btn.attr("inputid") + "] .ugitem[level=2]").remove();
					case "2":
						$(".ugsel[inputid=" + btn.attr("inputid") + "] .ugitem[level=3]").remove();
					case "3":
					break;
				}
				$(".ugsel[inputid=" + btn.attr("inputid") + "] .group_card").on("click",function(){
					hideMessage();
					$("#" + $(this).parent().attr("inputid")).val($(this).attr("uid")).trigger("change");
					$("#UGLB_" + $(this).parent().attr("inputid")).html($(this).html());
				});
			}
			</script>
			<?php $t2 = FastCache::outJSMin(); ob_start(); echo $t1; echo $t2;?>
			<div id="groupListSystem" style="display:none!important;">
				<?php $l->dump("SEL_GROUP")?>:
				<div inputid="" class="ugsel" style="max-height:250px;overflow:auto;">
				<?php
				foreach($dataLvl[0] as $d){
					echo('<div level="0" uid="'.$d["id"].'" class="ugitem group_card user_card material_card ripple">'.$d["name"].'</div>');
				}
				?>
				<div level="1" style="clear:both;" class="ugitem"></div>
				<?php
				foreach($dataLvl[1] as $d){
					echo('<div level="1" uid="'.$d["id"].'" class="ugitem group_card user_card material_card ripple">'.$d["name"].'</div>');
				}
				?>
				<div level="2" style="clear:both;" class="ugitem"></div>
				<?php
				foreach($dataLvl[2] as $d){
					echo('<div level="2" uid="'.$d["id"].'" class="ugitem group_card user_card material_card ripple">'.$d["name"].'</div>');
				}
				?>
				<div level="3" style="clear:both;" class="ugitem"></div>
				<?php
				foreach($dataLvl[3] as $d){
					echo('<div level="3" uid="'.$d["id"].'" class="ugitem group_card user_card material_card ripple">'.$d["name"].'</div>');
				}
				?>
				<div style="clear:both;"></div>
				</div>
			</div>
			<script>formHtmlUGSEL = $("#groupListSystem").html();$("#groupListSystem").remove();</script>
			<?php
			Template::appendBody(ob_get_clean());
			unset($dataLvl);
		}
		self::$users["printedDiv"] = true;
		ob_start();
		?>
		<input type="hidden" class="usergroup-input" name="<?php echo $input_name?>" id="<?php echo $input_name?>" value="<?php echo $group?>">
		<button level="<?php echo $level_option?>" inputid="<?php echo $input_name?>" onclick="UGLB_SelectGroup($(this));" type="button" class="btn btn-default btn-xs dropdown-toggle">
		<span id="UGLB_<?php echo $input_name?>"><?php echo self::getGroupName($group)?></span> <span class="caret"></span>
		</button>
		<?php
		return(ob_get_clean());
	}

	/**
	 * Get user details
	 * @param string $userID User ID
	 * @return array
	 */
	public static function getDetails($userID){
		$s = [];
		$s['email'] = Database::read("app_users_list","email","id",$userID);
		$s['phone'] = Database::read("app_users_list","phone","id",$userID);
		$s['lang'] = Database::read("app_users_list","lang","id",$userID);
		$s['name'] = Database::read("app_users_list","name","id",$userID);
		$s['group'] = Database::read("app_users_list","group","id",$userID);
		return $s;
	}

	/**
	 * Check if user exists or not
	 * @param string $userID User ID
	 * @return bool
	 */
	public static function isUserExists($userID){
		return(Database::read("app_users_list","id","id",$userID) == $userID);
	}

	/**
	 * Add login session
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
			Accounts::addSession($userid);
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
	 * @param string $auth_level USER_AUTH_SU, USER_AUTH_EMPLOYEE, USER_AUTH_REGISTERED, USER_AUTH_PUBLIC
	 * @return bool
	 */
	public static function authAccess($auth_level){
		//On CLI, user always authenticated as USER_AUTH_SU
		if(defined("__POSCLI")) return true;

		if($_SESSION['account']['loggedIn'] == 0){
			return(USER_AUTH_PUBLIC <= $auth_level);
		}else{
			$usr_group = $_SESSION["account"]["group"];
			$group_level = Database::read("app_users_grouplist","level","id",$usr_group);
			if($group_level == "") $group_level = USER_AUTH_PUBLIC;
			return($group_level <= $auth_level);
		}
	}

	/**
	 * Compare user authentication with group
	 * @param string $requiredGroup User group ID
	 * @return bool
	 */
	public static function authAccessAdvanced($requiredGroup){
		//On CLI, user always authenticated as USER_AUTH_SU
		if(defined("__POSCLI")) return true;

		//If user level > app level, then authAccess
		//If user level = app leve, compare
		$result = false;
		$user_level = self::getAuthLevel($_SESSION['account']['group']);
		if($user_level == self::getAuthLevel($requiredGroup)){
			$result = ($_SESSION['account']['group'] == $requiredGroup);
			if(!$result){
				switch($_SESSION['account']['group']){
					case 0:
					case 1:
					case 2:
					case 3:
						$result = true;
						break;
					default:
				}
			}
		}else{
			$result = (self::authAccess(self::getAuthLevel($requiredGroup)));
		}
		return($result);
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
