<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.11.2019
 * Time: 23:05
 */

require_once "Core.php";
require_once __DIR__. "/../designer/Designer.php";

$core = new Core();
$display = new Designer();
$html = $display->display();
$check = 'blue';
ob_start();
foreach ($html as $row) {
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