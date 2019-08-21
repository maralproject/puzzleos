<?php

use PuzzleUserException\GroupNotFound;

class PuzzleUserConfig
{
    private static $settings;

    private static function ll()
    {
        self::$settings = unserialize(file_get_contents(storage("config.cnf")));
    }

    private static function load($key)
    {
        if (self::$settings === null) self::ll();
        return self::$settings[$key];
    }

    private static function save($key, $value)
    {
        if (self::$settings === null) self::ll();
        self::$settings[$key] = $value;
        return file_put_contents(storage("config.cnf"), serialize(self::$settings));
    }

    private static function genericSaveLoad($key, $b)
    {
        if ($b === null) {
            return self::load($key);
        } else {
            return self::save($key, $b);
        }
    }

    public static function recaptchaPrivatekey(string $b = null)
    {
        return (string) self::genericSaveLoad(__FUNCTION__, $b);
    }

    public static function recaptchaSitekey(string $b = null)
    {
        return (string) self::genericSaveLoad(__FUNCTION__, $b);
    }

    public static function recaptchaEnabled(bool $b = null)
    {
        return (bool) self::genericSaveLoad(__FUNCTION__, $b);
    }

    public static function creationRequireActivation(bool $b = null)
    {
        return (bool) self::genericSaveLoad(__FUNCTION__, $b);
    }

    public static function emailRequired(bool $b = null)
    {
        $r = (bool) self::genericSaveLoad(__FUNCTION__, $b);
        if (!self::phoneRequired() && !$r) return true;
        return $r;
    }

    public static function phoneRequired(bool $b = null)
    {
        return (bool) self::genericSaveLoad(__FUNCTION__, $b);
    }

    public static function allowRegistration(bool $b = null)
    {
        return (bool) self::genericSaveLoad(__FUNCTION__, $b);
    }

    public static function allowChangeLocale(bool $b = null)
    {
        return (bool) self::genericSaveLoad(__FUNCTION__, $b);
    }

    public static function enableRememberMe(bool $b = null)
    {
        return (bool) self::genericSaveLoad(__FUNCTION__, $b);
    }

    public static function shareSessionToSubdomain(bool $b = null)
    {
        return (bool) self::genericSaveLoad(__FUNCTION__, $b);
    }

    /**
     * Not every user will have to do TFA challenge.
     * But TFA option will be in their account.
     */
    public static function allowUserTFA(bool $b = null)
    {
        return (bool) self::genericSaveLoad(__FUNCTION__, $b);
    }

    /**
     * @return PuzzleUserGroup if set
     * @return null if no config
     */
    public static function defaultRegistrationGroup(PuzzleUserGroup $g = null)
    {
        $r = self::genericSaveLoad(__FUNCTION__, $g->id);
        try {
            return PuzzleUserGroup::get($r);
        } catch (Throwable $e) {
            return PuzzleUserGroup::getRootByLevel(USER_AUTH_REGISTERED);
        }
    }
}
