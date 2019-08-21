<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

/**
 * This file responsible for creating system table schema
 */

/* Table `userdata` */
Database::newStructure("userdata", (new DatabaseTableBuilder)
    ->addColumn("app", "VARCHAR(50)")
    ->addColumn("identifier", "VARCHAR(500)")
    ->addColumn("physical_path", "VARCHAR(500)")
    ->addColumn("mime_type", "VARCHAR(100)")
    ->addColumn("ver", "INT")
    ->addColumn("secure", "TINYINT(1)")

    ->createIndex("main", ["app", "identifier"])
    ->createIndex("path", ["physical_path"]));

/* Table `multidomain_config` */
Database::newStructure("multidomain_config", (new DatabaseTableBuilder)
    ->addColumn("host", "VARCHAR(50)")->setAsPrimaryKey()
    ->addColumn("default_app", "VARCHAR(50)")
    ->addColumn("default_template", "VARCHAR(50)")
    ->addColumn("restricted_app")

    ->insertFresh([
        (new DatabaseRowInput)
            ->setField("host", "{root}")
            ->setField("default_app", "admin")
            ->setField("default_template", "admin_theme")
            ->setField("restricted_app", "[]")
    ]));

/* Table `app_security` */
Database::newStructure("app_security", (new DatabaseTableBuilder)
    ->addColumn("rootname", "VARCHAR(50)")->setAsPrimaryKey()
    ->addColumn("group", "INT")->allowNull(true)
    ->addColumn("system", "TINYINT")->defaultValue(0)

    ->insertFresh([
        (new DatabaseRowInput)->setField("rootname", "admin")->setField("system", 1),
        (new DatabaseRowInput)->setField("rootname", "bootstrap")->setField("system", 1),
        (new DatabaseRowInput)->setField("rootname", "fontawesome")->setField("system", 1),
        (new DatabaseRowInput)->setField("rootname", "menus")->setField("system", 1),
        (new DatabaseRowInput)->setField("rootname", "page_control")->setField("system", 1),
        (new DatabaseRowInput)->setField("rootname", "phpmailer")->setField("system", 1),
        (new DatabaseRowInput)->setField("rootname", "search_box")->setField("system", 1),
        (new DatabaseRowInput)->setField("rootname", "tinymce")->setField("system", 1),
        (new DatabaseRowInput)->setField("rootname", "upload_img_ajax")->setField("system", 1),
        (new DatabaseRowInput)->setField("rootname", "users")->setField("system", 1)
    ]));

/* Table `sessions` */
Database::newStructure("sessions", (new DatabaseTableBuilder)
    ->addColumn("session_id", "CHAR(40)")->setAsPrimaryKey()
    ->addColumn("content", "TEXT")
    ->addColumn("client", "TEXT")
    ->addColumn("cnf", "TEXT")
    ->addColumn("start", "INT")
    ->addColumn("expire", "INT")
    ->addColumn("user", "INT")->allowNull(true)
    ->createIndex("ses", ["user", "session_id"])
    ->createIndex("expire", ["expire"]));

/* Table `cron` */
Database::newStructure("cron", (new DatabaseTableBuilder)
    ->addColumn("key", "VARCHAR(50)")->setAsPrimaryKey()
    ->addColumn("last_exec", "INT"));