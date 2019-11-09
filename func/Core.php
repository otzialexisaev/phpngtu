<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 07.11.2019
 * Time: 22:48
 */

class Core
{
    private $root = null;
    private $cwd = null;
    private $cfgPath = __DIR__.'/configs/';
    private $folderCfg = null;

    public function __construct()
    {
        $this->root = __DIR__.'\..\\';
//        $this->root = '\..\\';
        $this->cwd = getcwd();
        $this->folderCfg = (include $this->cfgPath.'folders.php');
//        echo "<a href='$this->root'>asadsad</a>";
//        echo $this->cwd;
//        echo substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']));

        //todo too
        $folders = array_diff(scandir($this->cwd), array('..', '.'));
        foreach ($folders as $k => $folder) {
            if (!is_dir($folder) || in_array($folder, $this->folderCfg['hide'])) {
//                echo $folder;
                unset($folders[$k]);
            }
        }
        //todo
        $folders[] = '../';
        foreach ($folders as $folder) {
            echo "<a href='$folder'>$folder</a><br>";
        }
//        var_dump($folders);
//        echo $this->root;
//        echo $this->cwd;
    }
}