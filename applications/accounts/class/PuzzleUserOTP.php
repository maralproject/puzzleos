<?php

use PuzzleUserException\FailedToSendOTP;
use SuperClosure\Analyzer\TokenAnalyzer;
use SuperClosure\Serializer;

class PuzzleUserOTP
{
    private static $_s;
    private static $_se;

    private static function sendSMS($code, PuzzleUser $u, $override = null)
    {
        if (!self::$_s) return false;
        $a = self::$_s;
        return (bool) $a($code, $u, $override ?? $u->phone);
    }

    private static function sendEmail($code, PuzzleUser $u, $override = null)
    {
        if (self::$_se) {
            $a = self::$_se;
            return (bool) $a($code, $u, $override ?? $u);
        } else {
            $w = new Worker;
            $w->setTask(function ($id, $app) use ($u, $code, $override) {
                new Application("phpmailer");
                // $language = new Language($app);
                $mailer = new Mailer;
                $mailer->addRecipient = $override ?? $u->email;
                $mailer->subject = "Two-Factor Authentication";
                $mailer->body = "Hi, {$u->fullname}!\n\nYour verification code is $code. This code is SECRET. Keep it secure and safe!\nYou can ignore this e-mail if you don't make this request.\n\n" . __SITENAME . ".";
                return $mailer->sendPlain();
            })->run(["standalone" => true]);
            return true;
        }
    }

    private static function addDB(\Closure $callback, $session, $code, PuzzleUser $u, bool $usetotp = false)
    {
        Database::insert("app_users_otp", [
            DRI()->setField("hash", $session)
                ->setField("code", $code)
                ->setField("totp", $usetotp ? 1 : 0)
                ->setField("user", $u->id)
                ->setField("time", time())
                ->setField("callback", (new Serializer(new TokenAnalyzer()))->serialize($callback))
        ]);
    }

    /**
     * Generate and send OTP to this user
     * either by e-mail or SMS.
     * 
     * @param string $method email|sms
     * @return string Called the "session_hash" which can be used to execute the callback
     */
    public static function generate(PuzzleUser $u, \Closure $callback, string $method = null, string $recipient = null, bool $shorten_recp = false, bool $force_totp = false)
    {
        $session = rand_str(32);

        #See OTPNote.txt for details
        $i = ((int) ($u->tfa) << 2 | PuzzleUserConfig::TFAMethod() << 1 | (int) ($method !== null && $recipient !== null));
        switch ($i) {
            case 2:
                if (!$force_totp) {
                    $r = 1;
                    break;
                }
            case 6:
                $r = 2;
                break;
            case 0:
            case 1:
            case 3:
            case 4:
            case 5:
            case 7:
                $r = 1;
                break;
        }

        if ($r == 1) {
            $code = rand_str(6, "9012345678");
            return Database::transaction(function () use ($callback, $u, $session, $code, $method, $recipient, $shorten_recp) {
                self::addDB($callback, $session, $code, $u);
                if (($method === "sms" && $recipient != "") || (PuzzleUserConfig::phoneRequired() && self::$_s && $u->phone != "")) {
                    if ($method == "sms") $recipient = PuzzleUser::getE164($recipient);
                    if (self::sendSMS($code, $u, $method == "sms" ? $recipient : NULL)) {
                        $recp = $method == "sms" ? $recipient : $u->phone;
                        if ($shorten_recp) $recp = "••••" . substr($recp, -4);
                        return [
                            "hash" => $session,
                            "method" => "sms",
                            "recipient" => $recp
                        ];
                    } else {
                        if ($method === "sms") throw new FailedToSendOTP("Cannot send verification code to the user using SMS");
                    }
                }
                if (($method === "email" && $recipient != "") || (PuzzleUserConfig::emailRequired() && $u->email != "")) {
                    if ($method === "email" && !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidField("Email you entered is invalid.");
                    }
                    if (self::sendEmail($code, $u, $method == "email" ? $recipient : NULL)) {
                        $recp = $method == "email" ? $recipient : $u->email;
                        if ($shorten_recp) {
                            $split = explode('@', $recp);
                            $recp = substr($split[0], 0, 4) . "••••@" . substr($split[1], 0, 4) . "••••";
                        }
                        return [
                            "hash" => $session,
                            "method" => "email",
                            "recipient" => $recp
                        ];
                    }
                }
                throw new FailedToSendOTP("Cannot send verification code to the user");
            });
        } else if ($r == 2) {
            #Only for TFA
            return Database::transaction(function () use ($callback, $session, $u) {
                self::addDB($callback, $session, null, $u, true);
                return [
                    "hash" => $session,
                    "method" => "totp",
                    "recipient" => null
                ];
            });
        } else {
            throw new FailedToSendOTP("Cannot send verification code to the user");
        }
    }

    /**
     * Verify this user and do the requested callback
     * @return bool
     */
    public static function verify(string $session_hash, string $code, ...$args)
    {
        //No brute force attack on verify!, max 10 request per minute.
        if (!$_SESSION["_puotpca"]) $_SESSION["_puotpca"] = [time(), 0];
        else {
            if ($_SESSION["_puotpca"][0] + T_MINUTE < time()) {
                // Reset counter
                $_SESSION["_puotpca"] = [time(), 0];
            } else {
                if ($_SESSION["_puotpca"][1] > 10) abort(429, "Too Much Request");
                else $_SESSION["_puotpca"][1]++;
            }
        }

        //Start verificating
        $otp_session = Database::getRow("app_users_otp", "hash", $session_hash);
        if (!empty($otp_session)) {
            if ($otp_session["totp"]) {
                if (PuzzleUserGA::verifyCode(PuzzleUser::get($otp_session["user"]), $code, 2)) {
                    $func = (new Serializer(new TokenAnalyzer()))->unserialize($otp_session["callback"]);
                    $func(...$args);
                    Database::delete("app_users_otp", "hash", $session_hash);
                    return true;
                }
            } else {
                if ($otp_session["code"] === $code) {
                    $func = (new Serializer(new TokenAnalyzer()))->unserialize($otp_session["callback"]);
                    $func(...$args);
                    Database::delete("app_users_otp", "hash", $session_hash);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Register function in PuzzleUserOTP to send
     * SMS for Two-Factor Authentication
     * 
     * Callable will receive (string $6digitcode, PuzzleUser $user, string $phone_recipient)
     * Callback expected to return bool
     */
    public static function registerSMSSender(closure $sms_sender)
    {
        self::$_s = $sms_sender;
    }

    /**
     * Register function in PuzzleUserOTP to send
     * E-mail for Two-Factor Authentication
     * 
     * Callable will receive (string $6digitcode, PuzzleUser $user, string $email_recipient)
     * Callback expected to return bool
     */
    public static function registerEmailSender(closure $email_sender)
    {
        self::$_se = $email_sender;
    }
}
