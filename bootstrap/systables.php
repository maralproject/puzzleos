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
    ->addColumn("identifier", "VARCHAR(100)")
    ->addColumn("physical_path", "VARCHAR(500)")
    ->addColumn("mime_type", "VARCHAR(100)")
    ->addColumn("ver", "INT")
    ->addColumn("secure", "TINYINT(1)")

    ->createIndex("main", ["app", "identifier"], "UNIQUE")
    ->createIndex("path", ["physical_path"]));

/* Table `multidomain_config` */
Database::newStructure("multidomain_config", (new DatabaseTableBuilder)
    ->addColumn("host", "VARCHAR(50)")->setAsPrimaryKey()
    ->addColumn("default_app", "VARCHAR(50)")
    ->addColumn("default_template", "VARCHAR(50)")
    ->addColumn("restricted_app")

    ->insertFresh([[
        "host" => "{root}",
        "default_app" => "admin",
        "default_template" => "admin_theme",
        "restricted_app" => "[]"
    ]]));

/* Table `app_security` */
Database::newStructure("app_security", (new DatabaseTableBuilder)
    ->addColumn("rootname", "VARCHAR(50)")->setAsPrimaryKey()
    ->addColumn("group", "INT")->allowNull(true)
    ->addColumn("system", "TINYINT")->defaultValue(0)

    ->insertFresh([
        ["rootname" => "admin", "system" => 1],
        ["rootname" => "bootstrap", "system" => 1],
        ["rootname" => "fontawesome", "system" => 1],
        ["rootname" => "menus", "system" => 1],
        ["rootname" => "page_control", "system" => 1],
        ["rootname" => "phpmailer", "system" => 1],
        ["rootname" => "search_box", "system" => 1],
        ["rootname" => "tinymce", "system" => 1],
        ["rootname" => "upload_img_ajax", "system" => 1],
        ["rootname" => "users", "system" => 1]
    ]));

/* Table `sessions` */
Database::newStructure("sessions", (new DatabaseTableBuilder)
    ->addColumn("session_id", "CHAR(40)")->setAsPrimaryKey()

    // Content info, rewritable
    ->addColumn("user", "INT")->allowNull()
    ->addColumn("content", "TEXT")->allowNull()

    // Client info, filled once on insert
    ->addColumn("agent", "TEXT")->allowNull()
    ->addColumn("domain", "TEXT")
    ->addColumn("remote", "TEXT")
    ->addColumn("created", "INT")
    ->createIndex("ses", ["user", "session_id"])
    ->createIndex("created", ["created"]));

/* Table `cron` */
Database::newStructure("cron", (new DatabaseTableBuilder)
    ->addColumn("key", "VARCHAR(50)")
    ->addColumn("last_exec", "INT")
    ->createIndex("key", ["key"], "UNIQUE"));
