<?php
return DTB()
    ->addColumn("hash", "CHAR(32)")->setAsPrimaryKey()
    ->addColumn("code", "CHAR(6)")
    ->addColumn("user", "INT")
    ->addColumn("callback")
    ->addColumn("time", "INT");
