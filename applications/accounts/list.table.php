<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

/* This file defines, and update the structure of table `app_users_list` */
$table = new DatabaseTableBuilder;

$table->addColumn("id", "INT")->setAsPrimaryKey()->auto_increment();
$table->addColumn("group", "INT");
$table->addColumn("name");
$table->addColumn("email", "VARCHAR(50)")->allowNull();
$table->addColumn("phone", "VARCHAR(20)")->allowNull();
$table->addColumn("lang", "VARCHAR(10)");
$table->addColumn("password", "VARCHAR(60)");
$table->addColumn("tfa", "TINYINT(1)")->defaultValue(0);
$table->addColumn("totp_tfa", "CHAR(16)")->allowNull();
$table->addColumn("enabled", "TINYINT(1)")->defaultValue(1);
$table->addColumn("registered_time", "INT")->defaultValue(0);

$table->createIndex("email", ["email"], "UNIQUE");
$table->createIndex("phone", ["phone"], "UNIQUE");
$table->createIndex("totp_tfa", ["totp_tfa"], "UNIQUE");
$table->createIndex("registered_time", ["registered_time"]);
$table->createIndex("f", ["enabled", "registered_time"]);
$table->createIndex("group", ["group"]);

return $table;
