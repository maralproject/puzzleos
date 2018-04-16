<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.3") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */

/* Table app_users_list require app_users_grouplist. Build one if not exists */
if(Database::isTableExist("app_users_grouplist")) 
	Database::newStructure("app_users_grouplist",require("grouplist.table.php"));

/* This file defines, and update the structure of table `app_users_list` */
$table = new DatabaseTableBuilder;

$table->addColumn("id","INT")->setAsPrimaryKey()->defaultValue("AUTO_INCREMENT");
$table->addColumn("group","INT");
$table->addColumn("name");
$table->addColumn("email");
$table->addColumn("phone");
$table->addColumn("lang");
$table->addColumn("password");
$table->addColumn("username");
$table->addColumn("enabled","INT(1)")->defaultValue(1);

/* 
 * Warning! This is the default user credentials!
 * Do not remove the app_users_list table, or this will compromise the security
 * Username: admin
 * Password: admin
 */
$table->newInitialRow(Database::read("app_users_grouplist","id","level",0),"Administrator","","","def",password_hash("admin", PASSWORD_BCRYPT),"admin");

return $table;
?>