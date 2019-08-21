<?php

use PuzzleUserException\GroupNotFound;
use PuzzleUserException\MissingField;

/**
 * @property-read int $id
 * @property string $name
 * @property int $level
 * @property-read bool $system
 */
class PuzzleUserGroup implements JsonSerializable
{
    private static $singleton = [];

    private $id;
    private $name;
    private $level;
    private $system;

    private $_destructed = false;

    private function __construct()
    { }

    public function jsonSerialize()
    {
        if ($this->_destructed) throw new Exception("Account was deleted");
        return [
            "id" => (int) $this->id,
            "name" => $this->name,
            "level" => (int) $this->level,
            "system" => (bool) $this->system,
        ];
    }

    public function __set($name, $value)
    {
        if ($this->_destructed) throw new Exception("Account was deleted");
        if ($this->system) throw new PuzzleError("System group cannot be altered.");
        switch ($name) {
            case "name":
                if ($value == "") throw new MissingField("Group name cannot be empty");
                if (Database::update("app_users_grouplist", DRI()->setField("name", $value), "id", $this->id)) {
                    $this->name = $value;
                }
                break;
            case "level":
                switch ((int) $value) {
                    case USER_AUTH_SU:
                    case USER_AUTH_EMPLOYEE:
                    case USER_AUTH_REGISTERED:
                    case USER_AUTH_PUBLIC:
                        break;
                    default:
                        throw new PuzzleError("Invalid Group Level!");
                }
                if (Database::update("app_users_grouplist", DRI()->setField("level", $value), "id", $this->id)) {
                    $this->level = (int) $value;
                }
                break;
        }
    }

    public function __get($name)
    {
        if ($this->_destructed) throw new Exception("Account was deleted");
        switch ($name) {
            case "id":
                return (int) $this->id;
            case "name":
                return $this->name;
            case "level":
                return (int) $this->level;
            case "system":
                return (bool) $this->system;
        }
    }

    public function delete()
    {
        if ($this->_destructed) throw new Exception("Account was deleted");
        if ($this->system) throw new PuzzleError("System group cannot be deleted.");
        return Database::transaction(function () {
            if (Database::delete("app_users_grouplist", "id", $this->id)) {
                Database::update(
                    "app_users_list",
                    DRI()->setField("group", self::getRootByLevel(USER_AUTH_REGISTERED)->id),
                    "group",
                    $this->id
                );
                return $this->_destructed = true;
            }
        });
    }

    public static function get(int $id)
    {
        if (self::$singleton[$id]) return self::$singleton[$id];
        if (empty($row = Database::getRow("app_users_grouplist", "id", $id))) {
            throw new GroupNotFound("PuzzleUserGroup with id $id not found");
        }
        $su = new self;
        $su->id = (int) $row["id"];
        $su->name = $row["name"];
        $su->level = (int) $row["level"];
        $su->system = (bool) $row["system"];
        return self::$singleton[$id] = $su;
    }

    public static function __preloadDefault()
    {
        if (is_callbyme()) {
            $su = new self;
            $su->id = 1;
            $su->name = "Superuser";
            $su->level = 0;
            $su->system = true;
            $e = new self;
            $e->id = 2;
            $e->name = "Employee";
            $e->level = 1;
            $e->system = true;
            $r = new self;
            $r->id = 3;
            $r->name = "Registered";
            $r->level = 2;
            $r->system = true;
            $p = new self;
            $p->id = 4;
            $p->name = "Public";
            $p->level = 3;
            $p->system = true;
            self::$singleton = [1 => $su, 2 => $e, 3 => $r, 4 => $p];
        }
    }

    /**
     * Get system group id from USER_AUTH type
     * 
     * @param int $level Selected authentication type, use "USER_AUTH_*" constant!
     * @return self
     */
    public static function getRootByLevel(int $level)
    {
        switch ($level) {
            case USER_AUTH_SU:
                return self::get(1);
            case USER_AUTH_EMPLOYEE:
                return self::get(2);
            case USER_AUTH_REGISTERED:
                return self::get(3);
            case USER_AUTH_PUBLIC:
                return self::get(4);
            default:
                throw new PuzzleError("Invalid Level!");
        }
    }

    public static function getList()
    {
        $db = Database::execute("select id from app_users_grouplist");
        $su = [];
        $employee = [];
        $registered = [];
        $public = [];
        while ($row = $db->fetch_row()) {
            $d = self::get($row[0]);
            switch ($d->level) {
                case USER_AUTH_SU:
                    $su[] = $d;
                    break;
                case USER_AUTH_EMPLOYEE:
                    $employee[] = $d;
                    break;
                case USER_AUTH_REGISTERED:
                    $registered[] = $d;
                    break;
                case USER_AUTH_PUBLIC:
                    $public[] = $d;
                    break;
            }
        }
        return [
            USER_AUTH_SU => $su,
            USER_AUTH_EMPLOYEE => $employee,
            USER_AUTH_REGISTERED => $registered,
            USER_AUTH_PUBLIC => $public
        ];
    }

    /**
     * Create new Group
     * @return self
     */
    public static function create(string $name, int $level)
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
        if ($name == "") throw new MissingField("Group name cannot be empty");
        if (Database::insert("app_users_grouplist", [
            DRI()
                ->setField("name", $name)
                ->setField("level", $level)
                ->setField("system", 0)
        ])) {
            $id = (int) Database::lastId();
            return self::get($id);
        }
    }
}

PuzzleUserGroup::__preloadDefault();
