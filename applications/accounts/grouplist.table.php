<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/* This file defines, and update the structure of table `app_users_grouplist` */
/* Warning! This table will affect the app_security, and app_users_list */

$table = new DatabaseTableBuilder;

$table->addColumn("id","INT")->setAsPrimaryKey()->defaultValue("AUTO_INCREMENT");
$table->addColumn("name", "TEXT");
$table->addColumn("level", "INT");
$table->addColumn("system", "INT(1)")->defaultValue("0");

$table->insertFresh([
    (new DatabaseRowInput)->setField("name","Superuser")->setField("level",0)->setField("system",1),
    (new DatabaseRowInput)->setField("name","Employee")->setField("level",1)->setField("system",1),
    (new DatabaseRowInput)->setField("name","Registered")->setField("level",2)->setField("system",1),
    (new DatabaseRowInput)->setField("name","Public")->setField("level",3)->setField("system",1)
]);

return $table;