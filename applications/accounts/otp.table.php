<?php
return DTB()
    ->addColumn("hash", "CHAR(32)")->setAsPrimaryKey()
    ->addColumn("user", "INT")
    ->addColumn("callback")
    ->addColumn("code", "CHAR(6)")->allowNull()
    ->addColumn("totp", "TINYINT(1)")->defaultValue(0)
    ->addColumn("time", "INT")

    ->createIndex("hash", ["hash"], "UNIQUE");
