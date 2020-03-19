<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

$b = new DatabaseTableBuilder;
$b->addColumn("id","INT")->setAsPrimaryKey()->auto_increment();
$b->addColumn("name");
$b->addColumn("link");
$b->addColumn("fa");
$b->addColumn("minUser","INT");
$b->addColumn("location","INT(1)");

$b->insertFresh([
    (new DatabaseRowInput)->setField("name","Administrator")->setField("link","/admin")->setField("fa","wrench")->setField("minUser",1)->setField("location",0),
    (new DatabaseRowInput)->setField("name","Modify Menu")->setField("link","/admin/manage/menus")->setField("fa","link")->setField("minUser",1)->setField("location",0),
]);

return $b;