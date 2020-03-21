<?php

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

$b = new DatabaseTableBuilder;
$b->addColumn("id", "INT")->setAsPrimaryKey()->auto_increment();
$b->addColumn("name");
$b->addColumn("link");
$b->addColumn("fa");
$b->addColumn("minUser", "INT");
$b->addColumn("location", "INT(1)");

$b->insertFresh([
    ["name" => "Administrator", "link" => "/admin", "fa" => "wrench", "minUser" => 1, "location" => 0],
    ["name" => "Modify Menu", "link" => "/admin/manage/menus", "fa" => "link", "minUser" => 1, "location" => 0],
]);

return $b;
