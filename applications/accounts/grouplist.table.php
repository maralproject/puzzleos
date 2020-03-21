<?php

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

/* This file defines, and update the structure of table `app_users_grouplist` */
/* Warning! This table will affect the app_security, and app_users_list */

$table = new DatabaseTableBuilder;

$table->addColumn("id", "INT")->setAsPrimaryKey()->auto_increment();
$table->addColumn("name", "TEXT");
$table->addColumn("level", "TINYINT");
$table->addColumn("system", "TINYINT(1)")->defaultValue(0);

$table->insertFresh([
    ["name" => "Superuser", "level" => 0, "system" => 1],
    ["name" => "Employee", "level" => 1, "system" => 1],
    ["name" => "Registered", "level" => 2, "system" => 1],
    ["name" => "Public", "level" => 3, "system" => 1]
]);

return $table;
