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
        $this->cwd = getcwd();
        $this->folderCfg = (include $this->cfgPath.'folders.php');
    }

    public function getCurrentFolders()
    {
        $folders = array_diff(scandir($this->root), array('..', '.'));
        foreach ($folders as $k => $folder) {
            if (!is_dir($folder) || in_array($folder, $this->folderCfg['hide'])) {
                unset($folders[$k]);
            }
        }
        $folders[] = '../';
        return $folders;
    }
}