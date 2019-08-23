<?php

use PuzzleUserException\MissingField;
use PuzzleUserException\InvalidField;
use PuzzleUserException\UserNotFound;
use Automattic\Phone\Mobile_Validator;

define("USER_AUTH_SU", 0);
define("USER_AUTH_EMPLOYEE", 1);
define("USER_AUTH_REGISTERED", 2);
define("USER_AUTH_PUBLIC", 3);

/**
 * This class is successor of Accounts class.
 * It have a better structure, and easy to maintain.
 * @property-read int $id
 * @property PuzzleUserGroup $group
 * @property string $fullname
 * @property string $lang
 * @property bool $enabled
 * @property bool $tfa
 * @property-read int $registered_time
 * @property-read string $totp_tfa
 * @property string $email
 * @property string $phone
 */
class PuzzleUser implements JsonSerializable
{
    private static $postLoginFunction = [];
    private static $_l;
    private static $_p;

    private static $singleton = [];
    private static $loggedIn = null;

    #region Variables
    private $id;
    private $group;
    private $fullname;

    private $lang;
    private $enabled;
    private $registered_time;
    private $tfa;
    private $totp_tfa;

    private $email;
    private $phone;
    private $password_hashed;

    private $_destroyed = false;
    #endregion

    #region Private
    private static function bcrypt(string $pass)
    {
        return password_hash($pass, PASSWORD_BCRYPT);
    }

    private static function loadSession()
    {
        if ($_SESSION["_acc"]) {
            try {
                $u = self::get($_SESSION["_acc"]);
                if ($u->enabled) self::$loggedIn = $u;
            } catch (UserNotFound $e) {
                self::$loggedIn = null;
                self::saveSession();
            }
        }
    }

    private static function saveSession()
    {
        $_SESSION["_acc"] = self::$loggedIn ? self::$loggedIn->id : null;
    }

    private function __construct(int $id)
    {
        if (!empty($row = Database::getRow("app_users_list", "id", $id))) {
            $this->id = (int) $id;
            $this->group = PuzzleUserGroup::get($row["group"]);
            $this->fullname = $row["name"];
            $this->email = $row["email"] != "" ? $row["email"] : NULL;
            $this->phone = $row["phone"] != "" ? $row["phone"] : NULL;
            $this->lang = $row["lang"];
            $this->password_hashed = $row["password"];
            $this->enabled = (bool) $row["enabled"];
            $this->tfa = (bool) $row["tfa"];
            $this->totp_tfa = $row["totp_tfa"];
            $this->registered_time = (int) $row["registered_time"];
        } else
            throw new UserNotFound("PuzzleUser with id $id not found.");
    }
    #endregion

    #region Public
    /**
     * Reset or make new TOTP Secret for this user
     */
    public function resetTOTPSecret()
    {
        $_16charSecret = PuzzleUserGA::createSecret();
        if (Database::update(
            "app_users_list",
            (new DatabaseRowInput)->setField("totp_tfa", $_16charSecret),
            "id",
            $this->id
        )) {
            return $this->totp_tfa = $_16charSecret;
        }
        return false;
    }

    public function delete()
    {
        if (self::check() && self::active() == $this) return false;
        if (Database::delete("app_users_list", "id", $this->id)) {
            return $this->_destroyed = true;
        }
        return false;
    }

    public function jsonSerialize()
    {
        if ($this->_destroyed) throw new Exception("Account was deleted");
        return [
            "id" => $this->id,
            "group" => $this->group,
            "fullname" => $this->fullname,
            "lang" => $this->lang,
            "enabled" => $this->enabled,
            "registered_time" => $this->registered_time,
            "tfa" => $this->tfa,
            "email" => $this->email,
            "phone" => $this->phone,
        ];
    }

    public function __get($name)
    {
        if ($this->_destroyed) throw new Exception("Account was deleted");
        switch ($name) {
            case "id":
                return $this->id;
            case "group":
                return $this->group;
            case "fullname":
                return $this->fullname;
            case "lang":
                return $this->lang;
            case "enabled":
                return $this->enabled;
            case "registered_time":
                return $this->registered_time;
            case "tfa":
                return $this->tfa;
            case "totp_tfa":
                return $this->totp_tfa;
            case "email":
                return $this->email;
            case "phone":
                return $this->phone;
        }
    }

