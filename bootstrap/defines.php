<?php
define("APP_DEFAULT", 1);
define("APP_NOT_DEFAULT", 0);
define("APP_CANNOT_DEFAULT", 3);

define("T_DAY", 86400);
define("T_HOUR", 3600);
define("T_MINUTE", 60);
define("TODAY", strtotime(date("Y/m/d", time())));

define("__WORKERDIR", __ROOTDIR . "/storage/worker");
?>