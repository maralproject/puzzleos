<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

$table = new DatabaseTableBuilder;

$table->addColumn("id","VARCHAR(128)")->setAsPrimaryKey();
$table->addColumn("content");
$table->addColumn("expires","INT");

$table->createIndex("expires",["expires"]);

return $table;
?>