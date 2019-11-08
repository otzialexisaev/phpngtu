<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.11.2019
 * Time: 22:48
 */

class Core
{
    private $cwd = null;

    public function __construct()
    {
        $this->cwd = getcwd();
        echo $this->cwd;
    }
}