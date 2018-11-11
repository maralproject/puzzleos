<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * This file responsible for creating system table structure
 */

/* Table `userdata` */
$a = new DatabaseTableBuilder;
$a->addColumn("app", "VARCHAR(50)");
$a->addColumn("identifier", "VARCHAR(500)");
$a->addColumn("physical_path", "VARCHAR(500)");
$a->addColumn("mime_type", "VARCHAR(100)");
$a->addColumn("ver", "INT");
$a->addColumn("secure", "TINYINT(1)");

$a->createIndex("main", ["app", "identifier"]);
$a->createIndex("path", ["physical_path"]);

Database::newStructure("userdata", $a);

/* Table `multidomain_config` */
$a = new DatabaseTableBuilder;
$a->addColumn("host", "VARCHAR(50)")->setAsPrimaryKey();
$a->addColumn("default_app", "VARCHAR(50)");
$a->addColumn("default_template", "VARCHAR(50)");
$a->addColumn("restricted_app");

$a->insertFresh([
    (new DatabaseRowInput)
    ->setField("host","{root}")
    ->setField("default_app","admin")
    ->setField("default_template","blank")
    ->setField("restricted_app","[]")
]);

Database::newStructure("multidomain_config", $a);

/* Table `app_security` */
$a = new DatabaseTableBuilder;
$a->addColumn("rootname", "VARCHAR(50)")->setAsPrimaryKey();
$a->addColumn("group", "INT")->allowNull(true);
$a->addColumn("system", "INT")->defaultValue("0");

$a->insertFresh([
    (new DatabaseRowInput)->setField("rootname","admin")->setField("system",1),
    (new DatabaseRowInput)->setField("rootname","bootstrap")->setField("system",1),
    (new DatabaseRowInput)->setField("rootname","fontawesome")->setField("system",1),
    (new DatabaseRowInput)->setField("rootname","menus")->setField("system",1),
    (new DatabaseRowInput)->setField("rootname","page_control")->setField("system",1),
    (new DatabaseRowInput)->setField("rootname","phpmailer")->setField("system",1),
    (new DatabaseRowInput)->setField("rootname","search_box")->setField("system",1),
    (new DatabaseRowInput)->setField("rootname","tinymce")->setField("system",1),
    (new DatabaseRowInput)->setField("rootname","upload_img_ajax")->setField("system",1),
    (new DatabaseRowInput)->setField("rootname","users")->setField("system",1)
]);

Database::newStructure("app_security", $a);

/* Table `sessions` */
$a = new DatabaseTableBuilder;
$a->addColumn("session_id", "CHAR(40)")->setAsPrimaryKey();
$a->addColumn("content", "TEXT");
$a->addColumn("client", "TEXT");
$a->addColumn("cnf", "TEXT");
$a->addColumn("start", "INT");
$a->addColumn("expire", "INT");
$a->addColumn("user", "INT")->allowNull(true);

$a->createIndex("ses", ["user", "session_id"]);
$a->createIndex("expire", ["expire"]);

Database::newStructure("sessions", $a);

/* Table `cron` */
$a = new DatabaseTableBuilder;
$a->addColumn("key", "VARCHAR(50)")->setAsPrimaryKey();
$a->addColumn("last_exec", "INT");

Database::newStructure("cron", $a);