    public function __set($name, $value)
    {
        if ($this->_destroyed) throw new Exception("Account was deleted");
        switch ($name) {
            case "group":
                if (!$value instanceof PuzzleUserGroup) throw new InvalidArgumentException("Expected PuzzleUserGroup");
                $this->group = $value;
                break;
            case "fullname":
                if ($value == "") throw new InvalidArgumentException("Fullname cannot be empty");
                $this->fullname = $value;
                break;
            case "lang":
                $locale = require(__ROOTDIR . "/bootstrap/locale.php");
                if (!$locale[$value] && $value != "def") throw new InvalidArgumentException("Expecting language using ISO 639-1 with xx-XX format.");
                $this->lang = $value;
                break;
            case "enabled":
                $this->enabled = (bool) $value;
                break;
            case "tfa":
                $this->tfa = (bool) $value;
                break;
            case "email":
                if ($value == "" && PuzzleUser::isAccess(USER_AUTH_SU)) {
                    $this->email = null;
                } else {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidField("Email you entered is invalid.");
                    }
                    $this->email = strtolower($value);
                }
                break;
            case "phone":
                if ($value == "" && PuzzleUser::isAccess(USER_AUTH_SU)) {
                    $this->phone = null;
                } else {
                    $this->phone = self::getE164($value);
                }
                break;
        }
    }

    public function save()
    {
        if ($this->_destroyed) throw new Exception("Account was deleted");
        if (!self::isAccess(USER_AUTH_SU)) {
            // Check for pre-required form
            if (PuzzleUserConfig::emailRequired() && $this->email == "") {
                throw new MissingField("Email is required to be filled");
            }

            if (PuzzleUserConfig::phoneRequired() && $this->phone == "") {
                throw new MissingField("Phone is required to be filled");
            }
        }
        if ($this->phone == "" && $this->email == "") throw new MissingField("Either e-mail or phone should be filled");
        $g = Database::update("app_users_list", (new DatabaseRowInput)
            ->setField("group", $this->group->id)
            ->setField("name", $this->fullname)
            ->setField("email", $this->email ? strtolower($this->email) : NULL)
            ->setField("phone", $this->phone ?? NULL)
            ->setField("lang", $this->lang)
            ->setField("tfa", $this->tfa ? 1 : 0)
            ->setField("enabled", $this->enabled ? 1 : 0), "id", $this->id);
        if ($g) return true;
        return false;
    }

    /**
     * Force use to login.
     */
    public function logMeIn()
    {
        if ($this->_destroyed) throw new Exception("Account was deleted");
        if (!$this->enabled) return false;
        self::$loggedIn = $this;
        self::saveSession();
        foreach (self::$postLoginFunction as $f) {
            try {
                $f();
            } catch (\Throwable $r) { }
        }
        return true;
    }

    /**
     * Check password
     */
    public function verifyPassword(string $password)
    {
        if ($this->_destroyed) throw new Exception("Account was deleted");
        return password_verify($password, $this->password_hashed);
    }

    /**
     * Change user password
     */
    public function changePassword(string $new_password)
    {
        if ($this->_destroyed) throw new Exception("Account was deleted");
        if ($new_password == "") {
            throw new InvalidField("Password cannot be empty");
        }
        if (Database::update(
            "app_users_list",
            (new DatabaseRowInput)->setField("password", $newpass = self::bcrypt($new_password)),
            "id",
            $this->id
        )) {
            $this->password_hashed = $newpass;
            PuzzleSession::endUser($this->id);
            return $this->save();
        }
        return false;
    }

    public function changeEmailWithVerification(string $new_email)
    {
        if ($this->_destroyed) throw new Exception("Account was deleted");
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidField("Email you entered is invalid.");
        }
        return PuzzleUserOTP::generate($this, function () use ($new_email) {
            $this->email = strtolower($new_email);
            $this->save();
        }, "email", $new_email);
    }

    public function changePhoneWithVerification(string $new_phone)
    {
        if ($this->_destroyed) throw new Exception("Account was deleted");
        $new_phone = self::getE164($new_phone);
        return PuzzleUserOTP::generate($this, function () use ($new_phone) {
            $this->phone = $new_phone;
            $this->save();
        }, "sms", $new_phone);
    }

    public function enableTFAWithVerification()
    {
        if ($this->_destroyed) throw new Exception("Account was deleted");
        if ($this->tfa) return false;
        return PuzzleUserOTP::generate($this, function () {
            $this->tfa = true;
            $this->save();
        }, null, null, false, true);
    }
    #endregion

    #region Public Static
    public static function getE164(string $phone)
    {
        $_phone = Mobile_Validator::normalize($phone);
        if (empty($_phone)) $_phone = Mobile_Validator::normalize($phone, "ID");
        if (empty($_phone)) {
            throw new InvalidField("Cannot find any match country for this phone number.");
        } else {
            return $_phone[0];
        }
    }

    public static function logout()
    {
        self::$loggedIn = null;
        self::saveSession();
    }

    /**
     * @return self
     */
    public static function get(int $id)
    {
        return self::$singleton[$id] ?? self::$singleton[$id] = new self($id);
    }

    /** 
     * Find user using email or phone
     */
    public static function findUserByPhoneEmail(string $haystack)
    {
        if ($haystack == "") throw new UserNotFound("User not found");
        if (filter_var($haystack, FILTER_VALIDATE_EMAIL)) {
            // Find using email
            $userid = Database::read("app_users_list", "id", "email", strtolower($haystack));
        } else {
            // Find using phone
            try {
                $phone = self::getE164($haystack);
                $userid = Database::read("app_users_list", "id", "phone", $phone);
            } catch (InvalidField $e) { }
        }
        if ($userid) {
            return self::get((int) $userid);
        } else {
            throw new UserNotFound("Cannot find any matched account");
        }
    }

    /**
     * Get user that is logged in
     * @return self when success
     * @return null when no one was logged-in.
     */
    public static function active()
    {
        return self::$loggedIn;
    }

    /**
     * Count the number of registered user
     */
    public static function count()
    {
        return Database::execute("SELECT count(1) from app_users_list")->fetch_row()[0];
    }

    /**
     * Check if current user is logged in.
     * @return bool
     */
    public static function check()
    {
        return self::$loggedIn != null;
    }

    /**
     * Check if access level is allowed or not.
     * @return bool
     */
    public static function isAccess(int $access)
    {
        //On CLI, user always authenticated as USER_AUTH_SU
        if (is_cli() && defined("__POSCLI")) return true;

        if (!self::check()) {
            return ($access >= USER_AUTH_PUBLIC);
        } else {
            return (self::active()->group->level <= $access);
        }
    }

    /**
     * Check if this group is allowed to access
     */
    public static function isGroupAccess(PuzzleUserGroup $group)
    {
        //On CLI, user always authenticated as USER_AUTH_SU
        if (is_cli() && defined("__POSCLI")) return true;

        $level_required = $group->level;
        $imaginary_user_group = self::check() ? self::active()->group : PuzzleUserGroup::getRootByLevel(USER_AUTH_PUBLIC);
        $level_user = $imaginary_user_group->level;

        if ($level_user == $level_required) {
            switch ($group->id) {
                case 1:
                case 2:
                case 3:
                    return true;
                default:
                    switch ($imaginary_user_group->id) {
                        case 1:
                        case 2:
                        case 3:
                            return true;
                        default:
                            return ($imaginary_user_group->id == $group->id);
                    }
            }
        } else {
            return ($level_user < $level_required);
        }
    }

    /**
     * Create new user
     * @return self
     */
    public static function create(
        string $password,
        string $fullname,
        string $email = null,
        string $phone = null
    ) {
        if ($email != "") {
            // Validating email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidField("Email you entered is not valid.");
            }
            $email = strtolower($email);
        }

        if ($phone != "") {
            // Validating phone number
            $phone = self::getE164($phone);
        }

        if (PuzzleUserConfig::emailRequired() && $email == "") {
            throw new MissingField("Email is required to be filled");
        }

        if (PuzzleUserConfig::phoneRequired() && $phone == "") {
            throw new MissingField("Phone is required to be filled");
        }

        if ($fullname == "") {
            throw new MissingField(self::$_l->get("NAME_INV_EMPTY"));
        } else if (strlen($fullname) > 50) {
            throw new MissingField(self::$_l->get("NAME_INV_50"));
        }

        $group = PuzzleUserConfig::defaultRegistrationGroup() ?? PuzzleUserGroup::getRootByLevel(USER_AUTH_REGISTERED);
        if (Database::insert("app_users_list", [
            (new DatabaseRowInput)
                ->setField("group", $group->id)
                ->setField("name", $fullname)
                ->setField("email", $email ? strtolower($email) : NULL)
                ->setField("phone", $phone ?? NULL)
                ->setField("lang", "def")
                ->setField("password", self::bcrypt($password))
                ->setField("enabled", PuzzleUserConfig::creationRequireActivation() ? 0 : 1)
                ->setField("registered_time", time() + 600)
        ])) {
            $u = self::get(Database::lastId());
            // $u->resetTOTPSecret();
            return $u;
        }
        return false;
    }

    public static function __prepareEnv()
    {
        if (is_callbyme()) {
            self::loadSession();
            self::$_l = new Language();
        }
    }

    public static function getCustomPageURL()
    {
        return self::$_p;
    }

    public static function getList(int $limit = null, int $start = null)
    {
        if ($limit !== null) {
            $lq = "LIMIT $limit";
            if ($start !== null) {
                $lq .= ",$start";
            }
        }
        $db = Database::execute("select id from app_users_list " . $lq ?? "");
        $a = [];
        while ($row = $db->fetch_row()) {
            $a[] = self::get($row[0]);
        }
        return $a;
    }
    #endregion

    #region User overridable
    /**
     * Register a function to be run right
     * after the user was logged in.
     * 
     * Callable will receive (PuzzleUser)
     */
    public static function registerPostLoginCallback(\closure $method)
    {
        self::$postLoginFunction[] = $method;
    }

    /**
     * Call this function to tell PuzzleUser to use
     * this page instead of default page to serve user functionalities,
     * such as:
     * 
     * - Login
     * - Profile Management
     * - Change Password
     * - Forgot Password
     * 
     * When PuzzleUser myPage is set, PuzzleUser will never load
     * neither controller or view to the user. 
     * 
     * You have to create your own controller.
     */
    public static function useMyPage(string $view_url)
    {
        if (!filter_var($view_url, FILTER_VALIDATE_URL)) throw new InvalidArgumentException("Expecting a valid URL");
        self::$_p = $view_url;
    }
    #endregion
}

PuzzleUser::__prepareEnv();
