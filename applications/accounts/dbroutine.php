<?php
#Creating Admin
if (file_exists(__ROOTDIR . "/create.admin")) {
    try {
        $ctn = unserialize(base64_decode(file_get_contents(__ROOTDIR . "/create.admin")));
        if ($ctn !== false) {
            $u = PuzzleUser::create($ctn["password"], "Administrator", $ctn["username"]);
            $u->group = PuzzleUserGroup::getRootByLevel(USER_AUTH_SU);
            $u->enabled = true;
            $u->save();
            unlink(__ROOTDIR . "/create.admin");
        }
    } catch (DatabaseError $e) {
        // Table is not migrated yet. Try again on the next reload
    }
}

#Deleting TFA, and inactive user
if (is_cli()) {
    CronJob::register("tfa_remover", function () {
        Database::execute("DELETE FROM app_users_otp where time < (UNIX_TIMESTAMP() - 600)");
        Database::execute("DELETE FROM app_users_list where enabled = 0 and registered_time < (UNIX_TIMESTAMP() - 300)");
    }, _CT()->interval(5 * T_MINUTE));
}

#Setting Session
PuzzleSession::config("retain_on_same_pc", PuzzleUserConfig::enableRememberMe());
PuzzleSession::config("share_on_subdomain", PuzzleUserConfig::shareSessionToSubdomain());
PuzzleSession::config("expire_time",  7 * T_DAY);
