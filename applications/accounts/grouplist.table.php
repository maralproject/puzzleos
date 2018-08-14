<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.1
 */

/* This file defines, and update the structure of table `app_users_grouplist` */
/* Warning! This table will affect the app_security, and app_users_list */

$table = new DatabaseTableBuilder;

$table->addColumn("id","INT")->setAsPrimaryKey()->defaultValue("AUTO_INCREMENT");
$table->addColumn("name", "TEXT");
$table->addColumn("level", "INT");
$table->addColumn("system", "INT(1)")->defaultValue("0");

$table->newInitialRow("Superuser",0,1);
$table->newInitialRow("Employee",1,1);
$table->newInitialRow("Registered",2,1);
$table->newInitialRow("Public",3,1);

return $table;
?>