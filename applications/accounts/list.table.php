<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.0
 */

/* This file defines, and update the structure of table `app_users_list` */
$table = new DatabaseTableBuilder;

$table->addColumn("id","INT")->setAsPrimaryKey()->defaultValue("AUTO_INCREMENT");
$table->addColumn("group","INT");
$table->addColumn("name");
$table->addColumn("email")->allowNull(true);
$table->addColumn("phone")->allowNull(true);
$table->addColumn("lang");
$table->addColumn("password");
$table->addColumn("username","VARCHAR(50)");
$table->addColumn("enabled","INT(1)")->defaultValue(1);
$table->addColumn("registered_time","INT")->defaultValue(0);

/* 
 * Warning! This is the default user credentials!
 * Do not remove the app_users_list table, or this will compromise the security
 * Username: admin
 * Password: admin
 */
//$table->newInitialRow(1,"Administrator","","","def",password_hash("admin", PASSWORD_BCRYPT),"admin",1,0);
//Let installation do the creation of user

return $table;
?>