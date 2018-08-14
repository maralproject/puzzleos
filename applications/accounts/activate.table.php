<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.4") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.0
 */

$table = new DatabaseTableBuilder;

$table->addColumn("id","VARCHAR(128)")->setAsPrimaryKey();
$table->addColumn("content");
$table->addColumn("expires","INT");

return $table;
?>