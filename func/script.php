<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.11.2019
 * Time: 23:05
 */
ini_set("xdebug.var_display_max_children", -1);
ini_set("xdebug.var_display_max_data", -1);
ini_set("xdebug.var_display_max_depth", -1);
require_once "Core.php";
require_once __DIR__. "/../designer/Designer.php";

$core = new Core();
$mainMenuItems = $core->getMainFolders();
$navMenuItems = $core->getNavFolders();
$display = new Designer();
$head = $display->getHeadContents();
//var_dump($mainMenuItems);
$mainMenu = $display->getMainMenuContents($mainMenuItems);
//$navMenuItems = ['asasd','12312312'];
$navMenu = $display->getNavContents($navMenuItems);
ob_start();
foreach ($head as $row) {
    echo $row;
}
foreach ($mainMenu as $row) {
    echo $row;
}

foreach ($navMenu as $row) {
    echo $row;
}
//var_dump($html);
//<!--    <html>-->
//<!--    <head>-->
//<!--        <style>-->
//<!--        </style>-->
//<!--    </head>-->
//<!--    <body>-->
//<!--    <p>It's like comparing apples to oranges.</p>-->
//<!--    </body>-->
//<!--    </html>-->

ob_end_flush();