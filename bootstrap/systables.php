<?php
defined("__POSEXEC") or die("No direct access allowed!");
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @package      maral.puzzleos.core
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 *
 * @software     Release: 2.0.0
 */

/* This file responsible for handling system table structure */

/* Table `userdata` */
$a = new DatabaseTableBuilder;
$a->addColumn("app");
$a->addColumn("identifier");
$a->addColumn("physical_path");
$a->addColumn("mime_type");
$a->addColumn("ver","INT");
$a->addColumn("secure","TINYINT(1)");

Database::newStructure("userdata",$a);

/* Table `multidomain_config` */
$a = new DatabaseTableBuilder;
$a->addColumn("host","VARCHAR(50)")->setAsPrimaryKey();
$a->addColumn("default_app","VARCHAR(50)");
$a->addColumn("default_template","VARCHAR(50)");
$a->addColumn("restricted_app");

$a->newInitialRow("{root}","admin","blank","[]");

Database::newStructure("multidomain_config",$a);

/* Table `app_security` */
$a = new DatabaseTableBuilder;
$a->addColumn("rootname","VARCHAR(50)")->setAsPrimaryKey();
$a->addColumn("group","INT")->allowNull(true);
$a->addColumn("system","INT")->defaultValue("0");

$a->newInitialRow("admin",NULL,1);
$a->newInitialRow("bootstrap",NULL,1);
$a->newInitialRow("fontawesome",NULL,1);
$a->newInitialRow("menus",NULL,1);
$a->newInitialRow("page_control",NULL,1);
$a->newInitialRow("phpmailer",NULL,1);
$a->newInitialRow("search_box",NULL,1);
$a->newInitialRow("tinymce",NULL,1);
$a->newInitialRow("upload_img_ajax",NULL,1);
$a->newInitialRow("users",NULL,1);

Database::newStructure("app_security",$a);

/* Table `sessions` */
$a = new DatabaseTableBuilder;
$a->addColumn("session_id","CHAR(32)")->setAsPrimaryKey();
$a->addColumn("content","TEXT");
$a->addColumn("client","TEXT");
$a->addColumn("cnf","TEXT");
$a->addColumn("start","INT");
$a->addColumn("expire","INT");
$a->addColumn("user","INT")->allowNull(true);

Database::newStructure("sessions",$a);

/* Table `cron` */
$a = new DatabaseTableBuilder;
$a->addColumn("key","VARCHAR(50)")->setAsPrimaryKey();
$a->addColumn("last_exec","INT");

Database::newStructure("cron",$a);

unset($a);

?>
