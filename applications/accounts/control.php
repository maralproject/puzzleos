<?php

/**
 * If custom page is requested by other app,
 * then do not ever invoke this page!
 */

use PuzzleUserException\FailedToSendOTP;
use PuzzleUserException\InvalidField;

if ($a = PuzzleUser::getCustomPageURL()) redirect($a);

if (m() == "POST") {
    // Check for CSRF
    // @follow standard from Laravel https://laravel.com/docs/5.8/csrf
    if ($_SERVER["HTTP_X_CSRF_TOKEN"] !== session_id()) {
        if ($_POST["_token"] !== session_id()) {
            // Missing CSRF! Abort Request!
            abort(400, "CSRF Token is missing");
        }
    }

    // Do the actual request
    try {
        if (PuzzleUser::check()) {
            if (PuzzleUser::isAccess(USER_AUTH_SU)) {
                switch (request(1)) {
                    case "gulist":
                        json_out(PuzzleUser::getList());
                    case "cudelusr":
                        $u = PuzzleUser::get($_POST["uid"]);
                        json_out($u->delete());
                    case "cupass":
                        $u = PuzzleUser::get($_POST["uid"]);
                        json_out($u->changePassword($_POST["passnew"]));
                    case "cuname":
                        $u = PuzzleUser::get($_POST["uid"]);
                        $u->fullname = $_POST["val"];
                        json_out($u->save());
                    case "cuphone":
                        $u = PuzzleUser::get($_POST["uid"]);
                        $u->phone = $_POST["val"];
                        json_out($u->save() ? $u->phone : false);
                    case "cumail":
                        $u = PuzzleUser::get($_POST["uid"]);
                        $u->email = $_POST["val"];
                        json_out($u->save());
                    case "cuenable":
                        $u = PuzzleUser::get($_POST["uid"]);
                        $u->enabled = !$u->enabled;
                        json_out($u->save() && $u->enabled);
                    case "cugroup":
                        $u = PuzzleUser::get($_POST["uid"]);
                        $u->group = PuzzleUserGroup::get($_POST["val"]);
                        json_out($u->save());
                    case "culang":
                        $u = PuzzleUser::get($_POST["uid"]);
                        $u->lang = $_POST["val"];
                        json_out($u->save());
                    case "cudels":
                        $us = json_decode($_POST["usrs"], true);
                        foreach ($us as $uid) {
                            $u = PuzzleUser::get($uid)->delete();
                        }
                        json_out(true);
                    case "cuadd":
                        $u = PuzzleUser::create(
                            $_POST["password"],
                            $_POST["fullname"],
                            $_POST["email"],
                            $_POST["phone"]
                        );
                        $u->enabled = false;
                        json_out(true);
                    case "ggroup":
                        json_out(PuzzleUserGroup::getList());
                    case "cgname":
                        $g = PuzzleUserGroup::get($_POST["uid"]);
                        $g->name = $_POST["val"];
                        json_out(true);
                    case "cglevel":
                        $g = PuzzleUserGroup::get($_POST["uid"]);
                        $g->level = $_POST["val"];
                        json_out(true);
                    case "cgdel":
                        $g = PuzzleUserGroup::get($_POST["uid"]);
                        json_out($g->delete());
                    case "cgadd":
                        json_out(PuzzleUserGroup::create($_POST["name"], USER_AUTH_REGISTERED));
                    case "cgdels":
                        $us = json_decode($_POST["usrs"], true);
                        foreach ($us as $uid) {
                            $u = PuzzleUserGroup::get($uid);
                            if (!$u->system) $u->delete();
                        }
                        json_out(true);
                    case "cop":
                        switch ($_POST["n"]) {
                            case "emailRequired":
                                PuzzleUserConfig::emailRequired($_POST["v"]);
                                break;
                            case "phoneRequired":
                                PuzzleUserConfig::phoneRequired($_POST["v"]);
                                break;
                            case "allowChangeLocale":
                                PuzzleUserConfig::allowChangeLocale($_POST["v"]);
                                break;
                            case "allowUserTFA":
                                PuzzleUserConfig::allowUserTFA($_POST["v"]);
                                break;
                            case "allowRegistration":
                                PuzzleUserConfig::allowRegistration($_POST["v"]);
                                break;
                            case "shareSessionToSubdomain":
                                PuzzleUserConfig::shareSessionToSubdomain($_POST["v"]);
                                break;
                            case "enableRememberMe":
                                PuzzleUserConfig::enableRememberMe($_POST["v"]);
                                break;
                            case "creationRequireActivation":
                                PuzzleUserConfig::creationRequireActivation($_POST["v"]);
                                break;
                            case "recaptchaEnabled":
                                PuzzleUserConfig::recaptchaEnabled($_POST["v"]);
                                break;
                            case "recaptchaSitekey":
                                PuzzleUserConfig::recaptchaSitekey($_POST["v"]);
                                break;
                            case "recaptchaPrivatekey":
                                PuzzleUserConfig::recaptchaPrivatekey($_POST["v"]);
                                break;
                            case "defaultRegistrationGroup":
                                PuzzleUserConfig::defaultRegistrationGroup(PuzzleUserGroup::get($_POST["v"]));
                                break;
                            default:
                                throw new PuzzleError("Invalid action");
                        }
                        json_out(true);
                }
            }
            switch (request(1)) {
                case "cpass":
                    $u = PuzzleUser::active();
                    if ($u->verifyPassword($_POST["passold"]))
                        json_out($u->changePassword($_POST["passnew"]));
                    else json_out(false);
                case "verify":
                    json_out(PuzzleUserOTP::verify($_POST["session"], $_POST["code"]));
                case "profile":
                    $cn = false;
                    $u = PuzzleUser::active();
                    $u->fullname = $_POST["name"];
                    if (!$u->tfa && $_POST["tfa"]) {
                        // Prepare TFA challenge
                        try {
                            $tfa_en = $u->enableTFAWithVerification();
                        } catch (FailedToSendOTP $o) {
                            $tfa_en = false;
                        }
                        $cn = true;
                    } else {
                        $u->tfa = $_POST["tfa"];
                    }
                    if (PuzzleUserConfig::allowChangeLocale()) $u->lang = $_POST["lang"];
                    if (PuzzleUserConfig::emailRequired()) {
                        if ($_POST["email"] != $u->email) {
                            try {
                                $tfa_mail = $u->changeEmailWithVerification($_POST["email"]);
                            } catch (FailedToSendOTP $o) {
                                $tfa_mail = false;
                            }
                            $cn = true;
                        }
                    }
                    if (PuzzleUserConfig::phoneRequired()) {
                        if ($_POST["phone"] != $u->phone) {
                            try {
                                $tfa_phone = $u->changePhoneWithVerification($_POST["phone"]);
                            } catch (FailedToSendOTP $o) {
                                $tfa_phone = false;
                            }
                            $cn = true;
                        }
                    }
                    json_out([
                        "challenge_needed" => $cn,
                        "sms_challenge" => $tfa_phone,
                        "mail_challenge" => $tfa_mail,
                        "tfa_challenge" => $tfa_en,
                        "saved" => $u->save(),
                    ]);
                    break;
            }
        } else {
            switch (request(1)) {
                case "signup2":
                    json_out(PuzzleUserOTP::verify($_POST["session"], $_POST["code"]));
                case "signup1":
                    if (PuzzleUserConfig::recaptchaEnabled()) PuzzleUserRecaptcha::verify();
                    $redirection = filter_var(__SITEURL . '/' . $_POST["redir"], FILTER_VALIDATE_URL);
                    $u = PuzzleUser::create(
                        $_POST["password"],
                        $_POST["fullname"],
                        $_POST["email"],
                        $_POST["phone"]
                    );
                    if (!$u->enabled) {
                        $tfa = PuzzleUserOTP::generate($u, function () use ($u) {
                            $u->enabled = true;
                            $u->save();
                            $u->logMeIn();
                        });
                    } else {
                        $u->logMeIn();
                    };
                    json_out([
                        "need_activation" => $u->enabled == false,
                        "token" => $tfa,
                        "redir" => $redirection ? $_POST["redir"] : NULL
                    ]);
                case "forgot1":
                    if (PuzzleUserConfig::recaptchaEnabled()) PuzzleUserRecaptcha::verify();
                    $u = PuzzleUser::findUserByPhoneEmail($_POST["user"]);
                    $otp = PuzzleUserOTP::generate($u, function ($new_pass) use ($u) {
                        $u->changePassword($new_pass);
                    }, null, null, true);
                    json_out($otp);
                case "forgot2":
                    // Dikasih if biar OTP nya nggak hilang
                    if ($_POST["pass"] == "") throw new InvalidField("Password cannot be empty");
                    json_out(PuzzleUserOTP::verify($_POST["session"], $_POST["code"], $_POST["pass"]));
                case "verify":
                    json_out(PuzzleUserOTP::verify($_POST["session"], $_POST["code"]));
                case "login":
                    if (PuzzleUserConfig::recaptchaEnabled()) PuzzleUserRecaptcha::verify();
                    $redirection = filter_var(__SITEURL . '/' . $_POST["redir"], FILTER_VALIDATE_URL);
                    $user = PuzzleUser::findUserByPhoneEmail($_POST["user"]);
                    if (!$user->verifyPassword($_POST["pass"])) abort(401, "Wrong credentials");
                    if (!$user->enabled) abort(401, "Account is not activated");
                    if (PuzzleUserConfig::allowUserTFA() && $user->tfa) {
                        $token = PuzzleUserOTP::generate($user, function () use ($user) {
                            $user->logMeIn();
                        }, null, null, true);
                        json_out([
                            "challenge_needed" => true,
                            "token" => $token,
                            "loggedin" => false,
                            "redir" => $redirection ? $_POST["redir"] : NULL
                        ]);
                    } else {
                        $user->logMeIn();
                        json_out([
                            "challenge_needed" => false,
                            "loggedin" => true,
                            "redir" => $redirection ? $_POST["redir"] : NULL
                        ]);
                    }
                    break;
            }
        }
    } catch (Throwable $e) {
        abort(400, $e->getMessage(), false);
        json_out([
            "error" => true,
            "reason" => $e->getMessage()
        ]);
    }
}

$appProp->bundle["language"] = new Language();
$appProp->bundle["_csrf"] = function () {
    echo '<input type="hidden" name="_token" value="' . session_id() . '">';
};

if (PuzzleUser::check()) {
    switch (request(1)) {
        case "logout":
            PuzzleUser::logout();
            redirect("/");
        default:
            Template::setSubTitle("My Profile");
            $appProp->bundle["view"] = "profile.php";
    }
} else {
    switch (request(1)) {
        case "forgot":
            Template::setSubTitle("Forgot Password");
            $appProp->bundle["view"] = "reset_pass.php";
            break;
        case "signup":
            if (PuzzleUserConfig::allowRegistration()) {
                Template::setSubTitle("Sign Up");
                $appProp->bundle["view"] = "signup.php";
                break;
            }
        default:
            Template::setSubTitle("Log In");
            $appProp->bundle["view"] = "login.php";
    }
}
