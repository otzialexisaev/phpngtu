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
$display->display();