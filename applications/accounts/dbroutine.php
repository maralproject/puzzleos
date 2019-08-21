<?php
#Creating Admin
if (file_exists(__ROOTDIR . "/create.admin")) {
    $ctn = unserialize(base64_decode(file_get_contents(__ROOTDIR . "/create.admin")));
    if ($ctn !== false) {
        unlink(__ROOTDIR . "/create.admin");
        $u = PuzzleUser::create($ctn["password"], "Administrator", $ctn["username"]);
        $u->group = PuzzleUserGroup::getRootByLevel(USER_AUTH_SU);
        $u->save();
    }
}

#Deleting TFA, and inactive user
CronJob::register("tfa_remover", function () {
    Database::execute("DELETE FROM app_users_otp where time < (UNIX_TIMESTAMP() - 600)");
    Database::execute("DELETE FROM app_users_list where enabled = 0 and registered_time < (UNIX_TIMESTAMP() - 300)");
}, _CT()->interval(5 * T_MINUTE));

#Setting Session
PuzzleSession::get()->retain_on_same_pc = PuzzleUserConfig::enableRememberMe();
PuzzleSession::get()->share_on_subdomain = PuzzleUserConfig::shareSessionToSubdomain();
PuzzleSession::get()->expire = 30 * T_DAY;
