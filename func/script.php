<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.11.2019
 * Time: 23:05
 */
require_once "Core.php";
require_once __DIR__ . "/../designer/Designer.php";

$core = new Core();
$display = new Designer();
////////////////////////////////////////////////////////////////////
$mainMenuItems = $core->getMainFolders();
$navMenuItems = $core->getNavFolders();
$pagename = $core->getCurrentPageRusName();
$currentMenuItems = $core->getCurrentFolders();
$contentItem = $core->getContent();
////////////////////////////////////////////////////////////////////
$logoItem = $display->getLogoContents($core->httpUri);
$head = $display->getHeadContents($pagename);
$mainMenu = $display->getMainMenuContents($mainMenuItems);
$navMenu = $display->getNavContents($navMenuItems, $pagename);
$center = $display->getCenterContents($currentMenuItems, $contentItem);
////////////////////////////////////////////////////////////////////
ob_start();
foreach ($head as $row) {
    echo $row;
}
foreach ($logoItem as $row) {
    echo $row;
}
foreach ($mainMenu as $row) {
    echo $row;
}
foreach ($navMenu as $row) {
    echo $row;
}
foreach ($center as $row) {
//    var_dump($row);
    echo $row;
}
//var_dump($center);
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