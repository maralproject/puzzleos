<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

$b = new DatabaseTableBuilder;
$b->addColumn("id","INT")->setAsPrimaryKey()->defaultValue("AUTO_INCREMENT");
$b->addColumn("name");
$b->addColumn("link");
$b->addColumn("fa");
$b->addColumn("minUser","INT");
$b->addColumn("location","INT(1)");

$b->newInitialRow("Administrator","/admin","wrench",1,0);
$b->newInitialRow("Modify Menu","/admin/manage/menus","link",1,0);

return $b;

?>